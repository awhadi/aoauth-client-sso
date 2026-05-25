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
            <a href="https://plugins.awhadi.com/aoauth-client-sso" target="_blank" class="aoauth-feature-btn"><?php esc_html_e('Feature Details', 'aoauth-client-sso'); ?></a>
        </div>
    </div>
    
    <div class="aoauth-admin-tabs">
        <a href="<?php echo admin_url('admin.php?page=aoauth-providers'); ?>" class="aoauth-tab <?php echo $current_page === 'aoauth-providers' ? 'active' : ''; ?>"><?php esc_html_e('Providers', 'aoauth-client-sso'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=aoauth-settings'); ?>" class="aoauth-tab <?php echo $current_page === 'aoauth-settings' ? 'active' : ''; ?>"><?php esc_html_e('Settings', 'aoauth-client-sso'); ?></a>
        <a href="<?php echo admin_url('admin.php?page=aoauth-logs'); ?>" class="aoauth-tab <?php echo $current_page === 'aoauth-logs' ? 'active' : ''; ?>"><?php esc_html_e('Logs', 'aoauth-client-sso'); ?></a>
    </div>
    
    <div class="aoauth-admin-content two-column-layout">
        <!-- LEFT COLUMN - Settings Form -->
        <div class="aoauth-settings-column">
            <form id="aoauth-settings-form" class="aoauth-settings-form">
                <!-- Login Page Settings -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('Login Page Settings', 'aoauth-client-sso'); ?></h3>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="enable_login_buttons"><?php esc_html_e('Enable Login Page SSO Buttons', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Display SSO login buttons on the WordPress login page for enabled providers.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="enable_login_buttons" name="enable_login_buttons" <?php checked(!empty($settings['enable_login_buttons']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row-full">
                        <div class="aoauth-setting-label-full">
                            <label><?php esc_html_e('Login Button Theme', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Choose a visual style for the SSO buttons on the login screen.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control-full">
                            <div class="aoauth-theme-carousel" data-current-theme="<?php echo esc_attr($settings['login_button_theme'] ?? 'modern'); ?>">
                                <button type="button" class="theme-nav-btn theme-prev-btn" disabled>‹</button>
                                <div class="theme-carousel-container">
                                    <div class="theme-carousel-wrapper">
                                        <?php 
                                        $current_theme = $settings['login_button_theme'] ?? 'modern';
                                        foreach ($available_themes as $index => $theme): 
                                        ?>
                                        <label class="aoauth-theme-card <?php echo $current_theme === $theme['id'] ? 'active' : ''; ?>" data-theme="<?php echo esc_attr($theme['id']); ?>" data-index="<?php echo $index; ?>">
                                            <input type="radio" name="login_button_theme" value="<?php echo esc_attr($theme['id']); ?>" <?php checked($current_theme, $theme['id']); ?> class="aoauth-hidden-field">
                                            <div class="theme-card-preview">
                                                <div class="theme-preview-button aoauth-button">
                                                    <span class="aoauth-button-icon">
                                                        <img src="<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/google.png'); ?>" alt="G" onerror="this.src='<?php echo esc_url(AOAUTH_PLUGIN_URL . 'admin/images/providers/generic.png'); ?>'">
                                                    </span>
                                                    <span class="aoauth-button-text">Sign in</span>
                                                </div>
                                            </div>
                                            <span class="theme-card-name"><?php echo esc_html($theme['name']); ?></span>
                                            <span class="theme-active-badge">✓</span>
                                        </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <button type="button" class="theme-nav-btn theme-next-btn">›</button>
                            </div>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="login_button_layout"><?php esc_html_e('Login Button Layout', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('How the SSO buttons should be arranged on the login screen.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <select id="login_button_layout" name="login_button_layout" class="aoauth-form-control">
                                <option value="vertical" <?php selected($settings['login_button_layout'] ?? 'vertical', 'vertical'); ?>><?php esc_html_e('Vertical (Stacked)', 'aoauth-client-sso'); ?></option>
                                <option value="horizontal" <?php selected($settings['login_button_layout'] ?? 'vertical', 'horizontal'); ?>><?php esc_html_e('Horizontal (Row, wrap)', 'aoauth-client-sso'); ?></option>
                                <option value="grid" <?php selected($settings['login_button_layout'] ?? 'vertical', 'grid'); ?>><?php esc_html_e('Grid (2-3 columns)', 'aoauth-client-sso'); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="linking_page_title"><?php esc_html_e('Linking Page Title', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Shown above the password confirmation form. This page always follows the selected login button theme.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="text" id="linking_page_title" name="linking_page_title" class="aoauth-form-control" value="<?php echo esc_attr($settings['linking_page_title'] ?? 'Link Your Account'); ?>">
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
                
                <!-- User Management -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('User Management', 'aoauth-client-sso'); ?></h3>

                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="auto_create_users"><?php esc_html_e('Auto-Create Users', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Automatically create WordPress user accounts for new SSO users after successful authentication.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="auto_create_users" name="auto_create_users" <?php checked(!empty($settings['auto_create_users']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row <?php echo empty($settings['auto_create_users']) ? 'aoauth-is-hidden' : ''; ?>" id="default-role-row">
                        <div class="aoauth-setting-label">
                            <label for="default_role"><?php esc_html_e('Default User Role', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Set the default WordPress role for newly created SSO users.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <select id="default_role" name="default_role" class="aoauth-form-control">
                                <?php foreach ($roles as $role_key => $role): ?>
                                    <option value="<?php echo esc_attr($role_key); ?>" <?php selected($settings['default_role'] ?? 'subscriber', $role_key); ?>>
                                        <?php echo esc_html($role['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="allow_account_linking"><?php esc_html_e('Allow Account Linking', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Allow users to link their SSO provider to an existing WordPress account.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="allow_account_linking" name="allow_account_linking" <?php checked(!empty($settings['allow_account_linking']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Account Linking Security Settings - Only visible when account linking is enabled -->
                    <div id="account-linking-security-row" class="<?php echo empty($settings['allow_account_linking']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="linking_max_attempts"><?php esc_html_e('Max Password Attempts', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('Number of failed password attempts before temporary lockout when linking accounts.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <input type="number" id="linking_max_attempts" name="linking_max_attempts" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_max_attempts'] ?? 5); ?>" min="1" max="20">
                            </div>
                        </div>
                        
                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="linking_lockout_minutes"><?php esc_html_e('Lockout Duration (minutes)', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('How long to block further attempts after max attempts reached.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <input type="number" id="linking_lockout_minutes" name="linking_lockout_minutes" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_lockout_minutes'] ?? 15); ?>" min="1" max="1440">
                            </div>
                        </div>

                        <div class="aoauth-setting-row">
                            <div class="aoauth-setting-label">
                                <label for="linking_login_ban_minutes"><?php esc_html_e('Full Login Ban Duration (minutes)', 'aoauth-client-sso'); ?></label>
                                <p class="aoauth-setting-help"><?php esc_html_e('After too many failed account-linking password attempts, block all WordPress login methods for this user for the selected time. Set to 0 to disable the full login ban.', 'aoauth-client-sso'); ?></p>
                            </div>
                            <div class="aoauth-setting-control">
                                <input type="number" id="linking_login_ban_minutes" name="linking_login_ban_minutes" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_login_ban_minutes'] ?? 15); ?>" min="0" max="1440">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Security Settings -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('Security Settings', 'aoauth-client-sso'); ?></h3>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="security_level"><?php esc_html_e('Security Level', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('High: Enforces PKCE, state validation, and nonce. Medium: Basic validation only.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <select id="security_level" name="security_level" class="aoauth-form-control">
                                <option value="high" <?php selected($settings['security_level'] ?? 'high', 'high'); ?>><?php esc_html_e('High (Recommended)', 'aoauth-client-sso'); ?></option>
                                <option value="medium" <?php selected($settings['security_level'] ?? 'high', 'medium'); ?>><?php esc_html_e('Medium', 'aoauth-client-sso'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="rate_limit_attempts"><?php esc_html_e('Rate Limit Attempts', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Maximum number of authentication attempts allowed within the rate limit window.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="number" id="rate_limit_attempts" name="rate_limit_attempts" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['rate_limit_attempts'] ?? 5); ?>" min="1" max="100">
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="rate_limit_window"><?php esc_html_e('Rate Limit Window (seconds)', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Time window in seconds for rate limiting authentication attempts.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="number" id="rate_limit_window" name="rate_limit_window" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['rate_limit_window'] ?? 300); ?>" min="60" max="3600">
                        </div>
                    </div>
                </div>

                <!-- Bot Protection - Mutual Exclusive Options -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('Bot Protection', 'aoauth-client-sso'); ?></h3>
                    <p class="aoauth-setting-help aoauth-setting-help-spaced"><?php esc_html_e('Choose ONE bot protection method. Only one can be active at a time.', 'aoauth-client-sso'); ?></p>
                    
                    <!-- Option 1: Cloudflare Turnstile -->
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="enable_turnstile"><?php esc_html_e('Cloudflare Turnstile', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Free, privacy-focused, invisible bot protection.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="enable_turnstile" name="enable_turnstile" <?php checked(!empty($settings['enable_turnstile']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="aoauth-setting-row turnstile-fields <?php echo empty($settings['enable_turnstile']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-label">
                            <label for="turnstile_site_key"><?php esc_html_e('Turnstile Site Key', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Get from Cloudflare Dashboard → Turnstile.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="text" id="turnstile_site_key" name="turnstile_site_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['turnstile_site_key'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="aoauth-setting-row turnstile-fields <?php echo empty($settings['enable_turnstile']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-label">
                            <label for="turnstile_secret_key"><?php esc_html_e('Turnstile Secret Key', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Keep this secret. Used to verify the challenge.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="password" id="turnstile_secret_key" name="turnstile_secret_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['turnstile_secret_key'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="aoauth-setting-row aoauth-setting-divider-row">
                        <div class="aoauth-setting-label">
                            <label><?php esc_html_e('— OR —', 'aoauth-client-sso'); ?></label>
                        </div>
                        <div class="aoauth-setting-control"></div>
                    </div>

                    <!-- Option 2: Google reCAPTCHA -->
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="enable_recaptcha"><?php esc_html_e('Google reCAPTCHA v3', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Google\'s bot protection. Requires API keys.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="enable_recaptcha" name="enable_recaptcha" <?php checked(!empty($settings['enable_recaptcha']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="aoauth-setting-row recaptcha-fields <?php echo empty($settings['enable_recaptcha']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-label">
                            <label for="recaptcha_site_key"><?php esc_html_e('reCAPTCHA Site Key', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Get from Google Cloud Console → Credentials.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="text" id="recaptcha_site_key" name="recaptcha_site_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['recaptcha_site_key'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="aoauth-setting-row recaptcha-fields <?php echo empty($settings['enable_recaptcha']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-label">
                            <label for="recaptcha_secret_key"><?php esc_html_e('reCAPTCHA Secret Key', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Keep this secret. Used to verify the challenge.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['recaptcha_secret_key'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row recaptcha-fields <?php echo empty($settings['enable_recaptcha']) ? 'aoauth-is-hidden' : ''; ?>">
                        <div class="aoauth-setting-label">
                            <label for="recaptcha_score_threshold"><?php esc_html_e('Score Threshold (0.0 - 1.0)', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Minimum score to pass (0.5 recommended). Lower = less strict.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="number" id="recaptcha_score_threshold" name="recaptcha_score_threshold" class="aoauth-form-control" step="0.1" min="0" max="1" value="<?php echo esc_attr($settings['recaptcha_score_threshold'] ?? 0.5); ?>">
                        </div>
                    </div>

                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="bot_overlay_enabled"><?php esc_html_e('Full-Screen Verification Overlay', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('When bot protection runs, cover the login screen with a branded verification state until the provider redirect starts.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="hidden" name="bot_overlay_enabled" value="0">
                                <input type="checkbox" id="bot_overlay_enabled" name="bot_overlay_enabled" value="1" <?php checked(!empty($settings['bot_overlay_enabled']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="bot_overlay_message"><?php esc_html_e('Overlay Message', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Short message displayed while bot protection verifies the login request.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <input type="text" id="bot_overlay_message" name="bot_overlay_message" class="aoauth-form-control" value="<?php echo esc_attr($settings['bot_overlay_message'] ?? 'Verifying secure sign-in...'); ?>">
                        </div>
                    </div>
                </div>
                
                <!-- Logs -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('Logs', 'aoauth-client-sso'); ?></h3>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="enable_logs"><?php esc_html_e('Enable Detailed Logs', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Record all authentication events. When disabled, no logs are written.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="enable_logs" name="enable_logs" <?php checked(!empty($settings['enable_logs']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label><?php esc_html_e('Logs Retention Period', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('How long to keep log entries before automatic deletion.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <select id="logs_retention_period" name="logs_retention_period" class="aoauth-form-control">
                                <option value="7_days" <?php selected($settings['logs_retention_period'] ?? '30_days', '7_days'); ?>><?php esc_html_e('7 Days', 'aoauth-client-sso'); ?></option>
                                <option value="14_days" <?php selected($settings['logs_retention_period'] ?? '30_days', '14_days'); ?>><?php esc_html_e('14 Days', 'aoauth-client-sso'); ?></option>
                                <option value="30_days" <?php selected($settings['logs_retention_period'] ?? '30_days', '30_days'); ?>><?php esc_html_e('30 Days', 'aoauth-client-sso'); ?></option>
                                <option value="60_days" <?php selected($settings['logs_retention_period'] ?? '30_days', '60_days'); ?>><?php esc_html_e('60 Days', 'aoauth-client-sso'); ?></option>
                                <option value="90_days" <?php selected($settings['logs_retention_period'] ?? '30_days', '90_days'); ?>><?php esc_html_e('90 Days', 'aoauth-client-sso'); ?></option>
                                <option value="6_months" <?php selected($settings['logs_retention_period'] ?? '30_days', '6_months'); ?>><?php esc_html_e('6 Months', 'aoauth-client-sso'); ?></option>
                                <option value="1_year" <?php selected($settings['logs_retention_period'] ?? '30_days', '1_year'); ?>><?php esc_html_e('1 Year', 'aoauth-client-sso'); ?></option>
                                <option value="forever" <?php selected($settings['logs_retention_period'] ?? '30_days', 'forever'); ?>><?php esc_html_e('Forever', 'aoauth-client-sso'); ?></option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label><?php esc_html_e('Clear Logs', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('Manually delete all log entries from the database.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <button type="button" id="aoauth-clear-logs-settings-btn" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Clear All Logs', 'aoauth-client-sso'); ?></button>
                        </div>
                    </div>
                </div>
                
                <!-- Data Management -->
                <div class="aoauth-settings-section">
                    <h3 class="aoauth-section-title"><?php esc_html_e('Data Management', 'aoauth-client-sso'); ?></h3>
                    
                    <div class="aoauth-setting-row">
                        <div class="aoauth-setting-label">
                            <label for="delete_data_on_uninstall"><?php esc_html_e('Delete All Plugin Data on Uninstall', 'aoauth-client-sso'); ?></label>
                            <p class="aoauth-setting-help"><?php esc_html_e('When checked, all plugin settings, configurations, and logs will be permanently deleted when the plugin is uninstalled.', 'aoauth-client-sso'); ?></p>
                        </div>
                        <div class="aoauth-setting-control">
                            <label class="aoauth-toggle">
                                <input type="checkbox" id="delete_data_on_uninstall" name="delete_data_on_uninstall" <?php checked(!empty($settings['delete_data_on_uninstall']), true); ?>>
                                <span class="aoauth-toggle-slider"></span>
                            </label>
                        </div>
                    </div>
                </div>
                
                <div>
                    <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save Settings', 'aoauth-client-sso'); ?></button>
                </div>
            </form>
        </div>
        
        <!-- RIGHT COLUMN - Backup & Tools -->
        <div class="aoauth-tools-column">
            <div class="aoauth-tools-card">
                <h3><?php esc_html_e('Backup & Restore', 'aoauth-client-sso'); ?></h3>
                <p><?php esc_html_e('Export all plugin settings and provider configurations, or import a previously saved JSON file.', 'aoauth-client-sso'); ?></p>
                <p class="aoauth-note"><?php esc_html_e('Note: Leave the backup password blank to download settings without provider credentials or bot protection secret keys. Enter a backup password to include those secrets encrypted in the file; the same password is required during restore.', 'aoauth-client-sso'); ?></p>
                <div class="aoauth-tools-buttons">
                    <button type="button" id="aoauth-export-config-btn" class="aoauth-admin-button aoauth-admin-button-primary"><?php esc_html_e('Download Settings', 'aoauth-client-sso'); ?></button>
                    <form id="aoauth-import-form" class="aoauth-inline-form">
                        <input type="file" id="aoauth-import-file" accept=".json" class="aoauth-hidden-field">
                        <button type="button" id="aoauth-import-config-btn" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Restore Settings', 'aoauth-client-sso'); ?></button>
                    </form>
                </div>
            </div>
            
            <div class="aoauth-tools-card danger-zone">
                <h3><?php esc_html_e('Danger Zone', 'aoauth-client-sso'); ?></h3>
                <p><?php esc_html_e('Reset all plugin settings to default, remove all connected providers, and delete all logs. This action cannot be undone.', 'aoauth-client-sso'); ?></p>
                <button type="button" id="aoauth-factory-reset-btn" class="aoauth-admin-button aoauth-admin-button-danger"><?php esc_html_e('Factory Reset', 'aoauth-client-sso'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Factory Reset Modal -->
<div id="aoauth-factory-reset-modal" class="aoauth-modal aoauth-is-hidden">
    <div class="aoauth-modal-content">
        <h3><?php esc_html_e('Factory Reset', 'aoauth-client-sso'); ?></h3>
        <p><?php esc_html_e('This action will reset all plugin settings to default, remove all connected providers, and delete all logs. This cannot be undone.', 'aoauth-client-sso'); ?></p>
        <p><strong><?php esc_html_e('To confirm, please wait', 'aoauth-client-sso'); ?> <span id="aoauth-countdown">10</span> <?php esc_html_e('seconds', 'aoauth-client-sso'); ?>.</strong></p>
        <div class="aoauth-modal-buttons">
            <button id="aoauth-confirm-reset" class="aoauth-admin-button aoauth-admin-button-danger" disabled><?php esc_html_e('Confirm Reset', 'aoauth-client-sso'); ?></button>
            <button id="aoauth-cancel-reset" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Cancel', 'aoauth-client-sso'); ?></button>
        </div>
    </div>
</div>
