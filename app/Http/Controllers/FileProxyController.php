<?php

namespace App\Http\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\DosenPembimbingLapangan;
use App\Models\File;
use App\Models\KelompokKkn;
use App\Models\LogBook;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\TugasSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FileProxyController extends Controller
{
    private array $adminRoles = ['superadmin', 'admin_lppm', 'admin_prodi'];

    public function streamS3(Request $request, string $path)
    {
        $disk = 'public';

        try {
            if (! Storage::disk($disk)->exists($path)) {
                abort(404);
            }
        } catch (\Throwable) {
            abort(404);
        }

        if (! $this->canAccess($request->user(), $path)) {
            abort(403, 'Anda tidak memiliki akses ke file ini.');
        }

        $mimeType = Storage::disk($disk)->mimeType($path);
        $size = Storage::disk($disk)->size($path);

        return response()->stream(function () use ($disk, $path) {
            $stream = Storage::disk($disk)->readStream($path);
            if (is_resource($stream)) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    private function canAccess($user, string $path): bool
    {
        $firstDir = explode('/', $path)[0] ?? '';

        return match ($firstDir) {
            'dokumen-dhs' => $this->authorizeDokumenDhs($user, $path),
            'dokumen-pendaftaran' => $this->authorizeDokumenPendaftaran($user, $path),
            'logbook' => $this->authorizeLogbook($user, $path),
            'tugas-submission' => $this->authorizeTugasSubmission($user, $path),
            'foto-mahasiswa' => $this->authorizeFotoMahasiswa($user, $path),
            'foto_kelompok' => $this->authorizeFotoKelompok($user, $path),
            'foto' => $this->authorizeFoto($user, $path),
            default => true,
        };
    }

    private function authorizeDokumenDhs($user, string $path): bool
    {
        if ($this->hasAnyRole($user, ['superadmin', 'admin_lppm'])) {
            return true;
        }

        $mahasiswa = Mahasiswa::where('dhs_path', $path)->first();
        if ($mahasiswa && $mahasiswa->user_id === $user->id) {
            return true;
        }

        return false;
    }

    private function authorizeDokumenPendaftaran($user, string $path): bool
    {
        if ($this->hasAnyRole($user, ['superadmin', 'admin_prodi'])) {
            return true;
        }

        if ($user->hasRole('pembimbing')) {
            return true;
        }

        $file = File::where('path', $path)->first();
        if (! $file) {
            return false;
        }

        $dokumen = DokumenPendaftaran::where('file_id', $file->id)->first();
        if (! $dokumen) {
            return false;
        }

        $peserta = PesertaKkn::find($dokumen->peserta_kkn_id);
        if ($peserta && $peserta->mahasiswa_id === $user->id) {
            return true;
        }

        return false;
    }

    private function authorizeLogbook($user, string $path): bool
    {
        if ($this->hasAnyRole($user, $this->adminRoles)) {
            return true;
        }

        $logbook = LogBook::where('file_path', $path)->first();
        if (! $logbook) {
            return false;
        }

        if ($user->hasRole('pembimbing')) {
            $peserta = PesertaKkn::find($logbook->peserta_kkn_id);
            if ($peserta && $peserta->kelompok_kkn_id) {
                $kelompok = KelompokKkn::find($peserta->kelompok_kkn_id);
                if ($kelompok && $kelompok->dosen_pembimbing_lapangan_id === $user->id) {
                    return true;
                }
            }

            return false;
        }

        $peserta = PesertaKkn::find($logbook->peserta_kkn_id);
        if ($peserta && $peserta->mahasiswa_id === $user->id) {
            return true;
        }

        return false;
    }

    private function authorizeTugasSubmission($user, string $path): bool
    {
        if ($this->hasAnyRole($user, $this->adminRoles)) {
            return true;
        }

        if ($user->hasRole('pembimbing')) {
            return true;
        }

        $submission = TugasSubmission::where('file_path', $path)->first();
        if (! $submission) {
            return false;
        }

        $peserta = PesertaKkn::find($submission->peserta_kkn_id);
        if ($peserta && $peserta->mahasiswa_id === $user->id) {
            return true;
        }

        return false;
    }

    private function authorizeFotoMahasiswa($user, string $path): bool
    {
        if ($this->hasAnyRole($user, $this->adminRoles)) {
            return true;
        }

        if ($user->hasRole('pembimbing')) {
            return true;
        }

        $mahasiswa = Mahasiswa::where('foto', $path)->first();
        if ($mahasiswa && $mahasiswa->user_id === $user->id) {
            return true;
        }

        return false;
    }

    private function authorizeFotoKelompok($user, string $path): bool
    {
        if ($this->hasAnyRole($user, $this->adminRoles)) {
            return true;
        }

        if ($user->hasRole('pembimbing')) {
            return true;
        }

        $kelompok = KelompokKkn::where('foto_kelompok', $path)->first();
        if (! $kelompok) {
            return false;
        }

        $peserta = PesertaKkn::where('kelompok_kkn_id', $kelompok->id)
            ->where('mahasiswa_id', $user->id)
            ->exists();

        return $peserta;
    }

    private function authorizeFoto($user, string $path): bool
    {
        if ($this->hasAnyRole($user, $this->adminRoles)) {
            return true;
        }

        if ($user->hasRole('pembimbing')) {
            $dpl = DosenPembimbingLapangan::where('foto', $path)->first();
            if ($dpl) {
                return true;
            }
        }

        $dpl = DosenPembimbingLapangan::where('foto', $path)->first();
        if (! $dpl) {
            return false;
        }

        if ($dpl->user_id === $user->id) {
            return true;
        }

        if ($user->hasRole('mahasiswa')) {
            $peserta = PesertaKkn::where('mahasiswa_id', $user->id)->first();
            if ($peserta && $peserta->kelompok_kkn_id) {
                $kelompok = KelompokKkn::find($peserta->kelompok_kkn_id);
                if ($kelompok && $kelompok->dosen_pembimbing_lapangan_id === $dpl->user_id) {
                    return true;
                }
            }
        }

        return false;
    }

    private function hasAnyRole($user, array $roles): bool
    {
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}
