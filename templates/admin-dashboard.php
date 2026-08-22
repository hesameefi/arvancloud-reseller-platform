<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<script>
(function() {
    var theme = localStorage.getItem('arvan_admin_theme') || 'dark';
    if (theme === 'dark') {
        document.body.classList.add('arvan-admin-dark-theme');
    }
})();
</script>

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
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 14px;">
            <h2 style="margin: 0; border: none; padding: 0;">🖥️ مدیریت و مانیتورینگ ابرک‌های ابری کاربران</h2>
            <span style="font-size: 12.5px; color: #94a3b8; font-weight: 700;">
                مجموع: <?php echo count($recent_servers); ?> ابرک فعال و معلق
            </span>
        </div>

        <div class="arvan-table-responsive-wrapper">
            <table class="arvan-admin-table">
                <thead>
                    <tr>
                        <th style="width: 155px; min-width: 155px;">شناسه منبع</th>
                        <th style="min-width: 180px;">نام سرور</th>
                        <th style="width: 145px; min-width: 145px;">کاربر / مشترک</th>
                        <th style="width: 145px; min-width: 145px;">آدرس IP</th>
                        <th style="min-width: 200px;">پلن سخت‌افزاری</th>
                        <th style="width: 125px; min-width: 125px;">قیمت پایه آروان</th>
                        <th style="width: 90px; min-width: 90px;">حاشیه سود</th>
                        <th style="width: 130px; min-width: 130px;">قیمت نهایی</th>
                        <th style="width: 105px; min-width: 105px; text-align: center;">وضعیت</th>
                        <th style="width: 160px; min-width: 160px; text-align: center;">عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recent_servers)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 40px; color: #64748b; font-size: 14px;">
                                ☁️ هیچ سروری در دیتابیس ثبت نشده است. با کلیک روی دکمه «ایجاد و بارگذاری داده‌های دمو» نمونه سرورها بارگذاری می‌شوند.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_servers as $s): 
                            $is_act = ($s['status'] === 'ACTIVE');
                            
                            // User display formatting
                            $uid = intval($s['user_id']);
                            if ($uid === 1) {
                                $user_label = 'مدیر کل (حسام)';
                                $user_style = 'background: rgba(0, 186, 186, 0.15); color: #00e0e0; border-color: rgba(0, 186, 186, 0.3);';
                            } elseif ($uid === 2) {
                                $user_label = 'سارا ابری #2';
                                $user_style = 'background: rgba(99, 102, 241, 0.15); color: #a5b4fc; border-color: rgba(99, 102, 241, 0.3);';
                            } elseif ($uid === 3) {
                                $user_label = 'داده‌ورزان #3';
                                $user_style = 'background: rgba(245, 158, 11, 0.15); color: #fbbf24; border-color: rgba(245, 158, 11, 0.3);';
                            } elseif ($uid === 4) {
                                $user_label = 'علی رضایی #4';
                                $user_style = 'background: rgba(16, 185, 129, 0.15); color: #34d399; border-color: rgba(16, 185, 129, 0.3);';
                            } else {
                                $user_label = 'مشتری #' . $uid;
                                $user_style = 'background: rgba(148, 163, 184, 0.15); color: #cbd5e1; border-color: rgba(148, 163, 184, 0.3);';
                            }
                        ?>
                            <tr id="arvan_server_row_<?php echo $s['id']; ?>">
                                <td style="white-space: nowrap;">
                                    <code class="arvan-res-id-badge"><?php echo esc_html($s['resource_id']); ?></code>
                                </td>
                                <td style="white-space: nowrap;">
                                    <strong id="arvan_srv_name_<?php echo $s['id']; ?>" class="arvan-srv-title"><?php echo esc_html($s['name']); ?></strong>
                                </td>
                                <td style="white-space: nowrap;">
                                    <span class="arvan-user-tag" style="<?php echo $user_style; ?>">
                                        👤 <?php echo esc_html($user_label); ?>
                                    </span>
                                </td>
                                <td style="white-space: nowrap;">
                                    <code class="arvan-ip-badge"><?php echo esc_html($s['ip_address']); ?></code>
                                </td>
                                <td style="white-space: nowrap; font-weight: 600; color: #cbd5e1;"><?php echo esc_html($s['flavor_name']); ?></td>
                                <td style="white-space: nowrap; font-weight: 600; color: #94a3b8;"><?php echo number_format(floatval($s['hourly_base_price'])); ?> تومان</td>
                                <td style="white-space: nowrap;"><span class="arvan-margin-badge"><?php echo esc_html($s['reseller_margin_percent']); ?>%</span></td>
                                <td style="white-space: nowrap;"><strong class="arvan-price-highlight"><?php echo number_format(floatval($s['hourly_customer_price'])); ?> تومان</strong></td>
                                <td style="white-space: nowrap; text-align: center;">
                                    <?php if ($is_act): ?>
                                        <span class="arvan-status-pill arvan-status-active">
                                            <span class="arvan-pulse-dot arvan-dot-green"></span>
                                            فعال
                                        </span>
                                    <?php else: ?>
                                        <span class="arvan-status-pill arvan-status-suspended">
                                            <span class="arvan-pulse-dot arvan-dot-amber"></span>
                                            معلق
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space: nowrap; text-align: center;">
                                    <div class="arvan-action-buttons-wrap">
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary arvan-edit-server-btn" data-id="<?php echo $s['id']; ?>" data-name="<?php echo esc_attr($s['name']); ?>" data-status="<?php echo esc_attr($s['status']); ?>" title="ویرایش سرور">
                                            ✏️ ویرایش
                                        </button>
                                        <button type="button" class="arvan-btn-admin arvan-btn-admin-danger arvan-delete-server-btn" data-id="<?php echo $s['id']; ?>" title="حذف سرور">
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
