<?php

namespace App\Http\Controllers;

use App\Exports\BatchDplArtikelExport;
use App\Exports\DplArtikelZipExport;
use App\Exports\MahasiswaPerGelombangExport;
use App\Models\DosenPembimbingLapangan;
use App\Models\Gelombang;
use App\Models\LaporanDpl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Admin: Export mahasiswa per gelombang (multi-sheet Excel)
     * GET /admin/export/mahasiswa-per-gelombang?gelombang_id=X
     */
    public function mahasiswaPerGelombang(Request $request)
    {
        Gate::authorize('isSuperAdmin');

        $gelombangId = $request->filled('gelombang_id') ? (int) $request->gelombang_id : null;

        return Excel::download(
            new MahasiswaPerGelombangExport($gelombangId),
            'mahasiswa-per-gelombang-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    /**
     * Admin: DPL Artikel index page with filters
     * GET /admin/export/dpl-artikel
     */
    public function dplArtikelIndex(Request $request)
    {
        Gate::authorize('isSuperAdmin');

        $gelombangs = Gelombang::orderBy('tahun', 'desc')->orderBy('nama_gelombang')->get();
        $dpls = DosenPembimbingLapangan::with('user')->orderBy('user.name')->get();

        $query = LaporanDpl::with([
            'kelompokKkn.desaGelombang.desa.kecamatan',
            'kelompokKkn.dosenPembimbingLapangan.user',
            'dpl.user',
        ]);

        // Apply filters
        if ($request->filled('gelombang_id')) {
            $query->whereHas('kelompokKkn.desaGelombang', fn ($q) => $q->where('gelombang_id', $request->gelombang_id)
            );
        }

        if ($request->filled('dpl_id')) {
            $query->where('dpl_id', $request->dpl_id);
        }

        if ($request->filled('jenis')) {
            $query->where('jenis', $request->jenis);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $laporans = $query->latest()->paginate(20)->withQueryString();

        $jenisOptions = [
            'mingguan' => 'Laporan Mingguan',
            'akhir' => 'Laporan Akhir',
            'khusus' => 'Laporan Khusus',
        ];

        return view('export.dpl-artikel-index', compact(
            'gelombangs', 'dpls', 'laporans', 'jenisOptions'
        ));
    }

    /**
     * Admin: Export filtered DPL Artikel to Excel
     * GET /admin/export/dpl-artikel/excel
     */
    public function dplArtikelExcel(Request $request)
    {
        Gate::authorize('isSuperAdmin');

        $filters = $request->only(['gelombang_id', 'dpl_id', 'jenis', 'date_from', 'date_to', 'search']);

        return Excel::download(
            new BatchDplArtikelExport($filters),
            'dpl-artikel-'.now()->format('Ymd-His').'.xlsx'
        );
    }

    /**
     * Admin: Download ZIP of DPL Artikel files + index.xlsx
     * GET /admin/export/dpl-artikel/zip
     */
    public function dplArtikelZip(Request $request)
    {
        Gate::authorize('isSuperAdmin');

        $filters = $request->only(['gelombang_id', 'dpl_id', 'jenis', 'date_from', 'date_to', 'search']);

        $exporter = new DplArtikelZipExport($filters);
        $zipPath = $exporter->generate();

        return response()->download($zipPath, $exporter->getZipFileName())->deleteFileAfterSend(true);
    }

    /**
     * DPL: Download ZIP of own supervised groups' artikel files
     * GET /dpl/export/artikel-zip
     */
    public function dplArtikelZipOwn(Request $request)
    {
        $user = auth()->user();
        $dpl = $user->dosenPembimbingLapangan;

        if (! $dpl) {
            abort(403, 'Anda tidak memiliki akses DPL.');
        }

        $filters = ['dpl_id' => $dpl->id];
        $filters = array_merge($filters, $request->only(['gelombang_id', 'jenis', 'date_from', 'date_to', 'search']));

        $exporter = new DplArtikelZipExport($filters);
        $zipPath = $exporter->generate();

        return response()->download($zipPath, $exporter->getZipFileName())->deleteFileAfterSend(true);
    }
}
