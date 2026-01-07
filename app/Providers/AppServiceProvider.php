<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
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
        // Grant all permissions to Admin users
        Gate::before(function ($user, $ability) {
            if ($user->hasRole('Admin')) {
                return true;
            }
        });
    }
}
