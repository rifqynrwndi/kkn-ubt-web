<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

if (file_exists(base_path('vendor/laravel/telescope/composer.json'))) {
    Schedule::command('telescope:prune --hours=24')->daily()->environments(['local']);
}
