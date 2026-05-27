<?php if (!defined('ABSPATH')) exit;
$current_theme = $settings['login_button_theme'] ?? 'modern';
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
                        <label for="enable_brand_badge"><?php esc_html_e('Show Brand Badge', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Display the aOAUTH badge only for users who signed in with SSO.', 'aoauth-client-sso'); ?></p>
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
                                    <option value="vertical" <?php selected($settings['login_button_layout'] ?? 'vertical', 'vertical'); ?>><?php esc_html_e('Vertical', 'aoauth-client-sso'); ?></option>
                                    <option value="horizontal" <?php selected($settings['login_button_layout'] ?? 'vertical', 'horizontal'); ?>><?php esc_html_e('Horizontal', 'aoauth-client-sso'); ?></option>
                                    <option value="grid" <?php selected($settings['login_button_layout'] ?? 'vertical', 'grid'); ?>><?php esc_html_e('Grid', 'aoauth-client-sso'); ?></option>
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
                            <h4><?php esc_html_e('Verification Overlay', 'aoauth-client-sso'); ?></h4>
                            <p><?php esc_html_e('Used when full-screen verification overlay is enabled in Security.', 'aoauth-client-sso'); ?></p>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="bot_overlay_variant"><?php esc_html_e('Verification Overlay Style', 'aoauth-client-sso'); ?></label>
                            </div>
                            <div class="aoauth-setting-control">
                                <select id="bot_overlay_variant" name="bot_overlay_variant" class="aoauth-form-control">
                                    <option value="spotlight" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'spotlight'); ?>><?php esc_html_e('Spotlight', 'aoauth-client-sso'); ?></option>
                                    <option value="paper-plane" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'paper-plane'); ?>><?php esc_html_e('Paper Plane', 'aoauth-client-sso'); ?></option>
                                    <option value="glass-shield" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'glass-shield'); ?>><?php esc_html_e('Glass Shield', 'aoauth-client-sso'); ?></option>
                                    <option value="aurora" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'aurora'); ?>><?php esc_html_e('Aurora', 'aoauth-client-sso'); ?></option>
                                    <option value="panel" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'panel'); ?>><?php esc_html_e('Full Panel', 'aoauth-client-sso'); ?></option>
                                    <option value="minimal" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'minimal'); ?>><?php esc_html_e('Minimal', 'aoauth-client-sso'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="bot_overlay_branding_enabled"><?php esc_html_e('Show Verification Branding', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Show the verification provider and aOAUTH powered-by badge in the overlay corner.', 'aoauth-client-sso'); ?></p>
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
                    <div class="aoauth-linking-preview-wrap" data-preview-theme="<?php echo esc_attr($current_theme); ?>" data-overlay-variant="<?php echo esc_attr($settings['bot_overlay_variant'] ?? 'spotlight'); ?>">
                        <div class="aoauth-linking-preview-screen">
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
                                    <span><?php esc_html_e('Cloudflare Turnstile', 'aoauth-client-sso'); ?></span>
                                    <span><?php esc_html_e('Powered by aOAUTH Client SSO', 'aoauth-client-sso'); ?></span>
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
