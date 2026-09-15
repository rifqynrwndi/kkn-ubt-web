<?php

namespace App\Services;

use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\User;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    public function exportKelompokXlsx(): void
    {
        $kelompoks = KelompokKkn::with([
            'pesertaKkn.mahasiswa.user',
            'pesertaKkn.mahasiswa.prodi.fakultas',
            'dosenPembimbingLapangan.user',
            'desaGelombang.desa.kecamatan',
        ])->orderBy('nama_kelompok')->get();

        $grouped = $kelompoks->groupBy(fn ($k) => $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? 'Tanpa Kabupaten');

        $spreadsheet = new Spreadsheet;
        $first = true;

        foreach ($grouped as $kabupaten => $kels) {
            $sheet = $first ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $first = false;

            $sheet->setTitle($this->safeSheetName($kabupaten));

            $headers = ['No', 'Kelompok', 'DPL', 'Lokasi', 'Anggota'];
            $lastCol = 'E';

            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($this->headerStyle());
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

                    return ($i + 1).". {$nama} | {$npm} | {$prodi} | {$fakultas}";
                })->implode("\n");

                $rowData = [$no++, $k->nama_kelompok."\n(".$k->kode_kelompok.')', $dpl, "{$desa}\n{$kec}", $anggotaList];

                $sheet->fromArray([$rowData], null, "A{$row}");
                $style = ($no % 2 === 1) ? $this->rowStripeStyle() : $this->altRowStyle();
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                $sheet->getRowDimension($row)->setRowHeight(max(36, $k->pesertaKkn->count() * 18));
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            $sheet->getColumnDimension('A')->setWidth(6);
            $sheet->getColumnDimension('B')->setWidth(24);
            $sheet->getColumnDimension('C')->setWidth(22);
            $sheet->getColumnDimension('D')->setWidth(20);
            $sheet->getColumnDimension('E')->setWidth(50);
        }

        $this->streamDownload($spreadsheet, 'data-kelompok-kkn-'.now()->format('Ymd-His').'.xlsx');
    }

    public function exportTugasXlsx(int $gelombangId): void
    {
        $gelombang = Gelombang::findOrFail($gelombangId);

        $kelompoks = KelompokKkn::with([
            'desaGelombang.desa.kecamatan',
            'dosenPembimbingLapangan.user',
            'pesertaKkn.mahasiswa.user',
            'tugasKelompok.submissions.pesertaKkn.mahasiswa.user',
        ])->whereHas('desaGelombang', fn ($q) => $q->where('gelombang_id', $gelombangId))
            ->orderBy('nama_kelompok')->get();

        $kabupatens = $kelompoks->groupBy(fn ($k) => $k->desaGelombang?->desa?->kecamatan?->kabupaten ?? 'Unknown');

        $katLabels = ['tugas_kelompok' => 'Tugas Kelompok', 'luaran_wajib' => 'Luaran Wajib', 'luaran_lain' => 'Luaran Tambahan', 'laporan' => 'Laporan'];
        $statusLabels = ['tervalidasi' => 'Tervalidasi', 'ditolak' => 'Ditolak', 'menunggu' => 'Menunggu', 'belum' => 'Belum dikumpulkan'];

        $spreadsheet = new Spreadsheet;
        $firstSheet = true;

        foreach ($kabupatens as $kabupaten => $items) {
            $sheet = $firstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
            $firstSheet = false;

            $sheet->setTitle($this->safeSheetName($kabupaten));

            $headers = ['No', 'Kelompok', 'Nama Tugas', 'Kategori', 'Pengumpul', 'Status', 'Link File', 'Tanggal'];
            $lastCol = 'H';
            $sheet->fromArray([$headers], null, 'A1');
            $sheet->getStyle("A1:{$lastCol}1")->applyFromArray($this->headerStyle());
            $sheet->getRowDimension(1)->setRowHeight(28);

            $row = 2;
            $no = 1;
            foreach ($items as $k) {
                $tugasRows = $k->tugasKelompok->isEmpty() ? collect() : $k->tugasKelompok->sortBy('nama_tugas');

                if ($tugasRows->isEmpty()) {
                    $sheet->fromArray([[$no++, $k->nama_kelompok."\n(".$k->kode_kelompok.')', 'Belum ada tugas', '-', '-', $statusLabels['belum'], '', '']], null, "A{$row}");
                    $style = ($no % 2 === 1) ? $this->altRowStyle() : $this->rowStyle();
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                    $row++;
                    continue;
                }

                $blockStart = $row;
                $totalRows = 0;
                foreach ($tugasRows as $tugas) {
                    $totalRows += $tugas->submissions->isEmpty() ? 1 : $tugas->submissions->count();
                }

                $namaKelompok = $k->nama_kelompok."\n(".$k->kode_kelompok.')';
                $firstOfBlock = true;

                foreach ($tugasRows as $tugas) {
                    $namaTugas = $tugas->nama_tugas;
                    $kategori = $katLabels[$tugas->kategori] ?? $tugas->kategori;

                    $rowsToWrite = $tugas->submissions->isEmpty()
                        ? [[$namaTugas, $kategori, '-', $statusLabels['belum'], '', '', '']]
                        : $tugas->submissions->map(fn ($sub) => [
                            $namaTugas,
                            $kategori,
                            $sub->pesertaKkn?->mahasiswa?->user?->name ?? '-',
                            $statusLabels[$sub->status] ?? $sub->status,
                            $sub->file_name ?: ($sub->file_path ? 'Lihat File' : ''),
                            $sub->file_path ? storage_url($sub->file_path) : '',
                            $sub->created_at ? $sub->created_at->format('d-m-Y H:i') : '',
                        ])->values()->toArray();

                    foreach ($rowsToWrite as $rw) {
                        [$namaTugasRow, $katRow, $pengumpul, $statusRow, $fileText, $linkUrl, $tanggal] = $rw;
                        $sheet->fromArray([[
                            $firstOfBlock ? $no : '',
                            $firstOfBlock ? $namaKelompok : '',
                            $namaTugasRow, $katRow, $pengumpul, $statusRow, $fileText, $tanggal,
                        ]], null, "A{$row}");

                        if ($linkUrl) {
                            $sheet->getCell('G'.$row)->getHyperlink()->setUrl($linkUrl);
                            $sheet->getCell('G'.$row)->getHyperlink()->setTooltip($linkUrl);
                            $sheet->getStyle('G'.$row)->getFont()->getColor()->setARGB('0563C1');
                            $sheet->getStyle('G'.$row)->getFont()->setUnderline(true);
                        }

                        $style = ($no % 2 === 1) ? $this->altRowStyle() : $this->rowStyle();
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray($style);
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
                        $firstOfBlock = false;
                        $row++;
                    }
                }

                if ($totalRows > 1) {
                    $blockEnd = $blockStart + $totalRows - 1;
                    $sheet->mergeCells("A{$blockStart}:A{$blockEnd}");
                    $sheet->mergeCells("B{$blockStart}:B{$blockEnd}");
                    $sheet->getStyle("A{$blockStart}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
                    $sheet->getStyle("B{$blockStart}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                }
                $no++;
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

        $this->addKelompokSummarySheet($spreadsheet, $kelompoks, $firstSheet);

        $namaGelombang = str_replace(['\\', '/', ' ', ':'], '_', $gelombang->nama_gelombang ?? 'Gelombang_'.$gelombangId);
        $this->streamDownload($spreadsheet, 'rekap-tugas-kelompok-'.$namaGelombang.'-'.now()->format('Ymd-His').'.xlsx');
    }

    public function exportMahasiswaXlsx(?int $gelombangId = null): void
    {
        $query = User::with('mahasiswa.prodi.fakultas')
            ->role('mahasiswa')
            ->whereHas('mahasiswa');

        if ($gelombangId) {
            $query->whereHas('mahasiswa.pesertaKkn', fn ($q) => $q->where('gelombang_id', $gelombangId));
        }

        $users = $query->orderBy('name')->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['No', 'Nama Lengkap', 'NPM', 'Email', 'HP', 'Status', 'Jenis Kelamin', 'Fakultas', 'Prodi', 'Nama Ortu', 'HP Ortu', 'Alamat Ortu'];
        $sheet->fromArray([$headers], null, 'A1');
        $sheet->getStyle('A1:L1')->applyFromArray($this->headerStyle());
        $sheet->getRowDimension(1)->setRowHeight(28);

        $row = 2;
        $no = 1;
        foreach ($users as $u) {
            $m = $u->mahasiswa;
            $sheet->fromArray([
                $no++, $u->name,
                $m->npm ?? '-', $u->email, $m->no_hp ?? '-',
                $u->email_verified_at ? 'Verified' : 'Unverified',
                $m->jenis_kelamin === 'L' ? 'Laki-laki' : ($m->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                $m->prodi->fakultas->nama_fakultas ?? '-',
                $m->prodi->nama_prodi ?? '-',
                $m->nama_ortu ?? '-', $m->no_hp_ortu ?? '-', $m->alamat_ortu ?? '-',
            ], null, "A{$row}");

            $style = ($no % 2 === 1) ? $this->rowStripeStyle() : $this->altRowStyle();
            $sheet->getStyle("A{$row}:L{$row}")->applyFromArray($style);
            $sheet->getStyle("A{$row}:L{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $row++;
        }

        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $this->streamDownload($spreadsheet, 'data-mahasiswa-'.date('YmdHis').'.xlsx');
    }

    private function addKelompokSummarySheet(Spreadsheet $spreadsheet, Collection $kelompoks, bool $firstSheet): void
    {
        $sheet = $firstSheet ? $spreadsheet->getActiveSheet() : $spreadsheet->createSheet();
        $sheet->setTitle('Kelompok');

        $kelHeaders = ['No', 'Kelompok', 'Kode', 'Desa', 'Kecamatan', 'Kabupaten/Kota', 'DPL', 'Anggota'];
        $sheet->fromArray([$kelHeaders], null, 'A1');
        $sheet->getStyle('A1:H1')->applyFromArray($this->headerStyle());
        $sheet->getRowDimension(1)->setRowHeight(28);

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

                return ($i + 1).". {$nama} | {$npm}";
            })->implode("\n");

            $sheet->fromArray([[$no++, $k->nama_kelompok, $k->kode_kelompok, $desa, $kec, $kab, $dpl, $anggotaList]], null, "A{$row}");
            $style = ($no % 2 === 1) ? $this->altRowStyle() : $this->rowStyle();
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray($style);
            $sheet->getRowDimension($row)->setRowHeight(max(28, $k->pesertaKkn->count() * 16));
            $sheet->getStyle("A{$row}:H{$row}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
            $row++;
        }

        foreach (['A' => 6, 'B' => 26, 'C' => 18, 'D' => 22, 'E' => 22, 'F' => 22, 'G' => 22, 'H' => 50] as $c => $w) {
            $sheet->getColumnDimension($c)->setWidth($w);
        }
    }

    private function safeSheetName(string $name): string
    {
        return mb_substr(str_replace(['\\', '/', '*', '?', '[', ']', ':'], '', $name), 0, 31);
    }

    private function headerStyle(): array
    {
        return [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '2D3A8A']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ];
    }

    private function rowStripeStyle(): array
    {
        return [
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0F2FA']],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
        ];
    }

    private function rowStyle(): array
    {
        return [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
        ];
    }

    private function altRowStyle(): array
    {
        return [
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D0D5E8']]],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F9FC']],
        ];
    }

    private function streamDownload(Spreadsheet $spreadsheet, string $filename): void
    {
        $spreadsheet->setActiveSheetIndex(0);
        $writer = new Xlsx($spreadsheet);

        echo response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->send();
        exit;
    }
}
