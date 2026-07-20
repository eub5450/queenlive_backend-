# QueenLive Backend

Laravel 10 / PHP 8.1 admin + API backend for the **QueenLive** live-streaming platform (iOS + Android).

## Recent Updates

- **2026-07-20** — Session 4: `UserEmailChangeStore` dead-return bug fixed (new ID create now works); same-user swap guard added; user 1111111 (m01809752@gmail.com) created.
- **2026-07-20** — [WORKLIST_20260720.md](WORKLIST_20260720.md): Session 3 — user ID re-serialisation (99000xxx → 1111156–1111179, AUTO_INCREMENT=1111180), phone race-condition fix in all 3 AuthControllers, email-change admin alert fix, 9797 orphan rows deleted across 8 tables, laravel.log cleared on both nodes.
- **2026-07-20** — Session 2: sub-admin empty-permissions lockout fixed (AdminSettingController + settings form `open` attr), profile view NID `@endif` bug fixed (all 6 history sections were hidden from sub-admins without `profile_nid`).
- **2026-07-20** — Session 1: full RBAC from zero — AdminParmisiton model + migration, 60-key permission system (11 groups), sidebar/dashboard gating, 5-controller country scoping, 13 bugs fixed. See [PERMISSION_SYSTEM.md](PERMISSION_SYSTEM.md) and [ADMIN_BUGS.md](ADMIN_BUGS.md).

## Architecture

- **Nodes**: app-01 `159.223.42.204` · app-02 `152.42.223.173` (load-balanced, both must stay in sync)
- **Edge**: edge-01 `168.144.146.68` (nginx reverse proxy → upstream `queenlive_origin_pool`)
- **App root on nodes**: `/var/www/queenlive/current`
- **DB**: shared db-01 MariaDB 10.11
- **Cache/Queue**: Redis on `10.104.0.9:6379`
- **PHP**: 8.1-FPM (`unix:/run/php/php8.1-fpm.sock`)

## Repository

This is a **private code mirror** — the servers are NOT git-based. Deploy changes by SCP'ing files to both nodes, then:
```bash
php artisan view:clear
php artisan route:clear
systemctl reload php8.1-fpm
redis-cli -h 10.104.0.9 keys 'queenlive:adminparmisiton:*' | xargs redis-cli -h 10.104.0.9 del
```

## Admin Panel

URL: `https://queenlive.site/admin` (redirects to login)  
Permission management: `https://queenlive.site/setting/admin`

### Admin Roles (users.is_admin)
| Value | Role | Data Scope |
|-------|------|------------|
| 0 | Normal User | No admin access |
| 1 | Main Admin | All data — bypasses all permission checks |
| 2 | Country Admin | Only sees data from own `country_id` |
| 3 | Sub Admin | Permission-checked, global data |

**Hardcoded superuser**: user ID `1111120` — always returns `true` for every permission check regardless of DB state. Cannot be demoted.

### Permission System
See [PERMISSION_SYSTEM.md](PERMISSION_SYSTEM.md) for the complete key reference.

## Key Files

| File | Purpose |
|------|---------|
| `app/Models/AdminParmisiton.php` | Core permission check — `allowed($userId, $key)`, 60s Redis cache |
| `app/Http/Middleware/AdminMiddleware.php` | Route-level permission enforcement |
| `app/Http/Controllers/Admin/AdminSettingController.php` | `groups()` (all keys), `adminPreset()`, `subadminPreset()`, `countryAdminPreset()` |
| `app/Http/Controllers/Admin/DashbordController.php` | Dashboard — country-scoped metrics, server control |
| `app/Http/Controllers/Admin/ProfileController.php` | User profile search, role change, host/portal actions |
| `resources/views/backend/layouts/sidebar.blade.php` | Admin sidebar — all `$adminCan()` gates |
| `resources/views/backend/home.blade.php` | Dashboard view |
| `resources/views/backend/profile/index.blade.php` | User profile view (~2800 lines) |
| `resources/views/backend/setting/admin.blade.php` | Permission management UI |

## Environment Variables

All secrets must be in `.env` on each node — never hardcoded in PHP:

```
# Vultr server control
VULTR_API_KEY=
VULTR_INSTANCE_ID=

# Pusher (emergency kick channel — separate from main broadcasting)
PUSHER_KICK_APP_KEY=
PUSHER_KICK_APP_SECRET=
PUSHER_KICK_APP_ID=

# Main Pusher (broadcasting)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
```

## Security Notes

- All admin routes require `['auth', 'admin']` middleware — including diagnostic routes under `/socket-tools/*`
- Sub-admins cannot modify users with `is_admin > 0`
- Non-main-admins cannot set `is_app_admin` / `is_bd_admin` flags
- Superuser 1111120 is blocked from demotion in all three role-change code paths
- Country admins are data-scoped in: ProfileController, HostController, AgencyController, LiveController, WithdrawController, DashbordController
