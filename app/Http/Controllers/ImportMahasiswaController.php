<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use App\Models\Mahasiswa;
use App\Models\PesertaKkn;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ImportMahasiswaController extends Controller
{
    public function index(): View
    {
        $gelombangs = Gelombang::orderBy('tahun', 'desc')->get();
        return view('import-mahasiswa.index', compact('gelombangs'));
    }

    public function preview(Request $request): View|RedirectResponse
    {
        $request->validate([
            'file'       => 'required|file|mimes:csv,txt',
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
                $prodiId = \App\Models\ProgramStudi::where('nama_prodi', 'like', '%' . $row['prodi_text'] . '%')->value('id');
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
}
