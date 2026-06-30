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

    public function create(Request $request): View
    {
        abort_if(!$this->getKelompokId(), 404);

        $editing = null;
        if ($request->has('edit')) {
            $editing = LogBook::where('id', $request->edit)
                ->where('status', 'ditolak')
                ->whereHas('pesertaKkn', fn($q) => $q->where('mahasiswa_id', auth()->user()->mahasiswa->user_id))
                ->first();
            abort_if(!$editing, 404);
        }

        return view('kelompok.logbook.create', compact('editing'));
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
        abort_if($logbook->status === 'tervalidasi', 403, 'Log book yang sudah divalidasi tidak dapat diedit.');

        $request->validate([
            'tanggal' => 'required|date|before_or_equal:today',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string|min:50|max:2000',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $data = $request->only(['tanggal','judul','deskripsi']);
        $data['status'] = 'menunggu';
        $data['komentar_dpl'] = null;

        if ($request->hasFile('file')) {
            if ($logbook->file_path) Storage::disk('public')->delete($logbook->file_path);
            $data['file_path'] = $request->file('file')->store('logbook', 'public');
            $data['file_name'] = $request->file('file')->getClientOriginalName();
        }

        $logbook->update($data);

        return redirect()->route('kelompok.index', ['tab' => 'logbook'])->with('success', 'Log book diperbarui dan menunggu validasi ulang.');
    }

    public function destroy(LogBook $logbook): RedirectResponse
    {
        if ($logbook->status === 'tervalidasi') {
            return back()->with('error', 'Log book yang sudah divalidasi tidak dapat dihapus.');
        }
        if ($logbook->file_path) Storage::disk('public')->delete($logbook->file_path);
        $logbook->delete();
        return back()->with('success', 'Log book dihapus.');
    }

    public function validateAll(Request $request): RedirectResponse
    {
        $pesertaId = $request->peserta_id;
        $peserta = \App\Models\PesertaKkn::with('kelompokKkn')->findOrFail($pesertaId);

        $dpl = auth()->user()->dosenPembimbingLapangan;
        $isAdmin = auth()->user()->hasRole('superadmin');

        if ($dpl) {
            abort_if($peserta->kelompokKkn->dosen_pembimbing_lapangan_id !== $dpl->id, 403);
        } elseif ($isAdmin) {
            // admin can validate any
        } else {
            $kelompokId = $this->getKelompokId();
            abort_if(!$kelompokId || $peserta->kelompok_kkn_id !== $kelompokId, 403);
        }

        LogBook::where('peserta_kkn_id', $pesertaId)
            ->where('status', 'menunggu')
            ->update([
                'status' => 'tervalidasi',
                'is_validated' => true,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);

        $previous = url()->previous();
        $parsed = parse_url($previous);
        if (!$parsed || !isset($parsed['host'])) {
            return back()->with('success', 'Semua log book anggota ini berhasil divalidasi.');
        }
        $query = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        unset($query['tab']);
        $query['tab'] = 'logbook';
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $url = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $port . ($parsed['path'] ?? '/') . '?' . http_build_query($query);

        return redirect($url)->with('success', 'Semua log book anggota ini berhasil divalidasi.');
    }

    public function review(Request $request, LogBook $logbook): RedirectResponse
    {
        $request->validate([
            'action' => 'required|in:terima,tolak',
            'komentar_dpl' => 'nullable|string',
        ]);

        $dpl = auth()->user()->dosenPembimbingLapangan;
        $isAdmin = auth()->user()->hasRole('superadmin');

        if ($dpl) {
            abort_if($logbook->pesertaKkn->kelompokKkn->dosen_pembimbing_lapangan_id !== $dpl->id, 403);
        } elseif (!$isAdmin) {
            abort(403);
        }

        if ($request->action === 'terima') {
            $logbook->update([
                'status' => 'tervalidasi',
                'is_validated' => true,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
                'komentar_dpl' => $request->komentar_dpl,
            ]);
        } else {
            $logbook->update([
                'status' => 'ditolak',
                'is_validated' => false,
                'komentar_dpl' => $request->komentar_dpl,
                'validated_by' => auth()->id(),
                'validated_at' => now(),
            ]);
        }

        $previous = url()->previous();
        $parsed = parse_url($previous);
        if (!$parsed || !isset($parsed['host'])) {
            return back()->with('success', 'Log book di-review.');
        }
        $query = [];
        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
        }
        $tab = str_contains($parsed['path'] ?? '', '/kelompok-kkn/') ? 'admin-logbook' : 'logbook';
        $query['tab'] = $tab;
        $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
        $url = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '') . $port . ($parsed['path'] ?? '/') . '?' . http_build_query($query);

        return redirect($url)->with('success', 'Log book di-review.');
    }
}
