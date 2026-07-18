<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Models\UserLive;
use App\Services\V5\RoomActionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RoomActionController extends Controller
{
    /** @var RoomActionService */
    private $rooms;

    public function __construct(RoomActionService $rooms)
    {
        $this->rooms = $rooms;
    }

    public function handle(Request $request, string $roomType, string $channel, string $action): JsonResponse
    {
        $body = $this->body($request);
        $hostId = $this->hostId($body, $channel);
        if ($hostId === '') {
            return $this->fail('host_id_required', 'host_id required', 400);
        }

        try {
            $result = $this->dispatchAction($request, $roomType, $channel, $hostId, trim($action, '/'), $body);
        } catch (Throwable $e) {
            report($e);
            return $this->fail('server_error', 'server_error', 500);
        }

        if (!($result['ok'] ?? false)) {
            $code = (string) ($result['error'] ?? 'request_failed');
            return $this->fail($code, $code, $this->statusForError($code), $result);
        }

        return response()->json($result);
    }

    public function magicHeartSend(Request $request): JsonResponse
    {
        $body = $this->body($request);
        $authId = $request->user() ? trim((string) $request->user()->id) : '';
        if ($authId !== '') {
            $body['user_id'] = $authId;
        }

        try {
            $result = $this->rooms->sendMagicHeart($body);
        } catch (\InvalidArgumentException $e) {
            return $this->fail($e->getMessage(), $e->getMessage(), 400);
        } catch (Throwable $e) {
            report($e);
            return $this->fail('server_error', 'server_error', 500);
        }

        if (!($result['ok'] ?? false)) {
            $status = (int) ($result['code'] ?? 400);
            if ($status < 400 || $status > 599) {
                $status = 400;
            }
            return response()->json($result, $status);
        }

        return response()->json($result);
    }

    private function dispatchAction(Request $request, string $roomType, string $channel, string $hostId, string $action, array $body): array
    {
        $body['actor_user_id'] = $this->actorUserId($request, $body);

        if ($request->isMethod('get')) {
            if ($action === 'snapshot') {
                return $this->rooms->fetchSnapshot($roomType, $channel, $hostId, $body);
            }
            if ($action === 'comments') {
                return $this->rooms->fetchCommentsSince($roomType, $channel, $hostId, $body);
            }
            if ($action === 'cohost/pending' || $action === 'pending') {
                return $this->rooms->fetchPendingCohosts($roomType, $channel, $hostId, $body);
            }
        }

        if ($request->isMethod('get')) {
            return ['ok' => false, 'error' => 'method_not_allowed'];
        }

        switch ($action) {
            case 'cohost/request':
                $body['user_id'] = $this->userId($request, $body);
                return $this->rooms->requestCohost($roomType, $channel, $hostId, $body);
            case 'cohost/accept':
                return $this->rooms->acceptCohost($roomType, $channel, $hostId, $body);
            case 'cohost/reject':
                return $this->rooms->rejectCohost($roomType, $channel, $hostId, $body);
            case 'cohost/cut':
                return $this->rooms->cutCohost($roomType, $channel, $hostId, $body);
            case 'cohost/kick':
                return $this->rooms->kickCohost($roomType, $channel, $hostId, $body);
            case 'cohost/mute':
                return $this->rooms->muteCohost($roomType, $channel, $hostId, $body);
            case 'move-seat':
            case 'seat/move':
                return $this->rooms->moveSeat($roomType, $channel, $hostId, $body);
            case 'switch-seat':
            case 'seat/switch':
                return $this->rooms->switchSeat($roomType, $channel, $hostId, $body);
            case 'cohost/invite':
                return $this->rooms->inviteCohost($roomType, $channel, $hostId, $body);
            case 'kick':
                return $this->rooms->kickAudience($roomType, $channel, $hostId, $body);
            case 'comment':
                $body['user_id'] = $this->userId($request, $body);
                return $this->rooms->sendComment($roomType, $channel, $hostId, $body);
            case 'comment-mute':
                return $this->rooms->muteComment($roomType, $channel, $hostId, $body);
            case 'gift':
                $body['user_id'] = $this->userId($request, $body);
                if ($body['user_id'] !== '') {
                    $body['sender_id'] = $body['user_id'];
                    $body['sender_user_id'] = $body['user_id'];
                    $body['sander_id'] = $body['user_id'];
                }
                return $this->rooms->sendGift($roomType, $channel, $hostId, $body);
            case 'fun-sticker':
                $body['user_id'] = $this->userId($request, $body);
                if ($body['user_id'] !== '') {
                    $body['sender_id'] = $body['user_id'];
                    $body['sender_user_id'] = $body['user_id'];
                    $body['sander_id'] = $body['user_id'];
                }
                return $this->rooms->sendFunSticker($roomType, $channel, $hostId, $body);
            case 'join':
                $body['user_id'] = $this->userId($request, $body);
                return $this->rooms->joinAudience($roomType, $channel, $hostId, $body);
            case 'leave':
                $body['user_id'] = $this->userId($request, $body);
                return $this->rooms->leaveAudience($roomType, $channel, $hostId, $body);
            case 'lock':
                return $this->rooms->lockRoom($roomType, $channel, $hostId, $body);
            case 'seat-lock':
            case 'lock-seat':
            case 'unlock-seat':
                return $this->rooms->setSeatLocks($roomType, $channel, $hostId, $body);
            case 'close':
                return $this->rooms->closeRoom($roomType, $channel, $hostId, $body);
            case 'admin':
                return $this->rooms->setAdmin($roomType, $channel, $hostId, $body);
            case 'cohost/pending':
            case 'pending':
                return $this->rooms->fetchPendingCohosts($roomType, $channel, $hostId, $body);
        }

        return ['ok' => false, 'error' => 'unknown_action'];
    }

    private function body(Request $request): array
    {
        $json = $request->json()->all();
        return array_merge($request->query->all(), is_array($json) ? $json : $request->all());
    }

    private function hostId(array $body, string $channel): string
    {
        $hostId = trim((string) ($body['host_id'] ?? $body['hostId'] ?? ''));
        if ($hostId !== '') {
            return $hostId;
        }

        $live = UserLive::where('channelName', $channel)->orderByDesc('id')->first();
        return $live ? (string) $live->user_id : '';
    }

    private function userId(Request $request, array $body): string
    {
        $authId = $request->user() ? trim((string) $request->user()->id) : '';
        if ($authId !== '') {
            return $authId;
        }

        $userId = trim((string) ($body['user_id'] ?? $body['userId'] ?? $body['co_host_id'] ?? ''));
        if ($userId !== '') {
            return $userId;
        }
        return '';
    }

    private function actorUserId(Request $request, array $body): string
    {
        $authId = $request->user() ? trim((string) $request->user()->id) : '';
        if ($authId !== '') {
            return $authId;
        }

        return trim((string) ($body['actor_user_id'] ?? $body['actorUserId'] ?? $body['user_id'] ?? $body['userId'] ?? ''));
    }

    private function fail(string $code, string $message, int $status, array $extra = []): JsonResponse
    {
        return response()->json(array_merge($extra, [
            'ok' => false,
            'code' => $code,
            'error' => $code,
            'message' => $message,
        ]), $status);
    }

    private function statusForError(string $code): int
    {
        if (in_array($code, ['forbidden', 'target_protected', 'level_too_low', 'user_invisible', 'cohost_not_allowed', 'super_mute_active'], true)) {
            return 403;
        }
        if ($code === 'method_not_allowed') {
            return 405;
        }
        if (in_array($code, ['user_kicked', 'room_not_live', 'call_not_found', 'already_cohost', 'already_pending', 'cohost_in_other_room'], true)) {
            return 409;
        }
        return 400;
    }
}
