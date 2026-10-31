<?php

namespace App\Exports;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;

class MahasiswaPerGelombangExport implements WithMultipleSheets
{
    private ?int $gelombangId;

    public function __construct(?int $gelombangId = null)
    {
        $this->gelombangId = $gelombangId;
    }

    public function sheets(): array
    {
        $query = Gelombang::orderBy('tahun', 'desc')->orderBy('nama_gelombang');

        if ($this->gelombangId) {
            $query->where('id', $this->gelombangId);
        }

        $gelombangs = $query->get();

        $sheets = [];

        foreach ($gelombangs as $gelombang) {
            $sheets[] = new MahasiswaPerGelombangSheetExport($gelombang->id, $gelombang->nama_gelombang);
        }

        // Add "Belum Terdaftar" sheet for each gelombang (or all if no filter)
        foreach ($gelombangs as $gelombang) {
            $sheets[] = new BelumTerdaftarSheetExport($gelombang->id, $gelombang->nama_gelombang);
        }

        // If no gelombang filter, also add a combined "Belum Terdaftar Semua Gelombang" sheet
        if (! $this->gelombangId && $gelombangs->count() > 1) {
            $sheets[] = new BelumTerdaftarSemuaGelombangSheetExport($gelombangs->pluck('id')->toArray());
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

    public function collection(): \Illuminate\Support\Enumerable
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
        $title = str_replace(['\\', '/', '*', '?', '[', ']', ':'], '', $this->gelombangName);

        return strlen($title) > 31 ? substr($title, 0, 31) : $title;
    }
}

class BelumTerdaftarSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    private int $gelombangId;

    private string $gelombangName;

    public function __construct(int $gelombangId, string $gelombangName)
    {
        $this->gelombangId = $gelombangId;
        $this->gelombangName = $gelombangName;
    }

    public function collection(): \Illuminate\Support\Enumerable
    {
        // Get mahasiswa IDs that already have PesertaKkn for this gelombang
        $registeredMahasiswaIds = PesertaKkn::where('gelombang_id', $this->gelombangId)
            ->pluck('mahasiswa_id')
            ->toArray();

        return Mahasiswa::with(['user', 'prodi.fakultas'])
            ->whereNotIn('id', $registeredMahasiswaIds)
            ->orderBy('npm')
            ->get();
    }

    public function headings(): array
    {
        return ['NPM', 'Nama', 'Prodi', 'Fakultas'];
    }

    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->npm ?? '-',
            $mahasiswa->user?->name ?? '-',
            $mahasiswa->prodi?->nama_prodi ?? '-',
            $mahasiswa->prodi?->fakultas?->nama_fakultas ?? '-',
        ];
    }

    public function title(): string
    {
        $title = 'Belum Terdaftar - '.$this->gelombangName;

        $title = str_replace(['\\', '/', '*', '?', '[', ']', ':'], '', $title);

        return strlen($title) > 31 ? substr($title, 0, 31) : $title;
    }
}

class BelumTerdaftarSemuaGelombangSheetExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    private array $gelombangIds;

    public function __construct(array $gelombangIds)
    {
        $this->gelombangIds = $gelombangIds;
    }

    public function collection()
    {
        // Get mahasiswa IDs that have PesertaKkn in ANY of these gelombangs
        $registeredMahasiswaIds = PesertaKkn::whereIn('gelombang_id', $this->gelombangIds)
            ->pluck('mahasiswa_id')
            ->unique()
            ->toArray();

        return Mahasiswa::with(['user', 'prodi.fakultas'])
            ->whereNotIn('id', $registeredMahasiswaIds)
            ->orderBy('npm')
            ->get();
    }

    public function headings(): array
    {
        return ['NPM', 'Nama', 'Prodi', 'Fakultas'];
    }

    public function map($mahasiswa): array
    {
        return [
            $mahasiswa->npm ?? '-',
            $mahasiswa->user?->name ?? '-',
            $mahasiswa->prodi?->nama_prodi ?? '-',
            $mahasiswa->prodi?->fakultas?->nama_fakultas ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Belum Terdaftar (Semua Gelombang)';
    }
}
