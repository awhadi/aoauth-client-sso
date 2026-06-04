<?php
/**
 * Debug Logger for aOAUTH Client SSO
 * Only enabled when AOAUTH_DEBUG is true or OAUTH-DEBUG is "enabled" in wp-config.php.
 * Logs are stored in /wp-content/uploads/aoauth-debug/
 */

if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Debug {
    
    private static $instance = null;
    private $log_dir;
    private $enabled = false;
    private $session_id;
    private $show_sensitive_data = false;
    
    private function __construct() {
        if ($this->is_debug_constant_enabled()) {
            $this->enabled = true;
            
            // Allow showing sensitive data if explicitly enabled (for deep debugging)
            if (defined('AOAUTH_DEBUG_SHOW_SENSITIVE') && AOAUTH_DEBUG_SHOW_SENSITIVE === true) {
                $this->show_sensitive_data = true;
            }
            
            $this->init_log_dir();
            $this->generate_session_id();
            $this->write_raw('===========================================');
            $this->write_raw('DEBUG SESSION STARTED');
            $this->write_raw('Sensitive Data Mode: ' . ($this->show_sensitive_data ? 'SHOWING' : 'HIDDEN'));
            $this->write_raw('===========================================');
        }
    }

    private function is_debug_constant_enabled() {
        if (defined('AOAUTH_DEBUG') && AOAUTH_DEBUG === true) {
            return true;
        }

        if (defined('OAUTH-DEBUG') && constant('OAUTH-DEBUG') === 'enabled') {
            return true;
        }

        return false;
    }
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function init_log_dir() {
        $upload_dir = wp_upload_dir();
        $this->log_dir = $upload_dir['basedir'] . '/aoauth-debug/';
        
        if (!file_exists($this->log_dir)) {
            wp_mkdir_p($this->log_dir);
        }
        
        $htaccess_file = $this->log_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            $htaccess_content = "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder Deny,Allow\nDeny from all\n</IfModule>\n<FilesMatch \"\\.(log|txt|json)$\">\nRequire all denied\n</FilesMatch>\n";
            file_put_contents($htaccess_file, $htaccess_content);
        }
        
        $index_file = $this->log_dir . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
    }
    
    private function generate_session_id() {
    // Use browser session cookie if available (same across requests)
    $session_cookie = session_id();
    if (!empty($session_cookie)) {
        $this->session_id = substr(md5($session_cookie), 0, 8);
    } else {
        $this->session_id = substr(md5(uniqid() . time()), 0, 8);
    }
}
    
    public function is_enabled() {
        return $this->enabled;
    }
    
    private function get_caller_info() {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        for ($i = 2; $i < 5; $i++) {
            if (isset($backtrace[$i])) {
                $file = isset($backtrace[$i]['file']) ? basename($backtrace[$i]['file']) : '';
                $line = isset($backtrace[$i]['line']) ? $backtrace[$i]['line'] : '';
                $class = isset($backtrace[$i]['class']) ? $backtrace[$i]['class'] : '';
                $function = isset($backtrace[$i]['function']) ? $backtrace[$i]['function'] : '';
                
                if ($file && !strpos($file, 'class-debug.php')) {
                    $class_part = $class ? $class . '::' : '';
                    return sprintf("[%s:%d] %s%s()", $file, $line, $class_part, $function);
                }
            }
        }
        
        return '[unknown]';
    }
    
    private function write_raw($message) {
        if (!$this->enabled) {
            return;
        }
        
        $log_file = $this->log_dir . 'aoauth-debug-' . date('Y-m-d') . '.log';
        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = sprintf("[%s] [%s] %s\n", $timestamp, $this->session_id, $message);
        error_log($log_entry, 3, $log_file);
    }
    
    public function log($level, $message, $context = array()) {
        if (!$this->enabled) {
            return;
        }
        
        $log_file = $this->log_dir . 'aoauth-debug-' . date('Y-m-d') . '.log';
        
        $timestamp = current_time('Y-m-d H:i:s');
        $caller = $this->get_caller_info();
        
        $context_str = '';
        if (!empty($context)) {
            $safe_context = $this->sanitize_context($context);
            $context_str = ' | DATA: ' . json_encode($safe_context, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
        }
        
        $log_entry = sprintf(
            "[%s] [%s] [%s] [%s] %s%s\n",
            $timestamp,
            strtoupper($level),
            $this->session_id,
            $caller,
            $message,
            $context_str
        );
        
        error_log($log_entry, 3, $log_file);
    }
    
    private function sanitize_context($context) {
        $sensitive_keys = array('password', 'secret', 'token', 'api_key', 'bearer', 'authorization', 'state', 'nonce');
        
        array_walk_recursive($context, function(&$value, $key) use ($sensitive_keys) {
            if (is_string($value) && (stripos((string) $key, 'url') !== false || stripos((string) $key, 'uri') !== false)) {
                $value = $this->sanitize_url_for_log($value);
            }

            // Check if this is a sensitive field
            $is_sensitive = false;
            foreach ($sensitive_keys as $sensitive) {
                if (stripos($key, $sensitive) !== false && !empty($value)) {
                    $is_sensitive = true;
                    break;
                }
            }
            
            // Special handling for client_id and client_secret
            if (stripos($key, 'client_id') !== false && !empty($value)) {
                if ($this->show_sensitive_data) {
                    $value = 'CLIENT_ID: "' . $value . '" (FULL VISIBLE)';
                } else {
                    $value = 'CLIENT_ID: "' . substr($value, 0, 8) . '****" (length: ' . strlen($value) . ')';
                }
                return;
            }
            
            if (stripos($key, 'client_secret') !== false && !empty($value)) {
                if ($this->show_sensitive_data) {
                    $value = 'CLIENT_SECRET: "' . $value . '" (FULL VISIBLE)';
                } else {
                    $value = 'CLIENT_SECRET: "****' . substr($value, -4) . '" (length: ' . strlen($value) . ')';
                }
                return;
            }
            
            if ($is_sensitive && !empty($value)) {
                if ($this->show_sensitive_data) {
                    $value = 'SENSITIVE: "' . $value . '" (FULL VISIBLE)';
                } else {
                    $value = '***HIDDEN*** (length: ' . strlen($value) . ')';
                }
            }
        });
        
        return $context;
    }

    private function sanitize_url_for_log($url) {
        $parts = wp_parse_url($url);
        if (!is_array($parts) || empty($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        foreach (array('client_id', 'client_secret', 'code', 'state', 'nonce', 'id_token', 'access_token', 'refresh_token') as $key) {
            if (isset($query[$key])) {
                $query[$key] = '***HIDDEN***';
            }
        }

        $rebuilt = '';
        if (!empty($parts['scheme'])) {
            $rebuilt .= $parts['scheme'] . '://';
        }
        if (!empty($parts['host'])) {
            $rebuilt .= $parts['host'];
        }
        if (!empty($parts['path'])) {
            $rebuilt .= $parts['path'];
        }
        $rebuilt .= '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        if (!empty($parts['fragment'])) {
            $rebuilt .= '#' . $parts['fragment'];
        }

        return $rebuilt;
    }
    
    public function debug($message, $context = array()) {
        $this->log('debug', $message, $context);
    }
    
    public function info($message, $context = array()) {
        $this->log('info', $message, $context);
    }
    
    public function error($message, $context = array()) {
        $this->log('error', $message, $context);
    }
    
    public function warning($message, $context = array()) {
        $this->log('warning', $message, $context);
    }
    
    public function log_start($component, $params = array()) {
        $this->log('debug', '▶ START: ' . $component, $params);
    }
    
    public function log_end($component, $params = array()) {
        $this->log('debug', '◀ END: ' . $component, $params);
    }
    
    // ============================================
    // SPECIFIC DEBUG METHODS FOR OAUTH FLOW
    // ============================================
    
    public function log_oauth_config($config, $label = 'OAuth Configuration') {
        $safe_config = $config;
        
        // Handle client_id
        if (isset($safe_config['client_id'])) {
            if ($this->show_sensitive_data) {
                $safe_config['client_id_display'] = $safe_config['client_id'];
            } else {
                $safe_config['client_id_display'] = substr($safe_config['client_id'], 0, 8) . '****';
                $safe_config['client_id_length'] = strlen($safe_config['client_id']);
            }
            unset($safe_config['client_id']);
        }
        
        // Handle client_secret
        if (isset($safe_config['client_secret'])) {
            if ($this->show_sensitive_data) {
                $safe_config['client_secret_display'] = $safe_config['client_secret'];
            } else {
                $safe_config['client_secret_display'] = '****' . substr($safe_config['client_secret'], -4);
                $safe_config['client_secret_length'] = strlen($safe_config['client_secret']);
            }
            unset($safe_config['client_secret']);
        }
        
        $this->info($label, $safe_config);
    }
    
    public function log_credential_transmission($method, $client_id, $client_secret, $header_value = null) {
        $log_data = array(
            'transmission_method' => $method,
        );
        
        if ($method === 'header') {
            if ($this->show_sensitive_data) {
                $log_data['authorization_header'] = $header_value;
                $log_data['client_id'] = $client_id;
                $log_data['client_secret'] = $client_secret;
            } else {
                $log_data['authorization_header_preview'] = substr($header_value, 0, 30) . '...' . substr($header_value, -10);
                $log_data['client_id_preview'] = substr($client_id, 0, 8) . '****';
                $log_data['client_secret_preview'] = '****' . substr($client_secret, -4);
            }
            $log_data['base64_encoded'] = 'Yes (Basic Auth)';
        } else {
            if ($this->show_sensitive_data) {
                $log_data['client_id'] = $client_id;
                $log_data['client_secret'] = $client_secret;
            } else {
                $log_data['client_id_preview'] = substr($client_id, 0, 8) . '****';
                $log_data['client_secret_preview'] = '****' . substr($client_secret, -4);
            }
            $log_data['location'] = 'POST body parameters';
        }
        
        $this->info('Credential Transmission', $log_data);
    }
    
    public function log_user_info($user_info, $label = 'User Info Received') {
        $safe_info = $user_info;
        $this->info($label, array(
            'available_fields' => is_array($safe_info) ? array_keys($safe_info) : 'not_array',
            'email_present' => !empty($safe_info['email']),
            'email_value' => !empty($safe_info['email']) ? $safe_info['email'] : 'missing',
            'name_present' => !empty($safe_info['name']),
            'first_name_present' => !empty($safe_info['given_name']) || !empty($safe_info['first_name']),
            'last_name_present' => !empty($safe_info['family_name']) || !empty($safe_info['last_name']),
            'sub_present' => !empty($safe_info['sub']),
            'raw_sample' => is_array($safe_info) ? json_encode(array_slice($safe_info, 0, 10)) : 'not_array'
        ));
    }
    
    public function log_token_response($tokens, $label = 'Token Response') {
        $safe_tokens = array();
        $safe_tokens['access_token_exists'] = !empty($tokens['access_token']);
        $safe_tokens['id_token_exists'] = !empty($tokens['id_token']);
        $safe_tokens['refresh_token_exists'] = !empty($tokens['refresh_token']);
        $safe_tokens['expires_in'] = $tokens['expires_in'] ?? 'not_set';
        $safe_tokens['token_type'] = $tokens['token_type'] ?? 'not_set';
        $safe_tokens['scope'] = $tokens['scope'] ?? 'not_set';
        
        if (!empty($tokens['access_token'])) {
            if ($this->show_sensitive_data) {
                $safe_tokens['access_token_full'] = $tokens['access_token'];
            } else {
                $safe_tokens['access_token_preview'] = substr($tokens['access_token'], 0, 20) . '...' . substr($tokens['access_token'], -10);
                $safe_tokens['access_token_length'] = strlen($tokens['access_token']);
            }
        }
        
        if (!empty($tokens['id_token'])) {
            $parts = explode('.', $tokens['id_token']);
            if (count($parts) >= 2) {
                $payload = json_decode(base64_decode(strtr($parts[1], '-_', '+/')), true);
                if ($payload) {
                    $safe_tokens['id_token_claims'] = array_keys($payload);
                    if (isset($payload['email'])) {
                        $safe_tokens['id_token_email'] = $payload['email'];
                    }
                    if (isset($payload['sub'])) {
                        $safe_tokens['id_token_sub'] = $payload['sub'];
                    }
                }
            }
            if ($this->show_sensitive_data) {
                $safe_tokens['id_token_full'] = substr($tokens['id_token'], 0, 100) . '...';
            } else {
                $safe_tokens['id_token_preview'] = substr($tokens['id_token'], 0, 30) . '...';
            }
        }
        
        $this->info($label, $safe_tokens);
    }
    
    public function log_http_request($method, $url, $headers = array(), $body = array()) {
        $safe_headers = array();
        foreach ($headers as $key => $value) {
            if (stripos($key, 'authorization') !== false || stripos($key, 'bearer') !== false) {
                if ($this->show_sensitive_data) {
                    $safe_headers[$key] = $value;
                } else {
                    $safe_headers[$key] = substr($value, 0, 30) . '...' . substr($value, -10) . ' (HIDDEN)';
                }
            } else {
                $safe_headers[$key] = $value;
            }
        }
        
        $safe_body = $body;
        if (isset($safe_body['client_secret'])) {
            if ($this->show_sensitive_data) {
                $safe_body['client_secret'] = $safe_body['client_secret'];
            } else {
                $safe_body['client_secret'] = '****' . substr($safe_body['client_secret'], -4);
            }
        }
        if (isset($safe_body['client_id'])) {
            if ($this->show_sensitive_data) {
                $safe_body['client_id'] = $safe_body['client_id'];
            } else {
                $safe_body['client_id'] = substr($safe_body['client_id'], 0, 8) . '****';
            }
        }
        
        $this->debug('HTTP Request', array(
            'method' => $method,
            'url' => $url,
            'headers' => $safe_headers,
            'body_keys' => is_array($safe_body) ? array_keys($safe_body) : 'non-array'
        ));
    }
    
    public function log_http_response($url, $response, $label = 'HTTP Response') {
        $status_code = 0;
        $body = '';
        
        if (is_wp_error($response)) {
            $this->error('HTTP Request Failed', array(
                'url' => $url,
                'error' => $response->get_error_message(),
                'code' => $response->get_error_code()
            ));
            return;
        }
        
        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $headers = wp_remote_retrieve_headers($response);
        
        $log_data = array(
            'url' => $url,
            'status_code' => $status_code,
            'content_type' => isset($headers['content-type']) ? $headers['content-type'] : 'unknown',
            'body_preview' => substr($body, 0, 1000),
            'body_length' => strlen($body)
        );
        
        $json_body = json_decode($body, true);
        if ($json_body && is_array($json_body)) {
            $log_data['body_keys'] = array_keys($json_body);
            if (isset($json_body['error'])) {
                $log_data['error'] = $json_body['error'];
            }
            if (isset($json_body['error_description'])) {
                $log_data['error_description'] = $json_body['error_description'];
            }
            if (isset($json_body['access_token'])) {
                if ($this->show_sensitive_data) {
                    $log_data['access_token_full'] = $json_body['access_token'];
                } else {
                    $log_data['access_token_preview'] = substr($json_body['access_token'], 0, 20) . '...';
                }
            }
        }
        
        if ($status_code >= 400) {
            $this->error($label . ' - Failed', $log_data);
        } else {
            $this->debug($label, $log_data);
        }
    }
    
    public function log_email_extraction($user_info, $tokens, $provider_slug, $result_email) {
        $log_data = array(
            'provider' => $provider_slug,
            'result_email' => $result_email ?: 'NOT_FOUND',
            'user_info_fields' => is_array($user_info) ? array_keys($user_info) : 'not_array',
            'user_info_email_field' => isset($user_info['email']) ? $user_info['email'] : 'missing',
            'user_info_mail_field' => isset($user_info['mail']) ? $user_info['mail'] : 'missing',
            'user_info_upn_field' => isset($user_info['upn']) ? $user_info['upn'] : 'missing',
            'user_info_preferred_username' => isset($user_info['preferred_username']) ? $user_info['preferred_username'] : 'missing',
            'id_token_present' => !empty($tokens['id_token']),
            'success' => !empty($result_email)
        );
        
        if (!empty($result_email)) {
            $this->info('Email extraction successful', $log_data);
        } else {
            $this->error('Email extraction FAILED', $log_data);
        }
    }
    
    public function log_user_creation($user_data, $result) {
        $log_data = array(
            'email' => $user_data['user_email'] ?? 'missing',
            'username' => $user_data['user_login'] ?? 'missing',
            'role' => $user_data['role'] ?? 'subscriber',
            'first_name' => $user_data['first_name'] ?? 'empty',
            'last_name' => $user_data['last_name'] ?? 'empty',
            'display_name' => $user_data['display_name'] ?? 'empty'
        );
        
        if (is_wp_error($result)) {
            $log_data['error'] = $result->get_error_message();
            $log_data['error_code'] = $result->get_error_code();
            $this->error('User creation FAILED', $log_data);
        } else {
            $log_data['user_id'] = $result;
            $this->info('User created successfully', $log_data);
        }
    }
    
    public function log_existing_user_login($user, $provider_slug) {
        $this->info('Existing user login', array(
            'user_id' => $user->ID,
            'user_email' => $user->user_email,
            'provider' => $provider_slug,
            'linked_provider' => get_user_meta($user->ID, '_aoauth_provider', true)
        ));
    }
    
    public function log_provider_save($app_id, $app_data, $is_update) {
        $safe_data = array(
            'app_id' => $app_id,
            'app_name' => $app_data['app_name'] ?? 'not_set',
            'provider_name' => $app_data['provider_name'] ?? 'not_set',
            'enabled' => $app_data['enabled'] ?? 0,
            'has_userinfo_endpoint' => !empty($app_data['userinfo_endpoint']),
            'scopes' => $app_data['scopes'] ?? array(),
            'advanced_mapping_enabled' => !empty($app_data['enable_advanced_mapping']),
            'send_credentials_in_header' => !empty($app_data['send_credentials_in_header'])
        );
        
        // Handle client_id
        if (isset($app_data['client_id'])) {
            if ($this->show_sensitive_data) {
                $safe_data['client_id'] = $app_data['client_id'];
            } else {
                $safe_data['client_id_preview'] = substr($app_data['client_id'], 0, 8) . '****';
            }
        }
        
        $this->info(($is_update ? 'Provider UPDATED' : 'Provider CREATED'), $safe_data);
    }
    
    public function log_login_attempt($provider_slug, $redirect_to, $auth_url) {
        $this->info('Login attempt initiated', array(
            'provider' => $provider_slug,
            'redirect_to' => $redirect_to ?: 'default',
            'auth_url' => $auth_url
        ));
    }
    
    public function log_callback_received($params) {
        $this->debug('OAuth Callback received', array(
            'code_present' => !empty($params['code']),
            'code_preview' => !empty($params['code']) ? substr($params['code'], 0, 20) . '...' : 'none',
            'state_present' => !empty($params['state']),
            'state_preview' => !empty($params['state']) ? substr($params['state'], 0, 20) . '...' : 'none',
            'error' => $params['error'] ?? 'none',
            'error_description' => $params['error_description'] ?? 'none'
        ));
    }
    
    public function log_discovery_attempt($url, $result) {
        $log_data = array(
            'discovery_url' => $url,
            'success' => !is_wp_error($result)
        );
        
        if (is_wp_error($result)) {
            $log_data['error'] = $result->get_error_message();
            $this->error('OpenID Discovery FAILED', $log_data);
        } else if (isset($result['authorization_endpoint'])) {
            $log_data['found_endpoints'] = array(
                'authorization' => $result['authorization_endpoint'] ?? 'not_found',
                'token' => $result['token_endpoint'] ?? 'not_found',
                'userinfo' => $result['userinfo_endpoint'] ?? 'not_found'
            );
            $this->info('OpenID Discovery successful', $log_data);
        }
    }
    
    public function log_rate_limit_check($ip_address, $is_allowed, $attempts, $max_attempts) {
        $this->debug('Rate limit check', array(
            'ip' => $ip_address,
            'allowed' => $is_allowed,
            'current_attempts' => $attempts,
            'max_attempts' => $max_attempts,
            'window_seconds' => 300
        ));
    }
    
    public function log_linking_attempt($user_id, $linking_key, $is_allowed, $remaining_attempts = null) {
        $log_data = array(
            'user_id' => $user_id,
            'linking_key' => substr($linking_key, 0, 10) . '...',
            'allowed' => $is_allowed
        );
        
        if ($remaining_attempts !== null) {
            $log_data['remaining_attempts'] = $remaining_attempts;
        }
        
        if (!$is_allowed) {
            $this->warning('Account linking blocked due to rate limit', $log_data);
        } else {
            $this->debug('Account linking attempt allowed', $log_data);
        }
    }
}
