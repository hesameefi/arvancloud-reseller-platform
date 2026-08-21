<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * ArvanCloud AI RAG Knowledge Base & Semantic Search Engine
 * 
 * Provides structured technical documentation, hardware matrices, use-case mapping,
 * pricing calculations, and 1M-user architectural intelligence.
 */
class Arvan_AI_RAG {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {}

    /**
     * Retrieve complete hardware flavor catalog with live margin calculations
     */
    public function get_flavor_catalog() {
        $margin = floatval(get_option('arvan_reseller_margin', 20));
        
        return array(
            'g1-1-1-0' => array(
                'id' => 'g1-1-1-0',
                'name' => 'General Mini 1GB',
                'category' => 'general',
                'cpu' => 1,
                'ram' => 1024, // MB
                'disk' => 25, // GB NVMe
                'bandwidth' => '1 Gbps',
                'hourly_base' => 450,
                'hourly_customer' => round(450 * (1 + ($margin / 100))),
                'description' => 'مناسب سایت‌های شخصی، ربات‌های سبک، وبلاگ‌های سبک و پروژه‌های تستی',
                'tags' => array('سبک', 'ارزان', 'ربات', 'وبلاگ', 'پروژه تستی', 'تست', 'mini', '1gb')
            ),
            'g1-2-1-0' => array(
                'id' => 'g1-2-1-0',
                'name' => 'General 2GB',
                'category' => 'general',
                'cpu' => 2,
                'ram' => 2048,
                'disk' => 40,
                'bandwidth' => '1 Gbps',
                'hourly_base' => 890,
                'hourly_customer' => round(890 * (1 + ($margin / 100))),
                'description' => 'مناسب فروشگاه ووکامرس متوسط، وب‌سرویس‌های وردپرسی و پروژه‌های با ترافیک تا ۵,۰۰۰ بازدید در روز',
                'tags' => array('ووکامرس', 'فروشگاه', 'وردپرس', 'woocommerce', 'wordpress', '2gb', 'عمومی')
            ),
            'g1-4-2-0' => array(
                'id' => 'g1-4-2-0',
                'name' => 'Pro Standard 4GB',
                'category' => 'pro',
                'cpu' => 2,
                'ram' => 4096,
                'disk' => 60,
                'bandwidth' => '2.5 Gbps',
                'hourly_base' => 1650,
                'hourly_customer' => round(1650 * (1 + ($margin / 100))),
                'description' => 'مناسب بک‌اند لاراول، جنگو، Node.js، پردازش صف‌های کاری (Queue) و کانتینرهای داکر',
                'tags' => array('لاراول', 'laravel', 'django', 'جنگو', 'nodejs', 'api', 'بک‌اند', 'داکر', 'docker', '4gb', 'pro')
            ),
            'g1-8-4-0' => array(
                'id' => 'g1-8-4-0',
                'name' => 'Enterprise Power 8GB',
                'category' => 'enterprise',
                'cpu' => 4,
                'ram' => 8192,
                'disk' => 100,
                'bandwidth' => '5 Gbps',
                'hourly_base' => 3100,
                'hourly_customer' => round(3100 * (1 + ($margin / 100))),
                'description' => 'مناسب دیتابیس‌های پرتراکنش MySQL و PostgreSQL، کلاسترهای ردیس و وب‌سایت‌های با ترافیک بیش از ۵۰,۰۰۰ کاربر همزمان',
                'tags' => array('دیتابیس', 'database', 'postgres', 'postgresql', 'mysql', 'redis', 'ردیس', 'ترافیک سنگین', '8gb', 'enterprise')
            ),
            'c1-16-8-0' => array(
                'id' => 'c1-16-8-0',
                'name' => 'Compute Master 16GB',
                'category' => 'compute',
                'cpu' => 8,
                'ram' => 16384,
                'disk' => 180,
                'bandwidth' => '10 Gbps',
                'hourly_base' => 5900,
                'hourly_customer' => round(5900 * (1 + ($margin / 100))),
                'description' => 'مناسب پردازش‌های سنگین CPU-Intensive، هوش مصنوعی، کامپایل نرم‌افزار، سرورهای تحلیل داده و بیگ‌دیتا',
                'tags' => array('هوش مصنوعی', 'ai', 'پردازش سنگین', 'ml', 'machine learning', 'بیگ دیتا', '16gb', 'compute')
            ),
            'm1-32-4-0' => array(
                'id' => 'm1-32-4-0',
                'name' => 'Memory Monster 32GB',
                'category' => 'memory',
                'cpu' => 8,
                'ram' => 32768,
                'disk' => 250,
                'bandwidth' => '10 Gbps',
                'hourly_base' => 9800,
                'hourly_customer' => round(9800 * (1 + ($margin / 100))),
                'description' => 'مناسب سرورهای با مقیاس ۱ میلیون کاربر، ElasticSearch، کلاسترهای Kafka و دیتابیس‌های In-Memory',
                'tags' => array('۱ میلیون کاربر', 'مقیاس بزرگ', 'elasticsearch', 'kafka', 'رم بالا', '32gb', 'memory')
            )
        );
    }

    /**
     * Retrieve Datacenter Regions Knowledge
     */
    public function get_regions_catalog() {
        return array(
            'ir-thr-at1' => array(
                'id' => 'ir-thr-at1',
                'name' => 'تهران - دیتاسنتر عارف (at1)',
                'country' => 'ایران',
                'flag' => '🇮🇷',
                'latency' => '۵ تا ۱۰ میلی‌ثانیه برای کاربران داخلی',
                'best_for' => 'سایت‌ها و اپلیکیشن‌های با مخاطب اصلی داخل کشور و فروشگاه‌های اینترنتی'
            ),
            'ir-tbz-dc1' => array(
                'id' => 'ir-tbz-dc1',
                'name' => 'تبریز - دیتاسنتر شهریار (dc1)',
                'country' => 'ایران',
                'flag' => '🇮🇷',
                'latency' => '۱۰ تا ۲۰ میلی‌ثانیه',
                'best_for' => 'سرورهای بک‌آپ ثانویه، کلاسترهای توزیع‌شده و مناطق شمال غرب'
            ),
            'nl-ams-1' => array(
                'id' => 'nl-ams-1',
                'name' => 'هلند - دیتاسنتر آمستردام (ams1)',
                'country' => 'هلند',
                'flag' => '🇳🇱',
                'latency' => 'پورت ۱۰ گیگابیت بین‌الملل بدون فیلترینگ',
                'best_for' => 'سرویس‌های بین‌المللی، گیت‌هاب رانر، پروکسی، وب‌کراولرها و ارتباط با APIهای خارجی مثل OpenAI'
            )
        );
    }

    /**
     * Semantic Matcher: Find the best flavor, region, and OS for a user query
     */
    public function match_scenario($user_query) {
        $query_lower = mb_strtolower($user_query, 'UTF-8');
        $catalog = $this->get_flavor_catalog();
        
        $scores = array();
        foreach ($catalog as $flavor_id => $data) {
            $score = 0;
            foreach ($data['tags'] as $tag) {
                if (mb_strpos($query_lower, mb_strtolower($tag, 'UTF-8')) !== false) {
                    $score += 3;
                }
            }

            // Keyword boosts
            if (mb_strpos($query_lower, 'ووکامرس') !== false || mb_strpos($query_lower, 'فروشگاه') !== false) {
                if ($flavor_id === 'g1-2-1-0' || $flavor_id === 'g1-4-2-0') $score += 5;
            }
            if (mb_strpos($query_lower, 'لاراول') !== false || mb_strpos($query_lower, 'api') !== false || mb_strpos($query_lower, 'داکر') !== false) {
                if ($flavor_id === 'g1-4-2-0') $score += 6;
            }
            if (mb_strpos($query_lower, 'دیتابیس') !== false || mb_strpos($query_lower, 'پستگرس') !== false || mb_strpos($query_lower, 'mysql') !== false) {
                if ($flavor_id === 'g1-8-4-0') $score += 6;
            }
            if (mb_strpos($query_lower, 'هوش مصنوعی') !== false || mb_strpos($query_lower, 'پایتون') !== false || mb_strpos($query_lower, 'پردازش') !== false) {
                if ($flavor_id === 'c1-16-8-0') $score += 7;
            }
            if (mb_strpos($query_lower, '۱ میلیون') !== false || mb_strpos($query_lower, 'یک میلیون') !== false || mb_strpos($query_lower, 'ترافیک فوق سنگین') !== false) {
                if ($flavor_id === 'm1-32-4-0') $score += 10;
            }

            // Fallback for cheap / mini
            if (mb_strpos($query_lower, 'ارزان') !== false || mb_strpos($query_lower, 'تستی') !== false || mb_strpos($query_lower, 'کمترین هزینه') !== false) {
                if ($flavor_id === 'g1-1-1-0') $score += 8;
            }

            $scores[$flavor_id] = $score;
        }

        arsort($scores);
        $best_flavor_id = key($scores);
        $best_score = current($scores);

        // Default fallback if no specific keywords matched
        if ($best_score <= 0) {
            $best_flavor_id = 'g1-2-1-0'; // General 2GB as safe default
        }

        // Detect Preferred Region
        $region = 'ir-thr-at1';
        if (mb_strpos($query_lower, 'هلند') !== false || mb_strpos($query_lower, 'خارج') !== false || mb_strpos($query_lower, 'پروکسی') !== false || mb_strpos($query_lower, 'openai') !== false) {
            $region = 'nl-ams-1';
        } elseif (mb_strpos($query_lower, 'تبریز') !== false || mb_strpos($query_lower, 'بک آپ') !== false) {
            $region = 'ir-tbz-dc1';
        }

        // Detect OS Image
        $os_image = 'img-ubuntu-24';
        if (mb_strpos($query_lower, 'ویندوز') !== false || mb_strpos($query_lower, 'windows') !== false) {
            $os_image = 'img-win-2022';
        } elseif (mb_strpos($query_lower, 'دبیان') !== false || mb_strpos($query_lower, 'debian') !== false) {
            $os_image = 'img-debian-12';
        }

        $chosen = $catalog[$best_flavor_id];
        $regions = $this->get_regions_catalog();

        return array(
            'flavor' => $chosen,
            'region' => $regions[$region],
            'region_id' => $region,
            'os_image_id' => $os_image,
            'os_image_name' => ($os_image === 'img-win-2022' ? 'Windows Server 2022' : 'Ubuntu 24.04 LTS (Noble Numbat)'),
            'suggested_hostname' => 'node-' . preg_replace('/[^a-z0-9]/', '', substr($chosen['name'], 0, 8)) . '-' . rand(10, 99)
        );
    }
}
