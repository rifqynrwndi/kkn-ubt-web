<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

use App\Http\Controllers\{
    ActivityLogController,
    BiodataController,
    ExampleController,
    FakultasProdiController,
    FileManagerController,
    GelombangController,
    HakaksesController,
    HomeController,
    MahasiswaManagementController,
    NotificationController,
    PendaftaranKknController,
    DokumenPendaftaranController,
    VerifikasiDokumenController,
    ProfileController,
    SettingController
};

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::get('/', fn () => view('auth.login'));

Auth::routes(['verify' => true]);

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
        Route::get('/', [PendaftaranKknController::class, 'index'])->name('index');
        Route::post('/', [PendaftaranKknController::class, 'store'])->name('store');
    });

    Route::prefix('dokumen-pendaftaran')->middleware('auth')->name('dokumen-pendaftaran.')->group(function () {
        Route::get('/', [DokumenPendaftaranController::class, 'index'])->name('index');
        Route::post('/upload', [DokumenPendaftaranController::class, 'store'])->name('store');
        Route::get('/{id}', [DokumenPendaftaranController::class, 'show'])->name('show');
        Route::delete('/{id}', [DokumenPendaftaranController::class, 'destroy'])->name('destroy');
    });

    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect('/home');
    })->middleware(['signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('status', 'verification-link-sent');
    })->middleware(['throttle:6,1'])->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Notifications (All Authenticated Users)
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('/recent', [NotificationController::class, 'recent'])->name('recent');

        Route::post('/{id}/mark-as-read', [NotificationController::class, 'markAsRead'])->name('mark-as-read');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('mark-all-read');

        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::delete('/', [NotificationController::class, 'destroyAll'])->name('destroy-all');
    });
});

/*
|--------------------------------------------------------------------------
| Auth + Biodata Complete
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'biodata.complete'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | General Pages
    |--------------------------------------------------------------------------
    */

    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('/blank-page', [HomeController::class, 'blank'])->name('blank');
    Route::view('/quick-tour', 'layouts.quick-tour')->name('quick-tour');

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
    | Example Pages
    |--------------------------------------------------------------------------
    */

    Route::controller(ExampleController::class)->group(function () {
        Route::get('/table-example', 'table')->name('table.example');
        Route::get('/clock-example', 'clock')->name('clock.example');
        Route::get('/chart-example', 'chart')->name('chart.example');
        Route::get('/form-example', 'form')->name('form.example');
        Route::get('/map-example', 'map')->name('map.example');
        Route::get('/calendar-example', 'calendar')->name('calendar.example');
        Route::get('/gallery-example', 'gallery')->name('gallery.example');
        Route::get('/todo-example', 'todo')->name('todo.example');
        Route::get('/contact-example', 'contact')->name('contact.example');
        Route::get('/faq-example', 'faq')->name('faq.example');
        Route::get('/news-example', 'news')->name('news.example');
        Route::get('/about-example', 'about')->name('about.example');
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
        | Send Notification / Notification History (Superadmin Only)
        |--------------------------------------------------------------------------
        */

        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/create', [NotificationController::class, 'create'])->name('create');
            Route::post('/send', [NotificationController::class, 'send'])->name('send');

            Route::get('/history', [NotificationController::class, 'history'])->name('history');
            Route::delete('/history/{id}', [NotificationController::class, 'destroyHistory'])->name('history.destroy');
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
