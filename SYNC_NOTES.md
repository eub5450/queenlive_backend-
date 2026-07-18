# QueenLive Backend — Sync Notes

This repo is a **code-only mirror** of the QueenLive Laravel backend, pulled from the
production app node **app01** (`159.223.42.204`, `/var/www/queenlive/current`).
Excludes: `vendor/`, `node_modules/`, `.env`, runtime `storage/`, uploaded media in `public/`.

## 2026-07-18 — app01 ↔ app02 node sync

Audited the two load-balanced app nodes (app01 `159.223.42.204`, app02 `152.42.223.173`)
and found **app02 was stale** relative to app01. Synced app01 → app02 so both nodes serve
identical code. Backups (`.bak_<timestamp>`) were taken on app02 before overwrite.

Files brought in line (app01 → app02):

| File | State before sync |
|------|-------------------|
| `app/Http/Controllers/Api/V5/DeviceTokenController.php` | app02 was 33 lines behind |
| `app/Services/CheckinService.php` | app02 was 31 lines behind |
| `database/migrations/2026_07_05_000000_create_checkin_tables.php` | app02 differed (13 lines) |
| `database/migrations/2026_06_28_075044_v5_speed.php` | missing on app02 (added) |
| `database/migrations/2026_06_28_180000_index_live_calls_v5.php` | missing on app02 (added) |

After sync: all five md5-match app01; `php8.1-fpm` reloaded on app02 to clear opcache.
Remaining node differences are CRLF-only (line endings) on
`RankingController.php` and `V5RoomRealtimeService.php` — functionally identical.

## 2026-07-18 — admin sidebar fix

`resources/views/backend/layouts/sidebar.blade.php`: added the missing `</ul>` that closed
the `metismenu` list. The unterminated `<ul>` inside `<nav>` was garbling the admin panel
layout. Deployed to both app01 and app02; view cache cleared.
