<?php

namespace App\Providers;

use App\Libraries\Sync\Sync;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(Sync::class, function () {
            return Sync::getInstance();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Workaround to work is outdated mysql database
        // https://stackoverflow.com/a/65628140
        if (config('database.default') === 'mysql') {
            \Illuminate\Support\Facades\Schema::defaultStringLength(191);
        }
    }
}
