<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="arvan-admin-wrap">
    <!-- Header -->
    <div class="arvan-admin-header">
        <div class="arvan-admin-title">
            <span style="font-size: 32px;">☁️</span>
            <div>
                <h1>
                    پنل مدیریت ریسلری خدمات ابر آروان
                    <span class="arvan-version-badge">Enterprise v1.2</span>
                </h1>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; font-weight: 500;">مانیتورینگ پیشرفته ابرک‌های مشتریان، مدیریت کران‌جاب‌های مصرف ساعتی و تسویه‌حساب خودکار سود ریسلری.</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary" id="arvan_admin_theme_toggle">
                🌙 تم تاریک
            </button>
            <a href="<?php echo home_url('/'); ?>" target="_blank" class="arvan-btn-admin arvan-btn-admin-secondary">
                🌐 مشاهده داشبورد مشتریان
            </a>
        </div>
    </div>

    <!-- Action & Demo Data Management Toolbar -->
    <div class="arvan-admin-toolbar">
        <div class="arvan-toolbar-actions">
            <button type="button" id="arvan_btn_seed_demo" class="arvan-btn-admin arvan-btn-admin-primary">
                ✨ ایجاد و بارگذاری داده‌های دمو
            </button>
            <button type="button" id="arvan_btn_run_cron" class="arvan-btn-admin arvan-btn-admin-secondary">
                ⏱️ اجرای دستی کسر مصرف ساعتی (Cron)
            </button>
            <button type="button" id="arvan_btn_reset_demo" class="arvan-btn-admin arvan-btn-admin-danger">
                🗑️ ریست کل داده‌ها
            </button>
        </div>
        <div style="font-size: 13px; color: #475569; font-weight: 600;">
            وضعیت لجر: <strong style="color: #16a34a;">ACID Safe</strong> | حاشیه سود فعال: <strong style="color: #00baba;"><?php echo esc_html(get_option('arvan_reseller_margin', 20)); ?>%</strong>
        </div>
    </div>

    <!-- KPI Stats Bento Grid -->
    <div class="arvan-admin-kpi-grid">
        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">ابرک‌های فعال مشتریان</div>
            <div class="arvan-admin-kpi-value" style="color: #16a34a;"><?php echo intval($total_active_servers); ?> <small style="font-size: 14px; font-weight: normal; color: #64748b;">سرور</small></div>
            <div class="arvan-admin-kpi-sub">وضعیت آنلاین و در حال مصرف</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">ابرک‌های معلق (Suspended)</div>
            <div class="arvan-admin-kpi-value" style="color: #d97706;"><?php echo intval($total_suspended); ?> <small style="font-size: 14px; font-weight: normal; color: #64748b;">سرور</small></div>
            <div class="arvan-admin-kpi-sub">به علت کسری اعتبار کیف پول</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">مجموع سود خالص ریسلر</div>
            <div class="arvan-admin-kpi-value" style="color: #00baba;"><?php echo number_format(floatval($total_profit)); ?> <small style="font-size: 14px; font-weight: normal; color: #64748b;">تومان</small></div>
            <div class="arvan-admin-kpi-sub">محاسبه‌شده در جدول تسویه‌ها</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">کل موجودی در گردش کیف پول‌ها</div>
            <div class="arvan-admin-kpi-value" style="color: #0284c7;"><?php echo number_format(floatval($total_customer_balances)); ?> <small style="font-size: 14px; font-weight: normal; color: #64748b;">تومان</small></div>
            <div class="arvan-admin-kpi-sub">اعتبار پیش‌پرداخت امانی کاربران</div>
        </div>
    </div>

    <!-- Active Instances Table -->
    <div class="arvan-admin-card">
        <h2>🖥️ مدیریت و مانیتورینگ ابرک‌های ابری کاربران</h2>
        <div style="overflow-x: auto;">
            <table class="arvan-admin-table">
                <thead>
                    <tr>
                        <th>شناسه منبع</th>
                        <th>نام سرور</th>
                        <th>کاربر</th>
                        <th>آدرس IP</th>
                        <th>پلن سخت‌افزاری</th>
                        <th>قیمت پایه آروان</th>
                        <th>حاشیه سود</th>
                        <th>قیمت نهایی ساعتی</th>
                        <th>وضعیت</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_servers)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 30px; color: #64748b;">هیچ سروری در دیتابیس ثبت نشده است. با زدن دکمه «ایجاد و بارگذاری داده‌های دمو» نمونه سرورها بارگذاری می‌شوند.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_servers as $s): 
                            $is_act = ($s['status'] === 'ACTIVE');
                        ?>
                            <tr id="arvan_server_row_<?php echo $s['id']; ?>">
                                <td><code><?php echo esc_html($s['resource_id']); ?></code></td>
                                <td><strong id="arvan_srv_name_<?php echo $s['id']; ?>" style="color: #0f172a; font-weight: 800;"><?php echo esc_html($s['name']); ?></strong></td>
                                <td>کاربر #<?php echo esc_html($s['user_id']); ?></td>
                                <td><code style="direction: ltr;"><?php echo esc_html($s['ip_address']); ?></code></td>
                                <td><?php echo esc_html($s['flavor_name']); ?></td>
                                <td><?php echo number_format(floatval($s['hourly_base_price'])); ?> تومان</td>
                                <td><span style="color: #00baba; font-weight: 900;"><?php echo esc_html($s['reseller_margin_percent']); ?>%</span></td>
                                <td><strong style="color: #00baba; font-weight: 900;"><?php echo number_format(floatval($s['hourly_customer_price'])); ?> تومان</strong></td>
                                <td>
                                    <?php if ($is_act): ?>
                                        <span style="color: #15803d; font-weight: 800; background: #dcfce7; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px; font-size: 12px;">● فعال</span>
                                    <?php else: ?>
                                        <span style="color: #b45309; font-weight: 800; background: #fef3c7; border: 1px solid #fde68a; padding: 4px 10px; border-radius: 20px; font-size: 12px;">⏸ معلق (Suspended)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary arvan-edit-server-btn" data-id="<?php echo $s['id']; ?>" data-name="<?php echo esc_attr($s['name']); ?>" data-status="<?php echo esc_attr($s['status']); ?>" style="padding: 5px 12px; font-size: 12px;">
                                            ✏️ ویرایش
                                        </button>
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-danger arvan-delete-server-btn" data-id="<?php echo $s['id']; ?>" style="padding: 5px 12px; font-size: 12px;">
                                            🗑️ حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
