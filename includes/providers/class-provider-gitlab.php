<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_GitLab {
    public function get_name() {
        return 'gitlab';
    }
    
    public function get_label() {
        return 'GitLab';
    }
    
    public function get_description() {
        return 'Authenticate with GitLab accounts';
    }
    
    public function get_default_scopes() {
        return array('openid', 'profile', 'email');
    }
    
    public function get_authorization_endpoint() {
        return 'https://gitlab.com/oauth/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://gitlab.com/oauth/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://gitlab.com/oauth/userinfo';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://gitlab.com/.well-known/openid-configuration';
    }
}