<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="arvan-admin-wrap">
    <!-- Header -->
    <div class="arvan-admin-header">
        <div class="arvan-admin-title">
            <span style="font-size: 28px;">📜</span>
            <div>
                <h1>دفتر کل تراکنش‌های مالی سیستم (Double-Entry Ledger)</h1>
                <span style="font-size: 12.5px; color: #94a3b8;">ثبت دقیق و غیرقابل دستکاری تراکنش‌های شارژ کیف پول و کسر مصرف ساعتی بر اساس استانداردهای حسابداری دوطرفه.</span>
            </div>
        </div>
        <div>
            <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary" onclick="exportAdminTableToCSV('arvan_admin_ledger.csv');">
                📥 دانلود فایل اکسل (CSV)
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="arvan-admin-card">
        <div style="overflow-x: auto;">
            <table class="arvan-admin-table" id="arvan_admin_ledger_table">
                <thead>
                    <tr>
                        <th>ردیف</th>
                        <th>شناسه کیف پول</th>
                        <th>کاربر</th>
                        <th>نوع تراکنش</th>
                        <th>مبلغ تراکنش</th>
                        <th>موجودی قبل</th>
                        <th>موجودی پس از تراکنش</th>
                        <th>شناسه مرجع / سرور</th>
                        <th>شرح عملیات</th>
                        <th>زمان ثبت</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 30px; color: #64748b;">هیچ تراکنشی در دفتر کل ثبت نشده است.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $t): 
                            $is_deposit = ($t['type'] === 'DEPOSIT');
                        ?>
                            <tr>
                                <td><span style="color: #64748b; font-family: 'JetBrains Mono', monospace;">#<?php echo esc_html($t['id']); ?></span></td>
                                <td>کیف پول #<?php echo esc_html($t['wallet_id']); ?></td>
                                <td>کاربر #<?php echo esc_html($t['user_id']); ?></td>
                                <td>
                                    <?php if ($is_deposit): ?>
                                        <span style="color: #10b981; font-weight: 800; background: rgba(16, 185, 129, 0.15); padding: 3px 8px; border-radius: 4px;">➕ شارژ کیف پول</span>
                                    <?php else: ?>
                                        <span style="color: #ef4444; font-weight: 800; background: rgba(239, 68, 68, 0.15); padding: 3px 8px; border-radius: 4px;">➖ کسر مصرف ساعتی</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong style="color: <?php echo $is_deposit ? '#10b981' : '#ef4444'; ?>; font-family: 'JetBrains Mono', monospace;">
                                        <?php echo ($is_deposit ? '+' : '') . number_format(floatval($t['amount'])); ?> تومان
                                    </strong>
                                </td>
                                <td style="font-family: 'JetBrains Mono', monospace;"><?php echo number_format(floatval($t['balance_before'])); ?> تومان</td>
                                <td><strong style="color: #f8fafc; font-family: 'JetBrains Mono', monospace;"><?php echo number_format(floatval($t['balance_after'])); ?> تومان</strong></td>
                                <td><code><?php echo esc_html($t['reference_id']); ?></code></td>
                                <td><?php echo esc_html($t['description']); ?></td>
                                <td dir="ltr" style="font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #94a3b8;"><?php echo esc_html($t['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportAdminTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("#arvan_admin_ledger_table tr");
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
