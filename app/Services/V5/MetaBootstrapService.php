<?php

namespace App\Services\V5;

use App\Models\BedWord;
use App\Models\EntryFrame;
use App\Models\GiftFile;
use App\Models\Lavel;
use App\Models\Setting;
use App\Models\Slider;
use App\Support\SystemSettingValueHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RedisCacheFunction;

/**
 * Assembles the v5 meta bootstrap payload.
 *
 * Computes a stable `meta_version` (SHA1 of the maxUpdated timestamps of all
 * source tables) and serves the assembled envelope from Redis for 60s. When
 * the client's `If-Meta-Version` matches the current version, returns a
 * cheap `unchanged: true` short-circuit body.
 *
 * Cache invalidation is hooked on the Eloquent models in `boot()` of each
 * model (see App\Providers\MetaBootstrapCacheProvider) - any admin update
 * forgets `v5:meta:version` and the next request recomputes immediately.
 */
class MetaBootstrapService
{
    const VERSION_CACHE_KEY = 'v5:meta:version';
    const PAYLOAD_CACHE_PREFIX = 'v5:meta:payload:';
    const TTL_SECONDS = 60;

    /**
     * Build (or short-circuit) the meta bootstrap envelope.
     *
     * @param string|null $clientVersion value of the `If-Meta-Version` header
     * @return array
     */
    public function bootstrap(?string $clientVersion): array
    {
        $version = $this->currentVersion();

        if ($clientVersion !== null && hash_equals($version, $clientVersion)) {
            return [
                'ok' => true,
                'unchanged' => true,
                'meta_version' => $version,
            ];
        }

        $payloadKey = self::PAYLOAD_CACHE_PREFIX . $version;
        $cached = null;
        try {
            $cached = Cache::get($payloadKey);
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService payload cache read failed', ['error' => $e->getMessage()]);
        }

        if (is_array($cached)) {
            $cached['meta_version'] = $version;
            $cached['unchanged'] = false;
            $cached['ok'] = true;
            return $cached;
        }

        $payload = $this->assemble($version);

        try {
            Cache::put($payloadKey, $payload, self::TTL_SECONDS);
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService payload cache write failed', ['error' => $e->getMessage()]);
        }

        return $payload;
    }

    /**
     * Compute (or read cached) meta version hash.
     */
    public function currentVersion(): string
    {
        try {
            $cached = Cache::get(self::VERSION_CACHE_KEY);
            if (is_string($cached) && $cached !== '') {
                return $cached;
            }
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService version cache read failed', ['error' => $e->getMessage()]);
        }

        $version = sha1(implode('|', [
            $this->maxUpdated(Setting::class, 1),
            $this->maxUpdated(Slider::class),
            $this->maxUpdated(GiftFile::class),
            $this->maxUpdated(Lavel::class),
            $this->maxUpdated(BedWord::class),
            $this->maxUpdated(EntryFrame::class),
        ]));

        try {
            Cache::put(self::VERSION_CACHE_KEY, $version, self::TTL_SECONDS);
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService version cache write failed', ['error' => $e->getMessage()]);
        }

        return $version;
    }

    /**
     * Max `updated_at` (epoch ms) for a model. Setting clamps to row id=1.
     */
    private function maxUpdated(string $modelClass, $singletonId = null): string
    {
        try {
            if ($singletonId !== null) {
                $row = $modelClass::find($singletonId);
                return $row && $row->updated_at ? (string) $row->updated_at->getTimestamp() : '0';
            }
            $max = $modelClass::max('updated_at');
            return $max ? (string) strtotime((string) $max) : '0';
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService maxUpdated failed', [
                'model' => $modelClass,
                'error' => $e->getMessage(),
            ]);
            return '0';
        }
    }

    /**
     * Build the full payload.
     */
    private function assemble(string $version): array
    {
        $setting = $this->buildSettings();
        $sliders = $this->buildSliders();
        $hostTypes = $this->buildHostTypes();
        $powerRules = $this->buildPowerRules();
        $levelRules = $this->buildLevelRules();
        $giftList = $this->buildGiftList();
        $storeItems = $this->buildStoreItems();
        $badwords = $this->buildBadwords();
        $agora = [
            'appId' => isset($setting['agora_appId']) ? (string) $setting['agora_appId'] : '',
        ];
        $shortlinks = $this->buildShortlinks($setting);

        return [
            'ok' => true,
            'unchanged' => false,
            'meta_version' => $version,
            'settings' => $setting,
            'sliders' => $sliders,
            'host_types' => $hostTypes,
            'power_rules' => $powerRules,
            'level_rules' => $levelRules,
            'gift_list' => $giftList,
            'store_items' => $storeItems,
            'badwords' => $badwords,
            'agora' => $agora,
            'shortlinks' => $shortlinks,
        ];
    }

    private function buildSettings(): array
    {
        try {
            $setting = RedisCacheFunction::getSetting();
        } catch (\Throwable $e) {
            $setting = Setting::find(1);
        }
        if (!$setting) {
            return [];
        }

        try {
            $exchangeCut = Cache::store('redis')->get('queenlive_exchange_cut_parcentage');
        } catch (\Throwable $e) {
            $exchangeCut = null;
        }
        if (!is_numeric($exchangeCut)) {
            $exchangeCut = $setting->exchange_cut_parcentage ?? 30;
        }
        $exchangeCut = max(0, min(100, round((float) $exchangeCut, 2)));

        $pusherAppId  = config('broadcasting.connections.pusher.app_id') ?: ($setting->app_id ?? null);
        $pusherKey    = config('broadcasting.connections.pusher.key') ?: ($setting->key ?? null);
        $pusherSecret = config('broadcasting.connections.pusher.secret') ?: ($setting->secret ?? null);
        $pusherCluster= config('broadcasting.connections.pusher.options.cluster') ?: ($setting->cluster ?? null);

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $fallbackSocketHost = parse_url($setting->web_socket ?? '', PHP_URL_HOST);
        $socketHost = $appHost ?: ($fallbackSocketHost ?: '');
        $webSocketUrl = $pusherKey && $socketHost
            ? 'wss://' . $socketHost . '/app/' . $pusherKey
            : ($setting->web_socket ?? '');

        return [
            'version'                  => $setting->app_version ?? '',
            'flutter_version'          => $setting->flutter_version ?? '',
            'online_recharge'          => $setting->online_recharge ?? 0,
            'sdk'                      => $setting->sdk ?? 0,
            'pusher_app_id'            => (string) $pusherAppId,
            'pusher_key'               => (string) $pusherKey,
            'agora_appId'              => (string) ($setting->appId ?? ''),
            'pusher_cluster'           => (string) $pusherCluster,
            'web_socket'               => (string) $webSocketUrl,
            'pusher_secret'            => (string) $pusherSecret,
            'coin_beg'                 => $setting->coin_beg ?? 0,
            'apps_background'          => $setting->apps_background ?? '',
            'brd_scroll'               => $setting->brd_scroll ?? 0,
            'reward_banner'            => $setting->reward_banner ?? '',
            'vip_price_discount'       => $setting->vip_discount ?? 0,
            'vip_price_discount_percentage' => SystemSettingValueHelper::vipDiscountPercentage($setting),
            'portal_min_recharge_amount' => SystemSettingValueHelper::portalMinRechargeAmount($setting),
            'recharge_offer_reward'    => $setting->recharge_offer_reward ?? 0,
            'recharge_offer_reward_percentage' => SystemSettingValueHelper::rechargeOfferRewardPercentage($setting),
            'exchange_cut_parcentage'  => number_format($exchangeCut, 2, '.', ''),
            'game_pro'                 => $setting->game_pro ?? 0,
        ];
    }

    private function buildSliders(): array
    {
        try {
            return Slider::orderBy('id', 'desc')->get(['id', 'image', 'url', 'updated_at'])->toArray();
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService buildSliders failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Static host-type / role-pill enum. The legacy `host_type` endpoint
     * returns a per-user value; here we return the catalogue.
     */
    private function buildHostTypes(): array
    {
        return [
            ['id' => 0, 'key' => 'audience',  'label' => 'Audience'],
            ['id' => 1, 'key' => 'host',      'label' => 'Host'],
            ['id' => 2, 'key' => 'big_host',  'label' => 'Big Host'],
            ['id' => 3, 'key' => 'agency',    'label' => 'Agency'],
            ['id' => 4, 'key' => 'super_agency','label' => 'Super Agency'],
            ['id' => 5, 'key' => 'admin',     'label' => 'Admin'],
        ];
    }

    /**
     * Caller-scoped power-rule snapshot (booleans for client gating).
     * Falls back to all-zero when unauthenticated.
     */
    private function buildPowerRules(): array
    {
        $user = Auth::user();
        if (!$user) {
            return [
                'brd_off_power'      => 0,
                'comment_mute_power' => 0,
                'kick_power'         => 0,
                'sceen_short_power'  => 0,
                'is_admin'           => 0,
                'is_bd_admin'        => 0,
            ];
        }
        return [
            'brd_off_power'      => (int) ($user->brd_off_power ?? 0),
            'comment_mute_power' => (int) ($user->comment_mute_power ?? 0),
            'kick_power'         => (int) ($user->kick_power ?? 0),
            'sceen_short_power'  => (int) ($user->sceen_short_power ?? 0),
            'is_admin'           => (int) ($user->is_admin ?? 0),
            'is_bd_admin'        => (int) ($user->is_bd_admin ?? 0),
        ];
    }

    private function buildLevelRules(): array
    {
        try {
            return Lavel::select('amount', 'update_lavel')
                ->orderBy('amount', 'asc')
                ->get()
                ->toArray();
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService buildLevelRules failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildGiftList(): array
    {
        try {
            $propulars = Cache::remember('gift_data_propulars', now()->addDays(2), function () {
                return GiftFile::where('category', 1)->orderBy('amount', 'asc')->get();
            });
            $luxerys = Cache::remember('gift_data_luxerys', now()->addDays(2), function () {
                return GiftFile::where('category', 2)->orderBy('amount', 'asc')->get();
            });
            $fastival = Cache::remember('gift_data_fastival', now()->addDays(2), function () {
                return GiftFile::where('category', 3)->orderBy('amount', 'asc')->get();
            });
            return [
                'propulars' => $propulars,
                'luxerys'   => $luxerys,
                'fastival'  => $fastival,
            ];
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService buildGiftList failed', ['error' => $e->getMessage()]);
            return ['propulars' => [], 'luxerys' => [], 'fastival' => []];
        }
    }

    private function buildStoreItems(): array
    {
        try {
            $entryEffects = Cache::remember('entry_effects_type_1', now()->addDays(10), function () {
                return EntryFrame::where('type', 1)->where('is_show', 1)->orderByDesc('id')->get();
            });
            $frameEffects = Cache::remember('frame_effects_type_0', now()->addDays(10), function () {
                return EntryFrame::where('type', 0)->where('is_show', 1)->orderByDesc('id')->get();
            });
            return [
                'entry_effects' => $entryEffects,
                'frame_effects' => $frameEffects,
            ];
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService buildStoreItems failed', ['error' => $e->getMessage()]);
            return ['entry_effects' => [], 'frame_effects' => []];
        }
    }

    private function buildBadwords(): array
    {
        try {
            return BedWord::select('word')->get()->pluck('word')->all();
        } catch (\Throwable $e) {
            Log::warning('MetaBootstrapService buildBadwords failed', ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function buildShortlinks(array $setting): array
    {
        return [
            'support'    => (string) (config('app.support_url') ?? 'https://queenlive.site/support'),
            'tos'        => (string) (config('app.tos_url') ?? 'https://queenlive.site/tos'),
            'privacy'    => (string) (config('app.privacy_url') ?? 'https://queenlive.site/privacy'),
            'play_store' => (string) (config('app.play_store_url') ?? 'https://play.google.com/store/apps/details?id=com.bdlive.app'),
            'app_store'  => (string) (config('app.app_store_url') ?? 'https://apps.apple.com/app/bdlive'),
        ];
    }
}
