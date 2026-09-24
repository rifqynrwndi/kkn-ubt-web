<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureDhsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->hasRole('mahasiswa') && $user->mahasiswa) {
            $mhs = $user->mahasiswa;

            if ($mhs->hasDhsVerified()) {
                return $next($request);
            }

            if (! $mhs->dhs_path) {
                return redirect()->route('biodata.edit')
                    ->with('warning', 'Anda belum mengunggah DHS. Silakan unggah Daftar Hasil Studi terlebih dahulu.');
            }

            if ($mhs->dhs_status === 'rejected') {
                return redirect()->route('biodata.edit')
                    ->with('warning', 'DHS Anda ditolak oleh admin. Silakan periksa catatan dan unggah ulang DHS.');
            }

            return redirect()->route('biodata.edit')
                ->with('warning', 'DHS sudah diunggah. Silakan tunggu verifikasi oleh admin sebelum mendaftar KKN.');
        }

        return $next($request);
    }
}
