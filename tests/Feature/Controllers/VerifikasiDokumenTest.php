<?php

namespace Tests\Feature\Controllers;

use App\Models\DokumenPendaftaran;
use App\Models\File;
use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VerifikasiDokumenTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Storage::fake('public');

        $this->createRole('superadmin');
        $this->createRole('mahasiswa');
    }

    private function createRole(string $name): Role
    {
        return Role::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole($this->createRole('superadmin'));

        return $user;
    }

    private function createGelombang(): Gelombang
    {
        return Gelombang::create([
            'nama_gelombang' => 'Gelombang Test',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_akhir' => '2026-12-31',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'status' => 'pendaftaran',
            'required_documents' => ['surat_pernyataan', 'surat_ortu', 'surat_dokter'],
        ]);
    }

    private function createPeserta(
        Gelombang $gelombang,
        string $name = 'Budi Santoso',
        string $npm = '1234567890',
        string $status = 'pending_verification'
    ): PesertaKkn {
        $user = User::factory()->create(['name' => $name]);
        $user->assignRole($this->createRole('mahasiswa'));

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => $npm,
            'nama' => $name,
            'fakultas' => 'Fakultas Teknik',
            'prodi' => 'Teknik Informatika',
            'angkatan' => 2022,
            'no_hp' => '08123456789',
            'is_biodata_complete' => true,
        ]);

        return PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'gelombang_id' => $gelombang->id,
            'status_pendaftaran' => $status,
        ]);
    }

    private function attachDocument(
        PesertaKkn $peserta,
        string $jenis = 'surat_pernyataan',
        string $status = 'pending',
        bool $withFile = true
    ): DokumenPendaftaran {
        $fileId = null;

        if ($withFile) {
            $path = "dokumen-pendaftaran/{$jenis}-{$peserta->id}.pdf";
            Storage::disk('public')->put($path, 'pdf-content');

            $file = File::create([
                'user_id' => $peserta->mahasiswa_id,
                'name' => "{$jenis}.pdf",
                'path' => $path,
                'original_name' => "{$jenis}.pdf",
                'mime_type' => 'application/pdf',
                'size' => 11,
            ]);

            $fileId = $file->id;
        }

        return DokumenPendaftaran::create([
            'peserta_kkn_id' => $peserta->id,
            'file_id' => $fileId,
            'jenis_dokumen' => $jenis,
            'status_verifikasi' => $status,
        ]);
    }

    public function test_index_searches_by_name_or_npm(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $this->createPeserta($gelombang, 'Budi Santoso', '1111111111');
        $this->createPeserta($gelombang, 'Siti Aminah', '2222222222');

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.index', ['gelombang_id' => $gelombang->id, 'q' => 'Budi']))
            ->assertOk()
            ->assertSee('Budi Santoso')
            ->assertDontSee('Siti Aminah');

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.index', ['gelombang_id' => $gelombang->id, 'q' => '2222222222']))
            ->assertOk()
            ->assertSee('Siti Aminah')
            ->assertDontSee('Budi Santoso');
    }

    public function test_index_uses_indonesian_labels(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $this->createPeserta($gelombang);

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.index', ['gelombang_id' => $gelombang->id]))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Setujui Terpilih')
            ->assertSee('Tinjau')
            ->assertDontSee('Pending Verification')
            ->assertDontSee('Approve Selected');
    }

    public function test_detail_page_uses_indonesian_labels(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);
        $this->attachDocument($peserta, 'surat_pernyataan', 'verified');

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.show', $peserta->id))
            ->assertOk()
            ->assertSee('Verifikasi Dokumen Peserta')
            ->assertSee('Setujui Semua')
            ->assertSee('Tolak Semua')
            ->assertSee('Terverifikasi')
            ->assertDontSee('Approve Semua')
            ->assertDontSee('Reject Semua');
    }

    public function test_admin_can_view_document_file(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);
        $dokumen = $this->attachDocument($peserta);

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.dokumen.file', $dokumen->id))
            ->assertOk();
    }

    public function test_view_file_returns_404_when_file_is_missing(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);
        $dokumen = $this->attachDocument($peserta);

        Storage::disk('public')->delete($dokumen->file->path);

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.dokumen.file', $dokumen->id))
            ->assertNotFound();
    }

    public function test_admin_can_delete_document_and_status_is_recalculated(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang, 'Budi Santoso', '1234567890', 'approved');

        $this->attachDocument($peserta, 'surat_pernyataan', 'verified');
        $this->attachDocument($peserta, 'surat_ortu', 'verified');
        $dokumen = $this->attachDocument($peserta, 'surat_dokter', 'verified');

        $filePath = $dokumen->file->path;

        $this->actingAs($admin)
            ->delete(route('verifikasi-dokumen.dokumen.destroy', $dokumen->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('dokumen_pendaftaran', ['id' => $dokumen->id]);
        Storage::disk('public')->assertMissing($filePath);
        $this->assertSame('pending_documents', $peserta->fresh()->status_pendaftaran);
    }

    public function test_non_superadmin_cannot_access_verification_pages(): void
    {
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);

        $this->actingAs($peserta->mahasiswa->user)
            ->get(route('verifikasi-dokumen.index'))
            ->assertForbidden();

        $this->actingAs($peserta->mahasiswa->user)
            ->get(route('verifikasi-dokumen.show', $peserta->id))
            ->assertForbidden();
    }

    public function test_student_can_replace_own_document(): void
    {
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);
        $this->attachDocument($peserta, 'surat_pernyataan');
        $old = $this->attachDocument($peserta, 'surat_ortu');
        $this->attachDocument($peserta, 'surat_dokter');

        $this->actingAs($peserta->mahasiswa->user)
            ->get(route('dokumen-pendaftaran.create', ['jenis_dokumen' => 'surat_ortu']))
            ->assertOk()
            ->assertSee('Surat Keterangan Orang Tua');

        $this->actingAs($peserta->mahasiswa->user)
            ->post(route('dokumen-pendaftaran.store'), [
                'jenis_dokumen' => 'surat_ortu',
                'file' => UploadedFile::fake()->create('ortu-baru.pdf', 100, 'application/pdf'),
            ])
            ->assertRedirect(route('dokumen-pendaftaran.index'));

        $this->assertNotSame($old->file_id, $old->fresh()->file_id);
    }

    public function test_student_can_delete_own_document_before_approval(): void
    {
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);
        $dokumen = $this->attachDocument($peserta);

        $this->actingAs($peserta->mahasiswa->user)
            ->delete(route('dokumen-pendaftaran.destroy', $dokumen->id))
            ->assertRedirect();

        $this->assertDatabaseMissing('dokumen_pendaftaran', ['id' => $dokumen->id]);
    }

    public function test_student_cannot_delete_document_after_approval(): void
    {
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang, 'Budi Santoso', '1234567890', 'approved');
        $dokumen = $this->attachDocument($peserta);

        $this->actingAs($peserta->mahasiswa->user)
            ->delete(route('dokumen-pendaftaran.destroy', $dokumen->id))
            ->assertForbidden();

        $this->assertDatabaseHas('dokumen_pendaftaran', ['id' => $dokumen->id]);
    }

    public function test_other_student_cannot_view_document(): void
    {
        $gelombang = $this->createGelombang();
        $owner = $this->createPeserta($gelombang, 'Budi Santoso', '1111111111');
        $other = $this->createPeserta($gelombang, 'Siti Aminah', '2222222222');
        $dokumen = $this->attachDocument($owner);

        $this->actingAs($other->mahasiswa->user)
            ->get(route('dokumen-pendaftaran.show', $dokumen->id))
            ->assertNotFound();
    }

    public function test_export_requires_gelombang_id(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.export'))
            ->assertStatus(400);
    }

    public function test_export_downloads_xlsx_of_approved_peserta(): void
    {
        $admin = $this->createAdmin();
        $gelombang = $this->createGelombang();

        $approved = $this->createPeserta($gelombang, 'Aminah Lengkap', '1111111111', 'approved');
        $this->attachDocument($approved, 'surat_pernyataan', 'verified');
        $this->attachDocument($approved, 'surat_ortu', 'verified');
        $this->attachDocument($approved, 'surat_dokter', 'verified');

        $this->createPeserta($gelombang, 'Budi Pending', '2222222222', 'pending_verification');

        $response = $this->actingAs($admin)
            ->get(route('verifikasi-dokumen.export', ['gelombang_id' => $gelombang->id]));

        $response->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

        $this->assertStringContainsString(
            'attachment; filename=mahasiswa-berkas-lengkap-',
            (string) $response->headers->get('content-disposition')
        );
    }

    public function test_non_superadmin_cannot_export(): void
    {
        $gelombang = $this->createGelombang();
        $peserta = $this->createPeserta($gelombang);

        $this->actingAs($peserta->mahasiswa->user)
            ->get(route('verifikasi-dokumen.export', ['gelombang_id' => $gelombang->id]))
            ->assertForbidden();
    }
}
