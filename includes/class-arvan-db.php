<?php
if (!defined('ABSPATH')) {
    exit;
}

class Arvan_DB {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // 1. Wallets Table
        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_wallets} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            balance DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            warning_threshold DECIMAL(14,2) NOT NULL DEFAULT 50000.00,
            currency VARCHAR(10) NOT NULL DEFAULT 'IRR',
            status VARCHAR(20) NOT NULL DEFAULT 'ACTIVE',
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_user_id (user_id),
            KEY idx_status (status)
        ) {$charset_collate};");

        // 2. Ledger Table
        $table_ledger = $wpdb->prefix . 'arvan_ledger';
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_ledger} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            wallet_id BIGINT UNSIGNED NOT NULL,
            user_id BIGINT UNSIGNED NOT NULL,
            type ENUM('DEPOSIT', 'HOURLY_BURN', 'REFUND', 'ADJUSTMENT') NOT NULL,
            amount DECIMAL(14,2) NOT NULL,
            balance_before DECIMAL(14,2) NOT NULL,
            balance_after DECIMAL(14,2) NOT NULL,
            reference_id VARCHAR(100) NULL,
            description TEXT NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_wallet_id (wallet_id),
            KEY idx_user_id (user_id),
            KEY idx_type (type),
            KEY idx_created_at (created_at)
        ) {$charset_collate};");

        // 3. Resources Table
        $table_resources = $wpdb->prefix . 'arvan_resources';
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_resources} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT UNSIGNED NOT NULL,
            resource_id VARCHAR(64) NOT NULL,
            resource_type ENUM('SERVER', 'CDN', 'STORAGE') NOT NULL,
            name VARCHAR(128) NOT NULL,
            region VARCHAR(32) NOT NULL DEFAULT 'ir-thr-at1',
            flavor_id VARCHAR(64) NOT NULL,
            flavor_name VARCHAR(64) NULL,
            specs JSON NULL,
            hourly_base_price DECIMAL(10,2) NOT NULL,
            reseller_margin_percent DECIMAL(5,2) NOT NULL DEFAULT 20.00,
            hourly_customer_price DECIMAL(10,2) NOT NULL,
            ip_address VARCHAR(45) NULL,
            status ENUM('BUILDING', 'ACTIVE', 'SUSPENDED', 'TERMINATED', 'FAILED') NOT NULL DEFAULT 'BUILDING',
            suspended_at DATETIME NULL,
            terminated_at DATETIME NULL,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY idx_resource_id (resource_id),
            KEY idx_user_id (user_id),
            KEY idx_status (status),
            KEY idx_resource_type (resource_type)
        ) {$charset_collate};");

        // 4. Settlements Table
        $table_settlements = $wpdb->prefix . 'arvan_settlements';
        $wpdb->query("CREATE TABLE IF NOT EXISTS {$table_settlements} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            period_start DATETIME NOT NULL,
            period_end DATETIME NOT NULL,
            total_burned_amount DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            provider_base_cost DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            reseller_net_profit DECIMAL(14,2) NOT NULL DEFAULT 0.00,
            active_resources_count INT NOT NULL DEFAULT 0,
            created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_period (period_start, period_end)
        ) {$charset_collate};");
    }
}
