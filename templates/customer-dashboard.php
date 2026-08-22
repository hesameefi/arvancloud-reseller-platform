<script>
(function() {
    var savedTheme = localStorage.getItem('arvan_theme') || '<?php echo esc_js($global_theme); ?>';
    document.documentElement.setAttribute('data-theme', savedTheme);
    if (document.body) {
        document.body.setAttribute('data-theme', savedTheme);
        document.body.classList.remove('arvan-dark-theme', 'arvan-light-theme');
        document.body.classList.add('arvan-' + savedTheme + '-theme');
    }
})();
</script>
<?php
if (!defined('ABSPATH')) {
    exit;
}

$balance_num = floatval($wallet['balance']);
$is_low_balance = ($balance_num < 50000);

// Calculate Total Hourly Burn & Runway
$total_hourly_burn = 0;
$active_servers_count = 0;
foreach ($user_servers as $srv) {
    if ($srv['status'] === 'ACTIVE') {
        $total_hourly_burn += floatval($srv['hourly_customer_price']);
        $active_servers_count++;
    }
}

$runway_hours = ($total_hourly_burn > 0) ? round($balance_num / $total_hourly_burn) : 999;
$runway_days = round($runway_hours / 24, 1);

// Telemetry & Rate Limiting Status
$telemetry = Arvan_Rate_Limiter::get_instance()->get_telemetry();
$global_theme = get_option('arvan_global_theme', 'dark');
?>

<div class="ar-saas-layout" id="arvan_dashboard_app" data-theme="<?php echo esc_attr($global_theme); ?>">
    <!-- Right Sidebar (سایدبار عمودی راست با طراحی داشبوردی سرخ‌آب) -->
    <aside class="ar-sidebar" id="ar_sidebar">
        <div class="ar-sidebar-brand">
            <div class="ar-brand-logo">☁️</div>
            <div class="ar-brand-text">
                <h2>سرخ‌آب کلاود</h2>
                <span>ریسلر اختصاصی ابر آروان</span>
            </div>
        </div>

        <!-- Wallet Quick Widget in Sidebar -->
        <div class="ar-sidebar-wallet-card">
            <div class="ar-sidebar-wallet-label">موجودی کیف پول پیش‌پرداخت</div>
            <div class="ar-sidebar-wallet-val" id="ar_sidebar_balance"><?php echo number_format($balance_num); ?> <small>تومان</small></div>
            <button type="button" class="ar-btn ar-btn-primary ar-btn-block" id="ar_sidebar_open_deposit" onclick="document.getElementById('ar_open_deposit_modal').click();">
                ➕ افزایش اعتبار
            </button>
        </div>

        <!-- Vertical Dashboard Menu Items -->
        <nav class="ar-sidebar-nav">
            <button type="button" class="ar-sidebar-nav-item active" data-tab="tab_my_servers">
                <span class="ar-nav-icon">🖥️</span>
                <span class="ar-nav-title">سرورهای ابری من</span>
                <span class="ar-nav-badge"><?php echo count($user_servers); ?></span>
            </button>
            <button type="button" class="ar-sidebar-nav-item" data-tab="tab_create_server">
                <span class="ar-nav-icon">🚀</span>
                <span class="ar-nav-title">راه‌اندازی ابرک جدید</span>
                <span class="ar-nav-badge pulse">آنلاین</span>
            </button>
            <button type="button" class="ar-sidebar-nav-item" data-tab="tab_cdn_storage">
                <span class="ar-nav-icon">🌐</span>
                <span class="ar-nav-title">CDN و آبجکت استوریج S3</span>
            </button>
            <button type="button" class="ar-sidebar-nav-item" data-tab="tab_ai_advisor">
                <span class="ar-nav-icon">🤖</span>
                <span class="ar-nav-title">مشاور هوش مصنوعی پلن</span>
                <span class="ar-nav-badge ai">AI</span>
            </button>
            <button type="button" class="ar-sidebar-nav-item" data-tab="tab_wallet_history">
                <span class="ar-nav-icon">📜</span>
                <span class="ar-nav-title">دفتر کل مالی و تراکنش‌ها</span>
            </button>
            <button type="button" class="ar-sidebar-nav-item" data-tab="tab_rate_limit_health">
                <span class="ar-nav-icon">⚡</span>
                <span class="ar-nav-title">وضعیت ریت‌لیمیت و سلامت API</span>
            </button>
        </nav>

        <!-- Sidebar Footer Status -->
        <div class="ar-sidebar-footer">
            <div class="ar-region-status">
                <span class="ar-pulse-dot"></span> دیتاسنترهای تهران و تبریز لایو
            </div>
            <button type="button" class="ar-theme-toggle-sidebar" id="ar_theme_toggle_btn">
                ☀️ تم روشن
            </button>
        </div>
    </aside>

    <!-- Main Viewport Area (سمت چپ) -->
    <div class="ar-main-viewport">
        <!-- Top Navbar -->
        <header class="ar-top-navbar">
            <div class="ar-top-breadcrumb">
                <button type="button" class="ar-mobile-toggle" id="ar_mobile_toggle" title="منو">☰</button>
                <span style="color: var(--ar-text-muted);">کنسول ابری /</span>
                <strong id="ar_active_page_title" style="color: var(--ar-text-main);">سرورهای ابری من</strong>
            </div>

            <div class="ar-top-actions">
                <div class="ar-burn-rate-badge">
                    <span>⚡ مصرف ساعتی: <strong><?php echo number_format($total_hourly_burn); ?> تومان/ساعت</strong></span>
                </div>
                <button type="button" class="ar-btn ar-btn-primary" id="ar_open_deposit_modal" style="padding: 7px 14px; font-size: 13px;">
                    💳 شارژ حساب
                </button>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="ar-main-content">
            <!-- Low Balance Warning Banner -->
            <?php if ($is_low_balance): ?>
                <div style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--ar-radius-md); padding: 14px 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="font-size: 22px;">⚠️</span>
                        <div>
                            <div style="font-size: 14px; font-weight: 800; color: #fbbf24;">هشدار کسری موجودی حساب ابری</div>
                            <div style="font-size: 12.5px; color: var(--ar-text-secondary);">موجودی شما کمتر از ۵۰,۰۰۰ تومان است. جهت جلوگیری از خاموش شدن خودکار سرورها، حساب خود را شارژ نمایید.</div>
                        </div>
                    </div>
                    <button type="button" class="ar-btn ar-btn-primary" style="padding: 7px 16px; font-size: 13px;" onclick="document.getElementById('ar_open_deposit_modal').click();">
                        شارژ فوری کیف پول
                    </button>
                </div>
            <?php endif; ?>

            <!-- Bento Metrics Dashboard (4 KPI Tiles) -->
            <section class="ar-bento-grid">
                <div class="ar-bento-card">
                    <div class="ar-bento-card-top">
                        <div class="ar-bento-title">ابرک‌های ابری فعال</div>
                        <div class="ar-bento-icon" style="color: #38bdf8; background: rgba(56, 189, 248, 0.12); border-color: rgba(56, 189, 248, 0.25);">🖥️</div>
                    </div>
                    <div class="ar-bento-value"><?php echo $active_servers_count; ?> <span style="font-size: 13px; font-weight: 500; color: var(--ar-text-muted);">از <?php echo count($user_servers); ?> سرور</span></div>
                    <div class="ar-bento-sub" style="color: #34d399;">● وضعیت پایدار و آنلاین</div>
                </div>

                <div class="ar-bento-card">
                    <div class="ar-bento-card-top">
                        <div class="ar-bento-title">مصرف ساعتی (Burn Rate)</div>
                        <div class="ar-bento-icon" style="color: #f43f5e; background: rgba(244, 63, 94, 0.12); border-color: rgba(244, 63, 94, 0.25);">⚡</div>
                    </div>
                    <div class="ar-bento-value"><?php echo number_format($total_hourly_burn); ?> <span style="font-size: 12px; font-weight: 500; color: var(--ar-text-muted);">تومان/ساعت</span></div>
                    <div class="ar-bento-sub">کسر خودکار مصرف لحظه‌ای</div>
                </div>

                <div class="ar-bento-card">
                    <div class="ar-bento-card-top">
                        <div class="ar-bento-title">موجودی در دسترس</div>
                        <div class="ar-bento-icon" style="color: #10b981; background: rgba(16, 185, 129, 0.12); border-color: rgba(16, 185, 129, 0.25);">💳</div>
                    </div>
                    <div class="ar-bento-value" style="color: #00d2d2;"><?php echo number_format($balance_num); ?> <span style="font-size: 12px; font-weight: 500; color: var(--ar-text-muted);">تومان</span></div>
                    <div class="ar-bento-sub">کیف پول پیش‌پرداخت</div>
                </div>

                <div class="ar-bento-card">
                    <div class="ar-bento-card-top">
                        <div class="ar-bento-title">مدت بقای سرویس (Runway)</div>
                        <div class="ar-bento-icon" style="color: #a855f7; background: rgba(168, 85, 247, 0.12); border-color: rgba(168, 85, 247, 0.25);">⏳</div>
                    </div>
                    <div class="ar-bento-value"><?php echo ($total_hourly_burn > 0) ? ($runway_hours . ' <small style="font-size: 13px; font-weight: 500;">ساعت</small>') : 'نامحدود'; ?></div>
                    <div class="ar-bento-sub"><?php echo ($total_hourly_burn > 0) ? ("تقریباً {$runway_days} روز کاری") : 'بدون مصرف فعال'; ?></div>
                </div>
            </section>

            <!-- Tab 1: My Cloud Servers -->
            <div id="tab_my_servers" class="ar-tab-content active">
                <!-- View Controls Toolbar -->
                <div class="ar-view-toolbar">
                    <div class="ar-search-box">
                        <input type="text" id="ar_server_search" class="ar-search-input" placeholder="🔍 جستجوی سریع بر اساس نام سرور، IP، یا دیتاسنتر...">
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="ar-view-toggle">
                            <button type="button" class="ar-view-btn active" id="ar_btn_grid_view" onclick="switchServerView('grid');">⊞ نمای شبکه‌ای</button>
                            <button type="button" class="ar-view-btn" id="ar_btn_table_view" onclick="switchServerView('table');">☰ نمای جدولی</button>
                        </div>

                        <button type="button" class="ar-btn ar-btn-primary ar-tab-btn" data-tab="tab_create_server" style="padding: 8px 16px;">
                            ➕ سرور جدید
                        </button>
                    </div>
                </div>

                <?php if (empty($user_servers)): ?>
                    <div class="ar-form-card" style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 54px; margin-bottom: 16px;">🌩️</div>
                        <h3 style="font-size: 20px; font-weight: 800; margin: 0 0 10px 0;">شما هنوز هیچ سرور ابری راه‌اندازی نکرده‌اید</h3>
                        <p style="color: var(--ar-text-secondary); margin-bottom: 25px; font-size: 14px;">با چند کلیک، ابرک اختصاصی با دیسک پرسرعت NVMe و ترافیک نامحدود بسازید.</p>
                        <button type="button" class="ar-btn ar-btn-primary ar-tab-btn" data-tab="tab_create_server" style="padding: 12px 24px;">
                            🚀 ایجاد اولین سرور ابری
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Grid View -->
                    <div id="ar_servers_grid_container" class="ar-servers-grid">
                        <?php foreach ($user_servers as $srv): 
                            $is_active = ($srv['status'] === 'ACTIVE');
                            $specs = json_decode($srv['specs'], true) ?: array();
                            
                            $flag = '🇮🇷';
                            $loc_title = 'تهران - عارف';
                            if ($srv['region'] === 'ir-tbz-dc1') { $flag = '🇮🇷'; $loc_title = 'تبریز - شهریار'; }
                            elseif ($srv['region'] === 'nl-ams-1') { $flag = '🇳🇱'; $loc_title = 'هلند - آمستردام'; }
                        ?>
                            <div class="ar-server-card" data-server-name="<?php echo esc_attr(strtolower($srv['name'])); ?>" data-server-ip="<?php echo esc_attr($srv['ip_address']); ?>" data-server-region="<?php echo esc_attr(strtolower($srv['region'])); ?>">
                                <div>
                                    <div class="ar-server-card-top">
                                        <div class="ar-server-title">
                                            <h3><?php echo esc_html($srv['name']); ?></h3>
                                            <div class="ar-server-region"><?php echo $flag . ' ' . esc_html($loc_title); ?> (<code><?php echo esc_html($srv['region']); ?></code>)</div>
                                        </div>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span class="ar-status-badge <?php echo $is_active ? 'active' : 'suspended'; ?>">
                                                <?php if ($is_active): ?>
                                                    <span class="ar-pulse-dot" style="width: 6px; height: 6px;"></span> روشن و فعال
                                                <?php else: ?>
                                                    ⏹ خاموش (Suspend)
                                                <?php endif; ?>
                                            </span>
                                            <button type="button" class="ar-server-del-icon-btn ar-action-btn" data-action="terminate" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" title="حذف دائمی سرور">
                                                🗑️
                                            </button>
                                        </div>
                                    </div>

                                    <div class="ar-server-meta-grid">
                                        <div class="ar-server-meta-item">
                                            <span>آدرس IP عمومی</span>
                                            <strong dir="ltr" style="user-select: all;"><?php echo esc_html($srv['ip_address']); ?></strong>
                                        </div>
                                        <div class="ar-server-meta-item">
                                            <span>پلن سخت‌افزاری</span>
                                            <strong><?php echo esc_html($srv['flavor_name']); ?></strong>
                                        </div>
                                        <div class="ar-server-meta-item">
                                            <span>تعرفه ساعتی</span>
                                            <strong style="color: var(--ar-primary);"><?php echo number_format($srv['hourly_customer_price']); ?> تومان/ساعت</strong>
                                        </div>
                                        <div class="ar-server-meta-item">
                                            <span>تخمین ماهانه</span>
                                            <strong><?php echo number_format($srv['hourly_customer_price'] * 720); ?> تومان/ماه</strong>
                                        </div>
                                    </div>
                                </div>

                                <div class="ar-server-actions">
                                    <?php if ($is_active): ?>
                                        <button type="button" class="ar-btn ar-btn-danger ar-action-btn" data-action="power_off" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" title="خاموش کردن سرور">
                                            ⏹ خاموش
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="ar-btn ar-btn-primary ar-action-btn" data-action="power_on" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" title="روشن کردن سرور">
                                            ▶ روشن
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" class="ar-btn ar-btn-secondary ar-upgrade-srv-btn" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" data-flavor="<?php echo esc_attr($srv['flavor_id']); ?>" data-price="<?php echo esc_attr($srv['hourly_customer_price']); ?>" title="تغییر اندازه و ارتقای سخت‌افزاری">
                                        ⚡ ارتقا
                                    </button>

                                    <button type="button" class="ar-btn ar-btn-secondary ar-edit-srv-btn" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" title="ویرایش نام سرور">
                                        ✏️ ویرایش
                                    </button>

                                    <button type="button" class="ar-btn ar-btn-secondary ar-console-btn" data-name="<?php echo esc_attr($srv['name']); ?>" data-ip="<?php echo esc_attr($srv['ip_address']); ?>" title="وب کنسول ترمینال">
                                        💻 کنسول
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Table View -->
                    <div id="ar_servers_table_container" class="ar-servers-table-wrap" style="display: none;">
                        <table class="ar-data-table">
                            <thead>
                                <tr>
                                    <th>نام ابرک</th>
                                    <th>موقعیت دیتاسنتر</th>
                                    <th>آدرس IP عمومی</th>
                                    <th>پلن سخت‌افزار</th>
                                    <th>تعرفه ساعتی</th>
                                    <th>وضعیت</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_servers as $srv): 
                                    $is_active = ($srv['status'] === 'ACTIVE');
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($srv['name']); ?></strong></td>
                                        <td><code><?php echo esc_html($srv['region']); ?></code></td>
                                        <td dir="ltr"><code><?php echo esc_html($srv['ip_address']); ?></code></td>
                                        <td><?php echo esc_html($srv['flavor_name']); ?></td>
                                        <td style="color: var(--ar-primary); font-weight: 700;"><?php echo number_format($srv['hourly_customer_price']); ?> تومان</td>
                                        <td>
                                            <span class="ar-status-badge <?php echo $is_active ? 'active' : 'suspended'; ?>">
                                                <?php echo $is_active ? 'روشن' : 'خاموش'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div style="display: flex; gap: 6px;">
                                                <?php if ($is_active): ?>
                                                    <button type="button" class="ar-btn ar-btn-danger ar-action-btn" style="padding: 4px 8px; font-size: 11px;" data-action="power_off" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>">خاموش</button>
                                                <?php else: ?>
                                                    <button type="button" class="ar-btn ar-btn-primary ar-action-btn" style="padding: 4px 8px; font-size: 11px;" data-action="power_on" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>">روشن</button>
                                                <?php endif; ?>
                                                <button type="button" class="ar-btn ar-btn-secondary ar-upgrade-srv-btn" style="padding: 4px 8px; font-size: 11px;" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" data-flavor="<?php echo esc_attr($srv['flavor_id']); ?>" data-price="<?php echo esc_attr($srv['hourly_customer_price']); ?>" title="تغییر پلن سخت‌افزاری">⚡ ارتقا</button>
                                                <button type="button" class="ar-btn ar-btn-secondary ar-edit-srv-btn" style="padding: 4px 8px; font-size: 11px;" data-id="<?php echo esc_attr($srv['resource_id']); ?>" data-name="<?php echo esc_attr($srv['name']); ?>" title="ویرایش نام">✏️ ویرایش</button>
                                                <button type="button" class="ar-btn ar-btn-secondary ar-console-btn" style="padding: 4px 8px; font-size: 11px;" data-name="<?php echo esc_attr($srv['name']); ?>" data-ip="<?php echo esc_attr($srv['ip_address']); ?>">کنسول</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab 2: Create New Cloud Server -->
            <div id="tab_create_server" class="ar-tab-content">
                <div class="ar-form-card">
                    <div style="border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 16px; margin-bottom: 24px;">
                        <h2 style="margin: 0; font-size: 20px; font-weight: 900; color: var(--ar-text-main);">🚀 راه‌اندازی و استقرار ابرک جدید (Instant Cloud Deployer)</h2>
                        <p style="margin: 6px 0 0 0; color: var(--ar-text-secondary); font-size: 13.5px;">با تکمیل فرم زیر، سرور ابری شما در کمتر از ۳۰ ثانیه پیکربندی و آماده استفاده می‌شود.</p>
                    </div>

                    <form id="ar_create_server_form">
                        <!-- AI Recommendation Banner Hook -->
                        <div id="ar_ai_recommendation_box" style="display: none; background: rgba(0, 186, 186, 0.12); border: 1px solid rgba(0, 186, 186, 0.35); border-radius: var(--ar-radius-md); padding: 14px 18px; margin-bottom: 24px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 22px;">🤖</span>
                                <div>
                                    <div style="font-weight: 800; color: var(--ar-primary); font-size: 13.5px;">پیشنهاد هوش مصنوعی انتخاب پلن اعمال گردید:</div>
                                    <div id="ar_ai_text" style="font-size: 12.5px; color: var(--ar-text-main); margin-top: 2px;"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 1: Region -->
                        <div class="ar-form-group">
                            <label class="ar-form-label">۱. موقعیت جغرافیایی دیتاسنتر (Region):</label>
                            <div class="ar-plan-cards">
                                <label class="ar-plan-card" style="cursor: pointer;">
                                    <input type="radio" name="region" value="ir-thr-at1" checked style="display: none;">
                                    <div style="font-size: 24px; margin-bottom: 6px;">🇮🇷</div>
                                    <div style="font-weight: 800; font-size: 14px;">تهران - عارف (at1)</div>
                                    <div style="font-size: 11.5px; color: var(--ar-text-muted); margin-top: 4px;">کمترین پینگ داخلی (۵ms)</div>
                                </label>

                                <label class="ar-plan-card" style="cursor: pointer;">
                                    <input type="radio" name="region" value="ir-tbz-dc1" style="display: none;">
                                    <div style="font-size: 24px; margin-bottom: 6px;">🇮🇷</div>
                                    <div style="font-weight: 800; font-size: 14px;">تبریز - شهریار (dc1)</div>
                                    <div style="font-size: 11.5px; color: var(--ar-text-muted); margin-top: 4px;">پایداری بالا و بک‌آپ</div>
                                </label>

                                <label class="ar-plan-card" style="cursor: pointer;">
                                    <input type="radio" name="region" value="nl-ams-1" style="display: none;">
                                    <div style="font-size: 24px; margin-bottom: 6px;">🇳🇱</div>
                                    <div style="font-weight: 800; font-size: 14px;">هلند - آمستردام (ams1)</div>
                                    <div style="font-size: 11.5px; color: var(--ar-text-muted); margin-top: 4px;">پورت ۱۰ گیگابیت بین‌الملل</div>
                                </label>
                            </div>
                        </div>

                        <!-- Step 2: Flavor Selection -->
                        <div class="ar-form-group">
                            <label class="ar-form-label" for="ar_flavor_select">۲. پلن سخت‌افزاری و منابع پردازشی (Hardware Flavor):</label>
                            <select id="ar_flavor_select" class="ar-select" required>
                                <?php 
                                if (!empty($flavors) && is_array($flavors)) {
                                    foreach ($flavors as $flv): 
                                        if (!is_array($flv) || !isset($flv['id'])) continue;
                                        $reseller_margin = floatval(get_option('arvan_reseller_margin', 20));
                                        $base_h = isset($flv['hourly_price']) ? floatval($flv['hourly_price']) : 450;
                                        $cust_hourly = round($base_h * (1 + ($reseller_margin / 100)));
                                        $cust_monthly = $cust_hourly * 720;
                                        $flv_name = isset($flv['name']) ? $flv['name'] : $flv['id'];
                                ?>
                                    <option value="<?php echo esc_attr($flv['id']); ?>" data-name="<?php echo esc_attr($flv_name); ?>" data-hourly="<?php echo esc_attr($cust_hourly); ?>" data-monthly="<?php echo esc_attr($cust_monthly); ?>">
                                        <?php echo esc_html($flv_name); ?> — [ <?php echo number_format($cust_hourly); ?> تومان/ساعت | ~<?php echo number_format($cust_monthly); ?> تومان/ماه ]
                                    </option>
                                <?php 
                                    endforeach; 
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Step 3: Operating System Image -->
                        <div class="ar-form-group">
                            <label class="ar-form-label" for="ar_image_select">۳. سیستم‌عامل و پلتفرم اجرایی (OS Image):</label>
                            <select id="ar_image_select" class="ar-select" required>
                                <?php 
                                if (!empty($images) && is_array($images)) {
                                    foreach ($images as $img): 
                                        if (!is_array($img) || !isset($img['id'])) continue;
                                        $img_name = isset($img['name']) ? $img['name'] : $img['id'];
                                        $img_os = isset($img['os']) ? $img['os'] : 'Linux';
                                ?>
                                    <option value="<?php echo esc_attr($img['id']); ?>">
                                        <?php echo esc_html($img_name); ?> (<?php echo esc_html($img_os); ?>)
                                    </option>
                                <?php 
                                    endforeach; 
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Step 4: Server Name & SSH -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                            <div class="ar-form-group" style="margin-bottom: 0;">
                                <label class="ar-form-label" for="ar_server_name_input">۴. نام اختصاصی سرور (Hostname):</label>
                                <input type="text" id="ar_server_name_input" class="ar-input" value="web-server-node-1" placeholder="my-app-server" required>
                            </div>

                            <div class="ar-form-group" style="margin-bottom: 0;">
                                <label class="ar-form-label" for="ar_ssh_key_input">۵. کلید عمومی SSH (اختیاری):</label>
                                <input type="text" id="ar_ssh_key_input" class="ar-input" placeholder="ssh-rsa AAAA..." dir="ltr">
                            </div>
                        </div>

                        <!-- Pricing Summary Box -->
                        <div class="ar-pricing-summary-box">
                            <div>
                                <div style="font-size: 13px; color: var(--ar-text-secondary); font-weight: 600;">تعرفه مصرف ساعتی (کسر خودکار از کیف پول):</div>
                                <div style="font-size: 26px; font-weight: 900; color: var(--ar-primary); margin-top: 4px;">
                                    <span id="ar_create_hourly_price">--</span> <small style="font-size: 13px; font-weight: normal; color: var(--ar-text-muted);">تومان / ساعت</small>
                                </div>
                                <div style="font-size: 12.5px; color: var(--ar-text-muted); margin-top: 2px;">
                                    معادل تقریبی ماهانه: <strong id="ar_create_monthly_price" style="color: var(--ar-text-main);">--</strong> تومان
                                </div>
                            </div>

                            <button type="submit" id="ar_btn_submit_create" class="ar-btn ar-btn-primary" style="padding: 14px 28px; font-size: 15px; font-weight: 900;">
                                ⚡ راه‌اندازی و تحویل آنی ابرک
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tab 3: CDN & S3 Storage -->
            <div id="tab_cdn_storage" class="ar-tab-content">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                    <!-- CDN Management -->
                    <div class="ar-form-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <span style="font-size: 32px;">🌐</span>
                            <div>
                                <h3 style="margin: 0; font-size: 17px; font-weight: 800;">شبکه توزیع محتوا (CDN & WAF)</h3>
                                <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--ar-text-muted);">شتاب‌دهی وب‌سایت، کش ابری و محافظت ضد DDoS</p>
                            </div>
                        </div>

                        <div class="ar-form-group">
                            <label class="ar-form-label">افزودن دامنه جدید به شبکه CDN آروان:</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="ar_cdn_domain_input" class="ar-input" placeholder="example.com" dir="ltr">
                                <button type="button" class="ar-btn ar-btn-primary" onclick="alert('✅ دامنه به شبکه توزیع محتوا افزوده شد. رکوردهای NS را تنظیم نمایید.');">
                                    ثبت دامنه
                                </button>
                            </div>
                        </div>

                        <div style="background: var(--ar-bg-surface); border-radius: 10px; padding: 14px; margin-top: 16px;">
                            <div style="font-size: 12.5px; font-weight: 800; margin-bottom: 8px;">دامنه‌های فعال در CDN:</div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; border-bottom: 1px dashed var(--ar-border-subtle); padding-bottom: 8px;">
                                <span dir="ltr"><strong>shop4bit.ir</strong></span>
                                <span style="color: var(--ar-status-success); font-weight: 700;">● فعال (پروکسی روشن)</span>
                            </div>
                            <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; padding-top: 8px;">
                                <span dir="ltr"><strong>api.starcoach.cloud</strong></span>
                                <span style="color: var(--ar-status-success); font-weight: 700;">● فعال (SSL رایگان)</span>
                            </div>
                        </div>
                    </div>

                    <!-- S3 Object Storage -->
                    <div class="ar-form-card">
                        <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px;">
                            <span style="font-size: 32px;">🗄️</span>
                            <div>
                                <h3 style="margin: 0; font-size: 17px; font-weight: 800;">فضای ذخیره‌سازی ابری (S3 Storage)</h3>
                                <p style="margin: 2px 0 0 0; font-size: 12px; color: var(--ar-text-muted);">سازگار با پروتکل Amazon S3، باکت‌های عمومی و خصوصی</p>
                            </div>
                        </div>

                        <div class="ar-form-group">
                            <label class="ar-form-label">ایجاد باکت جدید (New Bucket):</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" id="ar_s3_bucket_input" class="ar-input" placeholder="my-app-uploads" dir="ltr">
                                <button type="button" id="ar_btn_create_bucket_submit" class="ar-btn ar-btn-primary">
                                    ایجاد باکت
                                </button>
                            </div>
                        </div>

                        <div style="background: var(--ar-bg-surface); border-radius: 10px; padding: 14px; margin-top: 16px;">
                            <div style="font-size: 12.5px; font-weight: 800; margin-bottom: 8px;">باکت‌های ذخیره‌سازی ابری:</div>
                            <div id="ar_s3_buckets_list">
                                <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; border-bottom: 1px dashed var(--ar-border-subtle); padding-bottom: 8px;">
                                    <span dir="ltr">🗂️ <strong>media-assets-prod</strong> (ir-thr-at1)</span>
                                    <span style="color: var(--ar-status-success); font-weight: 700;">● فعال (S3 Endpoint)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 4: AI Agentic Copilot & Autonomous Deployer -->
            <div id="tab_ai_advisor" class="ar-tab-content">
                <div class="ar-form-card" style="padding: 24px; position: relative;">
                    
                    <!-- Header -->
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 18px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <div class="ar-ai-avatar">🤖</div>
                            <div>
                                <h2 style="margin: 0; font-size: 18px; font-weight: 900; color: var(--ar-text-main);">دستیار هوشمند و عامل استقرار ابری (ArvanCloud AI Copilot)</h2>
                                <p style="margin: 3px 0 0 0; font-size: 12.5px; color: var(--ar-text-secondary);">متصل به پایگاه دانش RAG مستندات آروان، تحلیل خودکار نیاز و استقرار مستقیم سرور</p>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="ar-status-badge active" style="font-size: 11.5px;">
                                <span class="ar-pulse-dot" style="width: 6px; height: 6px;"></span> هوش مصنوعی آماده اقدام
                            </span>
                            <button type="button" class="ar-btn ar-btn-secondary" id="ar_ai_clear_chat" style="padding: 6px 12px; font-size: 12px;" title="شروع مکالمه جدید">
                                🔄 گفتگوی جدید
                            </button>
                        </div>
                    </div>

                    <!-- Quick Scenario Chips -->
                    <div class="ar-ai-quick-chips">
                        <span style="font-size: 12px; color: var(--ar-text-muted); font-weight: 700; display: flex; align-items: center; gap: 4px;">⚡ سناریوهای سریع:</span>
                        <button type="button" class="ar-ai-chip" data-prompt="من یک فروشگاه ووکامرس پربازدید با ۵۰۰۰ بازدید روزانه دارم، چه سروری با چه کانفیگی پیشنهاد میدی؟">🛒 فروشگاه ووکامرس</button>
                        <button type="button" class="ar-ai-chip" data-prompt="برای اجرای بک‌اند لاراول و پردازش صف‌های داکر به سرور با رم ۴ گیگ نیاز دارم، چه پلنی مناسبه؟">⚙️ بک‌اند لاراول و داکر</button>
                        <button type="button" class="ar-ai-chip" data-prompt="یک دیتابیس اختصاصی سنگین PostgreSQL با کش ردیس دارم، بهترین پلن با دیسک NVMe چیه؟">🐘 دیتابیس سنگین PostgreSQL</button>
                        <button type="button" class="ar-ai-chip" data-prompt="یک سرور خارج در هلند برای اتصال به APIهای خارجی و وب‌کراولر بدون محدودیت میخوام.">🇳🇱 سرور هلند (آمستردام)</button>
                        <button type="button" class="ar-ai-chip" data-prompt="برای سیستمی با مقیاس ۱ میلیون کاربر همزمان چه معماری و پلنی رو پیشنهاد می‌کنی؟">🚀 مقیاس ۱ میلیون کاربر</button>
                    </div>

                    <!-- Chat Messages Container -->
                    <div class="ar-ai-chat-box" id="ar_ai_chat_container">
                        <!-- Welcome message from AI -->
                        <div class="ar-ai-msg ar-ai-msg-bot">
                            <div class="ar-ai-msg-avatar">🤖</div>
                            <div class="ar-ai-msg-body">
                                <div class="ar-ai-msg-content">
                                    سلام! من **دستیار هوش مصنوعی عامل‌گرا (AI Copilot) سرخ‌آب کلاود** هستم. ☁️✨<br><br>
                                    من به تمام مستندات فنی، پلن‌های سخت‌افزاری، قیمت‌ها و دیتاسنترهای ابر آروان دسترسی دارم. کافیست سناریو یا هدف کاری‌تان را بگویید تا بهترین پلن را با محاسبه دقیق هزینه به شما پیشنهاد دهم و در صورت تمایل، <strong>مستقیماً همین سرور را برایتان بسازم و در داشبوردتان فعال کنم!</strong><br><br>
                                    <em>مثلاً بفرمایید: «من برای فلان کار سرور میخوام، تو چی پیشنهاد میدی؟»</em>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chat Input Form -->
                    <form id="ar_ai_chat_form" class="ar-ai-input-wrap">
                        <input type="text" id="ar_ai_input_text" class="ar-ai-input" placeholder="نیاز یا پروژه خود را بنویسید (مثلاً: یک سرور برای لاراول و دیتابیس میخوام)..." autocomplete="off" required>
                        <button type="submit" id="ar_ai_send_btn" class="ar-btn ar-btn-primary" style="padding: 0 24px; font-size: 14px; font-weight: 800; border-radius: var(--ar-radius-md); height: 50px;">
                            <span>ارسال</span> 🚀
                        </button>
                    </form>

                </div>
            </div>

            <!-- Tab 5: Wallet History & Ledger -->
            <div id="tab_wallet_history" class="ar-tab-content">
                <div class="ar-form-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 16px; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <h2 style="margin: 0; font-size: 18px; font-weight: 900; color: var(--ar-text-main);">📜 دفتر کل مالی و ریز تراکنش‌های مصرف ابری (Ledger)</h2>
                            <p style="margin: 4px 0 0 0; color: var(--ar-text-secondary); font-size: 12.5px;">ثبت دقیق هر ثانیه و ساعت کسر مصرف و افزایش اعتبار با کد پیگیری یکتا.</p>
                        </div>

                        <button type="button" class="ar-btn ar-btn-secondary" style="padding: 7px 14px; font-size: 12.5px;" onclick="exportTableToCSV('arvan-ledger-report.csv');">
                            📥 خروجی اکسل (CSV)
                        </button>
                    </div>

                    <?php if (empty($ledger_history)): ?>
                        <div style="text-align: center; padding: 40px; color: var(--ar-text-muted);">
                            هنوز هیچ تراکنشی ثبت نشده است.
                        </div>
                    <?php else: ?>
                        <div class="ar-servers-table-wrap">
                            <table class="ar-data-table" id="ar_ledger_table">
                                <thead>
                                    <tr>
                                        <th>شناسه تراکنش</th>
                                        <th>نوع عملیات</th>
                                        <th>مبلغ (تومان)</th>
                                        <th>موجودی بعد از تراکنش</th>
                                        <th>شرح و منبع تراکنش</th>
                                        <th>تاریخ و زمان</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($ledger_history as $tx): 
                                        $is_dep = ($tx['transaction_type'] === 'DEPOSIT');
                                        $amt_formatted = number_format(abs($tx['amount']));
                                    ?>
                                        <tr>
                                            <td dir="ltr"><code>#<?php echo esc_html($tx['reference_id'] ?: $tx['id']); ?></code></td>
                                            <td>
                                                <span class="ar-status-badge <?php echo $is_dep ? 'active' : 'suspended'; ?>" style="font-size: 11px;">
                                                    <?php echo $is_dep ? '➕ شارژ حساب' : '⚡ کسر مصرف ابری'; ?>
                                                </span>
                                            </td>
                                            <td style="font-weight: 800; color: <?php echo $is_dep ? 'var(--ar-status-success)' : '#f43f5e'; ?>;">
                                                <?php echo ($is_dep ? '+' : '-') . $amt_formatted; ?> تومان
                                            </td>
                                            <td style="font-weight: 700; color: var(--ar-text-main);">
                                                <?php echo number_format($tx['balance_after']); ?> تومان
                                            </td>
                                            <td style="font-size: 12.5px; color: var(--ar-text-secondary);"><?php echo esc_html($tx['description']); ?></td>
                                            <td dir="ltr" style="font-size: 12px; color: var(--ar-text-muted);"><?php echo esc_html($tx['created_at']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Tab 6: Rate Limiting & API Quota Telemetry -->
            <div id="tab_rate_limit_health" class="ar-tab-content">
                <div class="ar-form-card">
                    <div style="border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 16px; margin-bottom: 24px;">
                        <h2 style="margin: 0; font-size: 20px; font-weight: 900; color: var(--ar-text-main);">⚡ پایش سلامت وب‌سرویس، ریت‌لیمیت و امنیت کلیدها</h2>
                        <p style="margin: 6px 0 0 0; color: var(--ar-text-secondary); font-size: 13.5px;">سیستم هوشمند کنترل بار (Rate Limiting) و رمزنگاری کلیدهای اختصاصی با الگوریتم نظامی AES-256.</p>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin-bottom: 24px;">
                        <div class="ar-bento-card">
                            <div class="ar-bento-icon" style="color: #10b981;">🛡️</div>
                            <div class="ar-bento-data">
                                <div class="ar-bento-title">امنیت کلید در دیتابیس</div>
                                <div class="ar-bento-value" style="color: #10b981; font-size: 18px;">AES-256-CBC</div>
                                <div class="ar-bento-sub">رمزنگاری با سالت‌های وردپرس</div>
                            </div>
                        </div>

                        <div class="ar-bento-card">
                            <div class="ar-bento-icon" style="color: #6366f1;">⏱️</div>
                            <div class="ar-bento-data">
                                <div class="ar-bento-title">سقف مجاز درخواست‌ها</div>
                                <div class="ar-bento-value"><?php echo $telemetry['max_rpm']; ?> <small style="font-size: 12px; color: var(--ar-text-muted);">req/min</small></div>
                                <div class="ar-bento-sub">محدودیت پنجره لغزان ۶۰ ثانیه‌ای</div>
                            </div>
                        </div>

                        <div class="ar-bento-card">
                            <div class="ar-bento-icon" style="color: #38bdf8;">📊</div>
                            <div class="ar-bento-data">
                                <div class="ar-bento-title">تراکنش‌های امروز</div>
                                <div class="ar-bento-value"><?php echo number_format($telemetry['today_requests']); ?></div>
                                <div class="ar-bento-sub">مجموع کل: <?php echo number_format($telemetry['total_requests']); ?> ریکوئست</div>
                            </div>
                        </div>

                        <div class="ar-bento-card">
                            <div class="ar-bento-icon" style="color: #f43f5e;">⚠️</div>
                            <div class="ar-bento-data">
                                <div class="ar-bento-title">خطای ریت‌لیمیت (429)</div>
                                <div class="ar-bento-value" style="color: <?php echo $telemetry['total_throttled'] > 0 ? '#ef4444' : '#10b981'; ?>;">
                                    <?php echo $telemetry['total_throttled']; ?>
                                </div>
                                <div class="ar-bento-sub">شاخص سلامت: <?php echo $telemetry['health_score']; ?>٪ (<?php echo esc_html($telemetry['status']); ?>)</div>
                            </div>
                        </div>
                    </div>

                    <div style="background: var(--ar-bg-surface); border-radius: 12px; padding: 18px; border: 1px solid var(--ar-border-subtle);">
                        <h4 style="margin: 0 0 10px 0; font-size: 14px; font-weight: 800;">🔒 نحوه عملکرد سیستم امنیتی و کنترل ترافیک:</h4>
                        <ul style="margin: 0; padding-right: 20px; font-size: 13px; color: var(--ar-text-secondary); line-height: 1.8;">
                            <li>تمام کلیدهای API کاربر ماشین قبل از ذخیره در دیتابیس با کلیدهای یکتای سرور به صورت برگشت‌پذیر با الگوریتم AES-256 رمزنگاری می‌شوند تا حتی در صورت نفوذ به دیتابیس، کلیدها محافظت‌شده باشند.</li>
                            <li>موتور Rate Limiter تعداد درخواست‌های ارسال‌شده را در پنجره‌های لغزان ۶۰ ثانیه‌ای ردیابی می‌کند و از بروز خطای مسدودی IP و دریافت پاسخ 429 جلوگیری می‌نماید.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Deposit Modal -->
    <div class="ar-modal-backdrop" id="ar_deposit_modal">
        <div class="ar-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 900; color: var(--ar-text-main);">💳 افزایش اعتبار کیف پول ابری</h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 24px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="ar_deposit_form">
                <div class="ar-form-group">
                    <label class="ar-form-label" for="ar_deposit_amount">مبلغ شارژ (تومان):</label>
                    <input type="number" id="ar_deposit_amount" class="ar-input" min="10000" step="5000" value="100000" required>
                </div>

                <div style="display: flex; gap: 8px; margin-bottom: 24px; flex-wrap: wrap;">
                    <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12px;" onclick="document.getElementById('ar_deposit_amount').value=50000;">۵۰,۰۰۰ تومان</button>
                    <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12px;" onclick="document.getElementById('ar_deposit_amount').value=100000;">۱۰۰,۰۰۰ تومان</button>
                    <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12px;" onclick="document.getElementById('ar_deposit_amount').value=250000;">۲۵۰,۰۰۰ تومان</button>
                    <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12px;" onclick="document.getElementById('ar_deposit_amount').value=500000;">۵۰۰,۰۰۰ تومان</button>
                </div>

                <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%; padding: 14px; font-size: 15px; font-weight: 800;">
                    💳 پرداخت آنلاین و افزایش آنی موجودی
                </button>
            </form>
        </div>
    </div>

    
    <!-- Upgrade / Resize Server Modal -->
    <div class="ar-modal-backdrop" id="ar_upgrade_srv_modal">
        <div class="ar-modal-box" style="max-width: 540px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--ar-text-main);">
                    ⚡ ارتقا و تغییر پلن سخت‌افزاری ابرک
                </h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 22px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="ar_upgrade_srv_form">
                <input type="hidden" id="ar_upgrade_server_id" value="">
                
                <div style="background: var(--ar-bg-surface); border: 1px solid var(--ar-border-card); border-radius: 12px; padding: 14px; margin-bottom: 18px;">
                    <div style="font-size: 12px; color: var(--ar-text-muted); margin-bottom: 4px;">سرور انتخابی:</div>
                    <strong id="ar_upgrade_server_name_display" style="font-size: 15px; color: var(--ar-primary);"></strong>
                </div>

                <div class="ar-form-group">
                    <label class="ar-form-label" for="ar_upgrade_flavor_select">انتخاب پلن سخت‌افزاری جدید:</label>
                    <select id="ar_upgrade_flavor_select" class="ar-select" required>
                        <?php foreach ($flavors as $flv): 
                            $flv_price = round($flv['hourly_price'] * (1 + ($margin / 100)));
                        ?>
                            <option value="<?php echo esc_attr($flv['id']); ?>" data-price="<?php echo $flv_price; ?>" data-cpu="<?php echo $flv['cpu']; ?>" data-ram="<?php echo $flv['memory']; ?>" data-disk="<?php echo $flv['disk']; ?>">
                                <?php echo esc_html($flv['name']); ?> (<?php echo number_format($flv_price); ?> تومان/ساعت)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="background: rgba(0, 186, 186, 0.08); border: 1px solid rgba(0, 186, 186, 0.25); border-radius: 10px; padding: 12px 14px; margin-bottom: 20px; font-size: 12.5px; color: var(--ar-text-sec); line-height: 1.6;">
                    💡 <strong>توجه فنی:</strong> هایپروایزر ابری پس از تایید، دیسک و پردازنده‌های جدید را تخصیص داده و سرور را به صورت ایمن راه‌اندازی مجدد (Reboot) می‌نماید.
                </div>

                <button type="submit" id="ar_btn_submit_upgrade" class="ar-btn ar-btn-primary" style="width: 100%; padding: 13px; font-size: 14.5px; font-weight: 800;">
                    🚀 تایید و ارتقای آنی سرور
                </button>
            </form>
        </div>
    </div>

    <!-- Rename Server Modal -->
    <div class="ar-modal-backdrop" id="ar_rename_srv_modal">
        <div class="ar-modal-box" style="max-width: 460px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid var(--ar-border-subtle); padding-bottom: 12px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800; color: var(--ar-text-main);">
                    ✏️ ویرایش نام و هاست‌نیم سرور
                </h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 22px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="ar_rename_srv_form">
                <input type="hidden" id="ar_rename_server_id" value="">
                
                <div class="ar-form-group">
                    <label class="ar-form-label" for="ar_rename_server_input">نام جدید سرور:</label>
                    <input type="text" id="ar_rename_server_input" class="ar-input" placeholder="مثال: web-production-master" required>
                </div>

                <button type="submit" id="ar_btn_submit_rename" class="ar-btn ar-btn-primary" style="width: 100%; padding: 12px; font-size: 14px; font-weight: 800;">
                    💾 ذخیره نام جدید
                </button>
            </form>
        </div>
    </div>

    <!-- Web Console Modal -->
    <div class="ar-modal-backdrop" id="ar_console_modal">
        <div class="ar-modal-box" style="max-width: 680px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800;">💻 ترمینال و شبیه‌ساز کنسول: <span id="ar_console_server_name" style="color: var(--ar-primary);"></span></h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 24px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <div class="ar-terminal-screen">
                <div style="color: #94a3b8;">[ArvanCloud Hypervisor Engine] Connected to node: ir-thr-at1</div>
                <div style="color: #94a3b8;">Instance state: RUNNING | Ping: 8ms | CPU Load: 4%</div>
                <div style="color: #94a3b8;">Kernel: Linux 6.8.0-134-generic x86_64</div>
                <div style="color: #38bdf8; margin-top: 8px;">root@arvan-node:~# uname -a</div>
                <div>Linux enterprise-node 6.8.0-134-generic #134-Ubuntu SMP x86_64 GNU/Linux</div>
                <div style="color: #38bdf8; margin-top: 8px;">root@arvan-node:~# ip addr show eth0</div>
                <div>inet <span id="ar_console_ip"></span>/24 brd 185.143.233.255 scope global eth0</div>
                <div style="color: #fbbf24; margin-top: 8px;">root@arvan-node:~# _</div>
            </div>

            <button type="button" class="ar-btn ar-btn-secondary ar-close-modal" style="width: 100%;">
                بستن پنجره کنسول
            </button>
        </div>
    </div>
</div>

<script>
function switchServerView(mode) {
    var grid = document.getElementById('ar_servers_grid_container');
    var table = document.getElementById('ar_servers_table_container');
    var btnGrid = document.getElementById('ar_btn_grid_view');
    var btnTable = document.getElementById('ar_btn_table_view');

    if (mode === 'grid') {
        if (grid) grid.style.display = 'grid';
        if (table) table.style.display = 'none';
        if (btnGrid) btnGrid.classList.add('active');
        if (btnTable) btnTable.classList.remove('active');
    } else {
        if (grid) grid.style.display = 'none';
        if (table) table.style.display = 'block';
        if (btnGrid) btnGrid.classList.remove('active');
        if (btnTable) btnTable.classList.add('active');
    }
}

// Live Search Filter for Servers
document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('ar_server_search');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            var q = e.target.value.toLowerCase().trim();
            var cards = document.querySelectorAll('.ar-server-card');
            cards.forEach(function(card) {
                var name = card.getAttribute('data-server-name') || '';
                var ip = card.getAttribute('data-server-ip') || '';
                var reg = card.getAttribute('data-server-region') || '';
                if (name.includes(q) || ip.includes(q) || reg.includes(q)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
});

function applyAiRecommendation(flavorId, title, flavorName) {
    var sel = document.getElementById('ar_flavor_select');
    if (sel) {
        sel.value = flavorId;
        sel.dispatchEvent(new Event('change'));
    }
    var aiBox = document.getElementById('ar_ai_recommendation_box');
    var aiText = document.getElementById('ar_ai_text');
    if (aiBox && aiText) {
        aiText.innerHTML = 'برای سناریوی <strong>' + title + '</strong>، پلن بهینه <strong>' + flavorName + '</strong> با سرعت دیسک NVMe انتخاب گردید.';
        aiBox.style.display = 'block';
    }
}

function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#ar_ledger_table tr");
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        for (var j = 0; j < cols.length; j++) 
            row.push('"' + cols[j].innerText.replace(/"/g, '""') + '"');
        csv.push(row.join(","));
    }
    var csvFile = new Blob(["\uFEFF" + csv.join("\n")], {type: "text/csv;charset=utf-8;"});
    var downloadLink = document.createElement("a");
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}
</script>
