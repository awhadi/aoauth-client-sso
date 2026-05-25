<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Google {
    public function get_name() {
        return 'google';
    }
    
    public function get_label() {
        return 'Google';
    }
    
    public function get_description() {
        return 'Authenticate with Google accounts';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://accounts.google.com/o/oauth2/v2/auth';
    }
    
    public function get_token_endpoint() {
        return 'https://oauth2.googleapis.com/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://openidconnect.googleapis.com/v1/userinfo';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://accounts.google.com/.well-known/openid-configuration';
    }
}