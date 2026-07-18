<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

class ImageUploadStorageHelper
{
    private const DEFAULT_MAX_BYTES = 5242880;

    public static function storeBase64Image($value, $relativeDir, $filePrefix, $maxBytes = self::DEFAULT_MAX_BYTES)
    {
        $bytes = self::decodeBase64Image($value);
        if ($bytes === null || $bytes === '') {
            return null;
        }

        if ($maxBytes > 0 && strlen($bytes) > $maxBytes) {
            return null;
        }

        $extension = self::detectExtensionFromBytes($bytes);
        if ($extension === null) {
            return null;
        }

        return self::writeBytes($bytes, $relativeDir, $filePrefix, $extension);
    }

    public static function storeUploadedImage(UploadedFile $file, $relativeDir, $filePrefix, $maxBytes = self::DEFAULT_MAX_BYTES)
    {
        if (!$file->isValid()) {
            return null;
        }

        $realPath = $file->getRealPath();
        if (!$realPath || !is_file($realPath)) {
            return null;
        }

        $bytes = file_get_contents($realPath);
        if ($bytes === false || $bytes === '') {
            return null;
        }

        if ($maxBytes > 0 && strlen($bytes) > $maxBytes) {
            return null;
        }

        $extension = self::detectExtensionFromBytes($bytes);
        if ($extension === null) {
            return null;
        }

        $relativePath = trim(str_replace('\\', '/', (string) $relativeDir), '/');
        $directory = MediaPathHelper::ensureDirectory($relativePath);
        $fileName = self::safeFileName($filePrefix, $extension);

        $file->move($directory, $fileName);
        $storedPath = $directory . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($storedPath)) {
            @chmod($storedPath, 0664);
            return MediaPathHelper::publicUrl($relativePath . '/' . $fileName);
        }

        return null;
    }

    private static function decodeBase64Image($value)
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        if (strpos($value, ',') !== false) {
            $value = substr($value, strpos($value, ',') + 1);
        }

        $value = str_replace(' ', '+', $value);
        $decoded = base64_decode($value, true);

        return $decoded === false ? null : $decoded;
    }

    private static function writeBytes($bytes, $relativeDir, $filePrefix, $extension)
    {
        $relativePath = trim(str_replace('\\', '/', (string) $relativeDir), '/');
        $directory = MediaPathHelper::ensureDirectory($relativePath);
        $fileName = self::safeFileName($filePrefix, $extension);
        $storedPath = $directory . DIRECTORY_SEPARATOR . $fileName;

        $written = file_put_contents($storedPath, $bytes, LOCK_EX);
        if ($written === false) {
            return null;
        }

        @chmod($storedPath, 0664);

        return MediaPathHelper::publicUrl($relativePath . '/' . $fileName);
    }

    private static function safeFileName($prefix, $extension)
    {
        $safePrefix = preg_replace('/[^a-zA-Z0-9_-]+/', '-', (string) $prefix);
        $safePrefix = trim((string) $safePrefix, '-');
        if ($safePrefix === '') {
            $safePrefix = 'image';
        }

        return $safePrefix . '_' . gmdate('YmdHis') . '_' . uniqid() . '.' . $extension;
    }

    private static function detectExtensionFromBytes($bytes)
    {
        if (strlen($bytes) < 12) {
            return null;
        }

        if (substr($bytes, 0, 3) === "\xFF\xD8\xFF") {
            return 'jpg';
        }
        if (substr($bytes, 0, 8) === "\x89PNG\x0D\x0A\x1A\x0A") {
            return 'png';
        }
        if (substr($bytes, 0, 4) === 'GIF8') {
            return 'gif';
        }
        if (substr($bytes, 0, 4) === 'RIFF' && substr($bytes, 8, 4) === 'WEBP') {
            return 'webp';
        }

        $info = @getimagesizefromstring($bytes);
        $type = is_array($info) && isset($info[2]) ? (int) $info[2] : null;
        if ($type === IMAGETYPE_JPEG) {
            return 'jpg';
        }
        if ($type === IMAGETYPE_PNG) {
            return 'png';
        }
        if ($type === IMAGETYPE_GIF) {
            return 'gif';
        }
        if (defined('IMAGETYPE_WEBP') && $type === IMAGETYPE_WEBP) {
            return 'webp';
        }

        return null;
    }
}