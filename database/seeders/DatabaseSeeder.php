<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // ── Core system (wajib di semua environment) ──
            SettingSeeder::class,
            RolePermissionSeeder::class,
            SuperAdminSeeder::class,

            // ── Data master UBT (fakultas & prodi asli) ──
            FakultasProdiSeeder::class,

            // ── Gelombang: hanya dev/staging, production via admin UI ──
            GelombangSeeder::class,

            // ── Data desa KKN (lokasi asli Kalimantan Utara) ──
            DesaSeeder::class,

            // ── Kelompok & kuota ──
            KelompokKknSeeder::class,
            KelompokKuotaSeeder::class,

            // ── Slot WAR (jika ada sesi WAR) ──
            WarFacultySeeder::class,
        ]);
    }
}
