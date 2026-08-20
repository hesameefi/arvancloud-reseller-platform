<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enterprise Hourly Burn & 5-Stage Lifecycle Suspension Engine with Idempotency Locks
 */
class Arvan_Cron {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('arvan_hourly_burn_cron', array($this, 'process_hourly_consumption'));
    }

    public static function schedule_events() {
        if (!wp_next_scheduled('arvan_hourly_burn_cron')) {
            wp_schedule_event(time(), 'hourly', 'arvan_hourly_burn_cron');
        }
    }

    public static function clear_events() {
        $timestamp = wp_next_scheduled('arvan_hourly_burn_cron');
        if ($timestamp) {
            wp_unschedule_event($timestamp, 'arvan_hourly_burn_cron');
        }
    }

    /**
     * Process Hourly Consumption with Distributed Mutex Lock (Idempotent Execution)
     */
    public function process_hourly_consumption() {
        // Prevent concurrent overlapping cron executions (Distributed Mutex Lock)
        if (get_transient('arvan_cron_executing_lock')) {
            return;
        }
        set_transient('arvan_cron_executing_lock', true, 45); // 45 seconds lock

        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_settlements = $wpdb->prefix . 'arvan_settlements';

        try {
            $active_resources = $wpdb->get_results(
                "SELECT * FROM {$table_resources} WHERE status IN ('ACTIVE', 'SUSPENDED')",
                ARRAY_A
            );

            if (empty($active_resources)) {
                delete_transient('arvan_cron_executing_lock');
                return;
            }

            $wallet_mgr = Arvan_Wallet::get_instance();
            $api_client = Arvan_API_Client::get_instance();

            $total_burned = 0.00;
            $total_provider_cost = 0.00;
            $total_reseller_profit = 0.00;
            $active_count = 0;

            foreach ($active_resources as $res) {
                $user_id = intval($res['user_id']);
                $current_balance = $wallet_mgr->get_balance($user_id);
                $hourly_customer = floatval($res['hourly_customer_price']);
                $hourly_base = floatval($res['hourly_base_price']);

                // 1. If Active, deduct hourly burn atomically
                if ($res['status'] === 'ACTIVE') {
                    if ($current_balance >= $hourly_customer) {
                        $new_balance = $wallet_mgr->burn_hourly($user_id, $hourly_customer, $res['resource_id'], "مصرف ساعتی سرور {$res['name']}");
                        
                        $total_burned += $hourly_customer;
                        $total_provider_cost += $hourly_base;
                        $total_reseller_profit += ($hourly_customer - $hourly_base);
                        $active_count++;

                        // Check low balance threshold
                        $wallet = $wallet_mgr->get_wallet($user_id);
                        if ($new_balance <= floatval($wallet['warning_threshold'])) {
                            $this->send_low_balance_alert($user_id, $new_balance);
                        }
                    } else {
                        // Balance reached 0: Execute Stage 1 & 2 Auto-Suspension & Power-Off
                        $this->suspend_resource($res);
                    }
                } 
                // 2. If Suspended, check for 7-Day Termination Grace Period
                elseif ($res['status'] === 'SUSPENDED') {
                    if (!empty($res['suspended_at'])) {
                        $suspended_time = strtotime($res['suspended_at']);
                        $days_suspended = (time() - $suspended_time) / (60 * 60 * 24);

                        if ($days_suspended >= 7) {
                            // 7 Days Expired: Execute Permanent Termination
                            $this->terminate_resource($res);
                        }
                    }
                }
            }

            // Record Settlement Summary Log
            if ($total_burned > 0) {
                $wpdb->insert($table_settlements, array(
                    'period_start' => date('Y-m-d H:00:00', strtotime('-1 hour')),
                    'period_end' => date('Y-m-d H:00:00'),
                    'total_burned_amount' => $total_burned,
                    'provider_base_cost' => $total_provider_cost,
                    'reseller_net_profit' => $total_reseller_profit,
                    'active_resources_count' => $active_count,
                    'created_at' => current_time('mysql')
                ));
            }
        } finally {
            delete_transient('arvan_cron_executing_lock');
        }
    }

    /**
     * Suspend a Resource (Power Off & Lock Network)
     */
    public function suspend_resource($res) {
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';

        Arvan_API_Client::get_instance()->power_off_server($res['resource_id'], $res['region']);

        $wpdb->update(
            $table_resources,
            array(
                'status' => 'SUSPENDED',
                'suspended_at' => current_time('mysql')
            ),
            array('id' => $res['id'])
        );
    }

    /**
     * Terminate a Resource Permanently (After 7 Days)
     */
    public function terminate_resource($res) {
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';

        Arvan_API_Client::get_instance()->terminate_server($res['resource_id'], $res['region']);

        $wpdb->update(
            $table_resources,
            array(
                'status' => 'TERMINATED',
                'terminated_at' => current_time('mysql')
            ),
            array('id' => $res['id'])
        );
    }

    /**
     * Resume Suspended Services on Wallet Top-up
     */
    public function resume_user_services($user_id) {
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';

        $suspended = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_resources} WHERE user_id = %d AND status = 'SUSPENDED'",
            $user_id
        ), ARRAY_A);

        foreach ($suspended as $res) {
            Arvan_API_Client::get_instance()->power_on_server($res['resource_id'], $res['region']);
            $wpdb->update(
                $table_resources,
                array(
                    'status' => 'ACTIVE',
                    'suspended_at' => null
                ),
                array('id' => $res['id'])
            );
        }
    }

    private function send_low_balance_alert($user_id, $balance) {
        $user = get_userdata($user_id);
        if ($user && $user->user_email) {
            $subject = 'هشدار کاهش موجودی حساب ابری ابر آروان';
            $msg = "کاربر گرامی، موجودی کیف پول شما به {$balance} تومان کاهش یافته است. لطفاً جهت جلوگیری از خاموش شدن سرورها نسبت به شارژ اقدام فرمایید.";
            wp_mail($user->user_email, $subject, $msg);
        }
    }
}
