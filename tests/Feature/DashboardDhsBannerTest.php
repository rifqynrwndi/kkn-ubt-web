<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardDhsBannerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);

        DB::table('fakultas')->insert(['id' => 1, 'nama_fakultas' => 'Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('program_studi')->insert(['id' => 1, 'fakultas_id' => 1, 'nama_prodi' => 'Test Prodi', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createMahasiswa(array $overrides = []): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('mahasiswa');
        $user->markEmailAsVerified();

        Mahasiswa::create(array_merge([
            'user_id' => $user->id,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'no_hp' => '08123456789',
            'prodi_id' => 1,
            'nama_ortu' => 'Orang Tua',
            'no_hp_ortu' => '08123456788',
            'alamat_ortu' => 'Alamat Test',
            'foto' => 'foto-mahasiswa/test.jpg',
            'is_biodata_complete' => true,
        ], $overrides));

        return $user;
    }

    public function test_dashboard_shows_dhs_warning_when_no_dhs(): void
    {
        $user = $this->createMahasiswa([
            'dhs_path' => null,
        ]);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertSee('Unggah DHS Anda');
        $response->assertSee('belum mengunggah Daftar Hasil Studi');
    }

    public function test_dashboard_shows_dhs_warning_when_rejected(): void
    {
        $user = $this->createMahasiswa([
            'dhs_status' => 'rejected',
            'dhs_path' => 'dokumen-dhs/test.pdf',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertSee('Unggah DHS Anda');
        $response->assertSee('belum terverifikasi');
    }

    public function test_dashboard_hides_dhs_warning_when_verified(): void
    {
        $user = $this->createMahasiswa([
            'dhs_status' => 'verified',
            'dhs_path' => 'dokumen-dhs/test.pdf',
        ]);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertDontSee('Unggah DHS Anda');
    }

    public function test_dashboard_accessible_with_complete_biodata(): void
    {
        $user = $this->createMahasiswa(['is_biodata_complete' => true]);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertSee('Dashboard Mahasiswa');
    }
}
