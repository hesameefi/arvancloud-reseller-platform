<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_api_key = get_option('arvan_api_key', '');
$current_mode = get_option('arvan_mode', 'mock');
$current_margin = floatval(get_option('arvan_reseller_margin', 20));
?>
<div class="arvan-admin-wrap">
    <!-- Header -->
    <div class="arvan-admin-header">
        <div class="arvan-admin-title">
            <span style="font-size: 32px;">⚙️</span>
            <div>
                <h1>تنظیمات اتصال API و کارمزد سود ریسلر</h1>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; font-weight: 500;">پیکربندی کلید کاربر ماشین (Machine User)، حاشیه سود خودکار (حداکثر ۲۰٪) و انتخاب موتور هیبریدی لایو/ماک.</p>
            </div>
        </div>
    </div>

    <!-- Settings Card -->
    <div class="arvan-admin-card" style="max-width: 800px;">
        <form id="arvan_admin_settings_form">
            <div class="arvan-admin-form-group">
                <label for="arvan_setting_mode">🌐 حالت اتصال به وب‌سرویس آروان (API Mode):</label>
                <select name="mode" id="arvan_setting_mode" class="arvan-admin-select">
                    <option value="mock" <?php selected($current_mode, 'mock'); ?>>حالت شبیه‌ساز و دمو (Mock Engine - بدون هزینه و بسیار پایدار برای دمو)</option>
                    <option value="live" <?php selected($current_mode, 'live'); ?>>حالت لایو و واقعی (Real ArvanCloud API)</option>
                </select>
                <p style="font-size: 12.5px; color: #64748b; margin-top: 6px;">در حالت دمو، تمام امکانات ساخت، خاموش‌سازی و کسر کیف پول به صورت محلی و با تضمین پایداری اجرا می‌شوند.</p>
            </div>

            <div class="arvan-admin-form-group">
                <label for="arvan_setting_api_key">🔑 کلید کاربر ماشین (Machine User API Key):</label>
                <input type="password" name="api_key" id="arvan_setting_api_key" value="<?php echo esc_attr($current_api_key); ?>" class="arvan-admin-input" placeholder="Apikey xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx" dir="ltr">
                <p style="font-size: 12.5px; color: #64748b; margin-top: 6px;">کلید تولیدشده از منوی «کاربران ماشین» در پنل کاربری ابر آروان.</p>
            </div>

            <div class="arvan-admin-form-group">
                <label for="arvan_setting_margin">📈 حاشیه سود ریسلر (Reseller Margin %):</label>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <input type="number" name="margin" id="arvan_setting_margin" value="<?php echo esc_attr($current_margin); ?>" min="0" max="20" step="0.5" class="arvan-admin-input" style="width: 140px; font-weight: 900; font-size: 17px; color: #00baba;" required>
                    <span style="font-weight: 800; font-size: 15px; color: #0f172a;">درصد سود (٪)</span>
                </div>
                <p style="font-size: 12.5px; color: #64748b; margin-top: 6px;">طبق بریف هکاتون، حداکثر حاشیه سود مجاز <strong>۲۰ درصد</strong> می‌باشد که به صورت خودکار به تعرفه پایه آروان اضافه می‌شود.</p>
            </div>

            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9; display: flex; align-items: center; gap: 16px;">
                <button type="submit" id="arvan_btn_save_settings" class="arvan-btn-admin arvan-btn-admin-primary" style="padding: 12px 28px; font-size: 14.5px;">
                    💾 ذخیره تنظیمات ریسلری
                </button>
                <span id="arvan_save_msg" style="font-weight: 800; font-size: 14px;"></span>
            </div>
        </form>
    </div>
</div>
