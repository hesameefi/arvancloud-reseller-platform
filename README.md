# ☁️ ArvanCloud Reseller Pro — WordPress Plugin (Zero-Dependency)
> **Enterprise-grade, Zero-Dependency WordPress plugin for reselling ArvanCloud products (IaaS, CDN, Object Storage) with Sorkhab UI and Double-Entry Ledger.**

[![License: MIT](https://img.shields.io/badge/License-MIT-teal.svg)](https://opensource.org/licenses/MIT)
[![WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0%2B-purple.svg)](https://php.net)
[![ArvanCloud API](https://img.shields.io/badge/ArvanCloud-REST%20API-00baba.svg)](https://www.arvancloud.ir)
[![Zero-Dependency](https://img.shields.io/badge/Zero--Dependency-WooCommerce%20Free-success.svg)](#architecture)

---

## 🌐 زبان‌ها / Languages
* [English Documentation](#-english-overview)
* [مستندات فارسی](#-معرفی-پروژه-به-فارسی)

---

## 🇬🇧 English Overview

### 🌟 Key Architectural Features
1. **100% Zero-Dependency:**
   - Operates completely standalone with **NO WooCommerce, NO ACF, and NO theme dependencies**.
   - Direct MySQL relational engine using 4 custom tables with dedicated B-Tree indexes:
     - `wp_arvan_wallets`: Pre-paid virtual wallet balances and safety thresholds.
     - `wp_arvan_ledger`: Double-entry accounting tracking `balance_before` and `balance_after` for every deposit and hourly consumption.
     - `wp_arvan_resources`: Provisioned cloud servers, hardware specs, dedicated IPs, and region mapping.
     - `wp_arvan_settlements`: Automated settlement logs calculating provider base cost vs. reseller margin.

2. **🎨 Official Sorkhab Design System (Sorkhab UI Kit):**
   - Built with ArvanCloud's official teal `#00BABA` branding, deep cloud dark mode, and Vazirmatn Persian typography.
   - 100% responsive layout across mobile and desktop.

3. **💰 Dynamic Reseller Margin Engine:**
   - Adjustable margin slider from **0% to 20%** in the admin settings.
   - Real-time hourly cost calculation and 30-day monthly estimation in the client wizard.

4. **⏱️ Hourly Cron Burn Rate & 5-Stage Lifecycle Suspension:**
   - Automated hourly cron job that reads active server consumption, applies reseller markup, and deducts from pre-paid virtual wallets.
   - Instant automated **Suspension (Power-Off)** upon reaching zero balance, with automatic resume upon wallet top-up.

5. **🤖 AI Cloud Advisor & Multi-Product Expansion:**
   - Built-in AI recommendation engine suggesting optimal vCPU/RAM/NVMe specs based on website workload.
   - Modular tabs for **CDN Domain Management (with Edge Purge Cache)** and **S3-Compatible Object Storage Buckets**.
   - Live Web Terminal console modal and One-click CSV Export for financial auditing.

---

### 🚀 Quick Start & Installation
1. Clone or download the repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone https://github.com/hesameefi/arvan-reseller-plugin.git arvan-reseller
   ```
2. Activate the plugin from **WordPress Dashboard -> Plugins**.
3. Insert the shortcode `[arvan_cloud_dashboard]` into any page or set it as your homepage.
4. Configure your API Key and profit margin in **ArvanCloud Reseller -> Settings & Margin**.

---

## 🇮🇷 معرفی پروژه (به زبان فارسی)

پلاگین جامع، مستقل و پیشرفته‌ی نمایندگی فروش (ریسلری) محصولات ابری **ابر آروان (ArvanCloud)** برای سیستم مدیریت محتوای وردپرس، توسعه داده شده بر پایه معماری **Zero-Dependency** و دیزاین سیستم رسمی **سرخ‌آب (Sorkhab UI Kit)** جهت شرکت در رویداد استارکوچ (StarCoach Hackathon 1405).

### 🌟 ویژگی‌ها و مزایای برجسته فنی
1. **⚡ استقلال کامل و بدون وابستگی (Zero-Dependency):**
   - بدون نیاز به ووکامرس، ACF یا قالب‌های جانبی.
   - ایجاد خودکار ۴ جدول اختصاصی با ایندکس B-Tree در پایگاه داده دیتابیس MySQL جهت مقیاس‌پذیری ۱ میلیون کاربر.
2. **💳 معماری کیف پول پیش‌پرداخت و دفتر کل دوبل:**
   - مدیریت کیف پول مجازی با ثبت دقیق موجودی قبل و بعد از هر شارژ و کسر هزینه در جدول لجر.
3. **💰 حاشیه سود پویا (۰ تا ۲۰ درصد):**
   - امکان تعیین سهم سود ریسلر در پنل مدیریت و اعمال اتوماتیک روی نرخ ساعتی و ماهانه.
4. **⏱️ کران‌جاب ساعتی و چرخه ۵ مرحله‌ای قطع و تعلیق:**
   - کسر خودکار مصرف ساعتی، تعلیق و خاموش‌سازی خودکار سرور در صورت صفر شدن موجودی و فعال‌سازی مجدد پس از افزایش اعتبار.
5. **🤖 دستیار هوش مصنوعی و پوشش چندمحصولی:**
   - دستیار هوشمند پیشنهاد پلن سرور (AI Advisor)، مدیریت CDN و فضای ذخیره‌سازی S3 Storage، کنسول وب ترمینال لینوکس و خروجی اکسل/CSV.

---

## 🏗️ ساختار دیتابیس و جداول اختصاصی (Database Schema)

```sql
-- ۱. جدول کیف پول پیش‌پرداخت کاربران
CREATE TABLE wp_arvan_wallets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    balance DECIMAL(14, 2) DEFAULT 0.00,
    warning_threshold DECIMAL(14, 2) DEFAULT 50000.00,
    currency VARCHAR(10) DEFAULT 'IRT',
    updated_at DATETIME,
    INDEX idx_user (user_id)
);

-- ۲. جدول دفتر کل مالی دوطرفه
CREATE TABLE wp_arvan_ledger (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    wallet_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(30) NOT NULL,
    amount DECIMAL(14, 2) NOT NULL,
    balance_before DECIMAL(14, 2) NOT NULL,
    balance_after DECIMAL(14, 2) NOT NULL,
    reference_id VARCHAR(100),
    description TEXT,
    created_at DATETIME,
    INDEX idx_wallet_created (wallet_id, created_at)
);

-- ۳. جدول منابع و سرورهای ابری
CREATE TABLE wp_arvan_resources (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    resource_id VARCHAR(100) NOT NULL UNIQUE,
    resource_type VARCHAR(30) DEFAULT 'SERVER',
    name VARCHAR(150),
    region VARCHAR(50),
    flavor_id VARCHAR(50),
    flavor_name VARCHAR(150),
    specs JSON,
    hourly_base_price DECIMAL(10, 2),
    reseller_margin_percent DECIMAL(5, 2),
    hourly_customer_price DECIMAL(10, 2),
    ip_address VARCHAR(45),
    status VARCHAR(30) DEFAULT 'ACTIVE',
    created_at DATETIME,
    INDEX idx_user_status (user_id, status)
);

-- ۴. جدول تسویه حساب دوره‌ای سود ریسلر
CREATE TABLE wp_arvan_settlements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    period_start DATETIME,
    period_end DATETIME,
    total_burned_amount DECIMAL(14, 2),
    provider_base_cost DECIMAL(14, 2),
    reseller_net_profit DECIMAL(14, 2),
    active_resources_count INT,
    created_at DATETIME
);
```

---

## 👨‍💻 Tech Stack
* **Core:** PHP 8.1+, WordPress Core API, MySQL 8.0 (Custom InnoDB Tables with B-Tree indexes)
* **Frontend:** Vanilla JavaScript, Sorkhab Design System (CSS3 Tokens, Vazirmatn Typography)
* **API Integration:** ArvanCloud REST API (ECC IaaS, CDN, Object Storage, User API) with Hybrid Mock Sandbox Engine

---
**Developed with ❤️ for StarCoach Hackathon 1405**
