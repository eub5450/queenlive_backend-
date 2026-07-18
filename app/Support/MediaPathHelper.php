<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class MediaPathHelper
{
    private const FALLBACK_BASE_URL = 'https://queenlive.site';

    public static function baseUrl()
    {
        try {
            if (app()->bound('request')) {
                $request = request();

                if ($request && $request->getHost()) {
                    return rtrim($request->getSchemeAndHttpHost(), '/');
                }
            }
        } catch (\Throwable $e) {
        }

        $configured = trim((string) config('app.url', ''));

        if ($configured === '') {
            $configured = self::FALLBACK_BASE_URL;
        }

        return rtrim($configured, '/');
    }

    public static function isAbsoluteUrl($value)
    {
        $path = trim((string) $value);

        return stripos($path, 'http://') === 0 || stripos($path, 'https://') === 0;
    }

    public static function publicUrl($value, $default = 'store/profile/default.png')
    {
        $path = trim((string) $value);

        if ($path === '') {
            $path = $default;
        }

        if (self::isAbsoluteUrl($path)) {
            return $path;
        }

        return self::baseUrl() . '/' . ltrim(str_replace('\\', '/', $path), '/');
    }

    public static function localRelativePath($value, $default = '')
    {
        $path = trim((string) $value);

        if ($path === '') {
            return ltrim((string) $default, '/');
        }

        $path = str_replace('\\', '/', $path);

        if (self::isAbsoluteUrl($path)) {
            $baseHost = parse_url(self::baseUrl(), PHP_URL_HOST);
            $currentHost = parse_url($path, PHP_URL_HOST);

            if (!$currentHost || strcasecmp((string) $currentHost, (string) $baseHost) !== 0) {
                return ltrim((string) $default, '/');
            }

            $parsedPath = parse_url($path, PHP_URL_PATH);
            $path = $parsedPath ? $parsedPath : $default;
        }

        return ltrim((string) $path, '/');
    }

    public static function ensureDirectory($relativeDir)
    {
        $relativePath = trim(str_replace('\\', '/', (string) $relativeDir), '/');
        $fullPath = base_path($relativePath);

        if (!is_dir($fullPath)) {
            mkdir($fullPath, 0775, true);
        }

        return $fullPath;
    }

    public static function moveUploadedFile(
        UploadedFile $file,
        $relativeDir,
        $preferredBaseName = null,
        $preserveOriginalName = false
    ) {
        $relativePath = trim(str_replace('\\', '/', (string) $relativeDir), '/');
        $targetDirectory = self::ensureDirectory($relativePath);
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if ($preserveOriginalName) {
            $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        } else {
            $baseName = $preferredBaseName ?: gmdate('YmdHis') . '-' . Str::random(12);
        }

        $safeBaseName = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $baseName);
        $safeBaseName = trim((string) $safeBaseName, '-');

        if ($safeBaseName === '') {
            $safeBaseName = 'file';
        }

        $fileName = $extension !== '' ? $safeBaseName . '.' . $extension : $safeBaseName;
        $file->move($targetDirectory, $fileName);

        $storedPath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($storedPath)) {
            @chmod($storedPath, 0664);
        }

        return $relativePath . '/' . $fileName;
    }

    public static function deleteLocalFile($value, array $allowedPrefixes = array())
    {
        $relativePath = self::localRelativePath($value);
        if ($relativePath === '') {
            return;
        }

        if (!empty($allowedPrefixes)) {
            $allowed = false;

            foreach ($allowedPrefixes as $prefix) {
                $normalizedPrefix = trim(str_replace('\\', '/', (string) $prefix), '/');

                if ($normalizedPrefix !== '' && strpos($relativePath, $normalizedPrefix . '/') === 0) {
                    $allowed = true;
                    break;
                }
            }

            if (!$allowed) {
                return;
            }
        }

        $fullPath = base_path($relativePath);
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }
    }
}
