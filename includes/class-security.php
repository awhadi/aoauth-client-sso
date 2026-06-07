<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Security {
    private $encryption_key;
    private $cipher = 'aes-256-cbc';
    
    public function __construct() {
        $this->encryption_key = $this->get_encryption_key();
    }
    
    private function get_encryption_key() {
        $key = get_option('aoauth_encryption_key', '');
        if (empty($key)) {
            $key = $this->generate_encryption_key();
            update_option('aoauth_encryption_key', $key);
        }
        
        if (defined('AOAUTH_ENCRYPTION_KEY') && AOAUTH_ENCRYPTION_KEY) {
            $key = AOAUTH_ENCRYPTION_KEY;
        }
        
        return $key;
    }
    
    private function generate_encryption_key() {
        return base64_encode(openssl_random_pseudo_bytes(32));
    }
    
    public function encrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($this->cipher));
        $encrypted = openssl_encrypt($data, $this->cipher, base64_decode($this->encryption_key), 0, $iv);
        
        return base64_encode($iv . $encrypted);
    }
    
    public function decrypt($data) {
        if (empty($data)) {
            return '';
        }
        
        $data = base64_decode($data);
        $iv_length = openssl_cipher_iv_length($this->cipher);
        $iv = substr($data, 0, $iv_length);
        $encrypted = substr($data, $iv_length);
        
        return openssl_decrypt($encrypted, $this->cipher, base64_decode($this->encryption_key), 0, $iv);
    }
    
    public function encrypt_secret($data) {
        if (empty($data)) {
            return '';
        }
        
        if (strpos($data, 'aoauth_enc:') === 0) {
            return $data;
        }
        
        return 'aoauth_enc:' . $this->encrypt($data);
    }
    
    public function decrypt_secret($data) {
        if (empty($data)) {
            return '';
        }
        
        if (strpos($data, 'aoauth_enc:') !== 0) {
            return $data;
        }
        
        $decrypted = $this->decrypt(substr($data, 11));
        return false === $decrypted ? '' : $decrypted;
    }
    
    public function generate_state() {
        return wp_generate_password(64, false, false);
    }
    
    public function generate_secure_token($length = 64) {
        return $this->generate_random_string($length);
    }
    
    public function generate_nonce() {
        return wp_create_nonce('aoauth_sso_' . time());
    }
    
    public function verify_nonce($nonce, $action = -1) {
        return wp_verify_nonce($nonce, $action);
    }
    
    public function generate_pkce_challenge() {
        $verifier = $this->generate_random_string(128);
        return array(
            'verifier' => $verifier,
            'challenge' => $this->base64url_encode(hash('sha256', $verifier, true))
        );
    }
    
    private function generate_random_string($length = 64) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-._~';
        $result = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $result .= $chars[random_int(0, $max)];
        }
        
        return $result;
    }
    
    private function base64url_encode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    public function verify_state($state, $session_data) {
        if (!isset($session_data['state']) || $session_data['state'] !== $state) {
            return false;
        }
        
        if (isset($session_data['expires_at']) && strtotime($session_data['expires_at']) < time()) {
            return false;
        }
        
        return true;
    }
    
    public function sanitize_provider_config($config) {
        $sanitized = array();
        
        $sanitized['app_name'] = sanitize_text_field($config['app_name'] ?? '');
        $sanitized['provider_name'] = sanitize_text_field($config['provider_name'] ?? '');
        $sanitized['client_id'] = sanitize_text_field($config['client_id'] ?? '');
        $sanitized['client_secret'] = $config['client_secret'] ?? '';
        $sanitized['authorization_endpoint'] = esc_url_raw($config['authorization_endpoint'] ?? '');
        $sanitized['token_endpoint'] = esc_url_raw($config['token_endpoint'] ?? '');
        $sanitized['userinfo_endpoint'] = esc_url_raw($config['userinfo_endpoint'] ?? '');
        $sanitized['redirect_uri'] = esc_url_raw($config['redirect_uri'] ?? '');
        $sanitized['issuer'] = esc_url_raw($config['issuer'] ?? '');
        $sanitized['jwks_uri'] = esc_url_raw($config['jwks_uri'] ?? '');
        $sanitized['end_session_endpoint'] = esc_url_raw($config['end_session_endpoint'] ?? '');
        $sanitized['scopes'] = array_map('sanitize_text_field', (array)($config['scopes'] ?? array()));
        $sanitized['enabled'] = !empty($config['enabled']) ? 1 : 0;
        $sanitized['send_credentials_in_header'] = !empty($config['send_credentials_in_header']) ? 1 : 0;
        $sanitized['enable_advanced_mapping'] = !empty($config['enable_advanced_mapping']) ? 1 : 0;
        
        if (!empty($sanitized['enable_advanced_mapping'])) {
            $sanitized['attribute_mapping'] = $this->sanitize_mapping($config['attribute_mapping'] ?? array());
            $sanitized['role_mapping'] = $this->sanitize_mapping($config['role_mapping'] ?? array());
        }
        
        if (!empty($config['draft'])) {
            $sanitized['draft'] = 1;
        }
        
        return $sanitized;
    }

    public function validate_oauth_endpoint_url($url) {
        $url = esc_url_raw($url);
        if (empty($url)) {
            return false;
        }

        $parsed_url = wp_parse_url($url);
        if (!$parsed_url || empty($parsed_url['host']) || empty($parsed_url['scheme'])) {
            return false;
        }

        $scheme = strtolower($parsed_url['scheme']);
        $host = strtolower(trim($parsed_url['host'], '[]'));
        $allow_development_endpoints = defined('AOAUTH_ALLOW_PRIVATE_OAUTH_ENDPOINTS') && AOAUTH_ALLOW_PRIVATE_OAUTH_ENDPOINTS === true;

        if ($scheme !== 'https' && !($allow_development_endpoints && $scheme === 'http')) {
            return false;
        }

        if ($this->is_restricted_endpoint_host($host)) {
            return $allow_development_endpoints;
        }

        return true;
    }

    private function is_restricted_endpoint_host($host) {
        if ($host === '' || $host === 'localhost' || $this->string_ends_with($host, '.localhost') || $this->string_ends_with($host, '.local')) {
            return true;
        }

        if (strpos($host, '.') === false && strpos($host, ':') === false) {
            return true;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
        }

        return false;
    }

    private function string_ends_with($value, $suffix) {
        $suffix_length = strlen($suffix);
        if ($suffix_length === 0) {
            return true;
        }

        return substr($value, -$suffix_length) === $suffix;
    }
    
    public static function get_nested_value($array, $path) {
        if (empty($path) || !is_array($array)) {
            return '';
        }
        
        $keys = explode('.', $path);
        $value = $array;
        
        foreach ($keys as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                return '';
            }
            $value = $value[$key];
        }
        
        return $value;
    }
    
    private function sanitize_mapping($mapping) {
        $sanitized = array();
        foreach ((array)$mapping as $key => $value) {
            $sanitized[sanitize_key($key)] = sanitize_text_field($value);
        }
        return $sanitized;
    }
    
    public function validate_redirect_url($url) {
        $url = esc_url_raw($url);
        if (empty($url)) {
            return false;
        }
        
        $parsed_url = wp_parse_url($url);
        if (!$parsed_url || empty($parsed_url['host']) || empty($parsed_url['scheme'])) {
            return false;
        }
        
        if (!in_array(strtolower($parsed_url['scheme']), array('http', 'https'), true)) {
            return false;
        }
        
        return wp_validate_redirect($url, false) !== false;
    }
    
    public function check_rate_limit($ip_address) {
        $settings = get_option('aoauth_settings', array());
        $max_attempts = isset($settings['rate_limit_attempts']) ? intval($settings['rate_limit_attempts']) : 5;
        $window = isset($settings['rate_limit_window']) ? intval($settings['rate_limit_window']) : 300;
        
        $transient_key = 'aoauth_rate_' . md5($ip_address);
        $attempts = get_transient($transient_key);
        
        if ($attempts === false) {
            set_transient($transient_key, 1, $window);
            return true;
        }
        
        if ($attempts >= $max_attempts) {
            return false;
        }
        
        set_transient($transient_key, $attempts + 1, $window);
        return true;
    }
    
    /**
     * Check rate limit for account linking attempts
     * 
     * @param int $user_id User ID
     * @param string $linking_key The linking key
     * @return array|WP_Error Returns array with attempts data or WP_Error if locked out
     */
    public function check_linking_rate_limit($user_id, $linking_key) {
        $settings = get_option('aoauth_settings', array());
        $max_attempts = intval($settings['linking_max_attempts'] ?? 5);
        $lockout_minutes = intval($settings['linking_lockout_minutes'] ?? 15);
        
        $transient_key = 'aoauth_linking_lock_' . md5($user_id . '_' . $linking_key);
        $lockout_until = get_transient($transient_key);
        
        if ($lockout_until !== false && $lockout_until > time()) {
            $remaining = ceil(($lockout_until - time()) / 60);
            return new WP_Error('locked_out', sprintf(__('Too many failed attempts. Please try again in %d minutes.', 'aoauth-client-sso'), $remaining));
        }
        
        $attempts_key = 'aoauth_linking_attempts_' . md5($user_id . '_' . $linking_key);
        $attempts = get_transient($attempts_key);
        if ($attempts === false) {
            $attempts = 0;
        }
        
        return array('attempts' => $attempts, 'max' => $max_attempts, 'lockout_minutes' => $lockout_minutes);
    }
    
    /**
     * Record a failed linking attempt
     * 
     * @param int $user_id User ID
     * @param string $linking_key The linking key
     */
    public function record_linking_failure($user_id, $linking_key) {
        $settings = get_option('aoauth_settings', array());
        $max_attempts = intval($settings['linking_max_attempts'] ?? 5);
        $lockout_minutes = intval($settings['linking_lockout_minutes'] ?? 15);
        $login_ban_minutes = intval($settings['linking_login_ban_minutes'] ?? 15);
        
        $attempts_key = 'aoauth_linking_attempts_' . md5($user_id . '_' . $linking_key);
        $attempts = get_transient($attempts_key);
        if ($attempts === false) {
            $attempts = 1;
        } else {
            $attempts++;
        }
        
        if ($attempts >= $max_attempts) {
            $lockout_until = time() + ($lockout_minutes * 60);
            set_transient('aoauth_linking_lock_' . md5($user_id . '_' . $linking_key), $lockout_until, $lockout_minutes * 60);
            if ($login_ban_minutes > 0) {
                set_transient('aoauth_login_ban_' . intval($user_id), time() + ($login_ban_minutes * 60), $login_ban_minutes * 60);
            }
            delete_transient($attempts_key);

            $logger = aoauth_core()->get_logger();
            $logger->log('account_linking_locked', array(
                'user_id' => $user_id,
                'lockout_minutes' => $lockout_minutes,
                'login_ban_minutes' => $login_ban_minutes
            ), $user_id, null, 'warning');
        } else {
            set_transient($attempts_key, $attempts, 15 * MINUTE_IN_SECONDS);
        }
    }
    
    /**
     * Clear linking attempts after successful login
     * 
     * @param int $user_id User ID
     * @param string $linking_key The linking key
     */
    public function clear_linking_attempts($user_id, $linking_key) {
        $attempts_key = 'aoauth_linking_attempts_' . md5($user_id . '_' . $linking_key);
        delete_transient($attempts_key);
        delete_transient('aoauth_linking_lock_' . md5($user_id . '_' . $linking_key));
    }

    public function get_user_login_ban($user_id) {
        $ban_until = get_transient('aoauth_login_ban_' . intval($user_id));
        if ($ban_until !== false && $ban_until > time()) {
            return $ban_until;
        }

        return false;
    }
}
