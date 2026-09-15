<?php

namespace Tests\Unit\Services\War;

use App\Models\Fakultas;
use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use App\Models\WarFaculty;
use App\Models\WarSession;
use App\Services\War\WarValidationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarValidationServiceTest extends TestCase
{
    use RefreshDatabase;

    private WarValidationService $service;

    private int $userId;
    private int $fakultasId;
    private int $prodiId;
    private int $gelombangId;
    private int $kelompokId;
    private int $warSessionId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new WarValidationService;

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

    private function createWarFaculty(int $fakultasId, int $sessionId, array $overrides = []): void
    {
        DB::table('war_faculties')->insert(array_merge([
            'war_session_id' => $sessionId,
            'fakultas_id' => $fakultasId,
            'start_at' => now()->subHour(),
            'end_at' => now()->addHour(),
            'filled' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));
    }

    // --- Tests ---

    public function test_validate_passes_for_valid_request(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->createWarFaculty($this->fakultasId, $session->id);

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
        // No exception = pass
        $this->assertTrue(true);
    }

    public function test_throws_when_session_not_active(): void
    {
        $session = $this->createSession(['status' => 'scheduled']);
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WAR session sedang tidak aktif.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_session_not_started(): void
    {
        $session = $this->createSession(['start_at' => now()->addHour()]);
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WAR belum dimulai.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_session_ended(): void
    {
        $session = $this->createSession(['end_at' => now()->subHour()]);
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('WAR sudah berakhir.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_peserta_not_approved(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta(['status_pendaftaran' => 'draft']);
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Status pendaftaran kamu belum disetujui.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_peserta_wrong_gelombang(): void
    {
        $otherGelombang = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Other', 'tahun' => date('Y'),
            'tgl_mulai' => now(), 'tgl_akhir' => now()->addMonth(),
            'status' => 'pendaftaran', 'kuota_laki' => 50, 'kuota_perempuan' => 50, 'kuota_total' => 100,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $session = $this->createSession();
        $peserta = $this->createPeserta(['gelombang_id' => $otherGelombang]);
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kamu tidak terdaftar di gelombang ini.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_kelompok_penuh(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok(['status' => 'penuh']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kelompok ini sudah penuh.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_peserta_already_has_kelompok(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta(['kelompok_kkn_id' => $this->kelompokId]);
        $kelompok = $this->createKelompok();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Kamu sudah terdaftar di kelompok lain.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_war_faculty_not_configured(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        // No WarFaculty record created

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Fakultas kamu belum dikonfigurasi di sesi WAR ini.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_war_faculty_no_schedule(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->createWarFaculty($this->fakultasId, $session->id, ['start_at' => null, 'end_at' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Jadwal WAR untuk fakultas kamu belum diatur.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_war_faculty_turn_not_started(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->createWarFaculty($this->fakultasId, $session->id, [
            'start_at' => now()->addHour(),
            'end_at' => now()->addHours(2),
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Giliran fakultas kamu belum dimulai.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_throws_when_war_faculty_turn_ended(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        $this->createWarFaculty($this->fakultasId, $session->id, [
            'start_at' => now()->subHours(3),
            'end_at' => now()->subSeconds(10), // Past 5s grace period
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Giliran WAR untuk fakultas kamu sudah berakhir.');

        $this->service->validateJoinRequest($session, $peserta, $kelompok);
    }

    public function test_passes_within_grace_period(): void
    {
        $session = $this->createSession();
        $peserta = $this->createPeserta();
        $kelompok = $this->createKelompok();

        // End was 3 seconds ago (within 5s grace period)
        $this->createWarFaculty($this->fakultasId, $session->id, [
            'start_at' => now()->subHours(2),
            'end_at' => now()->subSeconds(3),
        ]);

        // Should NOT throw — within grace period
        $this->service->validateJoinRequest($session, $peserta, $kelompok);
        $this->assertTrue(true);
    }
}
