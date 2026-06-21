<?php

if (!function_exists('storage_url')) {
    function storage_url(?string $path): string
    {
        if (!$path) {
            return '';
        }

        $disk = env('FILESYSTEM_DISK', 'local');

        if ($disk === 's3') {
            try {
                return Storage::disk('s3')->temporaryUrl($path, now()->addDay());
            } catch (\Throwable $e) {
                return Storage::disk('s3')->url($path);
            }
        }

        return asset('storage/' . $path);
    }
}
