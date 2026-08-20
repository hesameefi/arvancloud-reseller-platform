<?php
if (!defined('ABSPATH')) {
    exit;
}

$current_api_key = get_option('arvan_api_key', '');
$current_mode = get_option('arvan_mode', 'mock');
$current_margin = floatval(get_option('arvan_reseller_margin', 20));
?>
<div class="wrap" style="direction: rtl; text-align: right; font-family: 'Vazirmatn', sans-serif;">
    <h1>⚙️ تنظیمات و درصد سود ریسلر آروان‌کلاد</h1>
    <p style="color: #50575e; margin-bottom: 25px;">در این بخش می‌توانید کلید API کاربر ماشین آروان و حاشیه سود مجاز (حداکثر ۲۰ درصد) را پیکربندی نمایید.</p>

    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 25px; max-width: 700px;">
        <form id="arvan_admin_settings_form">
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="arvan_setting_mode">حالت اتصال API:</label></th>
                    <td>
                        <select name="mode" id="arvan_setting_mode" class="regular-text">
                            <option value="mock" <?php selected($current_mode, 'mock'); ?>>حالت شبیه‌ساز و دمو (Mock Engine - بدون هزینه و پایدار برای دمو)</option>
                            <option value="live" <?php selected($current_mode, 'live'); ?>>حالت لایو و واقعی (Real ArvanCloud API)</option>
                        </select>
                        <p class="description">در حالت دمو، تمام امکانات ساخت، خاموش‌سازی و کسر کیف پول به صورت محلی و بدون کسر شارژ واقعی اجرا می‌شوند.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="arvan_setting_api_key">کلید API کاربر ماشین (Machine User Key):</label></th>
                    <td>
                        <input type="password" name="api_key" id="arvan_setting_api_key" value="<?php echo esc_attr($current_api_key); ?>" class="large-text" placeholder="Apikey xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx">
                        <p class="description">کلید ساخته‌شده از بخش «کاربران ماشین» در پنل کاربری ابر آروان.</p>
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="arvan_setting_margin">حاشیه سود ریسلر (Reseller Margin %):</label></th>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="number" name="margin" id="arvan_setting_margin" value="<?php echo esc_attr($current_margin); ?>" min="0" max="20" step="0.5" class="small-text" style="width: 80px;" required>
                            <span style="font-weight: bold; font-size: 16px;">درصد (٪)</span>
                        </div>
                        <p class="description">طبق بریف رسمی هکاتون، حداکثر حاشیه سود مجاز <strong>۲۰ درصد</strong> می‌باشد که به صورت خودکار به تعرفه پایه آروان اضافه می‌گردد.</p>
                    </td>
                </tr>
            </table>

            <div style="margin-top: 25px; padding-top: 15px; border-top: 1px solid #eee;">
                <button type="submit" id="arvan_btn_save_settings" class="button button-primary button-large" style="background: #00baba; border-color: #008080; color: black; font-weight: bold;">
                    💾 ذخیره تنظیمات ریسلری
                </button>
                <span id="arvan_save_msg" style="margin-right: 15px; font-weight: bold;"></span>
            </div>
        </form>
    </div>
</div>
