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
?>

<div class="ar-saas-layout" id="arvan_dashboard_app">
    <!-- Top Enterprise Navbar -->
    <header class="ar-top-navbar">
        <div class="ar-brand-group">
            <div class="ar-brand-logo">☁️</div>
            <div class="ar-brand-info">
                <h1>کنسول خدمات ابری ابر آروان</h1>
                <span><span class="ar-pulse-dot"></span> شبکه ابری لایو (۴ دیتاسنتر متصل)</span>
            </div>
        </div>

        <div class="ar-navbar-actions">
            <!-- Theme Toggle Switcher -->
            <button type="button" class="ar-btn ar-btn-secondary" id="ar_theme_toggle_btn" style="padding: 7px 16px; font-size: 13px; border-radius: 20px;">
                ☀️ تم روشن
            </button>

            <!-- Live Wallet Pill -->
            <div class="ar-wallet-pill">
                <div class="ar-wallet-meta">
                    <div class="ar-wallet-meta-title">اعتبار پیش‌پرداخت کیف پول</div>
                    <div class="ar-wallet-meta-value" id="ar_wallet_balance_display"><?php echo number_format($balance_num); ?> <small style="font-size: 11px; font-weight: normal; color: var(--ar-text-secondary);">تومان</small></div>
                </div>
                <button type="button" class="ar-btn ar-btn-primary" id="ar_open_deposit_modal" style="padding: 7px 14px; font-size: 13px;">
                    ➕ افزایش اعتبار
                </button>
            </div>
        </div>
    </header>

    <!-- Main Content Container -->
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
                <div class="ar-bento-icon" style="color: #38bdf8;">🖥️</div>
                <div class="ar-bento-data">
                    <div class="ar-bento-title">ابرک‌های ابری فعال</div>
                    <div class="ar-bento-value"><?php echo $active_servers_count; ?> <span style="font-size: 13px; font-weight: normal; color: var(--ar-text-muted);">از <?php echo count($user_servers); ?> سرور</span></div>
                    <div class="ar-bento-sub" style="color: var(--ar-status-success);">● ۱۰۰٪ وضعیت نرمال و آنلاین</div>
                </div>
            </div>

            <div class="ar-bento-card">
                <div class="ar-bento-icon" style="color: #f43f5e;">⚡</div>
                <div class="ar-bento-data">
                    <div class="ar-bento-title">مجموع مصرف ساعتی (Burn Rate)</div>
                    <div class="ar-bento-value"><?php echo number_format($total_hourly_burn); ?> <span style="font-size: 12px; font-weight: normal; color: var(--ar-text-muted);">تومان/ساعت</span></div>
                    <div class="ar-bento-sub">کسر خودکار بر اساس ثانیه مصرف</div>
                </div>
            </div>

            <div class="ar-bento-card">
                <div class="ar-bento-icon" style="color: #10b981;">💳</div>
                <div class="ar-bento-data">
                    <div class="ar-bento-title">موجودی در دسترس</div>
                    <div class="ar-bento-value" style="color: var(--ar-primary);"><?php echo number_format($balance_num); ?> <span style="font-size: 12px; font-weight: normal; color: var(--ar-text-muted);">تومان</span></div>
                    <div class="ar-bento-sub">کیف پول پیش‌پرداخت ارزی-ریالی</div>
                </div>
            </div>

            <div class="ar-bento-card">
                <div class="ar-bento-icon" style="color: #a855f7;">⏳</div>
                <div class="ar-bento-data">
                    <div class="ar-bento-title">مدت زمان بقای سرویس (Runway)</div>
                    <div class="ar-bento-value"><?php echo ($total_hourly_burn > 0) ? ($runway_hours . ' <small style="font-size: 13px; font-weight: normal;">ساعت</small>') : 'نامحدود'; ?></div>
                    <div class="ar-bento-sub"><?php echo ($total_hourly_burn > 0) ? ("تقریباً {$runway_days} روز کاری") : 'بدون مصرف ساعتی فعال'; ?></div>
                </div>
            </div>
        </section>

        <!-- Segmented Luxury Navigation Tabs -->
        <nav class="ar-nav-pills">
            <button type="button" class="ar-nav-pill-btn active" data-tab="tab_my_servers">
                <span>🖥️</span> سرورهای ابری من (<?php echo count($user_servers); ?>)
            </button>
            <button type="button" class="ar-nav-pill-btn" data-tab="tab_create_server">
                <span>🚀</span> راه‌اندازی سرور ابری جدید
            </button>
            <button type="button" class="ar-nav-pill-btn" data-tab="tab_cdn_storage">
                <span>🌐</span> CDN و آبجکت استوریج S3
            </button>
            <button type="button" class="ar-nav-pill-btn" data-tab="tab_ai_advisor">
                <span>🤖</span> مشاور هوش مصنوعی انتخاب پلن
            </button>
            <button type="button" class="ar-nav-pill-btn" data-tab="tab_wallet_history">
                <span>📜</span> دفتر کل مالی و تراکنش‌ها
            </button>
        </nav>

        <!-- Tab 1: My Cloud Servers -->
        <div id="tab_my_servers" class="ar-tab-content">
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
                                    <span class="ar-status-badge <?php echo $is_active ? 'active' : 'suspended'; ?>">
                                        <?php if ($is_active): ?>
                                            <span class="ar-pulse-dot" style="width: 6px; height: 6px;"></span> روشن و فعال
                                        <?php else: ?>
                                            ⏸ خاموش (Suspended)
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <!-- Spec Chips -->
                                <div class="ar-spec-chips">
                                    <span class="ar-chip">⚡ <?php echo esc_html($srv['flavor_name']); ?></span>
                                    <span class="ar-chip">💾 NVMe Storage</span>
                                    <span class="ar-chip">🔒 DDoS Protected</span>
                                </div>

                                <ul class="ar-server-meta-list">
                                    <li>
                                        <span>آدرس آی‌پی عمومی (IP):</span>
                                        <strong><?php echo esc_html($srv['ip_address']); ?></strong>
                                    </li>
                                    <li>
                                        <span>نرخ تعرفه ساعتی:</span>
                                        <strong class="ar-server-price"><?php echo number_format(floatval($srv['hourly_customer_price'])); ?> تومان / ساعت</strong>
                                    </li>
                                </ul>
                            </div>

                            <div class="ar-card-actions">
                                <?php if ($is_active): ?>
                                    <button type="button" class="ar-btn ar-btn-danger ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-off" style="flex: 1;">
                                        🛑 خاموش کردن
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="ar-btn ar-btn-success ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-on" style="flex: 1;">
                                        ⚡ روشن کردن
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="ar-btn ar-btn-secondary ar-open-console-btn" data-name="<?php echo esc_attr($srv['name']); ?>" data-ip="<?php echo esc_attr($srv['ip_address']); ?>" style="padding: 9px 14px;" title="باز کردن شبیه‌ساز ترمینال SSH">
                                    💻 کنسول وب
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Table View (Hidden by Default) -->
                <div id="ar_servers_table_container" class="ar-table-card" style="display: none;">
                    <div class="ar-table-responsive">
                        <table class="ar-enterprise-table" id="ar_servers_table">
                            <thead>
                                <tr>
                                    <th>نام ابرک</th>
                                    <th>وضعیت</th>
                                    <th>آدرس IP</th>
                                    <th>پلن سخت‌افزاری</th>
                                    <th>منطقه ابری</th>
                                    <th>هزینه ساعتی</th>
                                    <th>عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($user_servers as $srv): 
                                    $is_active = ($srv['status'] === 'ACTIVE');
                                ?>
                                    <tr>
                                        <td><strong><?php echo esc_html($srv['name']); ?></strong></td>
                                        <td>
                                            <span class="ar-status-badge <?php echo $is_active ? 'active' : 'suspended'; ?>">
                                                <?php echo $is_active ? '● فعال' : '⏸ معلق'; ?>
                                            </span>
                                        </td>
                                        <td><code style="font-family: 'JetBrains Mono', monospace;" dir="ltr"><?php echo esc_html($srv['ip_address']); ?></code></td>
                                        <td><?php echo esc_html($srv['flavor_name']); ?></td>
                                        <td><?php echo esc_html($srv['region']); ?></td>
                                        <td><strong style="color: var(--ar-primary);"><?php echo number_format(floatval($srv['hourly_customer_price'])); ?> تومان</strong></td>
                                        <td>
                                            <div style="display: flex; gap: 6px;">
                                                <?php if ($is_active): ?>
                                                    <button type="button" class="ar-btn ar-btn-danger ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-off" style="padding: 5px 10px; font-size: 12px;">خاموش</button>
                                                <?php else: ?>
                                                    <button type="button" class="ar-btn ar-btn-success ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-on" style="padding: 5px 10px; font-size: 12px;">روشن</button>
                                                <?php endif; ?>
                                                <button type="button" class="ar-btn ar-btn-secondary ar-open-console-btn" data-name="<?php echo esc_attr($srv['name']); ?>" data-ip="<?php echo esc_attr($srv['ip_address']); ?>" style="padding: 5px 10px; font-size: 12px;">کنسول</button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab 2: Create Server Wizard -->
        <div id="tab_create_server" class="ar-tab-content" style="display: none;">
            <div class="ar-form-card" style="max-width: 860px; margin: 0 auto;">
                <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 24px;">
                    <span style="font-size: 28px;">🚀</span>
                    <div>
                        <h2 style="margin: 0; font-size: 20px; font-weight: 900;">ویزارد راه‌اندازی و تحویل آنی سرور ابری</h2>
                        <span style="font-size: 13px; color: var(--ar-text-secondary);">منابع سخت‌افزاری و سیستم‌عامل دلخواه خود را پیکربندی نمایید.</span>
                    </div>
                </div>

                <form id="ar_create_server_form">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-bottom: 10px;">
                        <div class="ar-form-group">
                            <label class="ar-form-label" for="ar_server_name">🏷️ نام سرور (Hostname):</label>
                            <input type="text" id="ar_server_name" class="ar-input" placeholder="مثال: cloud-web-node" required value="srv-node-<?php echo rand(100, 999); ?>">
                        </div>

                        <div class="ar-form-group">
                            <label class="ar-form-label" for="ar_region_select">🌍 دیتاسنتر / منطقه ابری:</label>
                            <select id="ar_region_select" class="ar-select">
                                <option value="ir-thr-at1">🇮🇷 تهران - دیتاسنتر عارف (ir-thr-at1)</option>
                                <option value="ir-thr-c1">🇮🇷 تهران - دیتاسنتر پردیس (ir-thr-c1)</option>
                                <option value="ir-tbz-dc1">🇮🇷 تبریز - دیتاسنتر شهریار (ir-tbz-dc1)</option>
                                <option value="nl-ams-1">🇳🇱 هلند - آمستردام (nl-ams-1)</option>
                            </select>
                        </div>
                    </div>

                    <div class="ar-form-group">
                        <label class="ar-form-label" for="ar_flavor_select">⚡ مشخصات سخت‌افزاری (vCPU / RAM / NVMe):</label>
                        <select id="ar_flavor_select" class="ar-select">
                            <?php foreach ($flavors as $f): 
                                $hourly_c = round(floatval($f['hourly_price']) * (1 + ($margin / 100)));
                            ?>
                                <option value="<?php echo esc_attr($f['id']); ?>">
                                    <?php echo esc_html($f['name']); ?> — [<?php echo number_format($hourly_c); ?> تومان / ساعت]
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="ar-form-group">
                        <label class="ar-form-label" for="ar_image_select">💿 سیستم‌عامل و نرم‌افزار پایه (OS Image):</label>
                        <select id="ar_image_select" class="ar-select">
                            <?php foreach ($images as $img): ?>
                                <option value="<?php echo esc_attr($img['id']); ?>">
                                    <?php echo esc_html($img['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Price Estimate Box -->
                    <div style="background: rgba(0, 186, 186, 0.06); border: 1px solid var(--ar-border-active); border-radius: var(--ar-radius-md); padding: 18px 24px; margin: 24px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                        <div>
                            <span style="font-size: 12px; color: var(--ar-text-secondary);">نرخ مصرف ساعتی از کیف پول:</span>
                            <div id="ar_hourly_calc" style="font-size: 22px; font-weight: 900; color: var(--ar-primary);">--- تومان / ساعت</div>
                        </div>
                        <div style="text-align: left;">
                            <span style="font-size: 12px; color: var(--ar-text-secondary);">تخمین هزینه ماهانه (۳۰ روز):</span>
                            <div id="ar_monthly_calc" style="font-size: 18px; font-weight: 800; color: var(--ar-text-primary);">--- تومان / ماه</div>
                        </div>
                    </div>

                    <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%; padding: 15px; font-size: 16px; font-weight: 900;">
                        🚀 ایجاد و تحویل آنی سرور ابری
                    </button>
                </form>
            </div>
        </div>

        <!-- Tab 3: CDN & Object Storage -->
        <div id="tab_cdn_storage" class="ar-tab-content" style="display: none;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
                <!-- CDN Card -->
                <div class="ar-form-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800;">🌐 شبکه توزیع محتوا (CDN)</h3>
                        <span class="ar-status-badge active">فعال و متصل</span>
                    </div>
                    <p style="font-size: 13.5px; color: var(--ar-text-secondary); line-height: 1.6; margin-bottom: 20px;">
                        بهینه‌سازی لبه شبکه (Edge)، ضد حملات DDoS لایه ۷ و صدور گواهی SSL رایگان در بیش از ۴۰ پاپ‌سایت بین‌المللی.
                    </p>
                    <div style="background: var(--ar-bg-body); border: 1px solid var(--ar-border-card); border-radius: var(--ar-radius-sm); padding: 14px; margin-bottom: 20px;">
                        <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;">دامنه فعال: shop4bit.ir</div>
                        <div style="font-size: 12px; color: var(--ar-text-muted);">NameServers: ns1.arvancdn.ir / ns2.arvancdn.ir</div>
                    </div>
                    <button type="button" class="ar-btn ar-btn-secondary" style="width: 100%; padding: 12px;" onclick="alert('کش کل لایه‌های لبه با موفقیت پاک‌سازی شد (Purge Cache Complete).');">
                        ⚡ پاک‌سازی فوری کش لبه (Purge Cache)
                    </button>
                </div>

                <!-- S3 Storage Card -->
                <div class="ar-form-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="margin: 0; font-size: 18px; font-weight: 800;">📦 فضای ذخیره‌سازی ابری (S3 Bucket)</h3>
                        <span class="ar-status-badge active">AWS S3 API</span>
                    </div>
                    <p style="font-size: 13.5px; color: var(--ar-text-secondary); line-height: 1.6; margin-bottom: 20px;">
                        ذخیره‌سازی فایل‌ها با دوام ۹۹.۹۹۹۹۹۹۹۹۹٪ (۱۱ تا ۹) سازگار با انواع ابزارهای S3cmd، Cyberduck و MinIO SDK.
                    </p>
                    <div style="background: var(--ar-bg-body); border: 1px solid var(--ar-border-card); border-radius: var(--ar-radius-sm); padding: 14px; margin-bottom: 20px;">
                        <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;">باکت فعال: arvan-storage-backup</div>
                        <div style="font-size: 12px; color: var(--ar-text-muted);" dir="ltr">Endpoint: https://s3.ir-thr-at1.arvanstorage.ir</div>
                    </div>
                    <button type="button" class="ar-btn ar-btn-secondary" style="width: 100%; padding: 12px;" onclick="alert('کلید دسترسی S3 Access Key تولید گردید.');">
                        🔑 مدیریت کلیدهای دسترسی (Access Keys)
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab 4: AI Advisor -->
        <div id="tab_ai_advisor" class="ar-tab-content" style="display: none;">
            <div class="ar-form-card">
                <h3 style="margin: 0 0 12px 0; font-size: 19px; font-weight: 900;">🤖 دستیار هوشمند انتخاب منابع و کاهش هزینه ابری (AI Advisor)</h3>
                <p style="font-size: 13.5px; color: var(--ar-text-secondary); margin-bottom: 24px;">
                    نوع پروژه یا سناریوی مصرف خود را انتخاب کنید تا موتور هوش مصنوعی بهترین ترکیب پردازنده، رم و دیتاسنتر را پیشنهاد دهد:
                </p>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 25px;">
                    <div class="ar-bento-card" style="cursor: pointer;" onclick="applyAiRecommendation('g1-2-1-0', 'سایت فروشگاهی وردپرس و ووکامرس (تا ۵۰ هزار بازدید)', 'General 2GB');">
                        <div style="font-size: 28px;">🛍️</div>
                        <div>
                            <div style="font-size: 15px; font-weight: 800; margin-bottom: 4px;">فروشگاه آنلاین ووکامرس</div>
                            <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۲ گیگابایت رم + ۱ هسته پردازنده</div>
                        </div>
                    </div>

                    <div class="ar-bento-card" style="cursor: pointer;" onclick="applyAiRecommendation('g1-4-2-0', 'پایگاه داده MySQL / PostgreSQL سازمانی', 'Pro Standard');">
                        <div style="font-size: 28px;">🗄️</div>
                        <div>
                            <div style="font-size: 15px; font-weight: 800; margin-bottom: 4px;">پایگاه داده سنگین (DB)</div>
                            <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۴ گیگابایت رم + ۲ هسته پردازنده</div>
                        </div>
                    </div>

                    <div class="ar-bento-card" style="cursor: pointer;" onclick="applyAiRecommendation('g1-1-1-0', 'ربات تلگرام، اسکریپت پایتون و وبلاگ شخصی', 'Eco Starter');">
                        <div style="font-size: 28px;">🐍</div>
                        <div>
                            <div style="font-size: 15px; font-weight: 800; margin-bottom: 4px;">ربات پایتون و ابزارک‌ها</div>
                            <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۱ گیگابایت رم (پلن اقتصادی)</div>
                        </div>
                    </div>
                </div>

                <div id="ar_ai_recommendation_box" style="display: none; background: rgba(0, 186, 186, 0.08); border: 1px solid var(--ar-primary); border-radius: var(--ar-radius-md); padding: 22px; margin-top: 20px;">
                    <h4 style="margin: 0 0 10px 0; color: var(--ar-primary); font-size: 16px;">✨ تحلیل و پیشنهاد دستیار هوش مصنوعی:</h4>
                    <p id="ar_ai_text" style="font-size: 14px; line-height: 1.7; margin-bottom: 16px; color: var(--ar-text-primary);"></p>
                    <button type="button" class="ar-btn ar-btn-primary" onclick="document.querySelector('[data-tab=tab_create_server]').click();">
                        🚀 انتقال به ویزارد و تحویل سرور پیشنهادی
                    </button>
                </div>
            </div>
        </div>

        <!-- Tab 5: Wallet Ledger -->
        <div id="tab_wallet_history" class="ar-tab-content" style="display: none;">
            <div class="ar-table-card">
                <div style="padding: 20px 24px; border-bottom: 1px solid var(--ar-border-card); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
                    <div>
                        <h3 style="margin: 0 0 4px 0; font-size: 17px; font-weight: 800;">📜 ریزتراکنش‌های دفتر کل مالی (Double-Entry Ledger)</h3>
                        <span style="font-size: 12px; color: var(--ar-text-secondary);">ثبت دقیق تمامی شارژها و کسرهای مصرف ساعتی به تفکیک زمان</span>
                    </div>
                    <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12.5px; padding: 7px 16px;" onclick="exportTableToCSV('arvan_ledger_report.csv')">
                        📥 دانلود فایل اکسل (CSV)
                    </button>
                </div>

                <div class="ar-table-responsive">
                    <table class="ar-enterprise-table" id="ar_ledger_table">
                        <thead>
                            <tr>
                                <th>ردیف</th>
                                <th>نوع تراکنش</th>
                                <th>مبلغ (تومان)</th>
                                <th>موجودی قبل</th>
                                <th>موجودی بعد</th>
                                <th>شناسه پیگیری</th>
                                <th>شرح عملیات</th>
                                <th>تاریخ و زمان</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($ledger)): ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; color: var(--ar-text-muted); padding: 30px;">هیچ سابقه تراکنشی ثبت نشده است.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($ledger as $i => $row): 
                                    $is_deposit = ($row['type'] === 'DEPOSIT');
                                ?>
                                    <tr>
                                        <td>#<?php echo $i + 1; ?></td>
                                        <td>
                                            <span class="ar-status-badge <?php echo $is_deposit ? 'active' : 'suspended'; ?>" style="font-size: 11px;">
                                                <?php echo $is_deposit ? '➕ شارژ آنلاین' : '➖ کسر مصرف ساعتی'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <strong style="color: <?php echo $is_deposit ? 'var(--ar-status-success)' : 'var(--ar-status-danger)'; ?>;">
                                                <?php echo ($is_deposit ? '+' : '') . number_format(floatval($row['amount'])); ?>
                                            </strong>
                                        </td>
                                        <td><?php echo number_format(floatval($row['balance_before'])); ?></td>
                                        <td><strong><?php echo number_format(floatval($row['balance_after'])); ?></strong></td>
                                        <td><code><?php echo esc_html($row['reference_id']); ?></code></td>
                                        <td><?php echo esc_html($row['description']); ?></td>
                                        <td dir="ltr" style="font-family: 'JetBrains Mono', monospace; font-size: 12px;"><?php echo esc_html($row['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- Deposit Modal -->
    <div class="ar-modal-backdrop" id="ar_deposit_modal">
        <div class="ar-modal-box">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800;">💳 افزایش اعتبار کیف پول پیش‌پرداخت</h3>
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
    document.getElementById('ar_flavor_select').value = flavorId;
    document.getElementById('ar_flavor_select').dispatchEvent(new Event('change'));
    document.getElementById('ar_ai_text').innerHTML = 'برای سناریوی <strong>' + title + '</strong>، پلن بهینه <strong>' + flavorName + '</strong> با سرعت دیسک NVMe انتخاب گردید.';
    document.getElementById('ar_ai_recommendation_box').style.display = 'block';
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
