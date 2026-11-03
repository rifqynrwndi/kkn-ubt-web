<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DraftArtikelExportAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get(route('dpl.draft-artikel.summary'))
            ->assertRedirect('/login');
    }

    public function test_mahasiswa_cannot_access_dpl_summary(): void
    {
        $this->actingAs($this->userWithRole('mahasiswa'))
            ->getJson(route('dpl.draft-artikel.summary'))
            ->assertForbidden();
    }

    public function test_pembimbing_cannot_access_admin_zip(): void
    {
        $this->actingAs($this->userWithRole('pembimbing'))
            ->get(route('admin.tugas.draft-artikel.zip'))
            ->assertForbidden();
    }

    public function test_superadmin_can_open_tugas_index(): void
    {
        $this->actingAs($this->userWithRole('superadmin'))
            ->get(route('admin.tugas.index'))
            ->assertOk();
    }

    public function test_pembimbing_can_open_kelompok_index(): void
    {
        $user = $this->userWithRole('pembimbing');
        $user->dosenPembimbingLapangan()->create([]);

        $this->actingAs($user)
            ->get(route('dpl.kelompok.index'))
            ->assertOk();
    }

    public function test_pembimbing_summary_requires_gelombang(): void
    {
        $this->actingAs($this->userWithRole('pembimbing'))
            ->getJson(route('dpl.draft-artikel.summary'))
            ->assertStatus(422)
            ->assertJsonValidationErrors('gelombang_id');
    }

    public function test_superadmin_zip_requires_gelombang(): void
    {
        $this->actingAs($this->userWithRole('superadmin'))
            ->get(route('admin.tugas.draft-artikel.zip'))
            ->assertRedirect()
            ->assertSessionHasErrors('gelombang_id');
    }

    public function test_superadmin_zip_rejects_unknown_gelombang(): void
    {
        $this->actingAs($this->userWithRole('superadmin'))
            ->get(route('admin.tugas.draft-artikel.zip', ['gelombang_id' => 999999]))
            ->assertRedirect()
            ->assertSessionHasErrors('gelombang_id');
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
