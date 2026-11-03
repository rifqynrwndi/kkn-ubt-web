<?php

namespace App\Http\Controllers;

use App\Exports\MahasiswaPerGelombangExport;
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
}
