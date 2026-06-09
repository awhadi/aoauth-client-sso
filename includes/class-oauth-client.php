<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_OAuth_Client {
    private $config;
    private $security;
    
    public function __construct($config) {
        $this->config = $config;
        $this->security = new AOAUTH_Security();
    }
    
    public function get_authorization_url($state = '', $nonce = '', $extra_params = array()) {
        $this->assert_safe_oauth_endpoint($this->config['authorization_endpoint'], __('Authorization Endpoint', 'aoauth-client-sso'));

        if (empty($state)) {
            $state = $this->security->generate_state();
        }
        
        if (empty($nonce)) {
            $nonce = $this->security->generate_secure_token(64);
        }
        
        $pkce = $this->security->generate_pkce_challenge();
        
        $params = array(
            'response_type' => 'code',
            'client_id' => $this->config['client_id'],
            'redirect_uri' => $this->config['redirect_uri'],
            'scope' => implode(' ', $this->config['scopes']),
            'state' => $state,
            'nonce' => $nonce,
            'code_challenge' => $pkce['challenge'],
            'code_challenge_method' => 'S256'
        );

        if (!empty($extra_params) && is_array($extra_params)) {
            $params = array_merge($params, array_map('sanitize_text_field', $extra_params));
        }
        
        set_transient('aoauth_pkce_' . $state, $pkce['verifier'], 10 * MINUTE_IN_SECONDS);
        
        return add_query_arg($params, $this->config['authorization_endpoint']);
    }
    
    public function get_tokens($code) {
    $debug = aoauth_core()->get_debug();
    $debug->log_start('AOAUTH_OAuth_Client::get_tokens');
    
    $this->assert_safe_oauth_endpoint($this->config['token_endpoint'], __('Token Endpoint', 'aoauth-client-sso'));
    // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- OAuth state is verified by the callback handler before token exchange.
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    
    $params = array(
        'grant_type' => 'authorization_code',
        'code' => $code,
        'redirect_uri' => $this->config['redirect_uri'],
        'client_id' => $this->config['client_id'],
    );
    
    $pkce_verifier = get_transient('aoauth_pkce_' . $state);
    if ($pkce_verifier) {
        $params['code_verifier'] = $pkce_verifier;
        delete_transient('aoauth_pkce_' . $state);
        $debug->debug('PKCE verifier applied');
    }
    
    $client_secret = $this->security->decrypt($this->config['client_secret']);
    if (!empty($client_secret)) {
        $params['client_secret'] = $client_secret;
    }
    
    $headers = array(
        'Accept' => 'application/json',
    );
    
    // LOG THE TRANSMISSION METHOD
    if (!empty($this->config['send_credentials_in_header'])) {
        $auth_string = base64_encode($this->config['client_id'] . ':' . $client_secret);
        $headers['Authorization'] = 'Basic ' . $auth_string;
        unset($params['client_secret']);
        
        $debug->info('Credentials sent in HEADER (Basic Auth)', array(
            'client_id_preview' => substr($this->config['client_id'], 0, 10) . '...',
            'auth_header_preview' => 'Basic ' . substr($auth_string, 0, 20) . '...',
            'client_secret_length' => strlen($client_secret)
        ));
    } else {
        $debug->info('Credentials sent in BODY', array(
            'client_id_preview' => substr($this->config['client_id'], 0, 10) . '...',
            'client_secret_length' => strlen($client_secret)
        ));
    }
    
    $debug->debug('Token request params', array(
        'token_endpoint' => $this->config['token_endpoint'],
        'grant_type' => 'authorization_code',
        'redirect_uri' => $this->config['redirect_uri'],
        'has_client_secret' => !empty($client_secret)
    ));
    
    $response = wp_remote_post($this->config['token_endpoint'], array(
        'headers' => $headers,
        'body' => $params,
        'timeout' => 30,
        'sslverify' => true
    ));
    
    // Log response status
    $status_code = wp_remote_retrieve_response_code($response);
    $debug->debug('Token response status', array('status_code' => $status_code));
    
    if (is_wp_error($response)) {
        $debug->error('Token request failed', array('error' => $response->get_error_message()));
        throw new Exception(esc_html($response->get_error_message()));
    }
    
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);
    
    if (!empty($data['error'])) {
        $debug->error('OAuth token error', array(
            'error' => $data['error'],
            'error_description' => $data['error_description'] ?? 'none'
        ));
        throw new Exception(esc_html($data['error_description'] ?? $data['error']));
    }
    
    $debug->info('Token response successful', array(
        'access_token_exists' => !empty($data['access_token']),
        'id_token_exists' => !empty($data['id_token']),
        'expires_in' => $data['expires_in'] ?? 'unknown'
    ));
    
    $debug->log_end('AOAUTH_OAuth_Client::get_tokens');
    
    return $data;
}
    
    public function get_user_info($access_token) {
        if (empty($this->config['userinfo_endpoint'])) {
            throw new Exception('No UserInfo endpoint configured');
        }

        $this->assert_safe_oauth_endpoint($this->config['userinfo_endpoint'], __('UserInfo Endpoint', 'aoauth-client-sso'));
        
        $response = wp_remote_get($this->config['userinfo_endpoint'], array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
            ),
            'timeout' => 30,
            'sslverify' => true
        ));
        
        if (is_wp_error($response)) {
            throw new Exception(esc_html($response->get_error_message()));
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        if (!empty($data['error'])) {
            throw new Exception(esc_html($data['error_description'] ?? $data['error']));
        }
        
        return $data;
    }
    
    public function discover_endpoints($url) {
        $this->assert_safe_oauth_endpoint($url, __('Discovery URL', 'aoauth-client-sso'));
        $url = rtrim($url, '/');
        
        // Try standard OpenID Connect discovery
        $discovery_url = $url . '/.well-known/openid-configuration';
        $response = wp_remote_get($discovery_url, array(
            'timeout' => 15,
            'sslverify' => true,
            'headers' => array('Accept' => 'application/json')
        ));
        
        if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (is_array($data)) {
                return array(
                    'authorization_endpoint' => $data['authorization_endpoint'] ?? '',
                    'token_endpoint' => $data['token_endpoint'] ?? '',
                    'userinfo_endpoint' => $data['userinfo_endpoint'] ?? '',
                    'end_session_endpoint' => $data['end_session_endpoint'] ?? '',
                    'jwks_uri' => $data['jwks_uri'] ?? '',
                    'issuer' => $data['issuer'] ?? ''
                );
            }
        }
        
        // Try Keycloak pattern
        $keycloak_auth = $url . '/protocol/openid-connect/auth';
        $keycloak_token = $url . '/protocol/openid-connect/token';
        $keycloak_userinfo = $url . '/protocol/openid-connect/userinfo';
        
        // Test if endpoints are reachable (simple head request)
        $auth_test = wp_remote_head($keycloak_auth, array('timeout' => 5));
        if (!is_wp_error($auth_test) && wp_remote_retrieve_response_code($auth_test) !== 404) {
            return array(
                'authorization_endpoint' => $keycloak_auth,
                'token_endpoint' => $keycloak_token,
                'userinfo_endpoint' => $keycloak_userinfo,
                'method' => 'keycloak_pattern'
            );
        }
        
        // Try OAuth 2.0 standard endpoints
        $oauth_auth = $url . '/oauth/authorize';
        $oauth_token = $url . '/oauth/token';
        $auth_test2 = wp_remote_head($oauth_auth, array('timeout' => 5));
        if (!is_wp_error($auth_test2) && wp_remote_retrieve_response_code($auth_test2) !== 404) {
            return array(
                'authorization_endpoint' => $oauth_auth,
                'token_endpoint' => $oauth_token,
                'userinfo_endpoint' => '',
                'method' => 'oauth2_standard'
            );
        }
        
        throw new Exception('Could not discover endpoints. Please provide them manually.');
    }

    private function assert_safe_oauth_endpoint($url, $label) {
        if (!$this->security->validate_oauth_endpoint_url($url)) {
            throw new Exception(esc_html(sprintf(
                /* translators: %s: OAuth endpoint label, such as Authorization Endpoint or Token Endpoint. */
                __('%s must be a public HTTPS URL. Private, local, and plain HTTP endpoints are blocked by default.', 'aoauth-client-sso'),
                $label
            )));
        }
    }
}
