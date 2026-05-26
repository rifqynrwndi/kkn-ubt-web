<?php
namespace App\Http\Controllers;

use App\Models\KelompokProposal;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProposalController extends Controller
{
    private function getKelompok()
    {
        $mhs = auth()->user()->mahasiswa;
        if (! $mhs) return null;
        return \App\Models\PesertaKkn::where('mahasiswa_id', $mhs->user_id)
            ->whereNotNull('kelompok_kkn_id')->with('kelompokKkn')->first()?->kelompokKkn;
    }

    private function getProposal($kelompokId)
    {
        return KelompokProposal::where('kelompok_kkn_id', $kelompokId)->first();
    }

    public function index(): View
    {
        $kelompok = $this->getKelompok();
        abort_if(! $kelompok, 404);

        $proposal = $this->getProposal($kelompok->id);
        $isKetua = $kelompok->ketua_peserta_id === \App\Models\PesertaKkn::where('mahasiswa_id', auth()->user()->mahasiswa->user_id)->whereNotNull('kelompok_kkn_id')->value('id');
        $isDpl = $kelompok->dosen_pembimbing_lapangan_id === auth()->user()->dosenPembimbingLapangan?->id;

        return view('kelompok.proposal.index', compact('kelompok', 'proposal', 'isKetua', 'isDpl'));
    }

    public function create(): View
    {
        $kelompok = $this->getKelompok();
        abort_if(! $kelompok, 404);
        abort_if($kelompok->ketua_peserta_id !== \App\Models\PesertaKkn::where('mahasiswa_id', auth()->user()->mahasiswa->user_id)->whereNotNull('kelompok_kkn_id')->value('id'), 403);

        $proposal = $this->getProposal($kelompok->id);
        if ($proposal && $proposal->status !== 'draft' && $proposal->status !== 'ditolak') {
            return redirect()->route('kelompok.proposal.index')->with('error', 'Proposal sudah diajukan.');
        }

        return view('kelompok.proposal.form', compact('kelompok', 'proposal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $kelompok = $this->getKelompok();
        abort_if(! $kelompok, 404);

        $action = $request->input('action', 'draft');
        $isSubmit = $action === 'submit';

        $request->validate([
            'pendahuluan' => $isSubmit ? 'required|string|min:200' : 'nullable|string',
            'tujuan' => $isSubmit ? 'required|string|min:100' : 'nullable|string',
            'manfaat' => $isSubmit ? 'required|string|min:150' : 'nullable|string',
            'hasil_observasi' => 'nullable|string',
            'rancangan_program' => $isSubmit ? 'required|string|min:300' : 'nullable|string',
            'solusi_ide' => $isSubmit ? 'required|string|min:200' : 'nullable|string',
        ]);

        $proposal = $this->getProposal($kelompok->id);

        if ($proposal && ! in_array($proposal->status, ['draft', 'ditolak'])) {
            return back()->with('error', 'Tidak dapat menyimpan. Status proposal sudah diajukan.');
        }

        KelompokProposal::updateOrCreate(
            ['kelompok_kkn_id' => $kelompok->id],
            [
                'pendahuluan' => $request->pendahuluan,
                'tujuan' => $request->tujuan,
                'manfaat' => $request->manfaat,
                'hasil_observasi' => $request->hasil_observasi,
                'rancangan_program' => $request->rancangan_program,
                'solusi_ide' => $request->solusi_ide,
                'status' => $action === 'submit' ? 'diajukan' : 'draft',
                'submitted_by' => $action === 'submit' ? \App\Models\PesertaKkn::where('mahasiswa_id', auth()->user()->mahasiswa->user_id)->whereNotNull('kelompok_kkn_id')->value('id') : null,
                'submitted_at' => $action === 'submit' ? now() : null,
            ]
        );

        $msg = $action === 'submit' ? 'Proposal berhasil diajukan.' : 'Draft proposal disimpan.';
        return redirect()->route('kelompok.index', ['tab' => 'proposal'])->with('success', $msg);
    }

    public function review(Request $request, KelompokProposal $proposal): RedirectResponse
    {
        $dpl = auth()->user()->dosenPembimbingLapangan;
        abort_if(! $dpl || $proposal->kelompokKkn->dosen_pembimbing_lapangan_id !== $dpl->id, 403);

        $request->validate([
            'action' => 'required|in:setujui,tolak',
            'komentar_dpl' => 'required_if:action,tolak|string|max:1000',
        ]);

        $proposal->update([
            'status' => $request->action === 'setujui' ? 'disetujui' : 'ditolak',
            'komentar_dpl' => $request->komentar_dpl,
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        return redirect()->route('kelompok.index', ['tab' => 'proposal'])->with('success', 'Proposal berhasil di-review.');
    }
}
