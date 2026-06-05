<?php

namespace App\Http\Controllers;

use App\Models\DesaGelombang;
use App\Models\DosenPembimbingLapangan;
use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class KelompokKknController extends Controller
{
    public function index(Request $request): View
    {
        $query = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'desaGelombang.gelombang',
            'dosenPembimbingLapangan.user',
            'pesertaKkn',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $query->where(function ($q) use ($request) {
                $q->where('kode_kelompok', 'like', '%' . $request->search . '%')
                  ->orWhere('nama_kelompok', 'like', '%' . $request->search . '%')
                  ->orWhereHas('pesertaKkn.mahasiswa.user', fn($uq) => $uq->where('name', 'like', '%' . $request->search . '%'))
                  ->orWhereHas('pesertaKkn.mahasiswa', fn($mq) => $mq->where('npm', 'like', '%' . $request->search . '%'));
            });

        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kabupaten
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kabupaten')) {
            $query->whereHas('desaGelombang.desa.kecamatan', function ($q) use ($request) {
                $q->where('kabupaten', $request->kabupaten);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kecamatan
        |--------------------------------------------------------------------------
        */

        if ($request->filled('kecamatan_id')) {
            $query->whereHas('desaGelombang.desa.kecamatan', function ($q) use ($request) {
                $q->where('id', $request->kecamatan_id);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */

        if ($request->filled('status')) {

            $query->where(
                'status',
                $request->status
            );

        }

        $kabupatens = \App\Models\Kecamatan::select('kabupaten')->distinct()->orderBy('kabupaten')->pluck('kabupaten');
        $kecamatans = collect();
        $selectedKabupaten = $request->get('kabupaten');

        if ($selectedKabupaten) {
            $kecamatans = \App\Models\Kecamatan::where('kabupaten', $selectedKabupaten)
                ->orderBy('nama_kecamatan')
                ->get();
        }

        $kelompok = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view(
            'kelompok-kkn.index',
            compact('kelompok', 'kabupatens', 'kecamatans', 'selectedKabupaten')
        );
    }

    public function create(): View
    {
        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->get();

        $dpl = DosenPembimbingLapangan::with('user')
            ->where('status', 'aktif')
            ->get();

        return view(
            'kelompok-kkn.create',
            compact(
                'desaGelombang',
                'dpl'
            )
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([

            'desa_gelombang_id' =>
                'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' =>
                'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' =>
                'required|integer|min:1|max:20',

            'status' =>
                'required|in:draft,dibuka,ditutup',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Generate Nama Kelompok
        |--------------------------------------------------------------------------
        */

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->findOrFail(
            $validated['desa_gelombang_id']
        );

        $validated['nama_kelompok'] =
            $desaGelombang->desa->nama_desa
            . ' - ' .
            $desaGelombang->gelombang->nama_gelombang;

        KelompokKkn::create($validated);

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil dibuat.'
            );
    }

    public function show(
        KelompokKkn $kelompok_kkn
    ): View {

        $kelompok_kkn->load([
            'desaGelombang.desa',
            'desaGelombang.gelombang',
            'dosenPembimbingLapangan.user',
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'desaGelombang.desa.kecamatan',
        ]);

        $proposal = \App\Models\KelompokProposal::where('kelompok_kkn_id', $kelompok_kkn->id)->first();
        $statusService = app(\App\Services\StatusService::class);
        $statusStages = \App\Services\StatusService::STAGES;
        $statusCurrent = $statusService->getCurrentStage($kelompok_kkn);
        $statusHistory = $statusService->getHistory($kelompok_kkn);
        $tugasList = \App\Models\TugasKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['submissions.pesertaKkn.mahasiswa.user'])->get()->groupBy('kategori');
        $logbookData = \App\Models\LogBook::where('kelompok_kkn_id', $kelompok_kkn->id)->with(['pesertaKkn.mahasiswa.user'])->latest('tanggal')->get()->groupBy('peserta_kkn_id');
        $komponenList = \App\Models\PenilaianKomponen::orderBy('urutan')->get();
        $penilaianData = \App\Models\PenilaianKelompok::where('kelompok_kkn_id', $kelompok_kkn->id)->with('komponen')->get()->keyBy('komponen_id');

        $dplFinal = $this->calcScore($komponenList->where('kategori','dpl'), $penilaianData);
        $lppmFinal = $this->calcScore($komponenList->where('kategori','lppm'), $penilaianData);
        $finalScore = $dplFinal && $lppmFinal ? round(($dplFinal * 60 + $lppmFinal * 40) / 100, 2) : null;

        return view(
            'kelompok-kkn.show',
            compact('kelompok_kkn', 'proposal', 'statusStages', 'statusCurrent', 'statusHistory', 'tugasList', 'logbookData', 'komponenList', 'penilaianData', 'dplFinal', 'lppmFinal', 'finalScore')
        );
    }

    private function calcScore($komponenList, $penilaianData): ?float
    {
        $totalBobot = $komponenList->sum('bobot');
        if ($totalBobot === 0) return null;
        $totalNilai = $komponenList->sum(function ($k) use ($penilaianData) {
            return ($penilaianData[$k->id]->nilai ?? 0) * $k->bobot;
        });
        return $totalNilai > 0 ? round($totalNilai / $totalBobot, 2) : null;
    }

    public function edit(
        KelompokKkn $kelompok_kkn
    ): View {

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->get();

        $dpl = DosenPembimbingLapangan::with('user')
            ->where('status', 'aktif')
            ->get();

        return view(
            'kelompok-kkn.edit',
            compact(
                'kelompok_kkn',
                'desaGelombang',
                'dpl'
            )
        );
    }

    public function update(
        Request $request,
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        $validated = $request->validate([

            'desa_gelombang_id' =>
                'required|exists:desa_gelombang,id',

            'dosen_pembimbing_lapangan_id' =>
                'nullable|exists:dosen_pembimbing_lapangan,id',

            'kuota' =>
                'required|integer|min:1|max:20',

            'status' =>
                'required|in:draft,dibuka,ditutup,penuh',

        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Nama Kelompok
        |--------------------------------------------------------------------------
        */

        $desaGelombang = DesaGelombang::with([
            'desa',
            'gelombang',
        ])->findOrFail(
            $validated['desa_gelombang_id']
        );

        $validated['nama_kelompok'] =
            $desaGelombang->desa->nama_desa
            . ' - ' .
            $desaGelombang->gelombang->nama_gelombang;

        /*
        |--------------------------------------------------------------------------
        | Auto Full Check
        |--------------------------------------------------------------------------
        */

        if (
            $kelompok_kkn->pesertaKkn()->count()
            >=
            $validated['kuota']
        ) {

            $validated['status'] = 'penuh';

        }

        $kelompok_kkn->update($validated);

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil diperbarui.'
            );
    }

    public function destroy(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        if ($kelompok_kkn->pesertaKkn()->exists()) {

            return back()->with(
                'error',
                'Kelompok tidak dapat dihapus karena sudah memiliki anggota.'
            );

        }

        $kelompok_kkn->delete();

        return redirect()
            ->route('kelompok-kkn.index')
            ->with(
                'success',
                'Kelompok KKN berhasil dihapus.'
            );
    }

    public function buka(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        if ($kelompok_kkn->is_full) {

            return back()->with(
                'error',
                'Kelompok sudah penuh.'
            );

        }

        $kelompok_kkn->update([
            'status' => 'dibuka'
        ]);

        return back()->with(
            'success',
            'Kelompok berhasil dibuka.'
        );
    }

    public function tutup(
        KelompokKkn $kelompok_kkn
    ): RedirectResponse {

        $kelompok_kkn->update([
            'status' => 'ditutup'
        ]);

        return back()->with(
            'success',
            'Kelompok berhasil ditutup.'
        );
    }

    public function createAnggota(KelompokKkn $kelompok_kkn): View
    {
        $gelombangId = $kelompok_kkn->desaGelombang->gelombang_id;

        $peserta = PesertaKkn::with('mahasiswa.user', 'mahasiswa.prodi.fakultas')
            ->where('gelombang_id', $gelombangId)
            ->whereNull('kelompok_kkn_id')
            ->when(request('search'), fn($q) => $q->whereHas('mahasiswa.user', fn($q) =>
                $q->where('name', 'like', '%'.request('search').'%')
            ))
            ->paginate(20)
            ->withQueryString();

        return view(
            'kelompok-kkn.tambah-anggota',
            compact(
                'kelompok_kkn',
                'peserta'
            )
        );
    }

    public function tambahAnggota(Request $request, KelompokKkn $kelompok_kkn): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'peserta_kkn_id' => [
                'required',
                'exists:peserta_kkn,id',
            ],
        ]);

        $peserta = PesertaKkn::findOrFail(
            $validated['peserta_kkn_id']
        );

        if ($peserta->kelompok_kkn_id) {
            return back()->with(
                'error',
                'Mahasiswa sudah memiliki kelompok.'
            );
        }

        $peserta->kelompok_kkn_id = $kelompok_kkn->id;
        $peserta->status_pendaftaran = 'approved';
        $peserta->save();

        if ($kelompok_kkn->fresh()->pesertaKkn()->count() >= $kelompok_kkn->kuota) {
            $kelompok_kkn->update(['status' => 'penuh']);
        }

        return redirect()
            ->route('kelompok-kkn.show', $kelompok_kkn)
            ->with(
                'success',
                'Anggota berhasil ditambahkan.'
            );
    }

    public function hapusAnggota(KelompokKkn $kelompok_kkn, PesertaKkn $peserta): RedirectResponse
    {

        /*
        |--------------------------------------------------------------------------
        | Ensure Member Belongs To Group
        |--------------------------------------------------------------------------
        */

        if (
            $peserta->kelompok_kkn_id
            !=
            $kelompok_kkn->id
        ) {

            return back()->with(
                'error',
                'Anggota tidak ditemukan pada kelompok ini.'
            );

        }

        /*
        |--------------------------------------------------------------------------
        | Remove From Group
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($kelompok_kkn, $peserta) {

            $peserta->update([
                'kelompok_kkn_id' => null,
            ]);

            if ($kelompok_kkn->ketua_peserta_id === $peserta->id) {
                $kelompok_kkn->updateQuietly(['ketua_peserta_id' => null]);
            }

            \App\Models\WarParticipant::where('peserta_kkn_id', $peserta->id)->delete();

        });

        /*
        |--------------------------------------------------------------------------
        | Reopen Group If Needed
        |--------------------------------------------------------------------------
        */

        $kelompok_kkn->refresh();

        if ($kelompok_kkn->status === 'penuh' && $kelompok_kkn->terisi < $kelompok_kkn->kuota) {

            $kelompok_kkn->updateQuietly([
                'status' => 'dibuka'
            ]);

        }

        return back()->with(
            'success',
            'Anggota berhasil dihapus.'
        );
    }

    public function setKetua(KelompokKkn $kelompok_kkn, PesertaKkn $peserta): RedirectResponse
    {
        abort_if(
            $peserta->kelompok_kkn_id !== $kelompok_kkn->id,
            403,
            'Peserta ini bukan anggota kelompok ini.'
        );

        $kelompok_kkn->update([
            'ketua_peserta_id' => $peserta->id,
        ]);

        return back()->with(
            'success',
            'Ketua kelompok berhasil diubah menjadi ' . ($peserta->mahasiswa?->user?->name ?? 'Unknown') . '.'
        );
    }

    public function exportXlsx()
    {
        $kelompoks = KelompokKkn::with([
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'dosenPembimbingLapangan.user',
            'desaGelombang.desa.kecamatan',
        ])->orderBy('nama_kelompok')->get();

        $grouped = $kelompoks->groupBy(fn($k) => $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? 'Tanpa Kabupaten');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $first = true;

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D3A8A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];

        $rowStripeStyle = [
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F2FA']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
        ];

        $rowStyle = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
        ];

        $altRowStyle = [
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FC']],
        ];

        foreach ($grouped as $kabupaten => $kels) {
            $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;

            $safeName = mb_substr(str_replace(['\\','/','*','?','[',']',':'], '', $kabupaten), 0, 31);
            $sheet->setTitle($safeName);

            $headers = ['No', 'Kelompok', 'DPL', 'Lokasi', 'Anggota'];
            $lastCol = 'E';

            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($headerStyle);
            $sheet->getRowDimension(1)->setRowHeight(28);

            $row = 2;
            $no = 1;
            foreach ($kels as $k) {
                $dpl = $k->dosenPembimbingLapangan?->user?->name ?? '-';
                $desa = $k->desaGelombang?->desa?->nama_desa ?? '-';
                $kec = $k->desaGelombang?->desa?->kecamatan?->nama_kecamatan ?? '-';

                $anggotaList = $k->pesertaKkn->map(function ($p, $i) {
                    $m = $p->mahasiswa;
                    $nama = $m?->user?->name ?? '-';
                    $npm = $m?->npm ?? '';
                    $prodi = $m?->prodi?->nama_prodi ?? '';
                    $fakultas = $m?->prodi?->fakultas?->nama_fakultas ?? '';
                    return ($i + 1) . ". {$nama} | {$npm} | {$prodi} | {$fakultas}";
                })->implode("\n");

                $rowData = [$no++, $k->nama_kelompok . "\n(" . $k->kode_kelompok . ')', $dpl, "{$desa}\n{$kec}", $anggotaList];

                $sheet->fromArray([$rowData], null, "A{$row}");
                $style = ($no % 2 === 1) ? $rowStripeStyle : $altRowStyle;
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                $sheet->getRowDimension($row)->setRowHeight(max(36, $k->pesertaKkn->count() * 18));
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true)->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(24);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(50);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'data-kelompok-kkn-' . now()->format('Ymd-His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
