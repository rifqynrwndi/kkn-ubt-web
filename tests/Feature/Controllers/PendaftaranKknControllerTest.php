<?php

namespace Tests\Feature\Controllers;

use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PendaftaranKknControllerTest extends TestCase
{
    use RefreshDatabase;

    private int $userId;

    private int $gelombangId;

    private int $kelompokId;

    protected function setUp(): void
    {
        parent::setUp();

        // Create fakultas + prodi
        DB::table('fakultas')->insert(['id' => 1, 'nama_fakultas' => 'Fakultas Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('program_studi')->insert(['id' => 1, 'nama_prodi' => 'Prodi Test', 'fakultas_id' => 1, 'created_at' => now(), 'updated_at' => now()]);

        // Create user + mahasiswa
        $this->userId = DB::table('users')->insertGetId([
            'name' => 'Test User', 'email' => 'test@test.com', 'password' => bcrypt('password'),
            'created_at' => now(), 'updated_at' => now(),
        ]);

        DB::table('mahasiswa')->insert([
            'user_id' => $this->userId, 'npm' => '99999999999', 'jenis_kelamin' => 'L', 'prodi_id' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Create gelombang
        $this->gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Gelombang Test', 'tahun' => date('Y'),
            'tgl_mulai' => now(), 'tgl_akhir' => now()->addMonth(),
            'status' => 'pendaftaran', 'kuota_laki' => 50, 'kuota_perempuan' => 50, 'kuota_total' => 100,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        // Create desa + kecamatan + desa_gelombang
        $kecId = DB::table('kecamatan')->insertGetId(['nama_kecamatan' => 'Kec Test', 'kabupaten' => 'Kab Test', 'created_at' => now(), 'updated_at' => now()]);
        $desaId = DB::table('desa')->insertGetId(['nama_desa' => 'Desa Test', 'kecamatan_id' => $kecId, 'created_at' => now(), 'updated_at' => now()]);
        $desaGelId = DB::table('desa_gelombang')->insertGetId(['desa_id' => $desaId, 'gelombang_id' => $this->gelombangId, 'created_at' => now(), 'updated_at' => now()]);

        // Create kelompok using model (triggers kode_kelompok generation)
        $kelompok = KelompokKkn::create([
            'desa_gelombang_id' => $desaGelId, 'nama_kelompok' => 'Kelompok Test',
            'kuota' => 12, 'status' => 'dibuka', 'status_tahap' => 0,
        ]);
        $this->kelompokId = $kelompok->id;
    }

    private function createPeserta(string $status = 'approved', ?int $kelompokId = null): PesertaKkn
    {
        $pesertaId = DB::table('peserta_kkn')->insertGetId([
            'mahasiswa_id' => $this->userId, 'gelombang_id' => $this->gelombangId,
            'kelompok_kkn_id' => $kelompokId, 'status_pendaftaran' => $status,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return PesertaKkn::find($pesertaId);
    }

    public function test_mahasiswa_can_access_plotting(): void
    {
        $user = User::find($this->userId);
        $this->actingAs($user);

        $this->createPeserta(status: 'approved');

        $response = $this->get(route('pendaftaran-kkn.plotting'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $response = $this->get(route('pendaftaran-kkn.plotting'));
        $response->assertRedirect();
    }

    public function test_ambil_kelompok_rejects_unapproved_pendaftaran(): void
    {
        $user = User::find($this->userId);
        $this->actingAs($user);

        $this->createPeserta(status: 'draft');

        $response = $this->post(route('pendaftaran-kkn.ambil-kelompok', $this->kelompokId));
        $response->assertSessionHas('error');
    }

    public function test_ambil_kelompok_rejects_already_has_kelompok(): void
    {
        $user = User::find($this->userId);
        $this->actingAs($user);

        $this->createPeserta(status: 'approved', kelompokId: $this->kelompokId);

        $response = $this->post(route('pendaftaran-kkn.ambil-kelompok', $this->kelompokId));
        $response->assertSessionHas('error');
    }

    public function test_ambil_kelompok_rejects_penuh_status(): void
    {
        $user = User::find($this->userId);
        $this->actingAs($user);

        DB::table('kelompok_kkn')->where('id', $this->kelompokId)->update(['status' => 'penuh']);

        $this->createPeserta(status: 'approved');

        $response = $this->post(route('pendaftaran-kkn.ambil-kelompok', $this->kelompokId));
        $response->assertSessionHas('error');
    }

    public function test_ambil_kelompok_success_for_valid_request(): void
    {
        $user = User::find($this->userId);
        $this->actingAs($user);

        $this->createPeserta(status: 'approved');

        $response = $this->post(route('pendaftaran-kkn.ambil-kelompok', $this->kelompokId));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('peserta_kkn', [
            'mahasiswa_id' => $this->userId,
            'kelompok_kkn_id' => $this->kelompokId,
        ]);
    }
}
