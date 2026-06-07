<?php if (!defined('ABSPATH')) exit;
$current_page = isset($_GET['page']) ? $_GET['page'] : '';
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
            <a href="https://awhadi.com/aoauth-client-sso" target="_blank" class="aoauth-feature-btn"><?php esc_html_e('Feature Details', 'aoauth-client-sso'); ?></a>
        </div>
    </div>
    
    <?php $this->render_admin_tabs($current_page); ?>
    
    <div class="aoauth-admin-content">
        <?php if (!empty($applications)): ?>
<div class="aoauth-configure-header">
    <a href="<?php echo admin_url('admin.php?page=aoauth-wizard'); ?>" class="aoauth-admin-button aoauth-admin-button-primary aoauth-add-app-btn">
        <span class="dashicons dashicons-plus"></span>
        <?php esc_html_e('Add New Provider', 'aoauth-client-sso'); ?>
    </a>
</div>
<?php endif; ?>
        
        <div class="aoauth-applications-list">
            <?php if (empty($applications)): ?>
                <div class="aoauth-empty-state">
                    <div class="aoauth-empty-icon">
                        <span class="dashicons dashicons-admin-network"></span>
                    </div>
                    <h3><?php esc_html_e('No Providers Connected', 'aoauth-client-sso'); ?></h3>
                    <p><?php esc_html_e('Add your first OAuth 2.0 or OpenID Connect provider to enable Single Sign-On.', 'aoauth-client-sso'); ?></p>
                    <a href="<?php echo admin_url('admin.php?page=aoauth-wizard'); ?>" class="aoauth-admin-button aoauth-admin-button-primary aoauth-add-app-btn">
                        <?php esc_html_e('Add New Provider', 'aoauth-client-sso'); ?>
                    </a>
                </div>
            <?php else: ?>
                <?php foreach ($applications as $app_id => $app): ?>
                    <div class="aoauth-app-card" data-app-id="<?php echo esc_attr($app_id); ?>">
                        <div class="aoauth-app-info">
                            <div class="aoauth-app-icon">
                                <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $app['provider_name'] . '.png'); ?>" alt="<?php echo esc_attr($app['provider_name']); ?>">
                            </div>
                            <div class="aoauth-app-details">
                                <h4 class="aoauth-app-name"><?php echo esc_html($app['app_name']); ?></h4>
                                <span class="aoauth-app-protocol"><?php echo esc_html(strtoupper($app['provider_name'])); ?></span>
                                <?php if (!empty($app['draft'])): ?>
                                    <span class="aoauth-app-status draft"><?php esc_html_e('Draft', 'aoauth-client-sso'); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="aoauth-app-actions">
                            <?php if (!empty($app['draft'])): ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aoauth-wizard&edit=' . $app_id)); ?>" class="aoauth-admin-button aoauth-admin-button-primary aoauth-continue-setup-btn">
                                    <?php esc_html_e('Continue Setup', 'aoauth-client-sso'); ?>
                                </a>
                            <?php else: ?>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=aoauth-wizard&edit=' . $app_id)); ?>" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-edit-app-btn">
                                    <span class="dashicons dashicons-edit"></span>
                                    <?php esc_html_e('Edit', 'aoauth-client-sso'); ?>
                                </a>
                                <label class="aoauth-toggle">
                                    <input type="checkbox" class="aoauth-toggle-provider" data-app-id="<?php echo esc_attr($app_id); ?>" <?php checked(!empty($app['enabled']), true); ?>>
                                    <span class="aoauth-toggle-slider"></span>
                                </label>
                            <?php endif; ?>
                            <button type="button" class="aoauth-admin-button aoauth-admin-button-icon aoauth-admin-button-danger-ghost aoauth-delete-app-btn" data-app-id="<?php echo esc_attr($app_id); ?>">
                                <span class="dashicons dashicons-trash"></span>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
