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
        $tugasList = TugasKelompok::with(['kelompokKkn.desaGelombang.desa', 'submissions'])
            ->latest()->paginate(20);
        $kelompoks = KelompokKkn::with('desaGelombang.desa')->orderBy('nama_kelompok')->get();

        return view('tugas-admin.index', compact('tugasList', 'kelompoks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate(['nama_tugas'=>'required|string|max:255','kategori'=>'required|in:tugas_kelompok,luaran_wajib,luaran_lain,laporan','kelompok_ids'=>'required|array']);

        foreach ($request->kelompok_ids as $kid) {
            TugasKelompok::firstOrCreate(['kelompok_kkn_id'=>$kid,'nama_tugas'=>$request->nama_tugas],['kategori'=>$request->kategori,'created_by'=>auth()->id()]);
        }

        return back()->with('success','Tugas berhasil ditambahkan ke '.count($request->kelompok_ids).' kelompok.');
    }

    public function destroy(TugasKelompok $tugas): RedirectResponse
    {
        foreach ($tugas->submissions as $sub) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($sub->file_path);
        }
        $tugas->delete();
        return back()->with('success','Tugas dihapus.');
    }
}
