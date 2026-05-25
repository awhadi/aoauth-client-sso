<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Keycloak {
    public function get_name() {
        return 'keycloak';
    }
    
    public function get_label() {
        return 'Keycloak';
    }
    
    public function get_description() {
        return 'Authenticate with Keycloak identity provider.';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile');
    }
    
    public function get_authorization_endpoint() {
        return 'https://example.com/realms/realmname/protocol/openid-connect/auth';
    }
    
    public function get_token_endpoint() {
        return 'https://example.com/realms/realmname/protocol/openid-connect/token';
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