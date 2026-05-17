<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\ProgramStudi;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ImportMahasiswaController extends Controller
{
    public function index(): View
    {
        $gelombangs = Gelombang::orderBy('tahun', 'desc')->get();
        $hasOldConnection = $this->canConnectToOldDb();
        return view('import-mahasiswa.index', compact('gelombangs', 'hasOldConnection'));
    }

    /*
    |--------------------------------------------------------------------------
    | CSV IMPORT
    |--------------------------------------------------------------------------
    */

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file'         => 'required|file|mimes:csv,txt',
            'gelombang_id' => 'required|exists:gelombang,id',
        ]);

        $gelombang = Gelombang::findOrFail($request->gelombang_id);
        $file = $request->file('file');
        $path = $file->getRealPath();

        $handle = fopen($path, 'r');

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);
            return back()->with('error', 'File CSV kosong atau tidak valid.');
        }

        $header = array_map('trim', $header);
        $header = array_map('strtolower', $header);

        $rows = [];
        $errors = [];
        $rowNum = 1;

        while (($line = fgetcsv($handle)) !== false) {
            $rowNum++;
            if (count($line) < count($header)) continue;
            if (empty(array_filter($line))) continue;

            $data = array_combine($header, $line);

            $name  = trim($data['name'] ?? $data['nama'] ?? '');
            $email = trim($data['email'] ?? '');
            $npm   = trim($data['npm'] ?? '');
            $gender = strtoupper(trim($data['jenis_kelamin'] ?? $data['gender'] ?? 'L'));
            $prodi = trim($data['prodi_id'] ?? $data['nama_prodi'] ?? $data['prodi'] ?? '');

            if (empty($name)) { $errors[] = "Baris {$rowNum}: Nama kosong."; continue; }
            if (empty($email)) { $errors[] = "Baris {$rowNum}: Email kosong."; continue; }
            if (empty($npm)) { $errors[] = "Baris {$rowNum}: NPM kosong."; continue; }
            if (! in_array($gender, ['L', 'P'])) { $errors[] = "Baris {$rowNum}: Gender tidak valid ({$gender})."; continue; }

            $userExists = User::where('email', $email)->exists();
            $npmExists  = Mahasiswa::where('npm', $npm)->exists();

            $rows[] = [
                'name'      => $name,
                'email'     => $email,
                'npm'       => $npm,
                'gender'    => $gender,
                'prodi_id'  => is_numeric($prodi) ? (int) $prodi : null,
                'prodi_text'=> is_numeric($prodi) ? null : $prodi,
                'exists'    => $userExists || $npmExists ? 'Sudah Ada' : 'Baru',
            ];
        }

        fclose($handle);

        $totalRows = count($rows);
        $newRows   = count(array_filter($rows, fn($r) => $r['exists'] === 'Baru'));

        return view('import-mahasiswa.preview', compact(
            'rows', 'errors', 'gelombang', 'totalRows', 'newRows'
        ));
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate([
            'gelombang_id' => 'required|exists:gelombang,id',
            'data'         => 'required|json',
        ]);

        $gelombang = Gelombang::findOrFail($request->gelombang_id);
        $records = json_decode($request->data, true);

        if (! is_array($records)) {
            return back()->with('error', 'Data tidak valid.');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($records as $row) {
            if (User::where('email', $row['email'])->exists()) {
                $skipped++;
                continue;
            }
            if (Mahasiswa::where('npm', $row['npm'])->exists()) {
                $skipped++;
                continue;
            }

            $password = Hash::make($row['npm'] ?? 'kknubt2026');

            $user = User::create([
                'name'              => $row['name'],
                'email'             => $row['email'],
                'password'          => $password,
                'email_verified_at' => now(),
            ]);
            $user->assignRole('mahasiswa');

            $prodiId = $row['prodi_id'] ?? null;
            if (! $prodiId && ! empty($row['prodi_text'])) {
                $prodiId = ProgramStudi::where('nama_prodi', 'like', '%' . $row['prodi_text'] . '%')->value('id');
            }

            Mahasiswa::create([
                'user_id'       => $user->id,
                'npm'           => $row['npm'],
                'jenis_kelamin' => $row['gender'],
                'prodi_id'      => $prodiId,
                'is_biodata_complete' => false,
            ]);

            PesertaKkn::create([
                'mahasiswa_id'        => $user->id,
                'gelombang_id'        => $gelombang->id,
                'status_pendaftaran'  => 'approved',
                'submitted_at'        => now(),
            ]);

            $imported++;
        }

        return redirect()
            ->route('import-mahasiswa.index')
            ->with('success', "Berhasil import {$imported} mahasiswa ke gelombang {$gelombang->nama_gelombang}. Dilewati: {$skipped}.");
    }

    /*
    |--------------------------------------------------------------------------
    | SQL IMPORT — dari database lama via koneksi `old_mysql`
    |--------------------------------------------------------------------------
    */

    public function sqlImport(): View
    {
        $gelombangs = Gelombang::orderBy('tahun', 'desc')->get();
        $hasOldConnection = $this->canConnectToOldDb();

        return view('import-mahasiswa.sql-import', compact('gelombangs', 'hasOldConnection'));
    }

    public function sqlPreview(Request $request): View|RedirectResponse
    {
        if (! $this->canConnectToOldDb()) {
            return back()->with('error', 'Tidak dapat terhubung ke database lama. Pastikan koneksi old_mysql sudah dikonfigurasi di .env.');
        }

        $request->validate([
            'gelombang_id' => 'required|exists:gelombang,id',
        ]);

        $gelombang = Gelombang::findOrFail($request->gelombang_id);

        $oldUsers = DB::connection('old_mysql')
            ->table('users')
            ->get();

        $fakultasMap  = $this->fakultasMap();
        $prodiMap     = $this->prodiMap();

        $rows = [];
        $errors = [];

        foreach ($oldUsers as $old) {
            $name  = $old->nama ?? $old->name ?? 'Unknown';
            $email = $old->email ?? null;
            $npm   = $old->npm ?? $old->username ?? null;
            $roleId = $old->role ?? null;

            if (! $email) continue;
            if ($roleId == 4) continue; // skip admin

            $userExists = User::where('email', $email)->exists();
            $npmExists  = $npm && Mahasiswa::where('npm', $npm)->exists();

            $oldMahasiswa = null;
            $prodiId = null;
            $gender = null;

            if ($npm) {
                $oldMahasiswa = DB::connection('old_mysql')
                    ->table('mahasiswa')
                    ->where('user_id', $old->id)
                    ->first();

                if ($oldMahasiswa) {
                    $gender = match ((int) ($oldMahasiswa->jenis_kelamin ?? 0)) {
                        1 => 'L',
                        2 => 'P',
                        default => null,
                    };

                    $oldProdiId = $oldMahasiswa->prodi ?? null;
                    if ($oldProdiId && isset($prodiMap[$oldProdiId])) {
                        $mapped = $prodiMap[$oldProdiId];
                        $prodiId = ProgramStudi::find($mapped) ? $mapped : null;
                    }
                }
            }

            $rows[] = [
                'name'        => $name,
                'email'       => $email,
                'npm'         => $npm ?? '-',
                'gender'      => $gender,
                'prodi_id'    => $prodiId,
                'old_prodi'   => $oldMahasiswa->prodi ?? null,
                'old_fak'     => $oldMahasiswa->fakultas ?? null,
                'exists'      => $userExists || $npmExists ? 'Sudah Ada' : 'Baru',
            ];
        }

        $totalRows = count($rows);
        $newRows   = count(array_filter($rows, fn($r) => $r['exists'] === 'Baru' && $r['email']));

        return view('import-mahasiswa.sql-preview', compact(
            'rows', 'errors', 'gelombang', 'totalRows', 'newRows'
        ));
    }

    public function sqlRun(Request $request): RedirectResponse
    {
        if (! $this->canConnectToOldDb()) {
            return back()->with('error', 'Tidak dapat terhubung ke database lama.');
        }

        $request->validate([
            'gelombang_id' => 'required|exists:gelombang,id',
            'data'         => 'required|json',
        ]);

        $gelombang = Gelombang::findOrFail($request->gelombang_id);
        $records = json_decode($request->data, true);

        if (! is_array($records)) {
            return back()->with('error', 'Data tidak valid.');
        }

        $imported = 0;
        $skipped  = 0;

        foreach ($records as $row) {
            if (empty($row['email'])) continue;
            if (User::where('email', $row['email'])->exists()) {
                $skipped++;
                continue;
            }
            if (! empty($row['npm']) && Mahasiswa::where('npm', $row['npm'])->exists()) {
                $skipped++;
                continue;
            }

            $user = User::create([
                'name'              => $row['name'],
                'email'             => $row['email'],
                'password'          => Hash::make($row['npm'] ?: 'kknubt2026'),
                'email_verified_at' => now(),
            ]);
            $user->assignRole('mahasiswa');

            Mahasiswa::create([
                'user_id'       => $user->id,
                'npm'           => $row['npm'] ?? null,
                'jenis_kelamin' => $row['gender'] ?? null,
                'prodi_id'      => $row['prodi_id'] ?? null,
                'is_biodata_complete' => ! empty($row['gender']),
            ]);

            PesertaKkn::create([
                'mahasiswa_id'        => $user->id,
                'gelombang_id'        => $gelombang->id,
                'status_pendaftaran'  => 'approved',
                'submitted_at'        => now(),
            ]);

            $imported++;
        }

        return redirect()
            ->route('import-mahasiswa.index')
            ->with('success', "Berhasil import {$imported} mahasiswa dari DB lama ke gelombang {$gelombang->nama_gelombang}. Dilewati: {$skipped}.");
    }

    /*
    |--------------------------------------------------------------------------
    | HELPERS
    |--------------------------------------------------------------------------
    */

    private function canConnectToOldDb(): bool
    {
        try {
            DB::connection('old_mysql')->getPdo();
            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function fakultasMap(): array
    {
        return [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5, 6 => 6, 7 => 7];
    }

    private function prodiMap(): array
    {
        return [
            1  => 15, 2  => 17, 3  => 18, 4  => 16,
            15 => 13, 16 => 14,
            5  => 10, 6  => 11, 7  => 12,
            8  => 19,
            9  => 6,  10 => 7,  11 => 8,  12 => 9,
            13 => 4,  14 => 5,
            17 => 2,  18 => 1,  19 => 3,
            20 => 21, 21 => 20, 23 => 20,
        ];
    }
}
