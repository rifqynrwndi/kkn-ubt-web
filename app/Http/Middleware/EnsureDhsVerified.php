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
            if (! $user->mahasiswa->hasDhsVerified()) {
                return redirect()->route('biodata.edit')
                    ->with('warning', 'Verifikasi DHS terlebih dahulu sebelum mendaftar KKN.');
            }
        }

        return $next($request);
    }
}
