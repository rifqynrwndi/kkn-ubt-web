<?php

namespace Tests\Feature\Controllers;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProposalControllerTest extends TestCase
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

    public function test_unauthenticated_redirected_to_login(): void
    {
        $response = $this->get(route('kelompok.proposal.index'));
        $response->assertRedirect('/login');
    }

    public function test_mahasiswa_without_kelompok_gets_404(): void
    {
        $mhs = $this->createUserWithRole('mahasiswa');

        $response = $this->actingAs($mhs)->get(route('kelompok.proposal.index'));
        $response->assertStatus(404);
    }

    public function test_admin_cannot_access_proposal_as_ketua(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get(route('kelompok.proposal.index'));
        $response->assertStatus(404);
    }

    public function test_pembimbing_without_kelompok_gets_404(): void
    {
        $dpl = $this->createUserWithRole('pembimbing');

        $response = $this->actingAs($dpl)->get(route('kelompok.proposal.index'));
        $response->assertStatus(404);
    }
}
