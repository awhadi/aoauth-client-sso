<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_User_Manager {
    
    public function init() {
        add_action('init', array($this, 'handle_link_confirmation'));
    }
    
    public function process_oauth_user($user_info, $provider, $provider_slug) {
        if (empty($user_info['email'])) {
            return new WP_Error('missing_email', 'No email address provided by the OAuth provider.');
        }
        
        $settings = get_option('aoauth_settings', array());
        $allow_account_linking = isset($settings['allow_account_linking']) ? $settings['allow_account_linking'] : 0;
        
        $existing_user = get_user_by('email', $user_info['email']);
        
        if ($existing_user) {
            $linked_provider = get_user_meta($existing_user->ID, '_aoauth_provider', true);
            
            if ($linked_provider === $provider_slug) {
                return $this->login_existing_user($existing_user, $user_info, $provider, $provider_slug);
            }
            
            if ($allow_account_linking) {
                return $this->require_account_linking($existing_user, $user_info, $provider, $provider_slug);
            } else {
                return new WP_Error(
                    'account_exists',
                    sprintf(
                        'An account with email %s already exists. Please log in with your password first, then link your %s account from your profile.',
                        $user_info['email'],
                        $provider['app_name'] ?? $provider['provider_name']
                    )
                );
            }
        }
        
        $create_user = isset($settings['auto_create_users']) ? $settings['auto_create_users'] : 1;
        
        if (!$create_user) {
            return new WP_Error(
                'user_not_found',
                sprintf('No account found with email %s. Please create an account first.', $user_info['email'])
            );
        }
        
        return $this->create_new_user($user_info, $provider, $provider_slug);
    }
    
    private function require_account_linking($existing_user, $user_info, $provider, $provider_slug) {
        $linking_data = array(
            'user_id' => $existing_user->ID,
            'provider_slug' => $provider_slug,
            'provider_name' => $provider['app_name'] ?? $provider['provider_name'],
            'email' => $user_info['email'],
            'user_data' => $user_info,
            'timestamp' => time(),
        );
        
        $linking_key = 'aoauth_link_' . wp_generate_password(32, false, false);
        set_transient($linking_key, $linking_data, 10 * MINUTE_IN_SECONDS);
        
        $confirm_url = add_query_arg(array(
            'oauth_link' => 'confirm',
            'key' => $linking_key,
            'email' => urlencode($user_info['email']),
            'provider' => $provider_slug
        ), wp_login_url());
        
        wp_safe_redirect($confirm_url);
        exit;
    }
    
    public function handle_link_confirmation() {
        if (!isset($_GET['oauth_link']) || $_GET['oauth_link'] !== 'confirm') {
            return;
        }
        
        $key = isset($_GET['key']) ? sanitize_text_field($_GET['key']) : '';
        $email = isset($_GET['email']) ? sanitize_email($_GET['email']) : '';
        $provider_slug = isset($_GET['provider']) ? sanitize_text_field($_GET['provider']) : '';
        
        if (empty($key) || empty($email) || empty($provider_slug)) {
            wp_die(esc_html__('Invalid linking request.', 'aoauth-client-sso'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aoauth_link_password'])) {
            $password = $_POST['aoauth_link_password'];
            $nonce = $_POST['_wpnonce'] ?? '';
            
            if (!wp_verify_nonce($nonce, 'aoauth_link_confirm_' . $key)) {
                wp_die(esc_html__('Security check failed.', 'aoauth-client-sso'));
            }
            
            $result = $this->confirm_account_linking($key, $password);
            
            if (is_wp_error($result)) {
                $error_message = $result->get_error_message();
                $this->render_linking_form($key, $email, $provider_slug, $error_message);
                return;
            }
            
            wp_clear_auth_cookie();
            wp_set_auth_cookie($result, true);
            wp_set_current_user($result);
            
            $redirect_to = home_url();
            if (user_can($result, 'manage_options')) {
                $redirect_to = admin_url();
            }
            wp_safe_redirect($redirect_to);
            exit;
        }
        
        $this->render_linking_form($key, $email, $provider_slug);
        exit;
    }
    
    private function render_lockdown_message($message, $provider_slug) {
        $provider_name = ucfirst($provider_slug);
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php esc_html_e('Account Temporarily Locked', 'aoauth-client-sso'); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .aoauth-lockdown-container {
                    background: #fff;
                    border-radius: 16px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
                    max-width: 480px;
                    width: 100%;
                    padding: 40px;
                    text-align: center;
                }
                .lockdown-icon { width: 80px; height: 80px; background: #fee2e2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; }
                .lockdown-icon span { font-size: 48px; color: #dc2626; }
                h2 { color: #111827; font-size: 24px; margin-bottom: 16px; }
                p { color: #4b5563; font-size: 16px; line-height: 1.6; margin-bottom: 24px; }
                .lockdown-message { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-bottom: 24px; text-align: left; }
                .back-button {
                    display: inline-block;
                    background: #667eea;
                    color: #fff;
                    text-decoration: none;
                    padding: 12px 28px;
                    border-radius: 8px;
                    font-weight: 500;
                    transition: all 0.3s ease;
                }
                .back-button:hover { background: #5a6fd6; transform: translateY(-2px); }
            </style>
        </head>
        <body>
            <div class="aoauth-lockdown-container">
                <div class="lockdown-icon"><span>🔒</span></div>
                <h2><?php esc_html_e('Account Temporarily Locked', 'aoauth-client-sso'); ?></h2>
                <div class="lockdown-message"><p><?php echo esc_html($message); ?></p></div>
                <a href="<?php echo esc_url(wp_login_url()); ?>" class="back-button"><?php esc_html_e('Return to Login', 'aoauth-client-sso'); ?></a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    private function render_linking_form($key, $email, $provider_slug, $error = '') {
        $linking_data = get_transient($key);
        if ($linking_data) {
            $security = aoauth_core()->get_security();
            $rate_check = $security->check_linking_rate_limit($linking_data['user_id'], $key);
            
            if (is_wp_error($rate_check)) {
                $this->render_lockdown_message($rate_check->get_error_message(), $provider_slug);
                return;
            }
        }
        
        wp_enqueue_style('aoauth-public', AOAUTH_PLUGIN_URL . 'public/css/public-style.css', array(), AOAUTH_VERSION);
        
        $provider_name = ucfirst($provider_slug);
        ?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?>>
        <head>
            <meta charset="<?php bloginfo('charset'); ?>">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <title><?php esc_html_e('Confirm Account Linking', 'aoauth-client-sso'); ?></title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    min-height: 100vh;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 20px;
                }
                .aoauth-link-container {
                    background: #fff;
                    border-radius: 16px;
                    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
                    max-width: 480px;
                    width: 100%;
                    padding: 40px;
                }
                .provider-icon { text-align: center; margin-bottom: 24px; }
                .provider-icon img { width: 64px; height: 64px; border-radius: 12px; }
                h2 { color: #111827; font-size: 24px; margin-bottom: 12px; text-align: center; }
                .description { color: #6b7280; font-size: 14px; text-align: center; margin-bottom: 32px; }
                .email-badge { background: #f3f4f6; padding: 10px 16px; border-radius: 8px; margin-bottom: 24px; text-align: center; }
                .error-message { background: #fee2e2; border-left: 4px solid #dc2626; padding: 12px 16px; border-radius: 8px; margin-bottom: 24px; color: #991b1b; }
                .aoauth-link-form { display: flex; flex-direction: column; gap: 20px; }
                .aoauth-link-form input[type="password"] {
                    width: 100%;
                    padding: 12px 16px;
                    border: 2px solid #e5e7eb;
                    border-radius: 10px;
                    font-size: 16px;
                }
                .aoauth-link-form input[type="password"]:focus {
                    outline: none;
                    border-color: #667eea;
                }
                .submit-button {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: #fff;
                    border: none;
                    border-radius: 10px;
                    padding: 14px 24px;
                    font-size: 16px;
                    font-weight: 600;
                    cursor: pointer;
                }
                .submit-button:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(102,126,234,0.4); }
                .cancel-link { text-align: center; margin-top: 20px; }
                .cancel-link a { color: #9ca3af; text-decoration: none; font-size: 14px; }
                .cancel-link a:hover { color: #667eea; }
            </style>
        </head>
        <body>
            <div class="aoauth-link-container">
                <div class="provider-icon">
                    <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $provider_slug . '.png'); ?>" 
                         alt="<?php echo esc_attr($provider_name); ?>"
                         onerror="this.src='<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png'); ?>'">
                </div>
                <h2><?php echo sprintf(esc_html__('Link Your %s Account', 'aoauth-client-sso'), esc_html($provider_name)); ?></h2>
                <div class="email-badge"><strong><?php esc_html_e('Email:', 'aoauth-client-sso'); ?></strong> <?php echo esc_html($email); ?></div>
                <?php if (!empty($error)) : ?>
                    <div class="error-message"><?php echo esc_html($error); ?></div>
                <?php endif; ?>
                <form method="post" class="aoauth-link-form">
                    <input type="password" name="aoauth_link_password" placeholder="<?php esc_attr_e('Your WordPress password', 'aoauth-client-sso'); ?>" required autofocus>
                    <?php wp_nonce_field('aoauth_link_confirm_' . $key, '_wpnonce'); ?>
                    <button type="submit" class="submit-button"><?php esc_html_e('Confirm & Link Account', 'aoauth-client-sso'); ?></button>
                    <div class="cancel-link"><a href="<?php echo esc_url(wp_login_url()); ?>"><?php esc_html_e('Cancel', 'aoauth-client-sso'); ?></a></div>
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
    
    public function confirm_account_linking($linking_key, $password) {
        $security = aoauth_core()->get_security();
        
        $linking_data = get_transient($linking_key);
        
        if (false === $linking_data) {
            return new WP_Error('expired', __('Linking request expired. Please try again.', 'aoauth-client-sso'));
        }
        
        $user = get_userdata($linking_data['user_id']);
        
        if (!$user) {
            return new WP_Error('invalid_user', __('User not found.', 'aoauth-client-sso'));
        }
        
        $settings = get_option('aoauth_settings', array());
        $account_linking_enabled = !empty($settings['allow_account_linking']);
        
        if ($account_linking_enabled) {
            $rate_check = $security->check_linking_rate_limit($user->ID, $linking_key);
            if (is_wp_error($rate_check)) {
                return $rate_check;
            }
        }
        
        if (!wp_check_password($password, $user->user_pass, $user->ID)) {
            if ($account_linking_enabled) {
                $security->record_linking_failure($user->ID, $linking_key);
                $attempts_data = $security->check_linking_rate_limit($user->ID, $linking_key);
                $remaining = 0;
                if (is_array($attempts_data)) {
                    $remaining = $attempts_data['max'] - $attempts_data['attempts'] - 1;
                }
                if ($remaining > 0) {
                    return new WP_Error('wrong_password', sprintf(__('Incorrect password. %d attempts remaining.', 'aoauth-client-sso'), $remaining));
                } else {
                    return new WP_Error('wrong_password', __('Incorrect password. Too many failed attempts. Your account is temporarily locked.', 'aoauth-client-sso'));
                }
            } else {
                return new WP_Error('wrong_password', __('Incorrect password. Please try again.', 'aoauth-client-sso'));
            }
        }
        
        if ($account_linking_enabled) {
            $security->clear_linking_attempts($user->ID, $linking_key);
        }
        
        update_user_meta($user->ID, '_aoauth_provider', $linking_data['provider_slug']);
        update_user_meta($user->ID, '_aoauth_linked_' . $linking_data['provider_slug'], time());
        
        delete_transient($linking_key);
        
        $logger = aoauth_core()->get_logger();
        $logger->log('account_linked', array(
            'provider' => $linking_data['provider_slug']
        ), $user->ID, $linking_data['provider_slug'], 'success');
        
        return $user->ID;
    }
    
    private function login_existing_user($user, $user_info, $provider, $provider_slug) {
        $can_login = apply_filters('aoauth_can_login', true, $user, $user_info, $provider);
        
        if (!$can_login) {
            return new WP_Error('login_blocked', 'Login blocked by administrator.');
        }
        
        $current_provider = get_user_meta($user->ID, '_aoauth_provider', true);
        if (empty($current_provider)) {
            update_user_meta($user->ID, '_aoauth_provider', $provider_slug);
        }
        
        update_user_meta($user->ID, '_aoauth_last_login', time());
        
        do_action('aoauth_user_login', $user->ID, $user_info, $provider);
        
        return $user->ID;
    }
    
    private function create_new_user($user_info, $provider, $provider_slug) {
        $settings = get_option('aoauth_settings', array());
        
        if (!is_email($user_info['email'])) {
            return new WP_Error('invalid_email', 'Invalid email address provided.');
        }
        
        $username = !empty($user_info['username']) 
            ? $user_info['username'] 
            : sanitize_user(current(explode('@', $user_info['email'])), true);
        
        $username = $this->generate_unique_username($username);
        $role = isset($settings['default_role']) ? $settings['default_role'] : 'subscriber';
        
        if (!empty($provider['enable_advanced_mapping']) && !empty($provider['role_mapping'])) {
            $role_path = isset($provider['role_mapping']['attribute_path']) ? $provider['role_mapping']['attribute_path'] : '';
            $role_rules = isset($provider['role_mapping']['rules']) ? $provider['role_mapping']['rules'] : '';
            
            if (!empty($role_path) && !empty($role_rules)) {
                    $provider_role = AOAUTH_Security::get_nested_value($user_info, $role_path);
                if (!empty($provider_role)) {
                    $lines = explode("\n", $role_rules);
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (empty($line)) continue;
                        $parts = explode(':', $line, 2);
                        if (count($parts) === 2) {
                            $provider_role_value = trim($parts[0]);
                            $wp_role = trim($parts[1]);
                            if ($provider_role === $provider_role_value && wp_roles()->is_role($wp_role)) {
                                $role = $wp_role;
                                break;
                            }
                        }
                    }
                }
            }
        }
        
        if (!wp_roles()->is_role($role)) {
            $role = 'subscriber';
        }
        
        $user_data = array(
            'user_login' => $username,
            'user_email' => $user_info['email'],
            'user_pass' => wp_generate_password(32, true, true),
            'first_name' => isset($user_info['first_name']) ? $user_info['first_name'] : '',
            'last_name' => isset($user_info['last_name']) ? $user_info['last_name'] : '',
            'display_name' => isset($user_info['display_name']) ? $user_info['display_name'] : $username,
            'role' => $role,
            'user_registered' => current_time('mysql'),
        );
        
        $user_data = apply_filters('aoauth_user_data', $user_data, $user_info, $provider);
        $user_id = wp_insert_user($user_data);
        
        if (is_wp_error($user_id)) {
            return new WP_Error('user_creation_failed', 'Failed to create user account. Please try again.');
        }
        
        update_user_meta($user_id, '_aoauth_provider', $provider_slug);
        update_user_meta($user_id, '_aoauth_created', time());
        
        do_action('aoauth_user_created', $user_id, $user_info, $provider);
        
        return $user_id;
    }
    
    private function generate_unique_username($username) {
        $username = sanitize_user($username, true);
        
        if (empty($username)) {
            $username = 'user';
        }
        
        $original_username = $username;
        $suffix = 1;
        
        while (username_exists($username)) {
            $username = $original_username . $suffix;
            $suffix++;
        }
        
        return $username;
    }
    
}
