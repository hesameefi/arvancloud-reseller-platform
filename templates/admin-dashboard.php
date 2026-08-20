<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap" style="direction: rtl; text-align: right; font-family: 'Vazirmatn', sans-serif;">
    <h1 style="display: flex; align-items: center; gap: 12px; margin-bottom: 25px;">
        <span>☁️ پنل مدیریت ریسلری محصولات ابر آروان</span>
        <span style="font-size: 13px; background: #00baba; color: #081118; padding: 4px 10px; border-radius: 4px; font-weight: bold;">نسخه سازمانی ۱.۰</span>
    </h1>

    <!-- Action & Demo Data Management Toolbar -->
    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 18px 24px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
            <button type="button" id="arvan_btn_seed_demo" class="button button-primary" style="background: #00baba; border-color: #008080; color: #081118; font-weight: bold;">
                ✨ ایجاد و بارگذاری سناریوی دموی پیشرفته
            </button>
            <button type="button" id="arvan_btn_run_cron" class="button" style="background: #008080; border-color: #004a4a; color: white; font-weight: bold;">
                ⏱️ اجرای دستی محاسبه و کسر مصرف ساعتی
            </button>
            <button type="button" id="arvan_btn_reset_demo" class="button button-link-delete">
                🗑️ پاک‌سازی و ریست داده‌های دمو
            </button>
        </div>
        <div style="font-size: 13px; color: #646970;">
            سیستم لجر: <strong>فعال (Zero-Dependency)</strong> | حاشیه سود فعال: <strong style="color: #008080;"><?php echo esc_html(get_option('arvan_reseller_margin', 20)); ?>%</strong>
        </div>
    </div>

    <!-- KPI Stats Cards -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: #646970; font-size: 13px; margin-bottom: 6px;">سرورهای فعال مشتریان</div>
            <div style="font-size: 30px; font-weight: 800; color: #10b981;"><?php echo intval($total_active_servers); ?> عدد</div>
        </div>

        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: #646970; font-size: 13px; margin-bottom: 6px;">سرورهای معلق (اتمام موجودی)</div>
            <div style="font-size: 30px; font-weight: 800; color: #f59e0b;"><?php echo intval($total_suspended); ?> عدد</div>
        </div>

        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: #646970; font-size: 13px; margin-bottom: 6px;">مجموع سود خالص ریسلر (Margin)</div>
            <div style="font-size: 30px; font-weight: 800; color: #008080;"><?php echo number_format(floatval($total_profit)); ?> تومان</div>
        </div>

        <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 22px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
            <div style="color: #646970; font-size: 13px; margin-bottom: 6px;">موجودی امانی کاربران در کیف پول</div>
            <div style="font-size: 30px; font-weight: 800; color: #3b82f6;"><?php echo number_format(floatval($total_customer_balances)); ?> تومان</div>
        </div>
    </div>

    <!-- Active Instances Table with Inline Edit & Delete -->
    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 22px;">
        <h2 style="margin-top: 0; font-size: 17px; font-weight: bold; margin-bottom: 15px;">🖥️ مدیریت و مانیتورینگ سرورهای ابری کاربران</h2>
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>شناسه منبع</th>
                    <th>نام سرور</th>
                    <th>کاربر</th>
                    <th>آدرس IP</th>
                    <th>پلن سخت‌افزاری</th>
                    <th>هزینه پایه آروان</th>
                    <th>درصد سود</th>
                    <th>قیمت فروش ساعتی</th>
                    <th>وضعیت</th>
                    <th>عملیات</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_servers)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 25px; color: #646970;">هیچ سروری در دیتابیس ثبت نشده است. با زدن دکمه «ایجاد و بارگذاری سناریوی دموی پیشرفته» در بالا، نمونه سرورها را بارگذاری نمایید.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recent_servers as $s): 
                        $is_act = ($s['status'] === 'ACTIVE');
                    ?>
                        <tr id="arvan_server_row_<?php echo $s['id']; ?>">
                            <td><code><?php echo esc_html($s['resource_id']); ?></code></td>
                            <td><strong id="arvan_srv_name_<?php echo $s['id']; ?>"><?php echo esc_html($s['name']); ?></strong></td>
                            <td>کاربر #<?php echo esc_html($s['user_id']); ?></td>
                            <td><code style="direction: ltr; display: inline-block;"><?php echo esc_html($s['ip_address']); ?></code></td>
                            <td><?php echo esc_html($s['flavor_name']); ?></td>
                            <td><?php echo number_format(floatval($s['hourly_base_price'])); ?> تومان</td>
                            <td><span style="color: #008080; font-weight: bold;"><?php echo esc_html($s['reseller_margin_percent']); ?>%</span></td>
                            <td><strong><?php echo number_format(floatval($s['hourly_customer_price'])); ?> تومان</strong></td>
                            <td>
                                <?php if ($is_act): ?>
                                    <span style="color: #10b981; font-weight: bold;">● فعال</span>
                                <?php else: ?>
                                    <span style="color: #f59e0b; font-weight: bold;">⏸ معلق (Suspended)</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="button button-small arvan-edit-server-btn" data-id="<?php echo $s['id']; ?>" data-name="<?php echo esc_attr($s['name']); ?>" data-status="<?php echo esc_attr($s['status']); ?>">
                                    ✏️ ویرایش
                                </button>
                                <button type="button" class="button button-small button-link-delete arvan-delete-server-btn" data-id="<?php echo $s['id']; ?>">
                                    🗑️ حذف
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
