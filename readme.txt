=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional OAuth 2.0 and OpenID Connect Single Sign-On client for WordPress.

== Description ==

aOAUTH Client SSO provides OAuth 2.0 and OpenID Connect login support for WordPress with configurable providers, login button themes, account linking, bot verification, logging, and administrator tools.

== Developer Summary ==

Version: 2.4.5
Date: 2026-06-04
Author: Awhadi

Summary:
This release adds gettext language support files for German, Dari (Afghanistan), French, Russian, Turkish, Chinese, and Japanese, and fixes public wp-login.php icon-theme sizing when Icon Sunset or another icon theme is used with Wrap Centered layout.

Files changed:
- aoauth-client-sso.php
- public/css/login-single-sign-on.css
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
- The plugin already loads the aoauth-client-sso text domain from /languages, so the new compiled .mo files are available to WordPress locale loading.
- Locale files include translated core admin/login strings and fall back to English for untranslated strings.
- Icon themes now keep fixed theme dimensions on wp-login.php when Wrap Centered layout is selected.

Rollback plan:
Restore version 2.4.4 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. If a locale file causes an unexpected translation issue, remove the matching languages/aoauth-client-sso-LOCALE.mo file or roll back to 2.4.4.

== Changelog ==

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
