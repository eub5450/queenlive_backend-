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

Line-ending/whitespace normalization (app01 → app02) brought the last two files to
byte parity:

| File | Before |
|------|--------|
| `app/Services/V5/V5RoomRealtimeService.php` | app02 was CRLF, app01 LF |
| `app/Http/Controllers/Api/V5/RankingController.php` | app02 had 1 extra whitespace line |

Final state: **all 624 tracked code files byte-identical** across app01 and app02
(combined manifest md5 `9889dc166ddcdf40ff754c9d2868185c` on both). Verified to hold
after the `php8.1-fpm` reload (reload touches opcache, not disk).

### API parity check (live)

Hit both nodes directly on `:80` (`Host: queenlive.site`, `X-Forwarded-Proto: https`)
with identical requests and compared responses:

| Endpoint | Result (both nodes) |
|----------|---------------------|
| `GET /api/v5/setting_info` | HTTP 200, 617 b, md5 `42c8be4e23204d9fc2184351e27dee89` — **byte-identical** |
| `GET /api/v4/top_list` (RankingController) | HTTP 401, md5 `11977a90…` — identical |
| `GET /api/v4/rank` (RankingController) | HTTP 401, md5 `11977a90…` — identical |
| `GET /api/v4/comment_skip_word_list` | HTTP 401, md5 `11977a90…` — identical |

`setting_info` (unauthenticated, live config/version data) returned the same bytes from
both nodes; auth-gated endpoints returned matching `401`s. Both nodes serve identical
responses at every observable layer.

## 2026-07-18 — co-host "call request" not reaching host (audio + video)

Bug: audience "call request" never notified the host in both audio and video rooms.
Root cause (4-agent investigation): the level-2 gate was removed on the Flutter client
(`minimumCallRequestLevel = 0`) but **not** on the backend. `RoomActionService::requestCohost`
(the live path — client hits `POST /api/v5/room/{type}/{channel}/cohost/request`) still held
`COHOST_MIN_LEVEL = ['audio'=>2,'video'=>2,'multi'=>2]` and **early-returned `level_too_low`
before broadcasting `room.cohost.requested`**. Since `resolveUserLevel()` often reads 0, this
rejected virtually every request in both room types.

Fix (`app/Services/V5/RoomActionService.php:42`): `COHOST_MIN_LEVEL` → `0/0/0`, matching the
client. Deployed to app-01 + app-02 (backups, `php -l` clean, `php8.1-fpm` reloaded); app-01 ==
app-02 == mirror.

Companion client fixes (Flutter repo `eub5450/queenLive_app`, needs APK rebuild to take effect):
- Audio `sendCallRequest`: use the page's `userId → state.userId/autoDisposeProvider().userId`
  fallback for the requester id and guard non-empty (was passing a possibly-empty `userId`, which
  made `requestCohost` omit `user_id`/`co_host_id` → backend `user_id_required`, silent no-op).
- Video `sendCallRequest`: added `requesterId.isEmpty` to the pre-send guard (same identity-omission
  protection; video already had the id fallback + a recovery poll).

## 2026-07-18 — admin sidebar fix

`resources/views/backend/layouts/sidebar.blade.php`: added the missing `</ul>` that closed
the `metismenu` list. The unterminated `<ul>` inside `<nav>` was garbling the admin panel
layout. Deployed to both app01 and app02; view cache cleared.
