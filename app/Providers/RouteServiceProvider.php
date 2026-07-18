<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
     protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
           // return Limit::perMinute(60);
           
             return Limit::perMinute(8000000000)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('home_data_limit', function (Request $request) {
           // return Limit::perMinute(60);
           
             return Limit::perMinute(8000000000)->by(optional($request->user())->id ?: $request->ip());
        });
        RateLimiter::for('game_hit', function (Request $request) {
           // return Limit::perMinute(60);
           
             return Limit::perMinute(80000)->by(optional($request->user())->id ?: $request->ip());
        });
        
        RateLimiter::for('recharge', function ($request) {
        return Limit::perMinute(1)->by(optional($request->user())->id ?: $request->ip());
    });
    }
}
