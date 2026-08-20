<?php
/**
 * Plugin Name: ArvanCloud Reseller Pro | افزونه ریسلری ابر آروان
 * Plugin URI: https://arvan.shop4bit.ir
 * Description: افزونه مستقل و بدون وابستگی (Zero-Dependency) برای فروش و مدیریت منابع ابری آروان‌کلاد، کیف پول پیش‌پرداخت، کران‌جاب ساعتی مصرف و دیزاین سیستم سرخ‌آب.
 * Version: 1.0.0
 * Author: StarCoach Hackathon Team
 * Author URI: https://arvan.shop4bit.ir
 * Text Domain: arvan-reseller
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

define('ARVAN_RESELLER_VERSION', '1.0.0');
define('ARVAN_RESELLER_PATH', plugin_dir_path(__FILE__));
define('ARVAN_RESELLER_URL', plugin_dir_url(__FILE__));

// Autoload Includes
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-db.php';
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-api-client.php';
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-wallet.php';
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-cron.php';
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-admin.php';
require_once ARVAN_RESELLER_PATH . 'includes/class-arvan-frontend.php';

/**
 * Main Plugin Bootstrap Class
 */
class Arvan_Reseller_Plugin {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        add_action('plugins_loaded', array($this, 'init'));

        // Favicon injector for 0 console errors
        add_action('wp_head', array($this, 'inject_favicon'));
        add_action('admin_head', array($this, 'inject_favicon'));
    }

    public function init() {
        Arvan_DB::get_instance();
        Arvan_API_Client::get_instance();
        Arvan_Wallet::get_instance();
        Arvan_Cron::get_instance();
        Arvan_Admin::get_instance();
        Arvan_Frontend::get_instance();
    }

    public function inject_favicon() {
        $icon_url = ARVAN_RESELLER_URL . 'assets/images/favicon.svg';
        echo '<link rel="icon" type="image/svg+xml" href="' . esc_url($icon_url) . '">' . "\n";
        echo '<link rel="shortcut icon" href="' . esc_url($icon_url) . '">' . "\n";
    }

    public function activate() {
        Arvan_DB::create_tables();
        Arvan_Cron::schedule_events();
        flush_rewrite_rules();
    }

    public function deactivate() {
        Arvan_Cron::clear_events();
        flush_rewrite_rules();
    }
}

// Initialize Plugin
Arvan_Reseller_Plugin::get_instance();
