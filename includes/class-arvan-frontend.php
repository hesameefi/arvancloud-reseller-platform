<?php
if (!defined('ABSPATH')) {
    exit;
}

class Arvan_Frontend {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_shortcode('arvan_cloud_dashboard', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Customer AJAX Actions
        add_action('wp_ajax_arvan_customer_create_server', array($this, 'ajax_create_server'));
        add_action('wp_ajax_arvan_customer_toggle_power', array($this, 'ajax_toggle_power'));
        add_action('wp_ajax_arvan_customer_deposit', array($this, 'ajax_customer_deposit'));
    }

    public function enqueue_frontend_assets() {
        wp_enqueue_style('arvan-sorkhab-theme', ARVAN_RESELLER_URL . 'assets/css/sorkhab-theme.css', array(), ARVAN_RESELLER_VERSION);
        wp_enqueue_script('arvan-sorkhab-app', ARVAN_RESELLER_URL . 'assets/js/sorkhab-app.js', array('jquery'), ARVAN_RESELLER_VERSION, true);

        $user_id = get_current_user_id() ?: 1; // Fallback to demo user if not logged in
        $wallet = Arvan_Wallet::get_instance()->get_wallet($user_id);
        $margin = floatval(get_option('arvan_reseller_margin', 20));

        wp_localize_script('arvan-sorkhab-app', 'ArvanApp', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('arvan_frontend_nonce'),
            'user_id' => $user_id,
            'balance' => floatval($wallet['balance']),
            'margin' => $margin,
            'flavors' => Arvan_API_Client::get_instance()->get_flavors(),
            'images' => Arvan_API_Client::get_instance()->get_images()
        ));
    }

    public function render_shortcode($atts) {
        $user_id = get_current_user_id() ?: 1;
        $wallet = Arvan_Wallet::get_instance()->get_wallet($user_id);

        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $user_servers = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_resources} WHERE user_id = %d AND status != 'TERMINATED' ORDER BY id DESC",
            $user_id
        ), ARRAY_A);

        $ledger = Arvan_Wallet::get_instance()->get_ledger_history($user_id, 10);
        $flavors = Arvan_API_Client::get_instance()->get_flavors();
        $images = Arvan_API_Client::get_instance()->get_images();
        $margin = floatval(get_option('arvan_reseller_margin', 20));

        ob_start();
        include ARVAN_RESELLER_PATH . 'templates/customer-dashboard.php';
        return ob_get_clean();
    }

    public function ajax_create_server() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;

        $name = sanitize_text_field($_POST['name']);
        $flavor_id = sanitize_text_field($_POST['flavor_id']);
        $image_id = sanitize_text_field($_POST['image_id']);
        $region = sanitize_text_field($_POST['region'] ?: 'ir-thr-at1');

        if (empty($name) || empty($flavor_id)) {
            wp_send_json_error('اطلاعات سرور ناقص است.');
        }

        // Find flavor pricing
        $flavors = Arvan_API_Client::get_instance()->get_flavors($region);
        $chosen_flavor = null;
        foreach ($flavors as $f) {
            if ($f['id'] === $flavor_id) {
                $chosen_flavor = $f;
                break;
            }
        }

        if (!$chosen_flavor) {
            wp_send_json_error('پلن انتخابی معتبر نیست.');
        }

        $hourly_base = floatval($chosen_flavor['hourly_price']);
        $margin = floatval(get_option('arvan_reseller_margin', 20));
        $hourly_customer = round($hourly_base * (1 + ($margin / 100)), 2);

        // Check if user has at least enough balance for 24 hours
        $wallet_mgr = Arvan_Wallet::get_instance();
        $balance = $wallet_mgr->get_balance($user_id);
        $min_required = $hourly_customer * 24;

        if ($balance < $min_required) {
            wp_send_json_error("موجودی کیف پول شما کافی نیست. حداقل موجودی برای ساخت سرور، هزینه ۲۴ ساعت اول (" . number_format($min_required) . " تومان) می‌باشد. لطفاً ابتدا کیف پول خود را شارژ نمایید.");
        }

        // Call API
        $api_res = Arvan_API_Client::get_instance()->create_server(array(
            'name' => $name,
            'flavor_id' => $flavor_id,
            'image_id' => $image_id,
            'region' => $region
        ));

        if (!empty($api_res['error'])) {
            wp_send_json_error($api_res['message']);
        }

        $srv_data = $api_res['data'];

        // Save into DB
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $wpdb->insert($table_resources, array(
            'user_id' => $user_id,
            'resource_id' => $srv_data['id'],
            'resource_type' => 'SERVER',
            'name' => $name,
            'region' => $region,
            'flavor_id' => $flavor_id,
            'flavor_name' => $chosen_flavor['name'],
            'specs' => json_encode($chosen_flavor),
            'hourly_base_price' => $hourly_base,
            'reseller_margin_percent' => $margin,
            'hourly_customer_price' => $hourly_customer,
            'ip_address' => $srv_data['ip'],
            'status' => 'ACTIVE',
            'created_at' => current_time('mysql')
        ));

        wp_send_json_success(array(
            'message' => "سرور ابری {$name} با موفقیت ساخته شد و در حال حاضر روشن و فعال است.",
            'server' => $srv_data
        ));
    }

    public function ajax_toggle_power() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $resource_id = sanitize_text_field($_POST['resource_id']);
        $action = sanitize_text_field($_POST['power_action']); // 'power-off' or 'power-on'

        global $wpdb;
        $table = $wpdb->prefix . 'arvan_resources';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d AND resource_id = %s",
            $user_id,
            $resource_id
        ), ARRAY_A);

        if (!$res) {
            wp_send_json_error('سرور یافت نشد.');
        }

        if ($action === 'power-off') {
            Arvan_API_Client::get_instance()->power_off_server($resource_id, $res['region']);
            $wpdb->update($table, array('status' => 'SUSPENDED', 'suspended_at' => current_time('mysql')), array('id' => $res['id']));
            wp_send_json_success('سرور با موفقیت خاموش (Suspend) گردید.');
        } else {
            Arvan_API_Client::get_instance()->power_on_server($resource_id, $res['region']);
            $wpdb->update($table, array('status' => 'ACTIVE', 'suspended_at' => null), array('id' => $res['id']));
            wp_send_json_success('سرور با موفقیت روشن و راه‌اندازی شد.');
        }
    }

    public function ajax_customer_deposit() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $amount = floatval($_POST['amount']);

        if ($amount < 10000) {
            wp_send_json_error('حداقل مبلغ شارژ ۱۰,۰۰۰ تومان می‌باشد.');
        }

        $res = Arvan_Wallet::get_instance()->deposit($user_id, $amount, 'DEMO-DEP-' . rand(1000, 9999), 'افزایش موجودی آنلاین کیف پول پیش‌پرداخت');

        wp_send_json_success(array(
            'message' => 'کیف پول با موفقیت شارژ شد.',
            'new_balance' => $res['balance_after']
        ));
    }
}
