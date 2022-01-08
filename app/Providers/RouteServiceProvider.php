<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    public function map(): void
    {
        $this->mapApiRoutes();
    }

    protected function mapApiRoutes(): void
    {
        Route::middleware('api')
            ->group(base_path('routes/users/api.php'));

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('management')
            ->name('management.')
            ->group(base_path('routes/management/api.php'));

        Route::middleware('api')
            ->group(base_path('routes/auth.php'));

        Route::middleware(['api', 'auth:sanctum'])
            ->prefix('users')
            ->name('users.')
            ->group(base_path('routes/users.php'));
    }

    public function configureRateLimiting(): void
    {
        RateLimiter::for('api', static function () {
            return Limit::perMinute(120);
        });
        RateLimiter::for('login', static function () {
            return Limit::perMinute(5);
        });
        RateLimiter::for('email.verification', static function () {
            return Limit::perHour(3);
        });
        RateLimiter::for('registration', static function () {
            return Limit::perHour(3);
        });
    }
}
