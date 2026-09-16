<?php

namespace Tests\Unit\Services;

use App\Models\DokumenPendaftaran;
use App\Models\File;
use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\User;
use App\Services\DocumentVerificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private DocumentVerificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DocumentVerificationService;
    }

    private function createPeserta(array $requiredDocs, array $documents = []): PesertaKkn
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
        ]);

        $user->mahasiswa()->create([
            'npm' => fake()->unique()->numerify('######'),
            'user_id' => $user->id,
        ]);

        $gelombang = Gelombang::create([
            'nama_gelombang' => 'Gelombang Test',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_akhir' => '2026-12-31',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'status' => 'pendaftaran',
            'required_documents' => $requiredDocs,
        ]);

        $peserta = PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'gelombang_id' => $gelombang->id,
            'status_pendaftaran' => 'pending_verification',
        ]);

        foreach ($documents as $doc) {
            $file = File::create([
                'user_id' => $user->id,
                'name' => fake()->word(),
                'path' => fake()->filePath(),
                'original_name' => fake()->word().'.pdf',
                'mime_type' => 'application/pdf',
                'size' => 1024,
            ]);

            DokumenPendaftaran::create([
                'peserta_kkn_id' => $peserta->id,
                'file_id' => $file->id,
                'jenis_dokumen' => $doc['jenis'],
                'status_verifikasi' => $doc['status'],
            ]);
        }

        $peserta->load('dokumenPendaftaran');

        return $peserta;
    }

    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(DocumentVerificationService::class, $this->service);
    }

    public function test_pending_documents_when_not_all_uploaded(): void
    {
        $peserta = $this->createPeserta(
            ['surat_pernyataan', 'surat_ortu'],
            [['jenis' => 'surat_pernyataan', 'status' => 'pending']]
        );

        $result = $this->service->syncPesertaStatus($peserta);

        $this->assertEquals('pending_documents', $result);
        $this->assertEquals('pending_documents', $peserta->fresh()->status_pendaftaran);
    }

    public function test_rejected_when_any_document_rejected(): void
    {
        $peserta = $this->createPeserta(
            ['surat_pernyataan', 'surat_ortu'],
            [
                ['jenis' => 'surat_pernyataan', 'status' => 'verified'],
                ['jenis' => 'surat_ortu', 'status' => 'rejected'],
            ]
        );

        $result = $this->service->syncPesertaStatus($peserta);

        $this->assertEquals('rejected', $result);
        $this->assertEquals('rejected', $peserta->fresh()->status_pendaftaran);
    }

    public function test_revision_when_any_document_requires_revision(): void
    {
        $peserta = $this->createPeserta(
            ['surat_pernyataan', 'surat_ortu'],
            [
                ['jenis' => 'surat_pernyataan', 'status' => 'verified'],
                ['jenis' => 'surat_ortu', 'status' => 'revision_required'],
            ]
        );

        $result = $this->service->syncPesertaStatus($peserta);

        $this->assertEquals('revision', $result);
        $this->assertEquals('revision', $peserta->fresh()->status_pendaftaran);
    }

    public function test_approved_when_all_documents_verified(): void
    {
        $peserta = $this->createPeserta(
            ['surat_pernyataan', 'surat_ortu'],
            [
                ['jenis' => 'surat_pernyataan', 'status' => 'verified'],
                ['jenis' => 'surat_ortu', 'status' => 'verified'],
            ]
        );

        $result = $this->service->syncPesertaStatus($peserta);

        $this->assertEquals('approved', $result);
        $this->assertEquals('approved', $peserta->fresh()->status_pendaftaran);
    }

    public function test_pending_verification_when_documents_uploaded_but_not_reviewed(): void
    {
        $peserta = $this->createPeserta(
            ['surat_pernyataan', 'surat_ortu'],
            [
                ['jenis' => 'surat_pernyataan', 'status' => 'pending'],
                ['jenis' => 'surat_ortu', 'status' => 'pending'],
            ]
        );

        $peserta->update(['status_pendaftaran' => 'draft']);
        $peserta->refresh();

        $result = $this->service->syncPesertaStatus($peserta);

        $this->assertEquals('pending_verification', $result);
        $this->assertEquals('pending_verification', $peserta->fresh()->status_pendaftaran);
    }
}
