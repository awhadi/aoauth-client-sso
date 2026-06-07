=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

WordPress OAuth/OIDC Single Sign-On for Google, Microsoft, GitHub, Keycloak, Auth0, Okta, WordPress, and custom identity providers.

== Description ==

aOAUTH Client SSO is a WordPress Single Sign-On plugin that allows users to log in to WordPress with OAuth 2.0 and OpenID Connect providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, WordPress, and custom identity providers.

The plugin works with identity providers that conform to the OAuth 2.0 and OpenID Connect (OIDC) standards. Site owners can move away from password-only WordPress login, centralize authentication with existing business or social accounts, connect provider identities to WordPress users, map roles, control post-login redirects, style SSO buttons, and manage authentication from the WordPress admin area.

Automatic plugin updates can be enabled or disabled from the WordPress Plugins screen. When enabled, WordPress can install new versions of this plugin automatically when an update package is available from the configured plugin update source.

== Developer Summary ==

Version: 2.6.1
Date: 2026-06-07
Author: Awhadi

Summary:
This release resolves Plugin Check findings before WordPress.org review while preserving the existing SSO behavior. It tightens request sanitization, escaping, translation comments, generated download output, stylesheet printing, safe redirects, tested WordPress metadata, and scoped code-standard annotations for intentional OAuth/debug/custom-table behavior.

Files changed:
- admin/class-admin.php
- admin/views/logs.php
- aoauth-client-sso.php
- includes/class-core.php
- includes/class-debug.php
- includes/class-logger.php
- includes/class-oauth-client.php
- includes/class-security.php
- includes/class-sso-handler.php
- includes/class-user-manager.php
- CHANGELOG.md
- readme.txt

Security/UX notes:
- Plugin Check cleanup keeps the active OAuth, account-linking, bot verification, logging, and admin workflows intact.
- OAuth provider redirects now use wp_safe_redirect with a temporary allowlist for the selected provider host.
- Account-linking standalone pages print registered WordPress styles instead of raw stylesheet tags.
- Request data reads now consistently unslash and sanitize before use.
- OAuth authorization, token, userinfo, JWKS, and discovery endpoints must be public HTTPS URLs by default.
- Private, local, and plain HTTP OAuth endpoints are blocked unless explicitly allowed with AOAUTH_ALLOW_PRIVATE_OAUTH_ENDPOINTS for controlled development environments.
- High security mode now rejects ID tokens when signing keys are missing, instead of accepting unverifiable claims.
- The menu label is intentionally fixed as OAUTH SSO so the left WordPress admin navigation displays the requested brand text in every site language.
- The tiny settings and tab view wrappers were merged into admin render helpers without changing the individual tab views.
- .DS_Store files were removed from the release tree.

Rollback plan:
Restore version 2.6.0 from the previous Git tag or plugin zip, then deactivate and reactivate the plugin if WordPress does not refresh plugin metadata automatically. To roll back only this Plugin Check patch, restore admin/class-admin.php, admin/views/logs.php, aoauth-client-sso.php, includes/class-core.php, includes/class-debug.php, includes/class-logger.php, includes/class-oauth-client.php, includes/class-security.php, includes/class-sso-handler.php, includes/class-user-manager.php, languages, readme.txt, and CHANGELOG.md from the 2.6.0 tag.

== Changelog ==

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
