<?php if (!defined('ABSPATH')) exit;
$role_redirects = isset($settings['role_redirects']) && is_array($settings['role_redirects']) ? $settings['role_redirects'] : AOAUTH_Core::get_default_role_redirects();
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('User Management', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('User Creation', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('Controls how new WordPress users are created after successful SSO.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="auto_create_users"><?php esc_html_e('Auto-Create Users', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Create WordPress users after successful SSO when no account exists.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <label class="aoauth-toggle">
                        <input type="hidden" name="auto_create_users" value="0">
                        <input type="checkbox" id="auto_create_users" name="auto_create_users" value="1" <?php checked(!empty($settings['auto_create_users'])); ?>>
                        <span class="aoauth-toggle-slider"></span>
                    </label>
                </div>
            </div>

                <div class="aoauth-setting-row <?php echo empty($settings['auto_create_users']) ? 'aoauth-is-hidden' : ''; ?>" id="default-role-row">
                <div class="aoauth-setting-label">
                    <label for="default_role"><?php esc_html_e('Default User Role', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Applied to new SSO users unless provider role mapping overrides it.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <select id="default_role" name="default_role" class="aoauth-form-control">
                        <?php foreach ($roles as $role_key => $role): ?>
                            <option value="<?php echo esc_attr($role_key); ?>" <?php selected($settings['default_role'] ?? 'subscriber', $role_key); ?>><?php echo esc_html($role['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            </div>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('Role Redirects', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('Routes users to the right place after authentication.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row-full">
                <div class="aoauth-setting-label-full">
                    <label><?php esc_html_e('Role-Based Redirects', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Set a path or safe same-site URL for each role after successful authentication.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-role-redirect-grid">
                    <?php foreach ($roles as $role_key => $role): ?>
                        <label class="aoauth-role-redirect-row">
                            <span><?php echo esc_html($role['name']); ?></span>
                            <input type="text" name="role_redirects[<?php echo esc_attr($role_key); ?>]" class="aoauth-form-control" value="<?php echo esc_attr($role_redirects[$role_key] ?? '/'); ?>" placeholder="/">
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            </div>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('Account Linking', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('Lets users connect one or more enabled SSO providers to the same WordPress account.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="allow_account_linking"><?php esc_html_e('Allow Account Linking', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Allow existing users to confirm their WordPress password and attach an SSO provider.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <label class="aoauth-toggle">
                        <input type="hidden" name="allow_account_linking" value="0">
                        <input type="checkbox" id="allow_account_linking" name="allow_account_linking" value="1" <?php checked(!empty($settings['allow_account_linking'])); ?>>
                        <span class="aoauth-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div id="account-linking-security-row" class="<?php echo empty($settings['allow_account_linking']) ? 'aoauth-is-hidden' : ''; ?>">
                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="enable_self_service_account_linking"><?php esc_html_e('Self-Service Account Linking', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Allow logged-in users to link an SSO provider from a front-end page using [aoauth_link_account].', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="enable_self_service_account_linking" value="0">
                            <input type="checkbox" id="enable_self_service_account_linking" name="enable_self_service_account_linking" value="1" <?php checked(!empty($settings['enable_self_service_account_linking'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="linking_max_attempts"><?php esc_html_e('Max Password Attempts', 'aoauth-client-sso'); ?></label>
                    </div>
                    <div class="aoauth-setting-control">
                        <input type="number" id="linking_max_attempts" name="linking_max_attempts" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_max_attempts'] ?? 5); ?>" min="1" max="20">
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="linking_lockout_minutes"><?php esc_html_e('Lockout Duration', 'aoauth-client-sso'); ?></label>
                    </div>
                    <div class="aoauth-setting-control">
                        <input type="number" id="linking_lockout_minutes" name="linking_lockout_minutes" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_lockout_minutes'] ?? 15); ?>" min="1" max="1440">
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="linking_login_ban_minutes"><?php esc_html_e('Full Login Ban', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('After repeated failed account-linking password checks, block both SSO and WordPress password login for this user for the selected number of minutes. Use 0 to disable the full login ban.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <input type="number" id="linking_login_ban_minutes" name="linking_login_ban_minutes" class="aoauth-form-control aoauth-number-input" value="<?php echo esc_attr($settings['linking_login_ban_minutes'] ?? 15); ?>" min="0" max="1440">
                    </div>
                </div>
            </div>
            </div>
        </div>

        <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save User Management', 'aoauth-client-sso'); ?></button>
    </form>
</div>
