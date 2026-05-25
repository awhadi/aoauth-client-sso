<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Custom_OAuth {
    public function get_name() {
        return 'custom';
    }
    
    public function get_label() {
        return 'Custom OAuth 2.0';
    }
    
    public function get_description() {
        return 'Connect to any OAuth 2.0 compliant provider';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return '';
    }
    
    public function get_token_endpoint() {
        return '';
    }
    
    public function get_userinfo_endpoint() {
        return '';
    }
    
    public function supports_discovery() {
        return false;
    }
    
    public function get_discovery_url() {
        return '';
    }
}