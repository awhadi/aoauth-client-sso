=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional OAuth 2.0 and OpenID Connect Single Sign-On client for WordPress.

== Description ==

aOAUTH Client SSO provides OAuth 2.0 and OpenID Connect login support for WordPress with configurable providers, login button themes, account linking, bot verification, logging, and administrator tools.

== Developer Summary ==

Version: 2.4.4
Date: 2026-06-04
Author: Awhadi

Summary:
This release removes the current-user bot verification clearing shortcode, improves Session Management help text, stages Deep Debug changes until Save Tools Settings is clicked, restores clear SSO unlink controls on the Users screen, expands the Sign-In Experience preview, fixes Wrap Centered login button sizing, and removes duplicate provider wizard test notifications.

Files changed:
- aoauth-client-sso.php
- admin/class-admin.php
- admin/css/admin-style.css
- admin/js/admin-dashboard.js
- admin/js/wizard-script.js
- admin/views/sign-in-experience-settings.php
- admin/views/tools-settings.php
- includes/class-debug.php
- includes/class-sso-handler.php
- public/css/login-single-sign-on.css
- public/js/account-unlink.js
- CHANGELOG.md
- readme.txt

Security/UX notes:
- Deep Debug remains controlled through wp-config.php constants and is not saved as a normal plugin setting.
- Deep Debug toggle changes are applied only after Save Tools Settings succeeds.
- Debug log directory protection now includes Apache 2.4 denial rules while retaining older Apache/LiteSpeed fallback rules.
- Nginx does not read .htaccess files; block direct access to /wp-content/uploads/aoauth-debug/ in the Nginx server configuration.
- Admin-only global bot verification cleanup remains available in Tools.
- The WordPress Users screen now separates SSO provider display from unlink actions and keeps bulk unlink support.

Rollback plan:
Restore version 2.4.3 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. If Deep Debug was enabled during troubleshooting, remove define("OAUTH-DEBUG", "enabled"); or define("AOAUTH_DEBUG", true); from wp-config.php before rollback when debug logging is no longer needed.

== Changelog ==

= 2.4.4 =
* Removed current-user bot verification clearing shortcode and related profile/front-end UI.
* Added Session Management helper text with practical examples.
* Staged Deep Debug changes until Save Tools Settings is clicked.
* Restored explicit SSO Actions unlink buttons on the WordPress Users screen.
* Expanded the Sign-In Experience preview.
* Fixed Wrap Centered login button sizing inside wp-login.php.
* Removed duplicate provider wizard Test Connection toast.

