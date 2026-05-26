<?php

namespace App\Http\Controllers;

use App\Models\ProgramStudi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BiodataController extends Controller
{
    public function edit()
    {
        abort_if(! auth()->user()->hasRole('mahasiswa'), 403, 'Halaman ini hanya untuk mahasiswa.');

        $mahasiswa = auth()->user()->mahasiswa;
        $prodis = ProgramStudi::all();

        return view('biodata.edit', compact('mahasiswa', 'prodis'));
    }

    public function update(Request $request)
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $request->validate([
            'npm' => 'required|string|max:20|unique:mahasiswa,npm,' . $mahasiswa->user_id . ',user_id',
            'jenis_kelamin' => 'required|in:L,P',
            'no_hp' => 'required|string|max:20',

            'prodi_id' => 'required|exists:program_studi,id',

            'nama_ortu' => 'required|string|max:255',
            'no_hp_ortu' => 'required|string|max:20',
            'alamat_ortu' => 'required|string',

            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Handle Upload Foto
        if ($request->hasFile('foto')) {

            if ($mahasiswa->foto && Storage::disk('public')->exists($mahasiswa->foto)) {
                Storage::disk('public')->delete($mahasiswa->foto);
            }

            $fotoPath = $request->file('foto')->store('foto-mahasiswa', 'public');
        } else {
            $fotoPath = $mahasiswa->foto;
        }

        $mahasiswa->update([
            'npm' => $request->npm,
            'jenis_kelamin' => $request->jenis_kelamin,
            'no_hp' => $request->no_hp,
            'prodi_id' => $request->prodi_id,

            'nama_ortu' => $request->nama_ortu,
            'no_hp_ortu' => $request->no_hp_ortu,
            'alamat_ortu' => $request->alamat_ortu,

            'foto' => $fotoPath,

            'is_biodata_complete' => true,
        ]);

        return redirect()
            ->route('home')
            ->with('success', 'Biodata berhasil dilengkapi.');
    }
}
