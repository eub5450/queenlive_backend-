<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SystemSettingRuntimeStore
{
    private const TABLE_NAME = 'bd_game_final_runtime_settings';
    private const KEY_PREFIX = 'system_setting.';
    private const FILE_NAME = 'system_setting_runtime.json';

    public static function all(): array
    {
        try {
            if (Schema::hasTable(self::TABLE_NAME)) {
                $rows = DB::table(self::TABLE_NAME)
                    ->where('key', 'like', self::KEY_PREFIX . '%')
                    ->get(['key', 'value']);

                if ($rows->isNotEmpty()) {
                    $data = [];
                    foreach ($rows as $row) {
                        $key = substr((string) $row->key, strlen(self::KEY_PREFIX));
                        $decoded = json_decode((string) $row->value, true);
                        $data[$key] = json_last_error() === JSON_ERROR_NONE ? $decoded : $row->value;
                    }

                    return $data;
                }
            }
        } catch (\Throwable $throwable) {
            // Fall back to local file if the shared runtime table is unavailable.
        }

        $path = self::path();
        if (!is_file($path)) {
            return [];
        }

        $contents = @file_get_contents($path);
        if ($contents === false || trim($contents) === '') {
            return [];
        }

        $decoded = json_decode($contents, true);
        return is_array($decoded) ? $decoded : [];
    }

    public static function get(string $key, $default = null)
    {
        $data = self::all();
        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    public static function putMany(array $values): void
    {
        try {
            if (Schema::hasTable(self::TABLE_NAME)) {
                foreach ($values as $key => $value) {
                    DB::table(self::TABLE_NAME)->updateOrInsert(
                        ['key' => self::KEY_PREFIX . $key],
                        [
                            'value' => json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        } catch (\Throwable $throwable) {
            // Continue with file persistence when the shared runtime table is unavailable.
        }

        $data = self::all();
        foreach ($values as $key => $value) {
            $data[$key] = $value;
        }

        $directory = dirname(self::path());
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        file_put_contents(self::path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function path(): string
    {
        return storage_path('app/' . self::FILE_NAME);
    }
}
