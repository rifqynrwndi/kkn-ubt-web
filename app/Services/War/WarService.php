<?php

namespace App\Services\War;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarSession;

class WarService
{
    public function __construct(
        private readonly WarValidationService $validationService,
        private readonly WarAllocationService $allocationService,
    ) {}

    public function joinKelompok(WarSession $session, int $kelompokId, int $mahasiswaId): array
    {
        $peserta  = PesertaKkn::where('mahasiswa_id', $mahasiswaId)
            ->where('gelombang_id', $session->gelombang_id)
            ->with(['mahasiswa.prodi.fakultas'])
            ->firstOrFail();

        $kelompok = KelompokKkn::findOrFail($kelompokId);

        $this->validationService->validateJoinRequest($session, $peserta, $kelompok);

        $participant = $this->allocationService->allocate($session, $peserta, $kelompok);

        return [
            'success'       => true,
            'message'       => 'Berhasil bergabung ke ' . $kelompok->nama_kelompok . '!',
            'kelompok_id'   => $kelompok->id,
            'kelompok_nama' => $kelompok->nama_kelompok,
            'joined_at'     => $participant->joined_at->toISOString(),
        ];
    }
}
