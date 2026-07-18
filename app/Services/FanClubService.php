<?php

namespace App\Services;

use App\Models\User;
use App\Services\V5\RoomBroadcastService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

/**
 * Fan Club / Guardian subscription — recurring revenue (Boss 2026-07-07).
 *
 * Tables (created via /tmp/fan_club_migration.sql — see repo):
 *   fan_club_subscriptions   one row per (subscriber, host, subscription cycle)
 *   fan_club_tier_config     admin-editable tier catalog (price + perks JSON)
 *
 * Money model: DEFERRED RENEWAL (option 1 in the codex handoff prompt).
 *   subscribe -> one debit -> row.status='active' with expires_at = now()+N.
 *   after expires_at:            status='grace' (perks still on).
 *   after expires_at + 3 days:   status='expired' (perks off, user re-taps
 *                                Renew to re-subscribe).
 *
 * Every write broadcasts `room.fanclub.updated` on the host's room channels
 * so audience clients update the badge counter live.
 */
class FanClubService
{
    private RoomBroadcastService $broadcast;

    public function __construct(RoomBroadcastService $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    /** True when the tables exist. Callers should degrade gracefully otherwise. */
    public function isEnabled(): bool
    {
        try {
            return Schema::hasTable('fan_club_subscriptions')
                && Schema::hasTable('fan_club_tier_config');
        } catch (Throwable $e) {
            return false;
        }
    }

    /** All enabled tiers sorted for the client tier-picker. */
    public function tiers(): array
    {
        if (!$this->isEnabled()) {
            return [];
        }
        $rows = DB::table('fan_club_tier_config')
            ->where('enabled', 1)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'tier' => (string) $row->tier,
                'price' => (int) $row->price,
                'duration_days' => (int) $row->duration_days,
                'perks' => $this->decodePerks($row->perks_json),
                'sort_order' => (int) $row->sort_order,
            ];
        }
        return $out;
    }

    /**
     * Subscribe (or extend) a Fan Club membership.
     *
     * Rate-limited to 1 subscribe / user / 3 s to protect against
     * double-tap double-debit.
     */
    public function subscribe(string $subscriberId, string $hostId, string $tier): array
    {
        if (!$this->isEnabled()) {
            throw new InvalidArgumentException('fanclub_disabled');
        }
        $subscriberId = trim($subscriberId);
        $hostId = trim($hostId);
        $tier = strtolower(trim($tier));
        if ($subscriberId === '' || $hostId === '' || $tier === '') {
            throw new InvalidArgumentException('missing_required_fields');
        }
        if ($subscriberId === $hostId) {
            throw new InvalidArgumentException('cannot_subscribe_to_self');
        }

        // Tier lookup BEFORE the rate-limit gate so a bad-tier request
        // fails fast without locking the user out of a valid retry for 3s.
        $tierRow = DB::table('fan_club_tier_config')
            ->where('tier', $tier)
            ->where('enabled', 1)
            ->first();
        if (!$tierRow) {
            throw new InvalidArgumentException('unknown_tier');
        }
        $price = (int) $tierRow->price;
        $durationDays = (int) $tierRow->duration_days;

        // Rate limit after validation — protects the actual money path.
        $rateKey = 'queenlive:fanclub:sub_rate:' . $subscriberId;
        if (!Redis::setnx($rateKey, (string) time())) {
            return [
                'ok' => false,
                'code' => '429',
                'error' => 'rate_limited',
                'message' => 'Please wait before subscribing again.',
            ];
        }
        Redis::expire($rateKey, 3);

        return DB::transaction(function () use ($subscriberId, $hostId, $tier, $tierRow, $price, $durationDays) {
            $sub = User::where('id', $subscriberId)->lockForUpdate()->first();
            if (!$sub) {
                throw new InvalidArgumentException('sender_not_found');
            }
            $host = User::where('id', $hostId)->first();
            if (!$host) {
                throw new InvalidArgumentException('host_not_found');
            }
            if ((int) $sub->balance < $price) {
                return [
                    'ok' => false,
                    'code' => '402',
                    'error' => 'low_balance',
                    'message' => 'low_balance',
                    'balance' => (int) $sub->balance,
                ];
            }

            $now = CarbonImmutable::now();

            // If the (subscriber, host) already has an active/grace row, extend
            // it: mark existing as expired, new row references it via
            // renewed_from_id. This is the same shape as the /renew endpoint.
            $existing = DB::table('fan_club_subscriptions')
                ->where('subscriber_id', $subscriberId)
                ->where('host_id', $hostId)
                ->whereIn('status', ['active', 'grace'])
                ->lockForUpdate()
                ->first();

            $renewedFromId = null;
            if ($existing) {
                DB::table('fan_club_subscriptions')
                    ->where('id', $existing->id)
                    ->update(['status' => 'expired', 'updated_at' => $now]);
                $renewedFromId = (int) $existing->id;
            }

            $sub->balance = (int) $sub->balance - $price;
            $sub->save();

            $rowId = DB::table('fan_club_subscriptions')->insertGetId([
                'subscriber_id' => $subscriberId,
                'host_id' => $hostId,
                'tier' => $tier,
                'status' => 'active',
                'started_at' => $now,
                'expires_at' => $now->addDays($durationDays),
                'renewed_from_id' => $renewedFromId,
                'last_debit_amount' => $price,
                'last_debit_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $expiresAt = $now->addDays($durationDays);
            $envelope = $this->broadcastForHost($hostId, [
                'action' => 'subscribed',
                'subscription_id' => $rowId,
                'subscriber_id' => (string) $subscriberId,
                'host_id' => (string) $hostId,
                'tier' => $tier,
                'perks' => $this->decodePerks($tierRow->perks_json),
                'expires_at' => $expiresAt->toIso8601String(),
            ]);

            return [
                'ok' => true,
                'code' => '200',
                'message' => 'Fan Club subscribed',
                'subscription_id' => $rowId,
                'tier' => $tier,
                'expires_at' => $expiresAt->toIso8601String(),
                'balance' => (int) $sub->balance,
                'envelope_id' => $envelope['event_id'] ?? null,
            ];
        });
    }

    /** List subscriptions owned by a user (active + grace). */
    public function mine(string $subscriberId): array
    {
        if (!$this->isEnabled()) {
            return ['subscriptions' => []];
        }
        $rows = DB::table('fan_club_subscriptions AS s')
            ->leftJoin('fan_club_tier_config AS c', 's.tier', '=', 'c.tier')
            ->leftJoin('users AS u', 's.host_id', '=', 'u.id')
            ->where('s.subscriber_id', $subscriberId)
            ->whereIn('s.status', ['active', 'grace'])
            ->orderByDesc('s.expires_at')
            ->select([
                's.id', 's.host_id', 's.tier', 's.status',
                's.started_at', 's.expires_at',
                'c.perks_json', 'c.price', 'c.duration_days',
                'u.name AS host_name', 'u.profile AS host_profile', 'u.level AS host_level',
            ])
            ->get();
        $out = [];
        foreach ($rows as $row) {
            $out[] = [
                'subscription_id' => (int) $row->id,
                'host_id' => (string) $row->host_id,
                'host_name' => (string) ($row->host_name ?? ''),
                'host_profile' => (string) ($row->host_profile ?? ''),
                'host_level' => (int) ($row->host_level ?? 0),
                'tier' => (string) $row->tier,
                'status' => (string) $row->status,
                'started_at' => $row->started_at,
                'expires_at' => $row->expires_at,
                'perks' => $this->decodePerks($row->perks_json),
            ];
        }
        return ['subscriptions' => $out];
    }

    /** Fan Club summary for a host: subscriber count per tier + viewer's own row. */
    public function host(string $hostId, ?string $viewerId): array
    {
        if (!$this->isEnabled()) {
            return ['host_id' => $hostId, 'counts_by_tier' => [], 'my_status' => null];
        }
        $counts = DB::table('fan_club_subscriptions')
            ->where('host_id', $hostId)
            ->whereIn('status', ['active', 'grace'])
            ->groupBy('tier')
            ->select('tier', DB::raw('COUNT(*) AS c'))
            ->get()
            ->pluck('c', 'tier')
            ->toArray();
        $mine = null;
        if ($viewerId && $viewerId !== $hostId) {
            $row = DB::table('fan_club_subscriptions AS s')
                ->leftJoin('fan_club_tier_config AS c', 's.tier', '=', 'c.tier')
                ->where('s.subscriber_id', $viewerId)
                ->where('s.host_id', $hostId)
                ->whereIn('s.status', ['active', 'grace'])
                ->select(['s.id', 's.tier', 's.status', 's.expires_at', 'c.perks_json'])
                ->orderByDesc('s.expires_at')
                ->first();
            if ($row) {
                $mine = [
                    'subscription_id' => (int) $row->id,
                    'tier' => (string) $row->tier,
                    'status' => (string) $row->status,
                    'expires_at' => $row->expires_at,
                    'perks' => $this->decodePerks($row->perks_json),
                ];
            }
        }
        return [
            'host_id' => (string) $hostId,
            'counts_by_tier' => (object) $counts,
            'my_status' => $mine,
        ];
    }

    /** Alias for subscribe(same_tier) so client can call a semantic endpoint. */
    public function renew(string $subscriberId, string $subscriptionId): array
    {
        if (!$this->isEnabled()) {
            throw new InvalidArgumentException('fanclub_disabled');
        }
        $row = DB::table('fan_club_subscriptions')
            ->where('id', $subscriptionId)
            ->where('subscriber_id', $subscriberId)
            ->first();
        if (!$row) {
            throw new InvalidArgumentException('subscription_not_found');
        }
        return $this->subscribe($subscriberId, (string) $row->host_id, (string) $row->tier);
    }

    /** Cancel a subscription. Perks stay on until expires_at. */
    public function cancel(string $subscriberId, string $subscriptionId): array
    {
        if (!$this->isEnabled()) {
            throw new InvalidArgumentException('fanclub_disabled');
        }
        $affected = DB::table('fan_club_subscriptions')
            ->where('id', $subscriptionId)
            ->where('subscriber_id', $subscriberId)
            ->whereIn('status', ['active', 'grace'])
            ->update([
                'status' => 'canceled',
                'updated_at' => CarbonImmutable::now(),
            ]);
        if ($affected === 0) {
            throw new InvalidArgumentException('subscription_not_found');
        }
        $row = DB::table('fan_club_subscriptions')->where('id', $subscriptionId)->first();
        if ($row) {
            $this->broadcastForHost((string) $row->host_id, [
                'action' => 'canceled',
                'subscription_id' => (int) $row->id,
                'subscriber_id' => (string) $row->subscriber_id,
                'host_id' => (string) $row->host_id,
                'tier' => (string) $row->tier,
            ]);
        }
        return ['ok' => true, 'code' => '200', 'message' => 'canceled'];
    }

    /**
     * Cron: sweep active rows past expiry to grace, and grace past grace to
     * expired. Every row transition also broadcasts so client badge counters
     * refresh without a full re-fetch.
     */
    public function sweepExpirations(): array
    {
        if (!$this->isEnabled()) {
            return ['ok' => true, 'transitions' => 0];
        }
        $now = CarbonImmutable::now();
        $graceCutoff = $now->subDays(3);
        $activeToGrace = DB::table('fan_club_subscriptions')
            ->where('status', 'active')
            ->where('expires_at', '<', $now)
            ->update(['status' => 'grace', 'updated_at' => $now]);
        $graceToExpired = DB::table('fan_club_subscriptions')
            ->where('status', 'grace')
            ->where('expires_at', '<', $graceCutoff)
            ->update(['status' => 'expired', 'updated_at' => $now]);
        return [
            'ok' => true,
            'active_to_grace' => (int) $activeToGrace,
            'grace_to_expired' => (int) $graceToExpired,
        ];
    }

    private function decodePerks(?string $json): array
    {
        if (!$json || $json === '') {
            return [];
        }
        $parsed = json_decode($json, true);
        return is_array($parsed) ? $parsed : [];
    }

    /**
     * Broadcast on all three room channels for the host — audio + video +
     * multi — so no matter which live surface the host is on the client
     * gets the update. RoomBroadcastService is the same fan-out used by
     * seat + gift updates.
     */
    private function broadcastForHost(string $hostId, array $payload): array
    {
        $envelopeId = null;
        try {
            $liveRow = DB::table('user_lives')
                ->where('user_id', $hostId)
                ->orderByDesc('id')
                ->first();
            $channel = $liveRow->channelName ?? '';
            if ($channel !== '') {
                foreach (['audio', 'video', 'multi'] as $roomType) {
                    $env = $this->broadcast->broadcast(
                        $roomType,
                        (string) $channel,
                        (string) $hostId,
                        'room.fanclub.updated',
                        $payload,
                        [
                            'actor_user_id' => (string) ($payload['subscriber_id'] ?? ''),
                            'target_user_id' => (string) $hostId,
                        ]
                    );
                    $envelopeId = $env['event_id'] ?? $envelopeId;
                }
            }
        } catch (Throwable $e) {
            // Broadcast failure must not abort the DB commit — the row is
            // authoritative and audience clients pull `mine`/`host` lazily.
        }
        return ['event_id' => $envelopeId];
    }
}
