<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Date;
use Carbon\Carbon;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        // Set timezone untuk Carbon
        Carbon::setLocale('id');
        config(['app.locale' => 'id']);
        
        // Force timezone
        date_default_timezone_set(config('app.timezone', 'Asia/Jakarta'));
    }

    public function register()
    {
        //
    }
}