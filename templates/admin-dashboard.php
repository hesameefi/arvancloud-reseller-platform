<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="arvan-admin-wrap">
    <!-- Header -->
    <div class="arvan-admin-header">
        <div class="arvan-admin-title">
            <span style="font-size: 28px;">☁️</span>
            <div>
                <h1>
                    پنل مدیریت ریسلری خدمات ابر آروان
                    <span class="arvan-version-badge">Enterprise v1.2</span>
                </h1>
                <span style="font-size: 12.5px; color: #94a3b8;">مانیتورینگ پیشرفته ابرک‌های مشتریان، مدیریت کران‌جاب‌های مصرف ساعتی و تسویه‌حساب خودکار سود ریسلری.</span>
            </div>
        </div>
        <div style="display: flex; gap: 10px;">
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
        <div style="font-size: 12.5px; color: #94a3b8;">
            وضعیت لجر: <strong style="color: #10b981;">ACID Safe</strong> | حاشیه سود فعال: <strong style="color: #00baba;"><?php echo esc_html(get_option('arvan_reseller_margin', 20)); ?>%</strong>
        </div>
    </div>

    <!-- KPI Stats Bento Grid -->
    <div class="arvan-admin-kpi-grid">
        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">ابرک‌های فعال مشتریان</div>
            <div class="arvan-admin-kpi-value" style="color: #10b981;"><?php echo intval($total_active_servers); ?> <small style="font-size: 13px; font-weight: normal; color: #64748b;">سرور</small></div>
            <div class="arvan-admin-kpi-sub">وضعیت آنلاین و در حال مصرف</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">ابرک‌های معلق (Suspended)</div>
            <div class="arvan-admin-kpi-value" style="color: #f59e0b;"><?php echo intval($total_suspended); ?> <small style="font-size: 13px; font-weight: normal; color: #64748b;">سرور</small></div>
            <div class="arvan-admin-kpi-sub">به علت کسری اعتبار کیف پول</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">مجموع سود خالص ریسلر</div>
            <div class="arvan-admin-kpi-value" style="color: #00baba;"><?php echo number_format(floatval($total_profit)); ?> <small style="font-size: 13px; font-weight: normal; color: #64748b;">تومان</small></div>
            <div class="arvan-admin-kpi-sub">محاسبه‌شده در جدول تسویه‌ها</div>
        </div>

        <div class="arvan-admin-kpi-card">
            <div class="arvan-admin-kpi-title">کل موجودی در گردش کیف پول‌ها</div>
            <div class="arvan-admin-kpi-value" style="color: #38bdf8;"><?php echo number_format(floatval($total_customer_balances)); ?> <small style="font-size: 13px; font-weight: normal; color: #64748b;">تومان</small></div>
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
                                <td><strong id="arvan_srv_name_<?php echo $s['id']; ?>" style="color: #f8fafc;"><?php echo esc_html($s['name']); ?></strong></td>
                                <td>کاربر #<?php echo esc_html($s['user_id']); ?></td>
                                <td><code style="direction: ltr;"><?php echo esc_html($s['ip_address']); ?></code></td>
                                <td><?php echo esc_html($s['flavor_name']); ?></td>
                                <td style="font-family: 'JetBrains Mono', monospace;"><?php echo number_format(floatval($s['hourly_base_price'])); ?> تومان</td>
                                <td><span style="color: #00baba; font-weight: 800;"><?php echo esc_html($s['reseller_margin_percent']); ?>%</span></td>
                                <td><strong style="color: #00baba; font-family: 'JetBrains Mono', monospace;"><?php echo number_format(floatval($s['hourly_customer_price'])); ?> تومان</strong></td>
                                <td>
                                    <?php if ($is_act): ?>
                                        <span style="color: #10b981; font-weight: 800; background: rgba(16, 185, 129, 0.15); padding: 3px 8px; border-radius: 4px;">● فعال</span>
                                    <?php else: ?>
                                        <span style="color: #f59e0b; font-weight: 800; background: rgba(245, 158, 11, 0.15); padding: 3px 8px; border-radius: 4px;">⏸ معلق (Suspended)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary arvan-edit-server-btn" data-id="<?php echo $s['id']; ?>" data-name="<?php echo esc_attr($s['name']); ?>" data-status="<?php echo esc_attr($s['status']); ?>" style="padding: 4px 10px; font-size: 12px;">
                                            ✏️ ویرایش
                                        </button>
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-danger arvan-delete-server-btn" data-id="<?php echo $s['id']; ?>" style="padding: 4px 10px; font-size: 12px;">
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
