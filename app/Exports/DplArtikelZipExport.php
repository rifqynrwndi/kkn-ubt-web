<?php

namespace App\Exports;

use App\Models\LaporanDpl;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class DplArtikelZipExport
{
    private array $filters;

    private string $zipFileName;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->zipFileName = 'artikel_dpl_'.now()->format('Ymd_His').'.zip';
    }

    public function generate(): string
    {
        $tempDir = storage_path('app/temp_zip_'.uniqid());
        @mkdir($tempDir, 0755, true);

        $indexPath = $tempDir.'/index.xlsx';
        $this->generateIndexExcel($indexPath);

        $zipPath = storage_path('app/'.$this->zipFileName);
        $zip = new ZipArchive;
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Add index.xlsx
        $zip->addFile($indexPath, 'index.xlsx');

        // Get laporans
        $laporans = $this->getLaporans();

        foreach ($laporans as $laporan) {
            if (! $laporan->file_path || ! Storage::disk('public')->exists($laporan->file_path)) {
                continue;
            }

            $dplName = $laporan->dpl?->user?->name ?? 'Tanpa_DPL';
            $kelompokName = $laporan->kelompokKkn?->nama_kelompok ?? 'Tanpa_Kelompok';

            // Sanitize folder names
            $dplFolder = $this->sanitize($dplName);
            $kelompokFolder = $this->sanitize($kelompokName);

            $ext = pathinfo($laporan->file_name ?? $laporan->file_path, PATHINFO_EXTENSION);
            $fileName = sprintf(
                '%s_%s.%s',
                $laporan->id,
                $laporan->jenis,
                $ext ?: 'pdf'
            );

            $zipPathInArchive = "{$dplFolder}/{$kelompokFolder}/{$fileName}";
            $fullFilePath = Storage::disk('public')->path($laporan->file_path);

            if (file_exists($fullFilePath)) {
                $zip->addFile($fullFilePath, $zipPathInArchive);
            }
        }

        $zip->close();

        // Cleanup temp
        $this->deleteDirectory($tempDir);

        return $zipPath;
    }

    private function generateIndexExcel(string $path): void
    {
        $laporans = $this->getLaporans();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Index');

        $headers = [
            'No', 'ID Laporan', 'Tanggal', 'DPL', 'Kelompok', 'Desa',
            'Jenis', 'Judul', 'File Name', 'Path di ZIP',
        ];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);

        $row = 2;
        $no = 0;
        foreach ($laporans as $laporan) {
            $no++;
            $kelompok = $laporan->kelompokKkn;
            $desa = $kelompok?->desaGelombang?->desa?->nama_desa ?? '-';
            $dplName = $laporan->dpl?->user?->name ?? '-';
            $kelompokName = $kelompok?->nama_kelompok ?? '-';

            $dplFolder = $this->sanitize($dplName);
            $kelompokFolder = $this->sanitize($kelompokName);
            $ext = pathinfo($laporan->file_name ?? $laporan->file_path, PATHINFO_EXTENSION);
            $fileName = sprintf('%s_%s.%s', $laporan->id, $laporan->jenis, $ext ?: 'pdf');
            $zipPath = "{$dplFolder}/{$kelompokFolder}/{$fileName}";

            $sheet->fromArray([[
                $no,
                $laporan->id,
                $laporan->created_at?->format('d-m-Y H:i') ?? '-',
                $dplName,
                $kelompokName,
                $desa,
                $laporan->jenis,
                $laporan->judul ?? '-',
                $laporan->file_name ?? '-',
                $zipPath,
            ]], null, "A{$row}");
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }

    private function getLaporans()
    {
        $query = LaporanDpl::with([
            'kelompokKkn.desaGelombang.desa.kecamatan',
            'kelompokKkn.dosenPembimbingLapangan.user',
            'dpl.user',
        ]);

        if (! empty($this->filters['gelombang_id'])) {
            $query->whereHas('kelompokKkn.desaGelombang', fn ($q) => $q->where('gelombang_id', $this->filters['gelombang_id'])
            );
        }

        if (! empty($this->filters['dpl_id'])) {
            $query->where('dpl_id', $this->filters['dpl_id']);
        }

        if (! empty($this->filters['jenis'])) {
            $query->where('jenis', $this->filters['jenis']);
        }

        if (! empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }
        if (! empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (! empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        return $query->latest()->get();
    }

    private function sanitize(string $name): string
    {
        return preg_replace('/[^a-zA-Z0-9_\-]/', '_', $name);
    }

    private function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function getZipFileName(): string
    {
        return $this->zipFileName;
    }

    public function getZipPath(): string
    {
        return storage_path('app/'.$this->zipFileName);
    }
}
