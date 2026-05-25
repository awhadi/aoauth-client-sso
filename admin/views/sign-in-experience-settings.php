<?php if (!defined('ABSPATH')) exit;
$current_theme = $settings['login_button_theme'] ?? 'modern';
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Sign-In Experience', 'aoauth-client-sso'); ?></h3>

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

            <div class="aoauth-setting-row-full">
                <div class="aoauth-setting-label-full">
                    <label><?php esc_html_e('Single Sign-On Theme', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Controls the login buttons, account-linking page, and verification overlay styling.', 'aoauth-client-sso'); ?></p>
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

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="linking_page_title"><?php esc_html_e('Linking Page Title', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Shown above the password confirmation form when users link an existing account.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <input type="text" id="linking_page_title" name="linking_page_title" class="aoauth-form-control" value="<?php echo esc_attr($settings['linking_page_title'] ?? 'Link Your Account'); ?>">
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="bot_overlay_variant"><?php esc_html_e('Verification Overlay Style', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Applies when full-screen verification overlay is enabled in Security.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <select id="bot_overlay_variant" name="bot_overlay_variant" class="aoauth-form-control">
                        <option value="spotlight" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'spotlight'); ?>><?php esc_html_e('Spotlight', 'aoauth-client-sso'); ?></option>
                        <option value="panel" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'panel'); ?>><?php esc_html_e('Full Panel', 'aoauth-client-sso'); ?></option>
                        <option value="minimal" <?php selected($settings['bot_overlay_variant'] ?? 'spotlight', 'minimal'); ?>><?php esc_html_e('Minimal', 'aoauth-client-sso'); ?></option>
                    </select>
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="bot_overlay_color"><?php esc_html_e('Verification Overlay Color', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Base color for the full-screen overlay.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <input type="color" id="bot_overlay_color" name="bot_overlay_color" class="aoauth-form-control" value="<?php echo esc_attr($settings['bot_overlay_color'] ?? '#0f172a'); ?>">
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="bot_overlay_message_style"><?php esc_html_e('Overlay Message Style', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Controls message emphasis while verification runs.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <select id="bot_overlay_message_style" name="bot_overlay_message_style" class="aoauth-form-control">
                        <option value="standard" <?php selected($settings['bot_overlay_message_style'] ?? 'standard', 'standard'); ?>><?php esc_html_e('Standard', 'aoauth-client-sso'); ?></option>
                        <option value="quiet" <?php selected($settings['bot_overlay_message_style'] ?? 'standard', 'quiet'); ?>><?php esc_html_e('Quiet', 'aoauth-client-sso'); ?></option>
                        <option value="strong" <?php selected($settings['bot_overlay_message_style'] ?? 'standard', 'strong'); ?>><?php esc_html_e('Strong', 'aoauth-client-sso'); ?></option>
                    </select>
                </div>
            </div>

            <div class="aoauth-linking-preview-wrap" data-preview-theme="<?php echo esc_attr($current_theme); ?>">
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
                </div>
            </div>
        </div>

        <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save Sign-In Experience', 'aoauth-client-sso'); ?></button>
    </form>
</div>
