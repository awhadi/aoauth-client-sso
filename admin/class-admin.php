<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Admin {
    private $security;
    private $logger;
    
    public function __construct() {
        $this->security = new AOAUTH_Security();
        $this->logger = new AOAUTH_Logger();
    }
    
    public function init() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        add_action('wp_ajax_aoauth_save_application', array($this, 'ajax_save_application'));
        add_action('wp_ajax_aoauth_delete_application', array($this, 'ajax_delete_application'));
        add_action('wp_ajax_aoauth_save_settings', array($this, 'ajax_save_settings'));
        add_action('wp_ajax_aoauth_discover_endpoints', array($this, 'ajax_discover_endpoints'));
        add_action('wp_ajax_aoauth_toggle_provider', array($this, 'ajax_toggle_provider'));
        add_action('wp_ajax_aoauth_get_logs', array($this, 'ajax_get_logs'));
        add_action('wp_ajax_aoauth_clear_logs', array($this, 'ajax_clear_logs'));
        add_action('wp_ajax_aoauth_export_logs', array($this, 'ajax_export_logs'));
        add_action('wp_ajax_aoauth_export_config', array($this, 'ajax_export_config'));
        add_action('wp_ajax_aoauth_import_config', array($this, 'ajax_import_config'));
        add_action('wp_ajax_aoauth_test_connection', array($this, 'ajax_test_connection'));
        add_action('wp_ajax_aoauth_preview_theme', array($this, 'ajax_preview_theme'));
        add_action('wp_ajax_aoauth_factory_reset', array($this, 'ajax_factory_reset'));
        add_action('wp_ajax_aoauth_clear_bot_verifications', array($this, 'ajax_clear_bot_verifications'));
        add_action('wp_ajax_aoauth_clear_linking_lockouts', array($this, 'ajax_clear_linking_lockouts'));
        add_action('wp_ajax_aoauth_clear_oauth_temp_data', array($this, 'ajax_clear_oauth_temp_data'));
        add_action('wp_ajax_aoauth_run_log_cleanup', array($this, 'ajax_run_log_cleanup'));
        add_action('wp_ajax_aoauth_reschedule_log_cleanup', array($this, 'ajax_reschedule_log_cleanup'));
        add_action('wp_ajax_aoauth_toggle_deep_debug', array($this, 'ajax_toggle_deep_debug'));
        add_action('wp_ajax_aoauth_client_debug_log', array($this, 'ajax_client_debug_log'));
        add_action('wp_ajax_nopriv_aoauth_client_debug_log', array($this, 'ajax_client_debug_log'));
        
        // Account unlinking AJAX handlers
        add_action('wp_ajax_aoauth_unlink_account', array($this, 'ajax_unlink_account'));
        add_action('wp_ajax_aoauth_bulk_unlink_accounts', array($this, 'ajax_bulk_unlink_accounts'));
        
        // Profile page and users table hooks
        add_action('show_user_profile', array($this, 'render_profile_unlink_section'));
        add_action('edit_user_profile', array($this, 'render_profile_unlink_section'));
        add_filter('manage_users_columns', array($this, 'add_unlink_column'));
        add_filter('manage_users_custom_column', array($this, 'render_unlink_column'), 10, 3);
        add_filter('bulk_actions-users', array($this, 'add_bulk_unlink_action'));
        add_filter('handle_bulk_actions-users', array($this, 'handle_bulk_unlink_action'), 10, 3);
        
        // Bot protection AJAX handlers
        add_action('wp_ajax_nopriv_aoauth_verify_turnstile', array($this, 'ajax_verify_turnstile'));
        add_action('wp_ajax_aoauth_verify_turnstile', array($this, 'ajax_verify_turnstile'));
        add_action('wp_ajax_nopriv_aoauth_verify_recaptcha', array($this, 'ajax_verify_recaptcha'));
        add_action('wp_ajax_aoauth_verify_recaptcha', array($this, 'ajax_verify_recaptcha'));
        
        add_filter('plugin_action_links_' . AOAUTH_PLUGIN_BASENAME, array($this, 'add_plugin_action_links'));
    }
    
    public function add_admin_menu() {
        global $submenu;
        
        // Main menu with plugin name
        add_menu_page(
            __('aOAUTH Client SSO', 'aoauth-client-sso'),
            __('aOAUTH SSO', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-providers',
            array($this, 'render_connect_page'),
            AOAUTH_PLUGIN_URL . 'admin/images/menu-icon.svg',
            30
        );
        
        remove_submenu_page('aoauth-providers', 'aoauth-providers');
        
        add_submenu_page(
            'aoauth-providers',
            __('Providers', 'aoauth-client-sso'),
            __('Providers', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-providers',
            array($this, 'render_connect_page')
        );
        
        // Submenu: Settings
        add_submenu_page(
            'aoauth-providers',
            __('Sign-In Experience', 'aoauth-client-sso'),
            __('Sign-In Experience', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-sign-in-experience',
            array($this, 'render_general_page')
        );

        add_submenu_page(
            'aoauth-providers',
            __('User Management', 'aoauth-client-sso'),
            __('User Management', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-user-management',
            array($this, 'render_user_management_page')
        );

        add_submenu_page(
            'aoauth-providers',
            __('Security', 'aoauth-client-sso'),
            __('Security', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-security',
            array($this, 'render_security_page')
        );

        add_submenu_page(
            'aoauth-providers',
            __('Tools', 'aoauth-client-sso'),
            __('Tools', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-tools',
            array($this, 'render_tools_page')
        );

        add_submenu_page(
            null,
            __('Settings', 'aoauth-client-sso'),
            __('Settings', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-settings',
            array($this, 'render_general_page')
        );
        
        // Submenu: Logs
        add_submenu_page(
            'aoauth-providers',
            __('Logs', 'aoauth-client-sso'),
            __('Logs', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-logs',
            array($this, 'render_logs_page')
        );
        
        // Hidden page: Setup Wizard
        add_submenu_page(
            null,
            __('Setup Wizard', 'aoauth-client-sso'),
            __('Setup Wizard', 'aoauth-client-sso'),
            'manage_options',
            'aoauth-wizard',
            array($this, 'render_wizard_page')
        );
    }
    
    public function enqueue_admin_assets($hook) {
        wp_enqueue_style(
            'aoauth-admin-menu-icon',
            AOAUTH_PLUGIN_URL . 'admin/css/admin-menu-icon.css',
            array(),
            AOAUTH_VERSION
        );

        // Allow on all aoauth pages, profile, user-edit, AND the main users page
        if (strpos($hook, 'aoauth') === false && 
            $hook !== 'profile.php' && 
            $hook !== 'user-edit.php' &&
            $hook !== 'users.php') {
            return;
        }
        
        wp_enqueue_style(
            'aoauth-admin',
            AOAUTH_PLUGIN_URL . 'admin/css/admin-style.css',
            array(),
            AOAUTH_VERSION
        );

        wp_enqueue_style(
            'aoauth-toast-notifications',
            AOAUTH_PLUGIN_URL . 'admin/css/toast-notices.css',
            array(),
            AOAUTH_VERSION
        );

        wp_enqueue_script(
            'aoauth-toast-notices',
            AOAUTH_PLUGIN_URL . 'admin/js/toast-notices.js',
            array('jquery'),
            AOAUTH_VERSION,
            true
        );
        
        wp_enqueue_script(
            'aoauth-admin',
            AOAUTH_PLUGIN_URL . 'admin/js/admin-dashboard.js',
            array('jquery', 'jquery-ui-sortable', 'aoauth-toast-notices'),
            AOAUTH_VERSION,
            true
        );

        $current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
        if (in_array($current_page, array('aoauth-settings', 'aoauth-sign-in-experience', 'aoauth-user-management', 'aoauth-security', 'aoauth-tools'), true)) {
            wp_enqueue_script(
                'aoauth-settings-controls',
                AOAUTH_PLUGIN_URL . 'admin/js/settings-form-controls.js',
                array('jquery', 'aoauth-admin'),
                AOAUTH_VERSION,
                true
            );
        }
        
        $available_themes = aoauth_core()->get_available_themes();
        
        wp_localize_script('aoauth-admin', 'aoauth_admin', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aoauth_admin_nonce'),
            'plugin_url' => AOAUTH_PLUGIN_URL,
            'available_themes' => $available_themes,
            'providers' => aoauth_core()->get_providers_manager()->get_providers_list(),
            'translations' => array(
                'confirm_delete' => __('Are you sure you want to delete this application?', 'aoauth-client-sso'),
                'save_success' => __('Settings saved successfully', 'aoauth-client-sso'),
                'save_error' => __('Error saving settings', 'aoauth-client-sso'),
                'test_success' => __('Configuration validated! Provider enabled.', 'aoauth-client-sso'),
                'test_error' => __('Configuration validation failed', 'aoauth-client-sso'),
                'discover_success' => __('Endpoints discovered successfully', 'aoauth-client-sso'),
                'discover_error' => __('Failed to discover endpoints', 'aoauth-client-sso'),
                'copied' => __('Copied to clipboard', 'aoauth-client-sso'),
                'confirm_disable_logs' => __('Disabling logs will stop recording events. Are you sure?', 'aoauth-client-sso'),
                'confirm_clear_logs' => __('Are you sure you want to clear all logs? This action cannot be undone.', 'aoauth-client-sso'),
                'import_success' => __('Configuration imported successfully', 'aoauth-client-sso'),
                'import_error' => __('Failed to import configuration', 'aoauth-client-sso'),
                'deep_debug_enabled' => __('Deep Debug enabled in wp-config.php.', 'aoauth-client-sso'),
                'deep_debug_disabled' => __('Deep Debug disabled in wp-config.php.', 'aoauth-client-sso'),
                // New translations for unlinking
                'confirm_unlink' => __('Are you sure you want to unlink this SSO account?', 'aoauth-client-sso'),
                'confirm_bulk_unlink' => __('Are you sure you want to unlink SSO accounts for selected users?', 'aoauth-client-sso'),
                'confirm_disconnect_user' => __('Are you sure you want to disconnect SSO for %s?', 'aoauth-client-sso'),
                'confirm_disconnect_selected' => __('Are you sure you want to disconnect SSO for %d selected user(s)?', 'aoauth-client-sso'),
                'disconnect_sso_accounts' => __('Disconnect SSO Accounts', 'aoauth-client-sso'),
                'selected_users_sso_warning' => __('These users will no longer be able to log in using their SSO providers.', 'aoauth-client-sso'),
                'yes_disconnect' => __('Yes, Disconnect', 'aoauth-client-sso'),
                'cancel' => __('Cancel', 'aoauth-client-sso'),
                'unlink_success' => __('SSO account unlinked successfully', 'aoauth-client-sso'),
                'unlink_error' => __('Error unlinking SSO account', 'aoauth-client-sso'),
                'unlink_warning' => __('After unlinking, this user will no longer be able to log in using this SSO provider.', 'aoauth-client-sso'),
                'no_provider' => __('No SSO provider linked', 'aoauth-client-sso'),
                'unlink' => __('Unlink', 'aoauth-client-sso'),
                'action_not_allowed' => __('Action not allowed', 'aoauth-client-sso'),
                'select_users_disconnect' => __('Please select users to disconnect.', 'aoauth-client-sso'),
                'bulk_unlink_error' => __('Error processing bulk unlink request.', 'aoauth-client-sso'),
                'bulk_unlink_success' => __('Successfully disconnected %d account(s).', 'aoauth-client-sso'),
                'bulk_unlink_failed' => __('Failed to disconnect %d account(s).', 'aoauth-client-sso'),
                'error_saving_settings' => __('Error saving settings', 'aoauth-client-sso'),
                'error_toggling_provider' => __('Error toggling provider', 'aoauth-client-sso'),
                'error_deleting_application' => __('Error deleting application', 'aoauth-client-sso'),
                'error_clearing_logs' => __('Error clearing logs', 'aoauth-client-sso'),
                'copied_to_clipboard' => __('Copied to clipboard!', 'aoauth-client-sso'),
                'backup_passwords_mismatch' => __('Backup passwords do not match. Settings were not downloaded.', 'aoauth-client-sso'),
                'import_failed' => __('Import failed', 'aoauth-client-sso'),
                'factory_reset_failed' => __('Factory reset failed', 'aoauth-client-sso'),
                'maintenance_failed' => __('Maintenance action failed.', 'aoauth-client-sso'),
                'working' => __('Working...', 'aoauth-client-sso'),
                'processing' => __('Processing...', 'aoauth-client-sso'),
                'apply' => __('Apply', 'aoauth-client-sso'),
                'enabled' => __('Enabled', 'aoauth-client-sso'),
                'off' => __('Off', 'aoauth-client-sso'),
                'enable_on_save' => __('Enable on save', 'aoauth-client-sso'),
                'disable_on_save' => __('Disable on save', 'aoauth-client-sso'),
                'wp_config_update_failed' => __('Could not update wp-config.php.', 'aoauth-client-sso'),
                'error_loading_logs' => __('Error loading logs', 'aoauth-client-sso'),
                'security_check_failed_refresh' => __('Security check failed. Please refresh the page and try again.', 'aoauth-client-sso'),
                'server_error_try_again' => __('Server error occurred. Please try again.', 'aoauth-client-sso'),
                'retention_updated' => __('Log retention period updated. Changes will take effect on next cron job.', 'aoauth-client-sso')
            )
        ));
        
        if ($hook === 'admin_page_aoauth-wizard' || $hook === 'aoauth-sso_page_aoauth-wizard') {
            wp_enqueue_style(
                'aoauth-wizard',
                AOAUTH_PLUGIN_URL . 'admin/css/wizard-style.css',
                array(),
                AOAUTH_VERSION
            );
            
            wp_enqueue_script(
                'aoauth-wizard',
                AOAUTH_PLUGIN_URL . 'admin/js/wizard-script.js',
                array('jquery', 'aoauth-toast-notices'),
                AOAUTH_VERSION,
                true
            );
            
            $edit_app_id = isset($_GET['edit']) ? sanitize_text_field($_GET['edit']) : '';
            $applications = get_option('aoauth_applications', array());
            $edit_app_data = null;
            
            if (!empty($edit_app_id) && isset($applications[$edit_app_id])) {
                $edit_app_data = $applications[$edit_app_id];
                if (!empty($edit_app_data['client_secret'])) {
                    $edit_app_data['client_secret'] = aoauth_core()->get_security()->decrypt($edit_app_data['client_secret']);
                }
            }
            
            wp_localize_script('aoauth-wizard', 'aoauth_admin', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('aoauth_admin_nonce'),
                'debug_enabled' => aoauth_core()->get_debug()->is_enabled(),
                'providers' => aoauth_core()->get_providers_manager()->get_providers_list(),
                'edit_mode' => !empty($edit_app_id),
                'edit_app_id' => $edit_app_id,
                'edit_app_data' => $edit_app_data,
                'translations' => array(
                    'confirm_delete' => __('Are you sure you want to delete this application?', 'aoauth-client-sso'),
                    'save_success' => __('Provider saved successfully', 'aoauth-client-sso'),
                    'save_error' => __('Error saving provider', 'aoauth-client-sso'),
                    'discover_success' => __('Endpoints discovered successfully', 'aoauth-client-sso'),
                    'discover_error' => __('Failed to discover endpoints', 'aoauth-client-sso'),
                    'copied' => __('Copied to clipboard', 'aoauth-client-sso'),
                    'application_name_required' => __('Please enter an application name', 'aoauth-client-sso'),
                    'client_id_required' => __('Please enter a Client ID', 'aoauth-client-sso'),
                    'client_secret_required' => __('Please enter a Client Secret', 'aoauth-client-sso'),
                    'authorization_endpoint_required' => __('Please enter an Authorization Endpoint', 'aoauth-client-sso'),
                    'token_endpoint_required' => __('Please enter a Token Endpoint', 'aoauth-client-sso'),
                    'error_saving_application' => __('Error saving application', 'aoauth-client-sso'),
                    'connection_error_saving' => __('Connection error while saving', 'aoauth-client-sso'),
                    'connection_error' => __('Connection error: %s', 'aoauth-client-sso'),
                    'provider_saved_redirecting' => __('Provider configuration saved and enabled! Redirecting...', 'aoauth-client-sso'),
                    'draft_saved' => __('Draft saved successfully!', 'aoauth-client-sso'),
                    'copied_to_clipboard' => __('Copied to clipboard!', 'aoauth-client-sso'),
                    'discovery_url_required' => __('Please enter a discovery URL first', 'aoauth-client-sso'),
                    'mapping_reset' => __('Mapping reset to default values', 'aoauth-client-sso'),
                    'testing' => __('Testing...', 'aoauth-client-sso'),
                    'test_connection' => __('Test Connection', 'aoauth-client-sso'),
                    'back_to_providers' => __('Back to Providers', 'aoauth-client-sso'),
                    'discovering' => __('Discovering...', 'aoauth-client-sso'),
                    'auto_discover' => __('Auto Discover', 'aoauth-client-sso')
                )
            ));
        }
    }
    
    public function render_connect_page() {
        $applications = get_option('aoauth_applications', array());
        $providers = aoauth_core()->get_providers_manager()->get_providers_list();
        
        include AOAUTH_PLUGIN_DIR . 'admin/views/providers.php';
    }
    
    public function render_general_page() {
        $settings_view = 'sign-in-experience';
        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $settings['turnstile_secret_key'] = aoauth_core()->get_security()->decrypt_secret($settings['turnstile_secret_key'] ?? '');
        $settings['recaptcha_secret_key'] = aoauth_core()->get_security()->decrypt_secret($settings['recaptcha_secret_key'] ?? '');
        $roles = get_editable_roles();
        $available_themes = aoauth_core()->get_available_themes();
        
        include AOAUTH_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function render_user_management_page() {
        $settings_view = 'user-management';
        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $roles = get_editable_roles();
        $available_themes = aoauth_core()->get_available_themes();

        include AOAUTH_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function render_security_page() {
        $settings_view = 'security';
        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $settings['turnstile_secret_key'] = aoauth_core()->get_security()->decrypt_secret($settings['turnstile_secret_key'] ?? '');
        $settings['recaptcha_secret_key'] = aoauth_core()->get_security()->decrypt_secret($settings['recaptcha_secret_key'] ?? '');
        $roles = get_editable_roles();
        $available_themes = aoauth_core()->get_available_themes();

        include AOAUTH_PLUGIN_DIR . 'admin/views/settings.php';
    }

    public function render_tools_page() {
        $settings_view = 'tools';
        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $roles = get_editable_roles();
        $available_themes = aoauth_core()->get_available_themes();
        $sso_users = get_users(array(
            'meta_key' => '_aoauth_provider',
            'fields' => 'ID'
        ));

        include AOAUTH_PLUGIN_DIR . 'admin/views/settings.php';
    }
    
    public function render_logs_page() {
        $filters = $this->sanitize_log_filters($_GET);
        $logs = $this->logger->get_logs(array_merge($filters, array('limit' => 50, 'offset' => 0)));
        $total_logs = $this->logger->get_log_count($filters);
        $settings = get_option('aoauth_settings', array());
        
        include AOAUTH_PLUGIN_DIR . 'admin/views/logs.php';
    }
    
    public function render_wizard_page() {
        $applications = get_option('aoauth_applications', array());
        $providers = aoauth_core()->get_providers_manager()->get_providers_list();
        
        include AOAUTH_PLUGIN_DIR . 'admin/views/wizard.php';
    }
    
    /**
     * Render profile unlinking section on user profile page
     */
    public function render_profile_unlink_section($user) {
        if (!current_user_can('edit_user', $user->ID)) {
            return;
        }
        
        $linked_providers = AOAUTH_Core::get_user_linked_providers($user->ID);
        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $applications = get_option('aoauth_applications', array());
        $enabled_applications = array_filter($applications, function($application) {
            return !empty($application['enabled']);
        });
        $can_link_on_this_profile = (int) get_current_user_id() === (int) $user->ID;
        ?>
        <div class="aoauth-profile-unlink-section">
            <h2><?php esc_html_e('SSO Account Connection', 'aoauth-client-sso'); ?></h2>
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label><?php esc_html_e('Single Sign-On Status', 'aoauth-client-sso'); ?></label>
                    </th>
                    <td>
                        <?php if (!empty($linked_providers)): ?>
                            <div class="aoauth-connected-info">
                                <p class="description">
                                    <?php esc_html_e('This WordPress account can sign in with the connected SSO providers below.', 'aoauth-client-sso'); ?>
                                </p>
                                <div class="aoauth-profile-provider-grid">
                                    <?php foreach ($linked_providers as $provider_slug => $connection): ?>
                                        <?php
                                        $provider_data = $applications[$provider_slug] ?? array(
                                            'provider_name' => $provider_slug,
                                            'app_name' => ucfirst($provider_slug),
                                        );
                                        $provider_email = !empty($connection['email']) ? $connection['email'] : get_user_meta($user->ID, '_aoauth_provider_email_' . $provider_slug, true);
                                        ?>
                                        <div class="aoauth-profile-provider-card">
                                            <div class="aoauth-connected-badge">
                                                <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . sanitize_key($provider_data['provider_name']) . '.png'); ?>"
                                                     alt="<?php echo esc_attr($provider_data['provider_name']); ?>"
                                                     class="aoauth-provider-icon-small"
                                                     data-fallback-src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png'); ?>">
                                                <strong><?php echo esc_html($provider_data['app_name']); ?></strong>
                                                <span class="aoauth-connected-status"><?php esc_html_e('Connected', 'aoauth-client-sso'); ?></span>
                                            </div>
                                            <?php if ($provider_email): ?>
                                                <p class="description"><?php echo esc_html($provider_email); ?></p>
                                            <?php endif; ?>
                                            <button type="button"
                                                    class="aoauth-admin-button aoauth-admin-button-danger-ghost aoauth-unlink-profile-btn"
                                                    data-user-id="<?php echo esc_attr($user->ID); ?>"
                                                    data-provider="<?php echo esc_attr($provider_slug); ?>"
                                                    data-nonce="<?php echo esc_attr(wp_create_nonce('aoauth_unlink_' . $user->ID)); ?>">
                                                <span class="dashicons dashicons-unlink"></span>
                                                <?php esc_html_e('Unlink', 'aoauth-client-sso'); ?>
                                            </button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="aoauth-unlink-warning">
                                    <p class="description"><?php esc_html_e('Warning: before unlinking, make sure this user has another provider or a WordPress password available.', 'aoauth-client-sso'); ?></p>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="aoauth-no-connection">
                                <p>
                                    <span class="dashicons dashicons-admin-network"></span>
                                    <?php esc_html_e('No SSO provider linked to this account.', 'aoauth-client-sso'); ?>
                                </p>
                                <p class="description">
                                    <?php esc_html_e('To link an SSO provider, sign in using the provider on the login page.', 'aoauth-client-sso'); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($enabled_applications) && !empty($settings['allow_account_linking']) && !empty($settings['enable_self_service_account_linking'])): ?>
                            <div class="aoauth-profile-link-options">
                                <h3><?php esc_html_e('Link Another SSO Provider', 'aoauth-client-sso'); ?></h3>
                                <?php if ($can_link_on_this_profile): ?>
                                    <div class="aoauth-profile-provider-grid">
                                        <?php foreach ($enabled_applications as $app_id => $app): ?>
                                            <?php
                                            if (isset($linked_providers[$app_id])) {
                                                continue;
                                            }
                                            $provider_name = sanitize_key($app['provider_name'] ?? $app_id);
                                            $link_url = add_query_arg(array(
                                                'oauth' => 'login',
                                                'provider' => $app_id,
                                                'aoauth_link_current_user' => '1',
                                                'redirect_to' => get_edit_profile_url($user->ID),
                                                '_wpnonce' => wp_create_nonce('aoauth_login_' . $app_id),
                                                'aoauth_link_nonce' => wp_create_nonce('aoauth_link_current_user_' . $user->ID . '_' . $app_id),
                                            ), wp_login_url());
                                            ?>
                                            <a class="aoauth-profile-provider-card aoauth-profile-provider-link" href="<?php echo esc_url($link_url); ?>">
                                                <span class="aoauth-connected-badge">
                                                    <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $provider_name . '.png'); ?>"
                                                         alt="<?php echo esc_attr($provider_name); ?>"
                                                         class="aoauth-provider-icon-small"
                                                         data-fallback-src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png'); ?>">
                                                    <strong><?php echo esc_html($app['app_name'] ?? ucfirst($provider_name)); ?></strong>
                                                </span>
                                                <span class="aoauth-profile-link-action"><?php esc_html_e('Link provider', 'aoauth-client-sso'); ?></span>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                    <p class="description"><?php esc_html_e('Provider email may differ from the WordPress email. The provider account itself must not already be linked to another WordPress user.', 'aoauth-client-sso'); ?></p>
                                <?php else: ?>
                                    <p class="description"><?php esc_html_e('For security, users must link providers from their own profile while logged in as themselves.', 'aoauth-client-sso'); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Add unlink columns to users table
     */
    public function add_unlink_column($columns) {
        $columns['aoauth_sso'] = __('SSO Providers', 'aoauth-client-sso');
        $columns['aoauth_sso_actions'] = __('SSO Actions', 'aoauth-client-sso');
        return $columns;
    }
    
    /**
     * Render unlink column content
     */
    public function render_unlink_column($value, $column_name, $user_id) {
        if ($column_name === 'aoauth_sso' || $column_name === 'aoauth_sso_actions') {
            $linked_providers = AOAUTH_Core::get_user_linked_providers($user_id);
            $applications = get_option('aoauth_applications', array());
            $current_user = wp_get_current_user();
            
            if (!empty($linked_providers)) {
                $provider_items = array();
                foreach (array_keys($linked_providers) as $provider) {
                    $provider_name = $applications[$provider]['provider_name'] ?? $provider;
                    $app_name = $applications[$provider]['app_name'] ?? ucfirst($provider);
                    if ($column_name === 'aoauth_sso') {
                        $provider_items[] = '<span class="aoauth-provider-cell">
                                    <img src="' . esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . sanitize_key($provider_name) . '.png') . '"
                                         class="aoauth-provider-cell-icon"
                                         data-hide-on-error="1"
                                         alt="' . esc_attr($app_name) . '">
                                    <span>' . esc_html($app_name) . '</span>
                                </span>';
                        continue;
                    }

                    $disabled = '';
                    $title = '';
                    if ($user_id === $current_user->ID && count(get_users(array('role' => 'administrator'))) === 1) {
                        $disabled = 'disabled';
                        $title = __('Cannot unlink the only administrator account', 'aoauth-client-sso');
                    }
                    $provider_items[] = '<button type="button"
                                class="aoauth-provider-cell-action aoauth-unlink-user-btn"
                                data-user-id="' . esc_attr($user_id) . '"
                                data-provider="' . esc_attr($provider) . '"
                                data-nonce="' . esc_attr(wp_create_nonce('aoauth_unlink_' . $user_id)) . '"
                                ' . $disabled . '
                                title="' . esc_attr($title ?: sprintf(__('Unlink %s', 'aoauth-client-sso'), $app_name)) . '">
                            <span class="dashicons dashicons-unlink"></span>
                            <span>' . esc_html(sprintf(__('Unlink %s', 'aoauth-client-sso'), $app_name)) . '</span>
                        </button>';
                }
                return implode(' ', $provider_items);
            }
            return '<span class="aoauth-no-provider">—</span>';
        }
        
        return $value;
    }
    
    /**
     * Add bulk unlink action to users table
     */
    public function add_bulk_unlink_action($bulk_actions) {
        $bulk_actions['aoauth_bulk_unlink'] = __('Unlink SSO Accounts', 'aoauth-client-sso');
        return $bulk_actions;
    }
    
    /**
     * Handle bulk unlink action
     */
    public function handle_bulk_unlink_action($redirect_to, $doaction, $user_ids) {
        if ($doaction !== 'aoauth_bulk_unlink') {
            return $redirect_to;
        }
        
        if (!current_user_can('delete_users')) {
            wp_die(__('You do not have permission to perform this action.', 'aoauth-client-sso'));
        }
        
        $unlinked_count = 0;
        $failed_count = 0;
        $current_user_id = get_current_user_id();
        $admin_count = count(get_users(array('role' => 'administrator')));
        
        foreach ($user_ids as $user_id) {
            $user_id = intval($user_id);
            $linked_providers = AOAUTH_Core::get_user_linked_providers($user_id);
            
            if (empty($linked_providers)) {
                $failed_count++;
                continue;
            }
            
            // Prevent unlinking the only admin
            if ($user_id === $current_user_id && $admin_count === 1) {
                $failed_count++;
                continue;
            }
            
            foreach (array_keys($linked_providers) as $provider) {
                $result = AOAUTH_Core::unlink_user_provider($user_id, $provider);

                if ($result) {
                    $unlinked_count++;
                    $this->logger->log('account_unlinked_bulk', array(
                        'user_id' => $user_id,
                        'provider' => $provider,
                        'action_by' => $current_user_id
                    ), $user_id, $provider, 'info');
                } else {
                    $failed_count++;
                }
            }
        }
        
        $redirect_to = add_query_arg(
            array(
                'aoauth_bulk_unlinked' => $unlinked_count,
                'aoauth_bulk_failed' => $failed_count
            ),
            $redirect_to
        );
        
        return $redirect_to;
    }
    
    public function ajax_save_application() {
    $this->debug = aoauth_core()->get_debug();
    $this->debug->log_start('ajax_save_application');
    
    check_ajax_referer('aoauth_admin_nonce', 'nonce');
    
    if (!current_user_can('manage_options')) {
        $this->debug->error('Permission denied for save_application');
        wp_die(-1);
    }
    
    $app_data = isset($_POST['app_data']) ? $_POST['app_data'] : array();
    $this->debug->debug('Received app_data', array('data_keys' => array_keys($app_data)));
    
    if (!is_array($app_data)) {
        $this->debug->error('Invalid application data - not an array');
        wp_send_json_error(array('message' => __('Invalid application data', 'aoauth-client-sso')));
    }
    
    $app_data = $this->security->sanitize_provider_config($app_data);
    $this->debug->debug('After sanitization', array('provider_name' => $app_data['provider_name'] ?? 'not_set'));
    
    if (!empty($app_data['client_secret'])) {
        $app_data['client_secret'] = $this->security->encrypt($app_data['client_secret']);
        $this->debug->debug('Client secret encrypted');
    }
    
    $app_id = sanitize_key($app_data['provider_name']);
    if (empty($app_id)) {
        $this->debug->error('Provider name missing after sanitization');
        wp_send_json_error(array('message' => __('Provider name is required', 'aoauth-client-sso')));
    }
    
    $applications = get_option('aoauth_applications', array());
    if (!is_array($applications)) {
        $applications = array();
    }
    
    $applications[$app_id] = $app_data;
    
    update_option('aoauth_applications', $applications);
    $this->debug->info('Application saved', array('app_id' => $app_id, 'app_name' => $app_data['app_name']));
    
    $this->logger->log('application_saved', array(
        'provider' => $app_id,
        'app_name' => $app_data['app_name']
    ), get_current_user_id(), $app_id, 'success');
    
    $this->debug->log_end('ajax_save_application', array('success' => true));
    
    wp_send_json_success(array('message' => __('Provider saved successfully', 'aoauth-client-sso')));
}
    
    public function ajax_delete_application() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $app_id = sanitize_text_field($_POST['app_id'] ?? '');
        
        if (empty($app_id)) {
            wp_send_json_error(array('message' => __('Invalid provider ID', 'aoauth-client-sso')));
        }
        
        $applications = get_option('aoauth_applications', array());
        
        if (isset($applications[$app_id])) {
            unset($applications[$app_id]);
            update_option('aoauth_applications', $applications);
            
            $this->logger->log('application_deleted', array('provider' => $app_id), get_current_user_id(), $app_id, 'info');
            
            wp_send_json_success(array('message' => __('Provider disconnected successfully', 'aoauth-client-sso')));
        }
        
        wp_send_json_error(array('message' => __('Provider not found', 'aoauth-client-sso')));
    }
    
    public function ajax_save_settings() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $settings = $this->sanitize_settings($_POST);
        
        update_option('aoauth_settings', $settings);
        
        aoauth_core()->schedule_retention_cron();
        
        $this->logger->log('settings_updated', $settings, get_current_user_id(), null, 'info');
        
        wp_send_json_success(array('message' => __('Settings saved successfully', 'aoauth-client-sso')));
    }
    
    public function ajax_discover_endpoints() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $url = esc_url_raw($_POST['discovery_url'] ?? '');
        
        if (empty($url)) {
            wp_send_json_error(array('message' => __('Discovery URL is required', 'aoauth-client-sso')));
        }
        
        try {
            $oauth_client = new AOAUTH_OAuth_Client(array('provider_name' => 'discovery'));
            $endpoints = $oauth_client->discover_endpoints($url);
            
            wp_send_json_success(array(
                'endpoints' => $endpoints,
                'message' => __('Endpoints discovered successfully', 'aoauth-client-sso')
            ));
        } catch (Exception $e) {
            wp_send_json_error(array('message' => $e->getMessage()));
        }
    }
    
    public function ajax_toggle_provider() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $app_id = sanitize_text_field($_POST['app_id'] ?? '');
        $enabled = !empty($_POST['enabled']) ? 1 : 0;
        
        $applications = get_option('aoauth_applications', array());
        
        if (isset($applications[$app_id])) {
            $applications[$app_id]['enabled'] = $enabled;
            update_option('aoauth_applications', $applications);
            
            $this->logger->log(
                $enabled ? 'provider_enabled' : 'provider_disabled',
                array('provider' => $app_id),
                get_current_user_id(),
                $app_id,
                'info'
            );
            
            $provider_label = !empty($applications[$app_id]['provider_name'])
                ? $applications[$app_id]['provider_name']
                : $app_id;
            $provider_label = ucwords(str_replace(array('-', '_'), ' ', $provider_label));
            $status_label = $enabled ? __('enabled', 'aoauth-client-sso') : __('disabled', 'aoauth-client-sso');

            wp_send_json_success(array(
                'message' => sprintf(
                    /* translators: 1: Provider name, 2: enabled/disabled status. */
                    __('%1$s %2$s', 'aoauth-client-sso'),
                    $provider_label,
                    $status_label
                )
            ));
        }
        
        wp_send_json_error(array('message' => __('Provider not found', 'aoauth-client-sso')));
    }
    
    public function ajax_get_logs() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $page = max(1, intval($_POST['page'] ?? 1));
        $limit = max(1, min(100, intval($_POST['limit'] ?? 50)));
        $offset = ($page - 1) * $limit;
        $filters = $this->sanitize_log_filters($_POST);
        
        $logs = $this->logger->get_logs(array_merge($filters, array('limit' => $limit, 'offset' => $offset)));
        $total = $this->logger->get_log_count($filters);
        
        wp_send_json_success(array(
            'logs' => $logs,
            'total' => $total,
            'pages' => ceil($total / $limit),
            'current_page' => $page
        ));
    }

    private function sanitize_log_filters($source) {
        $source = is_array($source) ? wp_unslash($source) : array();
        $filters = array(
            'event_type' => isset($source['event_type']) ? sanitize_key($source['event_type']) : '',
            'provider' => isset($source['provider']) ? sanitize_key($source['provider']) : '',
            'status' => isset($source['status']) ? sanitize_key($source['status']) : '',
            'date_from' => isset($source['date_from']) ? sanitize_text_field($source['date_from']) : '',
            'date_to' => isset($source['date_to']) ? sanitize_text_field($source['date_to']) : '',
            'orderby' => isset($source['orderby']) ? sanitize_key($source['orderby']) : 'created_at',
            'order' => isset($source['order']) ? strtoupper(sanitize_key($source['order'])) : 'DESC',
        );

        if (!empty($source['user_id'])) {
            $filters['user_id'] = absint($source['user_id']);
        }

        if (!in_array($filters['status'], array('', 'info', 'success', 'warning', 'error'), true)) {
            $filters['status'] = '';
        }

        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $filters['date_from'])) {
            $filters['date_from'] = '';
        }

        if (!preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $filters['date_to'])) {
            $filters['date_to'] = '';
        }

        return $filters;
    }
    
    public function ajax_clear_logs() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $this->logger->clear_logs();
        
        wp_send_json_success(array('message' => __('Logs cleared successfully', 'aoauth-client-sso')));
    }
    
    public function ajax_export_logs() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $csv_data = $this->logger->export_logs();
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="aoauth-logs-' . date('Y-m-d-H-i-s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        foreach ($csv_data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
        
        exit;
    }
    
    
    public function ajax_export_config() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $backup_password = isset($_POST['backup_password']) ? (string) wp_unslash($_POST['backup_password']) : '';
        $include_encrypted_credentials = strlen($backup_password) > 0;
        
        $config = array(
            'settings' => get_option('aoauth_settings', array()),
            'applications' => get_option('aoauth_applications', array()),
            'version' => AOAUTH_VERSION,
            'export_date' => current_time('mysql'),
            'credentials' => $include_encrypted_credentials ? 'password_encrypted' : 'excluded'
        );
        
        if ($include_encrypted_credentials) {
            $config['settings']['turnstile_secret_key'] = $this->encrypt_backup_value(
                aoauth_core()->get_security()->decrypt_secret($config['settings']['turnstile_secret_key'] ?? ''),
                $backup_password
            );
            $config['settings']['recaptcha_secret_key'] = $this->encrypt_backup_value(
                aoauth_core()->get_security()->decrypt_secret($config['settings']['recaptcha_secret_key'] ?? ''),
                $backup_password
            );
            $config['settings']['_bot_secret_note'] = 'Turnstile and reCAPTCHA secret keys are password-encrypted. Use the same backup password during import.';
        } else {
            unset($config['settings']['turnstile_secret_key']);
            unset($config['settings']['recaptcha_secret_key']);
            $config['settings']['_bot_secret_note'] = 'Turnstile and reCAPTCHA secret keys were removed for security. Please re-enter them after import, or export again with a backup password.';
        }
        
        if (!empty($config['applications'])) {
            foreach ($config['applications'] as &$app) {
                if ($include_encrypted_credentials) {
                    $client_id = isset($app['client_id']) ? (string) $app['client_id'] : '';
                    $client_secret = isset($app['client_secret']) ? aoauth_core()->get_security()->decrypt($app['client_secret']) : '';
                    $app['client_id'] = $this->encrypt_backup_value($client_id, $backup_password);
                    $app['client_secret'] = $this->encrypt_backup_value($client_secret, $backup_password);
                    $app['_note'] = 'Client ID and Secret are password-encrypted. Use the same backup password during import.';
                } else {
                    unset($app['client_id']);
                    unset($app['client_secret']);
                    $app['_note'] = 'Client ID and Secret were removed for security. Please re-enter them after import, or export again with a backup password.';
                }
            }
        }
        
        $json = json_encode($config, JSON_PRETTY_PRINT);
        header('Content-Type: application/json');
        header('Content-Disposition: attachment; filename="aoauth-config-' . date('Y-m-d') . '.json"');
        echo $json;
        exit;
    }

    private function encrypt_backup_value($value, $password) {
        if ($value === '') {
            return '';
        }

        $salt = random_bytes(16);
        $iv = random_bytes(12);
        $key = hash_pbkdf2('sha256', $password, $salt, 200000, 32, true);
        $tag = '';
        $ciphertext = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return array(
            'aoauth_backup_encrypted' => 1,
            'cipher' => 'aes-256-gcm',
            'kdf' => 'pbkdf2-sha256',
            'iterations' => 200000,
            'salt' => base64_encode($salt),
            'iv' => base64_encode($iv),
            'tag' => base64_encode($tag),
            'value' => base64_encode($ciphertext),
        );
    }

    private function decrypt_backup_value($payload, $password) {
        if (empty($payload)) {
            return '';
        }

        if (!is_array($payload) || empty($payload['aoauth_backup_encrypted'])) {
            return is_string($payload) ? $payload : '';
        }

        if ($password === '') {
            return new WP_Error('missing_backup_password', __('This backup contains encrypted credentials. Please enter the backup password.', 'aoauth-client-sso'));
        }

        $salt = base64_decode($payload['salt'] ?? '', true);
        $iv = base64_decode($payload['iv'] ?? '', true);
        $tag = base64_decode($payload['tag'] ?? '', true);
        $ciphertext = base64_decode($payload['value'] ?? '', true);
        $iterations = max(100000, intval($payload['iterations'] ?? 200000));

        if (!$salt || !$iv || !$tag || !$ciphertext) {
            return new WP_Error('invalid_backup_payload', __('Encrypted credential payload is invalid.', 'aoauth-client-sso'));
        }

        $key = hash_pbkdf2('sha256', $password, $salt, $iterations, 32, true);
        $decrypted = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if ($decrypted === false) {
            return new WP_Error('invalid_backup_password', __('Could not decrypt credentials. Check the backup password.', 'aoauth-client-sso'));
        }

        return $decrypted;
    }
    
    public function ajax_import_config() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        if (empty($_FILES['config_file']) || $_FILES['config_file']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(array('message' => __('No file uploaded or upload error.', 'aoauth-client-sso')));
        }
        
        $file_content = file_get_contents($_FILES['config_file']['tmp_name']);
        $config = json_decode($file_content, true);
        $backup_password = isset($_POST['backup_password']) ? (string) wp_unslash($_POST['backup_password']) : '';
        
        if (!$config || !isset($config['settings']) || !isset($config['applications'])) {
            wp_send_json_error(array('message' => __('Invalid configuration file.', 'aoauth-client-sso')));
        }
        
        $settings = is_array($config['settings']) ? $config['settings'] : array();
        $encrypted_credentials_restored = false;
        $secrets_excluded = isset($config['credentials']) && $config['credentials'] === 'excluded';

        foreach (array('turnstile_secret_key', 'recaptcha_secret_key') as $secret_key) {
            if (isset($settings[$secret_key])) {
                $decrypted_secret = $this->decrypt_backup_value($settings[$secret_key], $backup_password);
                if (is_wp_error($decrypted_secret)) {
                    wp_send_json_error(array('message' => $decrypted_secret->get_error_message()));
                }
                if (is_array($settings[$secret_key]) && !empty($settings[$secret_key]['aoauth_backup_encrypted'])) {
                    $encrypted_credentials_restored = true;
                }
                $settings[$secret_key] = $decrypted_secret;
            }
        }
        unset($settings['_bot_secret_note']);

        $sanitized_settings = $this->sanitize_settings($settings);
        $sanitized_applications = array();
        
        if (!empty($config['applications']) && is_array($config['applications'])) {
            foreach ($config['applications'] as $app) {
                $app_had_encrypted_credentials = false;
                if (isset($app['client_id'])) {
                    $app_had_encrypted_credentials = is_array($app['client_id']) && !empty($app['client_id']['aoauth_backup_encrypted']);
                    $client_id = $this->decrypt_backup_value($app['client_id'], $backup_password);
                    if (is_wp_error($client_id)) {
                        wp_send_json_error(array('message' => $client_id->get_error_message()));
                    }
                    $app['client_id'] = $client_id;
                }

                if (isset($app['client_secret'])) {
                    $app_had_encrypted_credentials = $app_had_encrypted_credentials || (is_array($app['client_secret']) && !empty($app['client_secret']['aoauth_backup_encrypted']));
                    $client_secret = $this->decrypt_backup_value($app['client_secret'], $backup_password);
                    if (is_wp_error($client_secret)) {
                        wp_send_json_error(array('message' => $client_secret->get_error_message()));
                    }
                    $app['client_secret'] = $client_secret;
                }

                unset($app['_note']);
                if ($app_had_encrypted_credentials) {
                    $encrypted_credentials_restored = true;
                }

                $has_required_credentials = !empty($app['client_id']) && !empty($app['client_secret']);
                $app['enabled'] = !empty($app['enabled']) && $has_required_credentials ? 1 : 0;
                $sanitized_app = $this->security->sanitize_provider_config($app);
                if (!empty($sanitized_app['client_secret'])) {
                    $sanitized_app['client_secret'] = $this->security->encrypt($sanitized_app['client_secret']);
                }
                $app_id = sanitize_key($sanitized_app['provider_name']);
                if (!empty($app_id)) {
                    $sanitized_applications[$app_id] = $sanitized_app;
                }
            }
        }
        
        update_option('aoauth_settings', $sanitized_settings);
        update_option('aoauth_applications', $sanitized_applications);
        
        $this->logger->log('config_imported', array('user' => get_current_user_id()), get_current_user_id(), null, 'info');
        if ($encrypted_credentials_restored) {
            $message = __('Configuration imported successfully. Encrypted provider and bot protection secrets were restored.', 'aoauth-client-sso');
        } elseif ($secrets_excluded) {
            $message = __('Configuration imported successfully. Secrets were excluded from this backup, so providers without Client IDs and Secrets remain disabled.', 'aoauth-client-sso');
        } else {
            $message = __('Configuration imported successfully. Providers without Client IDs and Secrets remain disabled.', 'aoauth-client-sso');
        }
        wp_send_json_success(array('message' => $message));
    }

    public function ajax_client_debug_log() {
        $debug = aoauth_core()->get_debug();
        if (!$debug->is_enabled()) {
            wp_send_json_success(array('logged' => false));
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        $valid_nonce = wp_verify_nonce($nonce, 'aoauth_admin_nonce') || wp_verify_nonce($nonce, 'aoauth_public_nonce');
        if (!$valid_nonce) {
            wp_send_json_error(array('message' => __('Security check failed.', 'aoauth-client-sso')));
        }

        if (is_user_logged_in() && !current_user_can('manage_options') && is_admin()) {
            wp_send_json_error(array('message' => __('Permission denied.', 'aoauth-client-sso')));
        }

        $level = isset($_POST['level']) ? sanitize_key(wp_unslash($_POST['level'])) : 'debug';
        if (!in_array($level, array('debug', 'info', 'warning', 'error'), true)) {
            $level = 'debug';
        }

        $message = isset($_POST['message']) ? sanitize_text_field(wp_unslash($_POST['message'])) : 'Client event';
        $context = array();
        if (isset($_POST['context'])) {
            $raw_context = wp_unslash($_POST['context']);
            if (is_string($raw_context)) {
                $decoded_context = json_decode($raw_context, true);
                if (is_array($decoded_context)) {
                    $context = $decoded_context;
                }
            } elseif (is_array($raw_context)) {
                $context = $raw_context;
            }
        }

        $context['client_debug_source'] = isset($_POST['source']) ? sanitize_key(wp_unslash($_POST['source'])) : 'browser';
        $context['user_agent'] = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

        $debug->log($level, 'Browser: ' . $message, $context);
        wp_send_json_success(array('logged' => true));
    }
    
    private function sanitize_settings($settings) {
        $raw_settings = is_array($settings) ? $settings : array();
        $defaults = AOAUTH_Core::get_default_settings();
        $existing = array_merge($defaults, get_option('aoauth_settings', array()));
        $settings = array_merge($existing, $raw_settings);
        $security = aoauth_core()->get_security();
        
        $is_enabled = function($key) use ($raw_settings, $existing) {
            if (array_key_exists($key, $raw_settings)) {
                return !empty($raw_settings[$key]) ? 1 : 0;
            }

            return !empty($existing[$key]) ? 1 : 0;
        };
        
        $enable_turnstile = $is_enabled('enable_turnstile');
        $enable_recaptcha = $is_enabled('enable_recaptcha');
        if ($enable_turnstile && $enable_recaptcha) {
            $enable_recaptcha = 0;
        }
        
        $role = sanitize_text_field($settings['default_role']);
        if (!wp_roles()->is_role($role)) {
            $role = $defaults['default_role'];
        }
        
        $allow_account_linking = $is_enabled('allow_account_linking');
        
        $bot_protection_provider = sanitize_key($settings['bot_protection_provider'] ?? 'turnstile');
        if (!in_array($bot_protection_provider, array('turnstile', 'recaptcha'), true)) {
            $bot_protection_provider = 'turnstile';
        }

        $role_redirects = array();
        $posted_redirects = isset($settings['role_redirects']) && is_array($settings['role_redirects']) ? $settings['role_redirects'] : array();
        foreach (get_editable_roles() as $role_key => $role_data) {
            $fallback_redirect = AOAUTH_Core::get_default_role_redirects()[$role_key] ?? '/';
            $redirect_value = isset($posted_redirects[$role_key]) ? trim((string) $posted_redirects[$role_key]) : $fallback_redirect;
            $role_redirects[$role_key] = $this->sanitize_role_redirect_path($redirect_value, $fallback_redirect);
        }

        $login_button_layout = sanitize_key($settings['login_button_layout'] ?? 'vertical');
        if ($login_button_layout === 'horizontal') {
            $login_button_layout = 'wrap-centered';
        } elseif ($login_button_layout === 'grid') {
            $login_button_layout = 'two-column';
        }
        if (!in_array($login_button_layout, array('vertical', 'wrap-centered', 'two-column', 'compact'), true)) {
            $login_button_layout = $defaults['login_button_layout'];
        }

        $login_button_position = sanitize_key($settings['login_button_position'] ?? 'below_form');
        if (!in_array($login_button_position, array('below_form', 'inside_form'), true)) {
            $login_button_position = $defaults['login_button_position'];
        }

        $overlay_variant = sanitize_key($settings['bot_overlay_variant'] ?? 'spotlight');
        if (!in_array($overlay_variant, array('spotlight', 'minimal', 'constellation'), true)) {
            $overlay_variant = 'spotlight';
        }

        $turnstile_display_mode = sanitize_key($settings['turnstile_display_mode'] ?? 'invisible');
        if (!in_array($turnstile_display_mode, array('invisible', 'managed', 'non-interactive'), true)) {
            $turnstile_display_mode = 'invisible';
        }

        $turnstile_secret_key = array_key_exists('turnstile_secret_key', $raw_settings)
            ? $security->encrypt_secret(sanitize_text_field($settings['turnstile_secret_key']))
            : ($existing['turnstile_secret_key'] ?? '');
        $recaptcha_secret_key = array_key_exists('recaptcha_secret_key', $raw_settings)
            ? $security->encrypt_secret(sanitize_text_field($settings['recaptcha_secret_key']))
            : ($existing['recaptcha_secret_key'] ?? '');

        $sanitized = array(
            'enable_login_buttons' => $is_enabled('enable_login_buttons'),
            'enable_brand_badge' => $is_enabled('enable_brand_badge'),
            'login_button_theme' => sanitize_text_field($settings['login_button_theme']),
            'login_button_layout' => $login_button_layout,
            'login_button_position' => $login_button_position,
            'auto_create_users' => $is_enabled('auto_create_users'),
            'default_role' => $role,
            'allow_account_linking' => $allow_account_linking,
            'enable_self_service_account_linking' => $is_enabled('enable_self_service_account_linking'),
            'delete_data_on_uninstall' => $is_enabled('delete_data_on_uninstall'),
            'security_level' => sanitize_text_field($settings['security_level']),
            'rate_limit_attempts' => max(1, min(100, intval($settings['rate_limit_attempts']))),
            'rate_limit_window' => max(60, min(DAY_IN_SECONDS, intval($settings['rate_limit_window']))),
            'enable_logs' => $is_enabled('enable_logs'),
            'logs_retention_period' => sanitize_text_field($settings['logs_retention_period']),
            'enable_bot_protection' => $is_enabled('enable_bot_protection'),
            'bot_protection_provider' => $bot_protection_provider,
            'enable_turnstile' => $is_enabled('enable_bot_protection') && $bot_protection_provider === 'turnstile' ? 1 : 0,
            'turnstile_site_key' => sanitize_text_field($settings['turnstile_site_key']),
            'turnstile_secret_key' => $turnstile_secret_key,
            'turnstile_display_mode' => $turnstile_display_mode,
            'enable_recaptcha' => $is_enabled('enable_bot_protection') && $bot_protection_provider === 'recaptcha' ? 1 : 0,
            'recaptcha_site_key' => sanitize_text_field($settings['recaptcha_site_key']),
            'recaptcha_secret_key' => $recaptcha_secret_key,
            'recaptcha_score_threshold' => max(0, min(1, floatval($settings['recaptcha_score_threshold']))),
            'linking_max_attempts' => $allow_account_linking ? max(1, min(20, intval($settings['linking_max_attempts']))) : intval($defaults['linking_max_attempts']),
            'linking_lockout_minutes' => $allow_account_linking ? max(1, min(1440, intval($settings['linking_lockout_minutes']))) : intval($defaults['linking_lockout_minutes']),
            'linking_login_ban_minutes' => $allow_account_linking ? max(0, min(1440, intval($settings['linking_login_ban_minutes']))) : intval($defaults['linking_login_ban_minutes']),
            'linking_page_use_theme' => 1,
            'linking_page_title' => sanitize_text_field($settings['linking_page_title']),
            'bot_overlay_enabled' => $is_enabled('bot_overlay_enabled'),
            'bot_overlay_message' => sanitize_text_field($settings['bot_overlay_message']),
            'bot_overlay_variant' => $overlay_variant,
            'bot_overlay_opacity' => max(35, min(96, intval($settings['bot_overlay_opacity'] ?? $defaults['bot_overlay_opacity']))),
            'bot_overlay_branding_enabled' => $is_enabled('bot_overlay_branding_enabled'),
            'role_redirects' => $role_redirects,
        );
        
        return $sanitized;
    }

    private function sanitize_role_redirect_path($redirect_value, $fallback) {
        if ($redirect_value === '') {
            return $fallback;
        }

        if (strpos($redirect_value, '/') === 0) {
            return '/' . ltrim(sanitize_text_field($redirect_value), '/');
        }

        $url = esc_url_raw($redirect_value);
        if (!empty($url) && aoauth_core()->get_security()->validate_redirect_url($url)) {
            return $url;
        }

        return $fallback;
    }
    
    public function ajax_test_connection() {
        $debug = aoauth_core()->get_debug();
        $debug->log_start('ajax_test_connection');

        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            $debug->error('Permission denied');
            wp_send_json_error(array('message' => __('Permission denied', 'aoauth-client-sso')));
        }

        $app_data = isset($_POST['app_data']) ? $_POST['app_data'] : array();
        if (!is_array($app_data)) {
            $debug->error('Invalid application data - not an array');
            wp_send_json_error(array('message' => __('Invalid application data', 'aoauth-client-sso')));
        }

        $config = $this->security->sanitize_provider_config($app_data);
        $errors = $this->validate_test_connection_config($config);
        $warnings = array();
        $test_results = array();

        $debug->info('Testing provider configuration', array(
            'provider_type' => strtolower($config['provider_name']),
            'app_name' => $config['app_name'],
            'client_id_preview' => substr($config['client_id'], 0, 8) . '****',
            'authorization_endpoint' => $config['authorization_endpoint'],
            'token_endpoint' => $config['token_endpoint'],
            'userinfo_endpoint' => $config['userinfo_endpoint'] ?: '(not set)',
            'scopes' => implode(' ', $config['scopes'])
        ));

        if (!empty($errors)) {
            $debug->log_end('ajax_test_connection', array('success' => false, 'errors' => $errors));
            wp_send_json_error(array('message' => implode(' ', $errors)));
        }

        $auth_result = $this->probe_authorization_endpoint($config);
        if (!empty($auth_result['error'])) {
            $errors[] = $auth_result['error'];
        } elseif (!empty($auth_result['warning'])) {
            $warnings[] = $auth_result['warning'];
        }
        $test_results['authorization_endpoint'] = $auth_result;

        $token_result = $this->probe_token_endpoint($config);
        if (!empty($token_result['error'])) {
            $errors[] = $token_result['error'];
        } elseif (!empty($token_result['warning'])) {
            $warnings[] = $token_result['warning'];
        }
        $test_results['token_endpoint'] = $token_result;

        if (!empty($config['userinfo_endpoint'])) {
            $userinfo_result = $this->probe_protected_get_endpoint($config['userinfo_endpoint'], 'UserInfo endpoint');
            if (!empty($userinfo_result['error'])) {
                $errors[] = $userinfo_result['error'];
            } elseif (!empty($userinfo_result['warning'])) {
                $warnings[] = $userinfo_result['warning'];
            }
            $test_results['userinfo_endpoint'] = $userinfo_result;
        }

        if (!empty($config['jwks_uri'])) {
            $jwks_result = $this->probe_jwks_endpoint($config['jwks_uri']);
            if (!empty($jwks_result['error'])) {
                $errors[] = $jwks_result['error'];
            } elseif (!empty($jwks_result['warning'])) {
                $warnings[] = $jwks_result['warning'];
            }
            $test_results['jwks_uri'] = $jwks_result;
        }

        $client_secret_note = __('Client secret validation requires a real OAuth callback and cannot be proven by a safe endpoint probe.', 'aoauth-client-sso');

        if (!empty($errors)) {
            $message = implode(' ', $errors) . ' ' . $client_secret_note;

            $this->logger->log('test_connection_failed', array(
                'provider' => $config['provider_name'],
                'errors' => $errors,
                'warnings' => $warnings,
                'test_results' => $test_results
            ), get_current_user_id(), $config['provider_name'], 'error');

            $debug->log_end('ajax_test_connection', array('success' => false, 'errors' => $errors));
            wp_send_json_error(array(
                'message' => $message,
                'warnings' => $warnings,
                'test_results' => $test_results
            ));
        }

        $message = __('Connection checks passed. Required endpoints responded and the authorization request shape is valid.', 'aoauth-client-sso') . ' ' . $client_secret_note;
        if (!empty($warnings)) {
            $message .= ' ' . sprintf(__('Warnings: %s', 'aoauth-client-sso'), implode(' ', $warnings));
        }

        $this->logger->log('test_connection_success', array(
            'provider' => $config['provider_name'],
            'warnings' => $warnings,
            'test_results' => $test_results
        ), get_current_user_id(), $config['provider_name'], 'success');

        $debug->log_end('ajax_test_connection', array('success' => true, 'warnings' => $warnings));
        wp_send_json_success(array(
            'message' => $message,
            'warnings' => $warnings,
            'test_results' => $test_results
        ));
    }

    private function validate_test_connection_config($config) {
        $errors = array();

        if (empty($config['client_id'])) {
            $errors[] = __('Client ID is required.', 'aoauth-client-sso');
        }

        if (empty($config['client_secret'])) {
            $errors[] = __('Client Secret is required.', 'aoauth-client-sso');
        }

        if (empty($config['authorization_endpoint'])) {
            $errors[] = __('Authorization Endpoint is required.', 'aoauth-client-sso');
        } elseif (!$this->is_valid_http_url($config['authorization_endpoint'])) {
            $errors[] = __('Authorization Endpoint must be a valid HTTP or HTTPS URL.', 'aoauth-client-sso');
        }

        if (empty($config['token_endpoint'])) {
            $errors[] = __('Token Endpoint is required.', 'aoauth-client-sso');
        } elseif (!$this->is_valid_http_url($config['token_endpoint'])) {
            $errors[] = __('Token Endpoint must be a valid HTTP or HTTPS URL.', 'aoauth-client-sso');
        }

        if (empty($config['redirect_uri'])) {
            $errors[] = __('Redirect URI is required.', 'aoauth-client-sso');
        } elseif (!$this->is_valid_http_url($config['redirect_uri'])) {
            $errors[] = __('Redirect URI must be a valid HTTP or HTTPS URL.', 'aoauth-client-sso');
        }

        foreach (array('userinfo_endpoint', 'jwks_uri') as $optional_url_key) {
            if (!empty($config[$optional_url_key]) && !$this->is_valid_http_url($config[$optional_url_key])) {
                $errors[] = sprintf(__('%s must be a valid HTTP or HTTPS URL.', 'aoauth-client-sso'), $optional_url_key);
            }
        }

        return $errors;
    }

    private function is_valid_http_url($url) {
        $parsed = wp_parse_url($url);
        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return false;
        }

        return in_array(strtolower($parsed['scheme']), array('http', 'https'), true);
    }

    private function probe_authorization_endpoint($config) {
        $auth_url = add_query_arg(array(
            'response_type' => 'code',
            'client_id' => $config['client_id'],
            'redirect_uri' => $config['redirect_uri'],
            'scope' => implode(' ', $config['scopes']),
            'state' => 'aoauth-test-state',
            'nonce' => 'aoauth-test-nonce'
        ), $config['authorization_endpoint']);

        $response = wp_remote_get($auth_url, array(
            'timeout' => 15,
            'sslverify' => true,
            'redirection' => 0,
            'headers' => array('Accept' => 'text/html,application/xhtml+xml,application/json')
        ));

        if (is_wp_error($response)) {
            return array('error' => sprintf(__('Authorization endpoint is unreachable: %s.', 'aoauth-client-sso'), $response->get_error_message()));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $location = wp_remote_retrieve_header($response, 'location');
        $oauth_error = $this->extract_oauth_error_from_url($location);

        if ($status_code === 404) {
            return array('status_code' => $status_code, 'error' => __('Authorization endpoint returned 404. Verify the URL.', 'aoauth-client-sso'));
        }

        if ($status_code >= 500) {
            return array('status_code' => $status_code, 'error' => sprintf(__('Authorization endpoint returned HTTP %d.', 'aoauth-client-sso'), $status_code));
        }

        if ($oauth_error) {
            return array(
                'status_code' => $status_code,
                'error' => sprintf(__('Authorization endpoint rejected the request: %s.', 'aoauth-client-sso'), $oauth_error)
            );
        }

        return array(
            'status_code' => $status_code,
            'reachable' => true
        );
    }

    private function probe_token_endpoint($config) {
        $response = wp_remote_post($config['token_endpoint'], array(
            'timeout' => 15,
            'sslverify' => true,
            'headers' => array(
                'Accept' => 'application/json',
                'Content-Type' => 'application/x-www-form-urlencoded'
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => 'aoauth-endpoint-probe',
                'client_id' => $config['client_id'],
                'redirect_uri' => $config['redirect_uri']
            )
        ));

        if (is_wp_error($response)) {
            return array('error' => sprintf(__('Token endpoint is unreachable: %s.', 'aoauth-client-sso'), $response->get_error_message()));
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code === 404) {
            return array('status_code' => $status_code, 'error' => __('Token endpoint returned 404. Verify the URL.', 'aoauth-client-sso'));
        }

        if ($status_code === 405) {
            return array('status_code' => $status_code, 'error' => __('Token endpoint rejected POST requests. OAuth token endpoints must accept POST.', 'aoauth-client-sso'));
        }

        if ($status_code >= 500) {
            return array('status_code' => $status_code, 'error' => sprintf(__('Token endpoint returned HTTP %d.', 'aoauth-client-sso'), $status_code));
        }

        if (is_array($data) && !empty($data['error']) && in_array($data['error'], array('invalid_client', 'unauthorized_client'), true)) {
            return array(
                'status_code' => $status_code,
                'warning' => sprintf(__('Token endpoint responded, but the client was not accepted during the probe: %s.', 'aoauth-client-sso'), $data['error'])
            );
        }

        return array(
            'status_code' => $status_code,
            'reachable' => true
        );
    }

    private function probe_protected_get_endpoint($url, $label) {
        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'sslverify' => true,
            'redirection' => 0,
            'headers' => array('Accept' => 'application/json')
        ));

        if (is_wp_error($response)) {
            return array('warning' => sprintf(__('%1$s could not be reached: %2$s.', 'aoauth-client-sso'), $label, $response->get_error_message()));
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code === 404) {
            return array('status_code' => $status_code, 'error' => sprintf(__('%s returned 404. Verify the URL.', 'aoauth-client-sso'), $label));
        }

        if ($status_code >= 500) {
            return array('status_code' => $status_code, 'warning' => sprintf(__('%1$s returned HTTP %2$d.', 'aoauth-client-sso'), $label, $status_code));
        }

        return array(
            'status_code' => $status_code,
            'reachable' => true
        );
    }

    private function probe_jwks_endpoint($url) {
        $result = $this->probe_protected_get_endpoint($url, 'JWKS endpoint');
        if (!empty($result['error']) || !empty($result['warning'])) {
            return $result;
        }

        $response = wp_remote_get($url, array(
            'timeout' => 15,
            'sslverify' => true,
            'headers' => array('Accept' => 'application/json')
        ));

        if (is_wp_error($response)) {
            return array('warning' => sprintf(__('JWKS endpoint could not be reached: %s.', 'aoauth-client-sso'), $response->get_error_message()));
        }

        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($data) || empty($data['keys']) || !is_array($data['keys'])) {
            return array('warning' => __('JWKS endpoint responded but did not include a keys array.', 'aoauth-client-sso'));
        }

        return array(
            'status_code' => wp_remote_retrieve_response_code($response),
            'reachable' => true,
            'keys_found' => count($data['keys'])
        );
    }

    private function extract_oauth_error_from_url($url) {
        if (empty($url)) {
            return '';
        }

        $parts = wp_parse_url($url);
        $query = array();

        if (!empty($parts['query'])) {
            wp_parse_str($parts['query'], $query);
        }

        if (!empty($parts['fragment'])) {
            $fragment = array();
            wp_parse_str($parts['fragment'], $fragment);
            $query = array_merge($query, $fragment);
        }

        if (empty($query['error'])) {
            return '';
        }

        $description = !empty($query['error_description']) ? $query['error_description'] : $query['error'];
        return sanitize_text_field($description);
    }
    
    public function ajax_preview_theme() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        $theme = sanitize_text_field($_GET['theme'] ?? 'modern');
        
        header('Content-Type: text/css');
        
        $theme_file = AOAUTH_PLUGIN_DIR . 'public/css/themes/' . $theme . '.css';
        
        if (file_exists($theme_file)) {
            $css_content = file_get_contents($theme_file);
            $preview_class = '.aoauth-preview-' . $theme;
            $css_content = str_replace('.aoauth-login-buttons .aoauth-button', $preview_class, $css_content);
            echo $css_content;
        } else {
            echo '.aoauth-preview-modern { 
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                border-radius: 8px; 
                color: #fff; 
                transition: all 0.3s ease;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 10px 16px;
            }';
        }
        
        exit;
    }
    
    public function ajax_factory_reset() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }
        
        $default_settings = AOAUTH_Core::get_default_settings();
        
        update_option('aoauth_settings', $default_settings);
        
        delete_option('aoauth_applications');
        
        $this->logger->clear_logs();
        
        $this->logger->log('factory_reset', array(
            'user_id' => get_current_user_id(),
            'user_login' => wp_get_current_user()->user_login
        ), get_current_user_id(), null, 'info');
        
        wp_send_json_success(array('message' => __('Factory reset completed. All settings restored to defaults and all providers removed.', 'aoauth-client-sso')));
    }

    public function ajax_clear_bot_verifications() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $deleted = $this->delete_transients_by_prefixes(array('aoauth_bot_'), false);
        $this->logger->log('bot_verifications_cleared', array('deleted' => $deleted), get_current_user_id(), null, 'info');
        wp_send_json_success(array('message' => sprintf(__('Cleared %d bot verification record(s).', 'aoauth-client-sso'), $deleted)));
    }

    public function ajax_run_log_cleanup() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        aoauth_core()->run_retention_cron();
        $this->logger->log('log_cleanup_run_manually', array(
            'retention_period' => get_option('aoauth_settings', array())['logs_retention_period'] ?? '30_days'
        ), get_current_user_id(), null, 'info');

        wp_send_json_success(array('message' => __('Log cleanup completed.', 'aoauth-client-sso')));
    }

    public function ajax_reschedule_log_cleanup() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        wp_clear_scheduled_hook('aoauth_retention_cron');
        aoauth_core()->schedule_retention_cron();
        $next_run = wp_next_scheduled('aoauth_retention_cron');

        wp_send_json_success(array(
            'message' => $next_run ? __('Log cleanup schedule refreshed.', 'aoauth-client-sso') : __('Log cleanup is not scheduled because activity logs are disabled.', 'aoauth-client-sso')
        ));
    }

    public function ajax_clear_linking_lockouts() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $deleted = $this->delete_transients_by_prefixes(array('aoauth_linking_lock_', 'aoauth_linking_attempts_'), false);
        $this->logger->log('linking_lockouts_cleared', array('deleted' => $deleted), get_current_user_id(), null, 'info');
        wp_send_json_success(array('message' => sprintf(__('Cleared %d account-linking lockout record(s).', 'aoauth-client-sso'), $deleted)));
    }

    public function ajax_clear_oauth_temp_data() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $deleted = $this->delete_transients_by_prefixes(array('aoauth_state_', 'aoauth_test_state_', 'aoauth_link_'), true);
        $this->logger->log('oauth_temp_data_cleared', array('deleted' => $deleted, 'expired_only' => true), get_current_user_id(), null, 'info');
        wp_send_json_success(array('message' => sprintf(__('Cleared %d expired OAuth temporary record(s).', 'aoauth-client-sso'), $deleted)));
    }

    public function ajax_toggle_deep_debug() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(-1);
        }

        $enabled = !empty($_POST['enabled']);
        $result = $this->update_wp_config_deep_debug($enabled);
        if (is_wp_error($result)) {
            wp_send_json_error(array(
                'message' => $result->get_error_message(),
                'snippet' => 'define("OAUTH-DEBUG", "enabled");'
            ));
        }

        $this->logger->log($enabled ? 'deep_debug_enabled' : 'deep_debug_disabled', array(
            'updated_wp_config' => true
        ), get_current_user_id(), null, 'info');

        wp_send_json_success(array(
            'message' => $enabled ? __('Deep Debug enabled in wp-config.php.', 'aoauth-client-sso') : __('Deep Debug disabled in wp-config.php.', 'aoauth-client-sso'),
            'enabled' => $enabled
        ));
    }

    private function update_wp_config_deep_debug($enabled) {
        $config_path = $this->get_wp_config_path();
        if (!$config_path || !is_readable($config_path)) {
            return new WP_Error('wp_config_not_found', __('Could not find a readable wp-config.php file. Update it manually with the displayed constant.', 'aoauth-client-sso'));
        }

        if (!is_writable($config_path)) {
            return new WP_Error('wp_config_not_writable', __('wp-config.php is not writable by WordPress. Update it manually with the displayed constant.', 'aoauth-client-sso'));
        }

        $config = file_get_contents($config_path);
        if ($config === false) {
            return new WP_Error('wp_config_read_failed', __('Could not read wp-config.php. Update it manually with the displayed constant.', 'aoauth-client-sso'));
        }

        $constant_line = 'define("OAUTH-DEBUG", "enabled");';
        $config = preg_replace('/^[ \t]*define\s*\(\s*[\'"]OAUTH-DEBUG[\'"]\s*,\s*[\'"]enabled[\'"]\s*\)\s*;\s*[\r\n]*/mi', '', $config);
        $config = preg_replace('/^[ \t]*define\s*\(\s*[\'"]AOAUTH_DEBUG[\'"]\s*,\s*true\s*\)\s*;\s*[\r\n]*/mi', '', $config);

        if ($enabled) {
            $marker_pattern = '/\/\*\s*That\'s all, stop editing!.*?\*\//i';
            if (preg_match($marker_pattern, $config)) {
                $config = preg_replace($marker_pattern, $constant_line . PHP_EOL . '$0', $config, 1);
            } else {
                $config .= PHP_EOL . $constant_line . PHP_EOL;
            }
        }

        if (file_put_contents($config_path, $config, LOCK_EX) === false) {
            return new WP_Error('wp_config_write_failed', __('Could not write wp-config.php. Update it manually with the displayed constant.', 'aoauth-client-sso'));
        }

        return true;
    }

    private function get_wp_config_path() {
        $candidates = array(
            ABSPATH . 'wp-config.php',
            dirname(ABSPATH) . '/wp-config.php',
        );

        foreach ($candidates as $candidate) {
            if (file_exists($candidate)) {
                return $candidate;
            }
        }

        return '';
    }

    private function delete_transients_by_prefixes($prefixes, $expired_only) {
        global $wpdb;

        $deleted = 0;
        foreach ($prefixes as $prefix) {
            $timeout_like = $wpdb->esc_like('_transient_timeout_' . $prefix) . '%';
            if ($expired_only) {
                $timeout_names = $wpdb->get_col($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
                    $timeout_like,
                    time()
                ));
            } else {
                $timeout_names = $wpdb->get_col($wpdb->prepare(
                    "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s",
                    $timeout_like
                ));
            }

            foreach ($timeout_names as $timeout_name) {
                $transient_name = substr($timeout_name, strlen('_transient_timeout_'));
                if (delete_transient($transient_name)) {
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * AJAX handler for unlinking a single account
     */
    public function ajax_unlink_account() {
        $nonce = sanitize_text_field($_POST['nonce'] ?? '');
        $user_id = intval($_POST['user_id'] ?? 0);
        $provider = sanitize_text_field($_POST['provider'] ?? '');

        if (!wp_verify_nonce($nonce, 'aoauth_unlink_' . $user_id)) {
            wp_send_json_error(array('message' => __('Security check failed. Please refresh the page and try again.', 'aoauth-client-sso')));
        }

        if (!$user_id || !$provider) {
            wp_send_json_error(array('message' => __('Invalid request parameters.', 'aoauth-client-sso')));
        }

        if (!current_user_can('edit_user', $user_id)) {
            wp_send_json_error(array('message' => __('You do not have permission to unlink this account.', 'aoauth-client-sso')));
        }

        $current_user_id = get_current_user_id();
        $target_user = get_userdata($user_id);

        if (!$target_user) {
            wp_send_json_error(array('message' => __('User not found.', 'aoauth-client-sso')));
        }

        $is_target_admin = in_array('administrator', (array) $target_user->roles);
        if ($is_target_admin) {
            $admin_count = count(get_users(array('role' => 'administrator')));

            if ($admin_count === 1) {
                wp_send_json_error(array('message' => __('Cannot unlink the only administrator account. Please create another admin account first.', 'aoauth-client-sso')));
            }
        }

        if ($user_id === $current_user_id && empty($target_user->user_pass)) {
            wp_send_json_error(array(
                'message' => __('You cannot unlink your SSO account because you have no WordPress password set. Please set a password first via "Lost your password?" on the login page.', 'aoauth-client-sso')
            ));
        }

        $linked_providers = AOAUTH_Core::get_user_linked_providers($user_id);
        if (!isset($linked_providers[$provider])) {
            wp_send_json_error(array('message' => __('Provider mismatch or account not linked.', 'aoauth-client-sso')));
        }

        $deleted = AOAUTH_Core::unlink_user_provider($user_id, $provider);
        if ($deleted) {
            $this->logger->log('account_unlinked', array(
                'user_id' => $user_id,
                'provider' => $provider,
                'action_by' => $current_user_id
            ), $user_id, $provider, 'info');

            wp_send_json_success(array('message' => __('SSO account unlinked successfully.', 'aoauth-client-sso')));
        }

        wp_send_json_error(array('message' => __('Failed to unlink account. Please try again.', 'aoauth-client-sso')));
    }
    
    /**
     * AJAX handler for bulk unlinking accounts
     */
    public function ajax_bulk_unlink_accounts() {
        check_ajax_referer('aoauth_admin_nonce', 'nonce');
        
        if (!current_user_can('delete_users')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform bulk unlinking.', 'aoauth-client-sso')));
        }
        
        $user_ids = isset($_POST['user_ids']) ? array_map('intval', (array)$_POST['user_ids']) : array();
        
        if (empty($user_ids)) {
            wp_send_json_error(array('message' => __('No users selected.', 'aoauth-client-sso')));
        }
        
        $unlinked_count = 0;
        $failed_count = 0;
        $current_user_id = get_current_user_id();
        $admin_count = count(get_users(array('role' => 'administrator')));
        
        foreach ($user_ids as $user_id) {
            $linked_providers = AOAUTH_Core::get_user_linked_providers($user_id);
            
            if (empty($linked_providers)) {
                $failed_count++;
                continue;
            }
            
            // Prevent unlinking the only admin
            if ($user_id === $current_user_id && $admin_count === 1) {
                $failed_count++;
                continue;
            }
            
            foreach (array_keys($linked_providers) as $provider) {
                $result = AOAUTH_Core::unlink_user_provider($user_id, $provider);

                if ($result) {
                    $unlinked_count++;
                    $this->logger->log('account_unlinked_bulk', array(
                        'user_id' => $user_id,
                        'provider' => $provider,
                        'action_by' => $current_user_id
                    ), $user_id, $provider, 'info');
                } else {
                    $failed_count++;
                }
            }
        }
        
        wp_send_json_success(array(
            'message' => sprintf(
                __('Unlinked %d accounts. Failed: %d', 'aoauth-client-sso'),
                $unlinked_count,
                $failed_count
            ),
            'unlinked' => $unlinked_count,
            'failed' => $failed_count
        ));
    }
    
    /**
     * Verify Cloudflare Turnstile token
     */
    public function ajax_verify_turnstile() {
        check_ajax_referer('aoauth_public_nonce', 'nonce');
        
        $token = sanitize_text_field($_POST['token'] ?? '');
        $flow_id = isset($_POST['flow_id']) ? sanitize_text_field(wp_unslash($_POST['flow_id'])) : '';
        $provider = isset($_POST['provider']) ? sanitize_key(wp_unslash($_POST['provider'])) : '';
        $settings = get_option('aoauth_settings', array());
        $secret = aoauth_core()->get_security()->decrypt_secret($settings['turnstile_secret_key'] ?? '');
        
        if (empty($token) || empty($secret)) {
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'turnstile',
                'reason' => 'missing_verification_data'
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => __('Missing verification data.', 'aoauth-client-sso')));
        }
        
        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
            'body' => array(
                'secret' => $secret,
                'response' => $token
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'turnstile',
                'error' => $response->get_error_message()
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => __('Verification service unavailable.', 'aoauth-client-sso')));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($body['success'])) {
            $this->logger->log('bot_verification_success', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'turnstile'
            ), null, $provider, 'success');
            wp_send_json_success(array('verification' => $this->create_bot_verification_token('turnstile', $flow_id, $provider)));
        } else {
            $error_codes = isset($body['error-codes']) ? implode(', ', $body['error-codes']) : 'unknown';
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'turnstile',
                'error_codes' => $error_codes
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => __('Bot challenge failed. Please try again.', 'aoauth-client-sso')));
        }
    }
    
    /**
     * Verify Google reCAPTCHA v3 token
     */
    public function ajax_verify_recaptcha() {
        check_ajax_referer('aoauth_public_nonce', 'nonce');
        
        $token = sanitize_text_field($_POST['token'] ?? '');
        $flow_id = isset($_POST['flow_id']) ? sanitize_text_field(wp_unslash($_POST['flow_id'])) : '';
        $provider = isset($_POST['provider']) ? sanitize_key(wp_unslash($_POST['provider'])) : '';
        $settings = get_option('aoauth_settings', array());
        $secret = aoauth_core()->get_security()->decrypt_secret($settings['recaptcha_secret_key'] ?? '');
        $expected_score = floatval($settings['recaptcha_score_threshold'] ?? 0.5);
        
        if (empty($token) || empty($secret)) {
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'recaptcha',
                'reason' => 'missing_verification_data'
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => __('Missing verification data.', 'aoauth-client-sso')));
        }
        
        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $secret,
                'response' => $token
            ),
            'timeout' => 10
        ));
        
        if (is_wp_error($response)) {
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'recaptcha',
                'error' => $response->get_error_message()
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => __('Verification service unavailable.', 'aoauth-client-sso')));
        }
        
        $body = json_decode(wp_remote_retrieve_body($response), true);
        
        if (!empty($body['success']) && isset($body['score']) && $body['score'] >= $expected_score) {
            $this->logger->log('bot_verification_success', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'recaptcha',
                'score' => $body['score']
            ), null, $provider, 'success');
            wp_send_json_success(array('verification' => $this->create_bot_verification_token('recaptcha', $flow_id, $provider)));
        } else {
            $this->logger->log('bot_verification_failed', array(
                'flow_id' => $flow_id,
                'provider' => $provider,
                'type' => 'recaptcha',
                'score' => $body['score'] ?? 'unknown',
                'error_codes' => implode(', ', $body['error-codes'] ?? array())
            ), null, $provider, 'error');
            wp_send_json_error(array('message' => sprintf(
                __('Bot verification failed. Score %s is below threshold of %s.', 'aoauth-client-sso'),
                $body['score'] ?? 'unknown',
                $expected_score
            )));
        }
    }
    
    private function create_bot_verification_token($type, $flow_id = '', $provider = '') {
        $token = aoauth_core()->get_security()->generate_secure_token(48);
        set_transient('aoauth_bot_' . md5($token), array(
            'verified' => true,
            'type' => sanitize_key($type),
            'flow_id' => sanitize_text_field($flow_id),
            'provider' => sanitize_key($provider),
            'created_at' => time(),
            'ip' => sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '')
        ), 5 * MINUTE_IN_SECONDS);
        
        return $token;
    }
    
    public function add_plugin_action_links($links) {
        $wizard_link = '<a href="' . admin_url('admin.php?page=aoauth-wizard') . '">' . __('Add Provider', 'aoauth-client-sso') . '</a>';
        $settings_link = '<a href="' . admin_url('admin.php?page=aoauth-sign-in-experience') . '">' . __('Settings', 'aoauth-client-sso') . '</a>';
        
        array_unshift($links, $settings_link);
        array_unshift($links, $wizard_link);
        
        return $links;
    }
}
