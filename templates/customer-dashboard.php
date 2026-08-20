<?php
if (!defined('ABSPATH')) {
    exit;
}
$is_low_balance = (floatval($wallet['balance']) < 50000);
?>
<div class="ar-sorkhab-container" id="arvan_dashboard_app">
    <!-- Header & Wallet Banner -->
    <div class="ar-header">
        <div class="ar-logo-title">
            <div class="ar-logo-icon">☁️</div>
            <div>
                <h2>پنل مدیریت خدمات ابری آروان‌کلاد</h2>
                <span>زیرساخت ابری پیشرفته با تعرفه پرداخت به میزان مصرف (Pay-as-you-go)</span>
            </div>
        </div>

        <div class="ar-wallet-widget">
            <div class="ar-wallet-balance-info">
                <span>موجودی کیف پول پیش‌پرداخت:</span>
                <strong id="ar_wallet_balance_display"><?php echo number_format(floatval($wallet['balance'])); ?> تومان</strong>
            </div>
            <button type="button" class="ar-btn ar-btn-primary" id="ar_open_deposit_modal">
                ➕ افزایش اعتبار
            </button>
        </div>
    </div>

    <!-- Low Balance Warning Banner (Lifecycle Trigger) -->
    <?php if ($is_low_balance): ?>
        <div style="background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.4); border-radius: var(--ar-radius-sm); padding: 14px 20px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 20px;">⚠️</span>
                <span style="font-size: 13.5px; color: #fbbf24; font-weight: 700;">هشدار کسری موجودی: موجودی شما به زیر آستانه امن (۵۰,۰۰۰ تومان) رسیده است. جهت جلوگیری از قطع خودکار سرویس‌ها، نسبت به شارژ اقدام فرمایید.</span>
            </div>
            <button type="button" class="ar-btn ar-btn-primary" style="padding: 6px 14px; font-size: 12px;" onclick="document.getElementById('ar_open_deposit_modal').click();">
                شارژ فوری
            </button>
        </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <div class="ar-tabs">
        <button type="button" class="ar-tab-btn active" data-tab="tab_my_servers">🖥️ سرورهای ابری من (<?php echo count($user_servers); ?>)</button>
        <button type="button" class="ar-tab-btn" data-tab="tab_create_server">🚀 ساخت سرور ابری جدید</button>
        <button type="button" class="ar-tab-btn" data-tab="tab_cdn_storage">🌐 CDN و فضای ابری S3</button>
        <button type="button" class="ar-tab-btn" data-tab="tab_ai_advisor">🤖 دستیار هوشمند انتخاب پلن (AI)</button>
        <button type="button" class="ar-tab-btn" data-tab="tab_wallet_history">💳 دفتر کل مالی و تراکنش‌ها</button>
    </div>

    <!-- Tab 1: My Servers -->
    <div id="tab_my_servers" class="ar-tab-content">
        <?php if (empty($user_servers)): ?>
            <div class="ar-card" style="text-align: center; padding: 50px 20px;">
                <div style="font-size: 54px; margin-bottom: 15px;">🌩️</div>
                <h3 style="font-size: 20px; font-weight: 800;">شما در حال حاضر هیچ سرور ابری فعالی ندارید.</h3>
                <p style="color: var(--ar-text-muted); margin-bottom: 25px; font-size: 14px;">جهت راه‌اندازی سرور ابری با منابع اختصاصی NVMe، روی دکمه زیر کلیک نمایید.</p>
                <button type="button" class="ar-btn ar-btn-primary ar-tab-btn" data-tab="tab_create_server">
                    🚀 ایجاد سرور ابری جدید
                </button>
            </div>
        <?php else: ?>
            <div class="ar-grid">
                <?php foreach ($user_servers as $srv): 
                    $is_active = ($srv['status'] === 'ACTIVE');
                    $specs = json_decode($srv['specs'], true) ?: array();
                ?>
                    <div class="ar-card">
                        <div class="ar-card-header">
                            <h4 style="margin: 0; font-size: 16px; font-weight: 800;"><?php echo esc_html($srv['name']); ?></h4>
                            <span class="ar-badge <?php echo $is_active ? 'ar-badge-active' : 'ar-badge-suspended'; ?>">
                                <?php echo $is_active ? '● روشن و فعال' : '⏸ خاموش (Suspended)'; ?>
                            </span>
                        </div>

                        <ul class="ar-spec-list">
                            <li>
                                <span>آدرس IP اختصاصی:</span>
                                <strong style="font-family: monospace;" dir="ltr"><?php echo esc_html($srv['ip_address']); ?></strong>
                            </li>
                            <li>
                                <span>پلن سخت‌افزاری:</span>
                                <strong dir="ltr"><?php echo esc_html($srv['flavor_name']); ?></strong>
                            </li>
                            <li>
                                <span>دیتاسنتر / منطقه:</span>
                                <strong dir="ltr"><?php echo esc_html($srv['region']); ?></strong>
                            </li>
                            <li>
                                <span>تعرفه مصرف ساعتی:</span>
                                <strong style="color: var(--ar-primary); direction: rtl;"><?php echo number_format(floatval($srv['hourly_customer_price'])); ?> تومان / ساعت</strong>
                            </li>
                        </ul>

                        <div style="display: flex; gap: 8px; margin-top: 18px; flex-wrap: wrap;">
                            <?php if ($is_active): ?>
                                <button type="button" class="ar-btn ar-btn-danger ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-off" style="flex: 1;">
                                    🛑 خاموش کردن
                                </button>
                            <?php else: ?>
                                <button type="button" class="ar-btn ar-btn-success ar-toggle-power-btn" data-resource-id="<?php echo esc_attr($srv['resource_id']); ?>" data-action="power-on" style="flex: 1;">
                                    ⚡ روشن کردن
                                </button>
                            <?php endif; ?>
                            <button type="button" class="ar-btn ar-btn-secondary ar-open-console-btn" data-name="<?php echo esc_attr($srv['name']); ?>" data-ip="<?php echo esc_attr($srv['ip_address']); ?>" style="padding: 10px 14px;">
                                💻 کنسول وب
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Tab 2: Create Server Wizard -->
    <div id="tab_create_server" class="ar-tab-content" style="display: none;">
        <div class="ar-wizard-box">
            <h3 style="margin-top: 0; margin-bottom: 22px; font-size: 19px; font-weight: 800;">✨ ویزارد راه‌اندازی آنی سرور ابری ابر آروان</h3>
            <form id="ar_create_server_form">
                <div class="ar-grid" style="grid-template-columns: 1fr 1fr; margin-bottom: 10px;">
                    <div class="ar-form-group">
                        <label for="ar_server_name">🏷️ نام سرور (Hostname):</label>
                        <input type="text" id="ar_server_name" class="ar-input" placeholder="مثال: production-web-01" required value="srv-cloud-<?php echo rand(100, 999); ?>">
                    </div>

                    <div class="ar-form-group">
                        <label for="ar_region_select">🌍 منطقه ابری (Datacenter Region):</label>
                        <select id="ar_region_select" class="ar-select">
                            <option value="ir-thr-at1">تهران - دیتاسنتر عارف (ir-thr-at1)</option>
                            <option value="ir-thr-c1">تهران - دیتاسنتر پردیس (ir-thr-c1)</option>
                            <option value="ir-tbz-dc1">تبریز - دیتاسنتر شهریار (ir-tbz-dc1)</option>
                            <option value="nl-ams-1">هلند - آمستردام (nl-ams-1)</option>
                        </select>
                    </div>
                </div>

                <div class="ar-form-group">
                    <label for="ar_flavor_select">⚡ انتخاب منابع سخت‌افزاری (vCPU / RAM / NVMe):</label>
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
                    <label for="ar_image_select">💿 سیستم‌عامل و نرم‌افزار پایه (OS Image):</label>
                    <select id="ar_image_select" class="ar-select">
                        <?php foreach ($images as $img): ?>
                            <option value="<?php echo esc_attr($img['id']); ?>">
                                <?php echo esc_html($img['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price Summary Box -->
                <div style="background: var(--ar-surface-card); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 18px 24px; margin: 24px 0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
                    <div>
                        <span style="font-size: 12px; color: var(--ar-text-muted);">تعرفه کسر ساعتی از کیف پول:</span>
                        <div id="ar_hourly_calc" style="font-size: 20px; font-weight: 900; color: var(--ar-primary);">--- تومان / ساعت</div>
                    </div>
                    <div style="text-align: left;">
                        <span style="font-size: 12px; color: var(--ar-text-muted);">تخمین هزینه ماهانه (۳۰ روز):</span>
                        <div id="ar_monthly_calc" style="font-size: 17px; font-weight: 800; color: var(--ar-text-main);">--- تومان / ماه</div>
                    </div>
                </div>

                <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%; padding: 15px; font-size: 16px;">
                    🚀 ساخت و تحویل آنی سرور ابری
                </button>
            </form>
        </div>
    </div>

    <!-- Tab 3: CDN & Object Storage Multi-Product Expansion -->
    <div id="tab_cdn_storage" class="ar-tab-content" style="display: none;">
        <div class="ar-grid" style="grid-template-columns: 1fr 1fr;">
            <!-- CDN Box -->
            <div class="ar-card">
                <div class="ar-card-header">
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800;">🌐 شبکه توزیع محتوا (CDN)</h3>
                    <span class="ar-badge ar-badge-active">فعال</span>
                </div>
                <p style="font-size: 13px; color: var(--ar-text-muted); line-height: 1.6;">افزایش سرعت، امنیت DDoS و SSL رایگان برای دامنه‌های شما در سراسر جهان.</p>
                <div style="background: var(--ar-bg-dark); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 14px; margin-bottom: 15px;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;">دامنه متصل: shop4bit.ir</div>
                    <div style="font-size: 12px; color: var(--ar-text-muted);">NS1: ns1.arvancdn.ir | NS2: ns2.arvancdn.ir</div>
                </div>
                <button type="button" class="ar-btn ar-btn-secondary" style="width: 100%;" onclick="alert('کش کل دامنه با موفقیت در لبه‌های ابر آروان پاک‌سازی شد (Purge Cache Complete).');">
                    ⚡ پاک‌سازی کش لبه (Purge Cache)
                </button>
            </div>

            <!-- S3 Object Storage Box -->
            <div class="ar-card">
                <div class="ar-card-header">
                    <h3 style="margin: 0; font-size: 17px; font-weight: 800;">📦 فضای ذخیره‌سازی ابری (S3 Storage)</h3>
                    <span class="ar-badge ar-badge-active">سازگار با S3</span>
                </div>
                <p style="font-size: 13px; color: var(--ar-text-muted); line-height: 1.6;">ذخیره‌سازی نامحدود فایل‌ها و بکاپ‌ها با پروتکل استاندارد AWS S3 API.</p>
                <div style="background: var(--ar-bg-dark); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 14px; margin-bottom: 15px;">
                    <div style="font-size: 13px; font-weight: bold; margin-bottom: 4px;">باکت فعال: arvan-storage-backup</div>
                    <div style="font-size: 12px; color: var(--ar-text-muted);" dir="ltr">Endpoint: https://s3.ir-thr-at1.arvanstorage.ir</div>
                </div>
                <button type="button" class="ar-btn ar-btn-secondary" style="width: 100%;" onclick="alert('کلید دسترسی S3 Access Key تولید و فعال گردید.');">
                    🔑 مدیریت کلیدهای Access Key
                </button>
            </div>
        </div>
    </div>

    <!-- Tab 4: AI Smart Cloud Sizer & Assistant -->
    <div id="tab_ai_advisor" class="ar-tab-content" style="display: none;">
        <div class="ar-card">
            <h3 style="margin-top: 0; margin-bottom: 15px; font-size: 18px; font-weight: 800;">🤖 دستیار هوش مصنوعی انتخاب منابع و کاهش هزینه ابری</h3>
            <p style="font-size: 13.5px; color: var(--ar-text-muted); margin-bottom: 20px;">نیازمندی یا کاربرد وب‌سایت خود را انتخاب کنید تا هوش مصنوعی مناسب‌ترین پلن سخت‌افزاری و برآورد دقیق هزینه را پیشنهاد دهد:</p>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 15px; margin-bottom: 25px;">
                <div style="background: var(--ar-bg-dark); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 16px; cursor: pointer; transition: all 0.2s;" onclick="applyAiRecommendation('g1-2-1-0', 'سایت فروشگاهی وردپرس و ووکامرس (تا ۵۰ هزار بازدید ماهانه)', 'General 2GB');">
                    <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;">🛍️ فروشگاه وردپرس و ووکامرس</div>
                    <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۲ گیگابایت رم + ۱ هسته vCPU</div>
                </div>

                <div style="background: var(--ar-bg-dark); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 16px; cursor: pointer; transition: all 0.2s;" onclick="applyAiRecommendation('g1-4-2-0', 'پایگاه داده MySQL / PostgreSQL سازمانی', 'Pro Standard');">
                    <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;">🗄️ پایگاه داده و دیتابیس سنگین</div>
                    <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۴ گیگابایت رم + ۲ هسته vCPU</div>
                </div>

                <div style="background: var(--ar-bg-dark); border: 1px solid var(--ar-border-dark); border-radius: var(--ar-radius-sm); padding: 16px; cursor: pointer; transition: all 0.2s;" onclick="applyAiRecommendation('g1-1-1-0', 'ربات تلگرام، اسکریپت پایتون و وب‌سایت شرکتی', 'Eco Starter');">
                    <div style="font-size: 15px; font-weight: bold; margin-bottom: 4px;">🐍 ربات پایتون و وبلاگ شخصی</div>
                    <div style="font-size: 12px; color: var(--ar-primary);">پیشنهاد: ۱ گیگابایت رم (پلن اقتصادی)</div>
                </div>
            </div>

            <div id="ar_ai_recommendation_box" style="display: none; background: rgba(0, 186, 186, 0.1); border: 1px solid var(--ar-primary); border-radius: var(--ar-radius-sm); padding: 20px; margin-top: 20px;">
                <h4 style="margin: 0 0 10px 0; color: var(--ar-primary);">✨ تحلیل و پیشنهاد دستیار هوش مصنوعی:</h4>
                <p id="ar_ai_text" style="font-size: 14px; line-height: 1.6; margin-bottom: 15px;"></p>
                <button type="button" class="ar-btn ar-btn-primary" onclick="document.querySelector('[data-tab=tab_create_server]').click();">
                    🚀 انتقال به ویزارد و ساخت سرور پیشنهادی
                </button>
            </div>
        </div>
    </div>

    <!-- Tab 5: Wallet Ledger -->
    <div id="tab_wallet_history" class="ar-tab-content" style="display: none;">
        <div class="ar-card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 10px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800;">📜 ریزتراکنش‌های دفتر کل مالی (Double-Entry Ledger)</h3>
                <button type="button" class="ar-btn ar-btn-secondary" style="font-size: 12px; padding: 6px 14px;" onclick="exportTableToCSV('arvan_ledger_report.csv')">
                    📥 دانلود خروجی اکسل (CSV)
                </button>
            </div>

            <div class="ar-table-responsive">
                <table class="ar-table" id="ar_ledger_table">
                    <thead>
                        <tr>
                            <th>ردیف</th>
                            <th>نوع تراکنش</th>
                            <th>مبلغ</th>
                            <th>موجودی قبل</th>
                            <th>موجودی پس از تراکنش</th>
                            <th>شناسه مرجع</th>
                            <th>شرح تراکنش</th>
                            <th>زمان ثبت</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($ledger)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--ar-text-muted); padding: 30px;">هیچ تراکنشی یافت نشد.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($ledger as $i => $row): 
                                $is_deposit = ($row['type'] === 'DEPOSIT');
                            ?>
                                <tr>
                                    <td>#<?php echo $i + 1; ?></td>
                                    <td>
                                        <span class="ar-badge <?php echo $is_deposit ? 'ar-badge-active' : 'ar-badge-suspended'; ?>">
                                            <?php echo $is_deposit ? '➕ شارژ کیف پول' : '➖ مصرف ساعتی'; ?>
                                        </span>
                                    </td>
                                    <td style="font-weight: 800; color: <?php echo $is_deposit ? 'var(--ar-status-active)' : 'var(--ar-status-danger)'; ?>;">
                                        <?php echo ($is_deposit ? '+' : '') . number_format(floatval($row['amount'])); ?> تومان
                                    </td>
                                    <td><?php echo number_format(floatval($row['balance_before'])); ?> تومان</td>
                                    <td style="font-weight: 800;"><?php echo number_format(floatval($row['balance_after'])); ?> تومان</td>
                                    <td><code><?php echo esc_html($row['reference_id']); ?></code></td>
                                    <td><?php echo esc_html($row['description']); ?></td>
                                    <td style="font-family: monospace;" dir="ltr"><?php echo esc_html($row['created_at']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Deposit Modal -->
    <div class="ar-modal-backdrop" id="ar_deposit_modal">
        <div class="ar-modal">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3 style="margin: 0; font-size: 18px; font-weight: 800;">💳 افزایش موجودی کیف پول پیش‌پرداخت</h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 24px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <form id="ar_deposit_form">
                <div class="ar-form-group">
                    <label for="ar_deposit_amount">مبلغ شارژ (تومان):</label>
                    <input type="number" id="ar_deposit_amount" class="ar-input" min="10000" step="5000" value="100000" required>
                </div>

                <div style="display: flex; gap: 10px; margin-bottom: 24px; flex-wrap: wrap;">
                    <button type="button" class="ar-btn ar-btn-secondary" onclick="document.getElementById('ar_deposit_amount').value=50000;">۵۰,۰۰۰ تومان</button>
                    <button type="button" class="ar-btn ar-btn-secondary" onclick="document.getElementById('ar_deposit_amount').value=100000;">۱۰۰,۰۰۰ تومان</button>
                    <button type="button" class="ar-btn ar-btn-secondary" onclick="document.getElementById('ar_deposit_amount').value=500000;">۵۰۰,۰۰۰ تومان</button>
                </div>

                <button type="submit" class="ar-btn ar-btn-primary" style="width: 100%; padding: 14px; font-size: 15px;">
                    💳 پرداخت آنلاین و شارژ آنی
                </button>
            </form>
        </div>
    </div>

    <!-- Web Console Modal -->
    <div class="ar-modal-backdrop" id="ar_console_modal">
        <div class="ar-modal" style="max-width: 650px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; font-size: 17px; font-weight: 800;">💻 کنسول ترمینال سرور: <span id="ar_console_server_name" style="color: var(--ar-primary);"></span></h3>
                <button type="button" class="ar-close-modal" style="background: none; border: none; font-size: 24px; color: var(--ar-text-muted); cursor: pointer;">&times;</button>
            </div>

            <div class="ar-terminal-window">
                <div>[ArvanCloud Cloud Engine] Connected to hypervisor node: ir-thr-at1</div>
                <div>Server status: RUNNING (Uptime: 99.98%)</div>
                <div>Kernel: Linux 6.8.0-134-generic #134-Ubuntu SMP x86_64</div>
                <div style="color: #60a5fa;">root@arvan-server:~# uname -a</div>
                <div>Linux production-node 6.8.0-134-generic x86_64 GNU/Linux</div>
                <div style="color: #60a5fa;">root@arvan-server:~# ip addr show eth0</div>
                <div>inet <span id="ar_console_ip"></span>/24 brd 185.143.233.255 scope global eth0</div>
                <div style="color: #facc15;">root@arvan-server:~# _</div>
            </div>

            <button type="button" class="ar-btn ar-btn-secondary ar-close-modal" style="width: 100%;">
                بستن پنجره کنسول
            </button>
        </div>
    </div>
</div>

<script>
function applyAiRecommendation(flavorId, title, flavorName) {
    document.getElementById('ar_flavor_select').value = flavorId;
    document.getElementById('ar_flavor_select').dispatchEvent(new Event('change'));
    document.getElementById('ar_ai_text').innerHTML = 'برای کاربرد <strong>' + title + '</strong>، پلن پیشنهادی <strong>' + flavorName + '</strong> با سرعت هارد NVMe و تضمین پایداری انتخاب گردید.';
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
