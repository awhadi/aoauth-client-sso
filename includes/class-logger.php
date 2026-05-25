<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Logger {
    private $table_name;
    
    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'aoauth_logs';
    }
    
    public function log($event_type, $event_data = array(), $user_id = null, $provider = null, $status = 'info') {
        $settings = get_option('aoauth_settings', array());
        if (empty($settings['enable_logs']) && !in_array($event_type, array('plugin_activated', 'plugin_deactivated', 'settings_updated'))) {
            return false;
        }
        
        global $wpdb;
        $ip_address = $this->get_client_ip();
        
        $data = array(
            'event_type' => $event_type,
            'event_data' => maybe_serialize($this->sanitize_event_data($event_data)),
            'user_id' => $user_id,
            'provider' => $provider,
            'status' => $status,
            'ip_address' => $ip_address,
            'created_at' => current_time('mysql')
        );
        
        $wpdb->insert($this->table_name, $data);
        return $wpdb->insert_id;
    }

    private function sanitize_event_data($event_data) {
        if (!is_array($event_data)) {
            return $event_data;
        }

        $sensitive_fragments = array('password', 'secret', 'token', 'authorization', 'bearer', 'api_key');
        array_walk_recursive($event_data, function(&$value, $key) use ($sensitive_fragments) {
            if ($value === '' || $value === null) {
                return;
            }

            $key = (string) $key;
            if (stripos($key, 'client_id') !== false) {
                $string_value = (string) $value;
                $value = strlen($string_value) > 8 ? substr($string_value, 0, 8) . '****' : '****';
                return;
            }

            foreach ($sensitive_fragments as $fragment) {
                if (stripos($key, $fragment) !== false) {
                    $value = '***HIDDEN***';
                    return;
                }
            }
        });

        return $event_data;
    }
    
    public function get_logs($args = array()) {
        global $wpdb;
        
        $defaults = array(
            'limit' => 100,
            'offset' => 0,
            'orderby' => 'created_at',
            'order' => 'DESC',
            'event_type' => '',
            'provider' => '',
            'status' => '',
            'user_id' => null,
            'date_from' => '',
            'date_to' => ''
        );
        
        $args = wp_parse_args($args, $defaults);
        $where = array('1=1');
        
        if (!empty($args['event_type'])) {
            $where[] = $wpdb->prepare('l.event_type = %s', $args['event_type']);
        }
        if (!empty($args['provider'])) {
            $where[] = $wpdb->prepare('l.provider = %s', $args['provider']);
        }
        if (!empty($args['status'])) {
            $where[] = $wpdb->prepare('l.status = %s', $args['status']);
        }
        if ($args['user_id'] !== null) {
            $where[] = $wpdb->prepare('l.user_id = %d', $args['user_id']);
        }
        if (!empty($args['date_from'])) {
            $where[] = $wpdb->prepare('l.created_at >= %s', $args['date_from']);
        }
        if (!empty($args['date_to'])) {
            $where[] = $wpdb->prepare('l.created_at <= %s', $args['date_to']);
        }
        
        $where_clause = implode(' AND ', $where);
        $allowed_orderby = array(
            'id' => 'l.id',
            'event_type' => 'l.event_type',
            'provider' => 'l.provider',
            'status' => 'l.status',
            'user_id' => 'l.user_id',
            'ip_address' => 'l.ip_address',
            'created_at' => 'l.created_at',
        );
        $allowed_order = array('ASC', 'DESC');
        $order = strtoupper($args['order']);
        if (!in_array($order, $allowed_order)) $order = 'DESC';
        $orderby_key = sanitize_key($args['orderby']);
        $orderby_column = isset($allowed_orderby[$orderby_key]) ? $allowed_orderby[$orderby_key] : 'l.created_at';
        $orderby_clause = $orderby_column . ' ' . $order;
        
        $query = $wpdb->prepare(
            "SELECT l.*, u.user_login as username FROM {$this->table_name} l 
             LEFT JOIN {$wpdb->users} u ON l.user_id = u.ID 
             WHERE {$where_clause} ORDER BY {$orderby_clause} LIMIT %d OFFSET %d",
            $args['limit'],
            $args['offset']
        );
        
        return $wpdb->get_results($query);
    }
    
    public function get_log_count($args = array()) {
        global $wpdb;
        $where = array('1=1');
        if (!empty($args['event_type'])) $where[] = $wpdb->prepare('event_type = %s', $args['event_type']);
        if (!empty($args['provider'])) $where[] = $wpdb->prepare('provider = %s', $args['provider']);
        if (!empty($args['status'])) $where[] = $wpdb->prepare('status = %s', $args['status']);
        if (!empty($args['user_id'])) $where[] = $wpdb->prepare('user_id = %d', $args['user_id']);
        if (!empty($args['date_from'])) $where[] = $wpdb->prepare('created_at >= %s', $args['date_from']);
        if (!empty($args['date_to'])) $where[] = $wpdb->prepare('created_at <= %s', $args['date_to']);
        $where_clause = implode(' AND ', $where);
        return $wpdb->get_var("SELECT COUNT(*) FROM {$this->table_name} WHERE {$where_clause}");
    }
    
    public function clear_logs() {
        global $wpdb;
        $wpdb->query("TRUNCATE TABLE {$this->table_name}");
        $this->log('logs_cleared', array(), get_current_user_id(), null, 'info');
    }
    
    private function get_client_ip() {
        $ip = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) $ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        elseif (!empty($_SERVER['REMOTE_ADDR'])) $ip = $_SERVER['REMOTE_ADDR'];
        return sanitize_text_field($ip);
    }
    
    public function export_logs() {
        $logs = $this->get_logs(array('limit' => 5000));
        $csv_data = array();
        $csv_data[] = array('ID', 'Event Type', 'Status', 'Provider', 'Username', 'User ID', 'IP Address', 'Date', 'Data');
        foreach ($logs as $log) {
            $username = isset($log->username) ? $log->username : ( $log->user_id ? 'user_' . $log->user_id : '-' );
            $csv_data[] = array(
                $log->id,
                $log->event_type,
                $log->status,
                $log->provider,
                $username,
                $log->user_id,
                $log->ip_address,
                $log->created_at,
                maybe_serialize($log->event_data)
            );
        }
        return $csv_data;
    }
    
    public function delete_old_logs() {
        global $wpdb;
        $settings = get_option('aoauth_settings', array());
        
        $period = isset($settings['logs_retention_period']) ? $settings['logs_retention_period'] : '30_days';
        
        if ($period === 'forever') {
            return;
        }
        
        $days = 30;
        switch ($period) {
            case '7_days': $days = 7; break;
            case '14_days': $days = 14; break;
            case '30_days': $days = 30; break;
            case '60_days': $days = 60; break;
            case '90_days': $days = 90; break;
            case '6_months': $days = 180; break;
            case '1_year': $days = 365; break;
        }
        
        $date = date('Y-m-d H:i:s', strtotime("-$days days"));
        $wpdb->query($wpdb->prepare("DELETE FROM {$this->table_name} WHERE created_at < %s", $date));
        $this->log('old_logs_deleted', array('retention_days' => $days), null, null, 'info');
    }
}
