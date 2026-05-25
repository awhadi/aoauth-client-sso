<?php if (!defined('ABSPATH')) exit;
$bot_enabled = !empty($settings['enable_bot_protection']) || !empty($settings['enable_turnstile']) || !empty($settings['enable_recaptcha']);
$bot_provider = !empty($settings['bot_protection_provider']) ? $settings['bot_protection_provider'] : (!empty($settings['enable_recaptcha']) ? 'recaptcha' : 'turnstile');
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Security Settings', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="security_level"><?php esc_html_e('Security Level', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('High enforces state, nonce, PKCE, redirect validation, and stricter identity token checks where supported. Medium keeps compatibility for older providers but is less protective.', 'aoauth-client-sso'); ?></p>
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
                </div>
                <div class="aoauth-setting-control">
                    <input type="number" id="rate_limit_attempts" name="rate_limit_attempts" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['rate_limit_attempts'] ?? 5); ?>" min="1" max="100">
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="rate_limit_window"><?php esc_html_e('Rate Limit Window', 'aoauth-client-sso'); ?></label>
                </div>
                <div class="aoauth-setting-control">
                    <input type="number" id="rate_limit_window" name="rate_limit_window" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['rate_limit_window'] ?? 300); ?>" min="60" max="86400">
                </div>
            </div>
        </div>

        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Bot Protection', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="enable_bot_protection"><?php esc_html_e('Enable Bot Protection', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Require one browser verification before starting the provider redirect.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <label class="aoauth-toggle">
                        <input type="hidden" name="enable_bot_protection" value="0">
                        <input type="checkbox" id="enable_bot_protection" name="enable_bot_protection" value="1" <?php checked($bot_enabled); ?>>
                        <span class="aoauth-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="aoauth-bot-protection-fields <?php echo !$bot_enabled ? 'aoauth-is-hidden' : ''; ?>">
                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="bot_protection_provider"><?php esc_html_e('Verification Provider', 'aoauth-client-sso'); ?></label>
                    </div>
                    <div class="aoauth-setting-control">
                        <select id="bot_protection_provider" name="bot_protection_provider" class="aoauth-form-control">
                            <option value="turnstile" <?php selected($bot_provider, 'turnstile'); ?>><?php esc_html_e('Cloudflare Turnstile', 'aoauth-client-sso'); ?></option>
                            <option value="recaptcha" <?php selected($bot_provider, 'recaptcha'); ?>><?php esc_html_e('Google reCAPTCHA v3', 'aoauth-client-sso'); ?></option>
                        </select>
                    </div>
                </div>

                <div class="aoauth-setting-row turnstile-fields <?php echo $bot_provider !== 'turnstile' ? 'aoauth-is-hidden' : ''; ?>">
                    <div class="aoauth-setting-label"><label for="turnstile_site_key"><?php esc_html_e('Turnstile Site Key', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="text" id="turnstile_site_key" name="turnstile_site_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['turnstile_site_key'] ?? ''); ?>"></div>
                </div>

                <div class="aoauth-setting-row turnstile-fields <?php echo $bot_provider !== 'turnstile' ? 'aoauth-is-hidden' : ''; ?>">
                    <div class="aoauth-setting-label"><label for="turnstile_secret_key"><?php esc_html_e('Turnstile Secret Key', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="password" id="turnstile_secret_key" name="turnstile_secret_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['turnstile_secret_key'] ?? ''); ?>"></div>
                </div>

                <div class="aoauth-setting-row recaptcha-fields <?php echo $bot_provider !== 'recaptcha' ? 'aoauth-is-hidden' : ''; ?>">
                    <div class="aoauth-setting-label"><label for="recaptcha_site_key"><?php esc_html_e('reCAPTCHA Site Key', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="text" id="recaptcha_site_key" name="recaptcha_site_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['recaptcha_site_key'] ?? ''); ?>"></div>
                </div>

                <div class="aoauth-setting-row recaptcha-fields <?php echo $bot_provider !== 'recaptcha' ? 'aoauth-is-hidden' : ''; ?>">
                    <div class="aoauth-setting-label"><label for="recaptcha_secret_key"><?php esc_html_e('reCAPTCHA Secret Key', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="password" id="recaptcha_secret_key" name="recaptcha_secret_key" class="aoauth-form-control" value="<?php echo esc_attr($settings['recaptcha_secret_key'] ?? ''); ?>"></div>
                </div>

                <div class="aoauth-setting-row recaptcha-fields <?php echo $bot_provider !== 'recaptcha' ? 'aoauth-is-hidden' : ''; ?>">
                    <div class="aoauth-setting-label"><label for="recaptcha_score_threshold"><?php esc_html_e('Score Threshold', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="number" id="recaptcha_score_threshold" name="recaptcha_score_threshold" class="aoauth-form-control" step="0.1" min="0" max="1" value="<?php echo esc_attr($settings['recaptcha_score_threshold'] ?? 0.5); ?>"></div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="bot_overlay_enabled"><?php esc_html_e('Full-Screen Verification Overlay', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Overlay styling is configured in Sign-In Experience.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="bot_overlay_enabled" value="0">
                            <input type="checkbox" id="bot_overlay_enabled" name="bot_overlay_enabled" value="1" <?php checked(!empty($settings['bot_overlay_enabled'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label"><label for="bot_overlay_message"><?php esc_html_e('Overlay Message', 'aoauth-client-sso'); ?></label></div>
                    <div class="aoauth-setting-control"><input type="text" id="bot_overlay_message" name="bot_overlay_message" class="aoauth-form-control" value="<?php echo esc_attr($settings['bot_overlay_message'] ?? 'Verifying secure sign-in...'); ?>"></div>
                </div>
            </div>
        </div>

        <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save Security Settings', 'aoauth-client-sso'); ?></button>
    </form>
</div>
