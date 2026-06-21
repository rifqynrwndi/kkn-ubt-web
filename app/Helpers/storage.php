<?php

if (!function_exists('storage_url')) {
    function storage_url(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $disk = env('FILESYSTEM_DISK', 'local');

        if ($disk === 's3') {
            return url('/s3/' . $path);
        }

        return asset('storage/' . $path);
    }
}
