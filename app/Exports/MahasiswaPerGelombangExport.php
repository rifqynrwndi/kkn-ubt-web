<?php

namespace App\Exports;

use App\Models\Gelombang;
use App\Models\PesertaKkn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class MahasiswaPerGelombangExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $gelombangs = Gelombang::orderBy('tahun', 'desc')->orderBy('nama_gelombang')->get();

        $sheets = [];
        foreach ($gelombangs as $gelombang) {
            $sheets[] = new MahasiswaPerGelombangSheetExport($gelombang->id, $gelombang->nama_gelombang);
        }

        return $sheets;
    }
}

class MahasiswaPerGelombangSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    private int $gelombangId;

    private string $gelombangName;

    public function __construct(int $gelombangId, string $gelombangName)
    {
        $this->gelombangId = $gelombangId;
        $this->gelombangName = $gelombangName;
    }

    public function collection()
    {
        return PesertaKkn::with([
            'mahasiswa.user',
            'mahasiswa.prodi.fakultas',
            'kelompokKkn',
            'kelompokKkn.desaGelombang.desa',
        ])
            ->where('gelombang_id', $this->gelombangId)
            ->orderBy('status_pendaftaran')
            ->get();
    }

    public function headings(): array
    {
        return ['NPM', 'Nama', 'Prodi', 'Fakultas', 'Gelombang', 'Status', 'Kelompok', 'Desa'];
    }

    public function map($peserta): array
    {
        $m = $peserta->mahasiswa;
        $kelompok = $peserta->kelompokKkn;
        $desa = $kelompok?->desaGelombang?->desa?->nama_desa ?? '-';

        $statusLabels = [
            'draft' => 'Draft',
            'pending_documents' => 'Menunggu Dokumen',
            'pending_verification' => 'Menunggu Verifikasi',
            'revision' => 'Revisi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'expired' => 'Kadaluarsa',
        ];

        return [
            $m?->npm ?? '-',
            $m?->user?->name ?? '-',
            $m?->prodi?->nama_prodi ?? '-',
            $m?->prodi?->fakultas?->nama_fakultas ?? '-',
            $this->gelombangName,
            $statusLabels[$peserta->status_pendaftaran] ?? $peserta->status_pendaftaran,
            $kelompok?->nama_kelompok ?? '-',
            $desa,
        ];
    }

    public function title(): string
    {
        return str_replace(['\\', '/', '*', '?', '[', ']', ':'], '', $this->gelombangName);
    }
}
