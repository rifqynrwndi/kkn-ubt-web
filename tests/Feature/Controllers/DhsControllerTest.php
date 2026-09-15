<?php

namespace Tests\Feature\Controllers;

use App\Models\Mahasiswa;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DhsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
    }

    private function createMahasiswa(array $overrides = []): Mahasiswa
    {
        $user = User::factory()->create();
        $user->assignRole('mahasiswa');

        \DB::table('mahasiswa')->insert(array_merge([
            'user_id' => $user->id,
            'npm' => fake()->unique()->numerify('##########'),
            'dhs_status' => 'pending',
            'is_biodata_complete' => true,
        ], $overrides));

        return Mahasiswa::find($user->id);
    }

    /** @test */
    public function mahasiswa_can_upload_dhs(): void
    {
        Storage::fake('local');

        $mahasiswa = $this->createMahasiswa();
        $file = UploadedFile::fake()->create('dhs.pdf', 100, 'application/pdf');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertRedirect(route('biodata.edit'));
        $response->assertSessionHas('success');

        $mahasiswa->refresh();
        $this->assertEquals('pending', $mahasiswa->dhs_status);
        $this->assertNotNull($mahasiswa->dhs_path);
        $this->assertStringContainsString('dokumen-dhs/', $mahasiswa->dhs_path);
    }

    /** @test */
    public function dhs_validation_requires_pdf(): void
    {
        $mahasiswa = $this->createMahasiswa();
        $file = UploadedFile::fake()->create('image.jpg', 100, 'image/jpeg');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertSessionHasErrors('dhs_file');
    }

    /** @test */
    public function dhs_validation_max_2mb(): void
    {
        $mahasiswa = $this->createMahasiswa();
        $file = UploadedFile::fake()->create('dhs.pdf', 3000, 'application/pdf');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertSessionHasErrors('dhs_file');
    }

    /** @test */
    public function verified_mahasiswa_cannot_reupload(): void
    {
        $mahasiswa = $this->createMahasiswa(['dhs_status' => 'verified']);
        $file = UploadedFile::fake()->create('dhs.pdf', 100, 'application/pdf');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertRedirect(route('biodata.edit'));
        $response->assertSessionHas('info');
    }

    /** @test */
    public function rejected_mahasiswa_can_reupload(): void
    {
        Storage::fake('local');

        $mahasiswa = $this->createMahasiswa([
            'dhs_status' => 'rejected',
            'dhs_catatan' => 'File corrupt',
        ]);
        $file = UploadedFile::fake()->create('dhs.pdf', 100, 'application/pdf');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertRedirect(route('biodata.edit'));
        $response->assertSessionHas('success');

        $mahasiswa->refresh();
        $this->assertEquals('pending', $mahasiswa->dhs_status);
        $this->assertNull($mahasiswa->dhs_catatan);
    }

    /** @test */
    public function upload_sends_notification_to_admins(): void
    {
        Storage::fake('local');

        $mahasiswa = $this->createMahasiswa();

        $admin = User::factory()->create();
        $admin->assignRole('superadmin');

        $file = UploadedFile::fake()->create('dhs.pdf', 100, 'application/pdf');

        $response = $this->actingAs($mahasiswa->user)->post(route('profile.dhs.store'), [
            'dhs_file' => $file,
        ]);

        $response->assertRedirect(route('biodata.edit'));

        $this->assertDatabaseHas('notifications', [
            'type' => 'App\Notifications\DhsUploadedNotification',
        ]);
    }
}
