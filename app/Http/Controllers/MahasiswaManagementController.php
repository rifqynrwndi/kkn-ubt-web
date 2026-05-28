<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class MahasiswaManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('mahasiswa.pesertaKkn.gelombang')
            ->role('mahasiswa')
            ->whereHas('mahasiswa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhereHas('mahasiswa', fn($mq) =>
                      $mq->where('npm', 'like', "%{$search}%")
                  );
            });
        }

        if ($request->filled('status')) {
            if ($request->status == 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->status == 'unverified') {
                $query->whereNull('email_verified_at');
            } elseif ($request->status == 'biodata_incomplete') {
                $query->whereHas('mahasiswa', fn($q) => $q->where('is_biodata_complete', false));
            } elseif ($request->status == 'no_photo') {
                $query->whereHas('mahasiswa', fn($q) => $q->where('is_biodata_complete', false)->orWhereNull('foto'));
            }
        }

        if ($request->ajax()) {
            $mahasiswas = $query->latest()->paginate(10);
            return view('mahasiswa.partials.table', compact('mahasiswas'))->render();
        }

        $mahasiswas = $query->latest()->paginate(10)->withQueryString();

        return view('mahasiswa.index', compact('mahasiswas'));
    }

    public function export(Request $request)
    {
        $query = User::with('mahasiswa.prodi.fakultas')
            ->role('mahasiswa')
            ->whereHas('mahasiswa');

        if ($request->filled('gelombang_id')) {
            $query->whereHas('mahasiswa.pesertaKkn', fn($q) => $q->where('gelombang_id', $request->gelombang_id));
        }

        $users = $query->orderBy('name')->get();

        $filename = 'data-mahasiswa-' . date('YmdHis') . '.csv';
        $headers = ['Content-Type'=>'text/csv','Content-Disposition'=>"attachment; filename=\"{$filename}\""];

        $callback = function() use ($users) {
            $f = fopen('php://output','w');
            fputcsv($f, ['No','Nama Lengkap','NPM','Email','HP','Status','Jenis Kelamin','Fakultas','Prodi','Nama Ortu','HP Ortu','Alamat Ortu']);
            foreach ($users as $i => $u) {
                $m = $u->mahasiswa;
                fputcsv($f, [
                    $i+1,
                    $u->name,
                    $m->npm ?? '-',
                    $u->email,
                    $m->no_hp ?? '-',
                    $u->email_verified_at ? 'Verified' : 'Unverified',
                    $m->jenis_kelamin === 'L' ? 'Laki-laki' : ($m->jenis_kelamin === 'P' ? 'Perempuan' : '-'),
                    $m->prodi->fakultas->nama_fakultas ?? '-',
                    $m->prodi->nama_prodi ?? '-',
                    $m->nama_ortu ?? '-',
                    $m->no_hp_ortu ?? '-',
                    $m->alamat_ortu ?? '-',
                ]);
            }
            fclose($f);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function create()
    {
        return view('mahasiswa.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'npm' => 'required|string|max:20|unique:mahasiswa,npm',
            'jenis_kelamin' => 'required|in:L,P',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'email_verified_at' => now(),
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole('mahasiswa');

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => $request->npm,
            'jenis_kelamin' => $request->jenis_kelamin,
            'is_biodata_complete' => false,
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function show($id)
    {
        $mahasiswa = User::with('mahasiswa.prodi')
            ->role('mahasiswa')
            ->findOrFail($id);

        return view('mahasiswa.show', compact('mahasiswa'));
    }

    public function edit($id)
    {
        $mahasiswa = User::with('mahasiswa.prodi')
            ->role('mahasiswa')
            ->findOrFail($id);

        $prodis = ProgramStudi::all();

        return view('mahasiswa.edit', compact('mahasiswa', 'prodis'));
    }

    public function update(Request $request, $id)
    {
        $user = User::with('mahasiswa')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,

            'npm' => 'required|string|max:20|unique:mahasiswa,npm,' . $user->id . ',user_id',
            'jenis_kelamin' => 'required|in:L,P',
            'prodi_id' => 'required|exists:program_studi,id',

            'no_hp' => 'nullable|string|max:20',
            'nama_ortu' => 'nullable|string|max:255',
            'no_hp_ortu' => 'nullable|string|max:20',
            'alamat_ortu' => 'nullable|string',

            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',

            'password' => 'nullable|confirmed|min:8',
        ]);

        // Upload Foto
        $fotoPath = $user->mahasiswa->foto;

        if ($request->hasFile('foto')) {
            if ($fotoPath && Storage::disk('public')->exists($fotoPath)) {
                Storage::disk('public')->delete($fotoPath);
            }

            $fotoPath = $request->file('foto')->store('foto-mahasiswa', 'public');
        }

        // Update users table
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            ...( $request->password ? [
                'password' => Hash::make($request->password)
            ] : [] ),
        ]);

        // Update mahasiswa table
        $user->mahasiswa->update([
            'npm' => $request->npm,
            'jenis_kelamin' => $request->jenis_kelamin,
            'prodi_id' => $request->prodi_id,
            'no_hp' => $request->no_hp,
            'nama_ortu' => $request->nama_ortu,
            'no_hp_ortu' => $request->no_hp_ortu,
            'alamat_ortu' => $request->alamat_ortu,
            'foto' => $fotoPath,
        ]);

        return redirect()
            ->route('mahasiswa.index')
            ->with('success', 'Data mahasiswa berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $user = User::with('mahasiswa')->findOrFail($id);

        if ($user->mahasiswa) {
            $pesertaKkn = \App\Models\PesertaKkn::where('mahasiswa_id', $user->id)->first();

            if ($pesertaKkn?->kelompok_kkn_id) {
                return back()->with('error', 'Mahasiswa tidak dapat dihapus karena masih terdaftar di kelompok KKN.');
            }

            \App\Models\PesertaKkn::where('mahasiswa_id', $user->id)->delete();
            \App\Models\WarParticipant::where('peserta_kkn_id', $pesertaKkn?->id)->delete();

            $user->mahasiswa->delete();
        }

        $user->roles()->detach();

        $user->delete();

        return back()->with(
            'success',
            'Mahasiswa berhasil dihapus.'
        );
    }
}
