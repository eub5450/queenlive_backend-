<?php

namespace App\Services\V5;

use App\Models\AudienceJoin;
use App\Models\Gift;
use App\Models\LiveCall;
use App\Models\User;
use App\Models\UserLive;
use App\Http\Controllers\Api\V5\AgoraController;
use App\RedisCache\RedisCache as RedisCacheFunction;
use App\Services\LiveKitService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;

/**
 * v5 composite room-entry assembly.
 *
 * compose() does the read-side aggregation:
 *   1. room metadata        (UserLive snapshot)
 *   2. host_board           (host + cohosts seat layout, balances, mute) —
 *                            same shape as v4 prepareCallDetails so the
 *                            client mapping stays identical
 *   3. user_board           (entering user's own profile slice)
 *   4. rtc                  (provider + Agora keys/token; LiveKit join for
 *                            audio/multi). Reuses the EXISTING token
 *                            issuance — never invents a new format.
 *   5. settings_diff        (omitted when If-Meta-Version matches)
 *   6. audience_count_only  (single fast counter for clients that only
 *                            want headcount)
 *
 * Defensive: uses Agent M's users.total_gifts_received_value column when
 * present (Schema::hasColumn check), falls back to the SUM(gifts.value)
 * monthly aggregate that v4 already computes.
 */
class RoomEntryService
{
    /** @var LiveKitService|null */
    protected $livekit;

    public function __construct()
    {
        // LiveKitService is bound in the container. Resolve lazily so a
        // missing/broken LiveKit config can't break Agora-only flows.
        try {
            $this->livekit = app(LiveKitService::class);
        } catch (\Throwable $e) {
            $this->livekit = null;
        }
    }

    public function compose(string $roomType, string $channel, $user, $clientMetaVersion): array
    {
        $hostLive = UserLive::where('channelName', $channel)->first();
        if (!$hostLive) {
            return [
                'ok' => false,
                'envelope_version' => 1,
                'error' => 'room_not_found',
                'code' => 404,
            ];
        }

        $hostId = (int) $hostLive->user_id;
        $hostData = RedisCacheFunction::UserfindById($hostId);

        $room = $this->buildRoom($roomType, $channel, $hostLive, $hostData);
        $hostBoard = $this->buildHostBoard($hostId, $channel, $hostLive, $hostData);
        $userBoard = $this->buildUserBoard($user, $hostId);
        $rtc = $this->buildRtc($roomType, $channel, $user, $hostId);

        $audienceCountOnly = $this->safeCount($channel);

        $envelope = [
            'ok' => true,
            'envelope_version' => 1,
            'room' => $room,
            'host_board' => $hostBoard,
            'user_board' => $userBoard,
            'rtc' => $rtc,
            'audience_count_only' => $audienceCountOnly,
            // top-bar mirror at the envelope root so the Flutter top bar can
            // populate from enter without digging into host_board.
            'host_balance'          => $hostBoard['host_balance'] ?? 0,
            'star'                  => $hostBoard['star'] ?? 0,
            'star_complete_parcent' => $hostBoard['star_complete_parcent'] ?? 0,
            'audience_count'        => $hostBoard['audience_count'] ?? $audienceCountOnly,
        ];

        // settings_diff is OPTIONAL — omitted when the client's known
        // meta_version still matches the current snapshot. This keeps the
        // payload minimal for the common warm-cache case.
        $settingsDiff = $this->buildSettingsDiff($clientMetaVersion);
        if ($settingsDiff !== null) {
            $envelope['settings_diff'] = $settingsDiff;
        }

        return $envelope;
    }

    // ---------------------------------------------------------------- pieces

    protected function buildRoom(string $roomType, string $channel, $hostLive, $hostData): array
    {
        return [
            'host_id'      => (string) ($hostLive->user_id ?? ''),
            'host_name'    => $hostData ? (string) ($hostData->name ?? '') : '',
            'channel_name' => $channel,
            'channelName'  => $channel,
            'type'         => $roomType,
            'is_locked'    => (int) ($hostLive->locked ?? 0),
            'mute'         => (int) ($hostLive->mute ?? 0),
            'site_number'  => (int) ($hostLive->siteNumber ?? ($hostLive->site_number ?? 8)),
            'start_at'     => $hostLive->created_at ? (string) $hostLive->created_at : null,
        ];
    }

    /**
     * Mirrors the prepareCallDetails shape from VideoBrdController so the
     * client's existing parser keeps working: host_list, co_host_list,
     * host_balance, star, star_complete_parcent — PLUS audience_count and
     * the snapshot timestamp.
     */
    protected function buildHostBoard(int $hostId, string $channel, $hostLive, $hostData): array
    {
        $start_date = date('Y-m') . '-01';
        $end_date   = date('Y-m') . '-31';

        if (!$hostData) {
            return [
                'host_list' => [],
                'co_host_list' => [],
                'host_balance' => 0,
                'star' => 0,
                'star_complete_parcent' => 0,
                'audience_count' => 0,
                'snapshot_updated_at' => (string) (int) round(microtime(true) * 1000),
            ];
        }

        // Host balance — prefer Agent M's denormalized monthly column when
        // it exists; otherwise compute via SUM(gifts.value).
        $hostMonthlyGift = $this->monthlyGiftFor($hostId, $start_date, $end_date);
        $hostBalance = ($hostData->previous_coin ?? 0) + $hostMonthlyGift;

        $host = [
            'channelName'    => $channel,
            'profile'        => $hostData->profile ?? '',
            'is_vip'         => $hostData->is_vip ?? 0,
            'balance'        => $hostBalance,
            'co_host_name'   => $hostData->name ?? '',
            'name'           => $hostData->name ?? '',
            'set_no'         => '0',
            'mute'           => $hostLive->mute ?? 0,
            'frame'          => (string) ($hostData->frame ?? ''),
            'co_host_id'     => (string) $hostId,
            'co_host_status' => 'Accept',
            'super_mute'     => '0',
            // mini-profile + Agora host uid for the top bar (host uid == user id)
            'agora_uid'      => (string) $hostId,
            'host_agora_uid' => (string) $hostId,
            'level'          => (int) ($hostData->level ?? 0),
        ];
        $list = [$host];
        $coHostList = [];

        $acceptList = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('status', 'Accept')
            ->get();

        if ($acceptList->isNotEmpty()) {
            $coHostIds = $acceptList->pluck('co_host_id')->unique();
            $coHosts = User::whereIn('id', $coHostIds)->get()->keyBy('id');

            $coHostGifts = Gift::where('channelName', $channel)
                ->whereIn('reciever_id', $coHostIds)
                ->groupBy('reciever_id')
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->pluck('total_value', 'reciever_id');

            foreach ($acceptList as $call) {
                $coHost = $coHosts->get($call->co_host_id);
                if (!$coHost) {
                    continue;
                }
                $row = [
                    'channelName'    => $channel,
                    'profile'        => $coHost->profile,
                    'is_vip'         => $coHost->is_vip,
                    'balance'        => $coHostGifts[$call->co_host_id] ?? 0,
                    'co_host_name'   => $coHost->name,
                    'set_no'         => (string) ($call->set_no ?? '0'),
                    'mute'           => $call->mute,
                    'frame'          => (string) $coHost->frame,
                    'co_host_id'     => (string) $call->co_host_id,
                    'co_host_status' => (string) $call->is_co_host_active,
                    'super_mute'     => (string) $call->super_mute,
                ];
                $list[] = $row;
                $coHostList[] = $row;
            }
        }

        $totalGiftSum = ($hostData->previous_coin ?? 0) + $hostMonthlyGift;

        $todayGift = Gift::where('reciever_id', $hostId)
            ->whereDate('date', now()->toDateString())
            ->sum('value');

        $levels = [
            [0, 50000, 1, 50000],
            [50000, 200000, 2, 200000],
            [200000, 500000, 3, 500000],
            [500000, 1000000, 4, 1000000],
            [1000000, 2000000, 5, 2000000],
            [2000000, PHP_INT_MAX, 5, 20000000],
        ];
        $star = 0;
        $nextLevel = 1;
        foreach ($levels as $lv) {
            if ($todayGift >= $lv[0] && $todayGift < $lv[1]) {
                $star = $lv[2];
                $nextLevel = $lv[3];
                break;
            }
        }
        $needPercent = $nextLevel > 0 ? intval(($todayGift / $nextLevel) * 100) : 0;

        return [
            'host_list'             => $list,
            'co_host_list'          => $coHostList,
            'host_balance'          => $totalGiftSum,
            'star'                  => $star,
            'star_complete_parcent' => $needPercent,
            'audience_count'        => $this->safeCount($channel),
            'snapshot_updated_at'   => (string) (int) round(microtime(true) * 1000),
        ];
    }

    /**
     * The entering user's own profile slice. Excludes data the client
     * already has — keeps only fields the room UI binds to (level, badges,
     * vip flags, frame, entry effect, balance month-to-date, follow
     * status against the host).
     */
    protected function buildUserBoard($user, int $hostId): array
    {
        if (!$user) {
            return [
                'is_guest' => true,
            ];
        }

        $u = RedisCacheFunction::UserfindById($user->id);
        if (!$u) {
            return [
                'is_guest' => false,
                'user_id'  => (string) $user->id,
            ];
        }

        $start_date = date('Y-m') . '-01';
        $end_date   = date('Y-m') . '-31';
        $balance = $this->monthlyGiftFor((int) $user->id, $start_date, $end_date);

        $followStatus = 0;
        if ((int) $user->id !== $hostId) {
            try {
                $isFollowing  = $u->following()->where('follower_id', $hostId)->exists();
                $isFollowedBy = $u->followers()->where('user_id', $hostId)->exists();
                $followStatus = ($isFollowing && $isFollowedBy) ? 2 : 1;
            } catch (\Throwable $e) {
                $followStatus = 0;
            }
        }

        return [
            'is_guest'       => false,
            'user_id'        => (string) $u->id,
            'level'          => (int) ($u->level ?? 0),
            'is_vip'         => (int) ($u->is_vip ?? 0),
            'is_official_id' => (int) ($u->is_official_id ?? 0),
            'is_agency'      => (int) ($u->is_agency ?? 0),
            'frame'          => (string) ($u->frame ?? ''),
            'entry_effect'   => (string) ($u->entry ?? ''),
            'comment_badge'  => (string) ($u->comment_badge ?? ''),
            'balance'        => $balance,
            'follow_status'  => $followStatus,
        ];
    }

    /**
     * Build the RTC block. Reuses AgoraController::GetToken and
     * LiveKitService::createAccessToken so the token shape is byte-for-byte
     * identical to v4.
     */
    protected function buildRtc(string $roomType, string $channel, $user, int $hostId): array
    {
        $setting = RedisCacheFunction::getSetting();
        $appId = $setting->appId ?? '';
        $appCert = $setting->appCertificate ?? '';
        $uid = $user ? (int) $user->id : $hostId;

        $agoraToken = '';
        try {
            $agoraToken = AgoraController::GetToken($uid, $appId, $appCert, $channel);
        } catch (\Throwable $e) {
            Log::error('v5/room.enter agora token failed', ['err' => $e->getMessage()]);
        }

        $rtc = [
            'provider' => 'agora',
            'agora' => [
                'appId'       => (string) $appId,
                'token'       => (string) $agoraToken,
                'channelName' => $channel,
                'uid'         => $uid,
            ],
        ];

        if ($roomType === 'audio' || $roomType === 'multi') {
            try {
                if ($this->livekit) {
                    $lkToken = $this->livekit->createAccessToken($channel, (string) $uid);
                    $rtc['livekit'] = [
                        'wsUrl' => (string) config('services.livekit.url', ''),
                        'token' => (string) $lkToken,
                    ];
                    // Per Agent P: audio rooms moved to LiveKit primary;
                    // Agora kept as fallback.
                    $rtc['provider'] = 'livekit';
                }
            } catch (\Throwable $e) {
                Log::error('v5/room.enter livekit token failed', ['err' => $e->getMessage()]);
            }
        }

        return $rtc;
    }

    /**
     * Settings diff — small set of room-affecting flags the client needs
     * (Agora keys are inside rtc.* already; this block is for non-RTC
     * settings like comment skip rules, lavel thresholds, etc).
     * Returns NULL when the client's If-Meta-Version matches.
     */
    protected function buildSettingsDiff(?string $clientMetaVersion): ?array
    {
        $setting = RedisCacheFunction::getSetting();
        if (!$setting) {
            return null;
        }

        $current = [
            // Keep this list narrow & stable — anything the room renderer
            // needs on entry but isn't worth a separate endpoint hit.
            'min_co_host_level' => (int) ($setting->min_co_host_level ?? 0),
            'gift_send_limit'   => (int) ($setting->gift_send_limit ?? 0),
            'is_video_room_on'  => (int) ($setting->is_video_room_on ?? 1),
            'is_audio_room_on'  => (int) ($setting->is_audio_room_on ?? 1),
            'is_multi_room_on'  => (int) ($setting->is_multi_room_on ?? 1),
            'site_number_max'   => (int) ($setting->site_number_max ?? 15),
        ];
        $currentVersion = substr(md5(json_encode($current)), 0, 12);

        if ($clientMetaVersion !== null && trim($clientMetaVersion) === $currentVersion) {
            return null; // client already up to date — omit the block
        }

        return [
            'meta_version' => $currentVersion,
            'values'       => $current,
        ];
    }

    // ---------------------------------------------------------------- utils

    protected function monthlyGiftFor(int $userId, string $start, string $end)
    {
        // Agent M planned to denormalize total_gifts_received_value
        // onto users — check defensively and fall back to SUM(gifts.value).
        try {
            if (Schema::hasColumn('users', 'total_gifts_received_value')) {
                $val = DB::table('users')->where('id', $userId)->value('total_gifts_received_value');
                if ($val !== null && $val !== '') {
                    return $val;
                }
            }
        } catch (\Throwable $e) {
            // fall through to SUM
        }

        return Gift::where('reciever_id', $userId)
            ->whereDate('date', '>=', $start)
            ->whereDate('date', '<=', $end)
            ->sum('value');
    }

    protected function safeCount(string $channel): int
    {
        try {
            return (int) AudienceJoin::where('channelName', $channel)->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }
}
