<?php if (!defined('ABSPATH')) exit;
$current_page = isset($current_page) ? $current_page : (isset($_GET['page']) ? sanitize_key($_GET['page']) : '');
$tabs = array(
    'aoauth-providers' => __('Providers', 'aoauth-client-sso'),
    'aoauth-sign-in-experience' => __('Sign-In Experience', 'aoauth-client-sso'),
    'aoauth-user-management' => __('User Management', 'aoauth-client-sso'),
    'aoauth-security' => __('Security', 'aoauth-client-sso'),
    'aoauth-tools' => __('Tools', 'aoauth-client-sso'),
    'aoauth-logs' => __('Logs', 'aoauth-client-sso'),
);
?>
<div class="aoauth-admin-tabs">
    <?php foreach ($tabs as $page_slug => $label): ?>
        <?php $is_active = $current_page === $page_slug || ($current_page === 'aoauth-settings' && $page_slug === 'aoauth-sign-in-experience'); ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=' . $page_slug)); ?>" class="aoauth-tab <?php echo $is_active ? 'active' : ''; ?>"><?php echo esc_html($label); ?></a>
    <?php endforeach; ?>
</div>
