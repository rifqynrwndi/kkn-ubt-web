<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileProxyController extends Controller
{
    public function streamS3(Request $request, string $path)
    {
        $disk = env('FILESYSTEM_DISK') === 's3' ? 's3' : 'public';

        if (!Storage::disk($disk)->exists($path)) {
            abort(404);
        }

        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }
}
