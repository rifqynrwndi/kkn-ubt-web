<?php

namespace Tests\Unit\Models;

use App\Models\Mahasiswa;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MahasiswaDhsStatusTest extends TestCase
{
    use RefreshDatabase;

    private function createMahasiswa(array $overrides = []): Mahasiswa
    {
        $user = \DB::table('users')->insertGetId([
            'name' => 'Test User',
            'email' => fake()->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('mahasiswa')->insert(array_merge([
            'user_id' => $user,
            'npm' => fake()->unique()->numerify('##########'),
            'dhs_status' => 'pending',
        ], $overrides));

        return Mahasiswa::find($user);
    }

    /** @test */
    public function has_dhs_verified_returns_true_when_verified(): void
    {
        $mahasiswa = $this->createMahasiswa(['dhs_status' => 'verified']);

        $this->assertTrue($mahasiswa->hasDhsVerified());
    }

    /** @test */
    public function has_dhs_verified_returns_false_when_pending(): void
    {
        $mahasiswa = $this->createMahasiswa(['dhs_status' => 'pending']);

        $this->assertFalse($mahasiswa->hasDhsVerified());
    }

    /** @test */
    public function has_dhs_verified_returns_false_when_rejected(): void
    {
        $mahasiswa = $this->createMahasiswa(['dhs_status' => 'rejected']);

        $this->assertFalse($mahasiswa->hasDhsVerified());
    }

    /** @test */
    public function dhs_status_defaults_to_pending(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $this->assertEquals('pending', $mahasiswa->dhs_status);
    }

    /** @test */
    public function dhs_verified_by_is_null_by_default(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $this->assertNull($mahasiswa->dhs_verified_by);
    }

    /** @test */
    public function dhs_verified_at_is_null_by_default(): void
    {
        $mahasiswa = $this->createMahasiswa();

        $this->assertNull($mahasiswa->dhs_verified_at);
    }

    /** @test */
    public function dhs_verifier_relationship_returns_user(): void
    {
        $admin = \DB::table('users')->insertGetId([
            'name' => 'Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $mahasiswa = $this->createMahasiswa([
            'dhs_status' => 'verified',
            'dhs_verified_by' => $admin,
            'dhs_verified_at' => now(),
        ]);

        $this->assertEquals($admin, $mahasiswa->dhsVerifier->id);
    }

    /** @test */
    public function dhs_path_can_be_set(): void
    {
        $mahasiswa = $this->createMahasiswa([
            'dhs_path' => 'dokumen-dhs/test.pdf',
        ]);

        $this->assertEquals('dokumen-dhs/test.pdf', $mahasiswa->dhs_path);
    }

    /** @test */
    public function dhs_catatan_can_be_set(): void
    {
        $mahasiswa = $this->createMahasiswa([
            'dhs_status' => 'rejected',
            'dhs_catatan' => 'File corrupt',
        ]);

        $this->assertEquals('File corrupt', $mahasiswa->dhs_catatan);
    }
}
