<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Facebook {
    public function get_name() {
        return 'facebook';
    }
    
    public function get_label() {
        return 'Facebook';
    }
    
    public function get_description() {
        return 'Authenticate with Facebook accounts';
    }
    
    public function get_default_scopes() {
        return array('email', 'public_profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://www.facebook.com/v18.0/dialog/oauth';
    }
    
    public function get_token_endpoint() {
        return 'https://graph.facebook.com/v18.0/oauth/access_token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://graph.facebook.com/me?fields=id,name,email,first_name,last_name';
    }
    
    public function supports_discovery() {
        return false;
    }
    
    public function get_discovery_url() {
        return '';
    }
}