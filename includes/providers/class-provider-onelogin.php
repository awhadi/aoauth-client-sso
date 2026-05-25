<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_OneLogin {
    public function get_name() {
        return 'onelogin';
    }
    
    public function get_label() {
        return 'OneLogin';
    }
    
    public function get_description() {
        return 'Authenticate with OneLogin identity platform';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://{site}.onelogin.com/oidc/2/auth';
    }
    
    public function get_token_endpoint() {
        return 'https://{site}.onelogin.com/oidc/2/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://{site}.onelogin.com/oidc/2/me';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://{site}.onelogin.com/oidc/2/.well-known/openid-configuration';
    }
}