<?php

namespace Tests\Unit\Models;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KelompokKknTest extends TestCase
{
    use RefreshDatabase;

    private int $kecamatanId;
    private int $desaId;
    private int $gelombangId;
    private int $desaGelombangId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->kecamatanId = DB::table('kecamatan')->insertGetId([
            'nama_kecamatan' => 'Kecamatan Test',
            'kabupaten' => 'Kabupaten Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->desaId = DB::table('desa')->insertGetId([
            'nama_desa' => 'Desa Test',
            'kecamatan_id' => $this->kecamatanId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

        $this->desaGelombangId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $this->desaId,
            'gelombang_id' => $this->gelombangId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createKelompok(int $kuota = 12): KelompokKkn
    {
        return KelompokKkn::create([
            'desa_gelombang_id' => $this->desaGelombangId,
            'nama_kelompok' => 'Kelompok Test',
            'kuota' => $kuota,
            'status' => 'dibuka',
            'status_tahap' => 0,
        ]);
    }

    private function createPeserta(int $kelompokId): PesertaKkn
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
            'jenis_kelamin' => 'L',
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

    public function test_terisi_returns_correct_count(): void
    {
        $kelompok = $this->createKelompok();
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);

        $this->assertEquals(2, $kelompok->terisi);
    }

    public function test_terisi_returns_zero_when_empty(): void
    {
        $kelompok = $this->createKelompok();

        $this->assertEquals(0, $kelompok->terisi);
    }

    public function test_is_full_returns_true_when_at_capacity(): void
    {
        $kelompok = $this->createKelompok(kuota: 2);
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);

        $this->assertTrue($kelompok->is_full);
    }

    public function test_is_full_returns_false_when_under_capacity(): void
    {
        $kelompok = $this->createKelompok(kuota: 3);
        $this->createPeserta($kelompok->id);

        $this->assertFalse($kelompok->is_full);
    }

    public function test_sisa_kuota_returns_correct_value(): void
    {
        $kelompok = $this->createKelompok(kuota: 12);
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);

        $this->assertEquals(9, $kelompok->sisa_kuota);
    }

    public function test_terisi_uses_eager_loaded_collection_not_query(): void
    {
        $kelompok = $this->createKelompok(kuota: 12);
        $this->createPeserta($kelompok->id);
        $this->createPeserta($kelompok->id);

        // Load with eager loading
        $loaded = KelompokKkn::with('pesertaKkn')->find($kelompok->id);

        // Access terisi — should use eager-loaded collection, not fire new query
        DB::enableQueryLog();
        $count = $loaded->terisi;
        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        $this->assertEquals(2, $count);
        // No new SELECT queries should have been fired for peserta_kkn
        $pesertaQueries = array_filter($queries, fn ($q) => str_contains($q['query'], 'peserta_kkn'));
        $this->assertEmpty($pesertaQueries, 'terisi should use eager-loaded collection, not fire new query');
    }
}
