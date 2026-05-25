<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Okta {
    public function get_name() {
        return 'okta';
    }
    
    public function get_label() {
        return 'Okta';
    }
    
    public function get_description() {
        return 'Authenticate with Okta identity platform';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://{yourDomain}/oauth2/v1/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://{yourDomain}/oauth2/v1/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://{yourDomain}/oauth2/v1/userinfo';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://{yourDomain}/.well-known/openid-configuration';
    }
}