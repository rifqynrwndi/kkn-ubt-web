<?php

namespace Tests\Feature\Security;

use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerifyEmailTest extends TestCase
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

    private function createUnverifiedMahasiswa(): User
    {
        $user = User::factory()->unverified()->create();
        $user->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => fake()->numerify('##########'),
            'jenis_kelamin' => 'L',
        ]);

        return $user;
    }

    public function test_admin_can_verify_mahasiswa_email(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createUnverifiedMahasiswa();

        $response = $this->actingAs($admin)
            ->post(route('mahasiswa.verify-email', $mahasiswa->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertNotNull($mahasiswa->fresh()->email_verified_at);
    }

    public function test_verify_sets_email_verified_at_to_now(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createUnverifiedMahasiswa();

        $this->actingAs($admin)
            ->post(route('mahasiswa.verify-email', $mahasiswa->id));

        $mahasiswa->refresh();
        $this->assertNotNull($mahasiswa->email_verified_at);
        $this->assertTrue($mahasiswa->email_verified_at->isCurrentDay());
    }

    public function test_already_verified_user_still_works(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = User::factory()->create(['email_verified_at' => now()->subDay()]);
        $mahasiswa->assignRole('mahasiswa');

        $response = $this->actingAs($admin)
            ->post(route('mahasiswa.verify-email', $mahasiswa->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_mahasiswa_cannot_verify_email(): void
    {
        $mahasiswa = User::factory()->create();
        $mahasiswa->assignRole('mahasiswa');

        $target = $this->createUnverifiedMahasiswa();

        $response = $this->actingAs($mahasiswa)
            ->post(route('mahasiswa.verify-email', $target->id));

        $response->assertStatus(403);
    }

    public function test_pembimbing_cannot_verify_email(): void
    {
        $dpl = User::factory()->create();
        $dpl->assignRole('pembimbing');

        $mahasiswa = $this->createUnverifiedMahasiswa();

        $response = $this->actingAs($dpl)
            ->post(route('mahasiswa.verify-email', $mahasiswa->id));

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $mahasiswa = $this->createUnverifiedMahasiswa();

        $response = $this->post(route('mahasiswa.verify-email', $mahasiswa->id));

        $response->assertRedirect('/login');
    }

    public function test_invalid_id_returns_404(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->post(route('mahasiswa.verify-email', 999999));

        $response->assertStatus(404);
    }

    public function test_non_mahasiswa_user_returns_404(): void
    {
        $admin = $this->createAdmin();
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole('superadmin');

        $response = $this->actingAs($admin)
            ->post(route('mahasiswa.verify-email', $otherAdmin->id));

        $response->assertStatus(404);
    }
}
