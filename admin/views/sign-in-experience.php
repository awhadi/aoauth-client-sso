<?php if (!defined('ABSPATH')) exit;
$current_theme = $settings['login_button_theme'] ?? 'modern';
$current_overlay_variant = $settings['bot_overlay_variant'] ?? 'spotlight';
if (!in_array($current_overlay_variant, array('spotlight', 'constellation', 'minimal'), true)) {
    $current_overlay_variant = 'spotlight';
}
$current_layout = $settings['login_button_layout'] ?? 'vertical';
if ($current_layout === 'horizontal') {
    $current_layout = 'wrap-centered';
} elseif ($current_layout === 'grid') {
    $current_layout = 'two-column';
}
if (!in_array($current_layout, array('vertical', 'wrap-centered', 'two-column', 'compact'), true)) {
    $current_layout = 'vertical';
}
$current_position = ($settings['login_button_position'] ?? 'below_form') === 'inside_form' ? 'inside_form' : 'below_form';
$preview_applications = array_filter(get_option('aoauth_applications', array()), function($application) {
    return !empty($application['enabled']);
});
if (empty($preview_applications)) {
    $preview_applications = array(
        'google' => array('provider_name' => 'google', 'app_name' => 'Google'),
        'microsoft' => array('provider_name' => 'microsoft', 'app_name' => 'Microsoft'),
        'github' => array('provider_name' => 'github', 'app_name' => 'GitHub'),
    );
}
$preview_applications = array_slice($preview_applications, 0, 4, true);
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Sign-In Experience', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('Login Page Display', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('Controls what visitors see on the WordPress login page.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="enable_login_buttons"><?php esc_html_e('Enable Login Page SSO Buttons', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Display SSO login buttons on the WordPress login page for enabled providers.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="enable_login_buttons" value="0">
                            <input type="checkbox" id="enable_login_buttons" name="enable_login_buttons" value="1" <?php checked(!empty($settings['enable_login_buttons'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="enable_provider_auto_login"><?php esc_html_e('Auto-login users already authenticated with SSO provider', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('When visitors reach wp-login.php, automatically try the first enabled SSO provider once. If their browser already has a provider session, WordPress signs them in without clicking a button.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="enable_provider_auto_login" value="0">
                            <input type="checkbox" id="enable_provider_auto_login" name="enable_provider_auto_login" value="1" <?php checked(!empty($settings['enable_provider_auto_login'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="enable_brand_badge"><?php esc_html_e('Show Brand Badge', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Show the aOAUTH badge on front-end pages for users who signed in with SSO.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="enable_brand_badge" value="0">
                            <input type="checkbox" id="enable_brand_badge" name="enable_brand_badge" value="1" <?php checked(!empty($settings['enable_brand_badge'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="aoauth-signin-layout">
                <div class="aoauth-signin-settings">
                    <div class="aoauth-setting-group">
                        <div class="aoauth-setting-group-header">
                            <h4><?php esc_html_e('Button Appearance', 'aoauth-client-sso'); ?></h4>
                            <p><?php esc_html_e('The theme applies to login buttons, account-linking pages, and verification overlays.', 'aoauth-client-sso'); ?></p>
                        </div>

                        <div class="aoauth-setting-row-full">
                            <div class="aoauth-setting-label-full">
                                <label><?php esc_html_e('Single Sign-On Theme', 'aoauth-client-sso'); ?></label>
                            </div>
                            <div class="aoauth-setting-control-full">
                                <div class="aoauth-theme-carousel" data-current-theme="<?php echo esc_attr($current_theme); ?>">
                                    <button type="button" class="theme-nav-btn theme-prev-btn" disabled>&lsaquo;</button>
                                    <div class="theme-carousel-container">
                                        <div class="theme-carousel-wrapper">
                                            <?php foreach ($available_themes as $index => $theme): ?>
                                                <label class="aoauth-theme-card <?php echo $current_theme === $theme['id'] ? 'active' : ''; ?>" data-theme="<?php echo esc_attr($theme['id']); ?>" data-index="<?php echo esc_attr($index); ?>">
                                                    <input type="radio" name="login_button_theme" value="<?php echo esc_attr($theme['id']); ?>" <?php checked($current_theme, $theme['id']); ?> class="aoauth-hidden-field">
                                                    <div class="theme-card-preview">
                                                        <div class="theme-preview-button aoauth-button">
                                                            <span class="aoauth-button-icon"><img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/google.png'); ?>" alt="Google"></span>
                                                            <span class="aoauth-button-text"><?php esc_html_e('Sign in', 'aoauth-client-sso'); ?></span>
                                                        </div>
                                                    </div>
                                                    <span class="theme-card-name"><?php echo esc_html($theme['name']); ?></span>
                                                    <span class="theme-active-badge">✓</span>
                                                </label>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <button type="button" class="theme-nav-btn theme-next-btn">&rsaquo;</button>
                                </div>
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="login_button_layout"><?php esc_html_e('Login Button Layout', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Choose how providers are arranged on the login screen.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <select id="login_button_layout" name="login_button_layout" class="aoauth-form-control">
                                    <option value="vertical" <?php selected($current_layout, 'vertical'); ?>><?php esc_html_e('Full Width Stack', 'aoauth-client-sso'); ?></option>
                                    <option value="wrap-centered" <?php selected($current_layout, 'wrap-centered'); ?>><?php esc_html_e('Wrap Centered', 'aoauth-client-sso'); ?></option>
                                    <option value="two-column" <?php selected($current_layout, 'two-column'); ?>><?php esc_html_e('Two Columns', 'aoauth-client-sso'); ?></option>
                                    <option value="compact" <?php selected($current_layout, 'compact'); ?>><?php esc_html_e('Compact Row', 'aoauth-client-sso'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="login_button_position"><?php esc_html_e('SSO Button Position', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Place SSO buttons below the WordPress login button to reduce accidental provider clicks.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <select id="login_button_position" name="login_button_position" class="aoauth-form-control">
                                    <option value="below_form" <?php selected($settings['login_button_position'] ?? 'below_form', 'below_form'); ?>><?php esc_html_e('Below Login Form', 'aoauth-client-sso'); ?></option>
                                    <option value="inside_form" <?php selected($settings['login_button_position'] ?? 'below_form', 'inside_form'); ?>><?php esc_html_e('Inside Login Form', 'aoauth-client-sso'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="aoauth-setting-group">
                        <div class="aoauth-setting-group-header">
                            <h4><?php esc_html_e('Account Linking', 'aoauth-client-sso'); ?></h4>
                            <p><?php esc_html_e('Front-end pages can show linking controls with the shortcode [aoauth_link_account].', 'aoauth-client-sso'); ?></p>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="linking_page_title"><?php esc_html_e('Linking Page Title', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Shown above the password confirmation form when users link an existing account.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <input type="text" id="linking_page_title" name="linking_page_title" class="aoauth-form-control" value="<?php echo esc_attr($settings['linking_page_title'] ?? 'Link Your Account'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="aoauth-setting-group">
                        <div class="aoauth-setting-group-header">
                            <h4><?php esc_html_e('Bot Verification Overlay', 'aoauth-client-sso'); ?></h4>
                            <p><?php esc_html_e('Used when full-screen verification overlay is enabled in Security.', 'aoauth-client-sso'); ?></p>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="bot_overlay_variant"><?php esc_html_e('Bot Verification Overlay Style', 'aoauth-client-sso'); ?></label>
                            </div>
                            <div class="aoauth-setting-control">
                                <select id="bot_overlay_variant" name="bot_overlay_variant" class="aoauth-form-control">
                                    <option value="spotlight" <?php selected($current_overlay_variant, 'spotlight'); ?>><?php esc_html_e('Spotlight', 'aoauth-client-sso'); ?></option>
                                    <option value="constellation" <?php selected($current_overlay_variant, 'constellation'); ?>><?php esc_html_e('Constellation', 'aoauth-client-sso'); ?></option>
                                    <option value="minimal" <?php selected($current_overlay_variant, 'minimal'); ?>><?php esc_html_e('Minimal', 'aoauth-client-sso'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="bot_overlay_opacity"><?php esc_html_e('Overlay Opacity', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Controls the strength of the bot verification screen cover.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <input type="range" id="bot_overlay_opacity" name="bot_overlay_opacity" class="aoauth-range-control" value="<?php echo esc_attr($settings['bot_overlay_opacity'] ?? 86); ?>" min="35" max="96" step="1">
                                <span class="aoauth-range-value" data-range-value="bot_overlay_opacity"><?php echo esc_html($settings['bot_overlay_opacity'] ?? 86); ?>%</span>
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="bot_overlay_branding_enabled"><?php esc_html_e('Show Verification Branding', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Show a compact verification provider and aOAUTH Client SSO trust mark in the overlay corner.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <label class="aoauth-toggle">
                                    <input type="hidden" name="bot_overlay_branding_enabled" value="0">
                                    <input type="checkbox" id="bot_overlay_branding_enabled" name="bot_overlay_branding_enabled" value="1" <?php checked(!empty($settings['bot_overlay_branding_enabled'])); ?>>
                                    <span class="aoauth-toggle-slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="aoauth-signin-preview">
                    <div class="aoauth-signin-preview-heading">
                        <h4><?php esc_html_e('Theme Preview', 'aoauth-client-sso'); ?></h4>
                        <p><?php esc_html_e('Shows the WordPress login buttons, account-linking prompt, and verification overlay using the current Sign-In Experience settings.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-linking-preview-wrap" data-preview-theme="<?php echo esc_attr($current_theme); ?>" data-preview-layout="<?php echo esc_attr($current_layout); ?>" data-preview-position="<?php echo esc_attr($current_position); ?>" data-overlay-variant="<?php echo esc_attr($current_overlay_variant); ?>" data-overlay-opacity="<?php echo esc_attr($settings['bot_overlay_opacity'] ?? 86); ?>">
                        <div class="aoauth-linking-preview-screen">
                            <div class="aoauth-login-preview-card">
                                <span class="aoauth-login-preview-title"><?php esc_html_e('wp-login.php', 'aoauth-client-sso'); ?></span>
                                <span class="aoauth-login-preview-input"></span>
                                <span class="aoauth-login-preview-input aoauth-login-preview-input-short"></span>
                                <span class="aoauth-login-preview-submit"><?php esc_html_e('Log In', 'aoauth-client-sso'); ?></span>
                                <div class="aoauth-login-preview-divider"><span><?php esc_html_e('Or login with', 'aoauth-client-sso'); ?></span></div>
                                <div class="aoauth-preview-provider-buttons">
                                    <?php foreach ($preview_applications as $app_id => $app): ?>
                                        <?php $provider_name = sanitize_key($app['provider_name'] ?? $app_id); ?>
                                        <span class="aoauth-preview-login-button">
                                            <span class="aoauth-preview-login-icon"><img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $provider_name . '.png'); ?>" alt="<?php echo esc_attr($app['app_name'] ?? ucfirst($provider_name)); ?>" data-fallback-src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png'); ?>"></span>
                                            <span class="aoauth-preview-login-text"><?php echo esc_html($app['app_name'] ?? ucfirst($provider_name)); ?></span>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="aoauth-linking-preview-card">
                                <div class="aoauth-linking-preview-icon">
                                    <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/google.png'); ?>" alt="Google">
                                </div>
                                <strong class="aoauth-linking-preview-title"><?php echo esc_html($settings['linking_page_title'] ?? 'Link Your Account'); ?></strong>
                                <span class="aoauth-linking-preview-copy"><?php esc_html_e('Confirm your WordPress password to link Google for secure SSO login.', 'aoauth-client-sso'); ?></span>
                                <span class="aoauth-linking-preview-input"></span>
                                <span class="aoauth-linking-preview-button"><?php esc_html_e('Confirm & Link Account', 'aoauth-client-sso'); ?></span>
                            </div>
                            <div class="aoauth-overlay-preview">
                                <div class="aoauth-overlay-preview-motion"></div>
                                <div class="aoauth-overlay-preview-panel">
                                    <span class="aoauth-overlay-preview-ring"></span>
                                    <span class="aoauth-overlay-preview-copy"><?php esc_html_e('Verifying secure sign-in...', 'aoauth-client-sso'); ?></span>
                                </div>
                                <div class="aoauth-overlay-preview-brand">
                                    <span><?php esc_html_e('Verified by Cloudflare Turnstile', 'aoauth-client-sso'); ?></span>
                                    <span><?php esc_html_e('Protected with aOAUTH Client SSO', 'aoauth-client-sso'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save Sign-In Experience', 'aoauth-client-sso'); ?></button>
    </form>
</div>
