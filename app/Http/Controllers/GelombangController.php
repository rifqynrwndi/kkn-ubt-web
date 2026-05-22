<?php

namespace App\Http\Controllers;

use App\Models\Gelombang;
use Illuminate\Http\Request;

class GelombangController extends Controller
{
    public function index(Request $request)
    {
        $gelombang = Gelombang::withCount([
                'pesertaKkn as total_peserta',
            ])
            ->withCount([
                'pesertaKkn as total_pria' => fn($q) => $q->whereHas('mahasiswa', fn($q) => $q->where('jenis_kelamin', 'L')),
            ])
            ->withCount([
                'pesertaKkn as total_wanita' => fn($q) => $q->whereHas('mahasiswa', fn($q) => $q->where('jenis_kelamin', 'P')),
            ])
            ->when($request->search, function ($q) use ($request) {
                $q->where('nama_gelombang', 'like', '%' . $request->search . '%')
                  ->orWhere('tahun', 'like', '%' . $request->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('gelombang.index', compact('gelombang'));
    }

    public function show(Gelombang $gelombang)
    {
        return view('gelombang.show', compact('gelombang'));
    }

    public function create()
    {
        return view('gelombang.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'tahun' => 'required|integer',
            'tgl_mulai' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_mulai',

            'kuota_laki' => 'nullable|integer|min:0',
            'kuota_perempuan' => 'nullable|integer|min:0',

            'status' => 'required|in:persiapan,pendaftaran,berjalan,selesai',
        ]);

        $validated['kuota_total'] =
            ($validated['kuota_laki'] ?? 0) +
            ($validated['kuota_perempuan'] ?? 0);

        Gelombang::create($validated);

        return redirect()
            ->route('gelombang.index')
            ->with('success', 'Gelombang berhasil ditambahkan.');
    }

    public function edit(Gelombang $gelombang)
    {
        return view('gelombang.edit', compact('gelombang'));
    }

    public function update(Request $request, Gelombang $gelombang)
    {
        $validated = $request->validate([
            'nama_gelombang' => 'required|string|max:255',
            'tahun' => 'required|integer',
            'tgl_mulai' => 'required|date',
            'tgl_akhir' => 'required|date|after_or_equal:tgl_mulai',

            'kuota_laki' => 'nullable|integer|min:0',
            'kuota_perempuan' => 'nullable|integer|min:0',

            'status' => 'required|in:persiapan,pendaftaran,berjalan,selesai',
        ]);

        $validated['kuota_total'] =
            ($validated['kuota_laki'] ?? 0) +
            ($validated['kuota_perempuan'] ?? 0);

        $gelombang->update($validated);

        return redirect()
            ->route('gelombang.index')
            ->with('success', 'Gelombang berhasil diperbarui.');
    }

    public function destroy(Gelombang $gelombang)
    {
        if ($gelombang->pesertaKkn()->exists()) {
            return back()->with('error', 'Gelombang tidak dapat dihapus karena sudah memiliki peserta.');
        }

        $gelombang->delete();

        return back()->with('success', 'Gelombang berhasil dihapus.');
    }
}
