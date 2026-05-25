<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_GitHub {
    public function get_name() {
        return 'github';
    }
    
    public function get_label() {
        return 'GitHub';
    }
    
    public function get_description() {
        return 'Authenticate with GitHub accounts';
    }
    
    public function get_default_scopes() {
        return array('user:email', 'read:user');
    }
    
    public function get_authorization_endpoint() {
        return 'https://github.com/login/oauth/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://github.com/login/oauth/access_token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://api.github.com/user';
    }
    
    public function supports_discovery() {
        return false;
    }
    
    public function get_discovery_url() {
        return '';
    }
}