<?php
namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\TugasKelompok;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TugasAdminController extends Controller
{
    public function index(): View
    {
        $tugasList = TugasKelompok::with('submissions')->get()
            ->groupBy('nama_tugas')
            ->map(fn($group) => [
                'nama_tugas' => $group->first()->nama_tugas,
                'kategori' => $group->first()->kategori,
                'total_kelompok' => $group->count(),
                'total_submissions' => $group->sum(fn($t) => $t->submissions->count()),
                'ids' => $group->pluck('id')->toArray(),
                'first_id' => $group->first()->id,
            ])
            ->sortBy('kategori')
            ->values();

        return view('tugas-admin.index', compact('tugasList'));
    }

    public function create(): View
    {
        $kelompoks = KelompokKkn::with('desaGelombang.desa.kecamatan')->orderBy('nama_kelompok')->get();
        return view('tugas-admin.create', compact('kelompoks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_tugas' => 'required|string|max:255',
            'kategori' => 'required|in:tugas_kelompok,luaran_wajib,luaran_lain,laporan',
            'kelompok_ids' => 'required|array',
        ]);

        $count = 0;
        foreach ($request->kelompok_ids as $kid) {
            TugasKelompok::firstOrCreate(
                ['kelompok_kkn_id' => $kid, 'nama_tugas' => $request->nama_tugas],
                ['kategori' => $request->kategori, 'created_by' => auth()->id()]
            );
            $count++;
        }

        return redirect()->route('admin.tugas.index')
            ->with('success', "Tugas \"{$request->nama_tugas}\" berhasil ditambahkan ke {$count} kelompok.");
    }

    public function destroyByNama(Request $request): RedirectResponse
    {
        $nama = $request->input('nama_tugas');
        $tugasGroup = TugasKelompok::where('nama_tugas', $nama)->get();
        $count = 0;
        foreach ($tugasGroup as $t) {
            foreach ($t->submissions as $sub) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($sub->file_path);
            }
            $t->delete();
            $count++;
        }
        return back()->with('success', "Tugas \"{$nama}\" dihapus dari {$count} kelompok.");
    }
}
