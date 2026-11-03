<?php

use App\Http\Controllers\ExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Export Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:superadmin'])->prefix('admin/export')->name('admin.export.')->group(function () {
    // Mahasiswa per gelombang (multi-sheet)
    Route::get('/mahasiswa-per-gelombang', [ExportController::class, 'mahasiswaPerGelombang'])
        ->name('mahasiswa-per-gelombang');
});
