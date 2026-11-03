<?php

namespace Database\Seeders;

use App\Models\DosenPembimbingLapangan;
use App\Models\Gelombang;
use App\Models\KelompokKkn;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use App\Models\TugasKelompok;
use App\Models\TugasSubmission;
use App\Models\User;
use App\Support\TugasTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class DummyDraftArtikelSeeder extends Seeder
{
    private const NAMA_PESERTA = [
        'Ahmad Fauzi',
        'Siti Nurhaliza',
        'Rizky Pratama',
        'Dewi Lestari',
        'Bayu Saputra',
        'Nur Aisyah',
        'Fajar Ramadhan',
        'Intan Permata',
        'Hendra Wijaya',
        'Maya Sari',
        'Yoga Prasetyo',
        'Rina Marlina',
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('DummyDraftArtikelSeeder dilewati di production.');

            return;
        }

        $gelombang = Gelombang::all()->first(
            fn (Gelombang $g) => KelompokKkn::whereHas(
                'desaGelombang',
                fn ($q) => $q->where('gelombang_id', $g->id)
            )->count() >= 7
        );

        if (! $gelombang) {
            throw new RuntimeException('Tidak ada gelombang dengan minimal 7 kelompok. Jalankan seeder kelompok terlebih dahulu.');
        }

        $kelompoks = KelompokKkn::whereHas(
            'desaGelombang',
            fn ($q) => $q->where('gelombang_id', $gelombang->id)
        )
            ->with('desaGelombang')
            ->orderBy('nama_kelompok')
            ->take(7)
            ->get()
            ->values();

        $dplA = $this->dpl('DPL Dummy Satu, M.Pd.', 'dpl.dummy@kkn.test', '0000000001');
        $dplB = $this->dpl('DPL Dummy Dua, M.Pd.', 'dpl.dummy2@kkn.test', '0000000002');

        $plan = [
            [$kelompoks[0], $dplA, 'diterima', 'Sudah sesuai format.'],
            [$kelompoks[1], $dplA, 'menunggu', null],
            [$kelompoks[2], $dplB, 'revisi', 'Perbaiki daftar pustaka.'],
            [$kelompoks[3], $dplB, 'menunggu', null],
            [$kelompoks[4], $dplB, 'diterima', 'Layak dilanjutkan.'],
            [$kelompoks[5], null, 'ditolak', 'File tidak terbaca.'],
        ];

        foreach ($plan as [$kelompok, $dpl, $status, $komentar]) {
            $this->seedKelompok($gelombang, $kelompok, $dpl, $status, $komentar);
        }

        // Kelompok terakhir sengaja dibiarkan tanpa DPL dan tanpa submission
        // agar modal admin menampilkan baris "Belum mengumpulkan".
        $kelompoks[6]->update([
            'dosen_pembimbing_lapangan_id' => null,
            'ketua_peserta_id' => null,
        ]);

        $this->command?->info('Dummy Draft Artikel siap pada gelombang: '.$gelombang->nama_gelombang.' (ID '.$gelombang->id.')');
        $this->command?->info('DPL A: dpl.dummy@kkn.test / password (2 kelompok, keduanya upload)');
        $this->command?->info('DPL B: dpl.dummy2@kkn.test / password (3 kelompok, semuanya upload)');
        $this->command?->info('Tanpa DPL: '.$kelompoks[5]->nama_kelompok.' (upload), '.$kelompoks[6]->nama_kelompok.' (belum upload)');
    }

    private function dpl(string $name, string $email, string $nidn): DosenPembimbingLapangan
    {
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => 'password',
                'email_verified_at' => now(),
            ]
        );

        if (! $user->hasRole('pembimbing')) {
            $user->assignRole('pembimbing');
        }

        return DosenPembimbingLapangan::firstOrCreate(
            ['user_id' => $user->id],
            ['nidn' => $nidn, 'status' => 'aktif', 'jenis_kelamin' => 'laki_laki']
        );
    }

    private function seedKelompok(
        Gelombang $gelombang,
        KelompokKkn $kelompok,
        ?DosenPembimbingLapangan $dpl,
        string $status,
        ?string $komentar
    ): void {
        $kelompok->update([
            'dosen_pembimbing_lapangan_id' => $dpl?->id,
        ]);

        $this->detachWarfix($kelompok);
        $ketua = $this->attachPeserta($gelombang, $kelompok);

        $tugas = TugasKelompok::firstOrCreate(
            [
                'kelompok_kkn_id' => $kelompok->id,
                'nama_tugas' => TugasTemplate::DRAFT_ARTIKEL,
            ],
            [
                'kategori' => 'luaran_wajib',
                'is_wajib' => true,
            ]
        );

        $tugas->update(['kategori' => 'luaran_wajib', 'is_wajib' => true]);

        if (! $ketua) {
            $this->command?->warn('Tidak ada peserta untuk '.$kelompok->nama_kelompok.', submission dilewati.');

            return;
        }

        // Bersihkan submission lama milik peserta lain (mis. dari run seeder sebelumnya).
        TugasSubmission::where('tugas_kelompok_id', $tugas->id)
            ->where('peserta_kkn_id', '!=', $ketua->id)
            ->delete();

        $content = $this->pdfContent($kelompok->nama_kelompok);
        $path = 'tugas/dummy/draft-artikel-'.$kelompok->id.'.pdf';

        Storage::disk('public')->put($path, $content);

        TugasSubmission::updateOrCreate(
            [
                'tugas_kelompok_id' => $tugas->id,
                'peserta_kkn_id' => $ketua->id,
            ],
            [
                'judul' => 'Artikel KKN '.$kelompok->nama_kelompok,
                'deskripsi' => 'Data dummy untuk pengujian unduh Draft Artikel.',
                'file_path' => $path,
                'file_name' => 'Draft Artikel - '.$kelompok->nama_kelompok.'.pdf',
                'file_size' => strlen($content),
                'status' => $status,
                'komentar_dpl' => $komentar,
            ]
        );
    }

    /**
     * Peserta WAR yang sempat terpasang oleh versi seeder sebelumnya dilepas
     * kembali agar kelompok dummy hanya berisi peserta dummy.
     */
    private function detachWarfix(KelompokKkn $kelompok): void
    {
        PesertaKkn::where('kelompok_kkn_id', $kelompok->id)
            ->whereHas('mahasiswa.user', fn ($q) => $q->where('email', 'like', '%@warfix.local'))
            ->update(['kelompok_kkn_id' => null]);
    }

    private function attachPeserta(Gelombang $gelombang, KelompokKkn $kelompok): ?PesertaKkn
    {
        $prodiId = ProgramStudi::orderBy('id')->value('id');

        foreach ([1, 2] as $index) {
            $name = self::NAMA_PESERTA[($kelompok->id + $index) % count(self::NAMA_PESERTA)];
            $email = 'peserta.dummy.'.$kelompok->id.'.'.$index.'@kkn.test';

            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->hasRole('mahasiswa')) {
                $user->assignRole('mahasiswa');
            }

            $mahasiswa = Mahasiswa::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'npm' => 'DUM'.$kelompok->id.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                    'jenis_kelamin' => $index === 1 ? 'L' : 'P',
                    'prodi_id' => $prodiId,
                    'is_biodata_complete' => true,
                ]
            );

            $peserta = PesertaKkn::firstOrCreate(
                [
                    'mahasiswa_id' => $mahasiswa->user_id,
                    'gelombang_id' => $gelombang->id,
                ],
                [
                    'kelompok_kkn_id' => $kelompok->id,
                    'status_pendaftaran' => 'approved',
                    'submitted_at' => now(),
                ]
            );

            $peserta->update([
                'kelompok_kkn_id' => $kelompok->id,
                'status_pendaftaran' => 'approved',
            ]);
        }

        $ketua = $kelompok->pesertaKkn()->orderBy('id')->first();

        if ($ketua && (int) $kelompok->ketua_peserta_id !== (int) $ketua->id) {
            $kelompok->update(['ketua_peserta_id' => $ketua->id]);
        }

        return $ketua;
    }

    private function pdfContent(string $kelompok): string
    {
        return "%PDF-1.4\n"
            ."1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
            ."3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 300 200]/Contents 4 0 R/Resources<</Font<</F1 5 0 R>>>>>>endobj\n"
            ."4 0 obj<</Length 60>>stream\n"
            .'BT /F1 12 Tf 20 150 Td (Draft Artikel Dummy - '.$kelompok.') Tj ET'."\n"
            ."endstream\nendobj\n"
            ."5 0 obj<</Type/Font/Subtype/Type1/BaseFont/Helvetica>>endobj\n"
            ."trailer<</Root 1 0 R>>\n"
            ."%%EOF\n";
    }
}
