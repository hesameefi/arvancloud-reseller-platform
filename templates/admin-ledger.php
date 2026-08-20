<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="wrap" style="direction: rtl; text-align: right; font-family: 'Vazirmatn', sans-serif;">
    <h1>📜 دفتر کل تراکنش‌های مالی سیستم (Double-Entry Ledger)</h1>
    <p style="color: #50575e; margin-bottom: 25px;">لاگ تغییرات ریالی، شارژها و کسر مبالغ مصرف ساعتی تمامی کاربران سیستم با امکان حسابرسی دقیق.</p>

    <div style="background: white; border: 1px solid #ccd0d4; border-radius: 8px; padding: 20px;">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th>ردیف</th>
                    <th>شناسه کیف پول</th>
                    <th>کاربر</th>
                    <th>نوع تراکنش</th>
                    <th>مبلغ</th>
                    <th>موجودی قبلی</th>
                    <th>موجودی پس از تراکنش</th>
                    <th>شناسه مرجع / سرور</th>
                    <th>شرح تراکنش</th>
                    <th>زمان ثبت</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 20px; color: #646970;">هیچ تراکنشی در دفتر کل ثبت نشده است.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $t): 
                        $is_deposit = ($t['type'] === 'DEPOSIT');
                    ?>
                        <tr>
                            <td>#<?php echo esc_html($t['id']); ?></td>
                            <td>کیف پول #<?php echo esc_html($t['wallet_id']); ?></td>
                            <td>کاربر #<?php echo esc_html($t['user_id']); ?></td>
                            <td>
                                <?php if ($is_deposit): ?>
                                    <span style="color: #10b981; font-weight: bold;">➕ شارژ کیف پول</span>
                                <?php else: ?>
                                    <span style="color: #ef4444; font-weight: bold;">➖ کسر مصرف ساعتی</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-weight: bold; color: <?php echo $is_deposit ? '#10b981' : '#ef4444'; ?>;">
                                <?php echo ($is_deposit ? '+' : '') . number_format(floatval($t['amount'])); ?> تومان
                            </td>
                            <td><?php echo number_format(floatval($t['balance_before'])); ?> تومان</td>
                            <td style="font-weight: bold;"><?php echo number_format(floatval($t['balance_after'])); ?> تومان</td>
                            <td><code><?php echo esc_html($t['reference_id']); ?></code></td>
                            <td><?php echo esc_html($t['description']); ?></td>
                            <td><?php echo esc_html($t['created_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
