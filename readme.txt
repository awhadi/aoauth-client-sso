=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress OAuth/OIDC Single Sign-On for Google, Microsoft, GitHub, Keycloak, Auth0, Okta, WordPress, and custom identity providers.

== Description ==

aOAUTH Client SSO is a WordPress Single Sign-On plugin that allows users to log in to WordPress with OAuth 2.0 and OpenID Connect providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, WordPress, and custom identity providers.

The plugin works with identity providers that conform to the OAuth 2.0 and OpenID Connect (OIDC) standards. Site owners can move away from password-only WordPress login, centralize authentication with existing business or social accounts, connect provider identities to WordPress users, map roles, control post-login redirects, style SSO buttons, and manage authentication from the WordPress admin area.

Automatic plugin updates can be enabled or disabled from the WordPress Plugins screen. When enabled, WordPress can install new versions of this plugin automatically when an update package is available from the configured plugin update source.

== Developer Summary ==

Version: 2.6.5
Date: 2026-06-08
Author: Awhadi

Summary:
This release finalizes the first Git release package by optimizing bundled provider icon assets and confirming the release tree contains only referenced plugin files.

Files changed:
- aoauth-client-sso.php
- admin/css/admin-style.css
- admin/images/providers
- CHANGELOG.md
- readme.txt

Security/UX notes:
- Provider icons remain bundled locally; only asset size changed.
- No development junk files, hidden macOS metadata files, or obsolete review files are included in the release tree.
- Plugin Check fixes from 2.6.4 remain in place.

Rollback plan:
Restore version 2.6.4 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only this patch, restore admin/css/admin-style.css, admin/images/providers, aoauth-client-sso.php, readme.txt, and CHANGELOG.md from the 2.6.4 tag.

== Changelog ==

= 2.6.5 =
* Optimized bundled provider icon assets for a smaller release package.
* Updated the admin header watermark text for the release build.
* Confirmed tracked release assets are referenced and no extra development files are present in the plugin tree.

= 2.6.4 =
* Fixed Plugin Check findings for hidden files, translator comments, escaping, remote script enqueues, auto-update modification detection, and scoped database/query warnings.
* Preserved optional Turnstile and reCAPTCHA behavior by loading provider APIs from the existing public JavaScript file only when needed.
* Removed forced auto-update enabling on activation while keeping the native Plugins screen auto-update control.

= 2.6.3 =
* Restored callback compatibility for existing OIDC providers whose saved metadata does not include JWKS signing keys.
* Applied built-in OIDC metadata defaults when saved provider names use different casing.

= 2.6.2 =
* Fixed SSO authentication for existing providers by preserving exact provider identifiers during nonce verification and application lookup.
* Fixed account linking, provider toggles, and unlink actions to use the configured provider key instead of a lowercased slug.

= 2.6.1 =
* Fixed Plugin Check findings for escaping, translator comments, request unslashing, generated downloads, hidden files, and stylesheet loading.
* Updated Tested up to to WordPress 7.0.
* Switched OAuth provider redirects to safe redirects with a temporary provider host allowlist.
* Removed direct PHP error logging from the SSO error redirect path.
* Regenerated translation catalogs and compiled locale files for changed strings.

= 2.6.0 =
* Hardened OAuth/OIDC endpoint validation to require public HTTPS endpoints by default.
* Required ID token signing keys in High security mode before accepting ID token claims.
* Consolidated provider subject extraction into a shared helper.
* Merged tiny settings and tab view wrappers into admin render helpers.
* Removed release junk files and ignored .DS_Store files.
* Removed the unused SVG admin menu icon asset.
* Changed the WordPress admin left-menu label from aOAUTH SSO to OAUTH SSO.

= 2.5.1 =
* Moved auto-update management to the native WordPress Plugins screen Enable/Disable auto-updates link.
* Removed the duplicate automatic updates toggle from Tools.
* Changed the admin menu icon to a small color-inheriting "a".

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
