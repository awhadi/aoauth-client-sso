=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.5.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress OAuth/OIDC Single Sign-On for Google, Microsoft, GitHub, Keycloak, Auth0, Okta, WordPress, and custom identity providers.

== Description ==

aOAUTH Client SSO is a WordPress Single Sign-On plugin that allows users to log in to WordPress with OAuth 2.0 and OpenID Connect providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, WordPress, and custom identity providers.

The plugin works with identity providers that conform to the OAuth 2.0 and OpenID Connect (OIDC) standards. Site owners can move away from password-only WordPress login, centralize authentication with existing business or social accounts, connect provider identities to WordPress users, map roles, control post-login redirects, style SSO buttons, and manage authentication from the WordPress admin area.

Automatic plugin updates can be enabled or disabled from Tools. When enabled, WordPress can install new versions of this plugin automatically when an update package is available from the configured plugin update source.

== Developer Summary ==

Version: 2.5.0
Date: 2026-06-05
Author: Awhadi

Summary:
This release improves the WordPress plugin metadata and readme description so the copy clearly explains that the plugin adds OAuth 2.0/OpenID Connect Single Sign-On for WordPress, supports standard-compliant identity providers, and helps replace password-only login with managed provider authentication. It also adds an admin setting for WordPress automatic plugin updates and moves uninstall cleanup to the standard root uninstall.php file.

Files changed:
- aoauth-client-sso.php
- admin/class-admin.php
- admin/views/tools.php
- includes/class-core.php
- languages/aoauth-client-sso-de_DE.po and .mo
- languages/aoauth-client-sso-fa_AF.po and .mo
- languages/aoauth-client-sso-fr_FR.po and .mo
- languages/aoauth-client-sso-ja.po and .mo
- languages/aoauth-client-sso-ru_RU.po and .mo
- languages/aoauth-client-sso-tr_TR.po and .mo
- languages/aoauth-client-sso-zh_CN.po and .mo
- languages/aoauth-client-sso.pot
- uninstall.php
- CHANGELOG.md
- readme.txt

Security/UX notes:
- Authentication, provider configuration, styling, localization loading, and data handling behavior remain unchanged.
- Auto-update opt-in is controlled by the plugin Tools setting, scoped to this plugin basename only, and does not change update settings for other plugins.
- Uninstall cleanup still respects the Delete Plugin Data on Uninstall setting before removing plugin tables, options, and user metadata.

Rollback plan:
Restore version 2.4.11 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only the description copy, restore aoauth-client-sso.php and readme.txt from the 2.4.11 tag. To roll back only auto-update and uninstall handling, restore aoauth-client-sso.php, admin/class-admin.php, admin/views/tools.php, includes/class-core.php, and remove uninstall.php.

== Changelog ==

= 2.5.0 =
* Clarified the plugin description to explain supported OAuth/OIDC provider login, standards compatibility, and the WordPress login problem it solves.
* Added a Tools setting to enable or disable WordPress automatic plugin updates for this plugin.
* Moved uninstall cleanup to the standard root uninstall.php file.

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
