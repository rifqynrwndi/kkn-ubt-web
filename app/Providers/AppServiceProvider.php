<?php

namespace App\Providers;

use App\Mail\ResendTransport;
use Carbon\Carbon;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Telescope hanya ter-install sebagai require-dev. Daftarkan hanya
        // jika paketnya ada agar production (--no-dev) tetap bisa boot.
        // (Pakai file_exists, bukan class_exists: class_exists memicu
        // autoload yang melempar ErrorException saat file-nya tidak ada.)
        if (file_exists($this->app->basePath('vendor/laravel/telescope/composer.json'))) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    public function boot(): void
    {
        Paginator::useBootstrap();
        Carbon::setLocale('id');

        Mail::extend('resend', function (array $config) {
            return new ResendTransport(
                apiKey: $config['api_key'] ?? env('RESEND_API_KEY'),
            );
        });

        RateLimiter::for('login', fn (Request $request) => Limit::perMinute(5)->by($request->ip()));
        RateLimiter::for('register', fn (Request $request) => Limit::perMinute(3)->by($request->ip()));
        RateLimiter::for('global', fn (Request $request) => Limit::perMinute(100)->by($request->ip()));
    }
}
