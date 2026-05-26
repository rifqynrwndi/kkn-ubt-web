<?php
namespace App\Http\Controllers;

use App\Models\LogBook;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LogBookController extends Controller
{
    private function getKelompokId()
    {
        $mhs = auth()->user()->mahasiswa;
        if (!$mhs) return null;
        return \App\Models\PesertaKkn::where('mahasiswa_id', $mhs->user_id)->whereNotNull('kelompok_kkn_id')->value('kelompok_kkn_id');
    }

    public function create(): View
    {
        abort_if(!$this->getKelompokId(), 404);
        return view('kelompok.logbook.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $kelompokId = $this->getKelompokId();
        abort_if(!$kelompokId, 404);

        $peserta = \App\Models\PesertaKkn::where('mahasiswa_id', auth()->user()->mahasiswa->user_id)
            ->where('kelompok_kkn_id', $kelompokId)->firstOrFail();

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:50|max:2000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = [
            'peserta_kkn_id' => $peserta->id,
            'kelompok_kkn_id' => $kelompokId,
            'tanggal' => $request->tanggal,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data['file_path'] = $file->store('logbook', 'public');
            $data['file_name'] = $file->getClientOriginalName();
        }

        LogBook::create($data);

        return redirect()->route('kelompok.index', ['tab'=>'logbook'])->with('success', 'Log book berhasil ditambahkan.');
    }

    public function update(Request $request, LogBook $logbook): RedirectResponse
    {
        abort_if($logbook->is_validated, 403, 'Log book yang sudah divalidasi tidak dapat diedit.');

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:50|max:2000',
        ]);

        $logbook->update($request->only(['tanggal','judul','deskripsi']));

        return back()->with('success', 'Log book diperbarui.');
    }

    public function destroy(LogBook $logbook): RedirectResponse
    {
        if ($logbook->is_validated) {
            return back()->with('error', 'Log book yang sudah divalidasi tidak dapat dihapus.');
        }
        if ($logbook->file_path) Storage::disk('public')->delete($logbook->file_path);
        $logbook->delete();
        return back()->with('success', 'Log book dihapus.');
    }

    public function validateAll(Request $request): RedirectResponse
    {
        $kelompokId = $this->getKelompokId();
        abort_if(!$kelompokId, 404);

        $dpl = auth()->user()->dosenPembimbingLapangan;
        abort_if(!$dpl, 403);

        $pesertaId = $request->peserta_id;

        LogBook::where('peserta_kkn_id', $pesertaId)
            ->where('kelompok_kkn_id', $kelompokId)
            ->where('is_validated', false)
            ->update([
                'is_validated' => true,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

        return back()->with('success', 'Semua log book anggota ini berhasil divalidasi.');
    }
}
