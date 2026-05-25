<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_SSO_Handler {
    private $user_mapping;
    private $logger;
    private $security;
    private $debug;
    
    public function __construct() {
        $this->user_mapping = new AOAUTH_User_Mapping();
        $this->logger = new AOAUTH_Logger();
        $this->security = aoauth_core()->get_security();
        $this->debug = aoauth_core()->get_debug();
    }
    
    public function init() {
        $this->debug->log_start('AOAUTH_SSO_Handler::init');
        
        add_action('init', array($this, 'handle_callback'));
        add_action('init', array($this, 'handle_login_action'));
        add_action('init', array($this, 'handle_test_callback'));
        add_action('wp_login', array($this, 'wp_login_handler'), 10, 2);
        add_action('user_register', array($this, 'user_register_handler'));
        $this->register_unlink_shortcode();
        
        $this->debug->info('SSO Handler initialized');
        $this->debug->log_end('AOAUTH_SSO_Handler::init');
    }
    
    private function extract_email_from_provider($user_info, $tokens, $provider_slug) {
        $this->debug->log_start('extract_email_from_provider', array('provider' => $provider_slug));
        
        $email = null;
        
        $common_email_fields = array('email', 'mail', 'userPrincipalName', 'upn', 'preferred_username');
        foreach ($common_email_fields as $field) {
            if (!empty($user_info[$field]) && is_email($user_info[$field])) {
                $email = $user_info[$field];
                $this->debug->debug('Email found via field', array('field' => $field, 'email' => $email));
                break;
            }
        }
        
        switch ($provider_slug) {
            case 'github':
                if (!empty($tokens['access_token'])) {
                    $email = $this->fetch_github_email($tokens['access_token']);
                    $this->debug->debug('GitHub email fetch attempted', array('success' => !empty($email)));
                }
                break;
            case 'microsoft':
                if (empty($email) && !empty($user_info['userPrincipalName'])) {
                    $email = $user_info['userPrincipalName'];
                    $this->debug->debug('Email from userPrincipalName', array('email' => $email));
                }
                break;
            case 'linkedin':
                if (empty($email) && !empty($user_info['emailAddress'])) {
                    $email = $user_info['emailAddress'];
                    $this->debug->debug('Email from emailAddress', array('email' => $email));
                }
                break;
            default:
                $this->debug->debug('No special handling for provider', array('provider' => $provider_slug));
                break;
        }
        
        if (empty($email) && !empty($user_info['username']) && is_email($user_info['username'])) {
            $email = $user_info['username'];
            $this->debug->debug('Email from username field');
        }
        
        $result = is_email($email) ? $email : null;
        $this->debug->log_end('extract_email_from_provider', array('found' => !empty($result)));
        
        return $result;
    }
    
    private function fetch_github_email($access_token) {
        $this->debug->log_start('fetch_github_email');
        
        $response = wp_remote_get('https://api.github.com/user/emails', array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
                'Accept' => 'application/json',
                'User-Agent' => 'WordPress aOAUTH Plugin',
            ),
            'timeout' => 15,
        ));
        
        if (is_wp_error($response)) {
            $this->debug->error('GitHub email fetch failed', array('error' => $response->get_error_message()));
            $this->logger->log('github_email_fetch_failed', array(
                'error' => $response->get_error_message()
            ), null, 'github', 'warning');
            $this->debug->log_end('fetch_github_email', array('success' => false));
            return null;
        }
        
        $emails = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!is_array($emails)) {
            $this->debug->debug('Invalid GitHub emails response');
            $this->debug->log_end('fetch_github_email', array('success' => false));
            return null;
        }
        
        foreach ($emails as $email_entry) {
            if (!empty($email_entry['primary']) && !empty($email_entry['verified']) && !empty($email_entry['email'])) {
                $this->debug->debug('Found primary verified email', array('email' => $email_entry['email']));
                $this->debug->log_end('fetch_github_email', array('success' => true));
                return $email_entry['email'];
            }
        }
        
        foreach ($emails as $email_entry) {
            if (!empty($email_entry['verified']) && !empty($email_entry['email'])) {
                $this->debug->debug('Found verified email (not primary)', array('email' => $email_entry['email']));
                $this->debug->log_end('fetch_github_email', array('success' => true));
                return $email_entry['email'];
            }
        }
        
        $this->debug->debug('No valid email found in GitHub response');
        $this->debug->log_end('fetch_github_email', array('success' => false));
        return null;
    }
    
    private function extract_raw_claims_from_id_token($id_token) {
        $this->debug->log_start('extract_raw_claims_from_id_token');
        
        $parts = explode('.', $id_token);
        
        if (count($parts) !== 3) {
            $this->debug->error('Invalid ID token format', array('parts' => count($parts)));
            $this->debug->log_end('extract_raw_claims_from_id_token', array('success' => false));
            return new WP_Error('invalid_id_token', 'Invalid ID token format.');
        }
        
        $payload = $this->base64url_decode($parts[1]);
        
        if (false === $payload) {
            $this->debug->error('Failed to decode ID token payload');
            $this->debug->log_end('extract_raw_claims_from_id_token', array('success' => false));
            return new WP_Error('invalid_payload', 'Failed to decode ID token.');
        }
        
        $claims = json_decode($payload, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->debug->error('Failed to parse ID token claims', array('error' => json_last_error_msg()));
            $this->debug->log_end('extract_raw_claims_from_id_token', array('success' => false));
            return new WP_Error('invalid_json', 'Failed to parse ID token claims.');
        }
        
        $this->debug->debug('ID token claims extracted', array('claim_keys' => array_keys($claims)));
        $this->debug->log_end('extract_raw_claims_from_id_token', array('success' => true));
        
        return $claims;
    }
    
    public function handle_callback() {
    $debug = aoauth_core()->get_debug();
    
    $is_callback = (isset($_GET['oauth']) && $_GET['oauth'] === 'callback') || 
                   (isset($_GET['aoauth_action']) && $_GET['aoauth_action'] === 'callback');
    
    if (!$is_callback) {
        return;
    }
    
    // Skip test callbacks - they go to handle_test_callback instead
    if (isset($_GET['aoauth_test'])) {
        $debug->debug('Test callback detected, skipping to test handler');
        return;
    }
    
    $debug->info('=== OAUTH CALLBACK RECEIVED ===', array(
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'GET_params' => array_keys($_GET)
    ));
    
    $code = isset($_GET['code']) ? sanitize_text_field(wp_unslash($_GET['code'])) : '';
    $state = isset($_GET['state']) ? sanitize_text_field(wp_unslash($_GET['state'])) : '';
    $error = isset($_GET['error']) ? sanitize_text_field(wp_unslash($_GET['error'])) : '';
    $error_description = isset($_GET['error_description']) ? sanitize_text_field(wp_unslash($_GET['error_description'])) : '';
    
    $debug->debug('Callback params', array(
        'code_exists' => !empty($code),
        'state_exists' => !empty($state),
        'error' => $error,
        'error_description' => $error_description
    ));
    
    if (!empty($error)) {
        $message = !empty($error_description) ? $error_description : $error;
        $debug->error('OAuth error', array('error' => $error, 'description' => $error_description));
        $this->logger->log('sso_callback_failed', array(
            'reason' => 'provider_error',
            'error' => $error
        ), null, null, 'error');
        $this->redirect_with_error('OAuth Error: ' . $message);
        return;
    }
    
    if (empty($code) || empty($state)) {
        $debug->error('Missing code or state');
        $this->logger->log('sso_callback_failed', array(
            'reason' => 'missing_code_or_state'
        ), null, null, 'error');
        $this->redirect_with_error('Invalid callback parameters.');
        return;
    }
    
    $transient_key = 'aoauth_state_' . $state;
    $state_data = get_transient($transient_key);
    
    $debug->debug('State lookup', array(
        'key' => $transient_key,
        'found' => $state_data !== false
    ));
    
    if (false === $state_data) {
        $debug->error('State expired or not found');
        $this->logger->log('sso_callback_failed', array(
            'reason' => 'state_expired_or_not_found'
        ), null, null, 'error');
        $this->redirect_with_error('Session expired or invalid. Please try logging in again.');
        return;
    }
    
    delete_transient($transient_key);
    
    $provider_slug = sanitize_text_field($state_data['provider']);
    $redirect_to = !empty($state_data['redirect_to']) ? esc_url_raw($state_data['redirect_to']) : '';
    $flow_id = sanitize_text_field($state_data['flow_id'] ?? '');
    
    $debug->info('Processing callback', array('provider' => $provider_slug, 'redirect_to' => $redirect_to));
    
    if (!empty($redirect_to) && !$this->security->validate_redirect_url($redirect_to)) {
        $debug->warning('Invalid redirect URL', array('redirect_to' => $redirect_to));
        $redirect_to = '';
    }
    
    $applications = get_option('aoauth_applications', array());
    $provider = $this->get_provider_by_slug($provider_slug, $applications);
    
    if (false === $provider) {
        $debug->error('Provider not found in config', array('slug' => $provider_slug));
        $this->logger->log('sso_callback_failed', array(
            'flow_id' => $flow_id,
            'provider' => $provider_slug,
            'reason' => 'provider_not_found'
        ), null, $provider_slug, 'error');
        $this->redirect_with_error('Provider configuration not found.');
        return;
    }
    
    if (empty($provider['enabled'])) {
        $debug->error('Provider not enabled', array('slug' => $provider_slug));
        $this->logger->log('sso_callback_failed', array(
            'flow_id' => $flow_id,
            'provider' => $provider_slug,
            'reason' => 'provider_disabled'
        ), null, $provider_slug, 'error');
        $this->redirect_with_error('This login provider is not enabled.');
        return;
    }
    
    $provider['redirect_uri'] = $this->get_callback_url();
    
    try {
        $debug->debug('Exchanging code for tokens');
        $oauth_client = new AOAUTH_OAuth_Client($provider);
        $tokens = $oauth_client->get_tokens($code);
        
        $debug->info('Tokens received', array(
            'has_access_token' => !empty($tokens['access_token']),
            'has_id_token' => !empty($tokens['id_token']),
            'expires_in' => $tokens['expires_in'] ?? 'unknown'
        ));
        
        $user_info = null;
        $raw_claims = null;
        
        // Check if advanced mapping is enabled
        $use_advanced_mapping = !empty($provider['enable_advanced_mapping']);
        $debug->debug('Advanced mapping', array('enabled' => $use_advanced_mapping));
        
        if (!empty($tokens['id_token'])) {
            $validated_claims = $this->validate_id_token($tokens['id_token'], $provider, $state_data);
            if (is_wp_error($validated_claims)) {
                $debug->error('ID token validation failed', array('error' => $validated_claims->get_error_message()));
                $this->logger->log('sso_authentication_failed', array(
                    'flow_id' => $flow_id,
                    'provider' => $provider_slug,
                    'reason' => 'id_token_validation_failed'
                ), null, $provider_slug, 'error');
                $this->redirect_with_error('Login failed: identity token validation failed.');
                return;
            }
            
            if ($use_advanced_mapping) {
                // Use raw claims for manual mapping
                $raw_claims = $validated_claims;
                $user_info = $raw_claims;
                $debug->debug('User info from ID token (advanced)');
            } else {
                // Use normalized data for default mapping
                $user_info = $this->normalize_user_data($validated_claims);
                $debug->debug('User info from ID token (default)');
            }
        }
        
        if (null === $user_info && !empty($provider['userinfo_endpoint'])) {
            $debug->debug('Fetching from userinfo endpoint');
            $user_info = $oauth_client->get_user_info($tokens['access_token']);
            
            // If using advanced mapping, keep raw data from userinfo endpoint
            if ($use_advanced_mapping && is_array($user_info)) {
                $debug->debug('User info from userinfo endpoint (advanced)');
            }
        }
        
        // Extract email using global intelligent method
        $extracted_email = $this->extract_email_from_provider($user_info, $tokens, $provider_slug);
        
        $debug->info('Email extraction result', array(
            'success' => !empty($extracted_email),
            'email' => $extracted_email ?: 'NOT_FOUND'
        ));
        
        if (empty($extracted_email)) {
            $this->logger->log('email_extraction_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider_slug,
                'available_fields' => array_keys(is_array($user_info) ? $user_info : array())
            ), null, $provider_slug, 'error');
            
            $debug->error('Email extraction failed', array(
                'user_info_keys' => is_array($user_info) ? array_keys($user_info) : 'not_array'
            ));
            
            $this->redirect_with_error('Could not retrieve email from provider. Please ensure email scope is enabled and your email is not set to private.');
            return;
        }
        
        // Set the extracted email
        $user_info['email'] = $extracted_email;
        
        // Apply attribute mapping if configured
        $user_info = $this->user_mapping->map_user_data($user_info, $provider);
        $debug->debug('After mapping', array(
            'username' => $user_info['username'] ?? 'missing',
            'display_name' => $user_info['display_name'] ?? 'missing'
        ));
        
        $user_manager = new AOAUTH_User_Manager();
        $result = $user_manager->process_oauth_user($user_info, $provider, $provider_slug);
        
        if (is_wp_error($result)) {
            $debug->error('User processing failed', array('error' => $result->get_error_message()));
            $this->logger->log('sso_authentication_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider_slug,
                'reason' => 'user_processing_failed',
                'error' => $result->get_error_message()
            ), null, $provider_slug, 'error');
            $this->redirect_with_error($result->get_error_message());
            return;
        }
        
        $debug->info('User authenticated', array('user_id' => $result));
        
        wp_clear_auth_cookie();
        wp_set_auth_cookie($result, true);
        wp_set_current_user($result);
        
        do_action('wp_login', get_userdata($result)->user_login, get_userdata($result));
        
        // Determine where to redirect based on user role
        $redirect_url = $this->get_redirect_by_user_role($result, $redirect_to);
        
        $debug->info('Authentication success', array(
            'user_id' => $result,
            'provider' => $provider_slug,
            'redirect_url' => $redirect_url
        ));
        
        $this->logger->log('authentication_success', array(
            'flow_id' => $flow_id,
            'user_id' => $result,
            'provider' => $provider_slug,
            'redirect_url' => $redirect_url,
            'redirect_path' => $this->get_url_path_for_log($redirect_url)
        ), $result, $provider_slug, 'success');
        
        wp_safe_redirect($redirect_url);
        exit;
        
    } catch (Exception $e) {
        $debug->error('Authentication exception', array('error' => $e->getMessage()));
        
        $this->logger->log('authentication_failed', array(
            'flow_id' => $flow_id,
            'error' => $e->getMessage(),
            'provider' => $provider_slug
        ), null, $provider_slug, 'error');
        
        $this->redirect_with_error('Login failed: ' . $e->getMessage());
    }
}
    
    public function handle_test_callback() {
        $this->debug->log_start('AOAUTH_SSO_Handler::handle_test_callback');
        
        if (!isset($_GET['oauth']) || $_GET['oauth'] !== 'callback') {
            $this->debug->debug('Not a callback request, skipping');
            $this->debug->log_end('AOAUTH_SSO_Handler::handle_test_callback');
            return;
        }
        
        if (!isset($_GET['aoauth_test']) || $_GET['aoauth_test'] !== '1') {
            $this->debug->debug('Not a test callback, skipping');
            $this->debug->log_end('AOAUTH_SSO_Handler::handle_test_callback');
            return;
        }
        
        if (!isset($_GET['code']) || !isset($_GET['state'])) {
            $this->debug->error('Missing code or state in test callback');
            $this->debug->log_end('AOAUTH_SSO_Handler::handle_test_callback');
            return;
        }
        
        $state = sanitize_text_field($_GET['state']);
        $code = sanitize_text_field($_GET['code']);
        $error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
        
        $this->debug->debug('Test callback processing', array('state' => substr($state, 0, 10) . '...'));
        
        if (!empty($error)) {
            $this->debug->error('Test callback error', array('error' => $error));
            wp_redirect(admin_url('admin.php?page=aoauth-wizard&test=failed&error=' . urlencode($error)));
            exit;
        }
        
        $transient_key = 'aoauth_test_state_' . $state;
        $test_data = get_transient($transient_key);
        
        if (false === $test_data) {
            $this->debug->error('Test state transient not found or expired', array('key' => $transient_key));
            wp_redirect(admin_url('admin.php?page=aoauth-wizard&test=failed&error=' . urlencode('Test session expired or invalid state')));
            exit;
        }
        
        delete_transient($transient_key);
        
        $provider_slug = $test_data['provider'];
        $applications = get_option('aoauth_applications', array());
        $provider = isset($applications[$provider_slug]) ? $applications[$provider_slug] : null;
        
        if (!$provider) {
            $this->debug->error('Provider not found for test', array('slug' => $provider_slug));
            wp_redirect(admin_url('admin.php?page=aoauth-wizard&test=failed&error=' . urlencode('Provider configuration not found')));
            exit;
        }
        
        $provider['redirect_uri'] = $this->get_callback_url();
        
        try {
            $oauth_client = new AOAUTH_OAuth_Client($provider);
            $tokens = $oauth_client->get_tokens($code);
            
            $this->debug->debug('Test tokens received', array(
                'access_token_exists' => !empty($tokens['access_token'])
            ));
            
            if (empty($tokens['access_token'])) {
                throw new Exception('No access token received from provider');
            }
            
            $user_info = null;
            if (!empty($tokens['id_token'])) {
                $validated_claims = $this->validate_id_token($tokens['id_token'], $provider, $test_data);
                if (is_wp_error($validated_claims)) {
                    throw new Exception($validated_claims->get_error_message());
                }
                $user_info = $this->normalize_user_data($validated_claims);
            }
            
            if (empty($user_info) && !empty($provider['userinfo_endpoint'])) {
                $user_info = $oauth_client->get_user_info($tokens['access_token']);
            }
            
            $extracted_email = $this->extract_email_from_provider($user_info, $tokens, $provider_slug);
            
            if (empty($extracted_email)) {
                throw new Exception('Could not retrieve user email from provider. Make sure email scope is enabled and email is not private.');
            }
            
            $applications[$provider_slug]['enabled'] = 1;
            if (isset($applications[$provider_slug]['draft'])) {
                unset($applications[$provider_slug]['draft']);
            }
            update_option('aoauth_applications', $applications);
            
            $this->debug->info('Test authentication successful', array(
                'provider' => $provider_slug,
                'user_email' => $extracted_email
            ));
            
            $this->logger->log('test_authentication_success', array(
                'provider' => $provider_slug,
                'test_passed' => true,
                'user_email' => $extracted_email
            ), get_current_user_id(), $provider_slug, 'success');
            
            $this->debug->log_end('AOAUTH_SSO_Handler::handle_test_callback', array('success' => true));
            
            wp_redirect(admin_url('admin.php?page=aoauth-wizard&test=success'));
            exit;
            
        } catch (Exception $e) {
            $this->debug->error('Test authentication failed', array('error' => $e->getMessage()));
            
            $this->logger->log('test_authentication_failed', array(
                'provider' => $provider_slug,
                'error' => $e->getMessage()
            ), get_current_user_id(), $provider_slug, 'error');
            
            $error_msg = urlencode($e->getMessage());
            $this->debug->log_end('AOAUTH_SSO_Handler::handle_test_callback', array('success' => false));
            
            wp_redirect(admin_url('admin.php?page=aoauth-wizard&test=failed&error=' . $error_msg));
            exit;
        }
    }
    
    private function get_redirect_by_user_role($user_id, $requested_redirect) {
    $user = get_userdata($user_id);
    $is_admin = $user && (user_can($user, 'administrator') || user_can($user, 'manage_options'));

    // If a redirect was explicitly requested and it's valid
    if (!empty($requested_redirect) && $this->security->validate_redirect_url($requested_redirect)) {
        // BUT: if the requested redirect is the admin dashboard and the user is not an admin, ignore it
        if (strpos($requested_redirect, admin_url()) === 0 && !$is_admin) {
            // fall through to role-based logic
        } else {
            return $requested_redirect;
        }
    }

    $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
    $role_redirects = isset($settings['role_redirects']) && is_array($settings['role_redirects'])
        ? $settings['role_redirects']
        : AOAUTH_Core::get_default_role_redirects();

    if ($user) {
        foreach ((array) $user->roles as $role_key) {
            if (!empty($role_redirects[$role_key])) {
                return $this->resolve_role_redirect_url($role_redirects[$role_key]);
            }
        }
    }

    return $is_admin ? admin_url() : home_url();
}

    private function resolve_role_redirect_url($redirect) {
        $redirect = trim((string) $redirect);
        if ($redirect === '') {
            return home_url();
        }

        if (strpos($redirect, '/') === 0) {
            return home_url($redirect);
        }

        if ($this->security->validate_redirect_url($redirect)) {
            return $redirect;
        }

        return home_url();
    }
    
    private function base64url_decode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $data .= str_repeat('=', 4 - $remainder);
        }
        return base64_decode(strtr($data, '-_', '+/'), true);
    }
    
    private function validate_id_token($id_token, $provider, $state_data = array()) {
        $claims = $this->extract_raw_claims_from_id_token($id_token);
        if (is_wp_error($claims)) {
            return $claims;
        }
        
        $now = time();
        if (empty($claims['exp']) || intval($claims['exp']) < $now) {
            return new WP_Error('invalid_id_token_exp', 'Identity token is expired.');
        }
        
        if (!empty($claims['nbf']) && intval($claims['nbf']) > ($now + 300)) {
            return new WP_Error('invalid_id_token_nbf', 'Identity token is not valid yet.');
        }
        
        if (!empty($state_data['oidc_nonce']) && (!isset($claims['nonce']) || !hash_equals($state_data['oidc_nonce'], (string) $claims['nonce']))) {
            return new WP_Error('invalid_id_token_nonce', 'Identity token nonce mismatch.');
        }
        
        if (!empty($provider['client_id'])) {
            $audiences = isset($claims['aud']) ? (array) $claims['aud'] : array();
            if (empty($audiences) || !in_array($provider['client_id'], $audiences, true)) {
                return new WP_Error('invalid_id_token_aud', 'Identity token audience mismatch.');
            }
        }
        
        if (!empty($claims['email']) && isset($claims['email_verified']) && $claims['email_verified'] === false) {
            return new WP_Error('email_not_verified', 'Email address is not verified by the identity provider.');
        }
        
        $provider = $this->with_default_oidc_metadata($provider);
        $issuer = $provider['issuer'] ?? '';
        if (!empty($issuer) && (!isset($claims['iss']) || rtrim($claims['iss'], '/') !== rtrim($issuer, '/'))) {
            return new WP_Error('invalid_id_token_iss', 'Identity token issuer mismatch.');
        }
        
        $jwks_uri = $provider['jwks_uri'] ?? '';
        if (!empty($jwks_uri)) {
            $signature_valid = $this->verify_id_token_signature($id_token, $jwks_uri);
            if (is_wp_error($signature_valid)) {
                return $signature_valid;
            }
        } else {
            $this->debug->warning('JWKS URI missing; ID token signature could not be verified', array(
                'provider' => $provider['provider_name'] ?? 'unknown'
            ));
        }
        
        return $claims;
    }
    
    private function with_default_oidc_metadata($provider) {
        $provider_name = $provider['provider_name'] ?? '';
        
        if (empty($provider['issuer']) || empty($provider['jwks_uri'])) {
            switch ($provider_name) {
                case 'google':
                    if (empty($provider['issuer'])) {
                        $provider['issuer'] = 'https://accounts.google.com';
                    }
                    if (empty($provider['jwks_uri'])) {
                        $provider['jwks_uri'] = 'https://www.googleapis.com/oauth2/v3/certs';
                    }
                    break;
                case 'apple':
                    if (empty($provider['issuer'])) {
                        $provider['issuer'] = 'https://appleid.apple.com';
                    }
                    if (empty($provider['jwks_uri'])) {
                        $provider['jwks_uri'] = 'https://appleid.apple.com/auth/keys';
                    }
                    break;
                default:
                    break;
            }
        }
        
        return $provider;
    }
    
    private function verify_id_token_signature($id_token, $jwks_uri) {
        $parts = explode('.', $id_token);
        if (count($parts) !== 3) {
            return new WP_Error('invalid_id_token', 'Invalid ID token format.');
        }
        
        $header_json = $this->base64url_decode($parts[0]);
        $header = json_decode($header_json, true);
        if (!is_array($header) || empty($header['alg']) || empty($header['kid'])) {
            return new WP_Error('invalid_id_token_header', 'Identity token header is invalid.');
        }
        
        if ($header['alg'] !== 'RS256') {
            return new WP_Error('unsupported_id_token_alg', 'Unsupported identity token signing algorithm.');
        }
        
        $keys = $this->get_jwks_keys($jwks_uri);
        if (is_wp_error($keys)) {
            return $keys;
        }
        
        foreach ($keys as $key) {
            if (($key['kid'] ?? '') !== $header['kid'] || ($key['kty'] ?? '') !== 'RSA') {
                continue;
            }
            
            $public_key = $this->jwk_to_pem($key);
            if (is_wp_error($public_key)) {
                return $public_key;
            }
            
            $signed_data = $parts[0] . '.' . $parts[1];
            $signature = $this->base64url_decode($parts[2]);
            $verified = openssl_verify($signed_data, $signature, $public_key, OPENSSL_ALGO_SHA256);
            
            if ($verified === 1) {
                return true;
            }
        }
        
        return new WP_Error('invalid_id_token_signature', 'Identity token signature is invalid.');
    }
    
    private function get_jwks_keys($jwks_uri) {
        $cache_key = 'aoauth_jwks_' . md5($jwks_uri);
        $keys = get_transient($cache_key);
        if (is_array($keys)) {
            return $keys;
        }
        
        $response = wp_remote_get($jwks_uri, array(
            'timeout' => 10,
            'sslverify' => true,
            'headers' => array('Accept' => 'application/json')
        ));
        
        if (is_wp_error($response)) {
            return new WP_Error('jwks_fetch_failed', $response->get_error_message());
        }
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_Error('jwks_fetch_failed', 'Could not retrieve provider signing keys.');
        }
        
        $jwks = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($jwks) || empty($jwks['keys']) || !is_array($jwks['keys'])) {
            return new WP_Error('invalid_jwks', 'Provider signing keys response is invalid.');
        }
        
        set_transient($cache_key, $jwks['keys'], HOUR_IN_SECONDS);
        return $jwks['keys'];
    }
    
    private function jwk_to_pem($jwk) {
        if (empty($jwk['n']) || empty($jwk['e'])) {
            return new WP_Error('invalid_jwk', 'Provider signing key is incomplete.');
        }
        
        $modulus = $this->base64url_decode($jwk['n']);
        $exponent = $this->base64url_decode($jwk['e']);
        if (empty($modulus) || empty($exponent)) {
            return new WP_Error('invalid_jwk', 'Provider signing key could not be decoded.');
        }
        
        $components = array(
            'modulus' => $this->asn1_encode_integer($modulus),
            'publicExponent' => $this->asn1_encode_integer($exponent)
        );
        
        $rsa_public_key = $this->asn1_encode_sequence($components['modulus'] . $components['publicExponent']);
        $rsa_oid = hex2bin('300d06092a864886f70d0101010500');
        $public_key = $this->asn1_encode_sequence($rsa_oid . $this->asn1_encode_bit_string($rsa_public_key));
        
        return "-----BEGIN PUBLIC KEY-----\n" . chunk_split(base64_encode($public_key), 64, "\n") . "-----END PUBLIC KEY-----\n";
    }
    
    private function asn1_encode_length($length) {
        if ($length <= 0x7F) {
            return chr($length);
        }
        
        $temp = ltrim(pack('N', $length), chr(0));
        return chr(0x80 | strlen($temp)) . $temp;
    }
    
    private function asn1_encode_integer($value) {
        if (ord($value[0]) > 0x7F) {
            $value = chr(0) . $value;
        }
        
        return chr(0x02) . $this->asn1_encode_length(strlen($value)) . $value;
    }
    
    private function asn1_encode_sequence($value) {
        return chr(0x30) . $this->asn1_encode_length(strlen($value)) . $value;
    }
    
    private function asn1_encode_bit_string($value) {
        return chr(0x03) . $this->asn1_encode_length(strlen($value) + 1) . chr(0) . $value;
    }
    
    private function normalize_user_data($user_data) {
        $email = '';
        if (isset($user_data['email'])) {
            $email = sanitize_email($user_data['email']);
        } elseif (isset($user_data['upn'])) {
            $email = sanitize_email($user_data['upn']);
        }
        
        $first_name = '';
        if (isset($user_data['given_name'])) {
            $first_name = sanitize_text_field($user_data['given_name']);
        } elseif (isset($user_data['first_name'])) {
            $first_name = sanitize_text_field($user_data['first_name']);
        }
        
        $last_name = '';
        if (isset($user_data['family_name'])) {
            $last_name = sanitize_text_field($user_data['family_name']);
        } elseif (isset($user_data['last_name'])) {
            $last_name = sanitize_text_field($user_data['last_name']);
        }
        
        $display_name = '';
        if (isset($user_data['name'])) {
            $display_name = sanitize_text_field($user_data['name']);
        } elseif (!empty($first_name) || !empty($last_name)) {
            $display_name = trim($first_name . ' ' . $last_name);
        }
        
        $username = '';
        if (isset($user_data['preferred_username'])) {
            $username = sanitize_user($user_data['preferred_username'], true);
        } elseif (!empty($email)) {
            $username = sanitize_user(current(explode('@', $email)), true);
        } elseif (isset($user_data['sub'])) {
            $username = sanitize_user('user_' . substr($user_data['sub'], 0, 10), true);
        }
        
        return array(
            'email' => $email,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'display_name' => $display_name,
            'username' => $username,
            'subject' => isset($user_data['sub']) ? $user_data['sub'] : $email,
        );
    }
    
    public function handle_login_action() {
    $debug = aoauth_core()->get_debug();
    
    // Check if this is a login request
    $is_login = (isset($_GET['oauth']) && $_GET['oauth'] === 'login') || 
                (isset($_GET['aoauth_action']) && $_GET['aoauth_action'] === 'login');
    
    if (!$is_login) {
        return;
    }
    
    $debug->info('=== OAUTH LOGIN DETECTED ===', array(
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? 'unknown',
        'GET_params' => $_GET,
        'provider' => $_GET['provider'] ?? 'unknown'
    ));
    
    // Skip test logins
    if (isset($_GET['aoauth_test'])) {
        $debug->debug('Test login detected, skipping main handler');
        return;
    }
    
    $provider_slug = isset($_GET['provider']) ? sanitize_text_field(wp_unslash($_GET['provider'])) : '';
    
    if (empty($provider_slug)) {
        $debug->error('Provider not specified');
        $this->logger->log('login_initiation_failed', array(
            'reason' => 'provider_not_specified'
        ), null, null, 'error');
        wp_die(esc_html__('Provider not specified.', 'aoauth-client-sso'));
    }
    
    $nonce = isset($_GET['_wpnonce']) ? $_GET['_wpnonce'] : (isset($_GET['nonce']) ? $_GET['nonce'] : '');
    if (!wp_verify_nonce($nonce, 'aoauth_login_' . $provider_slug)) {
        $debug->error('Nonce verification failed', array('provider' => $provider_slug));
        $this->logger->log('login_initiation_failed', array(
            'provider' => $provider_slug,
            'reason' => 'nonce_verification_failed'
        ), null, $provider_slug, 'error');
        wp_die(esc_html__('Security check failed.', 'aoauth-client-sso'));
    }
    
    $redirect_to = isset($_GET['redirect_to']) ? esc_url_raw(wp_unslash($_GET['redirect_to'])) : '';
    if (!empty($redirect_to) && !$this->security->validate_redirect_url($redirect_to)) {
        $debug->warning('Invalid redirect URL', array('redirect_to' => $redirect_to));
        $redirect_to = '';
    }
    
    $applications = get_option('aoauth_applications', array());
    $provider = $this->get_provider_by_slug($provider_slug, $applications);
    
    if (false === $provider) {
        $debug->error('Provider not found', array('slug' => $provider_slug));
        $this->logger->log('login_initiation_failed', array(
            'provider' => $provider_slug,
            'reason' => 'provider_not_found',
            'redirect_path' => $this->get_url_path_for_log($redirect_to)
        ), null, $provider_slug, 'error');
        wp_die(esc_html__('Provider not found.', 'aoauth-client-sso'));
    }
    
    if (empty($provider['enabled'])) {
        $debug->error('Provider not enabled', array('slug' => $provider_slug));
        $this->logger->log('login_initiation_failed', array(
            'provider' => $provider_slug,
            'reason' => 'provider_disabled',
            'redirect_path' => $this->get_url_path_for_log($redirect_to)
        ), null, $provider_slug, 'error');
        wp_die(esc_html__('Provider is not enabled.', 'aoauth-client-sso'));
    }
    
    $debug->info('Provider loaded', array(
        'provider_name' => $provider['provider_name'],
        'app_name' => $provider['app_name'],
        'send_credentials_in_header' => !empty($provider['send_credentials_in_header'])
    ));
    
    $requested_flow_id = isset($_GET['aoauth_flow_id']) ? sanitize_text_field(wp_unslash($_GET['aoauth_flow_id'])) : '';
    $bot_verification = $this->consume_bot_protection_verification_for_login($provider_slug, $requested_flow_id);
    if (false === $bot_verification) {
        $debug->error('Bot protection verification missing or expired');
        $this->logger->log('bot_protection_required_failed', array(
            'provider' => $provider_slug,
            'reason' => 'missing_or_expired_verification',
            'redirect_path' => $this->get_url_path_for_log($redirect_to)
        ), null, $provider_slug, 'error');
        wp_die(esc_html__('Bot verification is required. Please return to the login page and try again.', 'aoauth-client-sso'));
    }
    
    $state = $this->security->generate_secure_token(64);
    $oidc_nonce = $this->security->generate_secure_token(64);
    $flow_id = !empty($bot_verification['flow_id']) ? sanitize_text_field($bot_verification['flow_id']) : $this->security->generate_secure_token(18);
    
    $state_data = array(
        'provider' => $provider_slug,
        'redirect_to' => $redirect_to,
        'oidc_nonce' => $oidc_nonce,
        'flow_id' => $flow_id,
        'timestamp' => time(),
    );
    
    set_transient('aoauth_state_' . $state, $state_data, 10 * MINUTE_IN_SECONDS);
    $debug->debug('State transient created', array('state_key' => substr($state, 0, 10) . '...'));
    
    $provider['redirect_uri'] = $this->get_callback_url();
    
    $oauth_client = new AOAUTH_OAuth_Client($provider);
    $auth_url = $oauth_client->get_authorization_url($state, $oidc_nonce);
    
    $debug->info('Login initiated - redirecting to provider', array(
        'provider' => $provider_slug,
        'redirect_to' => $redirect_to ?: 'default',
        'auth_url' => $auth_url
    ));
    
    $this->logger->log('login_initiated', array(
        'flow_id' => $flow_id,
        'provider' => $provider_slug,
        'redirect_to' => $redirect_to,
        'redirect_path' => $this->get_url_path_for_log($redirect_to),
        'bot_protection' => !empty($bot_verification['type']) ? $bot_verification['type'] : 'none'
    ), null, $provider_slug, 'info');
    
    wp_redirect($auth_url);
    exit;
}
    
    private function consume_bot_protection_verification_for_login($provider_slug, $requested_flow_id = '') {
        $settings = get_option('aoauth_settings', array());
        $bot_enabled = (!empty($settings['enable_turnstile']) && !empty($settings['turnstile_site_key']) && !empty($settings['turnstile_secret_key']))
            || (!empty($settings['enable_recaptcha']) && !empty($settings['recaptcha_site_key']) && !empty($settings['recaptcha_secret_key']));
        
        if (!$bot_enabled) {
            return array(
                'verified' => true,
                'type' => 'none',
                'flow_id' => !empty($requested_flow_id) ? $requested_flow_id : $this->security->generate_secure_token(18),
                'provider' => $provider_slug
            );
        }
        
        $verification = isset($_GET['aoauth_bot_verification']) ? sanitize_text_field(wp_unslash($_GET['aoauth_bot_verification'])) : '';
        if (empty($verification)) {
            return false;
        }
        
        $transient_key = 'aoauth_bot_' . md5($verification);
        $verification_data = get_transient($transient_key);
        if (false === $verification_data) {
            return false;
        }
        
        delete_transient($transient_key);
        if (empty($verification_data['verified'])) {
            return false;
        }

        $this->logger->log('bot_verification_consumed', array(
            'flow_id' => $verification_data['flow_id'] ?? '',
            'provider' => $provider_slug,
            'type' => $verification_data['type'] ?? 'unknown'
        ), null, $provider_slug, 'success');

        return $verification_data;
    }

    private function get_url_path_for_log($url) {
        if (empty($url)) {
            return '';
        }

        $parts = wp_parse_url($url);
        if (!is_array($parts)) {
            return '';
        }

        $path = $parts['path'] ?? '/';
        if (!empty($parts['query'])) {
            $path .= '?' . $parts['query'];
        }

        return $path;
    }
    
    private function get_provider_by_slug($slug, $applications) {
        foreach ($applications as $app_id => $app) {
            if ($app_id === $slug || (isset($app['slug']) && $app['slug'] === $slug)) {
                return $app;
            }
        }
        return false;
    }
    
    private function get_callback_url() {
        return add_query_arg('oauth', 'callback', wp_login_url());
    }
    
    private function redirect_with_error($message) {
        $this->debug->error('Redirecting with error', array('message' => $message));
        
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('aOAUTH Client SSO Error: ' . $message);
        }
        
        $public_message = __('Single sign-on could not be completed. Please try again or contact the site administrator.', 'aoauth-client-sso');
        $login_url = add_query_arg('oauth_error', urlencode($public_message), wp_login_url());
        wp_safe_redirect($login_url);
        exit;
    }
    
    public function wp_login_handler($user_login, $user) {
        $this->debug->log_start('AOAUTH_SSO_Handler::wp_login_handler', array('user_login' => $user_login));
        
        $provider = get_user_meta($user->ID, '_aoauth_provider', true);
        if ($provider) {
            $this->logger->log('wp_login_sso', array('provider' => $provider), $user->ID, $provider, 'success');
            $this->debug->info('SSO login recorded', array('user_id' => $user->ID, 'provider' => $provider));
        }
        
        $this->debug->log_end('AOAUTH_SSO_Handler::wp_login_handler');
    }
    
    public function user_register_handler($user_id) {
        $this->debug->debug('User registered', array('user_id' => $user_id));
    }
    
    public function register_unlink_shortcode() {
        add_shortcode('aoauth_unlink_account', array($this, 'render_unlink_shortcode'));
        $this->debug->debug('Unlink shortcode registered');
    }
    
    public function render_unlink_shortcode($atts) {
        $this->debug->log_start('render_unlink_shortcode');
        
        if (!is_user_logged_in()) {
            $this->debug->debug('User not logged in, showing login message');
            $this->debug->log_end('render_unlink_shortcode');
            return '<p>' . esc_html__('Please log in to manage your SSO connections.', 'aoauth-client-sso') . '</p>';
        }
        
        $user_id = get_current_user_id();
        $linked_provider = get_user_meta($user_id, '_aoauth_provider', true);
        $applications = get_option('aoauth_applications', array());
        $provider_data = null;
        
        if ($linked_provider && isset($applications[$linked_provider])) {
            $provider_data = $applications[$linked_provider];
        }
        
        wp_enqueue_style('aoauth-account-unlink', AOAUTH_PLUGIN_URL . 'public/css/login-single-sign-on.css', array(), AOAUTH_VERSION);
        wp_enqueue_script('aoauth-account-unlink', AOAUTH_PLUGIN_URL . 'public/js/account-unlink.js', array('jquery'), AOAUTH_VERSION, true);
        wp_localize_script('aoauth-account-unlink', 'aoauth_account_unlink', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'translations' => array(
                'confirm_unlink' => __('Are you sure you want to disconnect your SSO account?', 'aoauth-client-sso'),
                'unlink_success' => __('SSO account unlinked successfully', 'aoauth-client-sso'),
                'unlink_error' => __('Error unlinking SSO account', 'aoauth-client-sso'),
                'no_provider' => __('No SSO provider linked', 'aoauth-client-sso'),
            )
        ));
        
        ob_start();
        ?>
        <div class="aoauth-frontend-unlink">
            <?php if ($linked_provider && $provider_data): ?>
                <div class="aoauth-frontend-connected">
                    <p>
                        <strong><?php esc_html_e('Connected SSO Account:', 'aoauth-client-sso'); ?></strong>
                        <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $linked_provider . '.png'); ?>" 
                             alt="<?php echo esc_attr($provider_data['provider_name']); ?>"
                             class="aoauth-frontend-provider-icon"
                             onerror="this.style.display='none'">
                        <?php echo esc_html($provider_data['app_name']); ?>
                    </p>
                    <button type="button" 
                            class="aoauth-frontend-unlink-btn aoauth-unlink-profile-btn"
                            data-user-id="<?php echo esc_attr($user_id); ?>"
                            data-provider="<?php echo esc_attr($linked_provider); ?>"
                            data-nonce="<?php echo esc_attr(wp_create_nonce('aoauth_unlink_' . $user_id)); ?>">
                        <?php esc_html_e('Disconnect SSO Account', 'aoauth-client-sso'); ?>
                    </button>
                </div>
            <?php else: ?>
                <p><?php esc_html_e('No SSO account is currently connected to your WordPress account.', 'aoauth-client-sso'); ?></p>
                <p class="description">
                    <?php esc_html_e('To connect an SSO account, log in using your preferred provider on the login page.', 'aoauth-client-sso'); ?>
                </p>
            <?php endif; ?>
        </div>
        
        <?php
        
        $this->debug->log_end('render_unlink_shortcode');
        
        return ob_get_clean();
    }
}
