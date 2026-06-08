<?php
// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- WordPress uninstall files run in the global scope.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

$settings = get_option('aoauth_settings', array());
if (empty($settings['delete_data_on_uninstall'])) {
    return;
}

global $wpdb;

// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Removes this plugin's custom log table only when the admin opted into uninstall cleanup.
$wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}aoauth_logs");

delete_option('aoauth_settings');
delete_option('aoauth_applications');
delete_option('aoauth_version');
delete_option('aoauth_encryption_key');

$meta_prefix = $wpdb->esc_like('_aoauth_') . '%';
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Removes only this plugin's user meta during opted-in uninstall cleanup.
$wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE %s", $meta_prefix));
