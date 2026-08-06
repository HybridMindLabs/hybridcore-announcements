# HybridCore Announcements

[![HybridCore](https://img.shields.io/badge/HybridCore-%E2%89%A5%200.3.0-6366f1)](https://github.com/HybridMindLabs/HybridCore)
[![Version](https://img.shields.io/badge/version-1.1.0-22c55e)](CHANGELOG.md)
[![License](https://img.shields.io/badge/license-Proprietary-64748b)](LICENSE)

The official **Announcements** extension for [HybridCore](https://github.com/HybridMindLabs/HybridCore) — site-wide banner messages, below the header on every public page.

- 📢 **Site-wide banner** — shown below the header on every public page, not just the home page.
- 🎨 **Four severity levels** — info, success, warning, danger, each with its own colour and icon.
- 🗓️ **Optional scheduling** — set a start and/or end time; a message shows itself and hides itself automatically.
- 🙈 **Per-visitor dismiss** — each announcement can be closed individually; a dismissed one stays dismissed for that visitor until it's edited again.
- ⚙️ **Admin settings** — cap how many show at once, or turn the banner off entirely.
- 🌍 English + Bulgarian translations.

## Requirements

- HybridCore **≥ 0.3.0**

## Installation

```bash
cd extensions/hybridcore
git clone https://github.com/HybridMindLabs/hybridcore-announcements.git announcements

php artisan hybridcore:extensions:sync
php artisan hybridcore:extensions:enable hybridcore/announcements
```

## Usage

- **Admin → Announcements** — create, edit, reorder and remove messages.
- **Admin → Settings → Extensions → Announcements** — cap how many show at once, or disable the banner.
- A dismissed announcement re-appears for that visitor only if it's edited afterwards (dismissal is keyed to the message's last-updated time).

## Development

```bash
php artisan hybridcore:extensions:test hybridcore/announcements
php artisan hybridcore:extensions:zip hybridcore/announcements
```

See [`BUILDING_EXTENSIONS.md`](https://github.com/HybridMindLabs/HybridCore/blob/main/extensions/BUILDING_EXTENSIONS.md) for the Extension SDK reference.

## License

Proprietary — © HybridMind Labs. See [LICENSE](LICENSE).
