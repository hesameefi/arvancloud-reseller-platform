<?php
if (!defined('ABSPATH')) {
    exit;
}

class Arvan_Admin {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('admin_menu', array($this, 'register_admin_menus'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));

        // AJAX handlers
        add_action('wp_ajax_arvan_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_arvan_run_cron_now', array($this, 'ajax_run_cron_now'));
        add_action('wp_ajax_arvan_seed_demo_data', array($this, 'ajax_seed_demo_data'));
        add_action('wp_ajax_arvan_reset_demo_data', array($this, 'ajax_reset_demo_data'));
        add_action('wp_ajax_arvan_admin_delete_server', array($this, 'ajax_admin_delete_server'));
        add_action('wp_ajax_arvan_admin_edit_server', array($this, 'ajax_admin_edit_server'));
    }

    public function register_admin_menus() {
        add_menu_page(
            'ریسلر ابر آروان',
            'ابر آروان ریسلر',
            'manage_options',
            'arvan-reseller',
            array($this, 'render_dashboard_page'),
            'dashicons-cloud',
            30
        );

        add_submenu_page(
            'arvan-reseller',
            'داشبورد و وضعیت سود',
            'داشبورد و سود',
            'manage_options',
            'arvan-reseller',
            array($this, 'render_dashboard_page')
        );

        add_submenu_page(
            'arvan-reseller',
            'تنظیمات و سود ریسلر',
            'تنظیمات و کارمزد',
            'manage_options',
            'arvan-reseller-settings',
            array($this, 'render_settings_page')
        );

        add_submenu_page(
            'arvan-reseller',
            'لاگ جامع تراکنش‌ها',
            'دفتر کل (Ledger)',
            'manage_options',
            'arvan-reseller-ledger',
            array($this, 'render_ledger_page')
        );
    }

    public function enqueue_admin_assets($hook) {
        $page = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';
        if (strpos($page, 'arvan-reseller') !== false || strpos($hook, 'arvan-reseller') !== false) {
            $ver = time(); // Force cache-busting
            wp_enqueue_style('arvan-admin-css', ARVAN_RESELLER_URL . 'assets/css/admin.css', array(), $ver);
            wp_enqueue_script('arvan-admin-js', ARVAN_RESELLER_URL . 'assets/js/admin.js', array('jquery'), $ver, true);
            wp_localize_script('arvan-admin-js', 'ArvanAdmin', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('arvan_admin_nonce')
            ));
        }
    }

    public function render_dashboard_page() {
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $table_settlements = $wpdb->prefix . 'arvan_settlements';
        $table_wallets = $wpdb->prefix . 'arvan_wallets';

        $total_active_servers = $wpdb->get_var("SELECT COUNT(*) FROM {$table_resources} WHERE status = 'ACTIVE'") ?: 0;
        $total_suspended = $wpdb->get_var("SELECT COUNT(*) FROM {$table_resources} WHERE status = 'SUSPENDED'") ?: 0;
        $total_profit = $wpdb->get_var("SELECT SUM(reseller_net_profit) FROM {$table_settlements}") ?: 0;
        $total_customer_balances = $wpdb->get_var("SELECT SUM(balance) FROM {$table_wallets}") ?: 0;

        $recent_servers = $wpdb->get_results("SELECT * FROM {$table_resources} WHERE status != 'TERMINATED' ORDER BY id DESC", ARRAY_A);

        include ARVAN_RESELLER_PATH . 'templates/admin-dashboard.php';
    }

    public function render_settings_page() {
        include ARVAN_RESELLER_PATH . 'templates/admin-settings.php';
    }

    public function render_ledger_page() {
        global $wpdb;
        $table_ledger = $wpdb->prefix . 'arvan_ledger';
        $transactions = $wpdb->get_results("SELECT * FROM {$table_ledger} ORDER BY id DESC LIMIT 100", ARRAY_A);
        include ARVAN_RESELLER_PATH . 'templates/admin-ledger.php';
    }

    public function ajax_save_settings() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }
        $api_key = isset($_POST['api_key']) ? trim(sanitize_text_field($_POST['api_key'])) : '';
        $mode = isset($_POST['mode']) ? sanitize_text_field($_POST['mode']) : 'mock';
        $margin = isset($_POST['margin']) ? floatval($_POST['margin']) : 20.0;
        $rpm = isset($_POST['rate_limit_rpm']) ? intval($_POST['rate_limit_rpm']) : 60;

        if ($margin < 0 || $margin > 20) {
            wp_send_json_error('حاشیه سود ریسلر باید بین ۰ تا ۲۰ درصد باشد.');
        }

        // Encrypt API key with AES-256
        $encrypted_key = Arvan_Security::encrypt($api_key);

        update_option('arvan_api_key', $encrypted_key);
        update_option('arvan_mode', $mode);
        update_option('arvan_reseller_margin', $margin);
        update_option('arvan_rate_limit_rpm', max(10, min(600, $rpm)));

        // Reload credentials in API client singleton
        Arvan_API_Client::get_instance()->reload_credentials();

        wp_send_json_success('تنظیمات با موفقیت ذخیره و کلید API با الگوریتم AES-256 رمزنگاری گردید.');
    }

    public function ajax_run_cron_now() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }

        Arvan_Cron::get_instance()->process_hourly_consumption();
        wp_send_json_success('کران‌جاب ساعتی با موفقیت اجرا شد و مبالغ مصرف طبق فرمول کسر و سود محاسبه گردید.');
    }

    public function ajax_seed_demo_data() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }

        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_ledger = $wpdb->prefix . 'arvan_ledger';
        $table_settlements = $wpdb->prefix . 'arvan_settlements';

        // 1. Ensure user wallet has balance
        $wallet_mgr = Arvan_Wallet::get_instance();
        $wallet_mgr->deposit(1, 350000, 'SEED-DEP-101', 'افزایش اعتبار اولیه کیف پول (داده دمو)');

        // 2. Add realistic demo servers across different regions
        $demo_servers = array(
            array(
                'user_id' => 1,
                'resource_id' => 'srv-tehran-web',
                'resource_type' => 'SERVER',
                'name' => 'production-web-cluster',
                'region' => 'ir-thr-at1',
                'flavor_id' => 'g1-4-2-0',
                'flavor_name' => 'Pro Standard (2 vCPU, 4GB RAM)',
                'specs' => json_encode(array('cpu' => 2, 'memory' => 4096, 'disk' => 55)),
                'hourly_base_price' => 890.00,
                'reseller_margin_percent' => 20.00,
                'hourly_customer_price' => 1068.00,
                'ip_address' => '185.143.233.72',
                'status' => 'ACTIVE',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days'))
            ),
            array(
                'user_id' => 1,
                'resource_id' => 'srv-tabriz-db',
                'resource_type' => 'SERVER',
                'name' => 'postgresql-database-master',
                'region' => 'ir-tbz-dc1',
                'flavor_id' => 'g1-8-4-0',
                'flavor_name' => 'Enterprise Power (4 vCPU, 8GB RAM)',
                'specs' => json_encode(array('cpu' => 4, 'memory' => 8192, 'disk' => 100)),
                'hourly_base_price' => 1680.00,
                'reseller_margin_percent' => 20.00,
                'hourly_customer_price' => 2016.00,
                'ip_address' => '185.143.234.19',
                'status' => 'ACTIVE',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
            ),
            array(
                'user_id' => 1,
                'resource_id' => 'srv-ams-backup',
                'resource_type' => 'SERVER',
                'name' => 'amsterdam-edge-proxy',
                'region' => 'nl-ams-1',
                'flavor_id' => 'g1-2-1-0',
                'flavor_name' => 'General 2GB (1 vCPU, 2GB RAM)',
                'specs' => json_encode(array('cpu' => 1, 'memory' => 2048, 'disk' => 35)),
                'hourly_base_price' => 450.00,
                'reseller_margin_percent' => 20.00,
                'hourly_customer_price' => 540.00,
                'ip_address' => '194.36.174.88',
                'status' => 'SUSPENDED',
                'suspended_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'created_at' => date('Y-m-d H:i:s', strtotime('-6 days'))
            )
        );

        foreach ($demo_servers as $srv) {
            $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_resources} WHERE resource_id = %s", $srv['resource_id']));
            if (!$exists) {
                $wpdb->insert($table_resources, $srv);
            }
        }

        // 3. Add settlement profit logs
        $wpdb->insert($table_settlements, array(
            'period_start' => date('Y-m-d H:00:00', strtotime('-24 hours')),
            'period_end' => date('Y-m-d H:00:00'),
            'total_burned_amount' => 74800.00,
            'provider_base_cost' => 62330.00,
            'reseller_net_profit' => 12470.00,
            'active_resources_count' => 3,
            'created_at' => current_time('mysql')
        ));

        wp_send_json_success('داده‌های دمو با موفقیت در دیتابیس بارگذاری شدند.');
    }

    public function ajax_reset_demo_data() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }

        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}arvan_resources");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}arvan_ledger");
        $wpdb->query("TRUNCATE TABLE {$wpdb->prefix}arvan_settlements");
        $wpdb->query("UPDATE {$wpdb->prefix}arvan_wallets SET balance = 0.00");

        wp_send_json_success('تمام داده‌های دمو با موفقیت ریست شدند.');
    }

    public function ajax_admin_delete_server() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }

        $id = intval($_POST['id']);
        global $wpdb;
        $wpdb->delete($wpdb->prefix . 'arvan_resources', array('id' => $id));
        wp_send_json_success('سرور با موفقیت حذف گردید.');
    }

    public function ajax_admin_edit_server() {
        check_ajax_referer('arvan_admin_nonce', 'nonce');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('عدم دسترسی');
        }

        $id = intval($_POST['id']);
        $name = sanitize_text_field($_POST['name']);
        $status = sanitize_text_field($_POST['status']);

        global $wpdb;
        $wpdb->update($wpdb->prefix . 'arvan_resources', array(
            'name' => $name,
            'status' => $status
        ), array('id' => $id));

        wp_send_json_success('اطلاعات سرور با موفقیت ویرایش شد.');
    }
}
