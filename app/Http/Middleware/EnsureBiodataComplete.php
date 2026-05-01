<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Controllers\BiodataController;

class EnsureBiodataComplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if (
            $user->hasRole('mahasiswa') &&
            $user->mahasiswa &&
            !$user->mahasiswa->is_biodata_complete &&
            !$request->routeIs('biodata.*')
        ) {
            return redirect()->route('biodata.edit')
                ->with('warning', 'Lengkapi biodata terlebih dahulu.');
        }

        return $next($request);
    }
}
