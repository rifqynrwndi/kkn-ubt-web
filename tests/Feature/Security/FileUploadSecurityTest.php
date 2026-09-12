<?php

namespace Tests\Feature\Security;

use App\Models\KelompokKkn;
use App\Models\KelompokProposal;
use App\Models\PesertaKkn;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class FileUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createKetua(): User
    {
        $user = User::factory()->create();
        $user->assignRole('mahasiswa');

        $mahasiswa = $user->mahasiswa()->create([
            'npm' => '1234567890',
            'jenis_kelamin' => 'L',
            'prodi_id' => 1,
            'is_biodata_complete' => true,
        ]);

        $kelompok = KelompokKkn::create([
            'nama_kelompok' => 'Test Kelompok',
            'kuota' => 10,
        ]);

        $peserta = PesertaKkn::create([
            'mahasiswa_id' => $user->id,
            'kelompok_kkn_id' => $kelompok->id,
        ]);

        $kelompok->update(['ketua_peserta_id' => $peserta->id]);

        return $user;
    }

    public function test_php_file_rejected_on_tugas_submission(): void
    {
        $user = $this->createKetua();
        $kelompok = $user->pesertaKkn()->first()->kelompokKkn;

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
        $user = $this->createKetua();
        $kelompok = $user->pesertaKkn()->first()->kelompokKkn;

        $tugas = $kelompok->tugasKelompok()->create([
            'nama_tugas' => 'Test Tugas',
            'kategori' => 'tugas_kelompok',
        ]);

        $maliciousFile = UploadedFile::fake()->createWithContent('malware.exe', 'MZ' . random_bytes(100));

        $response = $this->actingAs($user)->post(route('kelompok.tugas.submit', $tugas->id), [
            'judul' => 'Test',
            'file' => $maliciousFile,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_valid_pdf_accepted_on_tugas_submission(): void
    {
        $user = $this->createKetua();
        $kelompok = $user->pesertaKkn()->first()->kelompokKkn;

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
        $user = $this->createKetua();
        $kelompok = $user->pesertaKkn()->first()->kelompokKkn;

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
