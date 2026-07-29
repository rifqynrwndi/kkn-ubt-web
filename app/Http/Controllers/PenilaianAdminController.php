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

    public function edit(KelompokKkn $kelompok): View
    {
        $kelompok->load('pesertaKkn.mahasiswa.user', 'desaGelombang.desa.kecamatan', 'dosenPembimbingLapangan.user');
        $komponenList = PenilaianKomponen::orderBy('urutan')->get();
        $penilaianKelompok = PenilaianKelompok::where('kelompok_kkn_id', $kelompok->id)->get()->keyBy('komponen_id');
        $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $kelompok->id)->get()->groupBy('peserta_kkn_id')->map(fn($g) => $g->keyBy('komponen_id'));

        return view('penilaian-admin.edit', compact('kelompok', 'komponenList', 'penilaianKelompok', 'penilaianIndividu'));
    }

    public function input(Request $request): RedirectResponse
    {
        $request->validate([
            'kelompok_kkn_id' => 'required|exists:kelompok_kkn,id',
            'komponen_id' => 'nullable|array',
            'komponen_id.*' => 'exists:penilaian_komponen,id',
            'nilai' => 'nullable|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
            'desa_peserta_kkn_id' => 'nullable|array',
            'desa_peserta_kkn_id.*' => 'exists:peserta_kkn,id',
            'desa_nilai' => 'nullable|array',
            'desa_nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $desaKom = PenilaianKomponen::where('nama_komponen', 'Nilai Desa')->first();

        if ($desaKom && $request->filled('desa_peserta_kkn_id')) {
            foreach ($request->desa_peserta_kkn_id as $i => $pesertaId) {
                $nilai = $request->desa_nilai[$i] ?? null;
                if ($nilai !== null && $nilai !== '') {
                    PenilaianIndividu::updateOrCreate(
                        [
                            'kelompok_kkn_id' => $request->kelompok_kkn_id,
                            'peserta_kkn_id' => $pesertaId,
                            'komponen_id' => $desaKom->id,
                        ],
                        ['nilai' => $nilai, 'input_by' => auth()->id()]
                    );
                }
            }
        }

        if ($request->filled('komponen_id')) {
            foreach ($request->komponen_id as $i => $komponenId) {
                $nilai = $request->nilai[$i] ?? null;
                if ($nilai !== null && $nilai !== '') {
                    PenilaianKelompok::updateOrCreate(
                        ['kelompok_kkn_id' => $request->kelompok_kkn_id, 'komponen_id' => $komponenId],
                        ['nilai' => $nilai, 'input_by' => auth()->id(), 'input_at' => now()]
                    );
                }
            }
        }

        return redirect()->route('penilaian.admin.index', $request->only(['gelombang_id', 'search', 'page']))->with('success', 'Nilai berhasil disimpan.');
    }

    public function export(Request $request): StreamedResponse
    {
        $gelombangId = $request->input('gelombang_id');
        abort_unless($gelombangId, 400, 'Pilih gelombang terlebih dahulu.');

        $komponenList = PenilaianKomponen::orderBy('urutan')->get();

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
            'pesertaKkn.mahasiswa.user',
        ])->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelombangId))
            ->orderBy('nama_kelompok')->get();

        $kabupatens = $kelompoks->groupBy(fn($k) => $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? 'Unknown');

        $spreadsheet = new Spreadsheet;
        $firstSheet = true;

        $headers = ['No', 'Kelompok', 'Mahasiswa', 'NPM', 'Desa', 'Kecamatan', 'Kabupaten', 'DPL', 'Nilai DPL', 'Nilai Desa', 'Nilai LPPM', 'Nilai Akhir'];
        $headerRange = range('A', 'L');

        foreach ($kabupatens as $kabupaten => $items) {
            if ($firstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $firstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $sheetName = mb_substr($kabupaten, 0, 31);
            $sheet->setTitle($sheetName);

            foreach ($headerRange as $i => $c) {
                $sheet->setCellValue($c . '1', $headers[$i]);
            }

            $row = 2;
            $no = 1;
            foreach ($items as $k) {
                $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
                $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get()->groupBy('peserta_kkn_id');

                $lppmScore = $penilaianData->first(fn($v) => $v->komponen->nama_komponen === 'Nilai LPPM')?->nilai;

                foreach ($k->pesertaKkn as $p) {
                    $dplKom = $komponenList->firstWhere('nama_komponen', 'Nilai DPL');
                    $dplScore = $penilaianIndividu[$p->id][$dplKom?->id]->nilai ?? null;

                    $desaKom = $komponenList->firstWhere('nama_komponen', 'Nilai Desa');
                    $desaScore = $penilaianIndividu[$p->id][$desaKom?->id]->nilai ?? null;

                    $finalScore = (!is_null($dplScore) && !is_null($desaScore) && !is_null($lppmScore))
                        ? round($dplScore * 0.40 + $desaScore * 0.30 + $lppmScore * 0.30, 2)
                        : null;

                    $sheet->setCellValue('A' . $row, $no++);
                    $sheet->setCellValue('B' . $row, $k->nama_kelompok);
                    $sheet->setCellValue('C' . $row, $p->mahasiswa?->user?->name ?? '-');
                    $sheet->setCellValue('D' . $row, $p->mahasiswa?->npm ?? '-');
                    $sheet->setCellValue('E' . $row, $k->desaGelombang?->desa?->nama_desa ?? '-');
                    $sheet->setCellValue('F' . $row, $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-');
                    $sheet->setCellValue('G' . $row, $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? '-');
                    $sheet->setCellValue('H' . $row, $k->dosenPembimbingLapangan?->user?->name ?? '-');
                    $sheet->setCellValue('I' . $row, $dplScore ?? '-');
                    $sheet->setCellValue('J' . $row, $desaScore ?? '-');
                    $sheet->setCellValue('K' . $row, $lppmScore ?? '-');
                    $sheet->setCellValue('L' . $row, $finalScore ?? '-');
                    $row++;
                }
            }

            foreach ($headerRange as $c) {
                $sheet->getColumnDimension($c)->setAutoSize(true);
            }
            $sheet->getColumnDimension('L')->setWidth(12);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $filename = 'nilai-kkn-ubt-' . date('Y-m-d') . '.xlsx';
        $tempPath = sys_get_temp_dir() . '/' . $filename;
        $writer->save($tempPath);

        return response()->download($tempPath, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
