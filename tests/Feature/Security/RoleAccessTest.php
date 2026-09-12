<?php

namespace Tests\Feature\Security;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createUserWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }

    public function test_mahasiswa_cannot_access_admin_mahasiswa_index(): void
    {
        $user = $this->createUserWithRole('mahasiswa');
        $response = $this->actingAs($user)->get(route('mahasiswa.index'));
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_admin_kelompok_index(): void
    {
        $user = $this->createUserWithRole('mahasiswa');
        $response = $this->actingAs($user)->get(route('kelompok-kkn.index'));
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_admin_gelombang_index(): void
    {
        $user = $this->createUserWithRole('mahasiswa');
        $response = $this->actingAs($user)->get(route('gelombang.index'));
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_admin_hakakses_index(): void
    {
        $user = $this->createUserWithRole('mahasiswa');
        $response = $this->actingAs($user)->get(route('hakakses.index'));
        $response->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_admin_war_index(): void
    {
        $user = $this->createUserWithRole('mahasiswa');
        $response = $this->actingAs($user)->get(route('admin.war.index'));
        $response->assertStatus(403);
    }

    public function test_pembimbing_cannot_access_admin_mahasiswa_index(): void
    {
        $user = $this->createUserWithRole('pembimbing');
        $response = $this->actingAs($user)->get(route('mahasiswa.index'));
        $response->assertStatus(403);
    }

    public function test_pembimbing_cannot_access_admin_kelompok_index(): void
    {
        $user = $this->createUserWithRole('pembimbing');
        $response = $this->actingAs($user)->get(route('kelompok-kkn.index'));
        $response->assertStatus(403);
    }

    public function test_superadmin_can_access_admin_mahasiswa_index(): void
    {
        $user = $this->createUserWithRole('superadmin');
        $response = $this->actingAs($user)->get(route('mahasiswa.index'));
        $response->assertStatus(200);
    }

    public function test_superadmin_can_access_admin_kelompok_index(): void
    {
        $user = $this->createUserWithRole('superadmin');
        $response = $this->actingAs($user)->get(route('kelompok-kkn.index'));
        $response->assertStatus(200);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/home');
        $response->assertRedirect('/login');
    }

    public function test_unauthenticated_user_cannot_access_kelompok_kkn(): void
    {
        $response = $this->get('/kelompok-kkn');
        $response->assertRedirect('/login');
    }
}
