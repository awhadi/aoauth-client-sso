=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional OAuth 2.0 and OpenID Connect Single Sign-On client for WordPress.

== Description ==

aOAUTH Client SSO provides OAuth 2.0 and OpenID Connect login support for WordPress with configurable providers, login button themes, account linking, bot verification, logging, and administrator tools.

== Developer Summary ==

Version: 2.4.9
Date: 2026-06-05
Author: Awhadi

Summary:
This release adds optional auto-login from an existing browser SSO provider session and always redirects already logged-in WordPress users away from the primary login screen. Normal SSO buttons and account-linking flows continue to work when auto-login is disabled.

Files changed:
- aoauth-client-sso.php
- admin/class-admin.php
- admin/views/sign-in-experience.php
- includes/class-core.php
- includes/class-sso-handler.php
- languages/aoauth-client-sso.pot
- languages/aoauth-client-sso-de_DE.po
- languages/aoauth-client-sso-de_DE.mo
- languages/aoauth-client-sso-fa_AF.po
- languages/aoauth-client-sso-fa_AF.mo
- languages/aoauth-client-sso-fr_FR.po
- languages/aoauth-client-sso-fr_FR.mo
- languages/aoauth-client-sso-ru_RU.po
- languages/aoauth-client-sso-ru_RU.mo
- languages/aoauth-client-sso-tr_TR.po
- languages/aoauth-client-sso-tr_TR.mo
- languages/aoauth-client-sso-zh_CN.po
- languages/aoauth-client-sso-zh_CN.mo
- languages/aoauth-client-sso-ja.po
- languages/aoauth-client-sso-ja.mo
- CHANGELOG.md
- readme.txt

Security/UX notes:
- Auto-login cannot read third-party provider cookies directly; it starts one normal authorization attempt with the first enabled provider. If the provider already has a browser session, the provider returns immediately and WordPress signs the user in.
- A short browser cookie prevents repeated auto-login loops when the provider session is missing or rejected.
- Already logged-in users are redirected away from the primary login screen and direct SSO login initiation is blocked for existing WordPress sessions, while account-linking requests remain allowed.

Rollback plan:
Restore version 2.4.8 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only the login-flow changes, restore includes/class-sso-handler.php, includes/class-core.php, admin/class-admin.php, and admin/views/sign-in-experience.php from the 2.4.8 tag.

== Changelog ==

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
