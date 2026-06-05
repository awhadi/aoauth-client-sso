<?php if (!defined('ABSPATH')) exit;
$debug_enabled = aoauth_core()->get_debug()->is_enabled();
$debug_constant = 'define("OAUTH-DEBUG", "enabled");';
$next_cleanup = wp_next_scheduled('aoauth_retention_cron');
$last_cleanup = get_option('aoauth_last_retention_run', '');
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Logs & Debug', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('Activity Logs', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('These are the normal plugin logs shown in the Logs tab.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="enable_logs"><?php esc_html_e('Enable Activity Logs', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Record authentication, provider, security, and admin utility events for the Logs screen.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="hidden" name="enable_logs" value="0">
                            <input type="checkbox" id="enable_logs" name="enable_logs" value="1" <?php checked(!empty($settings['enable_logs'])); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                    </div>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label for="logs_retention_period"><?php esc_html_e('Log Retention Period', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Old records are removed by native WordPress cron, so this may not appear in Action Scheduler screens.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <select id="logs_retention_period" name="logs_retention_period" class="aoauth-form-control">
                            <?php foreach (array('7_days' => '7 Days', '14_days' => '14 Days', '30_days' => '30 Days', '60_days' => '60 Days', '90_days' => '90 Days', '6_months' => '6 Months', '1_year' => '1 Year', 'forever' => 'Forever') as $value => $label): ?>
                                <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['logs_retention_period'] ?? '30_days', $value); ?>><?php echo esc_html($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="aoauth-cron-status">
                    <div>
                        <span><?php esc_html_e('Next Cleanup', 'aoauth-client-sso'); ?></span>
                        <strong><?php echo $next_cleanup ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $next_cleanup)) : esc_html__('Not scheduled', 'aoauth-client-sso'); ?></strong>
                    </div>
                    <div>
                        <span><?php esc_html_e('Last Cleanup', 'aoauth-client-sso'); ?></span>
                        <strong><?php echo $last_cleanup ? esc_html($last_cleanup) : esc_html__('Not yet run', 'aoauth-client-sso'); ?></strong>
                    </div>
                </div>

                <div class="aoauth-maintenance-actions">
                    <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-maintenance-action" data-action="aoauth_run_log_cleanup" data-reload="1"><?php esc_html_e('Run Cleanup Now', 'aoauth-client-sso'); ?></button>
                    <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-maintenance-action" data-action="aoauth_reschedule_log_cleanup" data-reload="1"><?php esc_html_e('Reschedule Cleanup', 'aoauth-client-sso'); ?></button>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label><?php esc_html_e('Clear Logs', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Delete all authentication logs from the database.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <button type="button" id="aoauth-clear-logs-settings-btn" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Clear All Logs', 'aoauth-client-sso'); ?></button>
                    </div>
                </div>
            </div>

            <div class="aoauth-setting-group">
                <div class="aoauth-setting-group-header">
                    <h4><?php esc_html_e('Deep Debug', 'aoauth-client-sso'); ?></h4>
                    <p><?php esc_html_e('Low-level request and OAuth flow debug files are controlled from wp-config.php, not saved plugin settings.', 'aoauth-client-sso'); ?></p>
                </div>

                <div class="aoauth-setting-row">
                    <div class="aoauth-setting-label">
                        <label><?php esc_html_e('Deep Debug Mode', 'aoauth-client-sso'); ?></label>
                        <p class="aoauth-setting-help"><?php esc_html_e('Applies the wp-config.php constant only after Save Tools Settings is clicked. Debug files are stored under uploads/aoauth-debug/.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-setting-control">
                        <label class="aoauth-toggle">
                            <input type="checkbox" id="aoauth-deep-debug-toggle" value="1" data-current-state="<?php echo $debug_enabled ? '1' : '0'; ?>" <?php checked($debug_enabled); ?>>
                            <span class="aoauth-toggle-slider"></span>
                        </label>
                        <span id="aoauth-deep-debug-status" class="aoauth-status-badge aoauth-status-<?php echo $debug_enabled ? 'success' : 'info'; ?>"><?php echo $debug_enabled ? esc_html__('Enabled', 'aoauth-client-sso') : esc_html__('Off', 'aoauth-client-sso'); ?></span>
                    </div>
                </div>

                <div class="aoauth-code-reference">
                    <code><?php echo esc_html($debug_constant); ?></code>
                    <p class="aoauth-setting-help"><?php esc_html_e('If automatic toggling fails, place this above the "stop editing" line in wp-config.php. Remove it after troubleshooting.', 'aoauth-client-sso'); ?></p>
                    <p class="aoauth-setting-help"><?php esc_html_e('Deep Debug is also active when AOAUTH_DEBUG is defined as true in wp-config.php. Disabling from this screen removes supported aOAUTH debug constants when WordPress can write to wp-config.php.', 'aoauth-client-sso'); ?></p>
                    <p class="aoauth-setting-help"><?php esc_html_e('Apache and LiteSpeed use the generated .htaccess protection in uploads/aoauth-debug/. Nginx ignores .htaccess, so block direct access to /wp-content/uploads/aoauth-debug/ in the server block.', 'aoauth-client-sso'); ?></p>
                </div>
            </div>
        </div>

        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Session Management', 'aoauth-client-sso'); ?></h3>
            <div class="aoauth-setting-group">
                <div class="aoauth-session-summary">
                    <div>
                        <span class="aoauth-session-count"><?php echo esc_html(count($sso_users)); ?></span>
                        <span><?php esc_html_e('SSO-linked users', 'aoauth-client-sso'); ?></span>
                    </div>
                    <a class="aoauth-admin-button aoauth-admin-button-secondary" href="<?php echo esc_url(admin_url('users.php')); ?>"><?php esc_html_e('Open Users', 'aoauth-client-sso'); ?></a>
                </div>

                <div class="aoauth-maintenance-actions">
                    <div class="aoauth-maintenance-item">
                        <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-maintenance-action" data-action="aoauth_clear_bot_verifications"><?php esc_html_e('Clear All Bot Verification Tokens', 'aoauth-client-sso'); ?></button>
                        <p class="aoauth-setting-help"><?php esc_html_e('Removes temporary Turnstile/reCAPTCHA approvals. Example: use this after changing bot protection keys or when users report repeated verification prompts.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-maintenance-item">
                        <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-maintenance-action" data-action="aoauth_clear_linking_lockouts"><?php esc_html_e('Clear Linking Lockouts', 'aoauth-client-sso'); ?></button>
                        <p class="aoauth-setting-help"><?php esc_html_e('Clears temporary password-failure lockouts for account linking. Example: use this after confirming a legitimate user was blocked while linking Google or Microsoft.', 'aoauth-client-sso'); ?></p>
                    </div>
                    <div class="aoauth-maintenance-item">
                        <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-maintenance-action" data-action="aoauth_clear_oauth_temp_data"><?php esc_html_e('Clear Expired OAuth Temp Data', 'aoauth-client-sso'); ?></button>
                        <p class="aoauth-setting-help"><?php esc_html_e('Deletes expired OAuth state, nonce, and account-linking records. Example: run this after interrupted sign-in tests or before handing a staging site to another admin.', 'aoauth-client-sso'); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Data Management', 'aoauth-client-sso'); ?></h3>
            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="enable_auto_updates"><?php esc_html_e('Enable Automatic Updates', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Allow WordPress to install new versions of this plugin automatically when an update package is available. Disable this if you prefer to update manually after testing.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <label class="aoauth-toggle">
                        <input type="hidden" name="enable_auto_updates" value="0">
                        <input type="checkbox" id="enable_auto_updates" name="enable_auto_updates" value="1" <?php checked(!empty($settings['enable_auto_updates'])); ?>>
                        <span class="aoauth-toggle-slider"></span>
                    </label>
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="delete_data_on_uninstall"><?php esc_html_e('Delete Plugin Data on Uninstall', 'aoauth-client-sso'); ?></label>
                </div>
                <div class="aoauth-setting-control">
                    <label class="aoauth-toggle">
                        <input type="hidden" name="delete_data_on_uninstall" value="0">
                        <input type="checkbox" id="delete_data_on_uninstall" name="delete_data_on_uninstall" value="1" <?php checked(!empty($settings['delete_data_on_uninstall'])); ?>>
                        <span class="aoauth-toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>

        <button type="submit" class="aoauth-admin-button aoauth-admin-button-primary aoauth-save-settings-btn"><?php esc_html_e('Save Tools Settings', 'aoauth-client-sso'); ?></button>
    </form>
</div>

<div class="aoauth-tools-column">
    <div class="aoauth-tools-card">
        <h3><?php esc_html_e('Backup & Restore', 'aoauth-client-sso'); ?></h3>
        <p><?php esc_html_e('Export settings and providers, or restore a saved JSON backup.', 'aoauth-client-sso'); ?></p>
        <p class="aoauth-note"><?php esc_html_e('Leave the backup password blank to exclude credentials. Enter a password to include encrypted secrets.', 'aoauth-client-sso'); ?></p>
        <div class="aoauth-tools-buttons">
            <button type="button" id="aoauth-export-config-btn" class="aoauth-admin-button aoauth-admin-button-primary"><?php esc_html_e('Download Settings', 'aoauth-client-sso'); ?></button>
            <form id="aoauth-import-form" class="aoauth-inline-form">
                <input type="file" id="aoauth-import-file" accept=".json" class="aoauth-hidden-field">
                <button type="button" id="aoauth-import-config-btn" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Restore Settings', 'aoauth-client-sso'); ?></button>
            </form>
        </div>
    </div>

    <div class="aoauth-tools-card">
        <h3><?php esc_html_e('Shortcodes', 'aoauth-client-sso'); ?></h3>
        <div class="aoauth-shortcode-reference">
            <div>
                <code>[aoauth_link_account]</code>
                <p><?php esc_html_e('Shows enabled providers that the logged-in user can link to their own WordPress account. Requires account linking and self-service linking to be enabled.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>[aoauth_unlink_account]</code>
                <p><?php esc_html_e('Shows the logged-in user their connected SSO providers and lets them disconnect a provider after confirmation.', 'aoauth-client-sso'); ?></p>
            </div>
        </div>
    </div>

    <div class="aoauth-tools-card danger-zone">
        <h3><?php esc_html_e('Danger Zone', 'aoauth-client-sso'); ?></h3>
        <p><?php esc_html_e('Reset settings, remove providers, and delete logs. This action cannot be undone.', 'aoauth-client-sso'); ?></p>
        <button type="button" id="aoauth-factory-reset-btn" class="aoauth-admin-button aoauth-admin-button-danger"><?php esc_html_e('Factory Reset', 'aoauth-client-sso'); ?></button>
    </div>
</div>

<div id="aoauth-factory-reset-modal" class="aoauth-modal aoauth-is-hidden">
    <div class="aoauth-modal-content">
        <h3><?php esc_html_e('Factory Reset', 'aoauth-client-sso'); ?></h3>
        <p><?php esc_html_e('This resets plugin settings, providers, and logs.', 'aoauth-client-sso'); ?></p>
        <p><strong><?php esc_html_e('Confirm after', 'aoauth-client-sso'); ?> <span id="aoauth-countdown">10</span> <?php esc_html_e('seconds', 'aoauth-client-sso'); ?>.</strong></p>
        <div class="aoauth-modal-buttons">
            <button id="aoauth-confirm-reset" class="aoauth-admin-button aoauth-admin-button-danger" disabled><?php esc_html_e('Confirm Reset', 'aoauth-client-sso'); ?></button>
            <button id="aoauth-cancel-reset" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Cancel', 'aoauth-client-sso'); ?></button>
        </div>
    </div>
</div>
