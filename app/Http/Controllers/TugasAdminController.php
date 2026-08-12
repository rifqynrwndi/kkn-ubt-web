<?php
namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\TugasKelompok;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TugasAdminController extends Controller
{
    public function index(Request $request): View
    {
        $tugasList = TugasKelompok::with('submissions')->get()
            ->groupBy('nama_tugas')
            ->map(fn($group) => [
                'nama_tugas' => $group->first()->nama_tugas,
                'kategori' => $group->first()->kategori,
                'total_kelompok' => $group->count(),
                'total_submissions' => $group->sum(fn($t) => $t->submissions->count()),
                'ids' => $group->pluck('id')->toArray(),
                'first_id' => $group->first()->id,
            ])
            ->sortBy('kategori')
            ->values();

        $wn = ['Program Kerja','Video Profil Desa','Draft Artikel','Laporan Program KKN'];
        $semuaTasks = TugasKelompok::whereIn('nama_tugas', $wn)->get();

        $gelombangs = Gelombang::orderBy('created_at', 'desc')->get();
        $selectedGelombang = $request->input('gelombang_id', $gelombangs->first()?->id);

        $kelompoks = KelompokKkn::with(['desaGelombang.desa.kecamatan',
            'tugasKelompok' => fn($q) => $q->whereIn('nama_tugas', $wn)]);

        if ($selectedGelombang) {
            $kelompoks->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $selectedGelombang));
        }

        $rekap = $kelompoks->orderBy('nama_kelompok')->get();

        return view('tugas-admin.index', compact('tugasList', 'rekap', 'semuaTasks', 'gelombangs', 'selectedGelombang'));
    }

    public function export(Request $request)
    {
        $gelombangId = $request->input('gelombang_id');
        abort_unless($gelombangId, 400, 'Pilih gelombang terlebih dahulu.');

        $gelombang = Gelombang::findOrFail($gelombangId);

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
            'pesertaKkn.mahasiswa.user',
            'tugasKelompok.submissions.pesertaKkn.mahasiswa.user',
        ])->whereHas('desaGelombang', fn($q) => $q->where('gelombang_id', $gelombangId))
            ->orderBy('nama_kelompok')->get();

        $kabupatens = $kelompoks->groupBy(fn($k) => $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? 'Unknown');

        $katLabels = ['tugas_kelompok' => 'Tugas Kelompok', 'luaran_wajib' => 'Luaran Wajib', 'luaran_lain' => 'Luaran Tambahan', 'laporan' => 'Laporan'];
        $statusLabels = ['tervalidasi' => 'Tervalidasi', 'ditolak' => 'Ditolak', 'menunggu' => 'Menunggu', 'belum' => 'Belum dikumpulkan'];

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];

        $rowStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
        ];

        $altRowStyle = [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FC']],
        ];

        $spreadsheet = new Spreadsheet;
        $firstSheet = true;

        foreach ($kabupatens as $kabupaten => $items) {
            if ($firstSheet) {
                $sheet = $spreadsheet->getActiveSheet();
                $firstSheet = false;
            } else {
                $sheet = $spreadsheet->createSheet();
            }

            $safeName = mb_substr(str_replace(['\\','/','*','?','[',']',':'], '', $kabupaten), 0, 31);
            $sheet->setTitle($safeName);

            $headers = ['No', 'Kelompok', 'Nama Tugas', 'Kategori', 'Pengumpul', 'Status', 'Link File', 'Tanggal'];
            $lastCol = 'H';
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $row = 2;
            $no = 1;
            foreach ($items as $k) {
                $desa = $k->desaGelombang?->desa?->nama_desa ?? '-';
                $kec = $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-';

                $tugasRows = $k->tugasKelompok->isEmpty() ? collect() : $k->tugasKelompok->sortBy('nama_tugas');
                if ($tugasRows->isEmpty()) {
                    $sheet->fromArray([[$no++, $k->nama_kelompok . "\n(" . $k->kode_kelompok . ')', 'Belum ada tugas', '-', '-', $statusLabels['belum'], '', '']], null, "A{$row}");
                    $style = ($no % 2 === 1) ? $altRowStyle : $rowStyle;
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                    $row++;
                    continue;
                }

                foreach ($tugasRows as $tugas) {
                    $namaTugas = $tugas->nama_tugas;
                    $kategori = $katLabels[$tugas->kategori] ?? $tugas->kategori;

                    if ($tugas->submissions->isEmpty()) {
                        $sheet->fromArray([[$no++, $k->nama_kelompok . "\n(" . $k->kode_kelompok . ')', $namaTugas, $kategori, '-', $statusLabels['belum'], '', '']], null, "A{$row}");
                    } else {
                        foreach ($tugas->submissions as $sub) {
                            $pengumpul = $sub->pesertaKkn?->mahasiswa?->user?->name ?? '-';
                            $status = $statusLabels[$sub->status] ?? $sub->status;
                            $link = $sub->file_path ? storage_url($sub->file_path) : '';
                            $tanggal = $sub->created_at ? $sub->created_at->format('d-m-Y H:i') : '';
                            $sheet->fromArray([[$no++, $k->nama_kelompok . "\n(" . $k->kode_kelompok . ')', $namaTugas, $kategori, $pengumpul, $status, $sub->file_name ?? $link, $tanggal]], null, "A{$row}");
                            if ($link) {
                                $cell = $sheet->getCell('G' . $row);
                                $cell->getHyperlink()->setUrl($link);
                                $cell->getHyperlink()->setTooltip($link);
                            }
                        }
                    }

                    $style = ($no % 2 === 1) ? $altRowStyle : $rowStyle;
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                    $row++;
                }
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(28);
            $sheet->getColumnDimension('C')->setWidth(24);
            $sheet->getColumnDimension('D')->setWidth(18);
            $sheet->getColumnDimension('E')->setWidth(22);
            $sheet->getColumnDimension('F')->setWidth(18);
            $sheet->getColumnDimension('G')->setWidth(40);
            $sheet->getColumnDimension('H')->setWidth(18);
        }

        $sheetKel = $firstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        $sheetKel->setTitle('Kelompok');

        $kelHeaders = ['No', 'Kelompok', 'Kode', 'Desa', 'Kecamatan', 'Kabupaten/Kota', 'DPL', 'Anggota'];
        $sheetKel->fromArray([$kelHeaders], null, 'A1');
        $sheetKel->getStyle('A1:H1')->applyFromArray($headerStyle);
        $sheetKel->getRowDimension(1)->setRowHeight(28);

        $row = 2;
        $no = 1;
        foreach ($kelompoks as $k) {
            $dpl = $k->dosenPembimbingLapangan?->user?->name ?? '-';
            $desa = $k->desaGelombang?->desa?->nama_desa ?? '-';
            $kec = $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-';
            $kab = $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? '-';

            $anggotaList = $k->pesertaKkn->map(function ($p, $i) {
                $m = $p->mahasiswa;
                $nama = $m?->user?->name ?? '-';
                $npm = $m?->npm ?? '';
                return ($i + 1) . ". {$nama} | {$npm}";
            })->implode("\n");

            $sheetKel->fromArray([[$no++, $k->nama_kelompok, $k->kode_kelompok, $desa, $kec, $kab, $dpl, $anggotaList]], null, "A{$row}");
            $style = ($no % 2 === 1) ? $altRowStyle : $rowStyle;
            $sheetKel->getStyle("A{$row}:H{$row}")->applyFromArray($style);
            $sheetKel->getRowDimension($row)->setRowHeight(max(28, $k->pesertaKkn->count() * 16));
            $sheetKel->getStyle("A{$row}:H{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $row++;
        }

        foreach (['A' => 6, 'B' => 26, 'C' => 18, 'D' => 22, 'E' => 22, 'F' => 22, 'G' => 22, 'H' => 50] as $c => $w) {
            $sheetKel->getColumnDimension($c)->setWidth($w);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $namaGelombang = str_replace(['\\','/',' ',':'], '_', $gelombang->nama_gelombang ?? 'Gelombang_' . $gelombangId);
        $filename = 'rekap-tugas-kelompok-' . $namaGelombang . '-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function create(): View
    {
        $kelompoks = KelompokKkn::with('desaGelombang.desa.kecamatan')->orderBy('nama_kelompok')->get();
        return view('tugas-admin.create', compact('kelompoks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_tugas' => 'required|string|max:255',
            'kategori' => 'required|in:tugas_kelompok,luaran_wajib,luaran_lain,laporan',
            'kelompok_ids' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->kelompok_ids as $kid) {
            TugasKelompok::firstOrCreate(
                ['kelompok_kkn_id' => $kid, 'nama_tugas' => $request->nama_tugas],
                ['kategori' => $request->kategori, 'created_by' => auth()->id()]
            );
            $count++;
        }

        return redirect()->route('admin.tugas.index')
            ->with('success', "Tugas \"{$request->nama_tugas}\" berhasil ditambahkan ke {$count} kelompok.");
    }

    public function destroyByNama(Request $request): RedirectResponse
    {
        $nama = $request->input('nama_tugas');
        $tugasGroup = TugasKelompok::where('nama_tugas', $nama)->get();
        $count = 0;
        foreach ($tugasGroup as $t) {
            foreach ($t->submissions as $sub) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($sub->file_path);
            }
            $t->delete();
            $count++;
        }
        return back()->with('success', "Tugas \"{$nama}\" dihapus dari {$count} kelompok.");
    }

    public function edit(Request $request): View
    {
        $nama = $request->query('nama_tugas');
        $tugas = TugasKelompok::where('nama_tugas', $nama)->firstOrFail();
        return view('tugas-admin.edit', compact('tugas'));
    }

    public function updateByNama(Request $request): RedirectResponse
    {
        $request->validate([
            'old_nama' => 'required|string',
            'nama_tugas' => 'required|string|max:255',
            'kategori' => 'required|in:tugas_kelompok,luaran_wajib,luaran_lain,laporan',
        ]);

        $count = TugasKelompok::where('nama_tugas', $request->old_nama)->update([
            'nama_tugas' => $request->nama_tugas,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('admin.tugas.index')
            ->with('success', "Tugas \"{$request->nama_tugas}\" diupdate pada {$count} kelompok.");
    }
}
