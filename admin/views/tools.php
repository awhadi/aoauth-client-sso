<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- This template receives local view variables from the admin renderer.
if (!defined('ABSPATH')) exit;
$debug_enabled = aoauth_core()->get_debug()->is_enabled();
$debug_constant = 'define("OAUTH-DEBUG", "enabled");';
$debug_log_directory = 'wp-content/uploads/aoauth-debug/';
$debug_log_filename = 'aoauth-debug-YYYY-MM-DD.log';
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
                        <p class="aoauth-setting-help"><?php esc_html_e('Applies the wp-config.php constant only after Save Tools Settings is clicked. Deep Debug files are stored in the WordPress uploads directory.', 'aoauth-client-sso'); ?></p>
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
                    <p class="aoauth-setting-help">
                        <?php
                        echo wp_kses(
                            /* translators: 1: debug log directory, 2: debug log filename pattern. */
                            sprintf(
                                __('Deep Debug writes files to %1$s with the daily filename pattern %2$s.', 'aoauth-client-sso'),
                                '<code>' . esc_html($debug_log_directory) . '</code>',
                                '<code>' . esc_html($debug_log_filename) . '</code>'
                            ),
                            array('code' => array())
                        );
                        ?>
                    </p>
                    <p class="aoauth-setting-help"><?php esc_html_e('Deep Debug is also active when AOAUTH_DEBUG is defined as true in wp-config.php. Disabling from this screen removes supported aOAUTH debug constants when WordPress can write to wp-config.php.', 'aoauth-client-sso'); ?></p>
                    <p class="aoauth-setting-help"><?php esc_html_e('Apache and LiteSpeed use the generated .htaccess protection in wp-content/uploads/aoauth-debug/. Nginx ignores .htaccess, so block direct access to /wp-content/uploads/aoauth-debug/ in the server block.', 'aoauth-client-sso'); ?></p>
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
        <p class="aoauth-note"><?php esc_html_e('For your protection, exports and imports require your current WordPress administrator password. Leave the backup password blank to exclude credentials. Enter a backup password to include or restore encrypted secrets.', 'aoauth-client-sso'); ?></p>
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

    <div class="aoauth-tools-card">
        <h3><?php esc_html_e('WP-CLI Administration', 'aoauth-client-sso'); ?></h3>
        <p><?php esc_html_e('Manage and audit aOAUTH from a terminal connected to this WordPress installation. Run commands from the WordPress root directory while the site database is available.', 'aoauth-client-sso'); ?></p>
        <p class="aoauth-note"><strong><?php esc_html_e('Security:', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Status commands never reveal Client IDs, Client Secrets, access tokens, or bot-protection secrets. Commands that change providers are audit logged. Configuration imports require confirmation and replace the current plugin settings and providers.', 'aoauth-client-sso'); ?></p>
        <div class="aoauth-shortcode-reference">
            <div>
                <code>wp aoauth status</code>
                <p><strong><?php esc_html_e('Configuration overview.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Displays the plugin version and administrator-facing values from Sign-In Experience, User Management, Security, Tools, role redirects, and provider availability. Use it for audits, troubleshooting, and deployment verification.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth providers</code>
                <p><strong><?php esc_html_e('Provider inventory.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Lists each provider ID, display name, provider type, enabled state, and whether required credentials are present. Credential values are never printed.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth provider enable keycloak</code>
                <p><strong><?php esc_html_e('Enable sign-in through a provider.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Replace keycloak with a provider ID reported by wp aoauth providers. The command refuses to enable a provider until its Client ID and Client Secret are configured, then records the change in Activity Logs.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth provider disable keycloak</code>
                <p><strong><?php esc_html_e('Pause sign-in through a provider.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Prevents new sign-in attempts through the selected provider while preserving its configuration, encrypted credentials, and linked-user records. The change is recorded in Activity Logs.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth export aoauth-backup.json</code>
                <p><strong><?php esc_html_e('Create a safe configuration backup.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Exports plugin settings and provider definitions to JSON with credentials excluded by default. The backup file is restricted to the current operating-system user when supported.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth export aoauth-backup.json --include-credentials</code>
                <p><strong><?php esc_html_e('Create an encrypted full backup.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Set AOAUTH_BACKUP_PASSWORD securely in the environment before running this command. Client IDs, Client Secrets, and bot-protection secrets are protected with password-based AES-256-GCM encryption; the password is never stored in the backup.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth import aoauth-backup.json</code>
                <p><strong><?php esc_html_e('Restore a validated configuration.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Reviews the file and asks for confirmation before replacing the current settings and providers. Create a current backup first. For encrypted backups, set the same AOAUTH_BACKUP_PASSWORD used during export. Use --yes only in controlled automation.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp aoauth status --format=json</code>
                <p><strong><?php esc_html_e('Machine-readable output.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Returns status data as JSON for monitoring, deployment checks, or configuration audits. The status and providers commands also support table, CSV, and YAML output.', 'aoauth-client-sso'); ?></p>
            </div>
            <div>
                <code>wp help aoauth</code>
                <p><strong><?php esc_html_e('Command reference.', 'aoauth-client-sso'); ?></strong> <?php esc_html_e('Displays all available aOAUTH commands. Use wp help aoauth status, wp help aoauth export, or another command path for its options, safeguards, and examples.', 'aoauth-client-sso'); ?></p>
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

<div id="aoauth-config-password-modal" class="aoauth-modal aoauth-is-hidden" data-export-message="<?php echo esc_attr__('Confirm your WordPress administrator password before downloading plugin settings. Add a backup password only when you need encrypted Client IDs, Client Secrets, and bot-protection secrets in the file.', 'aoauth-client-sso'); ?>" data-import-message="<?php echo esc_attr__('Confirm your WordPress administrator password before restoring plugin settings. If the backup contains encrypted credentials, enter the same backup password used during export.', 'aoauth-client-sso'); ?>">
    <div class="aoauth-modal-content">
        <h3><?php esc_html_e('Confirm Backup Action', 'aoauth-client-sso'); ?></h3>
        <p id="aoauth-config-password-message"></p>
        <div class="aoauth-modal-field">
            <label for="aoauth-admin-password-confirm"><?php esc_html_e('WordPress administrator password', 'aoauth-client-sso'); ?></label>
            <input type="password" id="aoauth-admin-password-confirm" class="aoauth-form-control" autocomplete="current-password">
        </div>
        <div class="aoauth-modal-field">
            <label for="aoauth-backup-password"><?php esc_html_e('Backup password', 'aoauth-client-sso'); ?></label>
            <input type="password" id="aoauth-backup-password" class="aoauth-form-control" autocomplete="new-password">
            <p class="aoauth-setting-help"><?php esc_html_e('Optional for settings-only backups. Required only for encrypted credential backups.', 'aoauth-client-sso'); ?></p>
        </div>
        <div id="aoauth-backup-password-confirm-row" class="aoauth-modal-field">
            <label for="aoauth-backup-password-confirm"><?php esc_html_e('Confirm backup password', 'aoauth-client-sso'); ?></label>
            <input type="password" id="aoauth-backup-password-confirm" class="aoauth-form-control" autocomplete="new-password">
        </div>
        <div class="aoauth-modal-buttons">
            <button type="button" id="aoauth-confirm-config-password" class="aoauth-admin-button aoauth-admin-button-primary"><?php esc_html_e('Continue', 'aoauth-client-sso'); ?></button>
            <button type="button" id="aoauth-cancel-config-password" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Cancel', 'aoauth-client-sso'); ?></button>
        </div>
    </div>
</div>
