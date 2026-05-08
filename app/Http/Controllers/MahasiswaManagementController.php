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
        $query = User::role('mahasiswa')->with('mahasiswa');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', function ($mq) use ($request) {
                      $mq->where('npm', 'like', "%{$request->search}%");
                  });
            });
        }

        $mahasiswas = $query->paginate(10);

        return view('mahasiswa.index', compact('mahasiswas'));
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
