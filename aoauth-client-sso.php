<?php
/**
 * Plugin Name: aOAUTH Client SSO
 * Plugin URI: https://awhadi.online
 * Description: WordPress OAuth/OIDC Single Sign-On for Google, Microsoft, GitHub, Keycloak, Auth0, Okta, WordPress, and custom identity providers.
 * Version: 2.6.6
 * Author: Awhadi
 * Author URI: https://awhadi.online
 * License: GPL v2 or later
 * Text Domain: aoauth-client-sso
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit;
}

define('AOAUTH_VERSION', '2.6.6');
define('AOAUTH_PLUGIN_FILE', __FILE__);
define('AOAUTH_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AOAUTH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('AOAUTH_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('AOAUTH_MINIMUM_WP_VERSION', '5.8');
define('AOAUTH_MINIMUM_PHP_VERSION', '7.4');

require_once AOAUTH_PLUGIN_DIR . 'includes/class-core.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-security.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-logger.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-debug.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-oauth-client.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-sso-handler.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-user-mapping.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-user-manager.php';
require_once AOAUTH_PLUGIN_DIR . 'includes/class-providers-manager.php';
require_once AOAUTH_PLUGIN_DIR . 'admin/class-admin.php';

register_activation_hook(__FILE__, 'aoauth_activate');
register_deactivation_hook(__FILE__, 'aoauth_deactivate');

function aoauth_activate() {
    if (version_compare(PHP_VERSION, AOAUTH_MINIMUM_PHP_VERSION, '<')) {
        deactivate_plugins(AOAUTH_PLUGIN_BASENAME);
        wp_die(sprintf(
            /* translators: %s: minimum required PHP version. */
            esc_html__('aOAUTH Client SSO requires PHP version %s or higher.', 'aoauth-client-sso'),
            esc_html(AOAUTH_MINIMUM_PHP_VERSION)
        ));
    }
    
    global $wp_version;
    if (version_compare($wp_version, AOAUTH_MINIMUM_WP_VERSION, '<')) {
        deactivate_plugins(AOAUTH_PLUGIN_BASENAME);
        wp_die(sprintf(
            /* translators: %s: minimum required WordPress version. */
            esc_html__('aOAUTH Client SSO requires WordPress version %s or higher.', 'aoauth-client-sso'),
            esc_html(AOAUTH_MINIMUM_WP_VERSION)
        ));
    }
    
    $core = aoauth_core();
    $core->activate();
    
    set_transient('aoauth_activation_redirect', true, 30);
}

function aoauth_deactivate() {
    $core = aoauth_core();
    $core->deactivate();
}

function aoauth_core() {
    return AOAUTH_Core::get_instance();
}

function aoauth_init() {
    $core = aoauth_core();
    $core->init();
    
    if (is_admin()) {
        $admin = new AOAUTH_Admin();
        $admin->init();
    }
    
    $sso_handler = new AOAUTH_SSO_Handler();
    $sso_handler->init();
    
    $user_manager = new AOAUTH_User_Manager();
    $user_manager->init();
}
add_action('plugins_loaded', 'aoauth_init');

function aoauth_redirect_after_activation() {
    if (get_transient('aoauth_activation_redirect')) {
        delete_transient('aoauth_activation_redirect');
        $applications = get_option('aoauth_applications', array());
        $has_configured_provider = is_array($applications) && !empty($applications);
        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- WordPress sets activate-multi during plugin activation redirects.
        if (!$has_configured_provider && !isset($_GET['activate-multi'])) {
            wp_safe_redirect(admin_url('admin.php?page=aoauth-wizard'));
            exit;
        }
    }
}
add_action('admin_init', 'aoauth_redirect_after_activation');
