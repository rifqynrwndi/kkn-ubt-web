<?php

namespace App\Providers;

use App\Mail\ResendTransport;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Mail;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrap();

        Mail::extend('resend', function (array $config) {
            return new ResendTransport(
                apiKey: $config['api_key'] ?? env('RESEND_API_KEY'),
            );
        });
    }
}
