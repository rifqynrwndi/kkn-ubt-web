<?php

namespace Tests\Unit\Services\War;

use App\Models\KelompokKkn;
use App\Models\KelompokKuota;
use App\Models\PesertaKkn;
use App\Services\War\WarRuleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarRuleServiceCheckCanJoinTest extends TestCase
{
    use RefreshDatabase;

    private WarRuleService $service;

    private int $gelombangId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new WarRuleService;

        $this->gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Gelombang Test',
            'tahun' => date('Y'),
            'tgl_mulai' => now(),
            'tgl_akhir' => now()->addMonth(),
            'status' => 'berjalan',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'kuota_total' => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create required FK records
        DB::table('fakultas')->updateOrInsert(
            ['id' => 1],
            ['nama_fakultas' => 'Fakultas Test', 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('program_studi')->updateOrInsert(
            ['id' => 1],
            ['nama_prodi' => 'Prodi Test 1', 'fakultas_id' => 1, 'created_at' => now(), 'updated_at' => now()]
        );

        DB::table('program_studi')->updateOrInsert(
            ['id' => 2],
            ['nama_prodi' => 'Prodi Test 2', 'fakultas_id' => 1, 'created_at' => now(), 'updated_at' => now()]
        );
    }

    private function createKelompok(int $kuota = 12, string $status = 'dibuka'): KelompokKkn
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

        $desaGelombangId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $desaId,
            'gelombang_id' => $this->gelombangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return KelompokKkn::create([
            'desa_gelombang_id' => $desaGelombangId,
            'nama_kelompok' => 'Kelompok Test',
            'kuota' => $kuota,
            'status' => $status,
            'status_tahap' => 0,
        ]);
    }

    private function createPeserta(int $kelompokId, string $gender = 'L', int $prodiId = 1, int $fakultasId = 1): PesertaKkn
    {
        $userId = DB::table('users')->insertGetId([
            'name' => 'Mahasiswa Test',
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('mahasiswa')->insert([
            'user_id' => $userId,
            'npm' => fake()->unique()->numerify('##############'),
            'jenis_kelamin' => $gender,
            'prodi_id' => $prodiId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pesertaId = DB::table('peserta_kkn')->insertGetId([
            'mahasiswa_id' => $userId,
            'gelombang_id' => $this->gelombangId,
            'kelompok_kkn_id' => $kelompokId,
            'status_pendaftaran' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return PesertaKkn::find($pesertaId);
    }

    private function createKuota(int $kelompokId, int $fakultasId = 1): KelompokKuota
    {
        // Ensure fakultas exists
        DB::table('fakultas')->updateOrInsert(
            ['id' => $fakultasId],
            ['nama_fakultas' => 'Fakultas Test', 'created_at' => now(), 'updated_at' => now()]
        );

        return KelompokKuota::create([
            'kelompok_kkn_id' => $kelompokId,
            'fakultas_id' => $fakultasId,
            'kuota' => 12,
            'kuota_laki' => 0,
            'kuota_perempuan' => 0,
        ]);
    }

    public function test_check_can_join_returns_true_for_empty_kelompok(): void
    {
        $kelompok = $this->createKelompok();
        $peserta = $this->createPeserta($kelompok->id);

        $this->assertTrue($this->service->checkCanJoin($kelompok, $peserta));
    }

    public function test_check_can_join_returns_false_when_kelompok_penuh(): void
    {
        $kelompok = $this->createKelompok(status: 'penuh');
        $peserta = $this->createPeserta($kelompok->id);

        $this->assertFalse($this->service->checkCanJoin($kelompok, $peserta));
    }

    public function test_check_can_join_returns_false_when_at_capacity(): void
    {
        $kelompok = $this->createKelompok(kuota: 2);
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);
        $peserta = $this->createPeserta($kelompok->id);

        $this->assertFalse($this->service->checkCanJoin($kelompok, $peserta));
    }

    public function test_check_can_join_returns_false_when_gender_limit_reached(): void
    {
        $kelompok = $this->createKelompok(kuota: 12);
        for ($i = 0; $i < WarRuleService::MAX_LAKI; $i++) {
            $this->createPeserta($kelompok->id, gender: 'L');
        }
        $peserta = $this->createPeserta($kelompok->id, gender: 'L');

        $this->assertFalse($this->service->checkCanJoin($kelompok, $peserta));
    }

    public function test_check_can_join_skips_prodi_check_when_false(): void
    {
        $kelompok = $this->createKelompok(kuota: 12);
        for ($i = 0; $i < WarRuleService::MAX_SAME_PRODI; $i++) {
            $this->createPeserta($kelompok->id, gender: 'P', prodiId: 1);
        }
        $peserta = $this->createPeserta($kelompok->id, gender: 'P', prodiId: 1);

        // With prodi check enabled, should return false
        $this->assertFalse($this->service->checkCanJoin($kelompok, $peserta, checkProdi: true));

        // With prodi check disabled, should return true
        $this->assertTrue($this->service->checkCanJoin($kelompok, $peserta, checkProdi: false));
    }

    public function test_check_can_join_uses_precomputed_kuota_when_provided(): void
    {
        $kelompok = $this->createKelompok(kuota: 12);
        $this->createPeserta($kelompok->id, gender: 'L');
        $peserta = $this->createPeserta($kelompok->id, gender: 'L');

        $kuota = $this->createKuota($kelompok->id);
        $kuota->update(['kuota' => 1]); // Faculty quota of 1

        // With precomputed kuota, should fail because faculty is at limit
        $this->assertFalse($this->service->checkCanJoin($kelompok, $peserta, $kuota));
    }
}
