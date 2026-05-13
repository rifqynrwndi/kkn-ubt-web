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

        // default Laravel behavior
        if (! $user || ! $user->hasVerifiedEmail()) {
            return redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
