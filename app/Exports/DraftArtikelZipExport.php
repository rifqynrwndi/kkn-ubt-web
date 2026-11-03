<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use ZipArchive;

class DraftArtikelZipExport
{
    private string $zipPath = '';

    /**
     * @param  Collection  $rows  Hasil DraftArtikelService::rows()
     * @param  bool  $groupByDpl  true = ZIP admin (DPL/Kelompok), false = ZIP DPL (Kelompok)
     */
    public function __construct(
        private Collection $rows,
        private string $gelombangName,
        private bool $groupByDpl = false,
    ) {}

    public function generate(): string
    {
        $entries = $this->entries();

        $tempDir = storage_path('app/draft-artikel-'.uniqid());
        $this->zipPath = storage_path('app/draft-artikel-'.uniqid().'.zip');

        $zip = new ZipArchive;

        try {
            if (! @mkdir($tempDir, 0755, true) && ! is_dir($tempDir)) {
                throw new \RuntimeException('Tidak dapat membuat direktori sementara.');
            }

            $indexPath = $tempDir.'/index.xlsx';
            $this->writeIndex($entries, $indexPath);

            if ($zip->open($this->zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Tidak dapat membuat file ZIP.');
            }

            $zip->addFile($indexPath, 'index.xlsx');

            foreach ($entries as $entry) {
                if ($entry['zip_path'] === null) {
                    continue;
                }

                $zip->addFile(
                    Storage::disk('public')->path($entry['submission']->file_path),
                    $entry['zip_path']
                );
            }

            $zip->close();
        } catch (\Throwable $e) {
            @$zip->close();
            @unlink($this->zipPath);

            throw $e;
        } finally {
            $this->deleteDirectory($tempDir);
        }

        return $this->zipPath;
    }

    /**
     * Lengkapi setiap baris dengan path di dalam ZIP, sekaligus menangani
     * nama folder kelompok yang bentrok.
     */
    private function entries(): array
    {
        $usedFolders = [];
        $entries = [];

        foreach ($this->rows as $row) {
            $entry = $row + ['zip_path' => null];

            if ($row['file_exists']) {
                $baseFolder = $this->folder($row);
                $folder = $baseFolder;
                $suffix = 2;

                while (isset($usedFolders[$folder])) {
                    $folder = $baseFolder.'_'.$suffix++;
                }

                $usedFolders[$folder] = true;
                $entry['zip_path'] = $folder.'/'.$this->fileName($row);
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    private function folder(array $row): string
    {
        $kelompok = 'Kelompok_'.($this->sanitize($row['kelompok']->nama_kelompok) ?: 'Tanpa_Nama');

        if (! $this->groupByDpl) {
            return $kelompok;
        }

        $dplName = $row['kelompok']->dosenPembimbingLapangan?->user?->name;
        $dplFolder = $dplName
            ? 'DPL_'.($this->sanitize($dplName) ?: 'Tanpa_Nama')
            : 'Tanpa_DPL';

        return $dplFolder.'/'.$kelompok;
    }

    private function fileName(array $row): string
    {
        $submission = $row['submission'];

        $ext = strtolower(pathinfo($submission->file_name ?: $submission->file_path, PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?? '';
        $ext = $ext !== '' ? substr($ext, 0, 10) : 'pdf';

        $judul = $this->sanitize($submission->judul, 60) ?: 'Tanpa_Judul';
        $ketua = $this->sanitize($submission->pesertaKkn?->mahasiswa?->user?->name, 40);

        $name = 'Draft_Artikel_'.$judul;

        if ($ketua !== '') {
            $name .= '_'.$ketua;
        }

        return $name.'.'.$ext;
    }

    private function writeIndex(array $entries, string $path): void
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Draft Artikel');

        $headers = [
            'No', 'Gelombang', 'Kelompok', 'Desa', 'DPL', 'Pengumpul',
            'Judul', 'Status', 'Komentar DPL', 'Nama File', 'Path di ZIP', 'Catatan',
        ];

        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);

        $statusLabels = [
            'menunggu' => 'Menunggu',
            'diterima' => 'Diterima',
            'ditolak' => 'Ditolak',
            'revisi' => 'Revisi',
        ];

        $no = 0;

        foreach ($entries as $entry) {
            $no++;
            $kelompok = $entry['kelompok'];
            $submission = $entry['submission'];

            $sheet->fromArray([[
                $no,
                $this->gelombangName,
                $kelompok->nama_kelompok,
                $kelompok->desaGelombang?->desa?->nama_desa ?? '-',
                $kelompok->dosenPembimbingLapangan?->user?->name ?? '-',
                $submission?->pesertaKkn?->mahasiswa?->user?->name ?? '-',
                $submission?->judul ?? '-',
                $submission ? ($statusLabels[$submission->status] ?? $submission->status) : '-',
                $submission?->komentar_dpl ?: '-',
                $submission?->file_name ?? '-',
                $entry['zip_path'] ?? '-',
                $entry['catatan'],
            ]], null, 'A'.($no + 1));
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($path);
    }

    private function sanitize(?string $value, int $max = 80): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = $ascii !== false ? $ascii : $value;
        $ascii = preg_replace('/[^A-Za-z0-9]+/', '_', $ascii) ?? '';
        $ascii = trim($ascii, '_');

        return $ascii === '' ? '' : mb_substr($ascii, 0, $max);
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
}
