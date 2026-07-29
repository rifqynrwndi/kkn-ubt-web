<?php
namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\PenilaianKelompok;
use App\Models\PenilaianIndividu;
use App\Models\PenilaianKomponen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PenilaianAdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('superadmin');
    }

    public function index(Request $request): View
    {
        $gelombangs = Gelombang::orderBy('created_at', 'desc')->get();

        $selectedGelombang = $request->input('gelombang_id', $gelombangs->first()?->id);

        $kelompoks = collect();
        if ($selectedGelombang) {
            $kelompoks = KelompokKkn::with([
                'desaGelombang.desa.kecamatan',
                'dosenPembimbingLapangan.user',
                'pesertaKkn',
                'desaGelombang.gelombang',
            ])->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $selectedGelombang));

            if ($request->filled('search')) {
                $s = $request->search;
                $kelompoks->where(function ($q) use ($s) {
                    $q->where('nama_kelompok', 'like', "%{$s}%")
                      ->orWhere('kode_kelompok', 'like', "%{$s}%")
                      ->orWhereHas('dosenPembimbingLapangan.user', fn($uq) => $uq->where('name', 'like', "%{$s}%"))
                      ->orWhereHas('desaGelombang.desa', fn($dq) => $dq->where('nama_desa', 'like', "%{$s}%"));
                });
            }

            $kelompoks = $kelompoks->orderBy('nama_kelompok')->paginate(20)->withQueryString();
        }

        $komponenList = PenilaianKomponen::orderBy('urutan')->get();

        return view('penilaian-admin.index', compact('gelombangs', 'selectedGelombang', 'kelompoks', 'komponenList'));
    }

    public function input(Request $request): RedirectResponse
    {
        $request->validate([
            'kelompok_kkn_id' => 'required|exists:kelompok_kkn,id',
            'komponen_id' => 'required|array',
            'komponen_id.*' => 'exists:penilaian_komponen,id',
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        foreach ($request->komponen_id as $i => $komponenId) {
            $nilai = $request->nilai[$i] ?? null;
            if ($nilai !== null && $nilai !== '') {
                PenilaianKelompok::updateOrCreate(
                    ['kelompok_kkn_id' => $request->kelompok_kkn_id, 'komponen_id' => $komponenId],
                    ['nilai' => $nilai, 'input_by' => auth()->id(), 'input_at' => now()]
                );
            }
        }

        return back()->with('success', 'Nilai berhasil disimpan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $gelombangId = $request->input('gelombang_id');
        $komponenList = PenilaianKomponen::orderBy('urutan')->get();

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
        ])->when($gelombangId, fn($q) => $q->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelombangId)))
            ->orderBy('nama_kelompok')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai Akhir');

        $headers = ['No', 'Kelompok', 'Kode', 'Desa', 'Kecamatan', 'Kabupaten', 'DPL', 'Nilai DPL', 'Nilai Desa', 'Nilai LPPM', 'Nilai Akhir'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }

        $row = 2;
        foreach ($kelompoks as $i => $k) {
            $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
            $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get();

            $dplKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai DPL');
            $dplScores = $penilaianIndividu->where('komponen_id', $dplKomponen?->id)->pluck('nilai');
            $dplScore = $dplScores->isNotEmpty() ? round($dplScores->avg(), 2) : null;

            $desaScore = $penilaianData->first(fn($v) => $v->komponen->nama_komponen === 'Nilai Desa')?->nilai;
            $lppmScore = $penilaianData->first(fn($v) => $v->komponen->nama_komponen === 'Nilai LPPM')?->nilai;

            $finalScore = (!is_null($dplScore) && !is_null($desaScore) && !is_null($lppmScore))
                ? round($dplScore * 0.40 + $desaScore * 0.30 + $lppmScore * 0.30, 2)
                : null;

            $sheet->setCellValue('A' . $row, $i + 1);
            $sheet->setCellValue('B' . $row, $k->nama_kelompok);
            $sheet->setCellValue('C' . $row, $k->kode_kelompok);
            $sheet->setCellValue('D' . $row, $k->desaGelombang?->desa?->nama_desa ?? '-');
            $sheet->setCellValue('E' . $row, $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-');
            $sheet->setCellValue('F' . $row, $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? '-');
            $sheet->setCellValue('G' . $row, $k->dosenPembimbingLapangan?->user?->name ?? '-');
            $sheet->setCellValue('H' . $row, $dplScore ?? '-');
            $sheet->setCellValue('I' . $row, $desaScore ?? '-');
            $sheet->setCellValue('J' . $row, $lppmScore ?? '-');
            $sheet->setCellValue('K' . $row, $finalScore ?? '-');
            $row++;
        }

        foreach (range('A', 'K') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'nilai-kkn-ubt-' . date('Y-m-d') . '.xlsx';

        $response = new StreamedResponse(function () use ($writer) {
            $writer->save('php://output');
        });
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename . '"');

        return $response;
    }
}
