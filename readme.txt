=== aOAUTH Client SSO ===
Contributors: awhadi
Tags: oauth, oidc, sso, login, security
Requires at least: 5.8
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.6.7
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

OAuth 2.0 and OpenID Connect Single Sign-On for WordPress with Google, Microsoft, GitHub, Keycloak, Auth0, Okta, and custom identity providers.

== Description ==

aOAUTH Client SSO is a WordPress Single Sign-On plugin that allows users to log in to WordPress with OAuth 2.0 and OpenID Connect providers such as Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, Apple, WordPress, and custom identity providers.

The plugin works with identity providers that conform to the OAuth 2.0 and OpenID Connect (OIDC) standards. Site owners can move away from password-only WordPress login, centralize authentication with existing business or social accounts, connect provider identities to WordPress users, map roles, control post-login redirects, style SSO buttons, and manage authentication from the WordPress admin area.

= Main features =

* OAuth 2.0 and OpenID Connect login for WordPress.
* Preconfigured providers for Google, Microsoft, GitHub, Keycloak, Auth0, Okta, OneLogin, GitLab, Facebook, LinkedIn, and Apple.
* Custom OAuth 2.0 and OIDC provider setup.
* Account linking for existing WordPress users.
* Optional user provisioning with default role and role mapping.
* Silent auto-login for already linked OIDC sessions when explicitly enabled.
* Cloudflare Turnstile and Google reCAPTCHA bot verification support.
* Login button themes, layouts, previews, and account-linking page styling.
* Admin logs, backup and restore, temporary session cleanup, and uninstall cleanup.
* Bundled translations for German, Dari Afghanistan, French, Russian, Turkish, Chinese, and Japanese.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/aoauth-client-sso` directory, or install the plugin through the WordPress Plugins screen.
2. Activate the plugin through the Plugins screen in WordPress.
3. Open **OAUTH SSO** in the WordPress admin menu.
4. Add a provider, enter the Client ID and Client Secret, configure scopes and endpoints, then save and test the connection.
5. Configure user creation, role mapping, security, and sign-in experience settings as needed.

== Frequently Asked Questions ==

= Does the plugin create WordPress users automatically? =

Only when user creation is enabled and the user manually starts an SSO login. Silent auto-login never creates users.

= Does silent auto-login redirect everyone to the first provider? =

No. When enabled, it checks supported OIDC providers in the background and only logs in already linked WordPress users with an active provider session.

= Can users still use normal WordPress login? =

Yes. The plugin adds SSO login options and does not remove the standard WordPress username and password form by default.

= Does it support custom providers? =

Yes. Custom OAuth 2.0 and OIDC providers can be configured with custom authorization, token, userinfo, JWKS, issuer, and discovery endpoints.

== Screenshots ==

1. SSO buttons on the WordPress login screen.
2. Provider management in the WordPress admin area.
3. Sign-In Experience preview and styling controls.
4. User management and SSO account linking settings.
5. Security settings for bot verification and login protection.
6. SSO logs and maintenance tools.

== Changelog ==

= 2.6.7 =
* Replaced first-provider auto redirects with silent OIDC auto-login checks.
* Limited silent auto-login to already linked WordPress users; silent checks never create users or link accounts.
* Added hidden-iframe silent checks with prompt=none for supported OIDC providers.
* Updated bundled translations for the new silent auto-login labels.

= 2.6.6 =
* Fixed bot verification getting stuck by preloading the configured bot provider API and executing invisible Turnstile widgets after rendering.

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
* Completed bundled translations for German, Dari Afghanistan, French, Russian, Turkish, Chinese, and Japanese.
* Regenerated compiled gettext files for all supported locales.

= 2.4.6 =
* Made login layout rules theme-agnostic.
* Fixed Wrap Centered for all current and future themes.
* Fixed Compact Row overflow by allowing provider buttons to wrap.
* Localized additional front-end and admin JavaScript labels/helper states.

= 2.4.5 =
* Added German, Dari Afghanistan, French, Russian, Turkish, Chinese, and Japanese gettext files.
* Fixed wp-login.php icon-theme button sizing for Wrap Centered layout.

= 2.4.4 =
* Removed current-user bot verification clearing shortcode and related profile/front-end UI.
* Added Session Management helper text with practical examples.
* Staged Deep Debug changes until Save Tools Settings is clicked.
* Restored explicit SSO Actions unlink buttons on the WordPress Users screen.
* Expanded the Sign-In Experience preview.
* Fixed Wrap Centered login button sizing inside wp-login.php.
* Removed duplicate provider wizard Test Connection toast.

== Upgrade Notice ==

= 2.6.7 =
Silent auto-login now checks supported OIDC providers in the background only when enabled and only logs in already linked WordPress users.
