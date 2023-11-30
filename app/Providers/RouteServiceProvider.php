<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            // Put all routes into optional route prefix from .env file
            $url = config('app.url');
            $prefix = parse_url($url, PHP_URL_PATH);
            Route::prefix($prefix)->group(function () {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));

                // Just serve the public directory
                Route::get('/public/{path}', function ($path) {
                    $real_path = public_path($path);
                    if (!file_exists($real_path)) {
                        abort(404);
                    }
                    $type = File::mimeType($real_path);
                    if (str_ends_with($path, '.js')) {
                        $type = 'text/javascript';
                    }
                    $size = File::size($real_path);
                    return response()->file($real_path, [
                        'Content-Type' => $type,
                        'Content-Length' => $size,
                    ]);
                })->where('path', '.*');
            });
        });
    }
}
