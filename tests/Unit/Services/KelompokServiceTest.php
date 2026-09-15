<?php

namespace Tests\Unit\Services;

use App\Models\KelompokKkn;
use App\Services\KelompokService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KelompokServiceTest extends TestCase
{
    use RefreshDatabase;

    private KelompokService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new KelompokService;
    }

    public function test_kelompok_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(KelompokService::class, $this->service);
    }

    public function test_kelompok_service_methods_exist(): void
    {
        $this->assertTrue(method_exists($this->service, 'seedTugasTemplates'));
        $this->assertTrue(method_exists($this->service, 'removeAnggota'));
        $this->assertTrue(method_exists($this->service, 'checkAndMarkFull'));
    }

    public function test_seed_tugas_templates_creates_7_records(): void
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

        $desaGelombangId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $desaId,
            'gelombang_id' => $gelombangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kelompok = KelompokKkn::create([
            'desa_gelombang_id' => $desaGelombangId,
            'nama_kelompok' => 'Kelompok Test',
            'kuota' => 12,
            'status' => 'dibuka',
            'status_tahap' => 0,
        ]);

        $this->service->seedTugasTemplates($kelompok);

        $this->assertDatabaseCount('tugas_kelompok', 7);
        $this->assertDatabaseHas('tugas_kelompok', [
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => 'tugas_kelompok',
            'nama_tugas' => 'Program Kerja',
        ]);
        $this->assertDatabaseHas('tugas_kelompok', [
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => 'luaran_wajib',
            'nama_tugas' => 'Video Profil Desa',
            'is_wajib' => true,
        ]);
    }

    public function test_check_and_mark_full_reopens_when_below_capacity(): void
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

        $desaGelombangId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $desaId,
            'gelombang_id' => $gelombangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $kelompok = KelompokKkn::create([
            'desa_gelombang_id' => $desaGelombangId,
            'nama_kelompok' => 'Kelompok Test',
            'kuota' => 12,
            'status' => 'penuh',
            'status_tahap' => 0,
        ]);

        // Simulate being below capacity by manually setting terisi to 0
        // (we can't easily add members without FK setup, so test the logic directly)
        $this->service->checkAndMarkFull($kelompok);

        // Status should change to 'dibuka' since terisi (0) < kuota (12)
        $this->assertDatabaseHas('kelompok_kkn', [
            'id' => $kelompok->id,
            'status' => 'dibuka',
        ]);
    }
}
