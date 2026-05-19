<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEmailVerifiedExceptSuperadmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // kalau superadmin, skip verifikasi email
        if ($user && $user->hasRole('superadmin')) {
            return $next($request);
        }

        // belum verifikasi → arahkan ke biodata (verifikasi lewat biodata edit)
        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->route('biodata.edit');
        }

        return $next($request);
    }
}
