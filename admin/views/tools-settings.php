<?php if (!defined('ABSPATH')) exit;
$debug_enabled = aoauth_core()->get_debug()->is_enabled();
?>
<div class="aoauth-settings-column">
    <form class="aoauth-settings-form">
        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Debug & Logs', 'aoauth-client-sso'); ?></h3>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label><?php esc_html_e('Debug Mode', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help">
                        <?php echo $debug_enabled
                            ? esc_html__('WordPress debugging is currently enabled. Plugin debug events can be recorded in logs.', 'aoauth-client-sso')
                            : esc_html__('WordPress debugging is not enabled. Add WP_DEBUG and WP_DEBUG_LOG to wp-config.php to capture low-level debug output.', 'aoauth-client-sso'); ?>
                    </p>
                </div>
                <div class="aoauth-setting-control">
                    <span class="aoauth-status-badge aoauth-status-<?php echo $debug_enabled ? 'success' : 'info'; ?>"><?php echo $debug_enabled ? esc_html__('Enabled', 'aoauth-client-sso') : esc_html__('Manual setup required', 'aoauth-client-sso'); ?></span>
                </div>
            </div>

            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label for="enable_logs"><?php esc_html_e('Enable Detailed Logs', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Record authentication, provider, and security events.', 'aoauth-client-sso'); ?></p>
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
                <div class="aoauth-setting-label"><label for="logs_retention_period"><?php esc_html_e('Log Retention Period', 'aoauth-client-sso'); ?></label></div>
                <div class="aoauth-setting-control">
                    <select id="logs_retention_period" name="logs_retention_period" class="aoauth-form-control">
                        <?php foreach (array('7_days' => '7 Days', '14_days' => '14 Days', '30_days' => '30 Days', '60_days' => '60 Days', '90_days' => '90 Days', '6_months' => '6 Months', '1_year' => '1 Year', 'forever' => 'Forever') as $value => $label): ?>
                            <option value="<?php echo esc_attr($value); ?>" <?php selected($settings['logs_retention_period'] ?? '30_days', $value); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
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

        <div class="aoauth-settings-section">
            <h3 class="aoauth-section-title"><?php esc_html_e('Session Management', 'aoauth-client-sso'); ?></h3>
            <div class="aoauth-setting-row">
                <div class="aoauth-setting-label">
                    <label><?php esc_html_e('SSO Users', 'aoauth-client-sso'); ?></label>
                    <p class="aoauth-setting-help"><?php esc_html_e('Recently found users with an SSO provider attached.', 'aoauth-client-sso'); ?></p>
                </div>
                <div class="aoauth-setting-control">
                    <span class="aoauth-status-badge aoauth-status-info"><?php echo esc_html(count($sso_users)); ?></span>
                </div>
            </div>
            <?php if (!empty($sso_users)): ?>
                <div class="aoauth-compact-table-wrap">
                    <table class="aoauth-logs-table">
                        <thead><tr><th><?php esc_html_e('User', 'aoauth-client-sso'); ?></th><th><?php esc_html_e('Provider', 'aoauth-client-sso'); ?></th><th><?php esc_html_e('Last SSO Login', 'aoauth-client-sso'); ?></th></tr></thead>
                        <tbody>
                            <?php foreach ($sso_users as $sso_user): ?>
                                <tr>
                                    <td><?php echo esc_html($sso_user->display_name ?: $sso_user->user_login); ?></td>
                                    <td><?php echo esc_html(get_user_meta($sso_user->ID, '_aoauth_provider', true)); ?></td>
                                    <td><?php $last_login = get_user_meta($sso_user->ID, '_aoauth_last_login', true); echo $last_login ? esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), (int) $last_login)) : '-'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
        <p class="aoauth-note"><?php esc_html_e('Leave the backup password blank to exclude credentials. Enter a password to include encrypted secrets.', 'aoauth-client-sso'); ?></p>
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
