<?php
if (!defined('ABSPATH')) {
    exit;
}

class Arvan_API_Client {

    private static $instance = null;
    private $api_key = '';
    private $is_mock = true;
    private $base_url_ecc = 'https://napi.arvancloud.ir/ecc/v1';
    private $base_url_cdn = 'https://napi.arvancloud.ir/cdn/4.0';
    private $base_url_storage = 'https://napi.arvancloud.ir/storage/v1';
    private $base_url_user = 'https://napi.arvancloud.ir/user/v1';

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->api_key = get_option('arvan_api_key', '');
        $this->is_mock = (get_option('arvan_mode', 'mock') === 'mock') || empty($this->api_key);
    }

    public function set_mode($mode) {
        $this->is_mock = ($mode === 'mock');
    }

    /**
     * Get Available Hardware Flavors/Sizes
     */
    public function get_flavors($region = 'ir-thr-at1') {
        if ($this->is_mock) {
            return array(
                array(
                    'id' => 'g1-1-1-0',
                    'name' => 'Eco Starter (1 vCPU, 1GB RAM, 25GB NVMe)',
                    'cpu' => 1,
                    'memory' => 1024,
                    'disk' => 25,
                    'hourly_price' => 250, // Tomans / hr
                    'monthly_price' => 180000,
                    'badge' => 'اقتصادی'
                ),
                array(
                    'id' => 'g1-2-1-0',
                    'name' => 'General 2GB (1 vCPU, 2GB RAM, 35GB NVMe)',
                    'cpu' => 1,
                    'memory' => 2048,
                    'disk' => 35,
                    'hourly_price' => 450,
                    'monthly_price' => 324000,
                    'badge' => 'محبوب‌ترین'
                ),
                array(
                    'id' => 'g1-4-2-0',
                    'name' => 'Pro Standard (2 vCPU, 4GB RAM, 55GB NVMe)',
                    'cpu' => 2,
                    'memory' => 4096,
                    'disk' => 55,
                    'hourly_price' => 890,
                    'monthly_price' => 640800,
                    'badge' => 'حرفه‌ای'
                ),
                array(
                    'id' => 'g1-8-4-0',
                    'name' => 'Enterprise Power (4 vCPU, 8GB RAM, 100GB NVMe)',
                    'cpu' => 4,
                    'memory' => 8192,
                    'disk' => 100,
                    'hourly_price' => 1680,
                    'monthly_price' => 1209600,
                    'badge' => 'سازمانی'
                )
            );
        }

        return $this->request('GET', "{$this->base_url_ecc}/regions/{$region}/sizes");
    }

    /**
     * Get Available OS Images
     */
    public function get_images($region = 'ir-thr-at1') {
        if ($this->is_mock) {
            return array(
                array('id' => 'ubuntu-24-04', 'name' => 'Ubuntu 24.04 LTS (Noble Numbat)', 'os' => 'Linux', 'icon' => 'ubuntu'),
                array('id' => 'ubuntu-22-04', 'name' => 'Ubuntu 22.04 LTS (Jammy Jellyfish)', 'os' => 'Linux', 'icon' => 'ubuntu'),
                array('id' => 'debian-12', 'name' => 'Debian 12 (Bookworm)', 'os' => 'Linux', 'icon' => 'debian'),
                array('id' => 'almalinux-9', 'name' => 'AlmaLinux 9.4 (RHEL Compatible)', 'os' => 'Linux', 'icon' => 'centos'),
                array('id' => 'windows-server-2022', 'name' => 'Windows Server 2022 Standard', 'os' => 'Windows', 'icon' => 'windows'),
                array('id' => 'docker-ce', 'name' => 'Docker CE on Ubuntu 24.04 (One-Click)', 'os' => 'App', 'icon' => 'docker')
            );
        }

        return $this->request('GET', "{$this->base_url_ecc}/regions/{$region}/images");
    }

    /**
     * Provision a New Cloud Server (IaaS)
     */
    public function create_server($data) {
        $region = !empty($data['region']) ? $data['region'] : 'ir-thr-at1';

        if ($this->is_mock) {
            $random_ip = '185.143.233.' . rand(10, 250);
            $server_id = 'srv-' . substr(md5(uniqid(rand(), true)), 0, 12);
            return array(
                'success' => true,
                'data' => array(
                    'id' => $server_id,
                    'name' => $data['name'],
                    'status' => 'ACTIVE',
                    'ip' => $random_ip,
                    'flavor_id' => $data['flavor_id'],
                    'region' => $region,
                    'created_at' => current_time('mysql')
                )
            );
        }

        return $this->request('POST', "{$this->base_url_ecc}/regions/{$region}/servers", $data);
    }

    /**
     * Power Off / Suspend Server
     */
    public function power_off_server($server_id, $region = 'ir-thr-at1') {
        if ($this->is_mock) {
            return array('success' => true, 'message' => 'Server powered off (Suspended)');
        }
        return $this->request('POST', "{$this->base_url_ecc}/regions/{$region}/servers/{$server_id}/power-off");
    }

    /**
     * Power On / Resume Server
     */
    public function power_on_server($server_id, $region = 'ir-thr-at1') {
        if ($this->is_mock) {
            return array('success' => true, 'message' => 'Server powered on (Resumed)');
        }
        return $this->request('POST', "{$this->base_url_ecc}/regions/{$region}/servers/{$server_id}/power-on");
    }

    /**
     * Terminate / Destroy Server
     */
    public function terminate_server($server_id, $region = 'ir-thr-at1') {
        if ($this->is_mock) {
            return array('success' => true, 'message' => 'Server terminated permanently');
        }
        return $this->request('DELETE', "{$this->base_url_ecc}/regions/{$region}/servers/{$server_id}");
    }

    /**
     * Get Provider Account Balance
     */
    public function get_provider_balance() {
        if ($this->is_mock) {
            return array(
                'balance' => 45000000, // 45M Tomans
                'currency' => 'IRT',
                'credit_limit' => 15000000
            );
        }
        return $this->request('GET', "{$this->base_url_user}/finance/balance");
    }

    /**
     * Generic HTTP Request Wrapper
     */
    private function request($method, $url, $body = null) {
        $args = array(
            'method' => $method,
            'headers' => array(
                'Authorization' => 'Apikey ' . $this->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ),
            'timeout' => 15
        );

        if ($body !== null) {
            $args['body'] = json_encode($body);
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            return array('error' => true, 'message' => $response->get_error_message());
        }

        $code = wp_remote_retrieve_response_code($response);
        $data = json_decode(wp_remote_retrieve_body($response), true);

        if ($code >= 400) {
            return array('error' => true, 'code' => $code, 'message' => isset($data['message']) ? $data['message'] : 'API Error');
        }

        return $data;
    }
}
