<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PhotoCropModalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);

        DB::table('fakultas')->insert(['id' => 1, 'nama_fakultas' => 'Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('program_studi')->insert(['id' => 1, 'fakultas_id' => 1, 'nama_prodi' => 'Test Prodi', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createMahasiswa(): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@test.com',
            'password' => bcrypt('password'),
        ]);
        $user->assignRole('mahasiswa');
        $user->markEmailAsVerified();

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
            'is_biodata_complete' => true,
        ]);

        return $user;
    }

    public function test_biodata_form_has_crop_modal(): void
    {
        $user = $this->createMahasiswa();
        $this->actingAs($user);

        $response = $this->get(route('biodata.edit'));

        $response->assertSuccessful();
        $response->assertSee('cropPhotoModal');
        $response->assertSee('crop-image');
        $response->assertSee('crop-preview');
        $response->assertSee('Ganti Foto');
    }

    public function test_biodata_form_includes_cropper_js(): void
    {
        $user = $this->createMahasiswa();
        $this->actingAs($user);

        $response = $this->get(route('biodata.edit'));

        $response->assertSuccessful();
        $response->assertSee('cropper.min.js');
        $response->assertSee('profile-photo-crop.js');
    }
}
