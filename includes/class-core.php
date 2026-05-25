<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Core {
    private static $instance = null;
    private $providers_manager;
    private $security;
    private $logger;
    private $debug;
    
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function __construct() {
        $this->debug = AOAUTH_Debug::get_instance();
        $this->debug->log_start('AOAUTH_Core::__construct');
        
        $this->security = new AOAUTH_Security();
        $this->logger = new AOAUTH_Logger();
        $this->providers_manager = new AOAUTH_Providers_Manager();
        add_action('init', array($this, 'schedule_retention_cron'));
        
        $this->debug->log_end('AOAUTH_Core::__construct');
    }
    
    public function get_debug() {
        return $this->debug;
    }
    
    public function init() {
        $this->debug->log_start('AOAUTH_Core::init');
        
        load_plugin_textdomain('aoauth-client-sso', false, dirname(AOAUTH_PLUGIN_BASENAME) . '/languages');
        
        add_action('login_enqueue_scripts', array($this, 'enqueue_login_assets'));
        add_action('login_form', array($this, 'render_login_buttons'));
        
        $this->debug->info('Plugin initialized');
        $this->debug->log_end('AOAUTH_Core::init');
    }
    
    public function activate() {
        $this->debug->log_start('AOAUTH_Core::activate');
        
        $this->create_tables();
        $this->set_default_options();
        $this->schedule_retention_cron();
        $this->logger->log('plugin_activated', 'Plugin activated successfully');
        
        $this->debug->info('Plugin activated', array('version' => AOAUTH_VERSION));
        $this->debug->log_end('AOAUTH_Core::activate');
    }
    
    public static function get_default_settings() {
        return array(
            'enable_login_buttons' => '1',
            'auto_create_users' => '1',
            'default_role' => 'subscriber',
            'allow_account_linking' => '0',
            'delete_data_on_uninstall' => '0',
            'security_level' => 'high',
            'rate_limit_attempts' => '5',
            'rate_limit_window' => '300',
            'enable_logs' => '1',
            'logs_retention_period' => '30_days',
            'login_button_theme' => 'modern',
            'login_button_layout' => 'vertical',
            'enable_turnstile' => '0',
            'turnstile_site_key' => '',
            'turnstile_secret_key' => '',
            'enable_recaptcha' => '0',
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'recaptcha_score_threshold' => '0.5',
            'linking_max_attempts' => '5',
            'linking_lockout_minutes' => '15',
            'linking_page_use_theme' => '1',
            'linking_page_title' => 'Link Your Account',
            'bot_overlay_enabled' => '1',
            'bot_overlay_message' => 'Verifying secure sign-in...'
        );
    }
    
    public function deactivate() {
        $this->debug->log_start('AOAUTH_Core::deactivate');
        
        $this->logger->log('plugin_deactivated', 'Plugin deactivated');
        wp_clear_scheduled_hook('aoauth_retention_cron');
        
        $this->debug->info('Plugin deactivated');
        $this->debug->log_end('AOAUTH_Core::deactivate');
    }
    
    public function uninstall() {
        $this->debug->log_start('AOAUTH_Core::uninstall');
        
        $options = get_option('aoauth_settings', array());
        if (!empty($options['delete_data_on_uninstall'])) {
            $this->delete_all_data();
            $this->debug->info('All plugin data deleted during uninstall');
        }
        
        $this->debug->log_end('AOAUTH_Core::uninstall');
    }
    
    private function create_tables() {
        $this->debug->log_start('AOAUTH_Core::create_tables');
        
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        $table_logs = $wpdb->prefix . 'aoauth_logs';
        
        $sql = "CREATE TABLE IF NOT EXISTS $table_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            event_type varchar(50) NOT NULL,
            event_data longtext,
            user_id bigint(20) DEFAULT NULL,
            provider varchar(50) DEFAULT NULL,
            status varchar(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY event_type (event_type),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) $charset_collate;";
        
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        $result = dbDelta($sql);
        
        $this->debug->info('Database tables created/updated', array('result' => $result));
        $this->debug->log_end('AOAUTH_Core::create_tables');
    }
    
    private function set_default_options() {
        $this->debug->log_start('AOAUTH_Core::set_default_options');
        
        $default_settings = self::get_default_settings();
        
        $existing = get_option('aoauth_settings', array());
        if (empty($existing)) {
            update_option('aoauth_settings', $default_settings);
            $this->debug->info('Default settings created');
        } else {
            $merged = array_merge($default_settings, $existing);
            update_option('aoauth_settings', $merged);
            $this->debug->info('Settings merged with defaults');
        }
        
        $this->debug->log_end('AOAUTH_Core::set_default_options');
    }
    
    private function delete_all_data() {
        $this->debug->log_start('AOAUTH_Core::delete_all_data');
        
        global $wpdb;
        
        $tables = array('aoauth_logs');
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}{$table}");
            $this->debug->debug('Dropped table', array('table' => $table));
        }
        
        delete_option('aoauth_settings');
        delete_option('aoauth_applications');
        delete_option('aoauth_version');
        delete_option('aoauth_encryption_key');
        
        $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_aoauth_%'");
        
        $this->debug->info('All plugin data deleted');
        $this->debug->log_end('AOAUTH_Core::delete_all_data');
    }
    
    public function get_available_themes() {
        $this->debug->log_start('AOAUTH_Core::get_available_themes');
        
        $themes_dir = AOAUTH_PLUGIN_DIR . 'public/css/themes/';
        $themes = array();
        
        if (is_dir($themes_dir)) {
            $files = scandir($themes_dir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'css') {
                    $theme_name = pathinfo($file, PATHINFO_FILENAME);
                    $display_name = ucfirst(str_replace('-', ' ', $theme_name));
                    
                    $themes[] = array(
                        'id' => $theme_name,
                        'name' => $display_name,
                        'file' => $theme_name . '.css',
                        'path' => $themes_dir . $file,
                        'url' => AOAUTH_PLUGIN_URL . 'public/css/themes/' . $theme_name . '.css'
                    );
                }
            }
        }
        
        usort($themes, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        $this->debug->debug('Available themes loaded', array('count' => count($themes)));
        $this->debug->log_end('AOAUTH_Core::get_available_themes');
        
        return $themes;
    }
    
    public function enqueue_login_assets() {
        $this->debug->log_start('AOAUTH_Core::enqueue_login_assets');
        
        $settings = array_merge(self::get_default_settings(), get_option('aoauth_settings', array()));
        if (empty($settings['enable_login_buttons'])) {
            $this->debug->debug('Login buttons disabled, skipping asset enqueue');
            $this->debug->log_end('AOAUTH_Core::enqueue_login_assets');
            return;
        }
        
        wp_enqueue_style(
            'aoauth-public',
            AOAUTH_PLUGIN_URL . 'public/css/public-style.css',
            array(),
            AOAUTH_VERSION
        );
        
        $theme = isset($settings['login_button_theme']) ? $settings['login_button_theme'] : 'modern';
        $theme_css_path = AOAUTH_PLUGIN_DIR . 'public/css/themes/' . $theme . '.css';
        
        if (file_exists($theme_css_path)) {
            wp_enqueue_style(
                'aoauth-theme-' . $theme,
                AOAUTH_PLUGIN_URL . 'public/css/themes/' . $theme . '.css',
                array('aoauth-public'),
                AOAUTH_VERSION
            );
            $this->debug->debug('Theme CSS enqueued', array('theme' => $theme));
        }
        
        $turnstile_enabled = !empty($settings['enable_turnstile']) && !empty($settings['turnstile_site_key']);
        $recaptcha_enabled = !empty($settings['enable_recaptcha']) && !empty($settings['recaptcha_site_key']);
        
        if ($turnstile_enabled) {
            wp_enqueue_script(
                'cloudflare-turnstile',
                'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit',
                array(),
                null,
                true
            );
            $this->debug->debug('Turnstile script enqueued');
        } elseif ($recaptcha_enabled) {
            wp_enqueue_script(
                'google-recaptcha',
                'https://www.google.com/recaptcha/api.js?render=' . urlencode($settings['recaptcha_site_key']),
                array(),
                null,
                true
            );
            $this->debug->debug('reCAPTCHA script enqueued');
        }
        
        wp_enqueue_script(
            'aoauth-public',
            AOAUTH_PLUGIN_URL . 'public/js/public-script.js',
            array('jquery'),
            AOAUTH_VERSION,
            true
        );
        
        $localize_data = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aoauth_public_nonce'),
            'spinner_url' => includes_url('images/spinner.gif'),
            'firefox' => strpos($_SERVER['HTTP_USER_AGENT'] ?? '', 'Firefox') !== false
        );
        
        if ($turnstile_enabled) {
            $localize_data['bot_protection'] = array(
                'type' => 'turnstile',
                'site_key' => $settings['turnstile_site_key'],
                'overlay_enabled' => !empty($settings['bot_overlay_enabled']),
                'overlay_message' => $settings['bot_overlay_message'] ?? 'Verifying secure sign-in...'
            );
        } elseif ($recaptcha_enabled) {
            $localize_data['bot_protection'] = array(
                'type' => 'recaptcha',
                'site_key' => $settings['recaptcha_site_key'],
                'score_threshold' => floatval($settings['recaptcha_score_threshold'] ?? 0.5),
                'overlay_enabled' => !empty($settings['bot_overlay_enabled']),
                'overlay_message' => $settings['bot_overlay_message'] ?? 'Verifying secure sign-in...'
            );
        } else {
            $localize_data['bot_protection'] = array(
                'type' => 'none'
            );
        }
        
        wp_localize_script('aoauth-public', 'aoauth_public', $localize_data);
        
        $this->debug->debug('Login assets enqueued', array(
            'theme' => $theme,
            'bot_protection' => $localize_data['bot_protection']['type']
        ));
        $this->debug->log_end('AOAUTH_Core::enqueue_login_assets');
    }
    
    public function render_login_buttons() {
        $this->debug->log_start('AOAUTH_Core::render_login_buttons');
        
        $settings = array_merge(self::get_default_settings(), get_option('aoauth_settings', array()));
        if (empty($settings['enable_login_buttons'])) {
            $this->debug->debug('Login buttons disabled, skipping render');
            $this->debug->log_end('AOAUTH_Core::render_login_buttons');
            return;
        }
        
        $applications = get_option('aoauth_applications', array());
        $enabled_apps = array_filter($applications, function($app) {
            return !empty($app['enabled']);
        });
        
        if (empty($enabled_apps)) {
            $this->debug->debug('No enabled providers found');
            $this->debug->log_end('AOAUTH_Core::render_login_buttons');
            return;
        }
        
        $theme = isset($settings['login_button_theme']) ? $settings['login_button_theme'] : 'modern';
        $layout = isset($settings['login_button_layout']) ? $settings['login_button_layout'] : 'vertical';
        $redirect_to = isset($_REQUEST['redirect_to']) ? sanitize_url($_REQUEST['redirect_to']) : admin_url();
        
        echo '<div class="aoauth-login-buttons aoauth-theme-' . esc_attr($theme) . ' aoauth-layout-' . esc_attr($layout) . '">';
        echo '<div class="aoauth-login-divider"><span>' . esc_html__('Or login with', 'aoauth-client-sso') . '</span></div>';
        echo '<div class="aoauth-providers-grid">';
        
        foreach ($enabled_apps as $app_id => $app) {
            $provider_name = esc_html($app['provider_name']);
            $app_name = esc_html($app['app_name']);
            
            $icon_url = AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $app['provider_name'] . '.png';
            
            $login_url = add_query_arg(array(
                'oauth' => 'login',
                'provider' => $app_id,
                'redirect_to' => $redirect_to,
                '_wpnonce' => wp_create_nonce('aoauth_login_' . $app_id)
            ), wp_login_url());
            
            echo '<a href="' . esc_url($login_url) . '" class="aoauth-button aoauth-provider-' . esc_attr($app_id) . '">';
            echo '<span class="aoauth-button-icon"><img src="' . esc_url($icon_url) . '" alt="' . $provider_name . '" onerror="this.src=\'' . esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png') . '\'"></span>';
            echo '<span class="aoauth-button-text">' . esc_html($app_name) . '</span>';
            echo '</a>';
        }
        
        echo '</div>';
        echo '</div>';
        
        $this->debug->debug('Login buttons rendered', array(
            'theme' => $theme,
            'layout' => $layout,
            'provider_count' => count($enabled_apps)
        ));
        $this->debug->log_end('AOAUTH_Core::render_login_buttons');
    }
    
    public function schedule_retention_cron() {
        $settings = get_option('aoauth_settings', array());
        
        if (empty($settings['enable_logs'])) {
            wp_clear_scheduled_hook('aoauth_retention_cron');
            $this->debug->debug('Logs retention cron cleared (logs disabled)');
            return;
        }
        
        if (!wp_next_scheduled('aoauth_retention_cron')) {
            wp_schedule_event(time(), 'daily', 'aoauth_retention_cron');
            $this->debug->info('Logs retention cron scheduled');
        }
        
        add_action('aoauth_retention_cron', array($this, 'run_retention_cron'));
    }
    
    public function run_retention_cron() {
        $this->debug->log_start('AOAUTH_Core::run_retention_cron');
        
        $settings = get_option('aoauth_settings', array());
        
        if (!empty($settings['enable_logs'])) {
            $this->logger->delete_old_logs();
            $this->debug->info('Logs retention cron executed');
        }
        
        $this->debug->log_end('AOAUTH_Core::run_retention_cron');
    }
    
    public function get_provider_icon($provider_name) {
        $provider_key = strtolower($provider_name);
        return AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $provider_key . '.png';
    }
    
    public function get_security() {
        return $this->security;
    }
    
    public function get_logger() {
        return $this->logger;
    }
    
    public function get_providers_manager() {
        return $this->providers_manager;
    }
}
