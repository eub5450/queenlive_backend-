<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ThomasGameLobbyController extends Controller
{
    private const CONNECTION = 'thomas_game_lobby';
    private const DEVELOPER_ONLY_USER_IDS = [1111, 22222];

    public function Index()
    {
        $this->authorizeThomasLobby();

        $data = $this->loadLobbyData();

        return view('backend.game.thomas_lobby')->with($data);
    }

    public function Update(Request $request)
    {
        $this->authorizeThomasLobby();

        $request->validate([
            'game_code' => 'required|string|max:80',
            'mode' => 'required|in:live,developer,maintenance',
        ]);

        $conn = $this->thomasConnection();
        $gameCode = trim((string) $request->input('game_code'));
        $mode = (string) $request->input('mode');
        $now = date('Y-m-d H:i:s');

        $game = $conn->table('bd_game_final_games')->where('game_code', $gameCode)->first();
        if (!$game) {
            throw ValidationException::withMessages([
                'game_code' => 'Game room not found in Thomas lobby.',
            ]);
        }

        $conn->transaction(function () use ($conn, $game, $mode, $now) {
            $isLive = $mode === 'live';
            $isDeveloperOnly = $mode === 'developer';
            $allowedUserIds = $isDeveloperOnly ? self::DEVELOPER_ONLY_USER_IDS : [];

            $conn->table('bd_game_final_games')
                ->where('id', $game->id)
                ->update([
                    'is_active' => $mode === 'maintenance' ? 0 : 1,
                    'updated_at' => $now,
                ]);

            $payload = [
                'game_status' => $isLive ? 'live' : ($isDeveloperOnly ? 'developer' : 'maintenance'),
                'maintenance_enabled' => $isLive ? 0 : 1,
                'maintenance_allowed_user_id' => $allowedUserIds ? (int) $allowedUserIds[0] : null,
                'maintenance_message' => $isDeveloperOnly
                    ? 'This game is open only for approved IDs: ' . implode(',', $allowedUserIds) . '.'
                    : ($isLive ? '' : 'This game is in maintenance. Please wait.'),
                'updated_at' => $now,
            ];

            $existing = $conn->table('bd_game_final_settings')->where('game_id', $game->id)->first();
            if ($existing) {
                $conn->table('bd_game_final_settings')->where('game_id', $game->id)->update($payload);
                return;
            }

            $payload['game_id'] = $game->id;
            $payload['created_at'] = $now;
            $conn->table('bd_game_final_settings')->insert($payload);
        });

        $this->syncThomasRuntimeAllowedUsers(
            $conn,
            $gameCode,
            $mode === 'developer' ? self::DEVELOPER_ONLY_USER_IDS : []
        );
        Cache::forget('bdgf:admin_runtime_settings');

        return redirect('admin/thomas-game-lobby')->with([
            'messege' => 'Thomas game lobby status updated successfully',
            'alert-type' => 'success',
        ]);
    }

    public function Details(Request $request)
    {
        $this->authorizeThomasLobby();

        $conn = $this->thomasConnection();
        $games = $conn->table('bd_game_final_games')
            ->where('is_active', 1)
            ->select('id', 'game_code', 'name')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $selectedGameCode = trim((string) $request->query('game_code', ''));
        $selectedGame = $games->firstWhere('game_code', $selectedGameCode) ?: $games->first();

        if (!$selectedGame) {
            return view('backend.game.thomas_lobby_details')->with([
                'domainName' => $this->thomasDomain(),
                'games' => $games,
                'selectedGame' => null,
                'boards' => collect(),
                'boardKeys' => [],
                'roundRows' => collect(),
                'betRows' => collect(),
            ]);
        }

        $boards = $conn->table('bd_game_final_boards')
            ->where('game_id', $selectedGame->id)
            ->where('is_active', 1)
            ->select('canonical_key', 'frontend_key', 'display_name', 'payout_multiplier', 'display_order')
            ->orderBy('display_order')
            ->get();

        $rounds = $conn->table('bd_game_final_rounds as r')
            ->leftJoin('bd_game_final_settlements as st', 'st.game_round_id', '=', 'r.id')
            ->where('r.game_id', $selectedGame->id)
            ->select(
                'r.id',
                'r.round_no',
                'r.status',
                'r.winner_board_key',
                'r.decision_mode',
                'r.decision_snapshot_json',
                'r.start_at',
                'r.created_at',
                'st.settlement_status',
                'st.total_bet_amount',
                'st.total_payout_amount',
                'st.total_winning_bets',
                'st.total_losing_bets',
                'st.net_house_result'
            )
            ->orderByDesc('r.start_at')
            ->orderByDesc('r.id')
            ->limit(40)
            ->get();

        $roundIds = $rounds->pluck('id')->filter()->values()->all();
        $summaries = collect();
        $bets = collect();

        if (!empty($roundIds)) {
            $summaries = $conn->table('bd_game_final_bet_summaries')
                ->whereIn('game_round_id', $roundIds)
                ->select('game_round_id', 'canonical_board_key', 'total_amount', 'total_players', 'potential_payout')
                ->get();

            $bets = $conn->table('bd_game_final_bets as b')
                ->leftJoin('users as u', 'u.id', '=', 'b.user_id')
                ->leftJoin('bd_game_final_settlement_items as si', 'si.game_bet_id', '=', 'b.id')
                ->whereIn('b.game_round_id', $roundIds)
                ->select(
                    'b.id',
                    'b.game_round_id',
                    'b.round_no',
                    'b.user_id',
                    'u.name as user_name',
                    'u.profile as user_profile',
                    'b.amount',
                    'b.frontend_board_key',
                    'b.canonical_board_key',
                    'b.payout_multiplier',
                    'b.potential_win',
                    'b.win_balance',
                    'b.status',
                    'b.created_at',
                    'si.win_amount',
                    'si.net_result',
                    'si.result_status',
                    'si.wallet_before',
                    'si.wallet_after'
                )
                ->orderByDesc('b.id')
                ->get();
        }

        $boardKeys = $boards->pluck('canonical_key')
            ->merge($summaries->pluck('canonical_board_key'))
            ->merge($bets->pluck('canonical_board_key'))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $boardMeta = collect($boardKeys)->mapWithKeys(function ($key) use ($boards) {
            $board = $boards->firstWhere('canonical_key', $key);

            return [$key => [
                'key' => $key,
                'label' => $board ? (string) $board->display_name : $this->boardLabel($key),
                'icon' => $this->boardIcon($key),
            ]];
        })->all();

        $summaryByRound = $summaries->groupBy('game_round_id');
        $roundRows = $rounds->map(function ($round) use ($summaryByRound, $boardKeys) {
            $summaryRows = $summaryByRound->get($round->id, collect());
            $boardTotals = [];

            foreach ($boardKeys as $key) {
                $boardTotals[$key] = (float) optional($summaryRows->firstWhere('canonical_board_key', $key))->total_amount;
            }

            $totalBet = (float) ($round->total_bet_amount ?? array_sum($boardTotals));
            $totalPayout = (float) ($round->total_payout_amount ?? 0);

            return [
                'id' => (int) $round->id,
                'round_no' => (string) $round->round_no,
                'status' => (string) $round->status,
                'settlement_status' => (string) ($round->settlement_status ?? ''),
                'winner_board_key' => (string) ($round->winner_board_key ?? ''),
                'algorithm_name' => $this->algorithmName((string) ($round->decision_mode ?? ''), (string) ($round->decision_snapshot_json ?? '')),
                'board_totals' => $boardTotals,
                'total_bet_amount' => $totalBet,
                'total_payout_amount' => $totalPayout,
                'net_house_result' => (float) ($round->net_house_result ?? ($totalBet - $totalPayout)),
                'total_winning_bets' => (int) ($round->total_winning_bets ?? 0),
                'total_losing_bets' => (int) ($round->total_losing_bets ?? 0),
                'start_at' => (string) ($round->start_at ?? $round->created_at ?? ''),
            ];
        });

        $betRows = $bets->map(function ($bet) use ($boardKeys) {
            $boardAmounts = array_fill_keys($boardKeys, 0.0);
            if (isset($boardAmounts[$bet->canonical_board_key])) {
                $boardAmounts[$bet->canonical_board_key] = (float) $bet->amount;
            }

            return [
                'id' => (int) $bet->id,
                'round_no' => (string) $bet->round_no,
                'user_id' => (int) $bet->user_id,
                'user_name' => (string) ($bet->user_name ?: 'User #' . (int) $bet->user_id),
                'user_profile' => (string) ($bet->user_profile ?? ''),
                'board_key' => (string) $bet->canonical_board_key,
                'board_amounts' => $boardAmounts,
                'amount' => (float) $bet->amount,
                'payout_multiplier' => (float) $bet->payout_multiplier,
                'potential_win' => (float) $bet->potential_win,
                'win_amount' => (float) ($bet->win_amount ?? $bet->win_balance ?? 0),
                'net_result' => (float) ($bet->net_result ?? 0),
                'status' => (string) ($bet->result_status ?: $bet->status),
                'wallet_before' => (float) ($bet->wallet_before ?? 0),
                'wallet_after' => (float) ($bet->wallet_after ?? 0),
                'created_at' => (string) ($bet->created_at ?? ''),
            ];
        });

        return view('backend.game.thomas_lobby_details')->with([
            'domainName' => $this->thomasDomain(),
            'games' => $games,
            'selectedGame' => $selectedGame,
            'boards' => $boardMeta,
            'boardKeys' => $boardKeys,
            'roundRows' => $roundRows,
            'betRows' => $betRows,
        ]);
    }

    private function authorizeThomasLobby(): void
    {
        if (!in_array((int) Auth::id(), [1, 22222], true)) {
            abort(403, 'Thomas Game Lobby permission required.');
        }
    }

    private function loadLobbyData(): array
    {
        $conn = $this->thomasConnection();
        $previewIdentity = $this->previewIdentity($conn);
        $previewUserId = (int) ($previewIdentity['user_id'] ?? 0);
        $previewAccessKey = (string) ($previewIdentity['access_key'] ?? '');
        $previewAvailable = $previewUserId > 0;
        $lobbyUrl = $previewAccessKey !== ''
            ? $this->previewLobbyUrl($previewAccessKey)
            : 'https://' . $this->thomasDomain() . '/play_bd_game';
        $ready = Schema::connection(self::CONNECTION)->hasTable('bd_game_final_games')
            && Schema::connection(self::CONNECTION)->hasTable('bd_game_final_settings');

        if (!$ready) {
            return [
                'domainName' => $this->thomasDomain(),
                'lobbyUrl' => $lobbyUrl,
                'adminUrl' => 'https://' . $this->thomasDomain() . '/thomas-admin',
                'previewAvailable' => $previewAvailable,
                'previewAccessLabel' => $this->previewAccessLabel(),
                'balanceSummary' => $this->thomasBalanceSummary($conn),
                'games' => collect(),
                'summary' => [
                    'total' => 0,
                    'live' => 0,
                    'developer' => 0,
                    'maintenance' => 0,
                ],
                'familySummary' => [],
                'errorMessage' => 'Thomas game tables are not ready.',
            ];
        }

        $runtimeAllowedUsers = $this->runtimeAllowedUserMap($conn);

        $games = $conn->table('bd_game_final_games as g')
            ->leftJoin('bd_game_final_settings as s', 's.game_id', '=', 'g.id')
            ->select(
                'g.id',
                'g.game_code',
                'g.name',
                'g.frontend_slug',
                'g.is_active',
                'g.sort_order',
                's.game_status',
                's.maintenance_enabled',
                's.maintenance_allowed_user_id',
                's.maintenance_message',
                's.ui_meta_json'
            )
            ->orderBy('g.sort_order')
            ->get()
            ->map(function ($game) use ($previewUserId, $previewAvailable, $runtimeAllowedUsers) {
                $meta = json_decode((string) ($game->ui_meta_json ?? ''), true);
                $banner = is_array($meta) ? trim((string) ($meta['lobby_banner_url'] ?? '')) : '';
                $gameCode = (string) $game->game_code;
                $allowedUserIds = $runtimeAllowedUsers[$gameCode] ?? [];
                $legacyAllowedUserId = (int) ($game->maintenance_allowed_user_id ?? 0);
                if ($legacyAllowedUserId > 0 && !in_array($legacyAllowedUserId, $allowedUserIds, true)) {
                    $allowedUserIds[] = $legacyAllowedUserId;
                }
                $status = $this->normalizeThomasStatus(
                    (string) ($game->game_status ?? ''),
                    (bool) ($game->maintenance_enabled ?? false),
                    $allowedUserIds,
                    (int) $game->is_active === 1
                );
                $isLive = $status === 'live';

                return [
                    'id' => (int) $game->id,
                    'game_code' => $gameCode,
                    'name' => (string) $game->name,
                    'frontend_slug' => (string) ($game->frontend_slug ?? $game->game_code),
                    'enabled' => (int) $game->is_active === 1,
                    'family_label' => $this->gameFamilyLabel($gameCode),
                    'banner' => $this->resolveLobbyBannerUrl($gameCode, $banner),
                    'is_live' => $isLive,
                    'status_key' => $status,
                    'status_label' => $this->statusLabel($status),
                    'access_label' => $this->accessLabel($status, $allowedUserIds),
                    'allowed_user_id' => $allowedUserIds ? (int) $allowedUserIds[0] : null,
                    'allowed_user_ids' => $allowedUserIds,
                    'maintenance_message' => (string) ($game->maintenance_message ?? ''),
                    'preview_url' => $previewAvailable ? $this->previewGameUrl($gameCode, $previewUserId) : '',
                    'details_url' => url('admin/thomas-game-lobby/details') . '?game_code=' . rawurlencode($gameCode),
                ];
            });

        return [
            'domainName' => $this->thomasDomain(),
            'lobbyUrl' => $lobbyUrl,
            'adminUrl' => 'https://' . $this->thomasDomain() . '/thomas-admin',
            'previewAvailable' => $previewAvailable,
            'previewAccessLabel' => $this->previewAccessLabel(),
            'balanceSummary' => $this->thomasBalanceSummary($conn),
            'games' => $games,
            'summary' => [
                'total' => $games->count(),
                'live' => $games->where('is_live', true)->count(),
                'developer' => $games->where('status_key', 'developer')->count(),
                'maintenance' => $games->where('status_key', 'maintenance')->count(),
            ],
            'familySummary' => $this->familySummary($games),
            'errorMessage' => null,
        ];
    }

    private function normalizeThomasStatus(string $rawStatus, bool $maintenanceEnabled, array $allowedUserIds, bool $enabled): string
    {
        $rawStatus = strtolower(trim($rawStatus));

        if (!$enabled) {
            return 'maintenance';
        }

        return match ($rawStatus) {
            'active' => 'live',
            'inactive' => 'maintenance',
            'live', 'developer', 'maintenance' => $rawStatus,
            default => $maintenanceEnabled
                ? (!empty($allowedUserIds) ? 'developer' : 'maintenance')
                : 'live',
        };
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'live' => 'All User ON',
            'developer' => 'OFF ID Only',
            default => 'OFF',
        };
    }

    private function accessLabel(string $status, array $allowedUserIds): string
    {
        return match ($status) {
            'live' => 'Open for all users',
            'developer' => $allowedUserIds ? 'Only IDs: ' . implode(', ', $allowedUserIds) : 'Special ID required',
            default => 'Hidden for normal users',
        };
    }

    private function runtimeAllowedUserMap($conn): array
    {
        try {
            if (!Schema::connection(self::CONNECTION)->hasTable('bd_game_final_runtime_settings')) {
                return [];
            }

            $raw = (string) $conn->table('bd_game_final_runtime_settings')
                ->where('key', 'maintenance_allowed_user_ids')
                ->value('value');
            $decoded = json_decode($raw, true);

            if (!is_array($decoded)) {
                return [];
            }

            $out = [];
            foreach ($decoded as $gameCode => $ids) {
                $out[(string) $gameCode] = $this->normalizeAllowedUserIds($ids);
            }

            return $out;
        } catch (\Throwable $e) {
            return [];
        }
    }

    private function syncThomasRuntimeAllowedUsers($conn, string $gameCode, array $allowedUserIds): void
    {
        if (!Schema::connection(self::CONNECTION)->hasTable('bd_game_final_runtime_settings')) {
            return;
        }

        $map = $this->runtimeAllowedUserMap($conn);
        $allowedUserIds = $this->normalizeAllowedUserIds($allowedUserIds);

        if ($allowedUserIds) {
            $map[$gameCode] = $allowedUserIds;
        } else {
            unset($map[$gameCode]);
        }

        $now = date('Y-m-d H:i:s');
        $this->upsertRuntimeSetting($conn, 'maintenance_allowed_user_ids', json_encode($map), $now);

        $currentVersion = (int) $conn->table('bd_game_final_runtime_settings')
            ->where('key', 'config_version')
            ->value('value');
        $this->upsertRuntimeSetting($conn, 'config_version', (string) max(1, $currentVersion + 1), $now);
        $this->upsertRuntimeSetting($conn, 'config_updated_at', date('c'), $now);
    }

    private function upsertRuntimeSetting($conn, string $key, string $value, string $now): void
    {
        $existing = $conn->table('bd_game_final_runtime_settings')->where('key', $key)->first();
        if ($existing) {
            $conn->table('bd_game_final_runtime_settings')->where('key', $key)->update([
                'value' => $value,
                'updated_at' => $now,
            ]);
            return;
        }

        $conn->table('bd_game_final_runtime_settings')->insert([
            'key' => $key,
            'value' => $value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function normalizeAllowedUserIds($ids): array
    {
        if (is_string($ids)) {
            $ids = preg_split('/[\s,]+/', $ids, -1, PREG_SPLIT_NO_EMPTY);
        }

        if (!is_array($ids)) {
            return [];
        }

        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function boardLabel(string $key): string
    {
        return ucwords(str_replace(['_', '-'], ' ', $key));
    }

    private function boardIcon(string $key): string
    {
        $key = strtolower($key);
        $icons = [
            'apple' => '🍎',
            'orange' => '🍊',
            'watermelon' => '🍉',
            'cherry' => '🍒',
            'pineapple' => '🍍',
            'grapes' => '🍇',
            'lemon' => '🍋',
            'banana' => '🍌',
            'dragon' => '🐉',
            'tiger' => '🐯',
        ];

        return $icons[$key] ?? '🎯';
    }

    private function algorithmName(string $mode, string $snapshotJson): string
    {
        $mode = trim($mode);
        if ($mode !== '') {
            return $this->humanizeAlgorithmName($mode);
        }

        $snapshot = json_decode($snapshotJson, true);
        if (is_array($snapshot)) {
            foreach (['decision_mode', 'algorithm', 'algorithm_name', 'selector', 'mode', 'reason'] as $key) {
                if (!empty($snapshot[$key]) && is_scalar($snapshot[$key])) {
                    return $this->humanizeAlgorithmName((string) $snapshot[$key]);
                }
            }

            if (!empty($snapshot['manual_winner_board'])) {
                return 'Manual Winner';
            }

            if (!empty($snapshot['weighted_random_enabled'])) {
                return 'Weighted Random';
            }
        }

        return 'Auto Payout Selector';
    }

    private function humanizeAlgorithmName(string $name): string
    {
        $name = trim(str_replace(['_', '-'], ' ', $name));

        return $name !== '' ? ucwords($name) : 'Auto Payout Selector';
    }

    private function gameFamilyLabel(string $gameCode): string
    {
        if (str_starts_with($gameCode, 'teen_patti')) {
            return 'Teen Patti';
        }

        if ($gameCode === 'lucky7_pro' || $gameCode === 'lucky88_master' || str_starts_with($gameCode, 'lucky77')) {
            return 'Lucky Wheel';
        }

        if (str_starts_with($gameCode, 'fruits_loop')) {
            return 'Fruits Loop';
        }

        if ($gameCode === 'greedy') {
            return 'Greedy';
        }

        if (str_starts_with($gameCode, 'fruit_slot')) {
            return 'Fruit Slot';
        }

        return 'Live Room';
    }

    private function familySummary($games): array
    {
        $labels = ['Teen Patti', 'Fruit Slot', 'Fruits Loop', 'Lucky Wheel', 'Greedy'];
        $counts = [];

        foreach ($labels as $label) {
            $count = $games
                ->where('family_label', $label)
                ->where('status_key', 'live')
                ->count();

            if ($count > 0) {
                $counts[] = [
                    'label' => $label,
                    'count' => $count,
                ];
            }
        }

        return $counts;
    }

    private function previewIdentity($conn): array
    {
        $user = Auth::user();
        if (!$user) {
            return ['user_id' => 0, 'access_key' => ''];
        }

        try {
            if (Schema::connection(self::CONNECTION)->hasTable('users')) {
                $hasEmail = Schema::connection(self::CONNECTION)->hasColumn('users', 'email');
                $sameIdUser = $conn->table('users')
                    ->where('id', (int) Auth::id())
                    ->select($hasEmail ? ['id', 'email'] : ['id'])
                    ->first();
                if ($sameIdUser) {
                    return [
                        'user_id' => (int) $sameIdUser->id,
                        'access_key' => $hasEmail ? strtolower(trim((string) ($sameIdUser->email ?? ''))) : '',
                    ];
                }

                $email = strtolower(trim((string) ($user->email ?? '')));
                if ($email !== '' && $hasEmail) {
                    $emailUser = $conn->table('users')
                        ->whereRaw('LOWER(email) = ?', [$email])
                        ->select('id', 'email')
                        ->first();
                    if ($emailUser) {
                        return [
                            'user_id' => (int) $emailUser->id,
                            'access_key' => strtolower(trim((string) ($emailUser->email ?? ''))),
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fall back to the authenticated admin email when same-id Thomas lookup is unavailable.
        }

        return [
            'user_id' => 0,
            'access_key' => strtolower(trim((string) ($user->email ?? ''))),
        ];
    }

    private function previewAccessLabel(): string
    {
        return 'Admin #' . (int) Auth::id();
    }

    private function previewGameUrl(string $gameCode, int $userId): string
    {
        return 'https://' . $this->thomasDomain()
            . '/game/'
            . rawurlencode($gameCode)
            . '/'
            . (int) $userId;
    }

    private function previewLobbyUrl(string $accessKey): string
    {
        return 'https://' . $this->thomasDomain()
            . '/play_bd_game/'
            . rawurlencode($accessKey);
    }

    private function thomasBalanceSummary($conn): array
    {
        $amount = 0.0;

        try {
            if (
                Schema::connection(self::CONNECTION)->hasTable('bd_game_final_settings')
                && Schema::connection(self::CONNECTION)->hasColumn('bd_game_final_settings', 'decision_balance_amount')
            ) {
                $amount = (float) $conn->table('bd_game_final_settings')->sum('decision_balance_amount');
            }
        } catch (\Throwable $e) {
            $amount = 0.0;
        }

        return [
            'label' => 'Thomas Game Balance',
            'amount' => $amount,
            'formatted' => number_format($amount, 2),
        ];
    }

    private function resolveLobbyBannerUrl(string $gameCode, string $storedBanner): string
    {
        if ($storedBanner !== '') {
            return $storedBanner;
        }

        $file = $this->lobbyBannerFile($gameCode);
        if ($file === '') {
            return '';
        }

        return 'https://' . $this->thomasDomain() . '/game_final_assets/lobby_banners/' . $file;
    }

    private function lobbyBannerFile(string $gameCode): string
    {
        $map = [
            'lucky77' => 'lucky77_purple.webp',
            'lucky77_max' => 'lucky77_green.webp',
            'lucky7_pro' => 'lucky77_purple.webp',
            'lucky88_master' => 'lucky77_red_money.webp',
            'lucky77_mirage' => 'lucky77_green.webp',
            'lucky77_ironfront' => 'lucky77_red_money.webp',
            'lucky77_lotus' => 'lucky77_green.webp',
            'lucky77_nebula' => 'lucky77_purple.webp',
            'lucky77_carnival' => 'lucky77_red_money.webp',
            'fruit_slot' => 'fruit_slot_tropical_jackpot.webp',
            'fruit_slot_oasis' => 'fruit_slot_oasis.webp',
            'fruit_slot_arsenal' => 'fruit_slot_citrus.webp',
            'fruit_slot_arcade' => 'fruit_slot_arcade.webp',
            'fruit_slot_lotus' => 'fruit_slot_oasis.webp',
            'fruit_slot_glacier' => 'fruit_slot_glacier.webp',
            'greedy' => 'fruit_slot_tropical_jackpot.webp',
            'fruits_loop' => 'fruits_loop_big_win.webp',
            'fruits_loop_ruby' => 'fruits_loop_big_win.webp',
            'fruits_loop_emerald' => 'fruits_loop_big_win.webp',
            'fruits_loop_bunny' => 'bunny_fruits_loops_big_win.png',
        ];

        return $map[$gameCode] ?? '';
    }

    private function thomasConnection()
    {
        $env = $this->readEnvFile($this->thomasRoot() . '/.env');

        Config::set('database.connections.' . self::CONNECTION, [
            'driver' => 'mysql',
            'host' => $env['DB_HOST'] ?? '127.0.0.1',
            'port' => $env['DB_PORT'] ?? '3306',
            'database' => $env['DB_DATABASE'] ?? '',
            'username' => $env['DB_USERNAME'] ?? '',
            'password' => $env['DB_PASSWORD'] ?? '',
            'unix_socket' => $env['DB_SOCKET'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        DB::purge(self::CONNECTION);

        return DB::connection(self::CONNECTION);
    }

    private function readEnvFile(string $path): array
    {
        if (!is_readable($path)) {
            throw ValidationException::withMessages([
                'env' => 'Thomas game environment file is not readable.',
            ]);
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            if ((strpos($value, '"') === 0 && substr($value, -1) === '"') || (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            $values[$key] = $value;
        }

        return $values;
    }

    private function thomasRoot(): string
    {
        return '/var/www/queenlive/subdomains/thomasgamecompanyltd.queenlive.site/current';
    }

    private function thomasDomain(): string
    {
        return 'thomasgamecompanyltd.queenlive.site';
    }
}
