# QueenLive Backend (code mirror)

**Private** code-only mirror of the QueenLive Laravel backend. This is a version-control
**backup / reference**, not a deployable checkout and not the source of truth — production
runs from the live nodes below.

> ⚠️ Keep this repo **private**. Some controllers contain hardcoded secrets (Agora
> `channel_secret`, `ACCESS_TOKEN` constants, a `syncToken`). Do not make it public.

## Where it comes from

| | |
|---|---|
| Public domain | `queenlive.site` |
| Edge (reverse proxy) | edge-01 `168.144.146.68` → upstream `queenlive_origin_pool` |
| App nodes (origin) | app-01 `159.223.42.204`, app-02 `152.42.223.173` (`:80`, load-balanced) |
| App root on each node | `/var/www/queenlive/current` (→ `releases/<ts>_queenlive_laravel_clone`) |
| Runtime | PHP 8.1-FPM, shared DB (db-01) |
| Snapshot pulled from | **app-01** |

This mirror is **not** the Flutter app — that lives in `eub5450/queenLive_app`.

## What's included / excluded

**Included:** `app/`, `bootstrap/`, `config/`, `database/`, `resources/`, `routes/`,
`public/` (code only), `composer.json`, `composer.lock`, `artisan`, `.env.example`.

**Excluded:** `vendor/`, `node_modules/`, real `.env`, runtime `storage/`, uploaded media
(`public/shortvideos|store|game|author|backend|fontend`, `public/storage`), and `*.bak*`
editor backups.

## Editing the live backend (direct-SSH workflow)

Both nodes must stay in sync — **they can drift**.

```bash
KEY="C:/Users/It Solutions BD/Documents/server/.state/uandme_livekit_prod_ed25519"
ssh -i "$KEY" root@159.223.42.204   # app-01
ssh -i "$KEY" root@152.42.223.173   # app-02
```

1. `cp <file> <file>.bak_$(date +%Y%m%d_%H%M%S)` before editing (on **both** nodes).
2. Blade change → `php artisan view:clear`. PHP change → `systemctl reload php8.1-fpm`.
3. Verify byte parity between app-01 and app-02 (`md5sum` manifest) after any edit.

## History

See [`SYNC_NOTES.md`](./SYNC_NOTES.md) — admin sidebar fix, admin→subadmin role change,
and the 2026-07-18 app-01 ↔ app-02 byte-parity sync + live API parity check.
