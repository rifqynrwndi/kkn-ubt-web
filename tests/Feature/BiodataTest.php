<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BiodataTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);

        // Seed fakultas + prodi via raw DB to avoid FK chain issues
        DB::table('fakultas')->insert(['id' => 1, 'nama_fakultas' => 'Test', 'created_at' => now(), 'updated_at' => now()]);
        DB::table('program_studi')->insert(['id' => 1, 'fakultas_id' => 1, 'nama_prodi' => 'Test Prodi', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function createMahasiswa(array $overrides = []): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('mahasiswa');

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

    private function validBiodata(array $overrides = []): array
    {
        return array_merge([
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'no_hp' => '08123456789',
            'birth_place' => 'Tarakan',
            'birth_date' => '2000-01-01',
            'prodi_id' => 1,
            'nama_ortu' => 'Orang Tua',
            'no_hp_ortu' => '08123456788',
            'alamat_ortu' => 'Alamat Test',
        ], $overrides);
    }

    public function test_birth_place_is_required(): void
    {
        $user = $this->createMahasiswa();
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata([
            'birth_place' => '',
        ]));

        $response->assertSessionHasErrors('birth_place');
    }

    public function test_birth_date_is_required(): void
    {
        $user = $this->createMahasiswa();
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata([
            'birth_date' => '',
        ]));

        $response->assertSessionHasErrors('birth_date');
    }

    public function test_biodata_complete_with_all_fields(): void
    {
        $user = $this->createMahasiswa(['is_biodata_complete' => false]);
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata());

        $response->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertTrue($user->mahasiswa->is_biodata_complete);
    }

    public function test_biodata_stays_incomplete_when_birth_place_missing(): void
    {
        $user = $this->createMahasiswa(['is_biodata_complete' => false]);
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata([
            'birth_place' => '',
        ]));

        $response->assertSessionHasErrors('birth_place');
        $user->refresh();
        $this->assertFalse($user->mahasiswa->is_biodata_complete);
    }

    public function test_biodata_stays_incomplete_when_birth_date_missing(): void
    {
        $user = $this->createMahasiswa(['is_biodata_complete' => false]);
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata([
            'birth_date' => '',
        ]));

        $response->assertSessionHasErrors('birth_date');
        $user->refresh();
        $this->assertFalse($user->mahasiswa->is_biodata_complete);
    }

    public function test_biodata_incomplete_with_default_avatar(): void
    {
        $user = $this->createMahasiswa([
            'is_biodata_complete' => false,
            'foto' => 'avatar/avatar-1.png',
        ]);
        $this->actingAs($user);

        $this->put(route('biodata.update'), $this->validBiodata());

        $user->refresh();
        $this->assertFalse($user->mahasiswa->is_biodata_complete);
    }

    public function test_birth_date_must_be_in_the_past(): void
    {
        $user = $this->createMahasiswa();
        $this->actingAs($user);

        $response = $this->put(route('biodata.update'), $this->validBiodata([
            'birth_date' => date('Y-m-d', strtotime('+1 year')),
        ]));

        $response->assertSessionHasErrors('birth_date');
    }
}
