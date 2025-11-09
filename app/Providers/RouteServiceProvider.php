<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route setup.
     */
    public function boot(): void
    {
        $this->routes(function () {
            // 👇 API routes (CORS, throttle, etc. will apply)
            Route::prefix('api')
                ->middleware('api')
                ->group(base_path('routes/api.php'));

            // 👇 Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
