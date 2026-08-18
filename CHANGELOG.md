# Changelog

All notable changes to `laravel-mail-dashboard` will be documented in this file.

## v0.0.1 - 2026-08-18

Initial release.

### Added

- Capture every outgoing email via the `MessageSent` event, whatever mail driver is used
- Store each email as a JSON file on any Laravel filesystem disk (`MAIL_DASHBOARD_DISK`, local `storage/mail-dashboard` by default)
- `/mail-dashboard` UI (React 19, Tailwind CSS 4, shadcn/ui) with pre-built assets — no publish or build step required
- Rendered HTML preview (sandboxed iframe, inline `cid:` images), plain text, raw MIME source and headers tabs
- Attachment listing and download
- Search, auto-refresh, dark mode
- Record the Mailable / Notification class that produced each email
- Delete one or all captured emails
- JSON API under `/mail-dashboard/api/emails`
- Horizon-style `viewMailDashboard` gate: open in local, gate-controlled everywhere else
- `enabled`, `path`, `middleware` and `storage` configuration options
