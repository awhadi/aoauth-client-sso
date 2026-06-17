<!--
=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
-->

<h1 align="center">aOAUTH Client SSO</h1>

<p align="center">
  <strong>OAuth 2.0 and OpenID Connect Single Sign-On for WordPress</strong><br>
  Let users sign in to WordPress with trusted identity providers instead of relying only on local passwords.
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
    <img src="https://img.shields.io/badge/Changelog-2.6.7-brightgreen" alt="Changelog">
  </a>
</p>

---

## Overview

**aOAUTH Client SSO** is a WordPress Single Sign-On plugin for OAuth 2.0 and OpenID Connect providers. It helps site owners connect WordPress login to providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, WordPress, and custom identity providers.

The plugin acts as an OAuth/OIDC client, sends users to the selected provider for authentication, validates the callback, and signs the matched WordPress user in. It also supports account linking, optional user provisioning, role and attribute mapping, bot verification, login-button themes, provider testing, logs, and admin-managed authentication settings.

---

## Key Features

- **OAuth 2.0 and OIDC support** for standard-compliant identity providers.
- **Preconfigured providers** for common services including Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, and Apple.
- **Custom provider setup** with configurable authorization, token, userinfo, JWKS, issuer, and discovery endpoints.
- **Secure login flow controls** including state, nonce, PKCE, redirect validation, and provider endpoint validation.
- **Account linking** so existing WordPress users can connect SSO identities to their accounts.
- **Optional user provisioning** with configurable default role and role mapping.
- **Silent auto-login for linked OIDC sessions** when explicitly enabled by the administrator.
- **Bot verification support** with Cloudflare Turnstile and Google reCAPTCHA.
- **Sign-in experience controls** for button layout, themes, previews, and account-linking pages.
- **Admin tools** for logs, backup and restore, temporary session cleanup, and safe uninstall cleanup.
- **Bundled translations** for German, Dari Afghanistan, French, Russian, Turkish, Chinese, and Japanese.

---

## Supported Providers

| Provider | Type | Status |
| :--- | :--- | :--- |
| Google | OAuth 2.0 / OIDC | Preconfigured |
| Microsoft | OAuth 2.0 / OIDC | Preconfigured |
| GitHub | OAuth 2.0 | Preconfigured |
| Keycloak | OIDC | Preconfigured |
| Auth0 | OIDC | Preconfigured |
| Okta | OIDC | Preconfigured |
| OneLogin | OIDC | Preconfigured |
| GitLab | OAuth 2.0 / OIDC | Preconfigured |
| Facebook | OAuth 2.0 | Preconfigured |
| LinkedIn | OAuth 2.0 / OIDC | Preconfigured |
| Apple | OIDC | Preconfigured |
| Custom / Generic | OAuth 2.0 / OIDC | Fully configurable |

---

## Installation

### From WordPress Admin

1. Go to **Plugins > Add New**.
2. Search for `aOAUTH Client SSO`.
3. Install and activate the plugin.
4. Open **OAUTH SSO** in the WordPress admin menu.

### Manual Installation

1. Download the latest plugin ZIP.
2. Go to **Plugins > Add New > Upload Plugin**.
3. Upload the ZIP file and activate it.
4. Open **OAUTH SSO** in the WordPress admin menu.

---

## Configuration

1. Add or edit a provider in the setup wizard.
2. Register your WordPress site as an application in the provider dashboard.
3. Use the callback URL shown by the plugin.
4. Enter the Client ID, Client Secret, scopes, and endpoints.
5. Save the provider and test the connection.
6. Configure user creation, role mapping, security, and sign-in experience settings as needed.

For OIDC providers, use discovery or provide issuer and JWKS metadata so identity tokens can be validated safely.

---

## Screenshots

| Login Screen | Providers List | Sign-In Experience |
| :---: | :---: | :---: |
| <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/login-screen.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/login-screen.png" alt="Login Screen" width="250"></a> | <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/providers-list.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/providers-list.png" alt="Providers List" width="250"></a> | <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/sign-in-experience.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/sign-in-experience.png" alt="Sign-In Experience" width="250"></a> |

| User Management | Security | Logs |
| :---: | :---: | :---: |
| <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/user-management.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/user-management.png" alt="User Management" width="250"></a> | <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/security.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/security.png" alt="Security" width="250"></a> | <a href="https://plugins.awhadi.online/aoauth-client-sso/screenshot/logs.png"><img src="https://plugins.awhadi.online/aoauth-client-sso/screenshot/logs.png" alt="Logs" width="250"></a> |

---

## Changelog

### [2.6.7] - 2026-06-09

- Replaced first-provider auto redirects with silent OIDC auto-login checks.
- Limited silent auto-login to already linked WordPress users; silent checks never create users or link accounts.
- Added hidden-iframe silent checks with `prompt=none` for supported OIDC providers.
- Updated bundled translations for the new silent auto-login labels.

For the full changelog, see [CHANGELOG.md](CHANGELOG.md).

---

## FAQ

**Does the plugin create WordPress users automatically?**

Only when the existing user creation setting is enabled and the user manually starts an SSO login. Silent auto-login never creates users.

**Does silent auto-login redirect everyone to the first provider?**

No. When enabled, it checks supported OIDC providers in the background and only logs in already linked WordPress users with an active provider session.

**Can users still use normal WordPress login?**

Yes. The plugin adds SSO login options and does not remove the standard WordPress username and password form by default.

**Does it support custom identity providers?**

Yes. Custom OAuth 2.0 and OIDC providers can be configured with their own endpoints and scopes.

---

## Security

Use HTTPS in production and configure providers with the correct redirect URI, issuer, scopes, and signing metadata. Client secrets and bot protection secret keys are stored encrypted in WordPress options.

---

## License

Distributed under the GNU General Public License v2.0 or later. See [LICENSE](LICENSE) for details.

---

## Support

- Plugin page: [WordPress.org](https://wordpress.org/plugins/aoauth-client-sso/)
- Issues: [GitHub Issues](https://github.com/awhadi/aoauth-client-sso/issues)
- Author: [Awhadi](https://github.com/awhadi)
