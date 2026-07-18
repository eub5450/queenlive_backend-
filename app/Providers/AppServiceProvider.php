<?php

namespace App\Providers;

use App\Models\BedWord;
use App\Models\EntryFrame;
use App\Models\GiftFile;
use App\Models\Lavel;
use App\Models\Setting;
use App\Models\Slider;
use App\Services\V5\MetaBootstrapService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('RedisCacheFunction', function () {
            return new \App\RedisCache\RedisCache();
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // v5 meta bootstrap: invalidate the cached meta_version whenever any
        // model that contributes to it is saved/deleted. The next bootstrap
        // request will recompute and clients will see a new etag.
        $forget = function () {
            try {
                Cache::forget(MetaBootstrapService::VERSION_CACHE_KEY);
            } catch (\Throwable $e) {
                Log::warning('v5 meta version cache forget failed', ['error' => $e->getMessage()]);
            }
        };

        foreach ([Setting::class, Slider::class, GiftFile::class, Lavel::class, BedWord::class, EntryFrame::class] as $model) {
            $model::saved($forget);
            $model::deleted($forget);
        }
    }
}
