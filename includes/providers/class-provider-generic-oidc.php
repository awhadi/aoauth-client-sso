<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Generic_OIDC {
    public function get_name() {
        return 'generic';
    }
    
    public function get_label() {
        return 'Generic OpenID Connect';
    }
    
    public function get_description() {
        return 'Connect to any OpenID Connect compliant provider';
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
        return true;
    }
    
    public function get_discovery_url() {
        return '';
    }
}