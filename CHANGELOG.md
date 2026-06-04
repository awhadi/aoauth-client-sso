# Changelog

All notable changes to this project are documented in this file.

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
