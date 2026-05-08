<?php

namespace App\Http\Controllers;

use App\Models\Desa;
use App\Models\DesaGelombang;
use App\Models\Gelombang;
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
        $query = Desa::with([
            'kecamatan',
            'desaGelombang.gelombang',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */
        if ($request->filled('search')) {
            $query->where(
                'nama_desa',
                'like',
                '%' . $request->search . '%'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Kecamatan
        |--------------------------------------------------------------------------
        */
        if ($request->filled('kecamatan_id')) {
            $query->where(
                'kecamatan_id',
                $request->kecamatan_id
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Filter Status
        |--------------------------------------------------------------------------
        */
        if ($request->filled('aktif')) {
            $query->where(
                'aktif',
                $request->aktif
            );
        }

        $desa = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $kecamatan = Kecamatan::orderBy('nama_kecamatan')
            ->get();

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
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')
            ->get();

        $gelombang = Gelombang::whereIn(
                'status',
                ['persiapan', 'pendaftaran', 'berjalan', 'selesai']
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return view('desa.create', compact(
            'kecamatan',
            'gelombang'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'nama_desa' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'aktif' => 'required|boolean',

            'gelombang_id' => 'nullable|exists:gelombang,id',
            'kuota_total' => 'nullable|integer|min:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Create Desa
        |--------------------------------------------------------------------------
        */
        $desa = Desa::create([
            'kecamatan_id' => $validated['kecamatan_id'],
            'nama_desa' => $validated['nama_desa'],
            'alamat' => $validated['alamat'] ?? null,
            'aktif' => $validated['aktif'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Auto Create Desa Gelombang
        |--------------------------------------------------------------------------
        */
        if ($request->filled('gelombang_id')) {

            DesaGelombang::create([
                'desa_id' => $desa->id,
                'gelombang_id' => $validated['gelombang_id'],
                'kuota_total' => $validated['kuota_total'] ?? 12,
                'status' => 'draft',
            ]);
        }

        return redirect()
            ->route('desa.index')
            ->with(
                'success',
                'Data desa berhasil ditambahkan.'
            );
    }

    /**
     * Display the specified resource.
     */
    public function show(Desa $desa): View
    {
        $desa->load([
            'kecamatan',
            'desaGelombang.gelombang',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Statistik
        |--------------------------------------------------------------------------
        */
        $jumlahGelombang = $desa->desaGelombang->count();

        $totalKuota = $desa->desaGelombang
            ->sum('kuota_total');

        $desaGelombangAktif = $desa->desaGelombang
                ->whereIn(
                    'status',
                    ['draft', 'dibuka', 'penuh']
                );

        return view('desa.show', compact(
            'desa',
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
        $kecamatan = Kecamatan::orderBy('nama_kecamatan')
            ->get();

        $gelombang = Gelombang::whereIn(
                'status',
                ['persiapan', 'pendaftaran', 'berjalan', 'selesai']
            )
            ->orderBy('created_at', 'desc')
            ->get();

        $desaGelombang = $desa->desaGelombang()
            ->first();

        return view('desa.edit', compact(
            'desa',
            'kecamatan',
            'gelombang',
            'desaGelombang'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        Request $request,
        Desa $desa
    ): RedirectResponse {

        $validated = $request->validate([
            'kecamatan_id' => 'required|exists:kecamatan,id',
            'nama_desa' => 'required|string|max:255',
            'alamat' => 'nullable|string',
            'aktif' => 'required|boolean',

            'gelombang_id' => 'nullable|exists:gelombang,id',
            'kuota_total' => 'nullable|integer|min:1',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update Desa
        |--------------------------------------------------------------------------
        */
        $desa->update([
            'kecamatan_id' => $validated['kecamatan_id'],
            'nama_desa' => $validated['nama_desa'],
            'alamat' => $validated['alamat'] ?? null,
            'aktif' => $validated['aktif'],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Sync Desa Gelombang
        |--------------------------------------------------------------------------
        */
        if ($request->filled('gelombang_id')) {

            DesaGelombang::updateOrCreate(
                [
                    'desa_id' => $desa->id,
                    'gelombang_id' => $validated['gelombang_id'],
                ],
                [
                    'kuota_total' => $validated['kuota_total'] ?? 12,
                    'status' => 'draft',
                ]
            );
        }

        return redirect()
            ->route('desa.index')
            ->with(
                'success',
                'Data desa berhasil diperbarui.'
            );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Desa $desa): RedirectResponse
    {
        /*
        |--------------------------------------------------------------------------
        | Prevent Delete If Used
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
        | Soft Disable
        |--------------------------------------------------------------------------
        */
        $desa->update([
            'aktif' => false,
        ]);

        return redirect()
            ->route('desa.index')
            ->with(
                'success',
                'Desa berhasil dinonaktifkan.'
            );
    }

    /**
     * Form Create Kecamatan
     */
    public function createKecamatan(): View
    {
        return view('kecamatan.create');
    }

    /**
     * Store Kecamatan
     */
    public function storeKecamatan(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_kecamatan' => 'required|unique:kecamatan,nama_kecamatan',
            'kabupaten' => 'required|string|max:255',
        ]);

        Kecamatan::create(
            $request->only(
                'nama_kecamatan',
                'kabupaten'
            )
        );

        return redirect()
            ->route('desa.index')
            ->with(
                'success',
                'Kecamatan berhasil ditambahkan.'
            );
    }

    /**
     * Form Edit Kecamatan
     */
    public function editKecamatan(
        Kecamatan $kecamatan
    ): View {

        return view(
            'desa.edit_kecamatan',
            compact('kecamatan')
        );
    }

    /**
     * Update Kecamatan
     */
    public function updateKecamatan(
        Request $request,
        Kecamatan $kecamatan
    ): RedirectResponse {

        $request->validate([
            'nama_kecamatan' =>
                'required|unique:kecamatan,nama_kecamatan,' . $kecamatan->id,

            'kabupaten' =>
                'required|string|max:255',
        ]);

        $kecamatan->update(
            $request->only(
                'nama_kecamatan',
                'kabupaten'
            )
        );

        return redirect()
            ->route('desa.index')
            ->with(
                'success',
                'Kecamatan berhasil diperbarui.'
            );
    }

    public function destroyKecamatan($id)
    {
        $kecamatan = Kecamatan::findOrFail($id);

        /*
        |--------------------------------------------------------------------------
        | Prevent Delete If Used
        |--------------------------------------------------------------------------
        */

        if ($kecamatan->desa()->exists()) {

            return back()->with(
                'error',
                'Kecamatan tidak dapat dihapus karena masih memiliki desa.'
            );

        }

        $kecamatan->delete();

        return back()->with(
            'success',
            'Kecamatan berhasil dihapus.'
        );
    }
}
