<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_LinkedIn {
    public function get_name() {
        return 'linkedin';
    }
    
    public function get_label() {
        return 'LinkedIn';
    }
    
    public function get_description() {
        return 'Authenticate with LinkedIn accounts';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://www.linkedin.com/oauth/v2/authorization';
    }
    
    public function get_token_endpoint() {
        return 'https://www.linkedin.com/oauth/v2/accessToken';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://api.linkedin.com/v2/userinfo';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://www.linkedin.com/oauth/.well-known/openid-configuration';
    }
}