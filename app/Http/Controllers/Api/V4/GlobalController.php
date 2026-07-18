<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Models\UserLive;
use App\Models\AudienceJoin;
use App\Models\LiveCall;
use App\Models\Comment;
use App\Models\BanDevice;
use App\Models\Country;
use RedisCacheFunction;

class GlobalController extends Controller
{
    private string $prefix = 'queenlive:';

    private array $roomSelect = [
        'users.name', 'users.id', 'users.level', 'users.profile',
        'user_lives.token', 'user_lives.channelName', 'user_lives.type',
        'user_lives.backgorund', 'user_lives.notice', 'user_lives.bullet_notice',
        'user_lives.pin', 'user_lives.audio_brd_design', 'users.host_badge',
        'user_lives.avatar', 'user_lives.sdk', 'user_lives.appId',
        'user_lives.siteNumber',
    ];

    public function Index(Request $request)
    {
        $token   = $request->access_token;
        $user_id = $request->user_id;

        if ($token !== '0411f0028cfb768b3a3d96ac3aa37dw3e5') {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $user = RedisCacheFunction::UserfindById($user_id);
        if (!$user) {
            return response()->json([['message' => 'User Not Found', 'code' => '404']], 404);
        }

        // Bulk-delete requesting user stale live session (replaces N+1 foreach loop).
        $channelNames = UserLive::where('user_id', $user_id)->pluck('channelName');
        if ($channelNames->isNotEmpty()) {
            AudienceJoin::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            LiveCall::where('host_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            Comment::where('reciever_id', $user_id)->whereIn('channelName', $channelNames)->delete();
            UserLive::where('user_id', $user_id)->delete();
        }

        // Countries — cached 24 h (rarely changes).
        $cacheCountries = $this->prefix . 'global_countries';
        try {
            $raw = Redis::get($cacheCountries);
            $countrys = $raw ? unserialize($raw) : null;
            if (!$countrys) {
                $countrys = $this->buildCountryList();
                Redis::setex($cacheCountries, 86400, serialize($countrys));
            }
        } catch (\Throwable $e) {
            Log::error('GlobalController countries Redis failed', ['error' => $e->getMessage()]);
            $countrys = $this->buildCountryList();
        }

        // World lives — cached 60 s.
        $cacheLives = $this->prefix . 'global_lives_no_cert';
        try {
            $raw = Redis::get($cacheLives);
            $lives = $raw ? unserialize($raw) : null;
            if (!$lives) {
                $lives = DB::table('user_lives')
                    ->join('users', 'users.id', 'user_lives.user_id')
                    ->select($this->roomSelect)
                    ->orderBy('user_lives.type', 'desc')
                    ->orderBy('user_lives.top_value', 'desc')
                    ->get();
                Redis::setex($cacheLives, 60, serialize($lives));
            }
            $lives = $this->stripSensitiveLiveFields($lives);
        } catch (\Throwable $e) {
            Log::error('GlobalController lives Redis failed', ['error' => $e->getMessage()]);
            $lives = DB::table('user_lives')
                ->join('users', 'users.id', 'user_lives.user_id')
                ->select($this->roomSelect)
                ->orderBy('user_lives.type', 'desc')
                ->orderBy('user_lives.top_value', 'desc')
                ->get();
            $lives = $this->stripSensitiveLiveFields($lives);
        }

        return response()->json(
            [['message' => 'Global Data Successfully', 'lives_now' => $lives, 'countrys' => $countrys, 'code' => '200']],
            200, ['options' => JSON_UNESCAPED_UNICODE]
        );
    }

    public function CountryWiseData(Request $request)
    {
        $token      = $request->access_token;
        $user_id    = $request->user_id;
        $country_id = $request->country_id;

        if ($token !== '0411f0028cfb768b3a3d96ac3aa37dw3e5') {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401);
        }

        $user = RedisCacheFunction::UserfindById($user_id);
        if (!$user) {
            return response()->json([['message' => 'User Not Found', 'code' => '404']], 404);
        }

        $ban = BanDevice::where('device_id', $user->device_id)->first();
        if ($ban) {
            return response()->json([['message' => 'Your Device Banned', 'code' => '404']], 404);
        }

        $cid = (int) $country_id;
        $cacheKey = $this->prefix . "global_lives_country_no_cert_{$cid}";
        try {
            $raw = Redis::get($cacheKey);
            $lives = $raw ? unserialize($raw) : null;
            if (!$lives) {
                $q = DB::table('user_lives')
                    ->join('users', 'users.id', 'user_lives.user_id')
                    ->select($this->roomSelect);
                if ($cid > 0) {
                    $q->where('users.country_id', $cid);
                }
                $lives = $q->orderBy('user_lives.type', 'desc')->orderBy('user_lives.top_value', 'desc')->get();
                Redis::setex($cacheKey, 60, serialize($lives));
            }
            $lives = $this->stripSensitiveLiveFields($lives);
        } catch (\Throwable $e) {
            $q = DB::table('user_lives')
                ->join('users', 'users.id', 'user_lives.user_id')
                ->select($this->roomSelect);
            if ($cid > 0) {
                $q->where('users.country_id', $cid);
            }
            $lives = $q->orderBy('user_lives.type', 'desc')->orderBy('user_lives.top_value', 'desc')->get();
            $lives = $this->stripSensitiveLiveFields($lives);
        }

        return response()->json(
            [['message' => 'Global Data Successfully', 'lives_now' => $lives, 'code' => '200']],
            200, ['options' => JSON_UNESCAPED_UNICODE]
        );
    }

    private function buildCountryList(): array
    {
        $list = [['id' => '0', 'name' => 'All', 'flag' => 'store/country/all.png']];
        foreach (Country::all() as $c) {
            $list[] = ['id' => $c->id, 'name' => $c->name, 'flag' => $c->flag];
        }
        return $list;
    }

    private function stripSensitiveLiveFields($value)
    {
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                if (in_array($key, array('appCertificate', 'app_certificate', 'appCert'), true)) {
                    unset($value[$key]);
                    continue;
                }
                $value[$key] = $this->stripSensitiveLiveFields($item);
            }
            return $value;
        }

        if (is_object($value)) {
            unset($value->appCertificate, $value->app_certificate, $value->appCert);
            if (method_exists($value, 'getCollection') && method_exists($value, 'setCollection')) {
                $value->setCollection($value->getCollection()->map(function ($item) {
                    return $this->stripSensitiveLiveFields($item);
                }));
            }
        }

        return $value;
    }
}
