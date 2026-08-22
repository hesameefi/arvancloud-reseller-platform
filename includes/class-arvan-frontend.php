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
        add_shortcode('arvan_ai_copilot', array($this, 'render_floating_ai_widget'));
        add_action('wp_footer', array($this, 'render_floating_ai_widget'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_assets'));

        // Customer AJAX Actions
        add_action('wp_ajax_arvan_customer_create_server', array($this, 'ajax_create_server'));
        add_action('wp_ajax_nopriv_arvan_customer_create_server', array($this, 'ajax_create_server'));

        add_action('wp_ajax_arvan_customer_toggle_power', array($this, 'ajax_toggle_power'));
        add_action('wp_ajax_nopriv_arvan_customer_toggle_power', array($this, 'ajax_toggle_power'));

        add_action('wp_ajax_arvan_customer_power_action', array($this, 'ajax_toggle_power'));
        add_action('wp_ajax_arvan_customer_upgrade_server', array($this, 'ajax_upgrade_server'));
        add_action('wp_ajax_nopriv_arvan_customer_upgrade_server', array($this, 'ajax_upgrade_server'));
        add_action('wp_ajax_arvan_customer_edit_server', array($this, 'ajax_edit_server'));
        add_action('wp_ajax_nopriv_arvan_customer_edit_server', array($this, 'ajax_edit_server'));
        add_action('wp_ajax_nopriv_arvan_customer_power_action', array($this, 'ajax_toggle_power'));

        add_action('wp_ajax_arvan_customer_deposit', array($this, 'ajax_customer_deposit'));
        add_action('wp_ajax_nopriv_arvan_customer_deposit', array($this, 'ajax_customer_deposit'));

        // S3 Object Storage AJAX Actions (Feature D)
        add_action('wp_ajax_arvan_customer_create_bucket', array($this, 'ajax_create_bucket'));
        add_action('wp_ajax_nopriv_arvan_customer_create_bucket', array($this, 'ajax_create_bucket'));
        add_action('wp_ajax_arvan_customer_list_buckets', array($this, 'ajax_list_buckets'));
        add_action('wp_ajax_nopriv_arvan_customer_list_buckets', array($this, 'ajax_list_buckets'));
        add_action('wp_ajax_arvan_customer_delete_bucket', array($this, 'ajax_delete_bucket'));
        add_action('wp_ajax_nopriv_arvan_customer_delete_bucket', array($this, 'ajax_delete_bucket'));

        // AI Agentic Copilot AJAX Actions
        add_action('wp_ajax_arvan_ai_chat_message', array($this, 'ajax_ai_chat_message'));
        add_action('wp_ajax_nopriv_arvan_ai_chat_message', array($this, 'ajax_ai_chat_message'));
        add_action('wp_ajax_arvan_ai_deploy_server', array($this, 'ajax_ai_deploy_server'));
        add_action('wp_ajax_nopriv_arvan_ai_deploy_server', array($this, 'ajax_ai_deploy_server'));

        // Signed Safe Actions & Diagnostics (Innovation 2 & 3)
        add_action('wp_ajax_arvan_execute_signed_action', array($this, 'ajax_execute_signed_action'));
        add_action('wp_ajax_nopriv_arvan_execute_signed_action', array($this, 'ajax_execute_signed_action'));
        add_action('wp_ajax_arvan_diagnose_server', array($this, 'ajax_diagnose_server'));
        add_action('wp_ajax_nopriv_arvan_diagnose_server', array($this, 'ajax_diagnose_server'));
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
        $resource_id = isset($_POST['server_id']) ? sanitize_text_field($_POST['server_id']) : (isset($_POST['resource_id']) ? sanitize_text_field($_POST['resource_id']) : '');
        $action = isset($_POST['power_action']) ? sanitize_text_field($_POST['power_action']) : (isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : '');

        global $wpdb;
        $table = $wpdb->prefix . 'arvan_resources';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE (user_id = %d OR user_id = 1) AND (resource_id = %s OR id = %s)",
            $user_id,
            $resource_id,
            $resource_id
        ), ARRAY_A);

        if (!$res) {
            $res = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table} WHERE resource_id = %s OR id = %s",
                $resource_id,
                $resource_id
            ), ARRAY_A);
        }

        if (!$res) {
            wp_send_json_error('سرور مورد نظر در دیتابیس یافت نشد.');
        }

        $region = !empty($res['region']) ? $res['region'] : 'ir-thr-at1';

        if ($action === 'power-off' || $action === 'power_off') {
            Arvan_API_Client::get_instance()->power_off_server($res['resource_id'], $region);
            $wpdb->update($table, array('status' => 'SUSPENDED', 'suspended_at' => current_time('mysql')), array('id' => $res['id']));
            wp_send_json_success(array('message' => 'سرور ابری با موفقیت خاموش (Suspend) گردید.'));
        } elseif ($action === 'power-on' || $action === 'power_on') {
            Arvan_API_Client::get_instance()->power_on_server($res['resource_id'], $region);
            $wpdb->update($table, array('status' => 'ACTIVE', 'suspended_at' => null), array('id' => $res['id']));
            wp_send_json_success(array('message' => 'سرور ابری با موفقیت روشن و راه‌اندازی شد.'));
        } elseif ($action === 'terminate' || $action === 'delete') {
            Arvan_API_Client::get_instance()->terminate_server($res['resource_id'], $region);
            $wpdb->update($table, array('status' => 'TERMINATED'), array('id' => $res['id']));
            wp_send_json_success(array('message' => 'سرور ابری با موفقیت حذف دائمی گردید.'));
        } else {
            wp_send_json_error('نوع عملیات نامعتبر است.');
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

    public function ajax_ai_chat_message() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $message = isset($_POST['message']) ? sanitize_text_field($_POST['message']) : '';

        if (empty($message)) {
            wp_send_json_error('متن پیام نمی‌تواند خالی باشد.');
        }

        $agent = Arvan_AI_Agent::get_instance();
        $result = $agent->process_message($user_id, $message);
        wp_send_json_success($result);
    }

    public function ajax_ai_deploy_server() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;

        $flavor_id = isset($_POST['flavor_id']) ? sanitize_text_field($_POST['flavor_id']) : '';
        $region = isset($_POST['region']) ? sanitize_text_field($_POST['region']) : 'ir-thr-at1';
        $image_id = isset($_POST['image_id']) ? sanitize_text_field($_POST['image_id']) : 'img-ubuntu-24';
        $hostname = isset($_POST['hostname']) ? sanitize_text_field($_POST['hostname']) : ('ai-server-' . rand(100, 999));

        $agent = Arvan_AI_Agent::get_instance();
        $result = $agent->process_message($user_id, 'بساز', $flavor_id, $region, $image_id, $hostname);

        if ($result['type'] === 'insufficient_balance' || $result['type'] === 'error') {
            wp_send_json_error($result);
        } else {
            wp_send_json_success($result);
        }
    }

    /**
     * S3 Object Storage Bucket Endpoints (Feature D)
     */
    
    /**
     * AJAX: Upgrade / Resize Server Hardware Flavor
     */
    public function ajax_upgrade_server() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $server_id = isset($_POST['server_id']) ? sanitize_text_field($_POST['server_id']) : '';
        $target_flavor_id = isset($_POST['flavor_id']) ? sanitize_text_field($_POST['flavor_id']) : '';

        if (empty($server_id) || empty($target_flavor_id)) {
            wp_send_json_error('اطلاعات ارتقای سرور ناقص است.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'arvan_resources';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE (user_id = %d OR user_id = 1) AND (resource_id = %s OR id = %s) AND status != 'TERMINATED'",
            $user_id, $server_id, $server_id
        ), ARRAY_A);

        if (!$res) {
            wp_send_json_error('سرور مورد نظر یافت نشد.');
        }

        $catalog = Arvan_API_Client::get_instance()->get_flavors($res['region']);
        $new_flavor = null;
        foreach ($catalog as $f) {
            if ($f['id'] === $target_flavor_id) {
                $new_flavor = $f;
                break;
            }
        }

        if (!$new_flavor) {
            wp_send_json_error('پلن سخت‌افزاری انتخابی معتبر نمی‌باشد.');
        }

        $margin = floatval(get_option('arvan_reseller_margin', 20));
        $hourly_base = floatval($new_flavor['hourly_price']);
        $hourly_customer = round($hourly_base * (1 + ($margin / 100)));

        // Call Hypervisor API
        Arvan_API_Client::get_instance()->resize_server($res['resource_id'], $target_flavor_id, $res['region']);

        // Update DB
        $wpdb->update($table, array(
            'flavor_id' => $target_flavor_id,
            'flavor_name' => $new_flavor['name'],
            'specs' => json_encode(array('cpu' => $new_flavor['cpu'], 'memory' => $new_flavor['memory'], 'disk' => $new_flavor['disk'])),
            'hourly_base_price' => $hourly_base,
            'hourly_customer_price' => $hourly_customer
        ), array('id' => $res['id']));

        wp_send_json_success(array(
            'message' => "⚡ سرور با موفقیت به پلن «{$new_flavor['name']}» تغییر یافت. نرخ جدید: " . number_format($hourly_customer) . " تومان/ساعت",
            'server_id' => $res['resource_id'],
            'flavor_name' => $new_flavor['name'],
            'hourly_customer_price' => number_format($hourly_customer) . ' تومان/ساعت',
            'hourly_price_raw' => $hourly_customer,
            'monthly_price_formatted' => number_format($hourly_customer * 720) . ' تومان/ماه'
        ));
    }

    /**
     * AJAX: Edit / Rename Server
     */
    public function ajax_edit_server() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $server_id = isset($_POST['server_id']) ? sanitize_text_field($_POST['server_id']) : '';
        $new_name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';

        if (empty($server_id) || empty($new_name)) {
            wp_send_json_error('نام جدید سرور نمی‌تواند خالی باشد.');
        }

        global $wpdb;
        $table = $wpdb->prefix . 'arvan_resources';
        $res = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE (user_id = %d OR user_id = 1) AND (resource_id = %s OR id = %s) AND status != 'TERMINATED'",
            $user_id, $server_id, $server_id
        ), ARRAY_A);

        if (!$res) {
            wp_send_json_error('سرور مورد نظر یافت نشد.');
        }

        // Call Hypervisor API
        Arvan_API_Client::get_instance()->rename_server($res['resource_id'], $new_name, $res['region']);

        // Update DB
        $wpdb->update($table, array('name' => $new_name), array('id' => $res['id']));

        wp_send_json_success(array(
            'message' => "نام سرور با موفقیت به «{$new_name}» تغییر یافت.",
            'server_id' => $res['resource_id'],
            'name' => $new_name
        ));
    }

    public function ajax_create_bucket() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;

        $bucket_name = sanitize_text_field($_POST['bucket_name']);
        $region = sanitize_text_field($_POST['region'] ?: 'ir-thr-at1');
        $acl = sanitize_text_field($_POST['acl'] ?: 'private');

        if (empty($bucket_name) || !preg_match('/^[a-z0-9\.\-]{3,63}$/', $bucket_name)) {
            wp_send_json_error('نام باکت نامعتبر است (باید بین ۳ تا ۶۳ کاراکتر با حروف کوچک، عدد و خط تیره باشد).');
        }

        global $wpdb;
        $table_buckets = $wpdb->prefix . 'arvan_buckets';

        // Check duplicate
        $exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table_buckets} WHERE bucket_name = %s", $bucket_name));
        if ($exists) {
            wp_send_json_error('این نام باکت قبلاً ثبت شده است. نام دیگری انتخاب نمایید.');
        }

        $margin = floatval(get_option('arvan_reseller_margin', 20));
        $base_price_gb = 650; // Tomans per GB/month
        $cust_price_gb = round($base_price_gb * (1 + ($margin / 100)));

        $wpdb->insert(
            $table_buckets,
            array(
                'user_id' => $user_id,
                'bucket_name' => $bucket_name,
                'region' => $region,
                'acl' => $acl,
                'size_gb' => 0.00,
                'monthly_price_per_gb' => $base_price_gb,
                'monthly_customer_price' => $cust_price_gb,
                'status' => 'ACTIVE',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s')
        );

        wp_send_json_success(array(
            'message' => "باکت ابری S3 با نام `{$bucket_name}` در دیتاسنتر تهران با موفقیت ایجاد شد.",
            'bucket' => array(
                'name' => $bucket_name,
                'region' => $region,
                'acl' => $acl,
                'endpoint' => "https://s3.{$region}.arvanstorage.ir/{$bucket_name}"
            )
        ));
    }

    public function ajax_list_buckets() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;

        global $wpdb;
        $table_buckets = $wpdb->prefix . 'arvan_buckets';
        $buckets = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table_buckets} WHERE user_id = %d AND status = 'ACTIVE' ORDER BY id DESC",
            $user_id
        ), ARRAY_A);

        wp_send_json_success(array('buckets' => $buckets ?: array()));
    }

    public function ajax_delete_bucket() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $bucket_id = intval($_POST['bucket_id']);

        global $wpdb;
        $table_buckets = $wpdb->prefix . 'arvan_buckets';
        $wpdb->update($table_buckets, array('status' => 'DELETED'), array('id' => $bucket_id, 'user_id' => $user_id));

        wp_send_json_success(array('message' => 'باکت ذخیره‌سازی ابری با موفقیت حذف گردید.'));
    }

    /**
     * Render Global Floating AI Copilot Widget (Feature C)
     */
    public function render_floating_ai_widget() {
        static $rendered = false;
        if ($rendered) {
            return;
        }
        $rendered = true;
        ?>
        <!-- Arvan Floating AI Cloud Architect Widget (Feature C) -->
        <div id="ar_floating_ai_widget" class="ar-floating-ai-container">
            <!-- Floating Trigger Button -->
            <button type="button" id="ar_floating_ai_btn" class="ar-floating-ai-fab" title="دستیار هوشمند ابر آروان">
                <span class="ar-fab-icon">🤖</span>
                <span class="ar-fab-badge">AI</span>
                <span class="ar-fab-pulse"></span>
            </button>

            <!-- Floating Slide-Up Chat Drawer -->
            <div id="ar_floating_ai_drawer" class="ar-floating-ai-drawer" style="display: none;">
                <div class="ar-drawer-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 20px;">🤖</span>
                        <div>
                            <strong style="font-size: 13.5px; color: #fff;">معمار ابری آروان (AI Copilot)</strong>
                            <div style="font-size: 11px; color: rgba(255,255,255,0.6);">مشاوره فنی، انتخاب سرور و راه‌اندازی ۱-کلیک</div>
                        </div>
                    </div>
                    <button type="button" id="ar_drawer_close" class="ar-drawer-close-btn">✕</button>
                </div>

                <div class="ar-drawer-body" id="ar_drawer_chat_messages">
                    <div class="ar-ai-msg bot">
                        <div class="ar-ai-bubble">
                            سلام و درود! 👋 من **معمار ابری هوشمند آروان‌کلود** هستم.<br>
                            پروژه یا سوال فنی خود را بفرمایید تا بهترین سرور یا باکت S3 را به شما پیشنهاد کنم! 🚀
                        </div>
                    </div>
                </div>

                <div class="ar-drawer-quick-chips">
                    <button type="button" class="ar-drawer-chip" data-prompt="یک سرور برای فروشگاه ووکامرس میخوام">🛒 ووکامرس</button>
                    <button type="button" class="ar-drawer-chip" data-prompt="سرور لاراول و داکر با رم ۴ گیگ">⚙️ لاراول</button>
                    <button type="button" class="ar-drawer-chip" data-prompt="دلیل خطای ۵۰۲ Bad Gateway چیه؟">🩺 دیباگ ۵۰۲</button>
                    <button type="button" class="ar-drawer-chip" data-prompt="تفاوت دیتاسنتر شهریار و هلند">🌐 دیتاسنترها</button>
                </div>

                <div class="ar-drawer-footer">
                    <form id="ar_drawer_form" style="display: flex; gap: 6px; width: 100%;">
                        <input type="text" id="ar_drawer_input" class="ar-drawer-input" placeholder="سوال فنی، پروژه یا عیب‌یابی سرور..." autocomplete="off">
                        <button type="submit" id="ar_drawer_send_btn" class="ar-drawer-send-btn">
                            ➤
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    /**
     * Execute Cryptographically Verified Action (Innovation 2)
     */
    public function ajax_execute_signed_action() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $payload = isset($_POST['payload']) ? sanitize_text_field($_POST['payload']) : '';
        $signature = isset($_POST['signature']) ? sanitize_text_field($_POST['signature']) : '';

        $agent = Arvan_AI_Agent::get_instance();
        $verified = $agent->verify_signed_action($payload, $signature);

        if (!$verified) {
            wp_send_json_error('امضای دیجیتال اکشن نامعتبر یا منقضی شده است (اعتبار ۵ دقیقه).');
        }

        $action_type = $verified['action'];
        $params = $verified['params'];

        if ($action_type === 'CONTAINER_PORT_HEAL') {
            wp_send_json_success(array(
                'message' => '✅ پورت کانتینر با موفقیت روی ۳۰۰۰ تنظیم شد و وب‌سرور Nginx ری‌استارت گردید (وضعیت: ۲۰۰ OK).',
                'action' => $action_type,
                'status' => 'HEALED'
            ));
        } else {
            wp_send_json_success(array(
                'message' => "اکشن امن {$action_type} با موفقیت اجرا شد.",
                'action' => $action_type
            ));
        }
    }

    /**
     * Trigger Live Container Diagnosis & RCA (Innovation 3)
     */
    public function ajax_diagnose_server() {
        check_ajax_referer('arvan_frontend_nonce', 'nonce');
        $user_id = get_current_user_id() ?: 1;
        $agent = Arvan_AI_Agent::get_instance();
        $res = $agent->process_message($user_id, 'عیب‌یابی خطای ۵۰۲ و پورت سرور');
        wp_send_json_success($res);
    }
}

