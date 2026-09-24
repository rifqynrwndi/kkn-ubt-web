<?php

namespace Tests\Feature;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TelegramBannerTest extends TestCase
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

    private function createMahasiswaWithGelombang(?string $telegramUrl = null): User
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $user->assignRole('mahasiswa');

        // Mark email as verified so we can access /home
        $user->markEmailAsVerified();

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => '2240302001',
            'jenis_kelamin' => 'L',
            'no_hp' => '08123456789',
            'prodi_id' => null,
            'nama_ortu' => 'Orang Tua',
            'no_hp_ortu' => '08123456788',
            'alamat_ortu' => 'Alamat Test',
            'foto' => 'foto-mahasiswa/test.jpg',
            'is_biodata_complete' => true,
        ]);

        Gelombang::create([
            'nama_gelombang' => 'Gelombang 1',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_akhir' => '2026-12-31',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'kuota_total' => 100,
            'status' => 'pendaftaran',
            'telegram_group_url' => $telegramUrl,
        ]);

        return $user;
    }

    public function test_telegram_banner_shows_when_url_exists(): void
    {
        $user = $this->createMahasiswaWithGelombang('https://t.me/kknubt2026');
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertSee('Gabung Grup Telegram KKN');
        $response->assertSee('https://t.me/kknubt2026');
        $response->assertSee('Join Sekarang');
    }

    public function test_telegram_banner_hidden_when_url_null(): void
    {
        $user = $this->createMahasiswaWithGelombang(null);
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertDontSee('Gabung Grup Telegram KKN');
    }

    public function test_telegram_banner_opens_in_new_tab(): void
    {
        $user = $this->createMahasiswaWithGelombang('https://t.me/kknubt2026');
        $this->actingAs($user);

        $response = $this->get(route('home'));

        $response->assertSuccessful();
        $response->assertSee('target="_blank"', escape: false);
        $response->assertSee('rel="noopener noreferrer"', escape: false);
    }

    public function test_gelombang_url_validates_as_url(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('superadmin');
        $this->actingAs($admin);

        $response = $this->post(route('gelombang.store'), [
            'nama_gelombang' => 'Test',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_akhir' => '2026-12-31',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'status' => 'persiapan',
            'telegram_group_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('telegram_group_url');
    }

    public function test_gelombang_url_is_optional(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);
        $admin->assignRole('superadmin');
        $this->actingAs($admin);

        $response = $this->post(route('gelombang.store'), [
            'nama_gelombang' => 'Test',
            'tahun' => 2026,
            'tgl_mulai' => '2026-01-01',
            'tgl_akhir' => '2026-12-31',
            'kuota_laki' => 50,
            'kuota_perempuan' => 50,
            'status' => 'persiapan',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gelombang', [
            'nama_gelombang' => 'Test',
            'telegram_group_url' => null,
        ]);
    }
}
