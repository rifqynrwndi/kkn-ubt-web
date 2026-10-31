<?php

namespace App\Policies;

use App\Models\User;
use App\Models\WarSession;
use Illuminate\Support\Facades\Cache;

class WarChannelPolicy
{
    public function join(User $user, int $sessionId): bool
    {
        // Superadmin can join any channel
        if ($user->hasRole('superadmin')) {
            return true;
        }

        $mahasiswa = $user->mahasiswa;

        if (! $mahasiswa) {
            return false;
        }

        // Cache session gelombang_id for 60s to avoid DB hit per auth check
        $gelombangId = Cache::remember("war:session:gelombang:{$sessionId}", 60, function () use ($sessionId) {
            return WarSession::where('id', $sessionId)->value('gelombang_id');
        });

        if (! $gelombangId) {
            return false;
        }

        // Check if user's mahasiswa is in the same gelombang
        $pesertaGelombangId = $mahasiswa->pesertaKkn()
            ->where('gelombang_id', $gelombangId)
            ->where('status_pendaftaran', 'approved')
            ->value('gelombang_id');

        return $pesertaGelombangId === $gelombangId;
    }
}
