=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 2.4.10
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Professional OAuth 2.0 and OpenID Connect Single Sign-On client for WordPress.

== Description ==

aOAUTH Client SSO provides OAuth 2.0 and OpenID Connect login support for WordPress with configurable providers, login button themes, account linking, bot verification, logging, and administrator tools.

== Developer Summary ==

Version: 2.4.10
Date: 2026-06-05
Author: Awhadi

Summary:
This release adds a Dari Afghanistan locale stylesheet that loads only when WordPress is using `fa_AF`. The stylesheet applies one Persian-capable font family across plugin admin screens, login buttons, shortcodes, verification overlays, and account-linking pages while preserving existing font sizes and weights.

Files changed:
- aoauth-client-sso.php
- admin/class-admin.php
- includes/class-core.php
- includes/class-sso-handler.php
- includes/class-user-manager.php
- public/css/locale-fa-af.css
- CHANGELOG.md
- readme.txt

Security/UX notes:
- The locale stylesheet is loaded only when the active locale is `fa_AF`.
- The stylesheet changes only font family; existing layout, font sizes, weights, and theme colors remain controlled by the existing plugin CSS.
- No external font file is bundled in this release. If a specific `.woff2` font is provided later, it can be added to the same locale stylesheet with `@font-face`.

Rollback plan:
Restore version 2.4.9 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only the locale font change, remove public/css/locale-fa-af.css and restore admin/class-admin.php, includes/class-core.php, includes/class-sso-handler.php, and includes/class-user-manager.php from the 2.4.9 tag.

== Changelog ==

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
