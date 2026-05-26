<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerifiedExceptSuperadmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // superadmin & pembimbing tidak perlu verifikasi email via biodata
        if ($user && ($user->hasRole('superadmin') || $user->hasRole('pembimbing'))) {
            return $next($request);
        }

        // mahasiswa: belum verifikasi → arahkan ke biodata
        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->route('biodata.edit');
        }

        return $next($request);
    }
}
