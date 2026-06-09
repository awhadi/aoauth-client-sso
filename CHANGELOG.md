# Changelog

All notable changes to this project are documented in this file.

## [2.6.7] - 2026-06-09

### Changed
- Replaced first-provider auto redirects with silent OIDC auto-login checks that only run when the existing setting is enabled.
- Limited silent auto-login to already linked WordPress users; silent checks never create users or link accounts.
- Added hidden-iframe silent checks with `prompt=none` and a minimal callback bridge for supported OIDC providers.
- Updated bundled translations and compiled locale files for the new silent auto-login labels.

## [2.6.6] - 2026-06-08

### Fixed
- Fixed bot verification getting stuck by preloading the configured bot provider API and executing invisible Turnstile widgets after rendering.

## [2.6.5] - 2026-06-08

### Changed
- Optimized bundled provider icon assets for a smaller release package.
- Updated the admin header watermark text for the release build.
- Confirmed tracked release assets are referenced and no extra development files are present in the plugin tree.

## [2.6.4] - 2026-06-08

### Fixed
- Fixed Plugin Check findings for hidden files, translator comments, escaping, remote script enqueues, auto-update modification detection, and scoped database/query warnings.
- Preserved optional Turnstile and reCAPTCHA behavior by loading provider APIs from the existing external public JavaScript file instead of enqueueing off-site scripts through WordPress.
- Removed forced auto-update enabling on activation while keeping the native Plugins screen auto-update control.

## [2.6.3] - 2026-06-08

### Fixed
- Restored callback compatibility for existing OIDC providers whose saved metadata does not include JWKS signing keys.
- Applied built-in OIDC metadata defaults when saved provider names use different casing.

## [2.6.2] - 2026-06-08

### Fixed
- Fixed SSO authentication for existing providers by preserving exact provider identifiers during nonce verification and application lookup.
- Fixed account linking, provider toggles, and unlink actions to use the configured provider key instead of a lowercased slug.

## [2.6.1] - 2026-06-07

### Changed
- Fixed Plugin Check findings for escaping, translator comments, request unslashing, generated downloads, hidden files, and stylesheet loading.
- Updated `Tested up to` metadata to WordPress 7.0.
- Switched OAuth provider redirects to `wp_safe_redirect()` with a temporary provider host allowlist.
- Removed direct PHP error logging from the SSO error redirect path.
- Removed the hidden `.gitignore` development file from the plugin package.
- Regenerated translation catalogs and compiled locale files for changed strings.

## [2.6.0] - 2026-06-07

### Added
- Added `.gitignore` coverage for `.DS_Store` release junk files.

### Changed
- Hardened OAuth/OIDC endpoint validation to require public HTTPS endpoints by default for authorization, token, userinfo, JWKS, and discovery URLs.
- Required configured signing keys before accepting ID token claims in High security mode.
- Consolidated provider subject extraction into a shared core helper.
- Merged the tiny settings and tab view partials into admin render helpers while keeping each settings tab in its own view file.
- Changed the WordPress admin left-menu label from `aOAUTH SSO` to `OAUTH SSO` while keeping the plugin name and page title unchanged.
- Removed the unused SVG admin menu icon asset.

## [2.5.1] - 2026-06-07

### Changed
- Moved automatic update management to the native WordPress Plugins screen Enable/Disable auto-updates link and removed the duplicate Tools toggle.
- Changed the admin menu icon to a small color-inheriting `a` rendered from the existing external admin menu CSS.

## [2.5.0] - 2026-06-05

### Added
- Added a Tools setting to enable or disable WordPress automatic plugin updates for this plugin when an update package is available from the configured update source.
- Added a root `uninstall.php` file for WordPress-standard uninstall cleanup.
- Added bundled translations for the new automatic update setting label and helper text.

### Changed
- Clarified the plugin header and WordPress readme descriptions to explain supported OAuth/OIDC provider login, standards compatibility, and how the plugin replaces password-only WordPress login with managed Single Sign-On.
- Moved uninstall cleanup out of the main plugin runtime and into `uninstall.php`.

## [2.4.11] - 2026-06-05

### Changed
- Improved the plugin header and WordPress readme descriptions to better explain supported OAuth 2.0/OpenID Connect SSO providers, standards compatibility, and core plugin features.

## [2.4.10] - 2026-06-05

### Added
- Added a Dari Afghanistan locale stylesheet loaded only for `fa_AF` to apply one Persian-capable font family across plugin admin, login, shortcode, overlay, and account-linking surfaces.

## [2.4.9] - 2026-06-05

### Added
- Added an optional Sign-In Experience setting to automatically try the first enabled SSO provider when visitors reach `wp-login.php`.

### Changed
- Redirect logged-in WordPress users away from the primary login screen so another login cannot be started in the same browser session.
- Prevent direct SSO login initiation for already logged-in users while preserving authenticated account-linking flows.

## [2.4.8] - 2026-06-05

### Changed
- Moved the Users screen SSO unlink action into the SSO Providers column as a text link beside each provider name and removed the separate SSO Actions column.
- Renamed settings tab view files to concise names and replaced the shared admin tabs partial with `tabs.php`.

### Fixed
- Completed visible admin translations for the wizard, User Management settings, Tools Backup & Restore, Shortcodes, Danger Zone, and helper text sections across all bundled locales.
- Regenerated the gettext template and compiled locale files after the admin view renames.

## [2.4.7] - 2026-06-04

### Fixed
- Completed shipped gettext catalogs for German, Dari (Afghanistan), French, Russian, Turkish, Chinese, and Japanese so admin tabs, settings labels, helper text, and JavaScript messages no longer fall back to empty translations.
- Regenerated compiled `.mo` files for every supported locale from the current gettext template.

## [2.4.6] - 2026-06-04

### Changed
- Made login button layout rules theme-agnostic so themes control visual style while layouts control arrangement.
- Updated Compact Row to wrap when many providers are enabled instead of forcing one overflowing row.
- Localized additional front-end and admin JavaScript labels, helper states, and verification messages through existing WordPress translation loading.
- Regenerated gettext templates and compiled locale files for shipped languages.

### Fixed
- Fixed Wrap Centered so all current and future themes keep their selected button style while wrapping as a centered block.
- Fixed inside-form layout overrides so only Full Width Stack and Two Columns intentionally stretch buttons.

## [2.4.5] - 2026-06-04

### Added
- Added gettext language template and compiled locale files for German, Dari (Afghanistan), French, Russian, Turkish, Chinese, and Japanese.

### Fixed
- Fixed Icon Only, Icon Aurora, Icon Sunset, and Icon Neon button sizing on wp-login.php when Wrap Centered layout is selected, preserving each theme's intended shape.

## [2.4.4] - 2026-06-04

### Removed
- Removed the current-user bot verification clearing shortcode and related profile/front-end UI.

### Changed
- Added clearer Session Management helper text for bot verification tokens, account-linking lockouts, and expired OAuth temporary data.
- Deep Debug changes are now staged until Save Tools Settings is clicked instead of applying immediately when the toggle changes.
- Restored a dedicated SSO Actions column on the WordPress Users screen with explicit unlink buttons beside SSO provider information.
- Expanded the Sign-In Experience preview to show login buttons, the account-linking prompt, and the verification overlay.
- Kept provider wizard Test Connection feedback to one inline result instead of showing the same message in a toast.

### Fixed
- Fixed Wrap Centered login button layout inside wp-login.php so icon themes keep natural button sizing instead of stretching full width.
- Hardened generated debug log directory access rules for Apache 2.4 while retaining older Apache/LiteSpeed denial support and documenting Nginx requirements.

## [2.4.3] - 2026-06-02

### Fixed
- Prevented Inside Login Form SSO buttons and the "Or login with" divider from appearing on non-login WordPress auth screens such as Lost Password.

## [2.4.2] - 2026-06-02

### Changed
- Improved Inside Login Form SSO spacing so the divider and provider buttons sit cleanly after the WordPress Login button.
- Applied the configured Bot Verification Overlay opacity consistently to supported selected overlay styles.

### Fixed
- Fixed the account-linking lockout path so the configured full-login ban duration is available when repeated password confirmation failures trigger a lockout.
- Removed a stale implementation comment from the plugin bootstrap.

## [2.4.1] - 2026-06-02

### Changed
- Adjusted Inside Login Form placement so SSO buttons appear inside the WordPress login form after the Login button.
- Kept Below Login Form as a separate outside-form placement option.
- Reduced Bot Verification Overlay choices to Spotlight, Constellation, and Minimal.
- Removed the moving line effect from Constellation while keeping the star animation.
- Simplified login button layouts by replacing duplicate Horizontal/Grid choices with Full Width Stack, Wrap Centered, Two Columns, and Compact Row.
- Updated the overlay preview so Spotlight is visible and overlay colors follow the selected theme.
- Clarified Turnstile Display Mode and Full Login Ban help text.

### Fixed
- Fixed Full Login Ban application after repeated failed account-linking password checks by reading the configured ban duration before setting the ban transient.
- Normalized legacy saved overlay/layout values to current supported options.

## [2.4.0] - 2026-06-02

### Added
- Added a Below Login Form SSO button position to reduce accidental provider clicks after users type WordPress credentials.
- Added a Wrap Centered login button layout.
- Added Bot Verification Overlay opacity control.
- Added Hyperspace, Constellation, and Signal Grid professional overlay styles while keeping Spotlight and Minimal.
- Added Turnstile display mode control with Invisible, Managed Visible in Overlay, and Non-Interactive options.
- Added Tools cleanup status showing next and last log cleanup runs.
- Added Run Cleanup Now and Reschedule Cleanup maintenance actions.
- Added `[aoauth_clear_bot_verification]` for logged-in users to clear temporary bot verification records for their current session.
- Added a matching profile-page bot verification troubleshooting action.

### Changed
- Clarified the Show Brand Badge help text to explain that it appears on front-end pages for SSO-authenticated users.
- Renamed verification overlay settings to Bot Verification Overlay.
- Made verification branding text more professional in the overlay and preview.
- Merged Users table SSO actions into the SSO Provider column.
- Moved image fallback behavior from inline handlers into existing external JavaScript files.
- Tinted icon-oriented provider logos through theme CSS filters where CSS tinting is practical for PNG assets.

### Security
- Current-user bot verification clearing is scoped to the visitor's current request/IP data; global bot token clearing remains administrator-only.
- Turnstile retry handling now resets stale widgets and keeps users in the verification overlay flow.
- Existing OAuth callback, linking, nonce, provider identity, and admin permission checks remain intact.

### Performance
- Log cleanup remains native WP-Cron and now records last cleanup time for admin visibility.
- No new frontend bundles were added; changes reuse existing public/admin CSS and JavaScript assets.

## [2.3.0] - 2026-05-27

### Added
- Added an admin-controlled Deep Debug toggle that updates wp-config.php when WordPress has write permission.
- Added Icon Aurora, Icon Sunset, and Icon Neon icon-only theme variants with light icon surfaces for provider logo visibility.
- Added log table indexes for provider/status/date filtering.

### Changed
- Prevented the setup wizard from opening after activation when any provider is already configured, whether enabled or disabled.
- Optimized the Logs screen by keeping pagination and avoiding the duplicate initial AJAX reload after the first server-rendered page.
- Extended verification overlay theme colors for the new icon-only theme variants.

### Performance
- Logs remain paginated at 50 entries per page.
- Added database indexes to reduce filtered Logs screen query cost on larger log tables.
- Frontend impact remains limited to login/profile pages because new theme CSS only loads when selected.

## [2.2.1] - 2026-05-27

### Added
- Added a Tools shortcode reference for `[aoauth_link_account]` and `[aoauth_unlink_account]`.
- Added support for `define("OAUTH-DEBUG", "enabled");` as a low-level debug constant.

### Changed
- Separated Activity Logs from Deep Debug in Tools so the Logs screen toggle is distinct from wp-config.php debug mode.
- Moved the Activity Logs toggle before log retention and clarified that Deep Debug is controlled by wp-config.php.

## [2.2.0] - 2026-05-27

### Added
- Added multi-provider account linking so one WordPress user can connect multiple enabled SSO providers.
- Added provider linking cards directly on the WordPress user profile screen for the current logged-in user's own profile.
- Added provider identity metadata for linked provider subject/email values so future logins can resolve to the correct WordPress user even when the provider email differs from the WordPress email.

### Changed
- Reorganized the User Management tab into User Creation, Role Redirects, and Account Linking groups.
- Updated existing SSO themes with distinct colors, weights, sizing, and shapes without importing external fonts.
- Refined the Paper Plane verification overlay so the plane travels left-to-right while the overlay grows behind it.
- Updated profile and shortcode unlink displays to handle multiple connected providers.

### Security
- Blocks linking when the same provider identity is already attached to a different WordPress user.
- Allows provider/WordPress email mismatch only after a logged-in, nonce-protected OAuth linking flow proves control of the provider account.
- Admins viewing another user's profile can see SSO status, but linking must be started by the user from their own logged-in profile.

## [2.1.0] - 2026-05-27

### Added
- Added self-service account linking with the `[aoauth_link_account]` shortcode for logged-in users.
- Added an admin setting to enable or disable self-service account linking.
- Added verification overlay branding with the verification provider name and "Powered by aOAUTH Client SSO".
- Added Paper Plane, Glass Shield, and Aurora verification overlay styles.
- Added Tools maintenance actions for bot verifications, account-linking lockouts, and expired OAuth temporary data.

### Changed
- Reorganized settings into related groups so each tab reads as connected workflows instead of isolated rows.
- Moved verification overlay appearance controls into a clearer Sign-In Experience group with a right-side preview.
- Removed manual verification overlay color and message-style controls from the UI; overlay colors now follow the selected SSO theme.
- Reworked Tools session management to show useful SSO status and maintenance actions instead of duplicating the WordPress Users table.
- Renamed the Tools logging toggle to Plugin Debug Logging while keeping WordPress debug status as an informational badge.
- Improved Spotlight, Full Panel, and Minimal overlay presentation while preserving existing bot verification behavior.

### Security
- Self-service account linking requires a logged-in user, a user-specific nonce, enabled account-linking settings, and a provider email matching the WordPress account email.
- Tools maintenance actions require administrator capability and the existing admin nonce.

### Performance
- Reused existing admin/public CSS and JavaScript assets instead of adding new frontend bundles.

## [2.0.0] - 2026-05-25

### Added
- Added separate admin tabs and view files for Sign-In Experience, User Management, Security, and Tools.
- Added role-based redirect settings for every editable WordPress role.
- Added configurable verification overlay style, color, and message emphasis.
- Added log filters for event type, provider, status, and date range.
- Added a WordPress-style SVG menu icon.
- Added a lightweight frontend-only account unlink script for the unlink shortcode.

### Changed
- Renamed broad CSS and JavaScript assets to purpose-specific filenames.
- Kept admin assets out of normal frontend pages while preserving login-page SSO assets and opt-in SSO brand badge assets.
- Moved backup, restore, factory reset, debug, log retention, and SSO user visibility into the Tools tab.
- Improved public OAuth failure redirects to avoid exposing low-level provider and token exchange details.
- Applied configured role redirects after SSO login and account linking.

### Security
- Hardened log ordering with an explicit SQL order-by whitelist.
- Tightened unlink CSRF protection to require the user-specific unlink nonce.
- Enforced full login bans for SSO login attempts in addition to WordPress password authentication.

### Performance
- Reduced frontend asset loading by replacing shortcode use of the admin dashboard script with a small dedicated public script.
- Preserved conditional loading for login assets and SSO brand badge assets.
