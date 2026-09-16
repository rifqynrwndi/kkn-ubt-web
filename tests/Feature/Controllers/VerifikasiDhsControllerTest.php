<?php

namespace Tests\Feature\Controllers;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class VerifikasiDhsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin(): User
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        $user->assignRole($role);

        return $user;
    }

    private function createMahasiswaWithDhs(string $status = 'pending'): Mahasiswa
    {
        $user = User::factory()->create();
        $role = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        $user->assignRole($role);

        $mhs = Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => '1234567890',
            'nama' => $user->name,
            'fakultas' => 'Fakultas Ilmu Sosial dan Ilmu Politik',
            'prodi' => 'Ilmu Administrasi Negara',
            'angkatan' => 2022,
            'no_hp' => '08123456789',
            'dhs_path' => 'dokumen-dhs/test.pdf',
            'dhs_status' => $status,
        ]);

        return $mhs;
    }

    public function test_admin_can_verify_dhs(): void
    {
        $admin = $this->createAdmin();
        $mhs = $this->createMahasiswaWithDhs('pending');

        $response = $this->actingAs($admin)->put(route('verifikasi-dokumen.dhs.verify', $mhs->user_id));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahasiswa', [
            'user_id' => $mhs->user_id,
            'dhs_status' => 'verified',
            'dhs_verified_by' => $admin->id,
        ]);
    }

    public function test_admin_can_reject_dhs_with_catatan(): void
    {
        $admin = $this->createAdmin();
        $mhs = $this->createMahasiswaWithDhs('pending');

        $response = $this->actingAs($admin)->put(route('verifikasi-dokumen.dhs.reject', $mhs->user_id), [
            'catatan' => 'DHS tidak terbaca dengan jelas',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('mahasiswa', [
            'user_id' => $mhs->user_id,
            'dhs_status' => 'rejected',
            'dhs_catatan' => 'DHS tidak terbaca dengan jelas',
        ]);
    }

    public function test_reject_requires_catatan(): void
    {
        $admin = $this->createAdmin();
        $mhs = $this->createMahasiswaWithDhs('pending');

        $response = $this->actingAs($admin)->put(route('verifikasi-dokumen.dhs.reject', $mhs->user_id), [
            'catatan' => '',
        ]);
        $response->assertSessionHasErrors('catatan');
    }

    public function test_verified_dhs_gets_notification(): void
    {
        $admin = $this->createAdmin();
        $mhs = $this->createMahasiswaWithDhs('pending');

        $this->actingAs($admin)->put(route('verifikasi-dokumen.dhs.verify', $mhs->user_id));

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\DhsVerifiedNotification',
            'notifiable_id' => $mhs->user_id,
        ]);
    }
}
