<?php

namespace App\Http\Controllers;

use App\Models\Fakultas;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class FakultasProdiController extends Controller
{
    public function index()
    {
        $fakultas = Fakultas::with('prodi')->get();

        $prodi = ProgramStudi::with('fakultas')
            ->join('fakultas', 'program_studi.fakultas_id', '=', 'fakultas.id')
            ->select('program_studi.*')
            ->orderBy('fakultas.nama_fakultas')
            ->orderBy('program_studi.nama_prodi')
            ->get();

        return view('fakultasprodi.index', compact('prodi', 'fakultas'));
    }

    // FAKULTAS
    public function createFakultas()
    {
        return view('fakultasprodi.create_fakultas');
    }

    public function storeFakultas(Request $request)
    {
        $request->validate([
            'nama_fakultas' => 'required|unique:fakultas,nama_fakultas'
        ]);

        Fakultas::create($request->only('nama_fakultas'));

        return redirect()
            ->route('fakultas-prodi.index')
            ->with('success', 'Fakultas ditambahkan');
    }

    public function editFakultas(Fakultas $fakultas)
    {
        return view('fakultasprodi.edit_fakultas', compact('fakultas'));
    }

    public function updateFakultas(Request $request, Fakultas $fakultas)
    {
        $request->validate([
            'nama_fakultas' => 'required|unique:fakultas,nama_fakultas,' . $fakultas->id
        ]);

        $fakultas->update($request->only('nama_fakultas'));

        return redirect()
            ->route('fakultas-prodi.index')
            ->with('success', 'Fakultas ditambahkan');
    }

    public function deleteFakultas(Fakultas $fakultas)
    {
        if ($fakultas->programStudi()->exists()) {
            return back()->with('error', 'Fakultas tidak dapat dihapus karena masih memiliki program studi.');
        }

        $fakultas->delete();
        return back()->with('success', 'Fakultas berhasil dihapus.');
    }

    // PRODI
    public function createProdi()
    {
        $fakultas = Fakultas::all();
        return view('fakultasprodi.create_prodi', compact('fakultas'));
    }

    public function storeProdi(Request $request)
    {
        $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'nama_prodi' => 'required'
        ]);

        ProgramStudi::create($request->only('fakultas_id', 'nama_prodi'));

        return redirect()
            ->route('fakultas-prodi.index')
            ->with('success', 'Prodi ditambahkan');
    }

    public function editProdi(ProgramStudi $prodi)
    {
        $fakultas = Fakultas::all();
        return view('fakultasprodi.edit_prodi', compact('prodi', 'fakultas'));
    }

    public function updateProdi(Request $request, ProgramStudi $prodi)
    {
        $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'nama_prodi' => 'required'
        ]);

        $prodi->update([
            'fakultas_id' => $request->fakultas_id,
            'nama_prodi' => $request->nama_prodi,
        ]);

        return redirect()
            ->route('fakultas-prodi.index')
            ->with('success', 'Prodi diupdate');
    }

    public function deleteProdi(ProgramStudi $prodi)
    {
        if (\App\Models\Mahasiswa::where('prodi_id', $prodi->id)->exists()) {
            return back()->with('error', 'Prodi tidak dapat dihapus karena masih memiliki mahasiswa.');
        }

        $prodi->delete();
        return back()->with('success', 'Prodi berhasil dihapus.');
    }
}
