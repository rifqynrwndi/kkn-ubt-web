<?php

namespace App\Providers;

use App\Mail\ResendTransport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();
        \Carbon\Carbon::setLocale('id');

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
