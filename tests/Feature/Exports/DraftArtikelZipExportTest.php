<?php

namespace Tests\Feature\Exports;

use App\Exports\DraftArtikelZipExport;
use App\Models\Desa;
use App\Models\DesaGelombang;
use App\Models\DosenPembimbingLapangan;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\TugasSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class DraftArtikelZipExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        Storage::disk('public')->put('tugas/draft.pdf', '%PDF-1.4 test');
    }

    public function test_admin_zip_groups_by_dpl_then_kelompok(): void
    {
        $rows = collect([
            $this->row('Papaya', 'Dr. Budi Santoso', true),
            $this->row('Mangga', 'Dr. Budi Santoso', false),
            $this->row('Durian', 'Dr. Siti Aminah', true),
            $this->row('Sukun', null, true),
        ]);

        $entries = $this->generateEntries($rows, true);

        $this->assertContains('index.xlsx', $entries);
        $this->assertContains(
            'DPL_Dr_Budi_Santoso/Kelompok_Papaya/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
        $this->assertContains(
            'DPL_Dr_Siti_Aminah/Kelompok_Durian/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
        $this->assertContains(
            'Tanpa_DPL/Kelompok_Sukun/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
        $this->assertFalse(
            collect($entries)->contains(fn ($e) => str_contains($e, 'Kelompok_Mangga')),
            'Kelompok tanpa file tidak boleh dibuatkan folder.'
        );
    }

    public function test_dpl_zip_groups_by_kelompok_only(): void
    {
        $entries = $this->generateEntries(
            collect([$this->row('Papaya', 'Dr. Budi Santoso', true)]),
            false
        );

        $this->assertContains(
            'Kelompok_Papaya/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
    }

    public function test_duplicate_folder_names_get_a_suffix(): void
    {
        $rows = collect([
            $this->row('Papaya', 'Dr. Budi Santoso', true),
            $this->row('Papaya', 'Dr. Budi Santoso', true),
        ]);

        $entries = $this->generateEntries($rows, false);

        $this->assertContains(
            'Kelompok_Papaya/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
        $this->assertContains(
            'Kelompok_Papaya_2/Draft_Artikel_Judul_Uji_Test_Budi_Santoso.pdf',
            $entries
        );
    }

    private function generateEntries($rows, bool $groupByDpl): array
    {
        $export = new DraftArtikelZipExport($rows, 'Gelombang Uji', $groupByDpl);
        $path = $export->generate();

        $zip = new ZipArchive;
        $zip->open($path);

        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entries[] = $zip->getNameIndex($i);
        }

        $zip->close();
        @unlink($path);

        return $entries;
    }

    private function row(string $kelompokName, ?string $dplName, bool $withFile): array
    {
        $kelompok = new KelompokKkn;
        $kelompok->nama_kelompok = $kelompokName;

        $desa = new Desa;
        $desa->nama_desa = 'Desa '.$kelompokName;
        $desaGelombang = new DesaGelombang;
        $desaGelombang->setRelation('desa', $desa);
        $kelompok->setRelation('desaGelombang', $desaGelombang);

        if ($dplName) {
            $dplUser = new User;
            $dplUser->name = $dplName;
            $dpl = new DosenPembimbingLapangan;
            $dpl->setRelation('user', $dplUser);
            $kelompok->setRelation('dosenPembimbingLapangan', $dpl);
        } else {
            $kelompok->setRelation('dosenPembimbingLapangan', null);
        }

        if (! $withFile) {
            return [
                'kelompok' => $kelompok,
                'submission' => null,
                'file_exists' => false,
                'size' => 0,
                'catatan' => 'Belum mengumpulkan',
            ];
        }

        $user = new User;
        $user->name = 'Budi Santoso';
        $mahasiswa = new Mahasiswa;
        $mahasiswa->setRelation('user', $user);
        $peserta = new PesertaKkn;
        $peserta->setRelation('mahasiswa', $mahasiswa);

        $submission = new TugasSubmission;
        $submission->judul = 'Judul Uji / Test';
        $submission->file_name = 'Draft Uji.pdf';
        $submission->file_path = 'tugas/draft.pdf';
        $submission->file_size = 13;
        $submission->status = 'menunggu';
        $submission->setRelation('pesertaKkn', $peserta);

        return [
            'kelompok' => $kelompok,
            'submission' => $submission,
            'file_exists' => true,
            'size' => 13,
            'catatan' => 'OK',
        ];
    }
}
