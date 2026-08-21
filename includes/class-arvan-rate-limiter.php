<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Intelligent Rate Limiter, Consumption Tracker & API Quota Guard
 */
class Arvan_Rate_Limiter {

    private static $instance = null;
    private $max_requests_per_minute = 60;
    private $burst_limit = 15; // Max concurrent per 5-second window

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $custom_rpm = get_option('arvan_rate_limit_rpm', 60);
        $this->max_requests_per_minute = intval($custom_rpm) > 0 ? intval($custom_rpm) : 60;
    }

    /**
     * Check if a request is allowed under current rate limits
     * Returns array('allowed' => bool, 'remaining' => int, 'retry_after' => int)
     */
    public function check_limit($key_identifier = 'default') {
        $transient_key = 'arvan_rl_' . md5($key_identifier);
        $now = time();
        $window = 60; // 60 seconds sliding window

        $history = get_transient($transient_key);
        if (!is_array($history)) {
            $history = array();
        }

        // Clean timestamps older than 60 seconds
        $valid_history = array();
        foreach ($history as $ts) {
            if ($now - $ts < $window) {
                $valid_history[] = $ts;
            }
        }

        $current_count = count($valid_history);
        $remaining = max(0, $this->max_requests_per_minute - $current_count);

        if ($current_count >= $this->max_requests_per_minute) {
            $oldest = reset($valid_history);
            $retry_after = max(1, $window - ($now - $oldest));
            $this->record_stat('throttled');
            return array(
                'allowed' => false,
                'remaining' => 0,
                'retry_after' => $retry_after,
                'current_rpm' => $current_count,
                'max_rpm' => $this->max_requests_per_minute
            );
        }

        // Add current timestamp and save back
        $valid_history[] = $now;
        set_transient($transient_key, $valid_history, $window);

        $this->record_stat('success');

        return array(
            'allowed' => true,
            'remaining' => $remaining - 1,
            'retry_after' => 0,
            'current_rpm' => $current_count + 1,
            'max_rpm' => $this->max_requests_per_minute
        );
    }

    /**
     * Record usage statistics in persistent option
     */
    public function record_stat($type = 'success', $latency_ms = 0) {
        $today = date('Y-m-d');
        $stats = get_option('arvan_api_usage_stats', array());

        if (!isset($stats['total_requests'])) {
            $stats['total_requests'] = 0;
            $stats['total_throttled'] = 0;
            $stats['daily'] = array();
        }

        $stats['total_requests']++;
        if ($type === 'throttled') {
            $stats['total_throttled']++;
        }

        if (!isset($stats['daily'][$today])) {
            $stats['daily'][$today] = array(
                'requests' => 0,
                'throttled' => 0,
                'date' => $today
            );
        }

        $stats['daily'][$today]['requests']++;
        if ($type === 'throttled') {
            $stats['daily'][$today]['throttled']++;
        }

        $stats['last_request_time'] = current_time('mysql');

        update_option('arvan_api_usage_stats', $stats, false);
    }

    /**
     * Get Complete Telemetry & Usage Metrics
     */
    public function get_telemetry() {
        $stats = get_option('arvan_api_usage_stats', array(
            'total_requests' => 0,
            'total_throttled' => 0,
            'last_request_time' => 'هنوز ترافیکی ثبت نشده'
        ));

        $today = date('Y-m-d');
        $today_reqs = isset($stats['daily'][$today]['requests']) ? $stats['daily'][$today]['requests'] : 0;
        $today_throttled = isset($stats['daily'][$today]['throttled']) ? $stats['daily'][$today]['throttled'] : 0;

        $health_score = 100;
        if ($stats['total_requests'] > 0) {
            $health_score = round((($stats['total_requests'] - $stats['total_throttled']) / $stats['total_requests']) * 100, 1);
        }

        return array(
            'max_rpm' => $this->max_requests_per_minute,
            'total_requests' => $stats['total_requests'],
            'total_throttled' => $stats['total_throttled'],
            'today_requests' => $today_reqs,
            'today_throttled' => $today_throttled,
            'health_score' => $health_score,
            'last_active' => $stats['last_request_time'],
            'status' => ($health_score >= 95) ? 'عالی و پایدار' : (($health_score >= 80) ? 'متوسط' : 'نیازمند بررسی')
        );
    }
}
