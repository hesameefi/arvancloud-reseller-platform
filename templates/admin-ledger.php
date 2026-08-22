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
            <span style="font-size: 32px;">📜</span>
            <div>
                <h1>دفتر کل تراکنش‌های مالی سیستم (Double-Entry Ledger)</h1>
                <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; font-weight: 500;">ثبت دقیق و غیرقابل دستکاری تراکنش‌های شارژ کیف پول و کسر مصرف ساعتی بر اساس استانداردهای حسابداری دوطرفه.</p>
            </div>
        </div>
        <div style="display: flex; align-items: center; gap: 10px;">
            <button type="button" class="arvan-btn-admin arvan-btn-admin-secondary" id="arvan_admin_theme_toggle">
                🌙 تم تاریک
            </button>
            <button type="button" class="arvan-btn-admin arvan-btn-admin-primary" onclick="exportAdminTableToExcel('arvan_admin_financial_ledger.xls');">
                📊 دانلود اکسل رسمی چندرنگ (Excel .XLS)
            </button>
        </div>
    </div>

    <!-- Table Card -->
    <div class="arvan-admin-card">
        <div style="overflow-x: auto;">
            <table class="arvan-admin-table" id="arvan_admin_ledger_table">
                <thead>
                    <tr>
                        <th style="width: 70px;">ردیف</th>
                        <th style="width: 120px;">شناسه کیف پول</th>
                        <th style="width: 100px;">کاربر</th>
                        <th style="width: 160px;">نوع تراکنش</th>
                        <th style="width: 140px;">مبلغ تراکنش</th>
                        <th style="width: 140px;">موجودی قبل</th>
                        <th style="width: 160px;">موجودی پس از تراکنش</th>
                        <th style="width: 160px;">شناسه مرجع / سرور</th>
                        <th>شرح عملیات</th>
                        <th style="width: 160px;">زمان ثبت</th>
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
                                <td><span style="color: #64748b; font-weight: 700;">#<?php echo esc_html($t['id']); ?></span></td>
                                <td style="white-space: nowrap; font-weight: 700; color: #334155;">کیف پول #<?php echo esc_html($t['wallet_id']); ?></td>
                                <td style="white-space: nowrap; font-weight: 700; color: #334155;">کاربر #<?php echo esc_html($t['user_id']); ?></td>
                                <td style="white-space: nowrap;">
                                    <?php if ($is_deposit): ?>
                                        <span class="arvan-badge-deposit" style="color: #15803d; font-weight: 800; background: #dcfce7; border: 1px solid #bbf7d0; padding: 4px 10px; border-radius: 20px; font-size: 12px;">➕ شارژ کیف پول</span>
                                    <?php else: ?>
                                        <span class="arvan-badge-usage" style="color: #b91c1c; font-weight: 800; background: #fee2e2; border: 1px solid #fecaca; padding: 4px 10px; border-radius: 20px; font-size: 12px;">➖ کسر مصرف ساعتی</span>
                                    <?php endif; ?>
                                </td>
                                <td style="white-space: nowrap;">
                                    <strong style="color: <?php echo $is_deposit ? '#15803d' : '#b91c1c'; ?>; font-weight: 900; font-size: 14px;">
                                        <?php echo ($is_deposit ? '+' : '') . number_format(floatval($t['amount'])); ?> تومان
                                    </strong>
                                </td>
                                <td style="white-space: nowrap; color: #475569; font-weight: 600;"><?php echo number_format(floatval($t['balance_before'])); ?> تومان</td>
                                <td style="white-space: nowrap;"><strong style="color: #0f172a; font-weight: 800;"><?php echo number_format(floatval($t['balance_after'])); ?> تومان</strong></td>
                                <td style="white-space: nowrap;"><code><?php echo esc_html($t['reference_id']); ?></code></td>
                                <td style="color: #1e293b; font-weight: 600;"><?php echo esc_html($t['description']); ?></td>
                                <td dir="ltr" style="white-space: nowrap; font-size: 12.5px; color: #64748b; font-weight: 600;"><?php echo esc_html($t['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportAdminTableToExcel(filename) {
    var table = document.getElementById("arvan_admin_ledger_table");
    if (!table) return;

    // Clone table to format for Excel
    var clone = table.cloneNode(true);

    var excelContent = `
    <html xmlns:o="urn:schemas-microsoft-com:office:office" 
          xmlns:x="urn:schemas-microsoft-com:office:excel" 
          xmlns="http://www.w3.org/TR/REC-html40">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <!--[if gte mso 9]>
        <xml>
            <x:ExcelWorkbook>
                <x:ExcelWorksheets>
                    <x:ExcelWorksheet>
                        <x:Name>دفتر کل مالی</x:Name>
                        <x:WorksheetOptions>
                            <x:DisplayRightToLeft/>
                            <x:DoNotDisplayGridlines/>
                        </x:WorksheetOptions>
                    </x:ExcelWorksheet>
                </x:ExcelWorksheets>
            </x:ExcelWorkbook>
        </xml>
        <![endif]-->
        <style>
            body { font-family: 'Segoe UI', Tahoma, Arial, sans-serif; direction: rtl; }
            .header-banner { background-color: #003434; color: #00baba; font-size: 16pt; font-weight: bold; text-align: center; height: 45px; vertical-align: middle; border: 1px solid #002222; }
            .sub-banner { background-color: #054e4e; color: #ffffff; font-size: 10pt; text-align: center; height: 25px; vertical-align: middle; }
            table { border-collapse: collapse; width: 100%; direction: rtl; }
            th { background-color: #1e293b; color: #f59e0b; font-weight: bold; font-size: 11pt; border: 1px solid #334155; padding: 10px; text-align: center; height: 35px; vertical-align: middle; }
            td { border: 1px solid #cbd5e1; padding: 8px 12px; font-size: 10pt; vertical-align: middle; text-align: right; }
            .bg-even { background-color: #f8fafc; }
            .bg-odd { background-color: #ffffff; }
            .deposit-cell { color: #15803d; font-weight: bold; background-color: #dcfce7; text-align: center; }
            .usage-cell { color: #b91c1c; font-weight: bold; background-color: #fee2e2; text-align: center; }
            .num-cell { mso-number-format:"\#\,\#\#0"; text-align: left; direction: ltr; font-weight: 600; }
            .code-cell { font-family: Consolas, monospace; color: #0284c7; }
            .date-cell { direction: ltr; text-align: center; font-size: 9pt; color: #64748b; }
        </style>
    </head>
    <body dir="rtl">
        <table>
            <tr>
                <td colspan="10" class="header-banner">☁️ گزارش رسمی دفتر کل مالی ابر آروان (ArvanCloud Financial Ledger)</td>
            </tr>
            <tr>
                <td colspan="10" class="sub-banner">تاریخ صدور: ${new Date().toLocaleDateString('fa-IR')} | تراکنش‌های قطعی حسابداری دوطرفه</td>
            </tr>
            <tr><td colspan="10" style="height: 15px; border: none;"></td></tr>
        </table>
        ${clone.outerHTML}
    </body>
    </html>
    `;

    var blob = new Blob(["\ufeff" + excelContent], { type: "application/vnd.ms-excel;charset=utf-8" });
    var link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename || "arvan_admin_financial_ledger.xls";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
