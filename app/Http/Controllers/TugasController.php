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
        abort_if($peserta->id !== $peserta->kelompokKkn->ketua_peserta_id, 403, 'Hanya ketua yang dapat mengumpulkan tugas.');
        $tugasList = TugasKelompok::where('kelompok_kkn_id', $peserta->kelompok_kkn_id)
            ->with(['submissions' => fn($q) => $q->where('peserta_kkn_id', $peserta->id)->whereIn('status', ['menunggu', 'diterima'])])
            ->get()
            ->filter(fn($t) => $t->submissions->isEmpty())
            ->groupBy('kategori');

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
        \Log::info('Tugas submit attempt', ['user'=>auth()->id(), 'tugas_id'=>$tugas->id, 'file'=>$request->hasFile('file')]);
        
        $request->validate([
            'judul' => 'required|string|max:255',
            'file' => 'required|file|max:10240',
        ]);

        $mhs = auth()->user()->mahasiswa;
        $peserta = \App\Models\PesertaKkn::where('mahasiswa_id',$mhs->user_id)
            ->where('kelompok_kkn_id', $tugas->kelompok_kkn_id)->firstOrFail();
        abort_if($peserta->id !== $tugas->kelompokKkn->ketua_peserta_id, 403, 'Hanya ketua yang dapat mengumpulkan tugas.');

        $file = $request->file('file');
        $path = $file->store('tugas/'.$tugas->id, 'public');

        \Log::info('Tugas submitted', ['path'=>$path, 'peserta'=>$peserta->id]);

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
        $request->validate([
            'status'=>'required|in:diterima,ditolak,revisi',
        ]);

        $updateData = [
            'status' => $request->status,
            'komentar_dpl' => $request->komentar_dpl,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ];

        if ($request->user()?->hasRole('superadmin')) {
            if ($request->status === 'diterima') {
                $updateData['nilai'] = 100;
            } elseif ($request->status === 'ditolak') {
                $updateData['nilai'] = null;
            }
        }

        $submission->update($updateData);

        return back()->with('success','Tugas di-review.');
    }

    public function destroySubmission(TugasSubmission $submission): RedirectResponse
    {
        $mhs = auth()->user()->mahasiswa;
        abort_if(!$mhs, 404);
        $peserta = \App\Models\PesertaKkn::where('mahasiswa_id', $mhs->user_id)
            ->where('kelompok_kkn_id', $submission->tugasKelompok->kelompok_kkn_id)->firstOrFail();
        abort_if($peserta->id !== $submission->peserta_kkn_id, 403);
        abort_if($submission->status !== 'menunggu', 403, 'Hanya submission berstatus menunggu yang dapat dihapus.');

        if ($submission->file_path) {
            Storage::disk('public')->delete($submission->file_path);
        }
        $submission->delete();

        return redirect()->route('kelompok.index', ['tab' => 'tugas'])->with('success', 'Tugas dihapus.');
    }
}
