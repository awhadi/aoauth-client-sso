# Changelog

All notable changes to this project are documented in this file.

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
