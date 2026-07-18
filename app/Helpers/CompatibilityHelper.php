<?php
// app/Helpers/CompatibilityHelper.php

namespace App\Helpers;

class CompatibilityHelper
{
    public static function contains($haystack, $needle)
    {
        return strpos($haystack, $needle) !== false;
    }
    
    public static function startsWith($haystack, $needle)
    {
        return strpos($haystack, $needle) === 0;
    }
    
    public static function endsWith($haystack, $needle)
    {
        $length = strlen($needle);
        if ($length == 0) return true;
        return substr($haystack, -$length) === $needle;
    }
    
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
    
    public static function isZipFile($path)
    {
        return pathinfo($path, PATHINFO_EXTENSION) == 'zip';
    }
    
    public static function isStoreFolder($path)
    {
        return basename($path) == 'store';
    }
}