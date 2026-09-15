<?php

namespace Tests\Unit\Models;

use App\Models\Gelombang;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GelombangRequiredDocumentsTest extends TestCase
{
    use RefreshDatabase;

    private int $gelombangId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Test Gelombang',
            'tahun' => date('Y'),
            'tgl_mulai' => now(),
            'tgl_akhir' => now()->addMonth(),
            'status' => 'pendaftaran',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'kuota_total' => 100,
            'required_documents' => json_encode(['surat_pernyataan']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_required_documents_returns_configured_types(): void
    {
        $gelombang = Gelombang::find($this->gelombangId);

        $this->assertEquals(['surat_pernyataan'], $gelombang->getRequiredDocumentTypesAttribute());
    }

    public function test_required_documents_defaults_to_four_when_null(): void
    {
        DB::table('gelombang')->where('id', $this->gelombangId)->update(['required_documents' => null]);

        $gelombang = Gelombang::find($this->gelombangId);

        $required = $gelombang->getRequiredDocumentTypesAttribute();

        $this->assertContains('surat_pernyataan', $required);
        $this->assertContains('surat_ortu', $required);
        $this->assertContains('surat_vaksin', $required);
        $this->assertContains('surat_dokter', $required);
        $this->assertCount(4, $required);
    }

    public function test_is_document_required_returns_true_for_configured_type(): void
    {
        $gelombang = Gelombang::find($this->gelombangId);

        $this->assertTrue($gelombang->isDocumentRequired('surat_pernyataan'));
    }

    public function test_is_document_required_returns_false_for_non_configured_type(): void
    {
        $gelombang = Gelombang::find($this->gelombangId);

        $this->assertFalse($gelombang->isDocumentRequired('surat_ortu'));
    }

    public function test_all_four_documents_required_when_configured(): void
    {
        DB::table('gelombang')->where('id', $this->gelombangId)->update([
            'required_documents' => json_encode([
                'surat_pernyataan', 'surat_ortu', 'surat_vaksin', 'surat_dokter',
            ]),
        ]);

        $gelombang = Gelombang::find($this->gelombangId);

        $this->assertTrue($gelombang->isDocumentRequired('surat_pernyataan'));
        $this->assertTrue($gelombang->isDocumentRequired('surat_ortu'));
        $this->assertTrue($gelombang->isDocumentRequired('surat_vaksin'));
        $this->assertTrue($gelombang->isDocumentRequired('surat_dokter'));
    }

    public function test_dhs_is_no_longer_valid_document_type(): void
    {
        $gelombang = Gelombang::find($this->gelombangId);

        $this->assertFalse($gelombang->isDocumentRequired('dhs'));
    }
}
