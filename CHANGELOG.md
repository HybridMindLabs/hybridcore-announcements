# Changelog

All notable changes to the HybridCore Announcements extension are documented here.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-08-06

### Changed
- Moved from a home-page-only widget (right column) to a true site-wide banner,
  rendered below the header on every public page via the new core `layout.top`
  slot — matches what the extension's own description always promised.
- Settings key `show_on_home` renamed to `show_banner` to match.

### Added
- Per-visitor dismiss — each announcement gets a close button; dismissing one
  is remembered in the visitor's browser (localStorage) and stays dismissed
  until the message is edited.

## [1.0.0] - 2026-01-01

### Added
- Admin CRUD for announcements: title, message, severity (info / success /
  warning / danger), active toggle, optional start/end scheduling, sort order.
- Home-page widget showing currently active announcements.
- Admin settings: maximum shown at once, on/off toggle.
- Admin dashboard widget: active announcement count.
- English and Bulgarian translations.

[1.1.0]: https://github.com/HybridMindLabs/hybridcore-announcements/releases/tag/v1.1.0
[1.0.0]: https://github.com/HybridMindLabs/hybridcore-announcements/releases/tag/v1.0.0
