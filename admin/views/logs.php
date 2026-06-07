<?php if (!defined('ABSPATH')) exit;
$aoauth_current_page = 'aoauth-logs';
$aoauth_log_limit = 50;
$aoauth_total_pages = $aoauth_log_limit > 0 ? (int) ceil((int) $total_logs / $aoauth_log_limit) : 1;
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
            <a href="https://awhadi.com/aoauth-client-sso" target="_blank" class="aoauth-feature-btn"><?php esc_html_e('Feature Details', 'aoauth-client-sso'); ?></a>
        </div>
    </div>
    
    <?php $this->render_admin_tabs($aoauth_current_page); ?>
    
    <div class="aoauth-admin-content">
        <?php if (!empty($settings['enable_logs'])): ?>
        <div class="aoauth-logs-header">
            <h3><?php esc_html_e('Authentication Logs', 'aoauth-client-sso'); ?></h3>
            <div class="aoauth-logs-actions">
                <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-refresh-logs-btn">
                    <span class="dashicons dashicons-update"></span>
                    <?php esc_html_e('Refresh', 'aoauth-client-sso'); ?>
                </button>
                <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-clear-logs-btn">
                    <span class="dashicons dashicons-trash"></span>
                    <?php esc_html_e('Clear Logs', 'aoauth-client-sso'); ?>
                </button>
                <button type="button" class="aoauth-admin-button aoauth-admin-button-secondary aoauth-export-logs-btn">
                    <span class="dashicons dashicons-download"></span>
                    <?php esc_html_e('Export CSV', 'aoauth-client-sso'); ?>
                </button>
            </div>
        </div>

        <form class="aoauth-log-filters" id="aoauth-log-filters">
            <input type="text" name="event_type" class="aoauth-form-control" placeholder="<?php esc_attr_e('Event type', 'aoauth-client-sso'); ?>" value="<?php echo esc_attr($filters['event_type'] ?? ''); ?>">
            <input type="text" name="provider" class="aoauth-form-control" placeholder="<?php esc_attr_e('Provider', 'aoauth-client-sso'); ?>" value="<?php echo esc_attr($filters['provider'] ?? ''); ?>">
            <select name="status" class="aoauth-form-control">
                <option value=""><?php esc_html_e('Any status', 'aoauth-client-sso'); ?></option>
                <?php foreach (array('info', 'success', 'warning', 'error') as $status): ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected($filters['status'] ?? '', $status); ?>><?php echo esc_html(ucfirst($status)); ?></option>
                <?php endforeach; ?>
            </select>
            <input type="date" name="date_from" class="aoauth-form-control" value="<?php echo esc_attr($filters['date_from'] ?? ''); ?>">
            <input type="date" name="date_to" class="aoauth-form-control" value="<?php echo esc_attr($filters['date_to'] ?? ''); ?>">
            <button type="submit" class="aoauth-admin-button aoauth-admin-button-secondary"><?php esc_html_e('Filter', 'aoauth-client-sso'); ?></button>
        </form>
        
        <div class="aoauth-log-results-summary">
            <?php echo esc_html(sprintf(
                /* translators: %d: number of log entries found. */
                _n('%d log entry found.', '%d log entries found.', (int) $total_logs, 'aoauth-client-sso'),
                (int) $total_logs
            )); ?>
        </div>

        <div class="aoauth-logs-table-wrap">
            <table class="aoauth-logs-table">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Event', 'aoauth-client-sso'); ?></th>
                        <th><?php esc_html_e('Provider', 'aoauth-client-sso'); ?></th>
                        <th><?php esc_html_e('Status', 'aoauth-client-sso'); ?></th>
                        <th><?php esc_html_e('Username', 'aoauth-client-sso'); ?></th>
                        <th><?php esc_html_e('IP Address', 'aoauth-client-sso'); ?></th>
                        <th><?php esc_html_e('Date', 'aoauth-client-sso'); ?></th>
                    </tr>
                </thead>
                <tbody id="aoauth-logs-tbody">
                    <?php foreach ($logs as $aoauth_log): ?>
                        <tr>
                            <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $aoauth_log->event_type))); ?></td>
                            <td><?php echo esc_html($aoauth_log->provider ?: '-'); ?></td>
                            <td><span class="aoauth-status-badge aoauth-status-<?php echo esc_attr($aoauth_log->status); ?>"><?php echo esc_html(ucfirst($aoauth_log->status)); ?></span></td>
                            <td><?php echo $aoauth_log->username ? esc_html($aoauth_log->username) : ($aoauth_log->user_id ? esc_html($aoauth_log->user_id) : '-'); ?></td>
                            <td><?php echo esc_html($aoauth_log->ip_address); ?></td>
                            <td><?php echo esc_html($aoauth_log->created_at); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div class="aoauth-pagination" id="aoauth-logs-pagination" data-current-page="1" data-total-pages="<?php echo esc_attr($aoauth_total_pages); ?>"></div>
        <?php else: ?>
            <div class="aoauth-notice"><?php esc_html_e('Detailed logs are disabled. Enable them in Settings.', 'aoauth-client-sso'); ?></div>
        <?php endif; ?>
    </div>
</div>
