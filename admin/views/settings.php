<?php if (!defined('ABSPATH')) exit;
$current_page = isset($_GET['page']) ? sanitize_key($_GET['page']) : '';
$settings_view = isset($settings_view) ? $settings_view : 'sign-in-experience';
$view_files = array(
    'sign-in-experience' => 'sign-in-experience.php',
    'user-management' => 'user-management.php',
    'security' => 'security.php',
    'tools' => 'tools.php',
);
?>
<div class="aoauth-admin-wrap">
    <div class="aoauth-admin-header">
        <div class="aoauth-header-brand">
            <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/logo.png'); ?>" alt="aOAUTH Client SSO" class="aoauth-header-logo">
            <div>
                <h1 class="aoauth-header-title"><?php esc_html_e('aOAUTH Client SSO', 'aoauth-client-sso'); ?></h1>
                <p class="aoauth-header-tagline"><?php esc_html_e('Secure OAuth 2.0 / OpenID Connect Single Sign-On', 'aoauth-client-sso'); ?></p>
            </div>
        </div>
        <div class="aoauth-header-actions">
            <span class="aoauth-version">v<?php echo esc_html(AOAUTH_VERSION); ?></span>
            <a href="https://plugins.awhadi.com/aoauth-client-sso" target="_blank" class="aoauth-feature-btn"><?php esc_html_e('Feature Details', 'aoauth-client-sso'); ?></a>
        </div>
    </div>

    <?php include AOAUTH_PLUGIN_DIR . 'admin/views/tabs.php'; ?>

    <div class="aoauth-admin-content <?php echo $settings_view === 'tools' ? 'two-column-layout' : ''; ?>">
        <?php include AOAUTH_PLUGIN_DIR . 'admin/views/' . $view_files[$settings_view]; ?>
    </div>
</div>
