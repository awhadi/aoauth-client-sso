# Changelog

All notable changes to this project are documented in this file.

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
