<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This template receives local view variables from the admin renderer.
if (!defined('ABSPATH')) {
    exit;
}
$edit_mode = false;
// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only wizard edit selector; saving still requires nonce verification.
$edit_app_id = isset($_GET['edit']) ? sanitize_key(wp_unslash($_GET['edit'])) : '';
$edit_app_data = array();

if (!empty($edit_app_id) && isset($applications[$edit_app_id])) {
    $edit_mode = true;
    $edit_app_data = $applications[$edit_app_id];
    
    if (!empty($edit_app_data['client_secret'])) {
        $edit_app_data['client_secret'] = aoauth_core()->get_security()->decrypt($edit_app_data['client_secret']);
    }
}
?>
<div class="aoauth-wizard-container">
    <div class="aoauth-wizard-header">
        <div class="aoauth-wizard-logo">
            <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/logo.png'); ?>" alt="aOAUTH Client SSO">
        </div>
        <div class="aoauth-wizard-actions">
            <?php if ($edit_mode): ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=aoauth-providers')); ?>" class="aoauth-wizard-skip aoauth-admin-button aoauth-admin-button-secondary">
                    <span class="dashicons dashicons-arrow-left-alt"></span>
                    <?php esc_html_e('Back to Providers', 'aoauth-client-sso'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo esc_url(admin_url('admin.php?page=aoauth-providers')); ?>" class="aoauth-wizard-skip aoauth-admin-button aoauth-admin-button-secondary">
                    <?php esc_html_e('Skip Setup', 'aoauth-client-sso'); ?>
                </a>
            <?php endif; ?>
            
        </div>
    </div>
    
    <div class="aoauth-wizard-body">
        <div class="aoauth-wizard-progress">
            <div class="aoauth-progress-step active" data-step="1">
                <span class="aoauth-step-number">1</span>
                <span class="aoauth-step-label"><?php esc_html_e('Select Provider', 'aoauth-client-sso'); ?></span>
            </div>
            <div class="aoauth-progress-line"></div>
            <div class="aoauth-progress-step" data-step="2">
                <span class="aoauth-step-number">2</span>
                <span class="aoauth-step-label"><?php esc_html_e('Configuration', 'aoauth-client-sso'); ?></span>
            </div>
            <div class="aoauth-progress-line"></div>
            <div class="aoauth-progress-step" data-step="3">
                <span class="aoauth-step-number">3</span>
                <span class="aoauth-step-label"><?php esc_html_e('Test & Save', 'aoauth-client-sso'); ?></span>
            </div>
        </div>
        
        <div class="aoauth-wizard-content">
            <!-- Step 1: Select Provider -->
            <div class="aoauth-wizard-step active" data-step="1">
                <div class="aoauth-step-content">
                    <h2><?php esc_html_e('Select Your Identity Provider', 'aoauth-client-sso'); ?></h2>
                    <p class="aoauth-step-description">
                        <?php esc_html_e('Choose the OAuth 2.0 or OpenID Connect provider you want to connect for Single Sign-On.', 'aoauth-client-sso'); ?>
                    </p>
                    
                    <div class="aoauth-provider-search">
                        <input type="text" id="aoauth-provider-search" placeholder="<?php esc_attr_e('Search your provider...', 'aoauth-client-sso'); ?>" class="aoauth-search-input">
                        <span class="dashicons dashicons-search"></span>
                    </div>
                    
                    <div class="aoauth-providers-grid" id="aoauth-providers-grid">
                        <?php foreach ($providers as $provider): ?>
                            <div class="aoauth-provider-card" data-provider="<?php echo esc_attr($provider['name']); ?>" data-label="<?php echo esc_attr($provider['label']); ?>">
                                <div class="aoauth-provider-icon">
                                    <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $provider['name'] . '.png'); ?>" alt="<?php echo esc_attr($provider['label']); ?>">
                                </div>
                                <div class="aoauth-provider-name"><?php echo esc_html($provider['label']); ?></div>
                                <div class="aoauth-provider-desc"><?php echo esc_html($provider['description']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Step 2: Configuration -->
            <div class="aoauth-wizard-step" data-step="2">
                <div class="aoauth-step-content">
                    <h2><?php esc_html_e('Provider Configuration', 'aoauth-client-sso'); ?></h2>
                    <p class="aoauth-step-description">
                        <?php esc_html_e('Configure your OAuth 2.0 application settings. Make sure you have registered your application with the provider first.', 'aoauth-client-sso'); ?>
                    </p>
                    
                    <form id="aoauth-config-form" class="aoauth-config-form">
                        <input type="hidden" id="aoauth-provider-name" name="provider_name" value="">
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-app-name"><?php esc_html_e('Application Name', 'aoauth-client-sso'); ?></label>
                            <input type="text" id="aoauth-app-name" name="app_name" class="aoauth-form-control" required>
                            <p class="aoauth-form-help"><?php esc_html_e('This will be displayed on the SSO login button.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-callback-url"><?php esc_html_e('Callback / Redirect URL', 'aoauth-client-sso'); ?></label>
                            <div class="aoauth-input-group">
                                <input type="text" id="aoauth-callback-url" class="aoauth-form-control" readonly value="<?php echo esc_url(add_query_arg('oauth', 'callback', wp_login_url())); ?>">
                                <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-copy-btn" data-target="aoauth-callback-url">
                                    <span class="dashicons dashicons-clipboard"></span>
                                </button>
                            </div>
                            <p class="aoauth-form-help"><?php esc_html_e('Use this URL when registering your application with the provider.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-row">
                            <div class="aoauth-form-group">
                                <label for="aoauth-client-id"><?php esc_html_e('Client ID', 'aoauth-client-sso'); ?></label>
                                <input type="text" id="aoauth-client-id" name="client_id" class="aoauth-form-control" required>
                            </div>
                            
                            <div class="aoauth-form-group">
                                <label for="aoauth-client-secret"><?php esc_html_e('Client Secret', 'aoauth-client-sso'); ?></label>
                                <input type="password" id="aoauth-client-secret" name="client_secret" class="aoauth-form-control" required>
                            </div>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-scopes"><?php esc_html_e('Scopes', 'aoauth-client-sso'); ?></label>
                            <div class="aoauth-tags-input" id="aoauth-scopes-container">
                                <div class="aoauth-tags-list" id="aoauth-scopes-list"></div>
                                <input type="text" id="aoauth-scopes-input" class="aoauth-tags-input-field" placeholder="<?php esc_attr_e('Type and press Enter to add scopes', 'aoauth-client-sso'); ?>">
                            </div>
                            <p class="aoauth-form-help"><?php esc_html_e('Press Enter after each scope to add it. Common scopes: openid, email, profile', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-discovery-url"><?php esc_html_e('Provider Base URL / Discovery URL', 'aoauth-client-sso'); ?></label>
                            <div class="aoauth-input-group">
                                <input type="url" id="aoauth-discovery-url" name="discovery_url" class="aoauth-form-control" placeholder="https://your-provider.com">
                                <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-discover-btn" id="aoauth-discover-btn">
                                    <?php esc_html_e('Auto Discover', 'aoauth-client-sso'); ?>
                                </button>
                            </div>
                            <p class="aoauth-form-help"><?php esc_html_e('Enter the base URL of your OAuth server. Click Auto Discover to automatically fetch the endpoints.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-auth-endpoint"><?php esc_html_e('Authorization Endpoint', 'aoauth-client-sso'); ?></label>
                            <input type="url" id="aoauth-auth-endpoint" name="authorization_endpoint" class="aoauth-form-control" required>
                            <p class="aoauth-form-help"><?php esc_html_e('The URL where users are redirected to authorize your application.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-token-endpoint"><?php esc_html_e('Token Endpoint', 'aoauth-client-sso'); ?></label>
                            <input type="url" id="aoauth-token-endpoint" name="token_endpoint" class="aoauth-form-control" required>
                            <p class="aoauth-form-help"><?php esc_html_e('The URL where authorization codes are exchanged for access tokens.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label for="aoauth-userinfo-endpoint"><?php esc_html_e('UserInfo Endpoint (Optional)', 'aoauth-client-sso'); ?></label>
                            <input type="url" id="aoauth-userinfo-endpoint" name="userinfo_endpoint" class="aoauth-form-control">
                            <p class="aoauth-form-help"><?php esc_html_e('The URL where user profile information can be retrieved. May be optional if using ID tokens.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-form-group">
                            <label class="aoauth-checkbox-label">
                                <input type="checkbox" id="aoauth-enable-advanced-mapping" name="enable_advanced_mapping">
                                <span class="aoauth-checkbox-text"><?php esc_html_e('Enable Advanced Attribute & Role Mapping', 'aoauth-client-sso'); ?></span>
                            </label>
                        </div>
                        
                        <div class="aoauth-advanced-mapping aoauth-is-hidden" id="aoauth-advanced-mapping">
                            <h3><?php esc_html_e('Attribute Mapping', 'aoauth-client-sso'); ?></h3>
                            <p class="aoauth-form-help aoauth-form-help-spaced"><?php esc_html_e('Map provider attributes to WordPress user fields. Leave empty to use default mappings.', 'aoauth-client-sso'); ?></p>
                            
                            <div class="aoauth-mapping-fields">
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Username', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[username]" class="aoauth-form-control aoauth-mapping-field" placeholder="email" data-default="email">
                                </div>
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Email', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[email]" class="aoauth-form-control aoauth-mapping-field" placeholder="email" data-default="email">
                                </div>
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Display Name', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[display_name]" class="aoauth-form-control aoauth-mapping-field" placeholder="name" data-default="name">
                                </div>
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('First Name', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[first_name]" class="aoauth-form-control aoauth-mapping-field" placeholder="given_name" data-default="given_name">
                                </div>
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Last Name', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[last_name]" class="aoauth-form-control aoauth-mapping-field" placeholder="family_name" data-default="family_name">
                                </div>
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Subject Identifier', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="attribute_mapping[subject]" class="aoauth-form-control aoauth-mapping-field" placeholder="sub" data-default="sub">
                                </div>
                            </div>
                            
                            <h3 class="aoauth-role-mapping-title"><?php esc_html_e('Role Mapping (Optional)', 'aoauth-client-sso'); ?></h3>
                            <p class="aoauth-form-help aoauth-form-help-spaced"><?php esc_html_e('Map provider roles to WordPress roles. Leave empty if not needed.', 'aoauth-client-sso'); ?></p>
                            
                            <div class="aoauth-mapping-fields" id="aoauth-role-mapping-container">
                                <div class="aoauth-form-group">
                                    <label><?php esc_html_e('Role Attribute Path', 'aoauth-client-sso'); ?></label>
                                    <input type="text" name="role_mapping[attribute_path]" class="aoauth-form-control" placeholder="roles">
                                </div>
                                <div class="aoauth-form-group aoauth-full-width-field">
                                    <label><?php esc_html_e('Role Mappings', 'aoauth-client-sso'); ?></label>
                                    <p class="aoauth-form-help"><?php esc_html_e('Format: ProviderRole:WordPressRole (one per line)', 'aoauth-client-sso'); ?></p>
                                    <textarea name="role_mapping[rules]" class="aoauth-form-control" rows="4" placeholder="admin:administrator&#10;user:subscriber&#10;editor:editor"></textarea>
                                </div>
                            </div>
                            
                            <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-reset-mapping-btn"><?php esc_html_e('Reset to Default', 'aoauth-client-sso'); ?></button>
                        </div>
                        
                        <div class="aoauth-form-actions">
                            <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-save-draft-btn"><?php esc_html_e('Save Draft', 'aoauth-client-sso'); ?></button>
                            <button type="button" class="aoauth-admin-button aoauth-admin-button-primary aoauth-next-step-btn"><?php esc_html_e('Next', 'aoauth-client-sso'); ?></button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Step 3: Test & Save -->
            <div class="aoauth-wizard-step" data-step="3">
                <div class="aoauth-step-content">
                    <h2><?php esc_html_e('Test & Save Configuration', 'aoauth-client-sso'); ?></h2>
                    <p class="aoauth-step-description">
                        <?php esc_html_e('Review your configuration and test the connection before saving. The provider will only be enabled if the test passes.', 'aoauth-client-sso'); ?>
                    </p>
                    
                    <div class="aoauth-summary" id="aoauth-config-summary"></div>
                    
                    <div class="aoauth-form-group">
                        <label><?php esc_html_e('Client Credentials Transmission Method', 'aoauth-client-sso'); ?></label>
                        <label class="aoauth-radio-label">
                            <input type="radio" name="credentials_location" value="header" checked>
                            <span><?php esc_html_e('Send Client Credentials in Header (HTTP Basic Auth)', 'aoauth-client-sso'); ?></span>
                        </label>
                        <label class="aoauth-radio-label">
                            <input type="radio" name="credentials_location" value="body">
                            <span><?php esc_html_e('Send Client Credentials in Body (POST parameters)', 'aoauth-client-sso'); ?></span>
                        </label>
                        <p class="aoauth-form-help aoauth-form-help-top-spaced">
                            <?php esc_html_e('Header method (HTTP Basic Auth using Authorization header) is more secure and follows OAuth 2.0 best practices.', 'aoauth-client-sso'); ?>
                        </p>
                    </div>
                    
                    <!-- Test Connection Section -->
                    <div class="aoauth-test-section">
                        <div class="aoauth-test-header">
                            <h3><?php esc_html_e('Test Connection', 'aoauth-client-sso'); ?></h3>
                            <p><?php esc_html_e('Verify that your configuration works before saving.', 'aoauth-client-sso'); ?></p>
                        </div>
                        
                        <div class="aoauth-test-status aoauth-is-hidden" id="aoauth-test-status">
                            <div class="test-loading">
                                <span class="spinner is-active"></span>
                                <span><?php esc_html_e('Testing connection...', 'aoauth-client-sso'); ?></span>
                            </div>
                            <div class="test-result" id="aoauth-test-result"></div>
                        </div>
                        
                        <div class="aoauth-test-actions">
                            <button type="button" id="aoauth-test-connection-btn" class="aoauth-admin-button aoauth-admin-button-primary aoauth-test-btn">
                                <span class="dashicons dashicons-yes-alt"></span>
                                <?php esc_html_e('Test Connection', 'aoauth-client-sso'); ?>
                            </button>
                        </div>
                    </div>
                    
                    <div class="aoauth-form-actions">
                        <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-back-step-btn"><?php esc_html_e('Back', 'aoauth-client-sso'); ?></button>
                        <button type="button" id="aoauth-finish-btn" class="aoauth-admin-button aoauth-admin-button-primary aoauth-finish-btn" disabled>
                            <?php esc_html_e('Save & Enable Provider', 'aoauth-client-sso'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
