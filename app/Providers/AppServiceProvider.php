<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\SteganographyService;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
         $this->app->singleton(SteganographyService::class, function ($app) {
        return new SteganographyService();
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
