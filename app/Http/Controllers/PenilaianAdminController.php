<?php
namespace App\Http\Controllers;

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

    public function index(): View
    {
        $komponenList = PenilaianKomponen::orderBy('urutan')->get();

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
            'pesertaKkn',
        ])->orderBy('nama_kelompok')->get();

        $kelompoks->each(function ($k) use ($komponenList) {
            $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
            $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get();

            $logbookKomponen = $komponenList->firstWhere('nama_komponen', 'Logbook');
            $desaKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai Pelaksanaan KKN UBT');

            $logbookScores = $penilaianIndividu->where('komponen_id', $logbookKomponen?->id)->pluck('nilai');
            $desaScores = $penilaianIndividu->where('komponen_id', $desaKomponen?->id)->pluck('nilai');

            $k->dplScore = $logbookScores->isNotEmpty() ? round($logbookScores->avg(), 2) : null;
            $k->desaScore = $desaScores->isNotEmpty() ? round($desaScores->avg(), 2) : null;

            $lppmKomponen = $komponenList->firstWhere('kategori', 'lppm');
            $k->lppmScore = $lppmKomponen && isset($penilaianData[$lppmKomponen->id]) ? $penilaianData[$lppmKomponen->id]->nilai : null;

            $k->finalScore = (!is_null($k->dplScore) && !is_null($k->desaScore) && !is_null($k->lppmScore))
                ? round($k->dplScore * 0.40 + $k->desaScore * 0.30 + $k->lppmScore * 0.30, 2)
                : null;

            $k->lppmKomponenId = $lppmKomponen?->id;
        });

        return view('penilaian-admin.index', compact('kelompoks'));
    }

    public function input(Request $request): RedirectResponse
    {
        $request->validate([
            'kelompok_kkn_id' => 'required|exists:kelompok_kkn,id',
            'nilai' => 'required|numeric|min:0|max:100',
        ]);

        $komponen = PenilaianKomponen::where('kategori', 'lppm')->first();
        if (!$komponen) {
            return back()->with('error', 'Komponen penilaian LPPM belum tersedia.');
        }

        PenilaianKelompok::updateOrCreate(
            ['kelompok_kkn_id' => $request->kelompok_kkn_id, 'komponen_id' => $komponen->id],
            ['nilai' => $request->nilai, 'input_by' => auth()->id(), 'input_at' => now()]
        );

        return redirect()->route('penilaian.admin.index')->with('success', 'Nilai LPPM berhasil disimpan.');
    }

    public function export(): StreamedResponse
    {
        $komponenList = PenilaianKomponen::orderBy('urutan')->get();

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
        ])->orderBy('nama_kelompok')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai Akhir');

        $headers = ['No', 'Kelompok', 'Kode', 'Desa', 'Kecamatan', 'Kabupaten', 'DPL', 'Nilai DPL', 'Nilai LPPM', 'Nilai Desa', 'Nilai Akhir'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '1', $h);
            $col++;
        }

        $row = 2;
        foreach ($kelompoks as $i => $k) {
            $penilaianData = PenilaianKelompok::where('kelompok_kkn_id', $k->id)->get()->keyBy('komponen_id');
            $penilaianIndividu = PenilaianIndividu::where('kelompok_kkn_id', $k->id)->get();

            $logbookKomponen = $komponenList->firstWhere('nama_komponen', 'Logbook');
            $desaKomponen = $komponenList->firstWhere('nama_komponen', 'Nilai Pelaksanaan KKN UBT');

            $logbookScores = $penilaianIndividu->where('komponen_id', $logbookKomponen?->id)->pluck('nilai');
            $desaScores = $penilaianIndividu->where('komponen_id', $desaKomponen?->id)->pluck('nilai');

            $dplScore = $logbookScores->isNotEmpty() ? round($logbookScores->avg(), 2) : null;
            $desaScore = $desaScores->isNotEmpty() ? round($desaScores->avg(), 2) : null;

            $lppmKomponen = $komponenList->firstWhere('kategori', 'lppm');
            $lppmScore = $lppmKomponen && isset($penilaianData[$lppmKomponen->id]) ? $penilaianData[$lppmKomponen->id]->nilai : null;

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
            $sheet->setCellValue('I' . $row, $lppmScore ?? '-');
            $sheet->setCellValue('J' . $row, $desaScore ?? '-');
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
