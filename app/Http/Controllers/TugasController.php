<?php
namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Models\TugasKelompok;
use App\Models\TugasSubmission;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class TugasController extends Controller
{
    public function create(): View
    {
        $mhs = auth()->user()->mahasiswa;
        abort_if(!$mhs, 404);
        $peserta = \App\Models\PesertaKkn::where('mahasiswa_id', $mhs->user_id)->whereNotNull('kelompok_kkn_id')->firstOrFail();
        $tugasList = TugasKelompok::where('kelompok_kkn_id', $peserta->kelompok_kkn_id)->get()->groupBy('kategori');

        return view('kelompok.tugas.submit', compact('tugasList'));
    }
    public function store(Request $request, KelompokKkn $kelompok): RedirectResponse
    {
        TugasKelompok::create([
            'kelompok_kkn_id' => $kelompok->id,
            'kategori' => $request->kategori ?? 'tugas_kelompok',
            'nama_tugas' => $request->nama_tugas,
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('kelompok.index', ['tab'=>'tugas'])->with('success','Tugas ditambahkan.');
    }

    public function destroy(TugasKelompok $tugas): RedirectResponse
    {
        foreach ($tugas->submissions as $sub) {
            Storage::disk('public')->delete($sub->file_path);
        }
        $tugas->delete();
        return back()->with('success','Tugas dihapus.');
    }

    public function submit(Request $request, TugasKelompok $tugas): RedirectResponse
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,doc,docx,zip,jpg,png|max:10240',
        ]);

        $mhs = auth()->user()->mahasiswa;
        $peserta = \App\Models\PesertaKkn::where('mahasiswa_id',$mhs->user_id)
            ->where('kelompok_kkn_id', $tugas->kelompok_kkn_id)->firstOrFail();

        $file = $request->file('file');
        $path = $file->store('tugas/'.$tugas->id, 'public');

        TugasSubmission::create([
            'tugas_kelompok_id' => $tugas->id,
            'peserta_kkn_id' => $peserta->id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
        ]);

        return redirect()->route('kelompok.index', ['tab'=>'tugas'])->with('success','Tugas dikumpulkan.');
    }

    public function review(Request $request, TugasSubmission $submission): RedirectResponse
    {
        $request->validate(['status'=>'required|in:diterima,ditolak,revisi']);

        $submission->update([
            'status' => $request->status,
            'komentar_dpl' => $request->komentar_dpl,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return back()->with('success','Tugas di-review.');
    }
}
