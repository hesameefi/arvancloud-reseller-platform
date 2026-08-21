<?php
if (!defined('ABSPATH')) {
    exit;
}

$raw_api_key = get_option('arvan_api_key', '');
$decrypted_api_key = Arvan_Security::decrypt($raw_api_key);
$masked_api_key = Arvan_Security::mask_key($raw_api_key);
$current_mode = get_option('arvan_mode', 'mock');
$current_margin = floatval(get_option('arvan_reseller_margin', 20));
$current_rpm = intval(get_option('arvan_rate_limit_rpm', 60));

$telemetry = Arvan_Rate_Limiter::get_instance()->get_telemetry();
?>
<div class="arvan-admin-wrap">
    <!-- Header -->
    <div class="arvan-admin-header">
        <div class="arvan-admin-title">
            <span style="font-size: 32px;">⚙️</span>
            <div>
                <h1>تنظیمات اتصال API، امنیت و سقف ریت‌لیمیت</h1>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; font-weight: 500;">رمزنگاری پیشرفته نظامی AES-256، کنترل سقف درخواست‌ها و مدیریت حاشیه سود ریسلر.</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary" id="arvan_admin_theme_toggle">
                🌙 تم تاریک
            </button>
        </div>
    </div>

    <!-- 2-Column Grid: Settings Form & Live Telemetry -->
    <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 24px; align-items: start;">
        
        <!-- Settings Card -->
        <div class="arvan-admin-card">
            <form id="arvan_admin_settings_form">
                <div class="arvan-admin-form-group">
                    <label for="arvan_setting_mode">🌐 حالت اتصال به وب‌سرویس آروان (API Mode):</label>
                    <select name="mode" id="arvan_setting_mode" class="arvan-admin-select">
                        <option value="mock" <?php selected($current_mode, 'mock'); ?>>حالت شبیه‌ساز و دمو (Mock Engine - بدون هزینه و فوق‌العاده پایدار)</option>
                        <option value="live" <?php selected($current_mode, 'live'); ?>>حالت لایو و واقعی (Real ArvanCloud API)</option>
                    </select>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px;">در حالت دمو، ساخت و خاموش‌سازی سرورها به صورت محلی و با تضمین پایداری اجرا می‌شوند.</p>
                </div>

                <div class="arvan-admin-form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <label for="arvan_setting_api_key" style="margin: 0;">🔑 کلید کاربر ماشین (Machine User API Key):</label>
                        <span style="background: rgba(16, 185, 129, 0.12); color: #059669; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 12px; border: 1px solid rgba(16, 185, 129, 0.25);">
                            🔒 رمزنگاری AES-256 در DB
                        </span>
                    </div>
                    <div style="position: relative; display: flex; align-items: center;">
                        <input type="password" name="api_key" id="arvan_setting_api_key" value="<?php echo esc_attr($decrypted_api_key); ?>" class="arvan-admin-input" placeholder="Apikey xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" dir="ltr" style="padding-left: 45px;">
                        <button type="button" id="arvan_toggle_key_vis" style="position: absolute; left: 10px; background: transparent; border: none; cursor: pointer; font-size: 16px; opacity: 0.6;" title="نمایش/مخفی‌سازی کلید">
                            👁️
                        </button>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px;">کلید پس از ذخیره به صورت خودکار با کلیدهای اختصاصی وردپرس رمزنگاری شده و در دیتابیس بدون خوانایی ذخیره می‌شود.</p>
                </div>

                <div class="arvan-admin-form-group">
                    <label for="arvan_setting_rpm">⏱️ سقف مجاز نرخ درخواست‌ها در دقیقه (Rate Limit RPM):</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="number" name="rate_limit_rpm" id="arvan_setting_rpm" value="<?php echo esc_attr($current_rpm); ?>" min="10" max="600" step="5" class="arvan-admin-input" style="width: 140px; font-weight: 800; font-size: 16px; color: #6366f1;" required>
                        <span style="font-weight: 700; font-size: 13.5px; color: #475569;">درخواست در هر دقیقه (Req/Min)</span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px;">جهت جلوگیری از بلاک شدن IP سرور یا دریافت خطای 429 از آروان‌کلاد.</p>
                </div>

                <div class="arvan-admin-form-group">
                    <label for="arvan_setting_margin">📈 حاشیه سود ریسلر (Reseller Margin %):</label>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <input type="number" name="margin" id="arvan_setting_margin" value="<?php echo esc_attr($current_margin); ?>" min="0" max="20" step="0.5" class="arvan-admin-input" style="width: 140px; font-weight: 900; font-size: 17px; color: #00baba;" required>
                        <span style="font-weight: 800; font-size: 15px; color: #0f172a;">درصد سود (٪)</span>
                    </div>
                    <p style="font-size: 12px; color: #64748b; margin-top: 6px;">حداکثر سقف مجاز طبق بریف هکاتون: <strong>۲۰ درصد</strong>.</p>
                </div>

                <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 16px;">
                    <button type="submit" id="arvan_btn_save_settings" class="arvan-btn-admin arvan-btn-admin-primary" style="padding: 12px 28px; font-size: 14.5px;">
                        💾 ذخیره تنظیمات و رمزنگاری کلید
                    </button>
                    <span id="arvan_save_msg" style="font-weight: 800; font-size: 14px;"></span>
                </div>
            </form>
        </div>

        <!-- Telemetry & Quota Guard Card -->
        <div class="arvan-admin-card" style="background: #ffffff;">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 8px;">
                ⚡ وضعیت مصرف کلید و سلامت API
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px;">
                    <div style="font-size: 12px; color: #64748b; font-weight: 600;">شاخص پایداری و سلامت کلید (Health Score)</div>
                    <div style="font-size: 24px; font-weight: 900; color: #10b981; margin-top: 4px;">
                        <?php echo $telemetry['health_score']; ?>٪
                    </div>
                    <div style="font-size: 11.5px; color: #059669; font-weight: 700; margin-top: 2px;">
                        ● وضعیت: <?php echo esc_html($telemetry['status']); ?>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
                        <div style="font-size: 11px; color: #64748b;">درخواست‌های امروز</div>
                        <div style="font-size: 18px; font-weight: 800; color: #0f172a; margin-top: 2px;">
                            <?php echo number_format($telemetry['today_requests']); ?>
                        </div>
                    </div>

                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px;">
                        <div style="font-size: 11px; color: #64748b;">خطای ریت‌لیمیت (429)</div>
                        <div style="font-size: 18px; font-weight: 800; color: <?php echo $telemetry['total_throttled'] > 0 ? '#ef4444' : '#10b981'; ?>; margin-top: 2px;">
                            <?php echo number_format($telemetry['total_throttled']); ?>
                        </div>
                    </div>
                </div>

                <div style="font-size: 12px; color: #64748b; border-top: 1px dashed #e2e8f0; padding-top: 12px;">
                    <div>مجموع کل درخواست‌ها: <strong><?php echo number_format($telemetry['total_requests']); ?></strong></div>
                    <div style="margin-top: 4px;">آخرین فعالیت: <strong dir="ltr"><?php echo esc_html($telemetry['last_active']); ?></strong></div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('arvan_toggle_key_vis');
    const keyInput = document.getElementById('arvan_setting_api_key');
    if (toggleBtn && keyInput) {
        toggleBtn.addEventListener('click', function() {
            if (keyInput.type === 'password') {
                keyInput.type = 'text';
                toggleBtn.innerText = '🔒';
            } else {
                keyInput.type = 'password';
                toggleBtn.innerText = '👁️';
            }
        });
    }
});
</script>
