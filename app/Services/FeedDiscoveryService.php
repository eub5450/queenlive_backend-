<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Smarter Feed / Discovery Rows (feature #9, Boss 2026-07-07).
 *
 * Builds the sectioned home-feed rows the client renders above the flat grid:
 *
 *   following_live  rooms hosted by users the viewer follows, live right now
 *   trending        ranked by recent gift velocity + viewers + freshness decay
 *   new_hosts       hosts whose account was created in the last 14 days, live
 *   nearby          geo-proximity — only when the request carries lat/lng
 *
 * Every room is deduped across sections: a room may appear in AT MOST ONE
 * section, resolved in the priority order following_live > trending >
 * new_hosts > nearby.
 *
 * Table facts (verified against the live app controllers, NOT invented):
 *   user_lives  user_id, channelName, name, type[1=audio 2=video 3=multi],
 *               notice, siteNumber, created_at, appId, appCertificate
 *   users       id, name, profile, level, balance, created_at
 *   followers   user_id (the follower/viewer), follower_id (the followed host)
 *   gifts       sander_id, reciever_id (host), value, date, channelName
 *
 * Every optional signal (gifts, geo columns) is gated behind Schema checks so
 * the endpoint degrades gracefully on any schema the confirmed tables miss.
 */
class FeedDiscoveryService
{
    /** How many rooms each row returns at most. */
    private const ROW_LIMIT = 20;

    /** Recent-gift window for the trending velocity signal, in minutes. */
    private const GIFT_WINDOW_MINUTES = 15;

    /** New-host account-age window, in days. */
    private const NEW_HOST_DAYS = 14;

    /** Nearby radius in kilometres when geo columns are available. */
    private const NEARBY_RADIUS_KM = 150.0;

    /**
     * Build the ordered discovery sections for a viewer.
     *
     * @param string     $viewerId  authed user id (may be '' for guests)
     * @param float|null $lat       viewer latitude, only when permission granted
     * @param float|null $lng       viewer longitude, only when permission granted
     *
     * @return array<int,array<string,mixed>>
     */
    public function sections(string $viewerId, ?float $lat, ?float $lng): array
    {
        $viewerId = trim($viewerId);

        // seenHostIds guarantees a room appears in exactly one section. Priority
        // is the order the rows are computed below.
        $seenHostIds = [];

        $following = $this->followingLive($viewerId, $seenHostIds);
        $trending  = $this->trending($seenHostIds);
        $newHosts  = $this->newHosts($seenHostIds);
        $nearby    = $this->nearby($lat, $lng, $seenHostIds);

        return [
            [
                'key' => 'following_live',
                'title' => 'Following · Live now',
                'rooms' => $following,
            ],
            [
                'key' => 'trending',
                'title' => 'Trending',
                'rooms' => $trending,
            ],
            [
                'key' => 'new_hosts',
                'title' => 'New hosts',
                'rooms' => $newHosts,
            ],
            [
                'key' => 'nearby',
                'title' => 'Nearby',
                'rooms' => $nearby,
            ],
        ];
    }

    /** Rooms hosted by users the viewer follows, live right now. */
    private function followingLive(string $viewerId, array &$seenHostIds): array
    {
        if ($viewerId === '' || !Schema::hasTable('followers')) {
            return [];
        }
        try {
            $rows = $this->baseLiveQuery()
                ->join('followers', 'followers.follower_id', '=', 'user_lives.user_id')
                ->where('followers.user_id', $viewerId)
                ->limit(self::ROW_LIMIT * 2)
                ->get();
        } catch (Throwable $e) {
            return [];
        }
        return $this->collectRooms($rows, $seenHostIds);
    }

    /**
     * Trending: rank by a composite score.
     *   score = recentGiftValue (last 15 min, if confirmable)
     *         + viewers
     *         + freshnessBonus (newer rooms surface higher, decayed)
     * When the gift table/columns are unconfirmable, rank on viewers + freshness
     * only. Ranking is done in PHP after a single query so we never depend on a
     * gift-table JOIN succeeding.
     */
    private function trending(array &$seenHostIds): array
    {
        try {
            $rows = $this->baseLiveQuery()
                ->limit(120)
                ->get();
        } catch (Throwable $e) {
            return [];
        }
        if ($rows->isEmpty()) {
            return [];
        }

        $giftByHost = $this->recentGiftValueByHost(
            $rows->pluck('user_id')->all()
        );

        $now = CarbonImmutable::now();
        $scored = [];
        foreach ($rows as $row) {
            $hostId = (string) $row->user_id;
            $viewers = $this->viewerCount($row);
            $gift = (float) ($giftByHost[$hostId] ?? 0.0);
            $freshness = $this->freshnessBonus($row->created_at ?? null, $now);
            // Weight gift velocity highest — it is the strongest revenue signal.
            $score = ($gift * 0.05) + ($viewers * 1.0) + $freshness;
            $scored[] = ['row' => $row, 'score' => $score];
        }
        usort($scored, static function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        $ordered = array_map(static fn ($e) => $e['row'], $scored);
        return $this->collectRooms($ordered, $seenHostIds);
    }

    /** Hosts whose account was created within the last 14 days and are live. */
    private function newHosts(array &$seenHostIds): array
    {
        if (!Schema::hasColumn('users', 'created_at')) {
            return [];
        }
        try {
            $cutoff = CarbonImmutable::now()->subDays(self::NEW_HOST_DAYS);
            $rows = $this->baseLiveQuery()
                ->where('users.created_at', '>=', $cutoff)
                ->orderByDesc('users.created_at')
                ->limit(self::ROW_LIMIT * 2)
                ->get();
        } catch (Throwable $e) {
            return [];
        }
        return $this->collectRooms($rows, $seenHostIds);
    }

    /**
     * Nearby: only when the request carries lat/lng AND the users table exposes
     * latitude/longitude columns. Otherwise returns an empty list and the
     * client omits the row entirely (it never prompts for permission).
     */
    private function nearby(?float $lat, ?float $lng, array &$seenHostIds): array
    {
        if ($lat === null || $lng === null) {
            return [];
        }
        $latCol = $this->firstExistingColumn('users', ['latitude', 'lat', 'geo_lat']);
        $lngCol = $this->firstExistingColumn('users', ['longitude', 'lng', 'lon', 'geo_lng']);
        if ($latCol === null || $lngCol === null) {
            return [];
        }
        try {
            // Haversine great-circle distance, computed in SQL. 6371 km = Earth
            // radius. Bind lat/lng as floats; the columns were resolved from a
            // fixed allow-list above so they are safe to interpolate.
            $distanceExpr = "(6371 * acos(least(1, cos(radians(?)) * cos(radians(users.$latCol)) "
                . "* cos(radians(users.$lngCol) - radians(?)) + sin(radians(?)) "
                . "* sin(radians(users.$latCol)))))";
            $rows = $this->baseLiveQuery()
                ->whereNotNull("users.$latCol")
                ->whereNotNull("users.$lngCol")
                ->selectRaw("$distanceExpr AS distance_km", [$lat, $lng, $lat])
                ->having('distance_km', '<=', self::NEARBY_RADIUS_KM)
                ->orderBy('distance_km')
                ->limit(self::ROW_LIMIT * 2)
                ->get();
        } catch (Throwable $e) {
            return [];
        }
        return $this->collectRooms($rows, $seenHostIds);
    }

    // ---- shared query + shaping ----

    /**
     * Base query over live rooms joined to their host user. Selects exactly the
     * fields the client LiveNowData model reads so rooms deserialize with the
     * SAME model the flat feed already consumes.
     */
    private function baseLiveQuery()
    {
        $query = DB::table('user_lives')
            ->join('users', 'users.id', '=', 'user_lives.user_id');

        $select = [
            'user_lives.user_id AS id',
            'users.name AS name',
            'users.profile AS profile',
            'users.level AS level',
            'user_lives.channelName AS channelName',
            'user_lives.type AS type',
            'user_lives.notice AS notice',
            'user_lives.siteNumber AS siteNumber',
            'user_lives.created_at AS created_at',
        ];
        // appId / appCertificate live on user_lives in this schema; include them
        // when present so the client can reuse the same join path.
        if (Schema::hasColumn('user_lives', 'appId')) {
            $select[] = 'user_lives.appId AS appId';
        }
        if (Schema::hasColumn('user_lives', 'appCertificate')) {
            $select[] = 'user_lives.appCertificate AS appCertificate';
        }
        foreach (['bullet_notice', 'pin', 'audio_brd_design', 'host_badge'] as $extra) {
            if (Schema::hasColumn('user_lives', $extra)) {
                $select[] = "user_lives.$extra AS $extra";
            }
        }

        return $query->select($select);
    }

    /**
     * Turn query rows into client room maps, deduped by host id across sections.
     * Mutates $seenHostIds so later sections skip rooms an earlier one claimed.
     */
    private function collectRooms($rows, array &$seenHostIds): array
    {
        $out = [];
        foreach ($rows as $row) {
            $hostId = (string) ($row->id ?? '');
            $channel = (string) ($row->channelName ?? '');
            if ($hostId === '' || $channel === '') {
                continue;
            }
            if (isset($seenHostIds[$hostId])) {
                continue;
            }
            $seenHostIds[$hostId] = true;
            $out[] = $this->shapeRoom($row);
            if (count($out) >= self::ROW_LIMIT) {
                break;
            }
        }
        return $out;
    }

    /** Shape a single row into the client feed room contract. */
    private function shapeRoom($row): array
    {
        $room = [
            'id' => (int) $row->id,
            'name' => (string) ($row->name ?? ''),
            'profile' => (string) ($row->profile ?? ''),
            'level' => (string) ($row->level ?? '0'),
            'channelName' => (string) ($row->channelName ?? ''),
            'type' => (string) ($row->type ?? '1'),
            'notice' => (string) ($row->notice ?? ''),
            'siteNumber' => (int) ($row->siteNumber ?? 8),
            'viewer' => $this->viewerCount($row),
        ];
        if (isset($row->appId)) {
            $room['appId'] = (string) $row->appId;
        }
        if (isset($row->appCertificate)) {
            $room['appCertificate'] = (string) $row->appCertificate;
        }
        foreach (['bullet_notice', 'pin', 'audio_brd_design', 'host_badge'] as $extra) {
            if (isset($row->$extra)) {
                $room[$extra] = (string) $row->$extra;
            }
        }
        return $room;
    }

    // ---- signals ----

    /**
     * Recent gift value per host id over the last GIFT_WINDOW_MINUTES.
     *
     * Prefers a real created_at window; falls back to gifts.date >= today when
     * only a DATE column exists; returns [] (no gift signal) when the gifts
     * table or its columns are unconfirmable, so trending still ranks on
     * viewers + freshness.
     *
     * @param array<int,mixed> $hostIds
     * @return array<string,float>
     */
    private function recentGiftValueByHost(array $hostIds): array
    {
        $hostIds = array_values(array_unique(array_filter(array_map('strval', $hostIds), static fn ($v) => $v !== '')));
        if (empty($hostIds) || !Schema::hasTable('gifts')) {
            return [];
        }
        if (!Schema::hasColumn('gifts', 'reciever_id') || !Schema::hasColumn('gifts', 'value')) {
            return [];
        }
        try {
            $query = DB::table('gifts')
                ->whereIn('reciever_id', $hostIds)
                ->select('reciever_id', DB::raw('SUM(value) AS gift_value'))
                ->groupBy('reciever_id');

            if (Schema::hasColumn('gifts', 'created_at')) {
                $query->where('created_at', '>=', CarbonImmutable::now()->subMinutes(self::GIFT_WINDOW_MINUTES));
            } elseif (Schema::hasColumn('gifts', 'date')) {
                // DATE-only column: coarse "today" freshness proxy.
                $query->whereDate('date', '>=', CarbonImmutable::now()->toDateString());
            } else {
                return [];
            }

            $out = [];
            foreach ($query->get() as $row) {
                $out[(string) $row->reciever_id] = (float) $row->gift_value;
            }
            return $out;
        } catch (Throwable $e) {
            return [];
        }
    }

    /** Best-effort viewer count from whatever the row exposes. */
    private function viewerCount($row): int
    {
        foreach (['viewer', 'viewers', 'viewCount', 'view_count', 'totalView', 'total_view'] as $key) {
            if (isset($row->$key) && is_numeric($row->$key)) {
                return (int) $row->$key;
            }
        }
        return 0;
    }

    /**
     * Freshness bonus: newer rooms score higher, decaying to ~0 over 6 hours.
     * Returns 0 when the timestamp is missing/unparseable.
     */
    private function freshnessBonus($createdAt, CarbonImmutable $now): float
    {
        if ($createdAt === null || $createdAt === '') {
            return 0.0;
        }
        try {
            $created = CarbonImmutable::parse((string) $createdAt);
        } catch (Throwable $e) {
            return 0.0;
        }
        $ageMinutes = max(0, $now->diffInMinutes($created));
        // Linear decay over 360 min, weighted so a brand-new room is worth ~12
        // viewers of head-start and old rooms contribute nothing.
        $decay = max(0.0, 1.0 - ($ageMinutes / 360.0));
        return $decay * 12.0;
    }

    private function firstExistingColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            try {
                if (Schema::hasColumn($table, $col)) {
                    return $col;
                }
            } catch (Throwable $e) {
                return null;
            }
        }
        return null;
    }
}
