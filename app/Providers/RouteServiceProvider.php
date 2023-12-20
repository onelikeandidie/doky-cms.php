<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

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

                Route::middleware('webhooks')
                    ->prefix('webhooks')
                    ->group(base_path('routes/webhooks.php'));

                // Just serve the public directory
                Route::get('/public/{path}', function ($path) {
                    $real_path = public_path($path);
                    if (!file_exists($real_path)) {
                        abort(404);
                    }
                    $type = File::mimeType($real_path);
                    $isImage = false;
                    if (Str::endsWith($path, '.js')) {
                        $type = 'text/javascript';
                    }
                    if (Str::endsWith($path, '.css')) {
                        $type = 'text/css';
                    }
                    if (Str::endsWith($path, '.png')) {
                        $type = 'image/png';
                        $isImage = true;
                    }
                    if (Str::endsWith($path, '.jpg')) {
                        $type = 'image/jpeg';
                        $isImage = true;
                    }
                    if (Str::endsWith($path, '.svg')) {
                        $type = 'image/svg+xml';
                    }
                    $size = File::size($real_path);
                    $headers = [
                        'Content-Type' => $type,
                        'Content-Length' => $size,
                    ];
                    if (config('cache.image.expire') && $isImage) {
                        $headers['Cache-Control'] = 'max-age=' . config('cache.image.expire');
                    }
                    return response()->file($real_path, $headers);
                })->where('path', '.*');
            });
        });
    }
}
