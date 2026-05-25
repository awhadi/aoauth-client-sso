<?php
if (!defined('ABSPATH')) {
    exit;
}

class AOAUTH_Providers_Manager {
    private $providers = array();
    
    public function __construct() {
        $this->load_providers();
    }
    
    private function load_providers() {
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-google.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-microsoft.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-facebook.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-github.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-gitlab.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-okta.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-onelogin.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-auth0.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-keycloak.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-apple.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-linkedin.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-generic-oidc.php';
        require_once AOAUTH_PLUGIN_DIR . 'includes/providers/class-provider-custom-oauth.php';
        
        $this->register_provider(new AOAUTH_Provider_Google());
        $this->register_provider(new AOAUTH_Provider_Microsoft());
        $this->register_provider(new AOAUTH_Provider_Facebook());
        $this->register_provider(new AOAUTH_Provider_GitHub());
        $this->register_provider(new AOAUTH_Provider_GitLab());
        $this->register_provider(new AOAUTH_Provider_Okta());
        $this->register_provider(new AOAUTH_Provider_OneLogin());
        $this->register_provider(new AOAUTH_Provider_Auth0());
        $this->register_provider(new AOAUTH_Provider_Keycloak());
        $this->register_provider(new AOAUTH_Provider_Apple());
        $this->register_provider(new AOAUTH_Provider_LinkedIn());
        $this->register_provider(new AOAUTH_Provider_Generic_OIDC());
        $this->register_provider(new AOAUTH_Provider_Custom_OAuth());
    }
    
    public function register_provider($provider) {
        $this->providers[$provider->get_name()] = $provider;
    }
    
    public function get_providers() {
        return $this->providers;
    }
    
    public function get_provider($name) {
        return $this->providers[$name] ?? null;
    }
    
    public function get_providers_list() {
        $list = array();
        
        foreach ($this->providers as $name => $provider) {
            $list[] = array(
                'name' => $name,
                'label' => $provider->get_label(),
                'description' => $provider->get_description(),
                'default_scopes' => $provider->get_default_scopes(),
                'authorization_endpoint' => $provider->get_authorization_endpoint(),
                'token_endpoint' => $provider->get_token_endpoint(),
                'userinfo_endpoint' => $provider->get_userinfo_endpoint(),
                'supports_discovery' => $provider->supports_discovery(),
                'icon' => $this->get_provider_icon($name)
            );
        }
        
        return $list;
    }
    
    private function get_provider_icon($name) {
        return AOAUTH_PLUGIN_URL . 'admin/images/providers/' . $name . '.png';
    }
}