<?php

namespace Tests\Feature\Middleware;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EnsureDhsVerifiedTest extends TestCase
{
    use RefreshDatabase;

    private function createMahasiswa(array $dhsAttrs = []): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        $user->assignRole($role);

        Mahasiswa::create(array_merge([
            'user_id' => $user->id,
            'npm' => '1234567890',
            'nama' => $user->name,
            'fakultas' => 'Fakultas Ilmu Sosial dan Ilmu Politik',
            'prodi' => 'Ilmu Administrasi Negara',
            'angkatan' => 2022,
            'no_hp' => '08123456789',
        ], $dhsAttrs));

        return $user;
    }

    public function test_unverified_mahasiswa_redirected_to_dhs_page(): void
    {
        $user = $this->createMahasiswa([
            'dhs_status' => 'pending',
            'dhs_path' => null,
        ]);

        $response = $this->actingAs($user)->get(route('pendaftaran-kkn.index'));
        $response->assertRedirect(route('biodata.edit'));
    }

    public function test_verified_mahasiswa_can_access_pendaftaran(): void
    {
        $user = $this->createMahasiswa([
            'dhs_status' => 'verified',
        ]);

        $response = $this->actingAs($user)->get(route('pendaftaran-kkn.index'));
        $response->assertStatus(200);
    }

    public function test_rejected_mahasiswa_redirected_to_dhs_page(): void
    {
        $user = $this->createMahasiswa([
            'dhs_status' => 'rejected',
        ]);

        $response = $this->actingAs($user)->get(route('pendaftaran-kkn.index'));
        $response->assertRedirect(route('biodata.edit'));
    }

    public function test_superadmin_not_affected_by_dhs_middleware(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('pendaftaran-kkn.index'));
        $response->assertStatus(200);
    }

    public function test_mahasiswa_without_biodata_not_affected(): void
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        $user->assignRole($role);

        $response = $this->actingAs($user)->get(route('pendaftaran-kkn.index'));
        $response->assertStatus(200);
    }
}
