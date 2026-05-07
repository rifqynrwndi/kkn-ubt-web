<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\Kecamatan;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DesaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Desa::with('kecamatan');

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $query->where('nama_desa', 'like', '%' . $request->search . '%');
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kecamatan
        |--------------------------------------------------------------------------
        */
        if ($request->filled('kecamatan_id')) {
            $query->where('kecamatan_id', $request->kecamatan_id);
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */
        if ($request->filled('aktif')) {
            $query->where('aktif', $request->aktif);
        }

        $desa = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('desa.index', compact(
            'desa',
            'kecamatan'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('desa.create', compact('kecamatan'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'nama_desa' => 'required|string|max:255',
            'kontak_nama' => 'nullable|string|max:255',
            'kontak_hp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'aktif' => 'required|boolean',
        ]);

        Desa::create($validated);

        return redirect()
            ->route('desa.index')
            ->with('success', 'Data desa berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Desa $desa): View
    {
        $desa->load([
            'kecamatan',
            'desaGelombang.gelombang',
            'desaGelombang.dosenPembimbingLapangan.user',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Statistik Future Ready
        |--------------------------------------------------------------------------
        */
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        $jumlahGelombang = $desa->desaGelombang->count();

        $totalKuota = $desa->desaGelombang->sum('kuota_total');

        $desaGelombangAktif = $desa->desaGelombang
            ->where('status', 'dibuka');

        return view('desa.show', compact(
            'desa',
            'kecamatan',
            'jumlahGelombang',
            'totalKuota',
            'desaGelombangAktif'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Desa $desa): View
    {
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')->get();

        return view('desa.edit', compact(
            'desa',
            'kecamatan'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Desa $desa): RedirectResponse
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'nama_desa' => 'required|string|max:255',
            'kontak_nama' => 'nullable|string|max:255',
            'kontak_hp' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'aktif' => 'required|boolean',
        ]);

        $desa->update($validated);

        return redirect()
            ->route('desa.index')
            ->with('success', 'Data desa berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Desa $desa): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent Delete If Already Used
        |--------------------------------------------------------------------------
        */
        if ($desa->desaGelombang()->exists()) {
            return back()->with(
                'error',
                'Desa tidak dapat dihapus karena sudah digunakan pada gelombang.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Soft Disable Instead Hard Delete
        |--------------------------------------------------------------------------
        */
        $desa->update([
            'aktif' => false,
        ]);

        return redirect()
            ->route('desa.index')
            ->with('success', 'Desa berhasil dinonaktifkan.');
    }

    public function createKecamatan() : View
    {
        return view('kecamatan.create');
    }

    public function storeKecamatan(Request $request)
    {
        $request->validate([
            'nama_kecamatan' => 'required|unique:kecamatan,nama_kecamatan',
            'kabupaten' => 'required|string|max:255'
        ]);

        Kecamatan::create($request->only('nama_kecamatan', 'kabupaten'));

        return redirect()
            ->route('desa.index')
            ->with('success', 'Kecamatan ditambahkan');
    }

    public function editKecamatan(Kecamatan $kecamatan)
    {
        return view('desa.edit_kecamatan', compact('kecamatan'));
    }

    public function updateKecamatan(Request $request, Kecamatan $kecamatan)
    {
        $request->validate([
            'nama_kecamatan' => 'required|unique:kecamatan,nama_kecamatan,' . $kecamatan->id,
            'kabupaten' => 'required|string|max:255'
        ]);

        $kecamatan->update($request->only('nama_kecamatan', 'kabupaten'));

        return redirect()
            ->route('desa.index')
            ->with('success', 'Kecamatan diperbarui');
    }
}
