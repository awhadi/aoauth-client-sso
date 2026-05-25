<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Provider_Microsoft {
    public function get_name() {
        return 'microsoft';
    }
    
    public function get_label() {
        return 'Microsoft';
    }
    
    public function get_description() {
        return 'Authenticate with Microsoft/Azure AD accounts';
    }
    
    public function get_default_scopes() {
        return array('openid', 'email', 'profile', 'User.Read');
    }
    
    public function get_authorization_endpoint() {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    }
    
    public function get_token_endpoint() {
        return 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    }
    
    public function get_userinfo_endpoint() {
        return 'https://graph.microsoft.com/v1.0/me';
    }
    
    public function supports_discovery() {
        return true;
    }
    
    public function get_discovery_url() {
        return 'https://login.microsoftonline.com/common/v2.0/.well-known/openid-configuration';
    }
}