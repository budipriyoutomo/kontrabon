<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Paginator memakai view sendiri supaya tombolnya seragam dengan
        // <x-button> dan ikut design token, termasuk saat dark mode.
        Paginator::defaultView('vendor.pagination.shadcn');
        Paginator::defaultSimpleView('vendor.pagination.shadcn-simple');
    }
}
