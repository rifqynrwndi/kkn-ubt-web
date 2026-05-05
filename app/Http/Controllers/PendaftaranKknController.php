<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\PesertaKkn;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PendaftaranKknController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $gelombangAktif = Gelombang::whereIn('status', [
            'pendaftaran',
            'berjalan'
        ])->latest()->first();

        $pendaftaran = null;

        if ($gelombangAktif) {
            $pendaftaran = PesertaKkn::where('mahasiswa_id', $user->id)
                ->where('gelombang_id', $gelombangAktif->id)
                ->first();
        }

        return view('pendaftaran-kkn.index', [
            'gelombang' => $gelombangAktif,
            'pendaftaran' => $pendaftaran,
        ]);
    }

    public function store(): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->hasVerifiedEmail()) {
            return back()->with('error', 'Verifikasi email terlebih dahulu.');
        }

        if (! $user->hasCompletedBiodata()) {
            return back()->with('error', 'Lengkapi biodata terlebih dahulu.');
        }

        $gelombangAktif = Gelombang::whereIn('status', [
            'pendaftaran',
            'berjalan'
        ])->latest()->first();

        if (! $gelombangAktif) {
            return back()->with('error', 'Tidak ada gelombang aktif.');
        }

        $alreadyRegistered = PesertaKkn::where('mahasiswa_id', $user->id)
            ->where('gelombang_id', $gelombangAktif->id)
            ->exists();

        if ($alreadyRegistered) {
            return back()->with('error', 'Anda sudah terdaftar pada gelombang ini.');
        }

        PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'gelombang_id' => $gelombangAktif->id,
            'status_pendaftaran' => 'draft',
            'submitted_at' => now(),
        ]);

        return redirect()
            ->route('pendaftaran-kkn.index')
            ->with('success', 'Berhasil mendaftar KKN. Silakan lengkapi dokumen pendaftaran.');
    }
}
