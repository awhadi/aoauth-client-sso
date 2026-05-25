<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_User_Mapping {
    private $default_mappings = array(
        'google' => array(
            'username' => 'email',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        ),
        'microsoft' => array(
            'username' => 'userPrincipalName',
            'email' => 'mail',
            'display_name' => 'displayName',
            'first_name' => 'givenName',
            'last_name' => 'surname',
            'subject' => 'id'
        ),
        'facebook' => array(
            'username' => 'email',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'first_name',
            'last_name' => 'last_name',
            'subject' => 'id'
        ),
        'github' => array(
            'username' => 'login',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'name',
            'last_name' => '',
            'subject' => 'id'
        ),
        'keycloak' => array(
            'username' => 'preferred_username',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        ),
        'okta' => array(
            'username' => 'preferred_username',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        ),
        'auth0' => array(
            'username' => 'nickname',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        ),
        'linkedin' => array(
            'username' => 'email',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        ),
        'generic' => array(
            'username' => 'email',
            'email' => 'email',
            'display_name' => 'name',
            'first_name' => 'given_name',
            'last_name' => 'family_name',
            'subject' => 'sub'
        )
    );
    
    public function map_user_data($user_info, $app_config) {
        $provider = $app_config['provider_name'];
        $mappings = $this->get_mappings($app_config);
        
        $user_data = array();
        
        foreach ($mappings as $wp_field => $provider_field) {
            if (!empty($provider_field)) {
                $value = AOAUTH_Security::get_nested_value($user_info, $provider_field);
                if (!empty($value)) {
                    $user_data[$wp_field] = $value;
                }
            }
        }
        
        // Only apply fallbacks if values are STILL empty
        if (empty($user_data['username'])) {
            // Try to get username from email or preferred_username
            if (!empty($user_info['preferred_username'])) {
                $user_data['username'] = $user_info['preferred_username'];
            } elseif (!empty($user_data['email'])) {
                $user_data['username'] = $user_data['email'];
            } elseif (!empty($user_info['email'])) {
                $user_data['username'] = $user_info['email'];
            } else {
                $user_data['username'] = 'user_' . wp_generate_password(8, false);
            }
        }
        
        if (empty($user_data['display_name'])) {
            $display_name = trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? ''));
            if (!empty($display_name)) {
                $user_data['display_name'] = $display_name;
            } elseif (!empty($user_info['name'])) {
                $user_data['display_name'] = $user_info['name'];
            } else {
                $user_data['display_name'] = $user_data['username'];
            }
        }
        
        // Merge with original user_info to retain any fields not mapped
        return array_merge($user_info, $user_data);
    }
    
    private function get_mappings($app_config) {
        if (!empty($app_config['enable_advanced_mapping']) && !empty($app_config['attribute_mapping'])) {
            return $app_config['attribute_mapping'];
        }
        
        $provider = $app_config['provider_name'];
        return $this->default_mappings[$provider] ?? $this->default_mappings['generic'];
    }
    
    public function get_default_mappings($provider) {
        return $this->default_mappings[$provider] ?? $this->default_mappings['generic'];
    }
    
    public function get_default_role_mappings($provider) {
        $defaults = array(
            'google' => array(),
            'microsoft' => array(),
            'facebook' => array(),
            'generic' => array()
        );
        
        return $defaults[$provider] ?? array();
    }
}
