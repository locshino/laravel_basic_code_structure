<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Symfony\Component\Finder\Finder; // Import Finder to scan for files

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard'; // Define your dashboard route

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Load default API routes
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            // Load default Web routes
            Route::middleware('web')
                ->group(base_path('routes/web.php'));

            // Load default auth routes
            Route::middleware('web')
                ->group(base_path('routes/auth.php'));

            // --- Bổ sung: Load các file route con theo tính năng ---

            // Load Web feature routes from routes/web/
            $webRouteFiles = Finder::create()->files()->in(base_path('routes/web'))->name('*.php');
            foreach ($webRouteFiles as $file) {
                Route::middleware('web')
                    // You can add ->prefix() or ->name() group here if you want to apply to the entire file
                    ->group($file->getRealPath());
            }

            // Load API feature routes from routes/api/
            $apiRouteFiles = Finder::create()->files()->in(base_path('routes/api'))->name('*.php');
            foreach ($apiRouteFiles as $file) {
                $featureName = pathinfo($file->getFilename(), PATHINFO_FILENAME); // Get the feature name from the file name
                Route::middleware('api')
                    ->prefix("api/{$featureName}")
                    ->name("api.{$featureName}.")
                    ->group($file->getRealPath());
            }

            // -----------------------------------------------------
        });
    }
}
