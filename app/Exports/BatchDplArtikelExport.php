<?php

namespace App\Exports;

use App\Models\LaporanDpl;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BatchDplArtikelExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    private array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = LaporanDpl::with([
            'kelompokKkn.desaGelombang.desa.kecamatan',
            'kelompokKkn.dosenPembimbingLapangan.user',
            'dpl.user',
        ]);

        // Filter by gelombang
        if (! empty($this->filters['gelombang_id'])) {
            $query->whereHas('kelompokKkn.desaGelombang', fn ($q) => $q->where('gelombang_id', $this->filters['gelombang_id'])
            );
        }

        // Filter by DPL
        if (! empty($this->filters['dpl_id'])) {
            $query->where('dpl_id', $this->filters['dpl_id']);
        }

        // Filter by jenis
        if (! empty($this->filters['jenis'])) {
            $query->where('jenis', $this->filters['jenis']);
        }

        // Filter by date range
        if (! empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        // Filter by search (judul/deskripsi)
        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'DPL',
            'Kelompok',
            'Desa',
            'Kecamatan',
            'Jenis Laporan',
            'Judul',
            'Deskripsi',
            'File Name',
            'File Path',
        ];
    }

    public function map($laporan): array
    {
        static $no = 0;
        $no++;

        $kelompok = $laporan->kelompokKkn;
        $desa = $kelompok?->desaGelombang?->desa?->nama_desa ?? '-';
        $kecamatan = $kelompok?->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-';
        $dplName = $laporan->dpl?->user?->name ?? '-';

        $jenisLabels = [
            'mingguan' => 'Laporan Mingguan',
            'akhir' => 'Laporan Akhir',
            'khusus' => 'Laporan Khusus',
        ];

        return [
            $no,
            $laporan->created_at?->format('d-m-Y H:i') ?? '-',
            $dplName,
            $kelompok?->nama_kelompok ?? '-',
            $desa,
            $kecamatan,
            $jenisLabels[$laporan->jenis] ?? $laporan->jenis,
            $laporan->judul ?? '-',
            $laporan->deskripsi ?? '-',
            $laporan->file_name ?? '-',
            $laporan->file_path ? url('storage/'.$laporan->file_path) : '-',
        ];
    }
}
