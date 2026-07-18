<?php

namespace App\Services\V5;

use App\Models\AudienceJoin;
use App\Models\BrdAdmin;
use App\Models\Comment;
use App\Models\CommentMute;
use App\Models\Gift;
use App\Models\Kick;
use App\Models\Lavel;
use App\Models\LiveCall;
use App\Models\User;
use App\Models\UserLive;
use App\Services\V5\Adapters\AdapterInterface;
use App\Services\V5\Adapters\AudioAdapter;
use App\Services\V5\Adapters\MultiAdapter;
use App\Services\V5\Adapters\VideoAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

/**
 * V5 shared room action service.
 *
 * Single entry point for room actions (cohost / comment / gift / admin / ...)
 * across audio + video + multi rooms. Validates inputs, performs the DB write
 * with existing Eloquent models, dispatches the v5 envelope via
 * RoomBroadcastService, and delegates room-type-specific tail-work (seat
 * layout, RTC tokens) to the matching adapter under Services\V5\Adapters.
 *
 * The v5 controllers (Agent B) should call into this service and not touch
 * LiveCall / Comment / Gift directly so the envelope contract stays in one
 * place.
 */
class RoomActionService
{
    /** @var RoomBroadcastService */
    protected $broadcast;

    /** Minimum level required to request cohost, per room type. */
    const COHOST_MIN_LEVEL = [
        'audio' => 2,
        'video' => 2,
        'multi' => 2,
    ];

    public function __construct(RoomBroadcastService $broadcast)
    {
        $this->broadcast = $broadcast;
    }

    /* ============================================================
     * Cohost actions
     * ============================================================ */

    public function requestCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $live = $this->mustLoadLive($channel, $hostId);
        if (!$live['ok']) {
            return $live;
        }
        $userLive = $live['user_live'];

        $userId = $this->stringId($body['user_id'] ?? null);
        if ($userId === '') {
            return ['ok' => false, 'error' => 'user_id_required'];
        }

        // Pre-conditions
        $user = User::where('id', $userId)->first();
        if (!$user) {
            return ['ok' => false, 'error' => 'user_not_found'];
        }

        $minLevel = self::COHOST_MIN_LEVEL[$roomType] ?? 0;
        $userLevel = $this->resolveUserLevel($userId);
        if (!$this->isOfficialOrAppAdmin($user) && $userLevel < $minLevel) {
            return [
                'ok' => false,
                'error' => 'level_too_low',
                'message' => 'You need level 2 to request a call',
                'level' => $userLevel,
                'min' => $minLevel,
            ];
        }
        if ((int) ($user->is_invisible_active ?? 0) === 1) {
            return ['ok' => false, 'error' => 'user_invisible'];
        }

        // Already cohost in this room. Treat this as idempotent recovery, not a
        // hard failure: a client can lose local state after a reconnect/app kill
        // while the authoritative Accept row is still valid.
        $existingAccept = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $userId)
            ->where('channelName', $channel)
            ->where('is_co_host_active', 'Accept')
            ->first();
        if ($existingAccept) {
            $envelope = $this->broadcast->broadcast(
                $roomType,
                $channel,
                $hostId,
                'room.cohost.accepted',
                [
                    'call_id'    => $existingAccept->id,
                    'co_host_id' => $userId,
                    'user_id'    => $userId,
                    'set_no'     => $existingAccept->set_no,
                    'mute'       => (int) ($existingAccept->mute ?? 1),
                    'super_mute' => (int) ($existingAccept->super_mute ?? 0),
                    'recovered'  => 1,
                ],
                ['actor_user_id' => $userId, 'target_user_id' => $userId]
            );

            return ['ok' => true, 'envelope' => $envelope, 'already_cohost' => true];
        }

        // Cohost in another room? A user can only be a cohost in ONE room at a
        // time, but requesting a seat HERE means they have moved on from the
        // other room — so RELEASE the other membership instead of hard-blocking.
        // (Boss 2026-07-10) The old `cohost_in_other_room` 409 permanently
        // stranded any user whose previous cohost seat was never cleaned up:
        // leaving a room / swiping the room reel / the app being killed does not
        // reliably cut the seat server-side, so the stale-but-"Accept" row (even
        // for a still-live room they already left) blocked every future request.
        // Auto-releasing here is safe: one RTC session at a time means they are
        // no longer present in the other room.
        $otherRooms = LiveCall::where('co_host_id', $userId)
            ->where('is_co_host_active', 'Accept')
            ->where('channelName', '<>', $channel)
            ->get();
        foreach ($otherRooms as $otherRoom) {
            LiveCall::where('id', $otherRoom->id)->update([
                'is_co_host_active' => 'Left',
                'status'            => 'Left',
                'updated_at'        => now(),
            ]);

            // Boss 2026-07-10: broadcast the cut to the OTHER room too so the
            // seat the user just left CLEARS INSTANTLY and shows a leave message
            // for everyone still there, instead of a ghost seat lingering until
            // the next full snapshot. Makes "instant call cut + leave message on
            // room-swap" reliable regardless of whether the leaving client got
            // to fire its own dispose-time cut (app kill, fast reel swipe, drop).
            $otherType = $otherRoom->type == 2 ? 'video'
                : ($otherRoom->type == 3 ? 'multi' : 'audio');
            $otherChannel = (string) $otherRoom->channelName;
            $otherHostId = (string) $otherRoom->host_id;
            if ($otherChannel !== '' && $otherHostId !== '') {
                try {
                    $this->broadcast->broadcast(
                        $otherType,
                        $otherChannel,
                        $otherHostId,
                        'room.cohost.cut',
                        ['co_host_id' => $userId, 'user_id' => $userId, 'auto_release' => 1],
                        ['actor_user_id' => $userId, 'target_user_id' => $userId]
                    );
                } catch (\Throwable $t) {
                    Log::warning('V5 requestCohost auto-release cut broadcast failed', [
                        'channel' => $otherChannel,
                        'error'   => $t->getMessage(),
                    ]);
                }
                $priorOtherSeat = $otherRoom->set_no;
                if ($priorOtherSeat !== null && (int) $priorOtherSeat > 0) {
                    try {
                        app(RoomSeatUpdateService::class)->emitSeatChange(
                            $otherType,
                            $otherChannel,
                            (int) $priorOtherSeat,
                            [
                                'cleared'    => true,
                                'co_host_id' => (string) $userId,
                                'host_id'    => $otherHostId,
                            ],
                            (string) $userId
                        );
                    } catch (\Throwable $t) {
                        Log::warning('V5 requestCohost auto-release seat-delta failed', [
                            'channel' => $otherChannel,
                            'seat_no' => $priorOtherSeat,
                            'error'   => $t->getMessage(),
                        ]);
                    }
                }
            }
        }

        // Kicked?
        $kicked = Kick::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('user_id', $userId)
            ->first();
        if ($kicked) {
            return ['ok' => false, 'error' => 'user_kicked'];
        }

        // Idempotent insert: drop any prior row for this (host, co_host, channel)
        // regardless of status, then insert one fresh pending row.
        LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $userId)
            ->where('channelName', $channel)
            ->delete();

        $requestedSetNo = $this->normalizeSeatNo($body['set_no'] ?? null);
        $pendingSetNo = $requestedSetNo ?: '0';

        $call = LiveCall::create([
            'host_id'           => $hostId,
            'co_host_id'        => $userId,
            'channelName'       => $channel,
            'status'            => 'pending',
            'is_co_host_active' => 'pending',
            'type'              => $this->roomTypeToInt($roomType),
            'set_no'            => $pendingSetNo,
        ]);

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.requested',
            [
                'call_id'    => $call->id,
                'co_host_id' => $userId,
                'user_id'    => $userId,
                'set_no'     => $pendingSetNo,
                'name'       => $user->name,
                'profile'    => $user->profile ?? null,
                'level'      => $userLevel,
                'is_vip'     => (int) ($user->is_vip ?? 0),
                'frame'      => $user->frame ?? null,
            ],
            ['actor_user_id' => $userId, 'target_user_id' => $hostId]
        );

        $this->dispatchAdapter($roomType, 'requestCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope, 'call_id' => $call->id];
    }

    public function acceptCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $live = $this->mustLoadLive($channel, $hostId);
        if (!$live['ok']) {
            return $live;
        }

        $coHostId = $this->stringId($body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($coHostId === '') {
            return ['ok' => false, 'error' => 'co_host_id_required'];
        }
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        $preserveMute = isset($body['preserve_mute']) && $body['preserve_mute'];
        $requestedSetNo = $this->normalizeSeatNo($body['set_no'] ?? null);
        $userLive = $live['user_live'];
        $selfAcceptUnlockedSeat = $this->canSelfAcceptUnlockedSeat(
            $roomType,
            $channel,
            $hostId,
            $actorId,
            $coHostId,
            $requestedSetNo,
            $userLive
        );
        if (!$selfAcceptUnlockedSeat && !$this->canManageCohost($hostId, $actorId, $coHostId, 'accept')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $call = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('is_co_host_active', 'pending')
                    ->orWhere('status', 'Request')
                    ->orWhere('is_co_host_active', 'Request');
            })
            ->first();
        if (!$call) {
            if ($selfAcceptUnlockedSeat) {
                $call = new LiveCall();
                $call->host_id = $hostId;
                $call->co_host_id = $coHostId;
                $call->channelName = $channel;
                $call->status = 'pending';
                $call->is_co_host_active = 'pending';
                $call->set_no = $requestedSetNo;
                $call->save();
            } else {
                return ['ok' => false, 'error' => 'pending_call_not_found'];
            }
        }

        $requestedSetNo = $requestedSetNo ?: $this->normalizeSeatNo($call->set_no ?? null);

        $resolved = DB::transaction(function () use (
            $call,
            $channel,
            $hostId,
            $coHostId,
            $requestedSetNo,
            $preserveMute,
            $roomType,
            $userLive
        ) {
            $lockedCall = LiveCall::where('id', $call->id)->lockForUpdate()->first();
            if (!$lockedCall) {
                return null;
            }

            $seatNo = $this->resolveAcceptSeatNo(
                $hostId,
                $channel,
                $coHostId,
                $requestedSetNo ?: $this->normalizeSeatNo($lockedCall->set_no),
                $userLive,
                $roomType
            );
            if ($seatNo === null) {
                return null;
            }

            $mute = $preserveMute ? (int) $lockedCall->mute : 0;
            DB::table('live_calls')
                ->where('id', $lockedCall->id)
                ->update([
                    'status'            => 'Accept',
                    'is_co_host_active' => 'Accept',
                    'set_no'            => $seatNo,
                    'mute'              => $mute,
                    'updated_at'        => now(),
                ]);

            return [
                'id'     => $lockedCall->id,
                'set_no' => $seatNo,
                'mute'   => $mute,
            ];
        });

        if (!$resolved) {
            return ['ok' => false, 'error' => 'room_full'];
        }

        $call->id     = $resolved['id'];
        $call->set_no = $resolved['set_no'];
        $call->mute   = $resolved['mute'];
        $body['set_no'] = $call->set_no;

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.accepted',
            [
                'call_id'    => $call->id,
                'co_host_id' => $coHostId,
                'user_id'    => $coHostId,
                'set_no'     => $call->set_no,
                'mute'       => (int) $call->mute,
            ],
            ['actor_user_id' => $actorId, 'target_user_id' => $coHostId]
        );

        // Defer the snapshot until tx-after-commit so the new Accept row is
        // visible to whatever builds the snapshot payload.
        DB::afterCommit(function () use ($roomType, $channel, $hostId) {
            try {
                $snapshot = $this->composeSnapshot($roomType, $channel, $hostId);
                $this->broadcast->broadcast(
                    $roomType,
                    $channel,
                    $hostId,
                    'room.snapshot',
                    $snapshot,
                    ['actor_user_id' => $hostId]
                );
            } catch (\Throwable $t) {
                Log::warning('V5 RoomActionService snapshot dispatch failed', [
                    'channel' => $channel,
                    'error'   => $t->getMessage(),
                ]);
            }
        });

        // Agent S design (2026-06-28): dual-emit per-seat delta behind the
        // existing snapshot path. Failure here MUST NOT abort the accept.
        try {
            app(RoomSeatUpdateService::class)->emitSeatChange(
                $roomType,
                $channel,
                (int) $call->set_no,
                [
                    'co_host_id' => (string) $coHostId,
                    'cohost_id'  => (string) $coHostId,
                    'host_id'    => (string) $hostId,
                    'mute'       => (int) $call->mute,
                ],
                (string) $hostId
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService seat-delta emit failed (accept)', [
                'channel' => $channel,
                'seat_no' => $call->set_no,
                'error'   => $t->getMessage(),
            ]);
        }

        $this->dispatchAdapter($roomType, 'acceptCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function moveSeat($roomType, $channel, $hostId, array $body)
    {
        return $this->moveAcceptedAudioSeat($roomType, $channel, $hostId, $body, false);
    }

    public function switchSeat($roomType, $channel, $hostId, array $body)
    {
        return $this->moveAcceptedAudioSeat($roomType, $channel, $hostId, $body, true);
    }

    protected function moveAcceptedAudioSeat($roomType, $channel, $hostId, array $body, $switch)
    {
        $this->assertRoomType($roomType);
        if ($roomType !== 'audio') {
            return ['ok' => false, 'error' => 'unsupported_room_type'];
        }

        $live = $this->mustLoadLive($channel, $hostId);
        if (!$live['ok']) {
            return $live;
        }

        $targetUserId = $this->stringId($body['target_user_id'] ?? $body['targetUserId'] ?? $body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($targetUserId === '') {
            return ['ok' => false, 'error' => 'target_user_id_required'];
        }

        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $targetUserId, $switch ? 'switch' : 'move')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $fromSeat = $this->normalizeSeatNo($body['from_seat'] ?? $body['fromSeat'] ?? $body['from_set_no'] ?? $body['fromSetNo'] ?? null);
        $toSeat = $this->normalizeSeatNo($body['to_seat'] ?? $body['toSeat'] ?? $body['to_set_no'] ?? $body['toSetNo'] ?? $body['set_no'] ?? null);
        if ($toSeat === null) {
            return ['ok' => false, 'error' => 'to_seat_required'];
        }

        $userLive = $live['user_live'];
        $seatCount = $this->roomSeatCount($userLive, $roomType);
        if (($fromSeat !== null && (int) $fromSeat > $seatCount) || (int) $toSeat > $seatCount) {
            return ['ok' => false, 'error' => 'seat_out_of_range', 'seat_count' => $seatCount];
        }

        $resolved = DB::transaction(function () use (
            $hostId,
            $channel,
            $targetUserId,
            $fromSeat,
            $toSeat,
            $switch,
            $userLive
        ) {
            $target = LiveCall::where('host_id', $hostId)
                ->where('channelName', $channel)
                ->where('co_host_id', $targetUserId)
                ->where(function ($query) {
                    $query->where('status', 'Accept')
                        ->orWhere('is_co_host_active', 'Accept');
                })
                ->lockForUpdate()
                ->first();
            if (!$target) {
                return ['ok' => false, 'error' => 'call_not_found'];
            }

            $currentSeat = $this->normalizeSeatNo($target->set_no);
            if ($currentSeat === null) {
                return ['ok' => false, 'error' => 'from_seat_required'];
            }
            if ($fromSeat !== null && (int) $fromSeat !== (int) $currentSeat) {
                return ['ok' => false, 'error' => 'seat_mismatch', 'current_seat' => $currentSeat];
            }
            if ((int) $currentSeat === (int) $toSeat) {
                return ['ok' => false, 'error' => 'already_on_seat'];
            }

            $allLocked = (int) ($userLive->locked ?? 0) === 1;
            $locked = LiveCall::where('host_id', $hostId)
                ->where('channelName', $channel)
                ->where('set_no', (int) $toSeat)
                ->where('status', 'locked')
                ->exists();
            if ($allLocked || $locked) {
                return ['ok' => false, 'error' => 'seat_locked'];
            }

            $other = LiveCall::where('host_id', $hostId)
                ->where('channelName', $channel)
                ->where('set_no', (int) $toSeat)
                ->where(function ($query) {
                    $query->where('status', 'Accept')
                        ->orWhere('is_co_host_active', 'Accept');
                })
                ->where('co_host_id', '!=', $targetUserId)
                ->lockForUpdate()
                ->first();

            if ($other && !$switch) {
                return ['ok' => false, 'error' => 'seat_occupied'];
            }
            if (!$other && $switch) {
                return ['ok' => false, 'error' => 'target_seat_empty'];
            }

            DB::table('live_calls')
                ->where('id', $target->id)
                ->update([
                    'set_no'     => (int) $toSeat,
                    'updated_at' => now(),
                ]);
            $target->set_no = (int) $toSeat;

            if ($other) {
                DB::table('live_calls')
                    ->where('id', $other->id)
                    ->update([
                        'set_no'     => (int) $currentSeat,
                        'updated_at' => now(),
                    ]);
                $other->set_no = (int) $currentSeat;
            }

            return [
                'ok'           => true,
                'from_seat'    => (string) $currentSeat,
                'to_seat'      => (string) $toSeat,
                'target_call'  => $target,
                'swapped_call' => $other,
            ];
        });

        if (!($resolved['ok'] ?? false)) {
            return $resolved;
        }

        $moveId = 'seat_move:' . $channel . ':' . $targetUserId . ':' . $resolved['from_seat'] . ':' . $resolved['to_seat'] . ':' . round(microtime(true) * 1000);
        $envelopes = [];

        try {
            if ($resolved['swapped_call']) {
                $envelopes[] = app(RoomSeatUpdateService::class)->emitSeatChange(
                    $roomType,
                    $channel,
                    (int) $resolved['from_seat'],
                    $this->seatDeltaForCall($hostId, $channel, $resolved['swapped_call']) + [
                        'move_id'   => $moveId,
                        'from_seat' => (string) $resolved['to_seat'],
                        'to_seat'   => (string) $resolved['from_seat'],
                    ],
                    (string) $actorId
                );
            } else {
                $envelopes[] = app(RoomSeatUpdateService::class)->emitSeatChange(
                    $roomType,
                    $channel,
                    (int) $resolved['from_seat'],
                    [
                        'host_id'    => (string) $hostId,
                        'co_host_id' => (string) $targetUserId,
                        'cohost_id'  => (string) $targetUserId,
                        'user_id'    => (string) $targetUserId,
                        'set_no'     => (string) $resolved['from_seat'],
                        'cleared'    => true,
                        'move_id'    => $moveId,
                        'from_seat'  => (string) $resolved['from_seat'],
                        'to_seat'    => (string) $resolved['to_seat'],
                    ],
                    (string) $actorId
                );
            }

            $envelopes[] = app(RoomSeatUpdateService::class)->emitSeatChange(
                $roomType,
                $channel,
                (int) $resolved['to_seat'],
                $this->seatDeltaForCall($hostId, $channel, $resolved['target_call']) + [
                    'move_id'   => $moveId,
                    'from_seat' => (string) $resolved['from_seat'],
                    'to_seat'   => (string) $resolved['to_seat'],
                ],
                (string) $actorId
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService seat-delta emit failed (move)', [
                'channel' => $channel,
                'error'   => $t->getMessage(),
            ]);
        }

        try {
            $snapshot = $this->composeSnapshot($roomType, $channel, $hostId);
            $envelopes[] = $this->broadcast->broadcast(
                $roomType,
                $channel,
                $hostId,
                'room.snapshot',
                $snapshot,
                ['actor_user_id' => $actorId, 'target_user_id' => $targetUserId]
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService snapshot dispatch failed (move)', [
                'channel' => $channel,
                'error'   => $t->getMessage(),
            ]);
        }

        $body['from_seat'] = $resolved['from_seat'];
        $body['to_seat'] = $resolved['to_seat'];
        $this->dispatchAdapter($roomType, $switch ? 'switchSeat' : 'moveSeat', $channel, $hostId, $body);

        $lastEnvelope = [];
        foreach ($envelopes as $envelope) {
            if (is_array($envelope) && !empty($envelope)) {
                $lastEnvelope = $envelope;
            }
        }

        return [
            'ok'         => true,
            'envelope'   => $lastEnvelope,
            'envelopes'  => array_values(array_filter($envelopes)),
            'from_seat'  => $resolved['from_seat'],
            'to_seat'    => $resolved['to_seat'],
            'switched'   => (bool) $resolved['swapped_call'],
        ];
    }

    public function rejectCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $coHostId = $this->stringId($body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($coHostId === '') {
            return ['ok' => false, 'error' => 'co_host_id_required'];
        }
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $coHostId, 'reject')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->delete();

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.rejected',
            ['co_host_id' => $coHostId, 'user_id' => $coHostId],
            ['actor_user_id' => $actorId, 'target_user_id' => $coHostId]
        );

        $this->dispatchAdapter($roomType, 'rejectCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function cutCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $coHostId = $this->stringId($body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($coHostId === '') {
            return ['ok' => false, 'error' => 'co_host_id_required'];
        }
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $coHostId, 'cut')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($actorId !== $coHostId && $this->moderationTargetProtected($hostId, $actorId, $coHostId)) {
            return ['ok' => false, 'error' => 'target_protected'];
        }

        // Capture set_no before we delete the row so the per-seat delta can
        // target the right slot.
        $priorSeat = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->value('set_no');

        LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->delete();

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.cut',
            ['co_host_id' => $coHostId, 'user_id' => $coHostId],
            ['actor_user_id' => $actorId, 'target_user_id' => $coHostId]
        );

        if ($priorSeat !== null) {
            try {
                app(RoomSeatUpdateService::class)->emitSeatChange(
                    $roomType,
                    $channel,
                    (int) $priorSeat,
                    [
                        'cleared'    => true,
                        'co_host_id' => (string) $coHostId,
                        'host_id'    => (string) $hostId,
                    ],
                    (string) $actorId
                );
            } catch (\Throwable $t) {
                Log::warning('V5 RoomActionService seat-delta emit failed (cut)', [
                    'channel' => $channel,
                    'seat_no' => $priorSeat,
                    'error'   => $t->getMessage(),
                ]);
            }
        }

        $this->dispatchAdapter($roomType, 'cutCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function kickCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $coHostId = $this->stringId($body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($coHostId === '') {
            return ['ok' => false, 'error' => 'co_host_id_required'];
        }
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $coHostId, 'kick')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($this->kickTargetProtected($hostId, $actorId, $coHostId)) {
            return ['ok' => false, 'error' => 'target_protected'];
        }

        $priorSeat = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->value('set_no');

        LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->delete();

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.kicked',
            ['co_host_id' => $coHostId, 'user_id' => $coHostId],
            ['actor_user_id' => $actorId, 'target_user_id' => $coHostId]
        );

        $this->emitSeatCleared($roomType, $channel, $hostId, $coHostId, $priorSeat, $actorId, 'kick');

        $this->dispatchAdapter($roomType, 'kickCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function muteCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $coHostId = $this->stringId($body['co_host_id'] ?? $body['user_id'] ?? null);
        if ($coHostId === '') {
            return ['ok' => false, 'error' => 'co_host_id_required'];
        }

        $mute = (int) ($body['mute'] ?? 0);
        $requestedSuperMute = array_key_exists('super_mute', $body) ? (int) $body['super_mute'] : null;
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $coHostId, 'mute')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($actorId === $coHostId && $requestedSuperMute === 1) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($actorId !== $coHostId && $this->moderationTargetProtected($hostId, $actorId, $coHostId)) {
            return ['ok' => false, 'error' => 'target_protected'];
        }

        $call = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $coHostId)
            ->where('channelName', $channel)
            ->first();
        if (!$call) {
            return ['ok' => false, 'error' => 'call_not_found'];
        }

        $currentSuperMute = (int) ($call->super_mute ?? 0);
        if ($actorId === $coHostId) {
            if ($currentSuperMute === 1 && $mute === 0) {
                return ['ok' => false, 'error' => 'super_mute_active'];
            }
            $superMute = $currentSuperMute;
        } else {
            $superMute = $requestedSuperMute === null ? $currentSuperMute : $requestedSuperMute;
        }

        $now = now();
        $updateData = [
            'mute'       => $mute,
            'super_mute' => $superMute,
            'updated_at' => $now,
        ];
        if ($this->hasLiveCallsMuteUpdatedAt()) {
            $updateData['mute_updated_at'] = $now;
        }
        LiveCall::where('id', $call->id)->update($updateData);

        $envelope = null;
        if (!config('realtime.mute_via_rtc', false)) {
            $envelope = $this->broadcast->broadcast(
                $roomType,
                $channel,
                $hostId,
                'room.cohost.mute_changed',
                [
                    'co_host_id' => $coHostId,
                    'mute'       => $mute,
                    'super_mute' => $superMute,
                    'updated_at' => $now->toIso8601String(),
                ],
                ['actor_user_id' => $actorId, 'target_user_id' => $coHostId]
            );
        }

        try {
            app(RoomSeatUpdateService::class)->emitSeatChange(
                $roomType,
                $channel,
                (int) $call->set_no,
                [
                    'mute'       => $mute,
                    'super_mute' => $superMute,
                    'co_host_id' => (string) $coHostId,
                    'host_id'    => (string) $hostId,
                    'updated_at' => $now->toIso8601String(),
                ],
                (string) $actorId
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService seat-delta emit failed (mute)', [
                'channel' => $channel,
                'seat_no' => $call->set_no,
                'error'   => $t->getMessage(),
            ]);
        }

        $this->dispatchAdapter($roomType, 'muteCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function inviteCohost($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $targetId = $this->stringId($body['target_user_id'] ?? $body['user_id'] ?? null);
        if ($targetId === '') {
            return ['ok' => false, 'error' => 'target_user_id_required'];
        }
        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageCohost($hostId, $actorId, $targetId, 'invite')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.cohost.invited',
            ['target_user_id' => $targetId, 'user_id' => $targetId],
            ['actor_user_id' => $actorId, 'target_user_id' => $targetId]
        );

        $this->dispatchAdapter($roomType, 'inviteCohost', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    /* ============================================================
     * Comments
     * ============================================================ */

    public function sendComment($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $userId  = $this->stringId($body['user_id'] ?? null);
        $message = trim((string) ($body['message'] ?? ''));
        if ($userId === '' || $message === '') {
            return ['ok' => false, 'error' => 'user_and_message_required'];
        }

        // Block comment if user is comment-muted in this room.
        $muted = CommentMute::where('user_id', $userId)
            ->where('channelName', $channel)
            ->first();
        if ($muted) {
            return ['ok' => false, 'error' => 'comment_muted'];
        }

        $user = User::where('id', $userId)->first();
        if (!$user) {
            return ['ok' => false, 'error' => 'user_not_found'];
        }

        $comment = Comment::create([
            'user_id'     => $userId,
            'reciever_id' => $hostId,
            'channelName' => $channel,
            'message'     => $message,
            'type'        => $body['type'] ?? 'message',
            'date'        => now()->toDateTimeString(),
        ]);

        $eventId = trim((string) ($body['event_id'] ?? $body['eventId'] ?? ''));

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.comment.added',
            [
                'id'               => $comment->id,
                'user_id'          => $userId,
                'name'             => $user->name,
                'profile'          => $user->profile ?? null,
                'message'          => $message,
                'level'            => $this->resolveUserLevel($userId),
                'is_vip'           => (int) ($user->is_vip ?? 0),
                'frame'            => $user->frame ?? null,
                'comment_badge'    => $user->comment_badge ?? null,
                'is_official_id'   => (int) ($user->is_official_id ?? 0),
                'event_id'         => $eventId !== '' ? $eventId : (string) $comment->id,
            ],
            [
                'actor_user_id'  => $userId,
                'target_user_id' => $hostId,
                'event_id'       => $eventId !== '' ? $eventId : null,
            ]
        );

        $this->storeCommentEnvelope($roomType, $channel, $envelope);

        $this->dispatchAdapter($roomType, 'sendComment', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope, 'comment_id' => $comment->id];
    }

    public function muteComment($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $targetId = $this->stringId($body['target_user_id'] ?? $body['user_id'] ?? null);
        if ($targetId === '') {
            return ['ok' => false, 'error' => 'target_user_id_required'];
        }

        $mute = isset($body['mute']) ? (int) $body['mute'] : 1;
        $actorId = $this->stringId($body['actor_user_id'] ?? $body['mute_by'] ?? $body['kick_by'] ?? $hostId);
        if (!$this->canCommentMute($hostId, $actorId, $mute)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($mute === 1 && $this->commentMuteTargetProtected($hostId, $actorId, $targetId)) {
            return ['ok' => false, 'error' => 'target_protected'];
        }
        $body['actor_user_id'] = $actorId;

        if ($mute === 1) {
            $row = CommentMute::where('user_id', $targetId)
                ->where('channelName', $channel)
                ->first();
            if (!$row) {
                $row = new CommentMute;
                $row->user_id = $targetId;
                $row->channelName = $channel;
            }
            if ($this->hasCommentMutesHostId()) {
                $row->host_id = $hostId;
            }
            $row->save();
            $eventType = 'room.comment.muted';
        } else {
            CommentMute::where('user_id', $targetId)
                ->where('channelName', $channel)
                ->delete();
            $eventType = 'room.comment.unmuted';
        }

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            $eventType,
            ['target_user_id' => $targetId, 'user_id' => $targetId, 'mute' => $mute],
            ['actor_user_id' => $actorId, 'target_user_id' => $targetId]
        );

        $this->dispatchAdapter($roomType, 'muteComment', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    /* ============================================================
     * Audience
     * ============================================================ */

    public function kickAudience($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $targetId = $this->stringId($body['target_user_id'] ?? $body['user_id'] ?? null);
        if ($targetId === '') {
            return ['ok' => false, 'error' => 'target_user_id_required'];
        }
        $kickBy = $this->stringId($body['actor_user_id'] ?? $body['kick_by'] ?? $hostId);
        if (!$this->canKickAudience($hostId, $kickBy)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }
        if ($this->kickTargetProtected($hostId, $kickBy, $targetId)) {
            return ['ok' => false, 'error' => 'target_protected'];
        }
        $body['actor_user_id'] = $kickBy;

        $priorSeat = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $targetId)
            ->where('channelName', $channel)
            ->value('set_no');

        Kick::updateOrCreate(
            ['user_id' => $targetId, 'channelName' => $channel],
            ['host_id' => $hostId, 'kick_by' => $kickBy]
        );

        AudienceJoin::where('user_id', $targetId)
            ->where('channelName', $channel)
            ->delete();

        LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $targetId)
            ->where('channelName', $channel)
            ->delete();

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.member.kicked',
            ['target_user_id' => $targetId, 'user_id' => $targetId, 'kick_by' => $kickBy],
            ['actor_user_id' => $kickBy, 'target_user_id' => $targetId]
        );

        $this->emitSeatCleared($roomType, $channel, $hostId, $targetId, $priorSeat, $kickBy, 'kick_audience');

        $this->dispatchAdapter($roomType, 'kickAudience', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function joinAudience($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $userId = $this->stringId($body['user_id'] ?? null);
        if ($userId === '') {
            return ['ok' => false, 'error' => 'user_id_required'];
        }

        $user = User::where('id', $userId)->first();
        if (!$user) {
            return ['ok' => false, 'error' => 'user_not_found'];
        }

        // Kick block
        $kicked = Kick::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('user_id', $userId)
            ->first();
        if ($kicked) {
            return ['ok' => false, 'error' => 'user_kicked'];
        }
        $adminPower = $this->roomAdminPower($hostId, $userId);

        $audience = AudienceJoin::firstOrNew(['user_id' => $userId, 'channelName' => $channel]);
        $isNewAudience = !$audience->exists;
        $audience->host_id = $hostId;
        $audience->profile = $user->profile ?? null;
        $audience->admin_power = $adminPower;
        $audience->entry_show = $body['entry_show'] ?? 0;
        $audience->save();

        $payload = [
            'user_id'          => $userId,
            'name'             => $user->name,
            'profile'          => $user->profile ?? null,
            'level'            => $this->resolveUserLevel($userId),
            'is_vip'           => (int) ($user->is_vip ?? 0),
            'frame'            => $user->frame ?? null,
            'entry_effect'     => $user->entry ?? null,
            'entry'            => $user->entry ?? null,
            'is_official_id'   => (int) ($user->is_official_id ?? 0),
            'comment_badge'    => $user->comment_badge ?? null,
            'admin_power'      => $adminPower,
        ];

        if (!$isNewAudience) {
            return [
                'ok' => true,
                'idempotent' => true,
                'envelope' => $this->localEnvelope(
                    $roomType,
                    $channel,
                    $hostId,
                    'room.member.entered',
                    $payload,
                    ['actor_user_id' => $userId, 'target_user_id' => $hostId]
                ),
            ];
        }

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.member.entered',
            $payload,
            ['actor_user_id' => $userId, 'target_user_id' => $hostId]
        );

        $this->dispatchAdapter($roomType, 'joinAudience', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function leaveAudience($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $userId = $this->stringId($body['user_id'] ?? null);
        if ($userId === '') {
            return ['ok' => false, 'error' => 'user_id_required'];
        }

        $priorSeat = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $userId)
            ->where('channelName', $channel)
            ->value('set_no');

        $audienceDeleted = AudienceJoin::where('user_id', $userId)
            ->where('channelName', $channel)
            ->delete();

        $callDeleted = LiveCall::where('host_id', $hostId)
            ->where('co_host_id', $userId)
            ->where('channelName', $channel)
            ->delete();

        $payload = ['user_id' => $userId];

        if ((int) $audienceDeleted <= 0 && (int) $callDeleted <= 0) {
            return [
                'ok' => true,
                'idempotent' => true,
                'envelope' => $this->localEnvelope(
                    $roomType,
                    $channel,
                    $hostId,
                    'room.member.left',
                    $payload,
                    ['actor_user_id' => $userId, 'target_user_id' => $hostId]
                ),
            ];
        }

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.member.left',
            $payload,
            ['actor_user_id' => $userId, 'target_user_id' => $hostId]
        );

        $this->emitSeatCleared($roomType, $channel, $hostId, $userId, $priorSeat, $userId, 'leave');

        $this->dispatchAdapter($roomType, 'leaveAudience', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    /* ============================================================
     * Room state    /* ============================================================
     * Room state
     * ============================================================ */

    public function setSeatLocks($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);
        if ($roomType !== 'audio') {
            return ['ok' => false, 'error' => 'unsupported_room_type'];
        }

        $live = $this->mustLoadLive($channel, $hostId);
        if (!$live['ok']) {
            return $live;
        }

        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageSeatLayout($hostId, $actorId)) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $seatCount = $this->roomSeatCount($live['user_live'], $roomType);
        $newLocks = $this->normalizeSeatSet($body['locked_seats'] ?? $body['lockedSeats'] ?? $body['seat_no'] ?? $body['seatNo'] ?? [], $seatCount);

        $currentRows = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('status', 'locked')
            ->get();
        $oldLocks = [];
        foreach ($currentRows as $row) {
            $seat = (int) $row->set_no;
            if ($seat >= 1 && $seat <= $seatCount) {
                $oldLocks[$seat] = $seat;
            }
        }

        DB::transaction(function () use ($hostId, $channel, $newLocks, $live) {
            LiveCall::where('host_id', $hostId)
                ->where('channelName', $channel)
                ->where('status', 'locked')
                ->delete();

            $type = (int) ($live['user_live']->type ?? 1);
            foreach (array_values($newLocks) as $seatNo) {
                $row = new LiveCall;
                $row->co_host_id = '0';
                $row->channelName = $channel;
                $row->type = $type;
                $row->host_id = $hostId;
                $row->set_no = $seatNo;
                $row->status = 'locked';
                $row->super_mute = '0';
                $row->save();
            }
        });

        $changedSeats = array_unique(array_merge(array_keys($oldLocks), array_keys($newLocks)));
        sort($changedSeats);
        $envelopes = [];
        foreach ($changedSeats as $seatNo) {
            try {
                $envelopes[] = app(RoomSeatUpdateService::class)->emitSeatChange(
                    $roomType,
                    $channel,
                    (int) $seatNo,
                    [
                        'host_id' => (string) $hostId,
                        'set_no'  => (string) $seatNo,
                        'locked'  => isset($newLocks[$seatNo]) ? 1 : 0,
                    ],
                    (string) $actorId
                );
            } catch (\Throwable $t) {
                Log::warning('V5 RoomActionService seat-lock delta failed', [
                    'channel' => $channel,
                    'seat_no' => $seatNo,
                    'error'   => $t->getMessage(),
                ]);
            }
        }

        try {
            $snapshot = $this->composeSnapshot($roomType, $channel, $hostId);
            $envelopes[] = $this->broadcast->broadcast(
                $roomType,
                $channel,
                $hostId,
                'room.snapshot',
                $snapshot,
                ['actor_user_id' => $actorId]
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService snapshot dispatch failed (seat-lock)', [
                'channel' => $channel,
                'error'   => $t->getMessage(),
            ]);
        }

        $lastEnvelope = [];
        foreach ($envelopes as $envelope) {
            if (is_array($envelope) && !empty($envelope)) {
                $lastEnvelope = $envelope;
            }
        }

        return [
            'ok'           => true,
            'envelope'     => $lastEnvelope,
            'envelopes'    => array_values(array_filter($envelopes)),
            'locked_seats' => implode(',', array_values($newLocks)),
        ];
    }

    public function lockRoom($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageRoomState($hostId, $actorId, 'lock')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $locked = (int) ($body['locked'] ?? 1);
        UserLive::where('user_id', $hostId)
            ->where('channelName', $channel)
            ->update(['locked' => $locked]);

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.snapshot',
            ['locked' => $locked],
            ['actor_user_id' => $actorId]
        );

        $this->dispatchAdapter($roomType, 'lockRoom', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function closeRoom($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if (!$this->canManageRoomState($hostId, $actorId, 'close')) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.ended',
            ['host_id' => $hostId, 'reason' => $body['reason'] ?? 'host_end'],
            ['actor_user_id' => $actorId]
        );

        $this->dispatchAdapter($roomType, 'closeRoom', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    public function setAdmin($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $adminId = $this->stringId($body['admin_id'] ?? $body['user_id'] ?? null);
        if ($adminId === '') {
            return ['ok' => false, 'error' => 'admin_id_required'];
        }

        $actorId = $this->stringId($body['actor_user_id'] ?? $hostId);
        if ((string) $actorId !== (string) $hostId) {
            return ['ok' => false, 'error' => 'forbidden'];
        }

        $grant = (int) ($body['grant'] ?? 1) === 1;
        $adminPower = $grant ? $this->normalizeAdminPower($body['admin_power'] ?? $body['adminPower'] ?? $body['type'] ?? 1) : 0;
        if ($grant && $adminPower === 0) {
            $adminPower = 1;
        }

        if ($grant) {
            DB::transaction(function () use ($hostId, $adminId, $adminPower, $channel) {
                BrdAdmin::where('user_id', $hostId)->where('type', $adminPower)->delete();
                BrdAdmin::where('user_id', $hostId)->where('admin_id', $adminId)->delete();
                BrdAdmin::create([
                    'user_id'  => $hostId,
                    'admin_id' => $adminId,
                    'type'     => $adminPower,
                ]);

                AudienceJoin::where('host_id', $hostId)
                    ->where('channelName', $channel)
                    ->where('admin_power', $adminPower)
                    ->update(['admin_power' => 0]);
                AudienceJoin::where('host_id', $hostId)
                    ->where('channelName', $channel)
                    ->where('user_id', $adminId)
                    ->update(['admin_power' => $adminPower]);
            });
            $eventType = 'room.admin.added';
        } else {
            BrdAdmin::where('user_id', $hostId)
                ->where('admin_id', $adminId)
                ->delete();
            AudienceJoin::where('host_id', $hostId)
                ->where('channelName', $channel)
                ->where('user_id', $adminId)
                ->update(['admin_power' => 0]);
            $eventType = 'room.admin.removed';
        }

        $action = $grant ? 'added' : 'removed';

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            $eventType,
            [
                'admin_id' => $adminId,
                'user_id' => $adminId,
                'admin_power' => $adminPower,
                'grant' => $grant ? 1 : 0,
                'action' => $action,
                'status' => $action,
            ],
            ['actor_user_id' => $actorId, 'target_user_id' => $adminId]
        );

        $this->dispatchAdapter($roomType, 'setAdmin', $channel, $hostId, $body);

        return ['ok' => true, 'envelope' => $envelope];
    }

    /* ============================================================
     * Gifts
     * ============================================================ */

    public function sendGift($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $senderId = $this->stringId($body['sander_id'] ?? $body['sender_id'] ?? $body['sender_user_id'] ?? $body['user_id'] ?? null);
        $receiverId = $this->stringId($body['reciever_id'] ?? $body['receiver_id'] ?? $body['receiver_user_id'] ?? $body['target_user_id'] ?? $hostId);
        $giftId = trim((string) ($body['gift_id'] ?? $body['giftId'] ?? ''));
        $quantity = max(1, (int) ($body['quantity'] ?? 1));
        $eventId = trim((string) ($body['event_id'] ?? $body['eventId'] ?? ''));

        if ($senderId === '' || $receiverId === '' || $giftId === '') {
            return ['ok' => false, 'error' => 'gift_invalid'];
        }
        if ($eventId === '') {
            return ['ok' => false, 'error' => 'event_id_required'];
        }
        if ($quantity > 1000) {
            return ['ok' => false, 'error' => 'gift_quantity_invalid'];
        }

        $cached = $this->readGiftRedisAudit($eventId);
        if ($cached) {
            $cached['idempotent'] = true;
            return $cached;
        }
        $lockKey = 'queenlive:v5:gift:event_lock:' . $eventId;
        if (!Redis::setnx($lockKey, (string) time())) {
            return ['ok' => false, 'error' => 'duplicate_in_progress'];
        }
        Redis::expire($lockKey, 30);

        $giftMeta = $this->resolveGiftMeta($giftId, $body);
        if (!$giftMeta['ok']) {
            return $giftMeta;
        }
        $name = $giftMeta['name'];
        $unitValue = (int) $giftMeta['value'];
        if ($unitValue <= 0) {
            return ['ok' => false, 'error' => 'gift_invalid'];
        }
        $totalCost = $unitValue * $quantity;

        try {
            $result = DB::transaction(function () use ($senderId, $receiverId, $name, $unitValue, $quantity, $totalCost, $channel) {
                $sender = User::where('id', $senderId)->lockForUpdate()->first();
                if (!$sender) {
                    throw new \RuntimeException('gift_sender_not_found');
                }
                $receiver = User::where('id', $receiverId)->lockForUpdate()->first();
                if (!$receiver) {
                    throw new \RuntimeException('gift_receiver_not_found');
                }
                if ((int) ($sender->balance ?? 0) < $totalCost) {
                    throw new \RuntimeException('insufficient_balance');
                }

                $sender->balance = (int) $sender->balance - $totalCost;
                $sender->save();

                $giftIds = [];
                for ($i = 0; $i < $quantity; $i++) {
                    $gift = Gift::create([
                        'sander_id'   => $senderId,
                        'reciever_id' => $receiverId,
                        'name'        => $name,
                        'value'       => $unitValue,
                        'channelName' => $channel,
                        'date'        => now(),
                    ]);
                    $giftIds[] = $gift->id;
                }

                return [
                    'gift_ids' => $giftIds,
                    'balance'  => (int) $sender->balance,
                    'receiver_name' => $receiver->name ?? '',
                    'receiver_profile' => $receiver->profile ?? null,
                    'sender_name' => $sender->name ?? '',
                    'sender_profile' => $sender->profile ?? null,
                ];
            }, 3);
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            if (in_array($msg, ['insufficient_balance', 'gift_sender_not_found', 'gift_receiver_not_found'], true)) {
                return ['ok' => false, 'error' => $msg];
            }
            throw $e;
        }

        $broadcastEventId = $eventId;
        $hostBalance = Gift::where('reciever_id', $receiverId)
            ->where('channelName', $channel)
            ->sum('value');

        $payload = [
            'gift_ids'          => $result['gift_ids'],
            'gift_id'           => $giftId,
            'name'              => $name,
            'gift_name'         => $name,
            'value'             => (string) $unitValue,
            'quantity'          => $quantity,
            'total_cost'        => $totalCost,
            'sander_id'         => $senderId,
            'sender_id'         => $senderId,
            'sender_name'       => $result['sender_name'],
            'sender_profile'    => $result['sender_profile'],
            'reciever_id'       => $receiverId,
            'receiver_id'       => $receiverId,
            'receiver_name'     => $result['receiver_name'],
            'receiver_profile'  => $result['receiver_profile'],
            'audience_balance'  => (string) $result['balance'],
            'host_balance'      => (string) $hostBalance,
            'gift_type'         => (string) ($body['gift_type'] ?? $body['giftType'] ?? ''),
            'giftSvga'          => $body['giftSvga'] ?? $body['svga'] ?? $body['svga_url'] ?? null,
            'svga'              => $body['svga'] ?? $body['giftSvga'] ?? $body['svga_url'] ?? null,
            'svga_url'          => $body['svga_url'] ?? $body['giftSvga'] ?? $body['svga'] ?? null,
            'event_id'          => $broadcastEventId,
        ];

        $committedResponse = [
            'ok' => true,
            'broadcast_pending' => true,
            'gift_ids' => $result['gift_ids'],
            'balance' => $result['balance'],
            'event_id' => $broadcastEventId,
        ];
        $this->storeGiftRedisAudit($eventId, $committedResponse);
        Redis::expire($lockKey, 2592000);

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.gift.sent',
            $payload,
            ['actor_user_id' => $senderId, 'target_user_id' => $receiverId, 'event_id' => $broadcastEventId]
        );

        $response = [
            'ok' => true,
            'envelope' => $envelope,
            'gift_ids' => $result['gift_ids'],
            'balance' => $result['balance'],
            'event_id' => $broadcastEventId,
        ];
        $this->storeGiftRedisAudit($eventId, $response);

        return $response;
    }



    public function sendFunSticker($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $live = $this->mustLoadLive($channel, $hostId);
        if (!$live['ok']) {
            return $live;
        }

        $senderId = $this->stringId($body['sender_user_id'] ?? $body['sender_id'] ?? $body['user_id'] ?? null);
        $receiverId = $this->stringId($body['receiver_user_id'] ?? $body['receiver_id'] ?? $body['target_user_id'] ?? null);
        $senderSeat = trim((string) ($body['sender_set_no'] ?? $body['sender_seat_no'] ?? $body['from_seat_no'] ?? ''));
        $receiverSeat = trim((string) ($body['receiver_set_no'] ?? $body['receiver_seat_no'] ?? $body['to_seat_no'] ?? ''));
        $stickerId = trim((string) ($body['sticker_id'] ?? $body['id'] ?? ''));
        $stickerType = strtolower(trim((string) ($body['sticker_type'] ?? $body['type'] ?? 'image')));
        $stickerUrl = trim((string) ($body['sticker_url'] ?? $body['url'] ?? ''));
        $eventId = trim((string) ($body['event_id'] ?? $body['eventId'] ?? ''));

        if ($senderId === '' || $receiverId === '') {
            return ['ok' => false, 'error' => 'sender_receiver_required'];
        }
        if ($senderId === $receiverId) {
            return ['ok' => false, 'error' => 'self_recipient_not_allowed'];
        }
        if ($senderSeat === '' || $receiverSeat === '') {
            return ['ok' => false, 'error' => 'seat_required'];
        }
        if ($stickerId === '' || $stickerUrl === '') {
            return ['ok' => false, 'error' => 'sticker_required'];
        }
        if (!in_array($stickerType, ['webp', 'gif', 'svga', 'image'], true)) {
            return ['ok' => false, 'error' => 'sticker_type_invalid'];
        }
        if ($eventId === '') {
            $eventId = (string) \Illuminate\Support\Str::uuid();
        }

        if (!$this->funStickerSeatIsActive($channel, $hostId, $senderId, $senderSeat)) {
            return ['ok' => false, 'error' => 'sender_not_on_seat'];
        }
        if (!$this->funStickerSeatIsActive($channel, $hostId, $receiverId, $receiverSeat)) {
            return ['ok' => false, 'error' => 'receiver_not_on_seat'];
        }

        $payload = [
            'event_id' => $eventId,
            'channelName' => $channel,
            'channel_name' => $channel,
            'room_type' => $roomType,
            'host_id' => (string) $hostId,
            'sender_user_id' => $senderId,
            'sender_id' => $senderId,
            'sender_set_no' => $senderSeat,
            'sender_seat_no' => $senderSeat,
            'receiver_user_id' => $receiverId,
            'receiver_id' => $receiverId,
            'reciever_id' => $receiverId,
            'receiver_set_no' => $receiverSeat,
            'receiver_seat_no' => $receiverSeat,
            'sticker_id' => $stickerId,
            'sticker_type' => $stickerType,
            'sticker_url' => $stickerUrl,
            'sticker' => [
                'id' => $stickerId,
                'type' => $stickerType,
                'url' => $stickerUrl,
            ],
        ];

        $envelope = $this->broadcast->broadcast(
            $roomType,
            $channel,
            $hostId,
            'room.fun_sticker.sent',
            $payload,
            ['actor_user_id' => $senderId, 'target_user_id' => $receiverId, 'event_id' => $eventId]
        );

        return ['ok' => true, 'envelope' => $envelope];
    }

    protected function funStickerSeatIsActive($channel, $hostId, $userId, $seatNo): bool
    {
        $userId = $this->stringId($userId);
        $seat = trim((string) $seatNo);
        if ($userId === '' || $seat === '') {
            return false;
        }
        if ($seat === '0') {
            return $userId === (string) $hostId;
        }

        return LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('co_host_id', $userId)
            ->where(function ($q) {
                $q->where('is_co_host_active', 'Accept')->orWhere('status', 'Accept');
            })
            ->where('set_no', (int) $seat)
            ->exists();
    }

    public function sendMagicHeart(array $body): array
    {
        $senderId = $this->stringId($body['user_id'] ?? $body['sender_id'] ?? null);
        $channel = trim((string)($body['channel'] ?? $body['channelName'] ?? ''));
        $hostIds = $this->normalizeMagicHeartHostIds($body['host_ids'] ?? $body['hostIds'] ?? $body['host_id'] ?? []);
        $quantity = (int)($body['quantity'] ?? 0);
        $path = $this->decodeMagicHeartPath($body['path_json'] ?? $body['pathJson'] ?? []);
        $eventId = trim((string)($body['event_id'] ?? $body['eventId'] ?? ''));
        $roomType = $this->resolveMagicHeartRoomType($channel, $body);
        $auditTableReady = $this->magicHeartAuditTableReady();

        if ($senderId === '' || $channel === '' || empty($hostIds)) {
            throw new \InvalidArgumentException('missing_required_fields');
        }
        if ($quantity < 30 || $quantity > 999) {
            throw new \InvalidArgumentException('invalid_quantity');
        }
        if (empty($path)) {
            throw new \InvalidArgumentException('invalid_path_json');
        }

        $perHostValue = $quantity * 10;
        $totalCost = $perHostValue * count($hostIds);
        if ($totalCost > 99900) {
            throw new \InvalidArgumentException('total_cost_limit_exceeded');
        }

        if ($eventId !== '') {
            if ($auditTableReady) {
                $existing = DB::table('magic_heart_send')->where('event_id', $eventId)->first();
                if ($existing) {
                    $sender = User::find($senderId);
                    return [
                        'ok' => true,
                        'code' => '200',
                        'message' => 'Magic Heart sent',
                        'idempotent' => true,
                        'quantity' => (int)$existing->quantity,
                        'total_cost' => (int)$existing->total_cost,
                        'balance' => $sender ? (int)$sender->balance : null,
                    ];
                }
            } else {
                $existing = $this->readMagicHeartRedisAudit($eventId);
                if ($existing) {
                    $existing['idempotent'] = true;
                    return $existing;
                }
            }
        }

        $eventLockKey = $eventId !== '' ? 'queenlive:magic_heart:event_lock:' . $eventId : null;
        if ($eventLockKey !== null) {
            if (!Redis::setnx($eventLockKey, (string)time())) {
                $existing = $this->readMagicHeartRedisAudit($eventId);
                if ($existing) {
                    $existing['idempotent'] = true;
                    return $existing;
                }
                return [
                    'ok' => false,
                    'code' => '429',
                    'error' => 'duplicate_in_progress',
                    'message' => 'Magic Heart is already processing.',
                ];
            }
            Redis::expire($eventLockKey, 30);
        }

        $rateKey = 'queenlive:magic_heart:rate:' . $senderId;
        if (!Redis::setnx($rateKey, (string)time())) {
            return [
                'ok' => false,
                'code' => '429',
                'error' => 'rate_limited',
                'message' => 'Please wait before sending another Magic Heart.',
            ];
        }
        Redis::expire($rateKey, 3);

        $primaryHostId = $hostIds[0];
        $pathJson = json_encode($path, JSON_UNESCAPED_SLASHES);
        $broadcastEventId = $eventId !== '' ? $eventId : (string)\Illuminate\Support\Str::uuid();

        $result = DB::transaction(function () use ($senderId, $channel, $hostIds, $quantity, $perHostValue, $totalCost, $pathJson, $eventId, $broadcastEventId, $auditTableReady) {
            if ($eventId !== '' && $auditTableReady) {
                $existing = DB::table('magic_heart_send')->where('event_id', $eventId)->lockForUpdate()->first();
                if ($existing) {
                    $sender = User::find($senderId);
                    return [
                        'ok' => true,
                        'code' => '200',
                        'message' => 'Magic Heart sent',
                        'idempotent' => true,
                        'quantity' => (int)$existing->quantity,
                        'total_cost' => (int)$existing->total_cost,
                        'balance' => $sender ? (int)$sender->balance : null,
                        'skip_broadcast' => true,
                    ];
                }
            }

            $sender = User::where('id', $senderId)->lockForUpdate()->first();
            if (!$sender) {
                throw new \InvalidArgumentException('sender_not_found');
            }
            if ((int)$sender->balance < $totalCost) {
                return [
                    'ok' => false,
                    'code' => '402',
                    'error' => 'low_balance',
                    'message' => 'low_balance',
                    'quantity' => $quantity,
                    'total_cost' => $totalCost,
                    'balance' => (int)$sender->balance,
                ];
            }

            $hosts = [];
            foreach ($hostIds as $hostId) {
                $host = User::where('id', $hostId)->lockForUpdate()->first();
                if (!$host) {
                    throw new \InvalidArgumentException('host_not_found');
                }
                $host->balance = (int)$host->balance + $perHostValue;
                $host->save();
                $hosts[] = [
                    'id' => (string)$host->id,
                    'name' => $host->name ?? $host->username ?? '',
                    'balance' => (int)$host->balance,
                ];
            }

            $sender->balance = (int)$sender->balance - $totalCost;
            $sender->save();

            if ($auditTableReady) {
                DB::table('magic_heart_send')->insert([
                    'event_id' => $eventId !== '' ? $eventId : null,
                    'user_id' => $senderId,
                    'channel' => $channel,
                    'host_ids' => json_encode($hostIds, JSON_UNESCAPED_SLASHES),
                    'quantity' => $quantity,
                    'total_cost' => $totalCost,
                    'path_json' => $pathJson,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return [
                'ok' => true,
                'code' => '200',
                'message' => 'Magic Heart sent',
                'event_id' => $broadcastEventId,
                'sender' => [
                    'id' => (string)$sender->id,
                    'name' => $sender->name ?? $sender->username ?? '',
                    'profile_image' => $sender->profile_pic ?? $sender->image ?? null,
                ],
                'hosts' => $hosts,
                'host_ids' => $hostIds,
                'quantity' => $quantity,
                'total_cost' => $totalCost,
                'balance' => (int)$sender->balance,
                'path_json' => json_decode($pathJson, true),
            ];
        }, 3);

        if (($result['ok'] ?? false) && empty($result['skip_broadcast'])) {
            $this->storeMagicHeartRedisAudit($eventId, $result);
            $payload = [
                'event_id' => $result['event_id'],
                'channel' => $channel,
                'sender' => $result['sender'],
                'hosts' => $result['hosts'],
                'host_ids' => $result['host_ids'],
                'quantity' => $result['quantity'],
                'total_cost' => $result['total_cost'],
                'path_json' => $result['path_json'],
            ];
            $result['envelope'] = $this->broadcast->broadcast($roomType, $channel, $primaryHostId, 'room.magic_heart.sent', $payload, [
                'actor_user_id' => $senderId,
                'target_user_id' => $primaryHostId,
                'event_id' => $result['event_id'],
            ]);
        }

        unset($result['skip_broadcast']);
        return $result;
    }

    private function magicHeartAuditTableReady(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('magic_heart_send');
        } catch (\Throwable $e) {
            Log::warning('magic_heart_audit_table_check_failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function readMagicHeartRedisAudit(string $eventId): ?array
    {
        if ($eventId === '') {
            return null;
        }
        try {
            $cached = Redis::get('queenlive:magic_heart:event:' . $eventId);
            $data = $cached ? json_decode($cached, true) : null;
            return is_array($data) ? $data : null;
        } catch (\Throwable $e) {
            Log::warning('magic_heart_redis_audit_read_failed', ['event_id' => $eventId, 'error' => $e->getMessage()]);
            return null;
        }
    }

    private function storeMagicHeartRedisAudit(string $eventId, array $result): void
    {
        try {
            $audit = [
                'ok' => true,
                'code' => '200',
                'message' => 'Magic Heart sent',
                'event_id' => $result['event_id'] ?? $eventId,
                'quantity' => $result['quantity'] ?? null,
                'total_cost' => $result['total_cost'] ?? null,
                'balance' => $result['balance'] ?? null,
                'host_ids' => $result['host_ids'] ?? [],
                'created_at' => now()->toDateTimeString(),
            ];
            if ($eventId !== '') {
                Redis::setex('queenlive:magic_heart:event:' . $eventId, 2592000, json_encode($audit, JSON_UNESCAPED_SLASHES));
            }
            Redis::lpush('queenlive:magic_heart:audit', json_encode($audit, JSON_UNESCAPED_SLASHES));
            Redis::ltrim('queenlive:magic_heart:audit', 0, 9999);
        } catch (\Throwable $e) {
            Log::warning('magic_heart_redis_audit_store_failed', ['event_id' => $eventId, 'error' => $e->getMessage()]);
        }
    }

    private function normalizeMagicHeartHostIds($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = json_last_error() === JSON_ERROR_NONE ? $decoded : explode(',', $raw);
        }
        if (!is_array($raw)) {
            $raw = [$raw];
        }
        $ids = [];
        foreach ($raw as $value) {
            if (is_array($value)) {
                $value = $value['id'] ?? $value['user_id'] ?? null;
            }
            $id = trim((string)$value);
            if ($id !== '' && !in_array($id, $ids, true)) {
                $ids[] = $id;
            }
        }
        return $ids;
    }

    private function decodeMagicHeartPath($raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            $raw = json_last_error() === JSON_ERROR_NONE ? $decoded : [];
        }
        if (!is_array($raw)) {
            return [];
        }
        $path = [];
        foreach ($raw as $point) {
            if (!is_array($point) || !isset($point['x'], $point['y']) || !is_numeric($point['x']) || !is_numeric($point['y'])) {
                continue;
            }
            $path[] = [
                'x' => max(0, min(1, (float)$point['x'])),
                'y' => max(0, min(1, (float)$point['y'])),
            ];
            if (count($path) >= 200) {
                break;
            }
        }
        return $path;
    }

    private function resolveMagicHeartRoomType(string $channel, array $body): string
    {
        $roomType = strtolower(trim((string)($body['room_type'] ?? $body['roomType'] ?? '')));
        if (in_array($roomType, ['audio', 'video', 'multi'], true)) {
            return $roomType;
        }

        try {
            $liveType = UserLive::where('channelName', $channel)->value('type');
            if ((int)$liveType === 1) {
                return 'audio';
            }
            if ((int)$liveType === 3) {
                return 'multi';
            }
        } catch (\Throwable $e) {
            Log::warning('magic_heart_room_type_fallback', ['channel' => $channel, 'error' => $e->getMessage()]);
        }

        return 'video';
    }

    /* ============================================================
     * Reads
     * ============================================================ */

    public function fetchSnapshot($roomType, $channel, $hostId, array $body = [])
    {
        $this->assertRoomType($roomType);

        $snapshot = $this->composeSnapshot($roomType, $channel, $hostId);
        $snapshot['ok'] = true;

        return $snapshot;
    }

    public function fetchCommentsSince($roomType, $channel, $hostId, array $body)
    {
        $this->assertRoomType($roomType);

        $sinceMs = $this->resolveCommentSinceMs($body);
        $events = $this->readCommentEnvelopes($roomType, $channel, $sinceMs);
        $seenCommentIds = [];
        foreach ($events as $event) {
            $payload = is_array($event['payload'] ?? null) ? $event['payload'] : [];
            $commentId = trim((string) ($payload['comment_id'] ?? $payload['commentId'] ?? $payload['id'] ?? ''));
            if ($commentId !== '') {
                $seenCommentIds[$commentId] = true;
            }
        }

        $since = gmdate('Y-m-d H:i:s', (int) floor($sinceMs / 1000));

        $rows = Comment::where('channelName', $channel)
            ->where('created_at', '>=', $since)
            ->orderBy('id', 'asc')
            ->limit(200)
            ->get();

        $userIds = $rows->pluck('user_id')->filter()->unique()->values()->all();
        $users = empty($userIds)
            ? collect()
            : User::whereIn('id', $userIds)->get()->keyBy(function ($user) {
                return (string) $user->id;
            });

        $comments = [];
        foreach ($rows as $row) {
            $commentId = (string) ($row->id ?? '');
            $comments[] = $row->toArray();
            if ($commentId !== '' && isset($seenCommentIds[$commentId])) {
                continue;
            }
            $events[] = $this->commentRowToEnvelope($roomType, $channel, $hostId, $row, $users->get((string) $row->user_id));
            if ($commentId !== '') {
                $seenCommentIds[$commentId] = true;
            }
        }

        usort($events, function ($left, $right) {
            $a = (int) ($left['ts_ms'] ?? $left['timestamp'] ?? 0);
            $b = (int) ($right['ts_ms'] ?? $right['timestamp'] ?? 0);
            if ($a === $b) {
                return (int) ($left['seq'] ?? 0) <=> (int) ($right['seq'] ?? 0);
            }
            return $a <=> $b;
        });
        if (count($events) > 200) {
            $events = array_slice($events, -200);
        }

        return [
            'ok' => true,
            'events' => array_values($events),
            'comments' => $comments,
            'since' => $since,
            'since_ms' => $sinceMs,
        ];
    }

    public function fetchPendingCohosts($roomType, $channel, $hostId, array $body = [])
    {
        $this->assertRoomType($roomType);

        $rows = LiveCall::join('users', 'users.id', 'live_calls.co_host_id')
            ->select(
                'users.name',
                'users.profile',
                'live_calls.id as call_id',
                'live_calls.channelName',
                'live_calls.co_host_id',
                'live_calls.status',
                'live_calls.set_no'
            )
            ->where('live_calls.host_id', $hostId)
            ->where('live_calls.channelName', $channel)
            ->where('live_calls.status', 'pending')
            ->get()
            ->map(function ($row) {
                return [
                    'call_id'     => (string) $row->call_id,
                    'name'        => $row->name,
                    'profile'     => $row->profile,
                    'channelName' => $row->channelName,
                    'channel_name'=> $row->channelName,
                    'co_host_id'  => (string) $row->co_host_id,
                    'user_id'     => (string) $row->co_host_id,
                    'status'      => $row->status,
                    'set_no'      => (string) ($row->set_no ?? '0'),
                ];
            })
            ->values()
            ->all();

        return [
            'ok' => true,
            'message' => 'Call Request',
            'channelName' => $channel,
            'channel_name' => $channel,
            'call_count' => (string) count($rows),
            'data' => $rows,
            'code' => '200',
            'event_type' => $this->pendingListEventType($roomType),
            'eventType' => $this->pendingListEventType($roomType),
        ];
    }

    protected function canKickAudience($hostId, $actorId)
    {
        if ($actorId === '') {
            return false;
        }
        if ((string) $actorId === (string) $hostId) {
            return true;
        }
        return $this->isElevatedModerator($hostId, $actorId, 'kick_power', false)
            || $this->roomAdminAllows($hostId, $actorId, 'kick');
    }

    protected function canCommentMute($hostId, $actorId, $mute = 1)
    {
        if ($actorId === '') {
            return false;
        }
        if ((string) $actorId === (string) $hostId) {
            return true;
        }
        if ($this->isElevatedModerator($hostId, $actorId, 'comment_mute_power', false)) {
            return true;
        }

        $power = $this->roomAdminPower($hostId, $actorId);
        if (in_array($power, [1, 2], true)) {
            return true;
        }

        return $power === 3 && (int) $mute === 1;
    }

    protected function kickTargetProtected($hostId, $actorId, $targetId)
    {
        if ((string) $targetId === (string) $hostId) {
            return true;
        }

        $target = User::where('id', $targetId)->first();
        if (!$target) {
            return false;
        }

        if ($this->isOfficialOrAppAdmin($target)) {
            return true;
        }

        return $this->isVipSevenOrHigher($target)
            && !$this->isElevatedModerator($hostId, $actorId, 'kick_power', false)
            && !in_array($this->roomAdminPower($hostId, $actorId), [1, 2], true);
    }

    protected function commentMuteTargetProtected($hostId, $actorId, $targetId)
    {
        if ((string) $targetId === (string) $hostId) {
            return true;
        }

        $target = User::where('id', $targetId)->first();
        return $target && $this->isOfficialOrAppAdmin($target);
    }

    protected function canManageCohost($hostId, $actorId, $coHostId, $action)
    {
        if ($actorId === '') {
            return false;
        }
        if ((string) $actorId === (string) $hostId) {
            return true;
        }
        if (in_array($action, ['cut', 'mute', 'move'], true) && (string) $actorId === (string) $coHostId) {
            return true;
        }
        if ($this->isElevatedModerator($hostId, $actorId, null, false)) {
            return true;
        }
        return $this->roomAdminAllows($hostId, $actorId, $action);
    }

    protected function canManageRoomState($hostId, $actorId, $action)
    {
        if ($actorId === '') {
            return false;
        }
        if ((string) $actorId === (string) $hostId) {
            return true;
        }
        return $this->isElevatedModerator($hostId, $actorId, null, false);
    }

    protected function canManageSeatLayout($hostId, $actorId)
    {
        if ($actorId === '') {
            return false;
        }
        if ((string) $actorId === (string) $hostId) {
            return true;
        }
        if ($this->isElevatedModerator($hostId, $actorId, null, false)) {
            return true;
        }
        return in_array($this->roomAdminPower($hostId, $actorId), [1, 2], true);
    }

    protected function roomAdminAllows($hostId, $actorId, $action)
    {
        switch ($this->roomAdminPower($hostId, $actorId)) {
            case 1:
                return in_array($action, ['accept', 'reject', 'cut', 'kick', 'mute', 'invite', 'move', 'comment_mute'], true);
            case 2:
                return in_array($action, ['accept', 'reject', 'cut', 'kick', 'mute', 'invite', 'move', 'comment_mute'], true);
            case 3:
                return in_array($action, ['comment_mute'], true);
            default:
                return false;
        }
    }

    protected function moderationTargetProtected($hostId, $actorId, $targetId)
    {
        if ((string) $targetId === (string) $hostId) {
            return true;
        }
        $target = User::where('id', $targetId)->first();
        return $target && $this->isOfficialOrAppAdmin($target);
    }

    protected function roomAdminPower($hostId, $actorId)
    {
        $type = BrdAdmin::where('user_id', $hostId)
            ->where('admin_id', $actorId)
            ->value('type');
        return $this->normalizeAdminPower($type);
    }

    protected function normalizeAdminPower($value)
    {
        if ($value === null || $value === '') {
            return 0;
        }
        $power = (int) $value;
        return in_array($power, [1, 2, 3], true) ? $power : 0;
    }

    protected function isElevatedModerator($hostId, $actorId, $powerColumn = null, $includeRoomAdmin = true)
    {
        $actor = User::where('id', $actorId)->first();
        if ($actor) {
            if ($powerColumn && isset($actor->{$powerColumn}) && (int) $actor->{$powerColumn} === 1) {
                return true;
            }
            if ($this->isOfficialOrAppAdmin($actor)) {
                return true;
            }
        }

        return $includeRoomAdmin && BrdAdmin::where('user_id', $hostId)
            ->where('admin_id', $actorId)
            ->exists();
    }

    protected function isOfficialOrAppAdmin($user)
    {
        return (int) ($user->is_official_id ?? 0) !== 0
            || (int) ($user->is_admin ?? 0) === 1
            || (int) ($user->is_bd_admin ?? 0) === 1;
    }

    protected function isVipSevenOrHigher($user)
    {
        return (int) ($user->is_vip ?? 0) >= 7
            || (int) ($user->vip ?? 0) >= 7
            || (int) ($user->vip_level ?? 0) >= 7;
    }

    /* ============================================================
     * Internals
     * ============================================================ */

    protected function assertRoomType($roomType)
    {
        if (!in_array($roomType, ['audio', 'video', 'multi'], true)) {
            throw new \InvalidArgumentException("v5 action: unknown room_type '{$roomType}'");
        }
    }

    protected function mustLoadLive($channel, $hostId)
    {
        $userLive = UserLive::where('channelName', $channel)
            ->where('user_id', $hostId)
            ->first();
        if (!$userLive) {
            return ['ok' => false, 'error' => 'room_not_found'];
        }
        return ['ok' => true, 'user_live' => $userLive];
    }

    protected function stringId($value)
    {
        $value = trim((string) $value);
        return ($value === '' || strtolower($value) === 'null') ? '' : $value;
    }

    protected function resolveCommentSinceMs(array $body)
    {
        $rawMs = $body['since_ms'] ?? $body['sinceMs'] ?? null;
        if (is_numeric($rawMs)) {
            $value = (int) $rawMs;
            if ($value > 0) {
                return $value < 100000000000 ? $value * 1000 : $value;
            }
        }

        $rawSince = trim((string) ($body['since'] ?? ''));
        if ($rawSince !== '') {
            $parsed = strtotime($rawSince);
            if ($parsed !== false && $parsed > 0) {
                return $parsed * 1000;
            }
        }

        return (int) round((microtime(true) * 1000) - 300000);
    }

    protected function storeCommentEnvelope($roomType, $channel, array $envelope)
    {
        $payload = is_array($envelope['payload'] ?? null) ? $envelope['payload'] : [];
        $eventType = (string) ($envelope['event_type'] ?? $envelope['eventType'] ?? $payload['event_type'] ?? '');
        if ($eventType !== 'room.comment.added') {
            return;
        }

        $tsMs = (int) ($envelope['ts_ms'] ?? $envelope['timestamp'] ?? $payload['ts_ms'] ?? 0);
        if ($tsMs <= 0) {
            $tsMs = (int) round(microtime(true) * 1000);
        }

        try {
            $key = $this->commentHistoryKey($roomType, $channel);
            $encoded = json_encode($envelope, JSON_UNESCAPED_SLASHES);
            if ($encoded === false) {
                return;
            }
            Redis::command('ZADD', [$key, (string) $tsMs, $encoded]);
            Redis::command('ZREMRANGEBYSCORE', [$key, '-inf', (string) ($tsMs - 86400000)]);
            Redis::expire($key, 86400);
        } catch (\Throwable $t) {
            Log::warning('V5 comment history store failed', [
                'room_type' => $roomType,
                'channel' => $channel,
                'error' => $t->getMessage(),
            ]);
        }
    }

    protected function readCommentEnvelopes($roomType, $channel, $sinceMs)
    {
        try {
            $rows = Redis::zRangeByScore(
                $this->commentHistoryKey($roomType, $channel),
                (string) $sinceMs,
                '+inf',
                ['limit' => [0, 200]]
            );
        } catch (\Throwable $t) {
            Log::warning('V5 comment history read failed', [
                'room_type' => $roomType,
                'channel' => $channel,
                'error' => $t->getMessage(),
            ]);
            return [];
        }

        if (!is_array($rows)) {
            return [];
        }

        $events = [];
        foreach ($rows as $raw) {
            $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
            if (!is_array($decoded)) {
                continue;
            }
            $eventType = (string) ($decoded['event_type'] ?? $decoded['eventType'] ?? '');
            if ($eventType === 'room.comment.added') {
                $events[] = $decoded;
            }
        }

        return $events;
    }

    protected function commentHistoryKey($roomType, $channel)
    {
        return 'queenlive:v5:comments:' . strtolower(trim((string) $roomType)) . ':' . trim((string) $channel);
    }

    protected function localEnvelope($roomType, $channel, $hostId, $eventType, array $payload, array $context = [])
    {
        $eventId = isset($context['event_id']) && trim((string) $context['event_id']) !== ''
            ? (string) $context['event_id']
            : (string) \Illuminate\Support\Str::uuid();
        $actorId = array_key_exists('actor_user_id', $context) && $context['actor_user_id'] !== null
            ? (string) $context['actor_user_id']
            : null;
        $targetId = array_key_exists('target_user_id', $context) && $context['target_user_id'] !== null
            ? (string) $context['target_user_id']
            : null;
        $nowMs = (int) round(microtime(true) * 1000);
        $payload = array_merge([
            'room_id'      => $channel,
            'room_type'    => $roomType,
            'roomType'     => $roomType,
            'channel'      => $channel,
            'channelName'  => $channel,
            'channel_name' => $channel,
            'host_id'      => (string) $hostId,
            'event_type'   => $eventType,
            'eventType'    => $eventType,
            'timestamp'    => $nowMs,
            'ts_ms'        => $nowMs,
        ], $payload);
        if ($actorId !== null && !array_key_exists('actor_user_id', $payload)) {
            $payload['actor_user_id'] = $actorId;
        }
        if ($targetId !== null && !array_key_exists('target_user_id', $payload)) {
            $payload['target_user_id'] = $targetId;
        }

        return [
            'event_id'       => $eventId,
            'room_id'        => $channel,
            'room_type'      => $roomType,
            'roomType'       => $roomType,
            'channel'        => $channel,
            'channelName'    => $channel,
            'channel_name'   => $channel,
            'host_id'        => (string) $hostId,
            'event_type'     => $eventType,
            'eventType'      => $eventType,
            'actor_user_id'  => $actorId,
            'target_user_id' => $targetId,
            'payload'        => $payload,
            'seq'            => 0,
            'ts'             => $nowMs,
            'ts_ms'          => $nowMs,
            'timestamp'      => $nowMs,
            'created_at'     => now()->toIso8601String(),
        ];
    }

    protected function commentRowToEnvelope($roomType, $channel, $hostId, $row, $user = null)
    {
        $commentId = (string) ($row->id ?? '');
        $userId = (string) ($row->user_id ?? '');
        $createdAt = $row->created_at ?? null;
        if ($createdAt instanceof \DateTimeInterface) {
            $tsMs = $createdAt->getTimestamp() * 1000;
            $createdIso = $createdAt->format(\DateTimeInterface::ATOM);
        } else {
            $parsed = strtotime((string) $createdAt);
            $tsMs = $parsed ? $parsed * 1000 : (int) round(microtime(true) * 1000);
            $createdIso = $createdAt ? date(\DateTimeInterface::ATOM, (int) floor($tsMs / 1000)) : now()->toIso8601String();
        }

        $payload = [
            'id'               => $commentId,
            'comment_id'       => $commentId,
            'user_id'          => $userId,
            'name'             => $user ? ($user->name ?? null) : null,
            'profile'          => $user ? ($user->profile ?? null) : null,
            'message'          => (string) ($row->message ?? ''),
            'type'             => (string) ($row->type ?? 'message'),
            'level'            => $this->resolveUserLevel($userId),
            'is_vip'           => $user ? (int) ($user->is_vip ?? 0) : 0,
            'frame'            => $user ? ($user->frame ?? null) : null,
            'comment_badge'    => $user ? ($user->comment_badge ?? null) : null,
            'is_official_id'   => $user ? (int) ($user->is_official_id ?? 0) : 0,
            'room_id'          => $channel,
            'room_type'        => $roomType,
            'roomType'         => $roomType,
            'channel'          => $channel,
            'channelName'      => $channel,
            'channel_name'     => $channel,
            'host_id'          => (string) $hostId,
            'event_type'       => 'room.comment.added',
            'eventType'        => 'room.comment.added',
            'timestamp'        => $tsMs,
            'ts_ms'            => $tsMs,
            'event_id'         => 'comment:' . $channel . ':' . $commentId,
        ];

        return [
            'event_id'       => 'comment:' . $channel . ':' . $commentId,
            'room_id'        => $channel,
            'room_type'      => $roomType,
            'roomType'       => $roomType,
            'channel'        => $channel,
            'channelName'    => $channel,
            'channel_name'   => $channel,
            'host_id'        => (string) $hostId,
            'event_type'     => 'room.comment.added',
            'eventType'      => 'room.comment.added',
            'actor_user_id'  => $userId !== '' ? $userId : null,
            'target_user_id' => (string) $hostId,
            'payload'        => $payload,
            'seq'            => 0,
            'ts'             => $tsMs,
            'ts_ms'          => $tsMs,
            'timestamp'      => $tsMs,
            'created_at'     => $createdIso,
        ];
    }

    protected function resolveGiftMeta($giftId, array $body)
    {
        $name = trim((string) ($body['giftName'] ?? $body['gift_name'] ?? $body['name'] ?? ''));

        foreach (['gift_files', 'gift_details', 'gifts_details', 'gift_items'] as $table) {
            try {
                if (!\Illuminate\Support\Facades\Schema::hasTable($table)) {
                    continue;
                }
                $row = DB::table($table)->where('id', $giftId)->first();
                if (!$row) {
                    continue;
                }
                $resolvedName = trim((string) ($row->name ?? $row->giftName ?? $row->gift_name ?? $name));
                $resolvedValue = (int) ($row->value ?? $row->coin ?? $row->price ?? 0);
                if ($resolvedName !== '' && $resolvedValue > 0) {
                    return ['ok' => true, 'name' => $resolvedName, 'value' => $resolvedValue];
                }
            } catch (\Throwable $t) {
                Log::warning('V5 gift meta lookup failed', [
                    'table' => $table,
                    'gift_id' => $giftId,
                    'error' => $t->getMessage(),
                ]);
            }
        }

        return ['ok' => false, 'error' => 'gift_not_found'];
    }

    private function readGiftRedisAudit(string $eventId): ?array
    {
        if ($eventId === '') {
            return null;
        }
        try {
            $cached = Redis::get('queenlive:v5:gift:event:' . $eventId);
            $data = $cached ? json_decode($cached, true) : null;
            return is_array($data) ? $data : null;
        } catch (\Throwable $t) {
            Log::warning('V5 gift audit read failed', ['event_id' => $eventId, 'error' => $t->getMessage()]);
            return null;
        }
    }

    private function storeGiftRedisAudit(string $eventId, array $result): void
    {
        if ($eventId === '') {
            return;
        }
        try {
            $audit = [
                'ok' => true,
                'event_id' => $result['event_id'] ?? $eventId,
                'gift_ids' => $result['gift_ids'] ?? [],
                'balance' => $result['balance'] ?? null,
                'envelope' => $result['envelope'] ?? null,
            ];
            Redis::setex('queenlive:v5:gift:event:' . $eventId, 2592000, json_encode($audit, JSON_UNESCAPED_SLASHES));
        } catch (\Throwable $t) {
            Log::warning('V5 gift audit store failed', ['event_id' => $eventId, 'error' => $t->getMessage()]);
        }
    }

    protected function normalizeSeatNo($value)
    {
        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'null') {
            return null;
        }
        $seat = (int) $value;
        if ($seat < 1 || $seat > 15) {
            return null;
        }
        return (string) $seat;
    }

    protected function roomSeatCount($userLive, $roomType = null)
    {
        if ($roomType === 'video') {
            return 3;
        }

        foreach (['siteNumber', 'site_number', 'seatNumber', 'seat_number'] as $column) {
            if (isset($userLive->{$column}) && (int) $userLive->{$column} >= 2) {
                return max(2, min(15, (int) $userLive->{$column}));
            }
        }

        return 15;
    }

    protected function resolveAcceptSeatNo($hostId, $channel, $coHostId, $preferredSetNo, $userLive, $roomType = null)
    {
        $seatCount = $this->roomSeatCount($userLive, $roomType);
        $preferred = $this->normalizeSeatNo($preferredSetNo);
        $rows = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where(function ($query) {
                $query->where('status', 'locked')
                    ->orWhere('status', 'Accept')
                    ->orWhere('is_co_host_active', 'Accept');
            })
            ->lockForUpdate()
            ->get();

        $occupied = [];
        $locked = [];
        $selfSeat = null;
        foreach ($rows as $row) {
            $seat = (int) $row->set_no;
            if ($seat < 1 || $seat > $seatCount) {
                continue;
            }
            if ((string) $row->status === 'locked') {
                $locked[$seat] = true;
                continue;
            }
            if ((string) $row->co_host_id === (string) $coHostId) {
                $selfSeat = $seat;
                continue;
            }
            $occupied[$seat] = true;
        }

        if ($preferred !== null) {
            $seat = (int) $preferred;
            if ($seat >= 1 &&
                $seat <= $seatCount &&
                empty($occupied[$seat]) &&
                empty($locked[$seat])) {
                return (string) $seat;
            }
        }

        if ($selfSeat !== null && empty($locked[$selfSeat])) {
            return (string) $selfSeat;
        }

        for ($seat = 1; $seat <= $seatCount; $seat++) {
            if (empty($occupied[$seat]) && empty($locked[$seat])) {
                return (string) $seat;
            }
        }

        return null;
    }

    protected function canSelfAcceptUnlockedSeat($roomType, $channel, $hostId, $actorId, $coHostId, $requestedSetNo, $userLive)
    {
        if ($roomType !== 'audio' || (string) $actorId !== (string) $coHostId) {
            return false;
        }
        if ((int) ($userLive->locked ?? 0) === 1) {
            return false;
        }

        $seat = $this->normalizeSeatNo($requestedSetNo);
        if ($seat === null) {
            return false;
        }

        $seatNo = (int) $seat;
        if ($seatNo < 1 || $seatNo > $this->roomSeatCount($userLive, $roomType)) {
            return false;
        }

        $rows = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('set_no', $seat)
            ->where(function ($query) {
                $query->where('status', 'locked')
                    ->orWhere('status', 'Accept')
                    ->orWhere('is_co_host_active', 'Accept');
            })
            ->get(['co_host_id', 'status', 'is_co_host_active']);

        foreach ($rows as $row) {
            if ((string) $row->status === 'locked') {
                return false;
            }
            if ((string) $row->co_host_id !== (string) $coHostId) {
                return false;
            }
        }

        return true;
    }

    protected function roomTypeToInt($roomType)
    {
        if ($roomType === 'audio') return 1;
        if ($roomType === 'video') return 2;
        if ($roomType === 'multi') return 3;
        return 0;
    }

    /**
     * Resolve a user's level via the Lavel model. Falls back to 0 if no row
     * is found rather than throwing — keeps the action path robust under
     * partial data and matches v4 behaviour.
     */
    protected function resolveUserLevel($userId)
    {
        // The authoritative per-user level lives on users.level. The Lavel
        // model is the level-RULES table (level -> threshold definitions),
        // NOT a per-user table, so the old Lavel::where('user_id') lookup
        // always returned 0 and false-rejected every cohost request with
        // level_too_low (proven on-device 2026-06-28: client level=30 but
        // V5 requestCohost returned 400 level_too_low). Read users.level
        // first; keep the Lavel lookup only as a defensive fallback.
        try {
            $user = User::where('id', $userId)->first();
            if ($user && isset($user->level) && (int) $user->level > 0) {
                return (int) $user->level;
            }
            if ($user && isset($user->lavel) && (int) $user->lavel > 0) {
                return (int) $user->lavel;
            }
        } catch (\Throwable $t) {
            // ignore, fall through to legacy lookup
        }
        try {
            $row = Lavel::where('user_id', $userId)->first();
            if ($row && isset($row->level)) {
                return (int) $row->level;
            }
            if ($row && isset($row->lavel)) {
                return (int) $row->lavel;
            }
        } catch (\Throwable $t) {
            // ignore
        }
        return 0;
    }

    protected function normalizeSeatSet($value, $seatCount)
    {
        $parts = [];
        if (is_array($value)) {
            $parts = $value;
        } else {
            $parts = preg_split('/[,\s]+/', (string) $value);
        }

        $seats = [];
        foreach ($parts as $part) {
            $seat = (int) trim((string) $part);
            if ($seat >= 1 && $seat <= $seatCount) {
                $seats[$seat] = $seat;
            }
        }
        ksort($seats);
        return $seats;
    }

    protected function lockedSeatCsv($hostId, $channel, $seatCount = 15)
    {
        $rows = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('status', 'locked')
            ->pluck('set_no')
            ->all();
        $seats = [];
        foreach ($rows as $row) {
            $seat = (int) $row;
            if ($seat >= 1 && $seat <= $seatCount) {
                $seats[$seat] = $seat;
            }
        }
        ksort($seats);
        return implode(',', array_values($seats));
    }

    protected function seatDeltaForCall($hostId, $channel, $call)
    {
        $userId = (string) ($call->co_host_id ?? '');
        $user = $userId !== '' ? User::where('id', $userId)->first() : null;

        return [
            'host_id'        => (string) $hostId,
            'co_host_id'     => $userId,
            'cohost_id'      => $userId,
            'user_id'        => $userId,
            'set_no'         => (string) ($call->set_no ?? ''),
            'mute'           => (int) ($call->mute ?? 0),
            'super_mute'     => (int) ($call->super_mute ?? 0),
            'co_host_status' => 'Accept',
            'status'         => 'Accept',
            'channelName'    => $channel,
            'channel_name'   => $channel,
            'name'           => $user ? ($user->name ?? null) : null,
            'profile'        => $user ? ($user->profile ?? null) : null,
            'frame'          => $user ? ($user->frame ?? null) : null,
            'level'          => $user ? (int) ($user->level ?? $user->lavel ?? 0) : 0,
            'is_vip'         => $user ? (int) ($user->is_vip ?? $user->vip ?? 0) : 0,
        ];
    }

    /**
     * Compose a room snapshot. Mirrors what prepareCallDetails returns in the
     * legacy controllers — keep this in sync if the controllers move first.
     */
    protected function composeSnapshot($roomType, $channel, $hostId)
    {
        $userLive = UserLive::where('channelName', $channel)
            ->where('user_id', $hostId)
            ->first();

        $host = User::where('id', $hostId)->first();

        // --- Top-bar balance/star (parity with GiftBalanceService /
        // prepareCallDetails). The client hydrates host_balance / star /
        // star_complete_parcent and each seat's `balance` from THIS snapshot;
        // without them the host + cohost balance chips render 0 (the snapshot
        // used to omit these fields entirely). host_balance = previous_coin +
        // this-month gift income; cohost balance = gifts received in-channel;
        // star from today's received gifts. ---
        $startDate = now()->startOfMonth()->toDateString();
        $endDate   = now()->endOfMonth()->toDateString();

        $hostMonthlyGift = (int) DB::table('gifts')
            ->where('reciever_id', $hostId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('value');
        $hostBalance = ((int) ($host->previous_coin ?? 0)) + $hostMonthlyGift;
        $todayGift   = (int) ($host->today_gifts_received_value ?? 0);

        $levels = [
            [0, 50000, 1, 50000],
            [50000, 200000, 2, 200000],
            [200000, 500000, 3, 500000],
            [500000, 1000000, 4, 1000000],
            [1000000, 2000000, 5, 2000000],
            [2000000, PHP_INT_MAX, 5, 20000000],
        ];
        $star = 0;
        $nextLevelAmount = 1;
        foreach ($levels as $lv) {
            if ($todayGift >= $lv[0] && $todayGift < $lv[1]) {
                $star = $lv[2];
                $nextLevelAmount = $lv[3];
                break;
            }
        }
        $starPercent = ($nextLevelAmount > 0)
            ? intval(($todayGift / $nextLevelAmount) * 100)
            : 0;

        $coHostRows = LiveCall::where('host_id', $hostId)
            ->where('channelName', $channel)
            ->where('is_co_host_active', 'Accept')
            ->get();

        // Per-cohost in-channel gift totals in ONE grouped query.
        $coHostGiftTotals = [];
        $cohostIds = $coHostRows->pluck('co_host_id')->filter()->unique()->all();
        if (!empty($cohostIds)) {
            $coHostGiftTotals = DB::table('gifts')
                ->where('channelName', $channel)
                ->whereIn('reciever_id', $cohostIds)
                ->groupBy('reciever_id')
                ->select('reciever_id', DB::raw('SUM(value) as total_value'))
                ->pluck('total_value', 'reciever_id')
                ->toArray();
        }

        $coHostList = $coHostRows
            ->map(function ($c) use ($coHostGiftTotals) {
                return [
                    'call_id'    => $c->id,
                    'co_host_id' => (string) $c->co_host_id,
                    'set_no'     => $c->set_no,
                    'mute'       => (int) $c->mute,
                    'super_mute' => (int) ($c->super_mute ?? 0),
                    'balance'    => (int) ($coHostGiftTotals[$c->co_host_id] ?? 0),
                ];
            })
            ->all();

        return [
            'room_type'    => $roomType,
            'channel_name' => $channel,
            'host_id'      => (string) $hostId,
            'live'         => $userLive ? 1 : 0,
            'is_live'      => $userLive ? 1 : 0,
            'host_list'    => [[
                'user_id' => (string) $hostId,
                'name'    => $host ? $host->name : null,
                'profile' => $host ? ($host->profile ?? null) : null,
                'balance' => $hostBalance,
            ]],
            'co_host_list' => $coHostList,
            'host_balance'          => $hostBalance,
            'star'                  => $star,
            'star_complete_parcent' => $starPercent,
            'locked'       => (int) ($userLive->locked ?? 0),
            'locked_seats' => $this->lockedSeatCsv($hostId, $channel, $this->roomSeatCount($userLive, $roomType)),
            'mute'         => (int) ($userLive->mute ?? 0),
            'siteNumber'   => $userLive->siteNumber ?? null,
        ];
    }

    /**
     * Resolve the room-type-specific adapter from the container.
     */
    protected function adapterFor($roomType)
    {
        switch ($roomType) {
            case 'audio': return app(AudioAdapter::class);
            case 'video': return app(VideoAdapter::class);
            case 'multi': return app(MultiAdapter::class);
        }
        throw new \InvalidArgumentException("v5 action: no adapter for '{$roomType}'");
    }

    /**
     * Defensive adapter dispatch — never let an adapter failure abort the
     * action. Agent C's real implementations will return ['ok' => true].
     */
    protected function dispatchAdapter($roomType, $method, $channel, $hostId, array $body)
    {
        try {
            $adapter = $this->adapterFor($roomType);
            if ($adapter instanceof AdapterInterface && method_exists($adapter, $method)) {
                return $adapter->{$method}($channel, $hostId, $body);
            }
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService adapter dispatch failed', [
                'room_type' => $roomType,
                'method'    => $method,
                'channel'   => $channel,
                'error'     => $t->getMessage(),
            ]);
        }
        return ['ok' => true, 'envelope' => null];
    }

    private function hasCommentMutesHostId(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        try {
            $cached = \Illuminate\Support\Facades\Schema::hasColumn('comment_mutes', 'host_id');
        } catch (\Throwable $t) {
            $cached = false;
        }
        return $cached;
    }

    private function pendingListEventType($roomType): string
    {
        if ($roomType === 'video') {
            return 'video.call.pending_list';
        }
        if ($roomType === 'multi') {
            return 'multi.call.pending_list';
        }
        return 'audio.call.pending_list';
    }

    private function emitSeatCleared($roomType, $channel, $hostId, $coHostId, $seatNo, $actorId, $reason): void
    {
        if ($seatNo === null || $seatNo === '') {
            return;
        }

        try {
            app(RoomSeatUpdateService::class)->emitSeatChange(
                $roomType,
                $channel,
                (int) $seatNo,
                [
                    'cleared'    => true,
                    'co_host_id' => (string) $coHostId,
                    'host_id'    => (string) $hostId,
                    'reason'     => (string) $reason,
                ],
                (string) $actorId
            );
        } catch (\Throwable $t) {
            Log::warning('V5 RoomActionService seat-delta emit failed (clear)', [
                'channel' => $channel,
                'seat_no' => $seatNo,
                'reason'  => $reason,
                'error'   => $t->getMessage(),
            ]);
        }
    }

    private function hasLiveCallsMuteUpdatedAt(): bool
    {
        static $cached = null;
        if ($cached !== null) {
            return $cached;
        }
        try {
            $cached = \Illuminate\Support\Facades\Schema::hasColumn('live_calls', 'mute_updated_at');
        } catch (\Throwable $t) {
            $cached = false;
        }
        return $cached;
    }
}
