=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional OAuth 2.0 and OpenID Connect Single Sign-On client for WordPress.

== Description ==

aOAUTH Client SSO provides OAuth 2.0 and OpenID Connect login support for WordPress with configurable providers, login button themes, account linking, bot verification, logging, and administrator tools.

== Developer Summary ==

Version: 2.4.6
Date: 2026-06-04
Author: Awhadi

Summary:
This release separates theme styling from layout behavior. Themes keep responsibility for button shape, color, login overlay color, and account-linking styling, while layout settings control only arrangement. Wrap Centered now works for all current and future themes as a centered wrapping block, Compact Row wraps instead of overlapping when many providers are enabled, and additional front-end/admin JavaScript labels and helper states are localized.

Files changed:
- aoauth-client-sso.php
- admin/class-admin.php
- admin/css/admin-style.css
- admin/js/admin-dashboard.js
- includes/class-core.php
- public/css/login-single-sign-on.css
- public/css/themes/icon-only.css
- public/css/themes/icon-aurora.css
- public/css/themes/icon-sunset.css
- public/css/themes/icon-neon.css
- public/js/login-single-sign-on.js
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
- Full Width Stack and Two Columns intentionally stretch buttons because those layout modes require full column width.
- Wrap Centered and Compact Row preserve theme-defined button dimensions and only control wrapping/alignment.
- Compact Row now wraps onto additional rows when provider count exceeds available width, avoiding overlap.
- The plugin loads the aoauth-client-sso text domain from /languages, and the gettext template/compiled locale files were regenerated.

Rollback plan:
Restore version 2.4.5 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. If layout behavior needs immediate rollback, restore public/css/login-single-sign-on.css and admin/css/admin-style.css from the 2.4.5 tag.

== Changelog ==

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
