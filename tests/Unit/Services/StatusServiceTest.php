<?php

namespace Tests\Unit\Services;

use App\Models\KelompokKkn;
use App\Models\KelompokStatusHistory;
use App\Models\User;
use App\Services\StatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StatusServiceTest extends TestCase
{
    use RefreshDatabase;

    private StatusService $service;

    private int $userId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new StatusService;

        // Create and authenticate a test user for auth()->id()
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $user = User::find($this->userId);
        $this->actingAs($user);
    }

    private function createKelompok(int $statusTahap = 0): KelompokKkn
    {
        $kecamatanId = DB::table('kecamatan')->insertGetId([
            'nama_kecamatan' => 'Kecamatan Test',
            'kabupaten' => 'Kabupaten Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $desaId = DB::table('desa')->insertGetId([
            'nama_desa' => 'Desa Test',
            'kecamatan_id' => $kecamatanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Gelombang Test '.uniqid(),
            'tahun' => 2026,
            'tgl_mulai' => now()->toDateString(),
            'tgl_akhir' => now()->addMonth()->toDateString(),
            'status' => 'berjalan',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $desaGelombangId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $desaId,
            'gelombang_id' => $gelombangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = DB::table('kelompok_kkn')->insertGetId([
            'kode_kelompok' => 'K'.uniqid(),
            'desa_gelombang_id' => $desaGelombangId,
            'nama_kelompok' => 'Test '.uniqid(),
            'kuota' => 10,
            'status' => 'dibuka',
            'status_tahap' => $statusTahap,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return KelompokKkn::findOrFail($id);
    }

    private function createPesertaKkn(int $kelompokId): int
    {
        $mahasiswaUserId = DB::table('users')->insertGetId([
            'name' => 'Mahasiswa Test',
            'email' => 'mhs_'.uniqid().'@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mahasiswa')->insert([
            'user_id' => $mahasiswaUserId,
            'npm' => uniqid('npm'),
            'jenis_kelamin' => 'L',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Get gelombang_id from the kelompok's desa_gelombang
        $kelompok = DB::table('kelompok_kkn')->where('id', $kelompokId)->first();
        $desaGelombang = DB::table('desa_gelombang')->where('id', $kelompok->desa_gelombang_id)->first();

        return DB::table('peserta_kkn')->insertGetId([
            'mahasiswa_id' => $mahasiswaUserId,
            'gelombang_id' => $desaGelombang->gelombang_id,
            'status_pendaftaran' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private array $komponenIds = [];

    private function seedPenilaianKomponen(): void
    {
        $this->komponenIds = [];
        for ($i = 0; $i < 4; $i++) {
            $id = DB::table('penilaian_komponen')->insertGetId([
                'nama_komponen' => 'Komponen '.$i,
                'kategori' => 'dpl',
                'bobot' => 25,
                'urutan' => $i,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->komponenIds[] = $id;
        }
    }

    private function seedPenilaianKelompok(int $kelompokId): void
    {
        foreach ($this->komponenIds as $komponenId) {
            DB::table('penilaian_kelompok')->insert([
                'kelompok_kkn_id' => $kelompokId,
                'komponen_id' => $komponenId,
                'nilai' => 80,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function test_stages_constant_has_5_entries(): void
    {
        $this->assertCount(5, StatusService::STAGES);
        $this->assertArrayHasKey(0, StatusService::STAGES);
        $this->assertArrayHasKey(4, StatusService::STAGES);
    }

    public function test_change_status_updates_kelompok(): void
    {
        $kelompok = $this->createKelompok(0);

        $this->service->changeStatus($kelompok, 1, 'test change');

        $kelompok->refresh();
        $this->assertEquals(1, $kelompok->status_tahap);
    }

    public function test_change_status_creates_history(): void
    {
        $kelompok = $this->createKelompok(0);

        $this->service->changeStatus($kelompok, 1, 'proposal submitted');

        $history = KelompokStatusHistory::where('kelompok_kkn_id', $kelompok->id)->first();
        $this->assertNotNull($history);
        $this->assertEquals(0, $history->status_lama);
        $this->assertEquals(1, $history->status_baru);
        $this->assertEquals('proposal submitted', $history->keterangan);
    }

    public function test_change_status_same_stage_does_nothing(): void
    {
        $kelompok = $this->createKelompok(0);

        $this->service->changeStatus($kelompok, 0);

        $this->assertEquals(0, KelompokStatusHistory::count());
    }

    public function test_get_current_stage_returns_correct_stage(): void
    {
        $kelompok = $this->createKelompok(2);

        $stage = $this->service->getCurrentStage($kelompok);

        $this->assertEquals('Disetujui DPL', $stage['nama']);
        $this->assertEquals('info', $stage['color']);
    }

    public function test_get_current_stage_defaults_to_0_for_invalid(): void
    {
        $kelompok = $this->createKelompok(99);

        $stage = $this->service->getCurrentStage($kelompok);

        $this->assertEquals('Belum Mulai', $stage['nama']);
    }

    public function test_on_proposal_submitted_advances_from_0_to_1(): void
    {
        $kelompok = $this->createKelompok(0);

        $this->service->onProposalSubmitted($kelompok);

        $kelompok->refresh();
        $this->assertEquals(1, $kelompok->status_tahap);
    }

    public function test_on_proposal_submitted_does_not_advance_from_other_stages(): void
    {
        $kelompok = $this->createKelompok(2);

        $this->service->onProposalSubmitted($kelompok);

        $kelompok->refresh();
        $this->assertEquals(2, $kelompok->status_tahap);
    }

    public function test_on_proposal_approved_advances_from_1_to_2(): void
    {
        $kelompok = $this->createKelompok(1);

        $this->service->onProposalApproved($kelompok);

        $kelompok->refresh();
        $this->assertEquals(2, $kelompok->status_tahap);
    }

    public function test_on_proposal_approved_does_not_advance_from_other_stages(): void
    {
        $kelompok = $this->createKelompok(3);

        $this->service->onProposalApproved($kelompok);

        $kelompok->refresh();
        $this->assertEquals(3, $kelompok->status_tahap);
    }

    public function test_on_dpl_assigned_advances_from_2_to_3(): void
    {
        $kelompok = $this->createKelompok(2);

        $this->service->onDplAssigned($kelompok);

        $kelompok->refresh();
        $this->assertEquals(3, $kelompok->status_tahap);
    }

    public function test_on_dpl_assigned_does_not_advance_from_other_stages(): void
    {
        $kelompok = $this->createKelompok(0);

        $this->service->onDplAssigned($kelompok);

        $kelompok->refresh();
        $this->assertEquals(0, $kelompok->status_tahap);
    }

    public function test_check_auto_advance_does_nothing_when_not_stage_3(): void
    {
        $kelompok = $this->createKelompok(1);

        $this->service->checkAutoAdvance($kelompok);

        $kelompok->refresh();
        $this->assertEquals(1, $kelompok->status_tahap);
    }

    public function test_check_auto_advance_advances_when_all_conditions_met(): void
    {
        $kelompok = $this->createKelompok(3);
        $pesertaId = $this->createPesertaKkn($kelompok->id);

        // 40 validated logbooks
        for ($i = 0; $i < 40; $i++) {
            DB::table('log_book')->insert([
                'peserta_kkn_id' => $pesertaId,
                'kelompok_kkn_id' => $kelompok->id,
                'tanggal' => now()->subDays(40 - $i)->toDateString(),
                'judul' => 'Log '.$i,
                'deskripsi' => 'Isi log '.$i,
                'is_validated' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1 tugas kelompok with submitted tugas
        $tugasId = DB::table('tugas_kelompok')->insertGetId([
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => 'tugas_kelompok',
            'nama_tugas' => 'Tugas 1',
            'deskripsi' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tugas_submission')->insert([
            'tugas_kelompok_id' => $tugasId,
            'peserta_kkn_id' => $pesertaId,
            'judul' => 'Submission 1',
            'deskripsi' => 'Test',
            'file_path' => 'test.pdf',
            'file_name' => 'test.pdf',
            'status' => 'diterima',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 4 scored penilaian components
        $this->seedPenilaianKomponen();
        $this->seedPenilaianKelompok($kelompok->id);

        $this->service->checkAutoAdvance($kelompok);

        $kelompok->refresh();
        $this->assertEquals(4, $kelompok->status_tahap);
    }

    public function test_check_auto_advance_does_not_advance_with_insufficient_logbooks(): void
    {
        $kelompok = $this->createKelompok(3);
        $pesertaId = $this->createPesertaKkn($kelompok->id);

        // Only 30 validated logbooks (need 40)
        for ($i = 0; $i < 30; $i++) {
            DB::table('log_book')->insert([
                'peserta_kkn_id' => $pesertaId,
                'kelompok_kkn_id' => $kelompok->id,
                'tanggal' => now()->subDays(30 - $i)->toDateString(),
                'judul' => 'Log '.$i,
                'deskripsi' => 'Isi log '.$i,
                'is_validated' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tugasId = DB::table('tugas_kelompok')->insertGetId([
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => 'tugas_kelompok',
            'nama_tugas' => 'Tugas 1',
            'deskripsi' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tugas_submission')->insert([
            'tugas_kelompok_id' => $tugasId,
            'peserta_kkn_id' => $pesertaId,
            'judul' => 'Submission 1',
            'deskripsi' => 'Test',
            'file_path' => 'test.pdf',
            'file_name' => 'test.pdf',
            'status' => 'diterima',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedPenilaianKomponen();
        $this->seedPenilaianKelompok($kelompok->id);

        $this->service->checkAutoAdvance($kelompok);

        $kelompok->refresh();
        $this->assertEquals(3, $kelompok->status_tahap);
    }

    public function test_check_auto_advance_does_not_advance_without_tugas(): void
    {
        $kelompok = $this->createKelompok(3);
        $pesertaId = $this->createPesertaKkn($kelompok->id);

        for ($i = 0; $i < 40; $i++) {
            DB::table('log_book')->insert([
                'peserta_kkn_id' => $pesertaId,
                'kelompok_kkn_id' => $kelompok->id,
                'tanggal' => now()->subDays(40 - $i)->toDateString(),
                'judul' => 'Log '.$i,
                'deskripsi' => 'Isi log '.$i,
                'is_validated' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // No tugas kelompok

        $this->seedPenilaianKomponen();
        $this->seedPenilaianKelompok($kelompok->id);

        $this->service->checkAutoAdvance($kelompok);

        $kelompok->refresh();
        $this->assertEquals(3, $kelompok->status_tahap);
    }

    public function test_full_lifecycle_0_to_4(): void
    {
        $kelompok = $this->createKelompok(0);

        // Stage 0 → 1
        $this->service->onProposalSubmitted($kelompok);
        $this->assertEquals(1, $kelompok->fresh()->status_tahap);

        // Stage 1 → 2
        $this->service->onProposalApproved($kelompok);
        $this->assertEquals(2, $kelompok->fresh()->status_tahap);

        // Stage 2 → 3
        $this->service->onDplAssigned($kelompok);
        $this->assertEquals(3, $kelompok->fresh()->status_tahap);

        // Stage 3 → 4
        $pesertaId = $this->createPesertaKkn($kelompok->id);

        for ($i = 0; $i < 40; $i++) {
            DB::table('log_book')->insert([
                'peserta_kkn_id' => $pesertaId,
                'kelompok_kkn_id' => $kelompok->id,
                'tanggal' => now()->subDays(40 - $i)->toDateString(),
                'judul' => 'Log '.$i,
                'deskripsi' => 'Isi log '.$i,
                'is_validated' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $tugasId = DB::table('tugas_kelompok')->insertGetId([
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => 'tugas_kelompok',
            'nama_tugas' => 'Tugas 1',
            'deskripsi' => 'Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tugas_submission')->insert([
            'tugas_kelompok_id' => $tugasId,
            'peserta_kkn_id' => $pesertaId,
            'judul' => 'Submission 1',
            'deskripsi' => 'Test',
            'file_path' => 'test.pdf',
            'file_name' => 'test.pdf',
            'status' => 'diterima',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seedPenilaianKomponen();
        $this->seedPenilaianKelompok($kelompok->id);

        $this->service->checkAutoAdvance($kelompok);
        $this->assertEquals(4, $kelompok->fresh()->status_tahap);

        $this->assertEquals(4, KelompokStatusHistory::where('kelompok_kkn_id', $kelompok->id)->count());
    }
}
