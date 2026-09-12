<?php

namespace Tests\Feature\Security;

use App\Models\DesaGelombang;
use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use App\Models\User;
use Database\Seeders\DesaSeeder;
use Database\Seeders\FakultasProdiSeeder;
use Database\Seeders\GelombangSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(FakultasProdiSeeder::class);
        $this->seed(GelombangSeeder::class);
        $this->seed(DesaSeeder::class);
    }

    private function createKetua(): array
    {
        $user = User::factory()->create();
        $user->assignRole('mahasiswa');

        $prodiId = ProgramStudi::first()->id;
        $user->mahasiswa()->create([
            'npm' => '1234567890',
            'jenis_kelamin' => 'L',
            'prodi_id' => $prodiId,
            'is_biodata_complete' => true,
        ]);

        $desaGelombang = DesaGelombang::first();
        $gelombang = Gelombang::first();
        $kelompok = KelompokKkn::create([
            'desa_gelombang_id' => $desaGelombang->id,
            'nama_kelompok' => 'Test Kelompok',
            'nomor_kelompok' => 1,
            'kuota' => 10,
        ]);

        $peserta = PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'kelompok_kkn_id' => $kelompok->id,
            'gelombang_id' => $gelombang->id,
        ]);

        $kelompok->update(['ketua_peserta_id' => $peserta->id]);

        return ['user' => $user, 'kelompok' => $kelompok];
    }

    public function test_php_file_rejected_on_tugas_submission(): void
    {
        ['user' => $user, 'kelompok' => $kelompok] = $this->createKetua();

        $tugas = $kelompok->tugasKelompok()->create([
            'nama_tugas' => 'Test Tugas',
            'kategori' => 'tugas_kelompok',
        ]);

        $maliciousFile = UploadedFile::fake()->createWithContent('shell.php', '<?php echo "hacked"; ?>');

        $response = $this->actingAs($user)->post(route('kelompok.tugas.submit', $tugas->id), [
            'judul' => 'Test',
            'file' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_exe_file_rejected_on_tugas_submission(): void
    {
        ['user' => $user, 'kelompok' => $kelompok] = $this->createKetua();

        $tugas = $kelompok->tugasKelompok()->create([
            'nama_tugas' => 'Test Tugas',
            'kategori' => 'tugas_kelompok',
        ]);

        $maliciousFile = UploadedFile::fake()->createWithContent('malware.exe', 'MZ'.random_bytes(100));

        $response = $this->actingAs($user)->post(route('kelompok.tugas.submit', $tugas->id), [
            'judul' => 'Test',
            'file' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_valid_pdf_accepted_on_tugas_submission(): void
    {
        ['user' => $user, 'kelompok' => $kelompok] = $this->createKetua();

        $tugas = $kelompok->tugasKelompok()->create([
            'nama_tugas' => 'Test Tugas',
            'kategori' => 'tugas_kelompok',
        ]);

        $validFile = UploadedFile::fake()->createWithContent('document.pdf', '%PDF-1.4 fake content');

        $response = $this->actingAs($user)->post(route('kelompok.tugas.submit', $tugas->id), [
            'judul' => 'Test',
            'file' => $validFile,
        ]);

        $response->assertSessionHasNoErrors();
    }

    public function test_oversized_file_rejected(): void
    {
        ['user' => $user, 'kelompok' => $kelompok] = $this->createKetua();

        $tugas = $kelompok->tugasKelompok()->create([
            'nama_tugas' => 'Test Tugas',
            'kategori' => 'tugas_kelompok',
        ]);

        $oversizedFile = UploadedFile::fake()->createWithContent('big.pdf', str_repeat('a', 11 * 1024 * 1024));

        $response = $this->actingAs($user)->post(route('kelompok.tugas.submit', $tugas->id), [
            'judul' => 'Test',
            'file' => $oversizedFile,
        ]);

        $response->assertSessionHasErrors('file');
    }
}
