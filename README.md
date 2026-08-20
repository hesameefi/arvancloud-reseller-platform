# ☁️ ArvanCloud Reseller Pro — WordPress Plugin (Zero-Dependency)

پلاگین جامع، مستقل و پیشرفته‌ی نمایندگی فروش (ریسلری) محصولات ابری **ابر آروان (ArvanCloud)** برای وردپرس، توسعه داده شده بر پایه معماری **Zero-Dependency** و دیزاین سیستم رسمی **سرخ‌آب (Sorkhab UI Kit)**.

---

## 🌟 ویژگی‌های کلیدی و برجسته (Core Features)

1. **⚡ استقلال کامل و بدون وابستگی (100% Zero-Dependency):**
   * بدون هیچ‌گونه وابستگی به ووکامرس (WooCommerce)، افزونه ACF، یا قالب‌های خاص وردپرس.
   * ایجاد خودکار ۴ جدول اختصاصی با ایندکس‌های استاندارد B-Tree در پایگاه داده MySQL:
     * `wp_arvan_wallets`: مدیریت موجودی کیف پول پیش‌پرداخت کاربران
     * `wp_arvan_ledger`: دفتر کل مالی دوطرفه (Double-Entry Accounting) با ثبت تغییرات موجودی قبل و بعد از تراکنش
     * `wp_arvan_resources`: مدیریت سرورهای ابری، مشخصات سخت‌افزاری، IP اختصاصی و دیتاسنترها
     * `wp_arvan_settlements`: ثبت دوره‌ای سهم سود ریسلر و تسویه با ابر آروان

2. **🎨 دیزاین سیستم سرخ‌آب (Sorkhab UI / UX):**
   * پیاده‌سازی کامل پالت رنگی سازمانی آروان (`#00BABA`) و حالت دارک‌مود ابری.
   * تایپوگرافی استاندارد با فونت وزیرمتن و طراحی ۱۰۰٪ واکنش‌گرا (Responsive) برای موبایل و دسکتاپ.

3. **💰 موتور قیمت‌گذاری و مدیریت حاشیه سود (Margin Engine):**
   * امکان تعیین حاشیه سود پویا از ۰ تا ۲۰ درصد در پنل مدیریت وردپرس.
   * محاسبه آنی تعرفه ساعتی و برآورد ماهانه در ویزارد ساخت سرور.

4. **⏱️ حسابداری دوره‌ای و چرخه ۵ مرحله‌ای قطع سرویس (Hourly Cron Engine):**
   * کران‌جاب ساعتی جهت کسر هزینه مصرف از کیف پول پیش‌پرداخت.
   * تعلیق خودکار سرورها (`Power-Off / Suspend`) به محض اتمام موجودی و بازگردانی آنی پس از شارژ.

5. **🤖 دستیار هوشمند هوش مصنوعی و پشتیبانی چندمحصولی:**
   * دستیار هوشمند انتخاب پلن سرور متناسب با نوع بار کاری (AI Cloud Advisor).
   * پنل مدیریت دامنه‌های CDN و فضاهای ذخیره‌سازی S3 Storage.
   * خروجی اکسل/CSV ریزتراکنش‌های دفتر کل با یک کلیک.

---

## 🚀 راهنمای نصب و راه‌اندازی (Installation)

1. پوشه `arvan-reseller` را در مسیر `wp-content/plugins/` وردپرس قرار دهید.
2. از بخش «افزونه‌ها» در پیشخوان وردپرس، افزونه **«ابر آروان ریسلر»** را فعال کنید.
3. کد کوتاه `[arvan_cloud_dashboard]` را در هر برگه‌ای از وب‌سایت قرار دهید.
4. از منوی «ابر آروان ریسلر -> تنظیمات و کارمزد»، کلید API و درصد سود خود را مشخص فرمایید.

---

## 🛠️ استک فنی (Tech Stack)
* **Backend:** PHP 8.1+, WordPress Plugin API, Custom MySQL Database Schema, WP-Cron Engine
* **Frontend:** Vanilla JavaScript (ES6+), Sorkhab UI Design Tokens, CSS3 Flexbox/Grid
* **Architecture:** Zero-Dependency, Double-Entry Ledger, Hybrid REST API Client (Real / Mock Sandbox)
* **Scalability:** Optimized for 1M+ Concurrent Users with indexed relational queries and asynchronous processing.

---
**توسعه داده شده برای رویداد استارکوچ (StarCoach Hackathon 1405)**
