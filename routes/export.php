<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Export Routes
|--------------------------------------------------------------------------
*/

// Admin export routes
Route::middleware(['auth', 'role:superadmin'])->prefix('admin/export')->name('admin.export.')->group(function () {
    // Mahasiswa per gelombang (multi-sheet)
    Route::get('/mahasiswa-per-gelombang', [ExportController::class, 'mahasiswaPerGelombang'])
        ->name('mahasiswa-per-gelombang');

    // DPL Artikel - Filter UI + paginated list
    Route::get('/dpl-artikel', [ExportController::class, 'dplArtikelIndex'])
        ->name('dpl-artikel');

    // DPL Artikel - Export filtered to Excel
    Route::get('/dpl-artikel/excel', [ExportController::class, 'dplArtikelExcel'])
        ->name('dpl-artikel.excel');

    // DPL Artikel - Download ZIP (files + index.xlsx)
    Route::get('/dpl-artikel/zip', [ExportController::class, 'dplArtikelZip'])
        ->name('dpl-artikel.zip');
});

// DPL export routes (own supervised groups only)
Route::middleware(['auth', 'role:dpl'])->prefix('dpl/export')->name('dpl.export.')->group(function () {
    Route::get('/artikel-zip', [ExportController::class, 'dplArtikelZipOwn'])
        ->name('artikel-zip');
});
