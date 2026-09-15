<?php

namespace Tests\Unit\Services\War;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\WarFaculty;
use App\Models\WarLog;
use App\Models\WarParticipant;
use App\Models\WarSession;
use App\Services\War\WarAllocationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarAllocationServiceTest extends TestCase
{
    use RefreshDatabase;

    private WarAllocationService $service;

    private int $userId;
    private int $fakultasId;
    private int $prodiId;
    private int $gelombangId;
    private int $kelompokId;
    private int $warSessionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(WarAllocationService::class);

        // Seed fakultas + prodi
        $this->fakultasId = DB::table('fakultas')->insertGetId([
            'nama_fakultas' => 'Fakultas Teknik', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $this->prodiId = DB::table('program_studi')->insertGetId([
            'nama_prodi' => 'Teknik Informatika', 'fakultas_id' => $this->fakultasId,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Seed user + mahasiswa
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User', 'email' => 'test@test.com', 'password' => bcrypt('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('mahasiswa')->insert([
            'user_id' => $this->userId, 'npm' => '99999999999', 'jenis_kelamin' => 'L', 'prodi_id' => $this->prodiId,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Seed gelombang
        $this->gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Gelombang Test', 'tahun' => date('Y'),
            'tgl_mulai' => now(), 'tgl_akhir' => now()->addMonth(),
            'status' => 'pendaftaran', 'kuota_laki' => 50, 'kuota_perempuan' => 50, 'kuota_total' => 100,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Seed desa + kecamatan + desa_gelombang
        $kecId = DB::table('kecamatan')->insertGetId(['nama_kecamatan' => 'Kec Test', 'kabupaten' => 'Kab Test', 'created_at' => now(), 'updated_at' => now()]);
        $desaId = DB::table('desa')->insertGetId(['nama_desa' => 'Desa Test', 'kecamatan_id' => $kecId, 'created_at' => now(), 'updated_at' => now()]);
        $desaGelId = DB::table('desa_gelombang')->insertGetId(['desa_id' => $desaId, 'gelombang_id' => $this->gelombangId, 'created_at' => now(), 'updated_at' => now()]);

        // Seed kelompok
        $this->kelompokId = DB::table('kelompok_kkn')->insertGetId([
            'desa_gelombang_id' => $desaGelId, 'nama_kelompok' => 'Kelompok Test',
            'kuota' => 12, 'status' => 'dibuka', 'status_tahap' => 0,
            'kode_kelompok' => 'KLT-001', 'created_at' => now(), 'updated_at' => now(),
        ]);

        // Seed war session
        $this->warSessionId = DB::table('war_sessions')->insertGetId([
            'gelombang_id' => $this->gelombangId, 'name' => 'WAR Test',
            'status' => 'active', 'start_at' => now()->subHour(), 'end_at' => now()->addHour(),
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function createPeserta(array $overrides = []): PesertaKkn
    {
        $defaults = [
            'mahasiswa_id' => $this->userId,
            'gelombang_id' => $this->gelombangId,
            'status_pendaftaran' => 'approved',
            'kelompok_kkn_id' => null,
        ];

        $id = DB::table('peserta_kkn')->insertGetId(array_merge($defaults, $overrides));

        return PesertaKkn::with(['mahasiswa.prodi.fakultas'])->find($id);
    }

    private function createSession(array $overrides = []): WarSession
    {
        $defaults = [
            'gelombang_id' => $this->gelombangId,
            'name' => 'Session Test',
            'status' => 'active',
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
        ];

        $id = DB::table('war_sessions')->insertGetId(array_merge($defaults, $overrides));

        // Auto-create war_faculty for this session
        DB::table('war_faculties')->insert([
            'war_session_id' => $id,
            'fakultas_id' => $this->fakultasId,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'filled' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return WarSession::find($id);
    }

    private function createKelompok(array $overrides = []): KelompokKkn
    {
        $defaults = [
            'desa_gelombang_id' => DB::table('desa_gelombang')->first()->id,
            'nama_kelompok' => 'Kelompok Test',
            'kuota' => 12,
            'status' => 'dibuka',
            'status_tahap' => 0,
            'kode_kelompok' => 'KLT-' . rand(100, 999),
        ];

        $id = DB::table('kelompok_kkn')->insertGetId(array_merge($defaults, $overrides));

        return KelompokKkn::find($id);
    }

    // --- Tests ---

    public function test_successful_allocation(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $participant = $this->service->allocate($session, $peserta, $kelompok);

        $this->assertInstanceOf(WarParticipant::class, $participant);
        $this->assertEquals($kelompok->id, $participant->kelompok_kkn_id);
        $this->assertEquals('joined', $participant->status);
        $this->assertNotNull($participant->joined_at);

        // Verify peserta was updated
        $peserta->refresh();
        $this->assertEquals($kelompok->id, $peserta->kelompok_kkn_id);
    }

    public function test_allocation_creates_war_log(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->service->allocate($session, $peserta, $kelompok);

        $this->assertDatabaseHas('war_logs', [
            'war_session_id' => $session->id,
            'peserta_kkn_id' => $peserta->id,
            'action' => 'join_success',
        ]);
    }

    public function test_allocation_increments_faculty_filled(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->service->allocate($session, $peserta, $kelompok);

        $filled = DB::table('war_faculties')
            ->where('war_session_id', $session->id)
            ->where('fakultas_id', $this->fakultasId)
            ->value('filled');

        $this->assertEquals(1, $filled);
    }

    public function test_kelompok_marked_penuh_when_full(): void
    {
        $session = $this->createSession();
        $kelompok = $this->createKelompok(['kuota' => 12]);

        // Create 11 existing members (3 male + 8 female to stay under gender limits)
        for ($i = 0; $i < 11; $i++) {
            $gender = $i < 3 ? 'L' : 'P';
            $memberUserId = DB::table('users')->insertGetId([
                'name' => "Member $i", 'email' => "member{$i}@test.com", 'password' => bcrypt('password'),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('mahasiswa')->insert([
                'user_id' => $memberUserId, 'npm' => (string) (70000000000 + $i), 'jenis_kelamin' => $gender, 'prodi_id' => $this->prodiId,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $memberPesertaId = DB::table('peserta_kkn')->insertGetId([
                'mahasiswa_id' => $memberUserId, 'gelombang_id' => $this->gelombangId,
                'status_pendaftaran' => 'approved', 'kelompok_kkn_id' => $kelompok->id,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        // Now add the 12th member (triggers penuh)
        $peserta = $this->createPeserta();
        $this->service->allocate($session, $peserta, $kelompok);

        $kelompok->refresh();
        $this->assertEquals('penuh', $kelompok->status);
    }

    public function test_kelompok_not_marked_penuh_when_not_full(): void
    {
        $session = $this->createSession();
        $kelompok = $this->createKelompok(['kuota' => 12]);

        $peserta = $this->createPeserta();
        $this->service->allocate($session, $peserta, $kelompok);

        $kelompok->refresh();
        $this->assertEquals('dibuka', $kelompok->status);
    }

    public function test_throws_when_peserta_already_has_kelompok(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta(['kelompok_kkn_id' => $this->kelompokId]);
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kamu sudah terdaftar di kelompok lain.');

        $this->service->allocate($session, $peserta, $kelompok);
    }

    public function test_throws_when_already_joined_session(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        // First allocation succeeds — creates WarParticipant
        $this->service->allocate($session, $peserta, $kelompok);

        // Reset kelompok_kkn_id to null (simulate stale state) while WarParticipant still exists
        DB::table('peserta_kkn')->where('id', $peserta->id)->update(['kelompok_kkn_id' => null]);
        $peserta->refresh();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kamu sudah join di sesi WAR ini.');

        $this->service->allocate($session, $peserta, $kelompok);
    }

    public function test_user_lock_prevents_concurrent_duplicate(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        // Simulate lock already held
        $lockService = app(\App\Services\War\WarLockService::class);
        $lockService->acquireUserLock($session->id, $peserta->id);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Request kamu sedang diproses.');

        $this->service->allocate($session, $peserta, $kelompok);
    }

    public function test_clears_cache_after_allocation(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        // Set cache
        \Cache::put("war:arena:{$session->id}", 'data', 60);
        \Cache::put("war:kelompok:{$session->id}", 'data', 60);

        $this->service->allocate($session, $peserta, $kelompok);

        $this->assertNull(\Cache::get("war:arena:{$session->id}"));
        $this->assertNull(\Cache::get("war:kelompok:{$session->id}"));
    }
}
