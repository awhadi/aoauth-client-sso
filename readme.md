<!--
=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
-->

<h1 align="center">aOAUTH Client SSO</h1>

<p align="center">
  <strong>Enterprise-Grade Single Sign-On (SSO) for WordPress</strong><br>
  Seamlessly authenticate users via OAuth 2.0 & OpenID Connect (OIDC).
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/aoauth-client-sso/">
    <img src="https://img.shields.io/wordpress/plugin/v/aoauth-client-sso" alt="WordPress Plugin Version">
  </a>
  <a href="https://wordpress.org/plugins/aoauth-client-sso/">
    <img src="https://img.shields.io/wordpress/plugin/tested/aoauth-client-sso" alt="WordPress Tested Up To">
  </a>
  <a href="https://wordpress.org/plugins/aoauth-client-sso/">
    <img src="https://img.shields.io/wordpress/plugin/dt/aoauth-client-sso" alt="WordPress Plugin Downloads">
  </a>
  <a href="https://www.gnu.org/licenses/gpl-2.0.html">
    <img src="https://img.shields.io/badge/License-GPL%20v2-blue.svg" alt="License: GPL v2">
  </a>
  <a href="https://php.net">
    <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4" alt="PHP Version">
  </a>
  <a href="https://github.com/awhadi/aoauth-client-sso/blob/main/CHANGELOG.md">
    <img src="https://img.shields.io/badge/Changelog-2.6.6-brightgreen" alt="Changelog">
  </a>
</p>

---

## 📖 Overview

**aOAUTH Client SSO** eliminates the need for traditional password-based logins by integrating WordPress with leading OAuth 2.0 and OpenID Connect providers. Centralize your authentication logic, enforce enterprise-grade security policies, and deliver a frictionless "One-Click" login experience for your users—whether they are internal employees, partners, or customers.

This plugin acts as an OAuth/OIDC client, redirecting users to your chosen Identity Provider (IdP) for authentication and safely handling the callback to create or log them into your WordPress site.

---

## ✨ Key Features

- **Universal OAuth 2.0 / OIDC Support** – Compatible with any provider that follows the OAuth 2.0 or OpenID Connect specifications.
- **Pre-configured Enterprise Providers** – Skip complex setups for Google, Microsoft (Azure/Office 365), GitHub, Keycloak, Auth0, and Okta.
- **Custom Provider Support** – Define custom endpoints (Authorize, Token, UserInfo, Revoke) for bespoke or legacy identity systems.
- **Automatic User Provisioning** – Automatically creates new WordPress user accounts upon first successful SSO login.
- **Role & Attribute Mapping** – Map IdP claims (e.g., `groups`, `roles`) to WordPress user roles during authentication.
- **Secure State & Nonce Handling** – Implements industry-standard CSRF protection and PKCE (Proof Key for Code Exchange) where supported.
- **Existing User Linking** – Allows existing WordPress users to link their SSO identity to their local account.
- **Admin Controlled SSO Enforcement** – Optionally restrict login to SSO-only, disabling the default WordPress login form.

---

## ☁️ Supported Providers

| Provider | Type | Status |
| :--- | :--- | :--- |
| **Google** | OAuth 2.0 / OIDC | ✅ Pre-configured |
| **Microsoft (Azure AD / Live)** | OAuth 2.0 / OIDC | ✅ Pre-configured |
| **GitHub** | OAuth 2.0 | ✅ Pre-configured |
| **Keycloak** | OIDC | ✅ Pre-configured |
| **Auth0** | OIDC | ✅ Pre-configured |
| **Okta** | OIDC | ✅ Pre-configured |
| **Custom / Generic** | OAuth 2.0 / OIDC | ⚙️ Fully Configurable |

> **Need another provider?** Open an issue or submit a PR to add a pre-configured template!

---

## 🚀 Quick Start (Installation)

### From WordPress Admin Dashboard
1. Navigate to **Plugins → Add New**.
2. Search for `"aOAUTH Client SSO"`.
3. Click **Install Now** and then **Activate**.

### Manual Installation
1. Download the latest release `.zip` from the [WordPress Plugin Repository](https://wordpress.org/plugins/aoauth-client-sso/) or [GitHub Releases](https://github.com/awhadi/aoauth-client-sso/releases).
2. Go to **Plugins → Add New → Upload Plugin**.
3. Upload the `.zip` file and activate it.

---

## ⚙️ Configuration Guide

Once activated, navigate to **Settings → aOAUTH SSO** to start configuring your provider.

### Step 1: Choose a Provider
Select one of the pre-built providers (e.g., Google, Microsoft) or select **"Custom"** to enter your own endpoints.

### Step 2: Register Your Application (IdP Side)
You must register your WordPress site as a client application with your chosen IdP. Generally, you will need:

- **Redirect / Callback URI:** `https://[YOUR_DOMAIN]/?oauth=callback`  
  *(or the specific callback URL provided in the plugin settings page).*
- **Client ID** & **Client Secret** (provided by the IdP after registration).

### Step 3: Enter Credentials in WordPress
Paste the `Client ID` and `Client Secret` into the respective fields in the plugin settings. For OpenID Connect providers, you can usually leave the discovery URL to auto-configure endpoints.

### Step 4: Map Scopes & Claims (Optional)
Define which OAuth scopes (e.g., `openid`, `profile`, `email`) you require and map the returned claims (e.g., `email`, `displayName`) to WordPress fields.

### Step 5: Save & Test
Click **Save Changes**, then use the **"Test Login"** button (or log out and visit `/wp-login.php`) to verify the SSO flow.

---

## 📸 Screenshots

<!-- Replace the placeholders with actual image URLs from your plugin assets or GitHub repo -->
| Admin Settings Panel | Provider Selection | User Login Screen |
| :---: | :---: | :---: |
| <img src="https://plugin.awhadi.online/aoauth-client-sso/screenshots/admin-settings.png" alt="Admin Settings" width="250"> | <img src="https://plugin.awhadi.online/aoauth-client-sso/screenshots/provider-selection.png" alt="Provider Selection" width="250"> | <img src="https://plugin.awhadi.online/aoauth-client-sso/screenshots/login-screen.png" alt="Login Screen" width="250"> |

---

## 🛠️ Development & Contributing

We welcome contributions from the community! Whether it's a bug fix, a new feature, or a pre-configured provider template, please follow these steps:

1. Fork the repository on [GitHub](https://github.com/awhadi/aoauth-client-sso).
2. Create a new branch for your feature (`git checkout -b feature/amazing-feature`).
3. Ensure your code follows the WordPress Coding Standards and includes inline documentation.
4. Commit your changes (`git commit -m 'Add amazing feature'`).
5. Push to the branch (`git push origin feature/amazing-feature`).
6. Open a Pull Request against the `main` branch.

### 🧪 Development Requirements
- WordPress 5.8+
- PHP 7.4+ (PHP 8.x recommended)
- Composer (for dependency management)

---

## 📜 Changelog

### [2.6.6] - 2026-06-17
- **Security**: Enhanced nonce validation for callback requests.
- **Fix**: Resolved a PHP warning regarding undefined array keys when using custom scopes.
- **Improvement**: Updated OIDC discovery endpoint parsing for better compatibility with Azure AD v2.0.
- **Localization**: Added new translation strings for Brazilian Portuguese (pt_BR).

For the full changelog, see [CHANGELOG.md](CHANGELOG.md).

---

## ❓ FAQ

**Does this work with multi-site (WordPress Network) installations?**  
Yes, the plugin is fully compatible with WordPress Multisite. You can activate it network-wide and configure SSO for individual subsites or globally.

**Can users still use their local WordPress passwords?**  
Yes. By default, SSO is an alternative method. You can optionally enable **"Force SSO"** in the settings to disable local password logins.

**What happens if the OAuth provider is unavailable?**  
If the provider is down, users will not be able to log in via SSO. It is recommended to keep a local admin account with a strong password for emergency recovery (the plugin disables SSO for administrators by default if enabled).

---

## 🔒 Security & Disclaimer

This plugin is provided "as is" without warranty of any kind. While we take security seriously (including implementing CSRF, nonce, and proper input sanitization), you are responsible for securely storing your Client Secrets and ensuring your IdP is properly configured. Always use **HTTPS** in production environments.

---

## 📄 License

Distributed under the **GNU General Public License v2.0 or later**.  
See [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) for more information.

---

## 💬 Support

- **Bug Reports & Feature Requests:** [GitHub Issues](https://github.com/awhadi/aoauth-client-sso/issues)
- **Plugin Page:** [WordPress.org](https://wordpress.org/plugins/aoauth-client-sso/)
- **Author:** [Awhadi](https://github.com/awhadi)

---

<p align="center">
  Made with ❤️ for the WordPress Community.
</p>
