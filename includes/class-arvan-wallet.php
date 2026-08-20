<?php
if (!defined('ABSPATH')) {
    exit;
}

class Arvan_Wallet {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get User Wallet
     */
    public function get_wallet($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'arvan_wallets';

        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d",
            $user_id
        ), ARRAY_A);

        if (!$wallet) {
            // Auto-create wallet for user with 0 balance
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'balance' => 0.00,
                'warning_threshold' => 50000.00,
                'currency' => 'IRT',
                'status' => 'ACTIVE'
            ));
            return $this->get_wallet($user_id);
        }

        return $wallet;
    }

    /**
     * Get Current Balance
     */
    public function get_balance($user_id) {
        $wallet = $this->get_wallet($user_id);
        return floatval($wallet['balance']);
    }

    /**
     * Deposit into User Wallet
     */
    public function deposit($user_id, $amount, $reference_id = null, $description = 'شارژ آنلاین کیف پول') {
        global $wpdb;
        $amount = floatval($amount);
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'مبلغ نامعتبر است.');
        }

        $wallet = $this->get_wallet($user_id);
        $balance_before = floatval($wallet['balance']);
        $balance_after = $balance_before + $amount;

        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_ledger = $wpdb->prefix . 'arvan_ledger';

        // Update Wallet Balance
        $wpdb->update(
            $table_wallets,
            array('balance' => $balance_after, 'status' => 'ACTIVE', 'updated_at' => current_time('mysql')),
            array('id' => $wallet['id'])
        );

        // Record in Ledger
        $wpdb->insert($table_ledger, array(
            'wallet_id' => $wallet['id'],
            'user_id' => $user_id,
            'type' => 'DEPOSIT',
            'amount' => $amount,
            'balance_before' => $balance_before,
            'balance_after' => $balance_after,
            'reference_id' => $reference_id,
            'description' => $description,
            'created_at' => current_time('mysql')
        ));

        // Auto-Resume any suspended servers if balance > 0
        Arvan_Cron::get_instance()->resume_user_services($user_id);

        return array(
            'success' => true,
            'balance_before' => $balance_before,
            'balance_after' => $balance_after
        );
    }

    /**
     * Deduct Hourly Consumption (Hourly Burn)
     */
    public function burn_hourly($user_id, $amount, $resource_id, $description = 'کسر هزینه ساعتی مصرف سرور ابری') {
        global $wpdb;
        $amount = floatval($amount);
        if ($amount <= 0) {
            return false;
        }

        $wallet = $this->get_wallet($user_id);
        $balance_before = floatval($wallet['balance']);
        $balance_after = $balance_before - $amount;

        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_ledger = $wpdb->prefix . 'arvan_ledger';

        // Update Wallet Balance
        $wpdb->update(
            $table_wallets,
            array('balance' => $balance_after, 'updated_at' => current_time('mysql')),
            array('id' => $wallet['id'])
        );

        // Record in Ledger
        $wpdb->insert($table_ledger, array(
            'wallet_id' => $wallet['id'],
            'user_id' => $user_id,
            'type' => 'HOURLY_BURN',
            'amount' => -$amount,
            'balance_before' => $balance_before,
            'balance_after' => $balance_after,
            'reference_id' => $resource_id,
            'description' => $description,
            'created_at' => current_time('mysql')
        ));

        return $balance_after;
    }

    /**
     * Get Ledger History
     */
    public function get_ledger_history($user_id, $limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'arvan_ledger';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY created_at DESC LIMIT %d",
            $user_id,
            $limit
        ), ARRAY_A);
    }
}
