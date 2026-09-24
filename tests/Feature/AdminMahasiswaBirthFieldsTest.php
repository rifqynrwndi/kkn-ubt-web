<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminMahasiswaBirthFieldsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        DB::table('fakultas')->insert(['id' => 1, 'nama_fakultas' => 'Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('program_studi')->insert(['id' => 1, 'fakultas_id' => 1, 'nama_prodi' => 'Test Prodi', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createAdmin(): User
    {
        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('superadmin');

        return $user;
    }

    private function createMahasiswa(): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'no_hp' => '08123456789',
            'prodi_id' => 1,
            'nama_ortu' => 'Orang Tua',
            'no_hp_ortu' => '08123456788',
            'alamat_ortu' => 'Alamat Test',
            'foto' => 'foto-mahasiswa/test.jpg',
            'birth_place' => 'Tarakan',
            'birth_date' => '2000-01-01',
            'is_biodata_complete' => true,
        ]);

        return $user;
    }

    public function test_admin_edit_form_shows_birth_fields(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->get(route('mahasiswa.edit', $mahasiswa->id));

        $response->assertSuccessful();
        $response->assertSee('birth_place');
        $response->assertSee('birth_date');
        $response->assertSee('Ketik nama kota/kabupaten');
    }

    public function test_admin_show_displays_birth_data(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->get(route('mahasiswa.show', $mahasiswa->id));

        $response->assertSuccessful();
        $response->assertSee('Tempat Lahir');
        $response->assertSee('Tarakan');
        $response->assertSee('Tanggal Lahir');
        $response->assertSee('01 Jan 2000');
    }

    public function test_admin_update_saves_birth_data(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->put(route('mahasiswa.update', $mahasiswa->id), [
            'name' => 'Updated Name',
            'email' => $mahasiswa->email,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'prodi_id' => 1,
            'no_hp' => '08123456789',
            'birth_place' => 'Jakarta',
            'birth_date' => '1999-06-15',
            'nama_ortu' => 'Orang Tua',
            'no_hp_ortu' => '08123456788',
            'alamat_ortu' => 'Alamat Test',
        ]);

        $response->assertRedirect();
        $mahasiswa->refresh();
        $this->assertEquals('Jakarta', $mahasiswa->mahasiswa->birth_place);
        $this->assertEquals('1999-06-15', $mahasiswa->mahasiswa->birth_date->format('Y-m-d'));
    }

    public function test_admin_update_requires_birth_place(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->put(route('mahasiswa.update', $mahasiswa->id), [
            'name' => 'Updated Name',
            'email' => $mahasiswa->email,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'prodi_id' => 1,
            'birth_place' => '',
            'birth_date' => '2000-01-01',
        ]);

        $response->assertSessionHasErrors('birth_place');
    }

    public function test_admin_update_requires_birth_date(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->put(route('mahasiswa.update', $mahasiswa->id), [
            'name' => 'Updated Name',
            'email' => $mahasiswa->email,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'prodi_id' => 1,
            'birth_place' => 'Jakarta',
            'birth_date' => '',
        ]);

        $response->assertSessionHasErrors('birth_date');
    }

    public function test_admin_update_rejects_future_birth_date(): void
    {
        $admin = $this->createAdmin();
        $mahasiswa = $this->createMahasiswa();
        $this->actingAs($admin);

        $response = $this->put(route('mahasiswa.update', $mahasiswa->id), [
            'name' => 'Updated Name',
            'email' => $mahasiswa->email,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'prodi_id' => 1,
            'birth_place' => 'Jakarta',
            'birth_date' => date('Y-m-d', strtotime('+1 year')),
        ]);

        $response->assertSessionHasErrors('birth_date');
    }
}
