<?php
namespace App\Http\Controllers;

use App\Models\KelompokKkn;
use App\Services\StatusService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StatusController extends Controller
{
    public function index(KelompokKkn $kelompok, StatusService $service): View
    {
        $current = $service->getCurrentStage($kelompok);
        $history = $service->getHistory($kelompok);
        $stages  = StatusService::STAGES;
        $isDpl   = $kelompok->dosen_pembimbing_lapangan_id === auth()->user()->dosenPembimbingLapangan?->id;
        $isAdmin = auth()->user()->hasRole('superadmin');

        return view('kelompok.status.index', compact('kelompok','current','history','stages','isDpl','isAdmin'));
    }

    public function change(Request $request, KelompokKkn $kelompok, StatusService $service): RedirectResponse
    {
        $stage = (int) $request->input('stage');
        $keterangan = $request->input('keterangan');

        $service->changeStatus($kelompok, $stage, $keterangan);

        return redirect()->route('kelompok.index', ['tab' => 'status'])
            ->with('success', 'Status berhasil diubah.');
    }
}
