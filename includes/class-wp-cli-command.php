<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_WP_CLI_Command {
    /**
     * Displays comprehensive non-sensitive plugin configuration.
     *
     * ## OPTIONS
     *
     * [--format=<format>]
     * : Output format: table, json, csv, or yaml. Defaults to table.
     *
     * ## EXAMPLES
     *
     *     wp aoauth status
     *     wp aoauth status --format=json
     *
     * @subcommand status
     */
    public function status($args, $assoc_args) {
        unset($args);

        $settings = array_merge(AOAUTH_Core::get_default_settings(), get_option('aoauth_settings', array()));
        $applications = get_option('aoauth_applications', array());
        $applications = is_array($applications) ? $applications : array();
        $enabled_providers = array_filter($applications, static function($application) {
            return !empty($application['enabled']);
        });
        $next_cleanup = wp_next_scheduled('aoauth_retention_cron');

        $items = array(
            $this->status_item('runtime', 'version', AOAUTH_VERSION),
            $this->status_item('runtime', 'configured_providers', count($applications)),
            $this->status_item('runtime', 'enabled_providers', count($enabled_providers)),
            $this->status_item('runtime', 'next_log_cleanup', $next_cleanup ? wp_date('Y-m-d H:i:s T', $next_cleanup) : 'not scheduled'),
            $this->status_item('runtime', 'deep_debug', aoauth_core()->get_debug()->is_enabled()),
            $this->status_item('sign_in', 'enable_login_buttons', $settings['enable_login_buttons']),
            $this->status_item('sign_in', 'enable_provider_auto_login', $settings['enable_provider_auto_login']),
            $this->status_item('sign_in', 'enable_brand_badge', $settings['enable_brand_badge']),
            $this->status_item('sign_in', 'login_button_theme', $settings['login_button_theme']),
            $this->status_item('sign_in', 'login_button_layout', $settings['login_button_layout']),
            $this->status_item('sign_in', 'login_button_position', $settings['login_button_position']),
            $this->status_item('sign_in', 'linking_page_use_theme', $settings['linking_page_use_theme']),
            $this->status_item('sign_in', 'linking_page_title', $settings['linking_page_title']),
            $this->status_item('sign_in', 'bot_overlay_enabled', $settings['bot_overlay_enabled']),
            $this->status_item('sign_in', 'bot_overlay_message', $settings['bot_overlay_message']),
            $this->status_item('sign_in', 'bot_overlay_variant', $settings['bot_overlay_variant']),
            $this->status_item('sign_in', 'bot_overlay_opacity', $settings['bot_overlay_opacity']),
            $this->status_item('sign_in', 'bot_overlay_branding_enabled', $settings['bot_overlay_branding_enabled']),
            $this->status_item('users', 'auto_create_users', $settings['auto_create_users']),
            $this->status_item('users', 'default_role', $settings['default_role']),
            $this->status_item('users', 'allow_account_linking', $settings['allow_account_linking']),
            $this->status_item('users', 'enable_self_service_account_linking', $settings['enable_self_service_account_linking']),
            $this->status_item('users', 'linking_max_attempts', $settings['linking_max_attempts']),
            $this->status_item('users', 'linking_lockout_minutes', $settings['linking_lockout_minutes']),
            $this->status_item('users', 'linking_login_ban_minutes', $settings['linking_login_ban_minutes']),
            $this->status_item('security', 'security_level', $settings['security_level']),
            $this->status_item('security', 'rate_limit_attempts', $settings['rate_limit_attempts']),
            $this->status_item('security', 'rate_limit_window', $settings['rate_limit_window']),
            $this->status_item('security', 'enable_bot_protection', $settings['enable_bot_protection']),
            $this->status_item('security', 'bot_protection_provider', $settings['bot_protection_provider']),
            $this->status_item('security', 'enable_turnstile', $settings['enable_turnstile']),
            $this->status_item('security', 'turnstile_site_key', $this->configured_value($settings['turnstile_site_key'])),
            $this->status_item('security', 'turnstile_secret_key', $this->configured_value($settings['turnstile_secret_key'])),
            $this->status_item('security', 'turnstile_display_mode', $settings['turnstile_display_mode']),
            $this->status_item('security', 'enable_recaptcha', $settings['enable_recaptcha']),
            $this->status_item('security', 'recaptcha_site_key', $this->configured_value($settings['recaptcha_site_key'])),
            $this->status_item('security', 'recaptcha_secret_key', $this->configured_value($settings['recaptcha_secret_key'])),
            $this->status_item('security', 'recaptcha_score_threshold', $settings['recaptcha_score_threshold']),
            $this->status_item('tools', 'enable_logs', $settings['enable_logs']),
            $this->status_item('tools', 'logs_retention_period', $settings['logs_retention_period']),
            $this->status_item('tools', 'delete_data_on_uninstall', $settings['delete_data_on_uninstall']),
        );

        foreach (wp_roles()->get_names() as $role_key => $role_name) {
            $items[] = array(
                'section' => 'User Management',
                'setting' => $role_name . ' Redirect',
                'value' => (string) ($settings['role_redirects'][$role_key] ?? '/'),
            );
        }

        foreach ($applications as $provider_id => $application) {
            $provider_name = sanitize_text_field($application['app_name'] ?? $provider_id);
            $items[] = array(
                'section' => 'Providers',
                'setting' => $provider_name,
                'value' => !empty($application['enabled']) ? 'Enabled' : 'Disabled',
            );
        }

        \WP_CLI\Utils\format_items(
            $this->get_format($assoc_args),
            $items,
            array('section', 'setting', 'value')
        );
    }

    /**
     * Lists configured providers without exposing credentials.
     *
     * [--format=<format>]
     * : Output format: table, json, csv, or yaml. Defaults to table.
     *
     * ## EXAMPLES
     *
     *     wp aoauth providers
     *     wp aoauth providers --format=json
     *
     * @subcommand providers
     */
    public function providers($args, $assoc_args) {
        unset($args);

        $applications = get_option('aoauth_applications', array());
        if (!is_array($applications) || empty($applications)) {
            WP_CLI::warning('No OAuth/OIDC providers are configured.');
            return;
        }

        $items = array();
        foreach ($applications as $provider_id => $application) {
            $items[] = array(
                'id' => (string) $provider_id,
                'name' => sanitize_text_field($application['app_name'] ?? $provider_id),
                'type' => sanitize_text_field($application['provider_name'] ?? 'custom'),
                'status' => !empty($application['enabled']) ? 'enabled' : 'disabled',
                'client_id' => $this->configured_value($application['client_id'] ?? ''),
                'client_secret' => $this->configured_value($application['client_secret'] ?? ''),
            );
        }

        \WP_CLI\Utils\format_items(
            $this->get_format($assoc_args),
            $items,
            array('id', 'name', 'type', 'status', 'client_id', 'client_secret')
        );
    }

    /**
     * Exports settings and providers to a JSON backup.
     *
     * ## OPTIONS
     *
     * <file>
     * : Destination JSON file.
     *
     * [--include-credentials]
     * : Include password-encrypted credentials. Requires AOAUTH_BACKUP_PASSWORD.
     *
     * [--force]
     * : Overwrite an existing file.
     *
     * ## EXAMPLES
     *
     *     wp aoauth export aoauth-backup.json
     *     AOAUTH_BACKUP_PASSWORD='strong password' wp aoauth export aoauth-backup.json --include-credentials
     *
     * @subcommand export
     */
    public function export($args, $assoc_args) {
        $file = $this->validate_export_path($args[0] ?? '', !empty($assoc_args['force']));
        $include_credentials = !empty($assoc_args['include-credentials']);
        $password = $this->get_backup_password($include_credentials);
        $admin = new AOAUTH_Admin();
        $config = $admin->prepare_export_configuration($password);
        $json = wp_json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

        if ($json === false || file_put_contents($file, $json . PHP_EOL, LOCK_EX) === false) {
            WP_CLI::error('Could not write the configuration backup.');
        }
        if (!chmod($file, 0600)) {
            WP_CLI::warning('The backup was written, but its file permissions could not be restricted to the current user.');
        }

        WP_CLI::success(sprintf(
            'Configuration exported to %s. Credentials: %s.',
            $file,
            $include_credentials ? 'password encrypted' : 'excluded'
        ));
    }

    /**
     * Imports settings and providers from a JSON backup.
     *
     * ## OPTIONS
     *
     * <file>
     * : Source JSON file exported by aOAUTH.
     *
     * [--yes]
     * : Confirm replacement of current settings and providers.
     *
     * ## EXAMPLES
     *
     *     wp aoauth import aoauth-backup.json
     *     AOAUTH_BACKUP_PASSWORD='strong password' wp aoauth import aoauth-backup.json --yes
     *
     * @subcommand import
     */
    public function import($args, $assoc_args) {
        $file = $this->validate_import_path($args[0] ?? '');
        WP_CLI::confirm('Importing replaces current aOAUTH settings and providers. Continue?', $assoc_args);

        $contents = file_get_contents($file);
        $config = json_decode($contents, true);
        if (!is_array($config) || json_last_error() !== JSON_ERROR_NONE) {
            WP_CLI::error('The backup is not valid JSON.');
        }

        $contains_encrypted_credentials = $this->contains_encrypted_credentials($config);
        $password = $this->get_backup_password($contains_encrypted_credentials);
        $admin = new AOAUTH_Admin();
        $result = $admin->import_configuration($config, $password);
        if (is_wp_error($result)) {
            WP_CLI::error($result->get_error_message());
        }

        aoauth_core()->schedule_retention_cron();
        WP_CLI::success($result['message']);
    }

    private function status_item($section, $setting, $value) {
        $boolean_settings = array(
            'deep_debug',
            'enable_login_buttons',
            'enable_provider_auto_login',
            'enable_brand_badge',
            'linking_page_use_theme',
            'bot_overlay_enabled',
            'bot_overlay_branding_enabled',
            'auto_create_users',
            'allow_account_linking',
            'enable_self_service_account_linking',
            'enable_bot_protection',
            'enable_turnstile',
            'enable_recaptcha',
            'enable_logs',
            'delete_data_on_uninstall',
        );

        if (in_array($setting, $boolean_settings, true)) {
            $value = !empty($value) ? 'Enabled' : 'Disabled';
        }

        return array(
            'section' => $this->get_section_label($section),
            'setting' => $this->get_setting_label($setting),
            'value' => $this->get_setting_value_label($setting, $value),
        );
    }

    private function configured_value($value) {
        return $value === '' || $value === null ? 'Not Configured' : 'Configured';
    }

    private function get_section_label($section) {
        $labels = array(
            'runtime' => 'Plugin Status',
            'sign_in' => 'Sign-In Experience',
            'users' => 'User Management',
            'security' => 'Security',
            'tools' => 'Tools',
        );

        return $labels[$section] ?? ucwords(str_replace('_', ' ', $section));
    }

    private function get_setting_label($setting) {
        $labels = array(
            'version' => 'Plugin Version',
            'configured_providers' => 'Configured Providers',
            'enabled_providers' => 'Enabled Providers',
            'next_log_cleanup' => 'Next Cleanup',
            'deep_debug' => 'Deep Debug Mode',
            'enable_login_buttons' => 'Enable Login Page SSO Buttons',
            'enable_provider_auto_login' => 'Silent auto-login for existing linked SSO sessions',
            'enable_brand_badge' => 'Show Brand Badge',
            'login_button_theme' => 'Single Sign-On Theme',
            'login_button_layout' => 'Login Button Layout',
            'login_button_position' => 'SSO Button Position',
            'linking_page_use_theme' => 'Use Theme on Linking Page',
            'linking_page_title' => 'Linking Page Title',
            'bot_overlay_enabled' => 'Full-Screen Verification Overlay',
            'bot_overlay_message' => 'Verification Overlay Message',
            'bot_overlay_variant' => 'Bot Verification Overlay Style',
            'bot_overlay_opacity' => 'Overlay Opacity',
            'bot_overlay_branding_enabled' => 'Show Verification Branding',
            'auto_create_users' => 'Auto-Create Users',
            'default_role' => 'Default User Role',
            'allow_account_linking' => 'Allow Account Linking',
            'enable_self_service_account_linking' => 'Self-Service Account Linking',
            'linking_max_attempts' => 'Max Password Attempts',
            'linking_lockout_minutes' => 'Lockout Duration',
            'linking_login_ban_minutes' => 'Full Login Ban',
            'role_redirects' => 'Role-Based Redirects',
            'security_level' => 'Security Level',
            'rate_limit_attempts' => 'Rate Limit Attempts',
            'rate_limit_window' => 'Rate Limit Window',
            'enable_bot_protection' => 'Enable Bot Protection',
            'bot_protection_provider' => 'Verification Provider',
            'enable_turnstile' => 'Cloudflare Turnstile Active',
            'turnstile_site_key' => 'Turnstile Site Key',
            'turnstile_secret_key' => 'Turnstile Secret Key',
            'turnstile_display_mode' => 'Turnstile Display Mode',
            'enable_recaptcha' => 'Google reCAPTCHA Active',
            'recaptcha_site_key' => 'reCAPTCHA Site Key',
            'recaptcha_secret_key' => 'reCAPTCHA Secret Key',
            'recaptcha_score_threshold' => 'Score Threshold',
            'enable_logs' => 'Enable Activity Logs',
            'logs_retention_period' => 'Log Retention Period',
            'delete_data_on_uninstall' => 'Delete Plugin Data on Uninstall',
        );

        return $labels[$setting] ?? ucwords(str_replace('_', ' ', $setting));
    }

    private function get_setting_value_label($setting, $value) {
        $value_labels = array(
            'login_button_layout' => array(
                'vertical' => 'Full Width Stack',
                'wrap-centered' => 'Wrap Centered',
                'two-column' => 'Two Columns',
                'compact' => 'Compact Row',
            ),
            'login_button_position' => array(
                'below_form' => 'Below Login Form',
                'inside_form' => 'Inside Login Form',
            ),
            'security_level' => array(
                'high' => 'High (Recommended)',
                'medium' => 'Medium',
            ),
            'bot_protection_provider' => array(
                'turnstile' => 'Cloudflare Turnstile',
                'recaptcha' => 'Google reCAPTCHA v3',
            ),
            'turnstile_display_mode' => array(
                'invisible' => 'Invisible',
                'managed' => 'Managed Visible in Overlay',
                'non-interactive' => 'Non-Interactive',
            ),
            'bot_overlay_variant' => array(
                'spotlight' => 'Spotlight',
                'constellation' => 'Constellation',
                'minimal' => 'Minimal',
            ),
            'logs_retention_period' => array(
                '7_days' => '7 Days',
                '14_days' => '14 Days',
                '30_days' => '30 Days',
                '60_days' => '60 Days',
                '90_days' => '90 Days',
                '6_months' => '6 Months',
                '1_year' => '1 Year',
                'forever' => 'Forever',
            ),
        );

        if ($setting === 'default_role') {
            $role = get_role((string) $value);
            if ($role) {
                $roles = wp_roles()->get_names();
                return isset($roles[$value]) ? (string) $roles[$value] : ucwords(str_replace('_', ' ', (string) $value));
            }
        }

        if ($setting === 'login_button_theme') {
            return ucwords(str_replace('-', ' ', (string) $value));
        }

        if ($setting === 'bot_overlay_opacity') {
            return (string) $value . '%';
        }

        if ($setting === 'rate_limit_window') {
            return (string) $value . ' seconds';
        }

        if (in_array($setting, array('linking_lockout_minutes', 'linking_login_ban_minutes'), true)) {
            return (string) $value . ' minutes';
        }

        if (isset($value_labels[$setting][(string) $value])) {
            return $value_labels[$setting][(string) $value];
        }

        return (string) $value;
    }

    private function validate_export_path($path, $force) {
        if ($path === '') {
            WP_CLI::error('A destination JSON file is required.');
        }

        $directory = realpath(dirname($path));
        if ($directory === false || !is_dir($directory) || !is_writable($directory)) {
            WP_CLI::error('The destination directory does not exist or is not writable.');
        }

        $file = $directory . DIRECTORY_SEPARATOR . basename($path);
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') {
            WP_CLI::error('The export file must use a .json extension.');
        }
        if (file_exists($file) && !$force) {
            WP_CLI::error('The destination file already exists. Use --force to overwrite it.');
        }
        if (is_link($file)) {
            WP_CLI::error('Refusing to write a configuration backup through a symbolic link.');
        }

        return $file;
    }

    private function validate_import_path($path) {
        $file = realpath($path);
        if ($file === false || !is_file($file) || !is_readable($file)) {
            WP_CLI::error('The configuration backup does not exist or is not readable.');
        }
        if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'json') {
            WP_CLI::error('The import file must use a .json extension.');
        }
        if (filesize($file) > 5 * MB_IN_BYTES) {
            WP_CLI::error('The configuration backup exceeds the 5 MB limit.');
        }

        return $file;
    }

    private function contains_encrypted_credentials($config) {
        return isset($config['credentials']) && $config['credentials'] === 'password_encrypted';
    }

    private function get_backup_password($required) {
        $password = getenv('AOAUTH_BACKUP_PASSWORD');
        $password = is_string($password) ? $password : '';

        if ($required && $password === '') {
            WP_CLI::error('Set AOAUTH_BACKUP_PASSWORD in the environment for encrypted credential backups.');
        }

        return $required ? $password : '';
    }

    private function get_format($assoc_args) {
        $format = isset($assoc_args['format']) ? sanitize_key($assoc_args['format']) : 'table';
        $allowed_formats = array('table', 'json', 'csv', 'yaml');

        if (!in_array($format, $allowed_formats, true)) {
            WP_CLI::error('Invalid format. Accepted values: table, json, csv, yaml.');
        }

        return $format;
    }
}

class AOAUTH_WP_CLI_Provider_Command {
    /**
     * Enables an existing provider.
     *
     * ## OPTIONS
     *
     * <id>
     * : Provider identifier shown by `wp aoauth providers`.
     *
     * ## EXAMPLES
     *
     *     wp aoauth provider enable keycloak
     */
    public function enable($args) {
        $this->set_status($args[0] ?? '', true);
    }

    /**
     * Disables an existing provider.
     *
     * ## OPTIONS
     *
     * <id>
     * : Provider identifier shown by `wp aoauth providers`.
     *
     * ## EXAMPLES
     *
     *     wp aoauth provider disable keycloak
     */
    public function disable($args) {
        $this->set_status($args[0] ?? '', false);
    }

    private function set_status($provider_id, $enabled) {
        $provider_id = sanitize_text_field($provider_id);
        $applications = get_option('aoauth_applications', array());

        if ($provider_id === '' || !is_array($applications) || !isset($applications[$provider_id])) {
            WP_CLI::error('Provider not found. Run `wp aoauth providers` to list valid IDs.');
        }
        if ($enabled && (empty($applications[$provider_id]['client_id']) || empty($applications[$provider_id]['client_secret']))) {
            WP_CLI::error('The provider cannot be enabled until its Client ID and Client Secret are configured.');
        }

        $applications[$provider_id]['enabled'] = $enabled ? 1 : 0;
        update_option('aoauth_applications', $applications);
        aoauth_core()->get_logger()->log(
            $enabled ? 'provider_enabled' : 'provider_disabled',
            array('provider' => $provider_id, 'source' => 'wp-cli'),
            null,
            $provider_id,
            'info'
        );

        WP_CLI::success(sprintf(
            'Provider %s %s.',
            $provider_id,
            $enabled ? 'enabled' : 'disabled'
        ));
    }
}
