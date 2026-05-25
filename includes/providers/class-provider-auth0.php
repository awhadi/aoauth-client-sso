<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Auth0 {
    public function get_name() {
        return 'auth0';
    }
    
    public function get_label() {
        return 'Auth0';
    }
    
    public function get_description() {
        return 'Authenticate with Auth0 identity platform';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://{yourDomain}/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://{yourDomain}/oauth/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://{yourDomain}/userinfo';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://{yourDomain}/.well-known/openid-configuration';
    }
}