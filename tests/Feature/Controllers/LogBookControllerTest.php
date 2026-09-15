<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogBookControllerTest extends TestCase
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

    public function test_unauthenticated_redirected_to_login(): void
    {
        $response = $this->get(route('kelompok.logbook.create'));
        $response->assertRedirect('/login');
    }

    public function test_mahasiswa_without_kelompok_gets_404_on_create(): void
    {
        $mhs = $this->createUserWithRole('mahasiswa');

        $response = $this->actingAs($mhs)->get(route('kelompok.logbook.create'));
        $response->assertStatus(404);
    }

    public function test_admin_cannot_create_logbook(): void
    {
        $admin = $this->createUserWithRole('superadmin');

        $response = $this->actingAs($admin)->get(route('kelompok.logbook.create'));
        $response->assertStatus(404);
    }

    public function test_pembimbing_cannot_create_logbook(): void
    {
        $dpl = $this->createUserWithRole('pembimbing');

        $response = $this->actingAs($dpl)->get(route('kelompok.logbook.create'));
        $response->assertStatus(404);
    }
}
