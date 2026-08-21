<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enterprise-Grade Security & AES-256 Encryption for API Keys & Secrets
 */
class Arvan_Security {

    private static $cipher = 'aes-256-cbc';

    /**
     * Derive a 32-byte encryption key from WordPress salts
     */
    private static function get_secret_key() {
        $salt1 = defined('AUTH_KEY') ? AUTH_KEY : 'arvan-cloud-salt-key-1';
        $salt2 = defined('SECURE_AUTH_KEY') ? SECURE_AUTH_KEY : 'arvan-cloud-salt-key-2';
        return hash('sha256', $salt1 . $salt2 . 'arvan_reseller_secure_vault', true);
    }

    /**
     * Encrypt Plaintext String into Base64-encoded IV + Ciphertext
     */
    public static function encrypt($plaintext) {
        if (empty($plaintext)) {
            return '';
        }

        // If already encrypted prefix is present, return as is
        if (strpos($plaintext, 'enc::') === 0) {
            return $plaintext;
        }

        if (!extension_loaded('openssl')) {
            // Fallback base64 encode if openssl missing
            return 'enc::b64::' . base64_encode($plaintext);
        }

        $key = self::get_secret_key();
        $ivlen = openssl_cipher_iv_length(self::$cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);

        return 'enc::' . base64_encode($iv . $hmac . $ciphertext_raw);
    }

    /**
     * Decrypt Ciphertext String back to Plaintext
     */
    public static function decrypt($encrypted_text) {
        if (empty($encrypted_text)) {
            return '';
        }

        // If not encrypted, return as is (for backwards compatibility with existing plain keys)
        if (strpos($encrypted_text, 'enc::') !== 0) {
            return $encrypted_text;
        }

        // Check fallback b64
        if (strpos($encrypted_text, 'enc::b64::') === 0) {
            return base64_decode(substr($encrypted_text, 10));
        }

        $raw_data = base64_decode(substr($encrypted_text, 5));
        $key = self::get_secret_key();
        $ivlen = openssl_cipher_iv_length(self::$cipher);

        $iv = substr($raw_data, 0, $ivlen);
        $hmac = substr($raw_data, $ivlen, 32);
        $ciphertext_raw = substr($raw_data, $ivlen + 32);

        $calc_hmac = hash_hmac('sha256', $ciphertext_raw, $key, true);
        if (!hash_equals($hmac, $calc_hmac)) {
            return ''; // Integrity check failed / tampered
        }

        $decrypted = openssl_decrypt($ciphertext_raw, self::$cipher, $key, OPENSSL_RAW_DATA, $iv);
        return ($decrypted !== false) ? $decrypted : '';
    }

    /**
     * Mask API Key for safe UI display (e.g. Apikey ********************3a8f)
     */
    public static function mask_key($key) {
        if (empty($key)) {
            return '';
        }
        $decrypted = self::decrypt($key);
        $len = strlen($decrypted);
        if ($len <= 8) {
            return '••••••••';
        }
        $prefix = substr($decrypted, 0, 6);
        $suffix = substr($decrypted, -4);
        return $prefix . str_repeat('•', max(8, $len - 10)) . $suffix;
    }
}
