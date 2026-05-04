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

        return $this->userDashboard();
    }

    private function adminDashboard(): View
    {
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

            'active_gelombang' => Gelombang::where('status', 'berjalan')->count(),
        ];

        $gelombangs = Gelombang::withCount([
            'pesertaKkn as peserta_kkn_count' => function ($q) {
                $q->where('status_pendaftaran', 'verified');
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

        if ($stats['active_gelombang'] == 0) {
            $reminders[] = 'Tidak ada gelombang aktif saat ini.';
        }

        return view('home-admin', compact(
            'stats',
            'gelombangChart',
            'latestMahasiswa',
            'reminders'
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

        $activeGelombang = Gelombang::where('status', 'berjalan')->first();

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

    public function blank(): View
    {
        return view('layouts.blank-page');
    }
}
