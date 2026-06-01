<?php
namespace App\Http\Controllers;

use App\Models\PenilaianKelompok;
use App\Models\PenilaianIndividu;
use App\Models\PenilaianKomponen;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class PenilaianController extends Controller
{
    public function input(Request $request): RedirectResponse
    {
        $komponen = PenilaianKomponen::findOrFail($request->komponen_id);
        $kelompokId = $request->kelompok_kkn_id;

        $isDpl = $komponen->kategori === 'dpl' && auth()->user()->dosenPembimbingLapangan;
        $isLppm = $komponen->kategori === 'lppm' && auth()->user()->hasRole('superadmin');

        if (!$isDpl && !$isLppm) abort(403);

        $request->validate(['nilai'=>'required|numeric|min:0|max:100']);

        // DPL per-individu scoring
        if ($isDpl && $request->filled('peserta_kkn_id')) {
            $request->validate(['peserta_kkn_id'=>'required|exists:peserta_kkn,id']);
            PenilaianIndividu::updateOrCreate(
                [
                    'kelompok_kkn_id' => $kelompokId,
                    'peserta_kkn_id' => $request->peserta_kkn_id,
                    'komponen_id' => $komponen->id,
                ],
                ['nilai' => $request->nilai, 'input_by' => auth()->id()]
            );
            return redirect()->route('dpl.kelompok.show', $kelompokId)->with('success', 'Nilai individu berhasil disimpan.');
        }

        PenilaianKelompok::updateOrCreate(
            ['kelompok_kkn_id'=>$kelompokId, 'komponen_id'=>$komponen->id],
            ['nilai'=>$request->nilai, 'input_by'=>auth()->id(), 'input_at'=>now()]
        );

        return redirect()->route('kelompok.index', ['tab'=>'penilaian'])->with('success','Nilai berhasil disimpan.');
    }
}
