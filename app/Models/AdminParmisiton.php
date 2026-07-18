<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AdminParmisiton extends Model
{
    protected $table = 'adminparmisiton';

    protected $fillable = [
        'user_id',
        'admin_mode',
        'permissions',
        'updated_by',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    public static function allowed($userId, string $permissionKey, bool $default = false): bool
    {
        $userId = (int) $userId;
        if ($userId <= 0 || $permissionKey === '') {
            return false;
        }

        $user = Auth::id() === $userId ? Auth::user() : User::find($userId);
        if ($user) {
            if (in_array((int) $user->id, [1111120], true)) {
                return true;
            }

            if ((int) ($user->is_admin ?? 0) === 1) {
                return true;
            }
        }

        if (!Schema::hasTable('adminparmisiton')) {
            return $default;
        }

        $permissions = self::permissionsForUser($userId);
        if ($permissions === null) {
            return $default;
        }

        return in_array($permissionKey, $permissions, true);
    }

    public static function any($userId, array $permissionKeys): bool
    {
        foreach ($permissionKeys as $key) {
            if (self::allowed($userId, (string) $key, false)) {
                return true;
            }
        }

        return false;
    }

    public static function permissionsForUser(int $userId): ?array
    {
        if ($userId <= 0 || !Schema::hasTable('adminparmisiton')) {
            return null;
        }

        return Cache::remember(self::cacheKey($userId), 60, function () use ($userId) {
            $row = self::query()->where('user_id', $userId)->first();
            if (!$row) {
                return null;
            }

            $permissions = $row->permissions;
            if (is_string($permissions)) {
                $decoded = json_decode($permissions, true);
                $permissions = is_array($decoded) ? $decoded : [];
            }

            return array_values(array_unique(array_filter(array_map('strval', (array) $permissions))));
        });
    }

    public static function forgetUser($userId): void
    {
        Cache::forget(self::cacheKey((int) $userId));
    }

    protected static function cacheKey(int $userId): string
    {
        return 'queenlive:adminparmisiton:' . $userId;
    }
}
