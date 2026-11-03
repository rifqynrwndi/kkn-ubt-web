<?php

namespace App\Http\Controllers;

use App\Exports\DraftArtikelZipExport;
use App\Models\DosenPembimbingLapangan;
use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Services\DraftArtikelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DraftArtikelExportController extends Controller
{
    public function __construct(private DraftArtikelService $service) {}

    public function adminZip(Request $request): BinaryFileResponse|RedirectResponse
    {
        return $this->download($this->gelombang($request), null, true, 'admin');
    }

    public function dplSummary(Request $request): JsonResponse
    {
        [$gelombang, $dpl] = $this->dplContext($request);

        return response()->json($this->payload($gelombang, $dpl->id, 'dpl.draft-artikel.zip'));
    }

    public function dplZip(Request $request): BinaryFileResponse|RedirectResponse
    {
        [$gelombang, $dpl] = $this->dplContext($request);

        return $this->download($gelombang, $dpl->id, false, 'dpl');
    }

    private function gelombang(Request $request): Gelombang
    {
        $request->validate([
            'gelombang_id' => 'required|integer|exists:gelombang,id',
        ]);

        return Gelombang::findOrFail($request->integer('gelombang_id'));
    }

    /**
     * @return array{0: Gelombang, 1: DosenPembimbingLapangan}
     */
    private function dplContext(Request $request): array
    {
        $gelombang = $this->gelombang($request);

        $dpl = auth()->user()->dosenPembimbingLapangan;

        abort_if(! $dpl, 403, 'Anda tidak terdaftar sebagai DPL.');

        $owns = KelompokKkn::where('dosen_pembimbing_lapangan_id', $dpl->id)
            ->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $gelombang->id))
            ->exists();

        abort_unless($owns, 403, 'Anda tidak memiliki kelompok pada gelombang ini.');

        return [$gelombang, $dpl];
    }

    private function payload(Gelombang $gelombang, ?int $dplId, string $downloadRoute): array
    {
        $summary = $this->service->summarize(
            $this->service->rows($gelombang->id, $dplId)
        );

        $summary['gelombang_id'] = $gelombang->id;
        $summary['gelombang_name'] = $this->gelombangName($gelombang);
        $summary['download_url'] = route($downloadRoute, ['gelombang_id' => $gelombang->id]);

        return $summary;
    }

    private function download(
        Gelombang $gelombang,
        ?int $dplId,
        bool $groupByDpl,
        string $context
    ): BinaryFileResponse|RedirectResponse {
        $rows = $this->service->rows($gelombang->id, $dplId);
        $summary = $this->service->summarize($rows);

        if ($summary['file_count'] === 0) {
            return back()->with('error', 'Belum ada file Draft Artikel yang bisa diunduh.');
        }

        if ($summary['over_limit']) {
            return back()->with(
                'error',
                "Ukuran unduhan melebihi batas ({$summary['max_files']} file / {$summary['max_size']}). Persempit pilihan atau hubungi admin."
            );
        }

        set_time_limit(300);

        $downloadName = $this->downloadName($gelombang, $context);

        $exporter = new DraftArtikelZipExport(
            $rows,
            $this->gelombangName($gelombang),
            $groupByDpl
        );

        $path = $exporter->generate();

        Log::info('draft-artikel.zip', [
            'user_id' => auth()->id(),
            'context' => $context,
            'gelombang_id' => $gelombang->id,
            'dpl_id' => $dplId,
            'file_count' => $summary['file_count'],
            'total_bytes' => $summary['total_bytes'],
        ]);

        return response()->download($path, $downloadName)->deleteFileAfterSend(true);
    }

    private function downloadName(Gelombang $gelombang, string $context): string
    {
        $gelombangSlug = $this->slug($this->gelombangName($gelombang));

        if ($context === 'dpl') {
            $dplName = auth()->user()->dosenPembimbingLapangan?->user?->name ?? 'dpl';

            return 'draft_artikel_'.$this->slug($dplName).'_'.$gelombangSlug.'.zip';
        }

        return 'draft_artikel_'.$gelombangSlug.'_'.now()->format('Ymd_His').'.zip';
    }

    private function gelombangName(Gelombang $gelombang): string
    {
        $name = trim((string) $gelombang->nama_gelombang);

        return $name !== '' ? $name : 'Gelombang_'.$gelombang->id;
    }

    private function slug(string $value): string
    {
        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        $ascii = $ascii !== false ? $ascii : $value;
        $ascii = preg_replace('/[^A-Za-z0-9]+/', '_', $ascii) ?? '';
        $ascii = strtolower(trim($ascii, '_'));

        return $ascii !== '' ? $ascii : 'gelombang';
    }
}
