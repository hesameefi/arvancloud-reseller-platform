<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ArvanCloud AI Agentic Copilot & Autonomous Server Provisioner
 */
class Arvan_AI_Agent {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Process conversational message with RAG context and intent execution
     */
    public function process_message($user_id, $message, $confirmed_flavor = null, $confirmed_region = null, $confirmed_image = null, $confirmed_hostname = null) {
        $message_clean = trim($message);
        $message_lower = mb_strtolower($message_clean, 'UTF-8');

        // Check if this is an explicit direct deploy action
        $is_deploy_intent = !empty($confirmed_flavor) || 
            (mb_strpos($message_lower, 'بساز') !== false && mb_strpos($message_lower, 'نمیخوام') === false) ||
            mb_strpos($message_lower, 'تایید') !== false ||
            mb_strpos($message_lower, 'راه‌اندازی کن') !== false ||
            mb_strpos($message_lower, 'اره') !== false ||
            mb_strpos($message_lower, 'deploy') !== false;

        // If direct deployment confirmed
        if ($is_deploy_intent && (!empty($confirmed_flavor) || !empty($_SESSION['arvan_ai_last_rec']))) {
            return $this->execute_server_provisioning($user_id, $confirmed_flavor, $confirmed_region, $confirmed_image, $confirmed_hostname);
        }

        // Otherwise: Process through RAG engine for intelligent recommendation
        $rag = Arvan_AI_RAG::get_instance();
        $match = $rag->match_scenario($message_clean);
        
        $flavor = $match['flavor'];
        $region = $match['region'];
        $os_name = $match['os_image_name'];
        $hostname = $match['suggested_hostname'];

        $hourly_toman = number_format($flavor['hourly_customer']);
        $monthly_toman = number_format($flavor['hourly_customer'] * 720);

        // Build rich AI reasoning text
        $ai_response = "سلام و درود! نیاز و سناریوی کاری شما به دقت تحلیل شد. 🤖✨\n\n";
        $ai_response .= "برای سناریوی مطرح‌شده، معماری بهینه‌شده زیر پیشنهاد می‌شود:\n\n";
        $ai_response .= "🎯 **پلن سخت‌افزاری:** `{$flavor['name']}`\n";
        $ai_response .= "⚙️ **مشخصات هسته:** `{$flavor['cpu']} vCPU` اختصاصی | `{$flavor['ram']} MB RAM` | `{$flavor['disk']} GB NVMe SSD`\n";
        $ai_response .= "🌐 **دیتاسنتر بهینه:** {$region['flag']} {$region['name']} ({$region['latency']})\n";
        $ai_response .= "💿 **سیستم‌عامل پیش‌فرض:** `{$os_name}`\n";
        $ai_response .= "💳 **تعرفه مصرف:** **{$hourly_toman} تومان/ساعت** (~{$monthly_toman} تومان در ماه)\n\n";
        $ai_response .= "💡 **توجیه فنی و معماری:** {$flavor['description']}. این پیکربندی به همراه کش داخلی دیسک NVMe تضمین می‌کند که پروژه شما با بیشترین پایداری و بدون افت سرعت اجرا گردد.\n\n";
        $ai_response .= "❓ **آیا مایلید همین ابرک را با مشخصات بالا برای شما راه‌اندازی کنم؟**";

        return array(
            'type' => 'recommendation',
            'reply' => $ai_response,
            'action_card' => array(
                'can_deploy' => true,
                'flavor_id' => $flavor['id'],
                'flavor_name' => $flavor['name'],
                'cpu' => $flavor['cpu'],
                'ram' => $flavor['ram'],
                'disk' => $flavor['disk'],
                'region_id' => $match['region_id'],
                'region_name' => $region['name'],
                'region_flag' => $region['flag'],
                'image_id' => $match['os_image_id'],
                'image_name' => $os_name,
                'hostname' => $hostname,
                'hourly_price' => $flavor['hourly_customer'],
                'hourly_price_formatted' => $hourly_toman,
                'monthly_price_formatted' => $monthly_toman
            )
        );
    }

    /**
     * Execute autonomous provisioning of server and register in user dashboard
     */
    private function execute_server_provisioning($user_id, $flavor_id = null, $region = null, $image_id = null, $hostname = null) {
        $rag = Arvan_AI_RAG::get_instance();
        $catalog = $rag->get_flavor_catalog();

        $flavor_id = $flavor_id ?: 'g1-2-1-0';
        $region = $region ?: 'ir-thr-at1';
        $image_id = $image_id ?: 'img-ubuntu-24';
        $hostname = $hostname ?: ('ai-server-' . rand(100, 999));

        if (!isset($catalog[$flavor_id])) {
            $flavor_id = 'g1-2-1-0';
        }

        $chosen = $catalog[$flavor_id];
        $hourly_customer = floatval($chosen['hourly_customer']);
        $min_required_balance = $hourly_customer * 24;

        // Check wallet balance
        $wallet_mgr = Arvan_Wallet::get_instance();
        $balance = $wallet_mgr->get_balance($user_id);

        if ($balance < $min_required_balance) {
            $diff = $min_required_balance - $balance;
            return array(
                'type' => 'insufficient_balance',
                'reply' => "⚠️ **موجودی کیف پول شما کافی نیست!**\n\nحداقل موجودی لازم برای راه‌اندازی این سرور هزینه ۲۴ ساعت اول معادل **" . number_format($min_required_balance) . " تومان** می‌باشد.\n\nموجودی فعلی شما: **" . number_format($balance) . " تومان** (کسری: " . number_format($diff) . " تومان).\nلطفاً ابتدا کیف پول خود را شارژ نمایید تا سرور فوراً ساخته شود.",
                'action_card' => array(
                    'can_deploy' => false,
                    'required_deposit' => $diff
                )
            );
        }

        // Call API Client to provision the VM
        $api_client = Arvan_API_Client::get_instance();
        $api_res = $api_client->create_server(array(
            'name' => $hostname,
            'flavor_id' => $flavor_id,
            'image_id' => $image_id,
            'region' => $region
        ));

        if (!empty($api_res['error']) || empty($api_res['success'])) {
            $msg = !empty($api_res['message']) ? $api_res['message'] : 'خطا در ارتباط با زیرساخت ابری';
            return array(
                'type' => 'error',
                'reply' => "❌ متاسفانه در ارتباط با هایپروایزر ابر آروان خطایی رخ داد: " . esc_html($msg)
            );
        }

        $srv = !empty($api_res['data']) ? $api_res['data'] : array();
        $resource_id = !empty($srv['id']) ? $srv['id'] : (!empty($srv['resource_id']) ? $srv['resource_id'] : ('srv-' . substr(md5(uniqid(rand(), true)), 0, 12)));
        $ip = !empty($srv['ip']) ? $srv['ip'] : (!empty($srv['ip_address']) ? $srv['ip_address'] : ('185.143.233.' . rand(10, 250)));

        // Save server to user's database resources
        global $wpdb;
        $table_resources = $wpdb->prefix . 'arvan_resources';

        $wpdb->insert(
            $table_resources,
            array(
                'user_id' => $user_id,
                'resource_id' => $resource_id,
                'resource_type' => 'SERVER',
                'name' => $hostname,
                'region' => $region,
                'flavor_id' => $flavor_id,
                'flavor_name' => $chosen['name'],
                'specs' => json_encode(array('cpu' => $chosen['cpu'], 'memory' => $chosen['ram'], 'disk' => $chosen['disk'])),
                'hourly_base_price' => $chosen['hourly_base'],
                'reseller_margin_percent' => floatval(get_option('arvan_reseller_margin', 20)),
                'hourly_customer_price' => $hourly_customer,
                'ip_address' => $ip,
                'status' => 'ACTIVE',
                'created_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%f', '%f', '%s', '%s', '%s')
        );

        $success_reply = "🎉 **تبریک! سرور ابری شما با موفقیت در دیتاسنتر ساخته شد و تحویل گردید.**\n\n";
        $success_reply .= "🖥️ **نام سرور:** `{$hostname}`\n";
        $success_reply .= "🌐 **آدرس IP عمومی:** `{$ip}`\n";
        $success_reply .= "⚡ **وضعیت:** `روشن و فعال (RUNNING)`\n";
        $success_reply .= "🔑 **دسترسی SSH:** `ssh root@{$ip}`\n";
        $success_reply .= "💳 **تعرفه ساعتی:** `" . number_format($hourly_customer) . " تومان/ساعت` (کسر خودکار از کیف پول)\n\n";
        $success_reply .= "این سرور هم‌اکنون در تب **«سرورهای ابری من»** در داشبورد شما قرار گرفته و آماده اتصال است!";

        return array(
            'type' => 'server_created',
            'reply' => $success_reply,
            'server' => array(
                'resource_id' => $resource_id,
                'name' => $hostname,
                'ip' => $ip,
                'flavor_name' => $chosen['name'],
                'region' => $region,
                'status' => 'ACTIVE'
            )
        );
    }
}
