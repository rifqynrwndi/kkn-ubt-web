<?php

namespace Tests\Feature;

use App\Models\DesaGelombang;
use App\Models\KelompokKkn;
use App\Models\TugasKelompok;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Database\Seeders\GelombangSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasTemplateRenameGuardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
        $this->seed(GelombangSeeder::class);
        $this->seed(DesaSeeder::class);
    }

    public function test_built_in_task_cannot_be_renamed(): void
    {
        $tugas = $this->tugas('Draft Artikel');

        $this->actingAs($this->superadmin())
            ->put(route('admin.tugas.updateByNama'), [
                'old_nama' => 'Draft Artikel',
                'nama_tugas' => 'Draft Artikel Revisi',
                'kategori' => 'tugas_kelompok',
            ])
            ->assertSessionHasErrors('nama_tugas');

        $this->assertSame('Draft Artikel', $tugas->fresh()->nama_tugas);
    }

    public function test_custom_task_cannot_be_renamed_to_built_in_name(): void
    {
        $tugas = $this->tugas('Tugas Bebas');

        $this->actingAs($this->superadmin())
            ->put(route('admin.tugas.updateByNama'), [
                'old_nama' => 'Tugas Bebas',
                'nama_tugas' => 'Draft Artikel',
                'kategori' => 'tugas_kelompok',
            ])
            ->assertSessionHasErrors('nama_tugas');

        $this->assertSame('Tugas Bebas', $tugas->fresh()->nama_tugas);
    }

    public function test_built_in_task_category_can_still_be_updated(): void
    {
        $tugas = $this->tugas('Draft Artikel');

        $this->actingAs($this->superadmin())
            ->put(route('admin.tugas.updateByNama'), [
                'old_nama' => 'Draft Artikel',
                'nama_tugas' => 'Draft Artikel',
                'kategori' => 'luaran_wajib',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame('luaran_wajib', $tugas->fresh()->kategori);
    }

    private function tugas(string $nama): TugasKelompok
    {
        $kelompok = KelompokKkn::create([
            'desa_gelombang_id' => DesaGelombang::first()->id,
            'nama_kelompok' => 'Kelompok Uji',
            'nomor_kelompok' => 1,
            'kuota' => 10,
        ]);

        return $kelompok->tugasKelompok()->create([
            'nama_tugas' => $nama,
            'kategori' => 'tugas_kelompok',
        ]);
    }

    private function superadmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        return $user;
    }
}
