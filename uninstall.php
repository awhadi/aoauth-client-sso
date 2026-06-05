<?php
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = get_option('aoauth_settings', array());
if (empty($settings['delete_data_on_uninstall'])) {
    return;
}

global $wpdb;

$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aoauth_logs");

delete_option('aoauth_settings');
delete_option('aoauth_applications');
delete_option('aoauth_version');
delete_option('aoauth_encryption_key');

$meta_prefix = $wpdb->esc_like('_aoauth_') . '%';
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $meta_prefix));
