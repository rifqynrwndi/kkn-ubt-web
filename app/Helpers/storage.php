<?php

if (!function_exists('storage_url')) {
    function storage_url(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $disk = env('FILESYSTEM_DISK', 'local');
        $s3Disks = ['s3', 'r2', 'do', 'b2'];

        if (in_array($disk, $s3Disks)) {
            return Storage::disk($disk)->url($path);
        }

        return asset('storage/' . $path);
    }
}
