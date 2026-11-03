<?php

namespace App\Services;

use App\Models\KelompokKkn;
use App\Support\TugasTemplate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class DraftArtikelService
{
    public const MAX_FILES = 500;

    public const MAX_BYTES = 1073741824; // 1 GB

    /**
     * Ambil satu baris per kelompok untuk satu gelombang, berisi submission
     * Draft Artikel terbaru (semua status).
     */
    public function rows(int $gelombangId, ?int $dplId = null): Collection
    {
        return KelompokKkn::query()
            ->with([
                'desaGelombang.desa',
                'desaGelombang.gelombang',
                'dosenPembimbingLapangan.user',
                'tugasKelompok' => fn ($q) => $q
                    ->where('nama_tugas', TugasTemplate::DRAFT_ARTIKEL)
                    ->with(['submissions.pesertaKkn.mahasiswa.user']),
            ])
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $gelombangId))
            ->when($dplId, fn ($q) => $q->where('dosen_pembimbing_lapangan_id', $dplId))
            ->orderBy('nama_kelompok')
            ->get()
            ->map(fn (KelompokKkn $kelompok) => $this->row($kelompok))
            ->values();
    }

    private function row(KelompokKkn $kelompok): array
    {
        $tugas = $kelompok->tugasKelompok->sortByDesc('id')->first();
        $submission = $tugas?->submissions->sortByDesc('id')->first();

        $fileExists = $submission
            && $submission->file_path
            && Storage::disk('public')->exists($submission->file_path);

        $catatan = match (true) {
            $kelompok->tugasKelompok->isEmpty() => 'Belum ada tugas Draft Artikel',
            ! $submission => 'Belum mengumpulkan',
            ! $fileExists => 'File hilang',
            default => 'OK',
        };

        $size = 0;
        if ($fileExists) {
            $size = (int) ($submission->file_size ?: Storage::disk('public')->size($submission->file_path));
        }

        return [
            'kelompok' => $kelompok,
            'submission' => $submission,
            'file_exists' => (bool) $fileExists,
            'size' => $size,
            'catatan' => $catatan,
        ];
    }

    public function summarize(Collection $rows): array
    {
        $files = $rows->where('file_exists', true);
        $totalBytes = (int) $files->sum('size');

        return [
            'total_kelompok' => $rows->count(),
            'submitted' => $rows->filter(fn ($r) => $r['submission'] !== null)->count(),
            'missing' => $rows->filter(
                fn ($r) => in_array($r['catatan'], ['Belum mengumpulkan', 'Belum ada tugas Draft Artikel'], true)
            )->count(),
            'file_hilang' => $rows->where('catatan', 'File hilang')->count(),
            'file_count' => $files->count(),
            'total_bytes' => $totalBytes,
            'total_size' => $this->formatBytes($totalBytes),
            'over_limit' => $files->count() > self::MAX_FILES || $totalBytes > self::MAX_BYTES,
            'max_files' => self::MAX_FILES,
            'max_size' => $this->formatBytes(self::MAX_BYTES),
            'rows' => $rows->map(fn ($r) => [
                'kelompok' => $r['kelompok']->nama_kelompok,
                'dpl' => $r['kelompok']->dosenPembimbingLapangan?->user?->name ?? '-',
                'judul' => $r['submission']?->judul,
                'file_name' => $r['submission']?->file_name,
                'catatan' => $r['catatan'],
            ])->values(),
        ];
    }

    public function formatBytes(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2).' GB';
        }

        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2).' MB';
        }

        if ($bytes >= 1024) {
            return number_format($bytes / 1024, 2).' KB';
        }

        return $bytes.' B';
    }
}
