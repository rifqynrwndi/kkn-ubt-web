<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class KelompokKknControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_admin_can_access_index(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('kelompok-kkn.index'));

        $response->assertStatus(200);
    }

    public function test_mahasiswa_cannot_access_index(): void
    {
        $mhs = $this->createUserWithRole('mahasiswa');

        $response = $this->actingAs($mhs)->get(route('kelompok-kkn.index'));

        $response->assertStatus(403);
    }

    public function test_pembimbing_cannot_access_index(): void
    {
        $dpl = $this->createUserWithRole('pembimbing');

        $response = $this->actingAs($dpl)->get(route('kelompok-kkn.index'));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_redirected_to_login(): void
    {
        $response = $this->get(route('kelompok-kkn.index'));

        $response->assertRedirect('/login');
    }

    public function test_admin_can_access_create_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('kelompok-kkn.create'));

        $response->assertStatus(200);
    }

    public function test_admin_can_access_show_page(): void
    {
        $admin = $this->createAdmin();

        // Create minimal kelompok
        $kecamatanId = DB::table('kecamatan')->insertGetId([
            'nama_kecamatan' => 'Kec Test', 'kabupaten' => 'Kab Test',
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $desaId = DB::table('desa')->insertGetId([
            'nama_desa' => 'Desa Test', 'kecamatan_id' => $kecamatanId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $gelombangId = DB::table('gelombang')->insertGetId([
            'nama_gelombang' => 'Gelombang Test', 'tahun' => 2026,
            'tgl_mulai' => now()->toDateString(), 'tgl_akhir' => now()->addMonth()->toDateString(),
            'status' => 'berjalan', 'created_at' => now(), 'updated_at' => now(),
        ]);
        $dgId = DB::table('desa_gelombang')->insertGetId([
            'desa_id' => $desaId, 'gelombang_id' => $gelombangId,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $kelompokId = DB::table('kelompok_kkn')->insertGetId([
            'kode_kelompok' => 'K1', 'desa_gelombang_id' => $dgId,
            'nama_kelompok' => 'Kelompok 1', 'kuota' => 10,
            'status' => 'dibuka', 'status_tahap' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get(route('kelompok-kkn.show', $kelompokId));

        $response->assertStatus(200);
    }
}
