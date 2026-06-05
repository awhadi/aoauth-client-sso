=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.11
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Secure OAuth 2.0 and OpenID Connect Single Sign-On for WordPress with Google, Microsoft, GitHub, Keycloak, Auth0, Okta, and custom identity providers.

== Description ==

aOAUTH Client SSO lets users sign in to WordPress with OAuth 2.0 and OpenID Connect identity providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, or any compatible custom OAuth/OIDC provider.

The plugin works with identity providers that follow OAuth 2.0 and OpenID Connect standards, and includes configurable provider setup, secure authorization-code login with PKCE, account linking, role mapping, role-based redirects, login button themes, bot verification, activity logs, backup and restore tools, and multilingual admin settings.

== Developer Summary ==

Version: 2.4.11
Date: 2026-06-05
Author: Awhadi

Summary:
This release improves the WordPress plugin metadata and readme description so the plugin clearly explains its OAuth 2.0/OpenID Connect SSO purpose, supported provider examples, standards compatibility, and main security/UX features.

Files changed:
- aoauth-client-sso.php
- CHANGELOG.md
- readme.txt

Security/UX notes:
- This release changes plugin description copy only and does not alter authentication, provider configuration, styling, localization loading, or data handling behavior.

Rollback plan:
Restore version 2.4.10 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only the description copy, restore aoauth-client-sso.php and readme.txt from the 2.4.10 tag.

== Changelog ==

= 2.4.11 =
* Improved plugin header and readme descriptions for WordPress plugin listing clarity.

= 2.4.10 =
* Added a fa_AF-only locale stylesheet for consistent Dari/Persian typography across plugin UI surfaces.

= 2.4.9 =
* Added optional auto-login from existing browser SSO provider sessions.
* Redirected already logged-in WordPress users away from wp-login.php to prevent second login attempts in the same browser.
* Preserved normal manual SSO buttons and account-linking behavior.

= 2.4.8 =
* Completed visible admin translations for wizard, User Management, Tools, Shortcodes, Danger Zone, and helper text sections.
* Moved single-user SSO unlinking into the SSO Providers column as a text link.
* Renamed settings tab view files to concise names and regenerated gettext files.

= 2.4.7 =
* Completed bundled translations for German, Dari (Afghanistan), French, Russian, Turkish, Chinese, and Japanese.
* Regenerated compiled gettext files for all supported locales.

= 2.4.6 =
* Made login layout rules theme-agnostic.
* Fixed Wrap Centered for all current and future themes.
* Fixed Compact Row overflow by allowing provider buttons to wrap.
* Localized additional front-end and admin JavaScript labels/helper states.

= 2.4.5 =
* Added German, Dari (Afghanistan), French, Russian, Turkish, Chinese, and Japanese gettext files.
* Fixed wp-login.php icon-theme button sizing for Wrap Centered layout.

= 2.4.4 =
* Removed current-user bot verification clearing shortcode and related profile/front-end UI.
* Added Session Management helper text with practical examples.
* Staged Deep Debug changes until Save Tools Settings is clicked.
* Restored explicit SSO Actions unlink buttons on the WordPress Users screen.
* Expanded the Sign-In Experience preview.
* Fixed Wrap Centered login button sizing inside wp-login.php.
* Removed duplicate provider wizard Test Connection toast.
