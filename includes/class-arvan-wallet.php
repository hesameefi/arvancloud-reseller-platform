<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enterprise Double-Entry Wallet Manager with ACID Transactions & Row-Level Locking
 */
class Arvan_Wallet {

    private static $instance = null;

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get or Create User Wallet
     */
    public function get_wallet($user_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'arvan_wallets';

        $wallet = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d",
            $user_id
        ), ARRAY_A);

        if (!$wallet) {
            $wpdb->insert($table, array(
                'user_id' => $user_id,
                'balance' => 0.00,
                'warning_threshold' => 50000.00,
                'currency' => 'IRT',
                'status' => 'ACTIVE',
                'updated_at' => current_time('mysql')
            ));
            return $this->get_wallet($user_id);
        }

        return $wallet;
    }

    /**
     * Get Current Balance
     */
    public function get_balance($user_id) {
        $wallet = $this->get_wallet($user_id);
        return floatval($wallet['balance']);
    }

    /**
     * Deposit into User Wallet with ACID Transaction & Row Lock (Pessimistic Locking)
     */
    public function deposit($user_id, $amount, $reference_id = null, $description = 'شارژ آنلاین کیف پول') {
        global $wpdb;
        $amount = floatval($amount);
        if ($amount <= 0) {
            return array('success' => false, 'message' => 'مبلغ نامعتبر است.');
        }

        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_ledger = $wpdb->prefix . 'arvan_ledger';

        // 1. Begin ACID Transaction
        $wpdb->query("START TRANSACTION");

        try {
            // 2. Lock wallet row FOR UPDATE to prevent race conditions & lost updates
            $wallet = $wpdb->get_row($wpdb->prepare(
                "SELECT id, balance FROM {$table_wallets} WHERE user_id = %d FOR UPDATE",
                $user_id
            ), ARRAY_A);

            if (!$wallet) {
                // Auto create if not existing
                $wpdb->insert($table_wallets, array(
                    'user_id' => $user_id,
                    'balance' => 0.00,
                    'warning_threshold' => 50000.00,
                    'currency' => 'IRT',
                    'status' => 'ACTIVE',
                    'updated_at' => current_time('mysql')
                ));
                $wallet_id = $wpdb->insert_id;
                $balance_before = 0.00;
            } else {
                $wallet_id = $wallet['id'];
                $balance_before = floatval($wallet['balance']);
            }

            $balance_after = round($balance_before + $amount, 2);

            // 3. Update Wallet Balance
            $updated = $wpdb->update(
                $table_wallets,
                array('balance' => $balance_after, 'status' => 'ACTIVE', 'updated_at' => current_time('mysql')),
                array('id' => $wallet_id)
            );

            if ($updated === false) {
                throw new Exception('خطا در به‌روزرسانی کیف پول.');
            }

            // 4. Record in Double-Entry Ledger
            $inserted = $wpdb->insert($table_ledger, array(
                'wallet_id' => $wallet_id,
                'user_id' => $user_id,
                'type' => 'DEPOSIT',
                'amount' => $amount,
                'balance_before' => $balance_before,
                'balance_after' => $balance_after,
                'reference_id' => $reference_id ?: ('DEP-' . uniqid()),
                'description' => $description,
                'created_at' => current_time('mysql')
            ));

            if ($inserted === false) {
                throw new Exception('خطا در ثبت تراکنش در دفتر کل.');
            }

            // 5. Commit Transaction
            $wpdb->query("COMMIT");

            // Auto-Resume any suspended servers
            Arvan_Cron::get_instance()->resume_user_services($user_id);

            return array(
                'success' => true,
                'balance_before' => $balance_before,
                'balance_after' => $balance_after
            );

        } catch (Exception $e) {
            $wpdb->query("ROLLBACK");
            return array('success' => false, 'message' => $e->getMessage());
        }
    }

    /**
     * Deduct Hourly Consumption with ACID Transaction & Row Lock
     */
    public function burn_hourly($user_id, $amount, $resource_id, $description = 'کسر هزینه ساعتی مصرف سرور ابری') {
        global $wpdb;
        $amount = floatval($amount);
        if ($amount <= 0) {
            return false;
        }

        $table_wallets = $wpdb->prefix . 'arvan_wallets';
        $table_ledger = $wpdb->prefix . 'arvan_ledger';

        // 1. Begin ACID Transaction
        $wpdb->query("START TRANSACTION");

        try {
            // 2. Lock wallet row FOR UPDATE
            $wallet = $wpdb->get_row($wpdb->prepare(
                "SELECT id, balance FROM {$table_wallets} WHERE user_id = %d FOR UPDATE",
                $user_id
            ), ARRAY_A);

            if (!$wallet) {
                $wpdb->query("ROLLBACK");
                return false;
            }

            $wallet_id = $wallet['id'];
            $balance_before = floatval($wallet['balance']);
            $balance_after = round($balance_before - $amount, 2);

            // 3. Update Wallet Balance
            $wpdb->update(
                $table_wallets,
                array('balance' => $balance_after, 'updated_at' => current_time('mysql')),
                array('id' => $wallet_id)
            );

            // 4. Record in Double-Entry Ledger
            $wpdb->insert($table_ledger, array(
                'wallet_id' => $wallet_id,
                'user_id' => $user_id,
                'type' => 'HOURLY_BURN',
                'amount' => -$amount,
                'balance_before' => $balance_before,
                'balance_after' => $balance_after,
                'reference_id' => $resource_id,
                'description' => $description,
                'created_at' => current_time('mysql')
            ));

            // 5. Commit Transaction
            $wpdb->query("COMMIT");

            return $balance_after;

        } catch (Exception $e) {
            $wpdb->query("ROLLBACK");
            return false;
        }
    }

    /**
     * Get Ledger History
     */
    public function get_ledger_history($user_id, $limit = 20) {
        global $wpdb;
        $table = $wpdb->prefix . 'arvan_ledger';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$table} WHERE user_id = %d ORDER BY id DESC LIMIT %d",
            $user_id,
            $limit
        ), ARRAY_A);
    }
}
