<?php

namespace App\Services\War;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarSession;

class WarValidationService
{
    public function validateJoinRequest(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok): void
    {
        $this->assertSessionActive($session);
        $this->assertPesertaApproved($peserta);
        $this->assertSameGelombang($session, $peserta, $kelompok);
        $this->assertKelompokAvailable($kelompok);
        $this->assertPesertaBelumPunyaKelompok($peserta);
        $this->assertFakultasGiliranAktif($session, $peserta);
    }

    private function assertSessionActive(WarSession $session): void
    {
        if ($session->status !== 'active') {
            throw new \RuntimeException('WAR session sedang tidak aktif.');
        }

        if (now()->lt($session->start_at)) {
            throw new \RuntimeException('WAR belum dimulai.');
        }

        if (now()->gt($session->end_at)) {
            throw new \RuntimeException('WAR sudah berakhir.');
        }
    }

    private function assertPesertaApproved(PesertaKkn $peserta): void
    {
        if ($peserta->status_pendaftaran !== 'approved') {
            throw new \RuntimeException('Status pendaftaran kamu belum disetujui.');
        }
    }

    private function assertSameGelombang(WarSession $session, PesertaKkn $peserta, KelompokKkn $kelompok): void
    {
        if ($peserta->gelombang_id !== $session->gelombang_id) {
            throw new \RuntimeException('Kamu tidak terdaftar di gelombang ini.');
        }

        if ($kelompok->desaGelombang->gelombang_id !== $session->gelombang_id) {
            throw new \RuntimeException('Kelompok ini tidak tersedia di gelombang ini.');
        }
    }

    private function assertKelompokAvailable(KelompokKkn $kelompok): void
    {
        if ($kelompok->status === 'penuh') {
            throw new \RuntimeException('Kelompok ini sudah penuh.');
        }
    }

    private function assertPesertaBelumPunyaKelompok(PesertaKkn $peserta): void
    {
        if ($peserta->kelompok_kkn_id !== null) {
            throw new \RuntimeException('Kamu sudah terdaftar di kelompok lain.');
        }
    }

    private function assertFakultasGiliranAktif(WarSession $session, PesertaKkn $peserta): void
    {
        $fakultasId = $peserta->mahasiswa->prodi->fakultas_id;

        $warFaculty = WarFaculty::where('war_session_id', $session->id)
            ->where('fakultas_id', $fakultasId)
            ->first();

        if (! $warFaculty) {
            throw new \RuntimeException('Fakultas kamu belum dikonfigurasi di sesi WAR ini. Hubungi admin.');
        }

        if (! $warFaculty->start_at) {
            throw new \RuntimeException('Jadwal WAR untuk fakultas kamu belum diatur. Hubungi admin.');
        }

        if (now()->lt($warFaculty->start_at)) {
            $mulai = $warFaculty->start_at->format('d M Y, H:i');
            throw new \RuntimeException("Giliran fakultas kamu belum dimulai. Jadwal mulai: {$mulai}.");
        }

        if (now()->gt($warFaculty->end_at)) {
            throw new \RuntimeException('Giliran WAR untuk fakultas kamu sudah berakhir.');
        }
    }
}
