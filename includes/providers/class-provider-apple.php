<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Apple {
    public function get_name() {
        return 'apple';
    }
    
    public function get_label() {
        return 'Apple';
    }
    
    public function get_description() {
        return 'Authenticate with Apple ID';
    }
    
    public function get_default_scopes() {
        return array('name', 'email');
    }
    
    public function get_authorization_endpoint() {
        return 'https://appleid.apple.com/auth/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://appleid.apple.com/auth/token';
    }
    
    public function get_userinfo_endpoint() {
        return '';
    }
    
    public function supports_discovery() {
        return false;
    }
    
    public function get_discovery_url() {
        return 'https://appleid.apple.com/.well-known/openid-configuration';
    }
}