<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        if (auth()->user()->hasRole('superadmin')) {
            return $this->adminDashboard();
        }

        if (auth()->user()->hasRole('pembimbing')) {
            return $this->dplDashboard();
        }

        return $this->userDashboard();
    }

    private function dplDashboard(): View
    {
        $dpl = auth()->user()->dosenPembimbingLapangan;

        if ($dpl) {
            $dpl->load(['kelompokKkn' => function ($q) {
                $q->withCount('pesertaKkn')
                  ->with(['desaGelombang.desa.kecamatan']);
            }]);
        }

        return view('home-dpl', compact('dpl'));
    }

    private function adminDashboard(): View
    {
        $activeGelombang = Gelombang::whereIn('status', ['pendaftaran', 'berjalan'])
            ->latest()
            ->first();

        $stats = [
            'total_mahasiswa' => User::role('mahasiswa')->count(),

            'verified' => User::role('mahasiswa')
                ->whereNotNull('email_verified_at')
                ->count(),

            'incomplete_biodata' => User::role('mahasiswa')
                ->where(function ($q) {
                    $q->whereDoesntHave('mahasiswa')
                    ->orWhereHas('mahasiswa', function ($q2) {
                        $q2->where('is_biodata_complete', false);
                    });
                })
                ->count(),
        ];

        $gelombangs = Gelombang::withCount([
            'pesertaKkn as peserta_kkn_count' => function ($q) {
                $q->where('status_pendaftaran', 'approved');
            }
        ])->get();

        $gelombangChart = [
            'labels' => $gelombangs->pluck('nama_gelombang'),
            'data' => $gelombangs->pluck('peserta_kkn_count'),
        ];

        $latestMahasiswa = User::role('mahasiswa')
            ->latest()
            ->take(10)
            ->get();

        $reminders = [];

        if ($stats['incomplete_biodata'] > 0) {
            $reminders[] = $stats['incomplete_biodata'] . ' mahasiswa belum melengkapi biodata.';
        }

        if (!$activeGelombang) {
            $reminders[] = 'Tidak ada gelombang aktif saat ini.';
        }

        return view('home-admin', compact(
            'stats',
            'gelombangChart',
            'latestMahasiswa',
            'reminders',
            'activeGelombang'
        ));
    }

    private function userDashboard(): View
    {
        $user = auth()->user();

        $pendaftaran = $user->mahasiswa?->pesertaKkn()
            ->latest()
            ->with('gelombang')
            ->first();

        $recentNotifications = $user->notifications()
            ->latest()
            ->take(5)
            ->get();

        // Cek apakah ada gelombang aktif untuk ditampilkan di dashboard

        $activeGelombang = Gelombang::whereIn('status', ['pendaftaran', 'berjalan'])
            ->latest()
            ->first();

        $reminders = [];

        if (!$user->hasCompletedBiodata()) {
            $reminders[] = 'Lengkapi biodata Anda sebelum mendaftar KKN.';
        }

        if (!$pendaftaran) {
            $reminders[] = 'Anda belum mendaftar KKN.';
        }

        return view('home-user', [
            'biodataComplete' => $user->hasCompletedBiodata(),
            'registrationStatus' => $pendaftaran ? 'Sudah Daftar' : 'Belum Daftar',
            'activeGelombang' => $activeGelombang?->nama_gelombang,
            'pendaftaran' => $pendaftaran,
            'recentNotifications' => $recentNotifications,
            'reminders' => $reminders,
        ]);
    }
}
