<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\{
    ActivityLogController,
    BiodataController,
    FakultasProdiController,
    FileManagerController,
    GelombangController,
    HakaksesController,
    HomeController,
    MahasiswaManagementController,
    NotificationController,
    DosenPembimbingLapanganController,
    DesaController,
    PendaftaranKknController,
    DokumenPendaftaranController,
    DplController,
    VerifikasiDokumenController,
    KelompokKknController,
    KelompokController,
    WarAdminController,
    WarController,
    WarMonitorController,
    ProfileController,
    SettingController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('auth.login'));

Auth::routes();

// Custom error pages
Route::get('/errors/413', fn () => view('errors.413'))->name('error.413');

/*
|--------------------------------------------------------------------------
| Authenticated Users
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Biodata / Profile Basic
    |--------------------------------------------------------------------------
    */

    Route::prefix('biodata')->name('biodata.')->group(function () {
        Route::get('/edit', [BiodataController::class, 'edit'])->name('edit');
        Route::put('/update', [BiodataController::class, 'update'])->name('update');
    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::put('/password', [ProfileController::class, 'password'])->name('password');
    });

    Route::prefix('pendaftaran-kkn')->name('pendaftaran-kkn.')->group(function () {
        Route::get('/', [PendaftaranKknController::class,'index'])->name('index');
        Route::get('/gelombang', [PendaftaranKknController::class, 'gelombang'])->name('gelombang');
        Route::post('/store', [PendaftaranKknController::class, 'store'])->name('store');
        Route::get('/plotting', [PendaftaranKknController::class, 'plotting'])->name('plotting');
        Route::post('/plotting/{kelompok}', [PendaftaranKknController::class, 'ambilKelompok'])->name('ambil-kelompok');
        Route::get('/kelompok', [PendaftaranKknController::class, 'kelompokSaya'])->name('kelompok');
    });

    Route::prefix('dokumen-pendaftaran')->group(function () {
        Route::get('/', [DokumenPendaftaranController::class, 'index'])->name('dokumen-pendaftaran.index');
        Route::get('/create', [DokumenPendaftaranController::class, 'create'])->name('dokumen-pendaftaran.create');
        Route::post('/', [DokumenPendaftaranController::class, 'store'])->name('dokumen-pendaftaran.store');
        Route::get('/{id}', [DokumenPendaftaranController::class, 'show'])->name('dokumen-pendaftaran.show');
        Route::delete('/{id}', [DokumenPendaftaranController::class, 'destroy'])->name('dokumen-pendaftaran.destroy');
    });

    Route::post('/email/verification-notification', function (Request $request) {
        try {
            $request->user()->sendEmailVerificationNotification();

            return response()->json([
                'success' => true,
                'message' => 'Link verifikasi telah dikirim ke email Anda.',
            ]);
        } catch (\Throwable $e) {
            logger()->error('Verification email failed', [
                'user_id' => $request->user()->id,
                'email'   => $request->user()->email,
                'error'   => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim verifikasi. ' . $e->getMessage(),
            ], 500);
        }
    })->middleware(['throttle:10,1'])->name('verification.send');

}); // end auth group

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();

    return redirect()->route('biodata.edit')->with('success', 'Email berhasil diverifikasi. Silakan lengkapi biodata Anda.');
})->middleware(['signed'])->name('verification.verify');

Route::middleware(['auth', 'biodata.complete', 'email.verified.except.superadmin'])->group(function () {

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');

        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');

        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    });

    /*
    |--------------------------------------------------------------------------
    | General Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('dpl')->name('dpl.')->middleware('role:pembimbing')->group(function () {
        Route::get('/kelompok', [DplController::class, 'kelompokIndex'])->name('kelompok.index');
        Route::get('/kelompok/{kelompok}', [DplController::class, 'kelompokShow'])->name('kelompok.show');
        Route::get('/mahasiswa/{peserta}', [DplController::class, 'mahasiswaShow'])->name('mahasiswa.show');
    });

    Route::prefix('kelompok')->name('kelompok.')->group(function () {
        Route::get('/', [KelompokController::class, 'index'])->name('index');
        Route::post('/upload-photo', [KelompokController::class, 'uploadPhoto'])->name('upload-photo');
    });

    /*
    |--------------------------------------------------------------------------
    | WAR KKN — Mahasiswa
    |--------------------------------------------------------------------------
    */

    Route::prefix('war')->name('war.')->group(function () {

        // Lobby — lihat jadwal & status war
        Route::get('/', [WarController::class, 'index'])->name('index');

        // Arena — halaman rebutan (hanya saat war aktif)
        Route::get('/{session}/arena', [WarController::class, 'arena'])->name('arena');

        // Sudah join — halaman konfirmasi
        Route::get('/{session}/joined', [WarController::class, 'joined'])->name('joined');

        // Core action — JOIN kelompok (AJAX, throttle anti-spam)
        Route::post('/{session}/join/{kelompokId}', [WarController::class, 'join'])
            ->name('join')
            ->middleware('throttle:10,1'); // maks 10 request/menit per user

        // Status check — AJAX polling (apakah war masih aktif? sudah dapat kelompok?)
        Route::get('/{session}/status', [WarController::class, 'status'])->name('status');

        // Kelompok list — AJAX untuk live refresh daftar kelompok
        Route::get('/{session}/kelompoks', [WarController::class, 'kelompokList'])->name('kelompoks');

    });

    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('/edit', [ProfileController::class, 'edit'])->name('edit');
        Route::put('/update', [ProfileController::class, 'update'])->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | File Manager
    |--------------------------------------------------------------------------
    */

    Route::prefix('file-manager')->name('file-manager.')->group(function () {
        Route::get('/', [FileManagerController::class, 'index'])->name('index');
        Route::post('/upload', [FileManagerController::class, 'upload'])->name('upload');
        Route::post('/create-folder', [FileManagerController::class, 'createFolder'])->name('create-folder');

        Route::get('/{id}/download', [FileManagerController::class, 'download'])->name('download');
        Route::get('/{id}/show', [FileManagerController::class, 'show'])->name('show');

        Route::put('/{id}', [FileManagerController::class, 'update'])->name('update');
        Route::delete('/{id}', [FileManagerController::class, 'destroy'])->name('destroy');
    });

    /*
    |--------------------------------------------------------------------------
    | Superadmin Only
    |--------------------------------------------------------------------------
    */

    Route::middleware('superadmin')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Mahasiswa
        |--------------------------------------------------------------------------
        */

        Route::resource('mahasiswa', MahasiswaManagementController::class);

        /*
        |--------------------------------------------------------------------------
        | Gelombang
        |--------------------------------------------------------------------------
        */

        Route::resource('gelombang', GelombangController::class);

        /*
        |--------------------------------------------------------------------------
        | Dosen Pembimbing Lapangan
        |--------------------------------------------------------------------------
        */

        Route::resource('pembimbing-lapangan', DosenPembimbingLapanganController::class);

        /*
        |--------------------------------------------------------------------------
        | Desa
        |--------------------------------------------------------------------------
        */

        Route::resource('desa', DesaController::class);

        Route::prefix('desa')->name('desa.')->group(function () {
            Route::get('/kecamatan/create', [DesaController::class, 'createKecamatan'])
                ->name('createKecamatan');

            Route::post('/kecamatan/store', [DesaController::class, 'storeKecamatan'])
                ->name('storeKecamatan');

            Route::get('/kecamatan/{kecamatan}/edit', [DesaController::class, 'editKecamatan'])
                ->name('editKecamatan');

            Route::put('/kecamatan/{kecamatan}', [DesaController::class, 'updateKecamatan'])
                ->name('updateKecamatan');

            Route::delete('/kecamatan/{kecamatan}', [DesaController::class, 'destroyKecamatan'])
                ->name('destroyKecamatan');
        });

        /*
        |--------------------------------------------------------------------------
        | Kelompok KKN
        |--------------------------------------------------------------------------
        */

        Route::resource('kelompok-kkn', KelompokKknController::class);

        Route::get('kelompok-kkn/{kelompok_kkn}/anggota/create',[KelompokKknController::class, 'createAnggota'])->name('kelompok-kkn.anggota.create');
        Route::post('kelompok-kkn/{kelompok_kkn}/anggota',[KelompokKknController::class, 'tambahAnggota'])->name('kelompok-kkn.anggota.store');
        Route::delete('kelompok-kkn/{kelompok_kkn}/anggota/{peserta}',[KelompokKknController::class, 'hapusAnggota'])->name('kelompok-kkn.anggota.destroy');
        Route::put('kelompok-kkn/{kelompok_kkn}/buka',[KelompokKknController::class, 'buka'])->name('kelompok-kkn.buka');
        Route::put('kelompok-kkn/{kelompok_kkn}/tutup',[KelompokKknController::class, 'tutup'])->name('kelompok-kkn.tutup');
        Route::put('kelompok-kkn/{kelompok_kkn}/ketua/{peserta}',[KelompokKknController::class, 'setKetua'])->name('kelompok-kkn.ketua');

        /*
        |--------------------------------------------------------------------------
        | Fakultas & Prodi
        |--------------------------------------------------------------------------
        */

        Route::prefix('fakultas-prodi')->name('fakultas-prodi.')->group(function () {
            Route::get('/', [FakultasProdiController::class, 'index'])->name('index');

            Route::prefix('fakultas')->name('fakultas.')->group(function () {
                Route::get('/create', [FakultasProdiController::class, 'createFakultas'])->name('create');
                Route::post('/', [FakultasProdiController::class, 'storeFakultas'])->name('store');
                Route::get('/{fakultas}/edit', [FakultasProdiController::class, 'editFakultas'])->name('edit');
                Route::put('/{fakultas}', [FakultasProdiController::class, 'updateFakultas'])->name('update');
                Route::delete('/{fakultas}', [FakultasProdiController::class, 'deleteFakultas'])->name('delete');
            });

            Route::prefix('prodi')->name('prodi.')->group(function () {
                Route::get('/create', [FakultasProdiController::class, 'createProdi'])->name('create');
                Route::post('/', [FakultasProdiController::class, 'storeProdi'])->name('store');
                Route::get('/{prodi}/edit', [FakultasProdiController::class, 'editProdi'])->name('edit');
                Route::put('/{prodi}', [FakultasProdiController::class, 'updateProdi'])->name('update');
                Route::delete('/{prodi}', [FakultasProdiController::class, 'deleteProdi'])->name('delete');
            });

        });

        /*
        |--------------------------------------------------------------------------
        | War Admin — Session Management
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin/war')->name('admin.war.')->group(function () {

            // Dashboard
            Route::get('/', [WarAdminController::class, 'index'])->name('index');

            // CRUD Session
            Route::get('/create', [WarAdminController::class, 'create'])->name('create');
            Route::post('/', [WarAdminController::class, 'store'])->name('store');
            Route::get('/{war}', [WarAdminController::class, 'show'])->name('show');
            Route::put('/{war}', [WarAdminController::class, 'update'])->name('update');
            Route::delete('/{war}', [WarAdminController::class, 'destroy'])->name('destroy');

            // Status Control
            Route::post('/{war}/activate', [WarAdminController::class, 'activate'])->name('activate');
            Route::post('/{war}/stop', [WarAdminController::class, 'stop'])->name('stop');
            Route::post('/{war}/reset', [WarAdminController::class, 'reset'])->name('reset');

            // Faculty Config
            Route::post('/{war}/set-faculty-quota', [WarAdminController::class, 'setFacultyQuota'])->name('setFacultyQuota');
            Route::post('/{war}/set-faculty-schedule', [WarAdminController::class, 'setFacultySchedule'])->name('setFacultySchedule');

            // Monitor Page (Blade)
            Route::get('/{war}/monitor', [WarAdminController::class, 'monitor'])->name('monitor');

        });

        /*
        |--------------------------------------------------------------------------
        | War Monitor — Realtime AJAX Endpoints (Admin)
        |--------------------------------------------------------------------------
        */

        Route::prefix('admin/war/{session}/monitor')->name('admin.war.monitor.')->group(function () {

            // Live stats summary
            Route::get('/stats', [WarMonitorController::class, 'stats'])->name('stats');

            // Live kelompok list
            Route::get('/kelompoks', [WarMonitorController::class, 'kelompoks'])->name('kelompoks');

            // Live activity log
            Route::get('/logs', [WarMonitorController::class, 'logs'])->name('logs');

            // Live participant list
            Route::get('/participants', [WarMonitorController::class, 'participants'])->name('participants');

            // Export CSV
            Route::get('/export-log', [WarMonitorController::class, 'exportLog'])->name('exportLog');

        });
        /*
        |--------------------------------------------------------------------------
        | Send Notification / Notification History (Superadmin Only)
        |--------------------------------------------------------------------------
        */

        Route::prefix('notifications/admin')->name('notifications.admin.')->group(function () {

            Route::get('/create', [NotificationController::class, 'create'])
                ->name('create');

            Route::post('/send', [NotificationController::class, 'send'])
                ->name('send');

            Route::get('/history', [NotificationController::class, 'history'])
                ->name('history');

            Route::delete('/history/{id}', [NotificationController::class, 'destroyHistory'])
                ->name('history.destroy');

        });

        /*
        |--------------------------------------------------------------------------
        | Hak Akses
        |--------------------------------------------------------------------------
        */

        Route::resource('hakakses', HakaksesController::class)
            ->parameters(['hakakses' => 'id']);

        Route::prefix('verifikasi-dokumen')->name('verifikasi-dokumen.')->group(function () {
            Route::get('/', [VerifikasiDokumenController::class, 'index'])->name('index');
            Route::get('/{id}', [VerifikasiDokumenController::class, 'show'])->name('show');
            Route::put('/dokumen/{id}', [VerifikasiDokumenController::class, 'update'])->name('update');
            Route::post('/bulk-approve',[VerifikasiDokumenController::class, 'bulkApprove'])->name('bulk-approve');
            Route::put('/{peserta}/bulk-update',[VerifikasiDokumenController::class, 'bulkUpdate'])->name('bulk-update');
        });

        /*
        |--------------------------------------------------------------------------
        | Activity Logs
        |--------------------------------------------------------------------------
        */

        Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
            Route::get('/', [ActivityLogController::class, 'index'])->name('index');
            Route::get('/{id}', [ActivityLogController::class, 'show'])->name('show');
            Route::delete('/{id}', [ActivityLogController::class, 'destroy'])->name('destroy');
            Route::delete('/', [ActivityLogController::class, 'clear'])->name('clear');
        });

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */

        Route::prefix('settings')->name('settings.')->group(function () {
            Route::get('/', [SettingController::class, 'index'])->name('index');
            Route::put('/', [SettingController::class, 'update'])->name('update');
            Route::post('/', [SettingController::class, 'store'])->name('store');
            Route::post('/reset', [SettingController::class, 'reset'])->name('reset');
        });

    });
});
