<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\FanClubService;
use App\Services\V5\RoomBroadcastService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RevenueRewardController extends Controller
{
    public function fanClub()
    {
        $service = new FanClubService(app(RoomBroadcastService::class));
        $tiers = $service->tiers(true);
        $stats = [
            'active' => $this->subscriptionCount('active'),
            'grace' => $this->subscriptionCount('grace'),
            'expired' => $this->subscriptionCount('expired'),
            'canceled' => $this->subscriptionCount('canceled'),
        ];

        return view('backend.revenue_rewards.fanclub', compact('tiers', 'stats'));
    }

    public function saveFanClubTier(Request $request)
    {
        $validated = $request->validate([
            'tier' => 'required|string|max:24',
            'price' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1|max:365',
            'perks_json' => 'nullable|string',
            'enabled' => 'nullable',
            'sort_order' => 'nullable|integer|min:-1000|max:1000',
        ]);

        $validated['enabled'] = $request->has('enabled') ? 1 : 0;
        $validated['perks_json'] = trim((string) ($validated['perks_json'] ?? '{}')) ?: '{}';

        (new FanClubService(app(RoomBroadcastService::class)))->upsertTier($validated);

        return redirect()->back()->with([
            'messege' => 'Fan Club tier saved successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function combo()
    {
        $settings = $this->comboSettings();
        return view('backend.revenue_rewards.combo', compact('settings'));
    }

    public function saveCombo(Request $request)
    {
        $validated = $request->validate([
            'combo_decay_ms' => 'required|integer|min:800|max:15000',
            'combo_max' => 'required|integer|min:1|max:9999',
            'combo_milestones' => 'required|string|max:120',
            'combo_enabled' => 'nullable',
        ]);

        if (!Schema::hasTable('combo_settings')) {
            return redirect()->back()->with([
                'messege' => 'combo_settings table missing. Run migration first.',
                'alert-type' => 'error',
            ]);
        }

        DB::table('combo_settings')->updateOrInsert(
            ['id' => 1],
            [
                'combo_enabled' => $request->has('combo_enabled') ? 1 : 0,
                'combo_decay_ms' => (int) $validated['combo_decay_ms'],
                'combo_max' => (int) $validated['combo_max'],
                'combo_milestones' => $this->sanitizeMilestones($validated['combo_milestones']),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->back()->with([
            'messege' => 'Combo settings saved successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function checkin()
    {
        $rows = Schema::hasTable('checkin_rewards')
            ? DB::table('checkin_rewards')->orderBy('day')->get()
            : collect();

        $todayClaims = 0;
        $totalClaims = 0;
        if (Schema::hasTable('user_checkins')) {
            $totalClaims = (int) DB::table('user_checkins')->count();
            $dateColumn = Schema::hasColumn('user_checkins', 'checkin_date')
                ? 'checkin_date'
                : (Schema::hasColumn('user_checkins', 'last_checkin_date')
                    ? 'last_checkin_date'
                    : null);
            if ($dateColumn !== null) {
                $todayClaims = (int) DB::table('user_checkins')
                    ->where($dateColumn, now()->toDateString())
                    ->count();
            }
        }

        $stats = [
            'today_claims' => $todayClaims,
            'total_claims' => $totalClaims,
        ];

        return view('backend.revenue_rewards.checkin', compact('rows', 'stats'));
    }

    public function saveCheckin(Request $request)
    {
        $validated = $request->validate([
            'day' => 'required|integer|min:1|max:30',
            'reward_amount' => 'required|integer|min:0|max:1000000',
            'is_active' => 'nullable',
        ]);

        if (!Schema::hasTable('checkin_rewards')) {
            return redirect()->back()->with([
                'messege' => 'checkin_rewards table missing. Run migration first.',
                'alert-type' => 'error',
            ]);
        }

        DB::table('checkin_rewards')->updateOrInsert(
            ['day' => (int) $validated['day']],
            [
                'reward_amount' => (int) $validated['reward_amount'],
                'is_active' => $request->has('is_active') ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        return redirect()->back()->with([
            'messege' => 'Check-in reward saved successfully.',
            'alert-type' => 'success',
        ]);
    }

    private function subscriptionCount(string $status): int
    {
        if (!Schema::hasTable('fan_club_subscriptions')) {
            return 0;
        }

        return (int) DB::table('fan_club_subscriptions')->where('status', $status)->count();
    }

    private function comboSettings()
    {
        if (Schema::hasTable('combo_settings')) {
            $row = DB::table('combo_settings')->where('id', 1)->first();
            if ($row) {
                return $row;
            }
        }

        return (object) [
            'combo_enabled' => 1,
            'combo_decay_ms' => 3500,
            'combo_max' => 99,
            'combo_milestones' => '10,50,99',
        ];
    }

    private function sanitizeMilestones(string $raw): string
    {
        $items = array_filter(array_map('trim', explode(',', $raw)), function ($item) {
            return ctype_digit($item) && (int) $item > 0;
        });

        return implode(',', array_values(array_unique($items))) ?: '10,50,99';
    }
}
