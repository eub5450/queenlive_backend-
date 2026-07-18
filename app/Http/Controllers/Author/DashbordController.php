<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashbordController extends Controller
{
    public function Home()
    {
        $admin = Auth::user();
        $countryId = (int) $admin->country_id;
        $country = DB::table('countries')->where('id', $countryId)->first();
        $giftTotals = $this->giftTotalsSubquery();

        $dashboard = [
            'country_id' => $countryId,
            'country_name' => $country ? ucfirst($country->name) : 'Country ' . $countryId,
            'admin_name' => $admin->name,
            'admin_email' => $admin->email,
            'total_users' => User::where('country_id', $countryId)->count(),
            'active_users' => User::where('country_id', $countryId)->where('status', 1)->count(),
            'blocked_users' => User::where('country_id', $countryId)->where('status', '!=', 1)->count(),
            'total_balance' => User::where('country_id', $countryId)->sum('balance'),
            'hold_balance' => User::where('country_id', $countryId)->sum('hold_balance'),
            'active_hosts' => User::where('country_id', $countryId)->where('is_host_id', 1)->where('status', 1)->count(),
            'pending_hosts' => User::where('country_id', $countryId)->where('is_host_id', 2)->where('status', 1)->count(),
            'agencies' => DB::table('agencies')->where('country_id', $countryId)->count(),
            'active_agencies' => DB::table('agencies')->where('country_id', $countryId)->where('status', 1)->count(),
            'pending_agencies' => DB::table('agencies')
                ->where('country_id', $countryId)
                ->where(function ($query) {
                    $query->whereNull('status')->orWhere('status', 0);
                })
                ->count(),
            'live_rooms' => DB::table('user_lives')
                ->join('users', 'users.id', '=', 'user_lives.user_id')
                ->where('users.country_id', $countryId)
                ->count(),
            'portal_recharge' => DB::table('portal_recharges')
                ->join('users', 'users.id', '=', 'portal_recharges.user_id')
                ->where('users.country_id', $countryId)
                ->where('portal_recharges.is_recall', 0)
                ->sum('portal_recharges.amount'),
            'portal_recall' => DB::table('portal_recharges')
                ->join('users', 'users.id', '=', 'portal_recharges.user_id')
                ->where('users.country_id', $countryId)
                ->where('portal_recharges.is_recall', 1)
                ->sum('portal_recharges.amount'),
            'portal_transfer' => DB::table('portal_transfers')
                ->join('users', 'users.id', '=', 'portal_transfers.portal_user_id')
                ->where('users.country_id', $countryId)
                ->sum('portal_transfers.amount'),
            'gift_sent_value' => DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('users.country_id', $countryId)
                ->sum('gifts.value'),
            'gift_received_value' => DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.reciever_id')
                ->where('users.country_id', $countryId)
                ->sum('gifts.value'),
        ];

        $recentUsers = User::where('country_id', $countryId)
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'email', 'phone', 'balance', 'is_host_id', 'is_agency', 'status', 'created_at']);

        $topHosts = User::leftJoin('host_data', 'host_data.user_id', '=', 'users.id')
            ->leftJoin('agencies', 'agencies.code', '=', 'host_data.agency_code')
            ->leftJoinSub($giftTotals, 'gift_totals', function ($join) {
                $join->on('gift_totals.reciever_id', '=', 'users.id');
            })
            ->where('users.country_id', $countryId)
            ->where('users.is_host_id', 1)
            ->orderByDesc(DB::raw('COALESCE(gift_totals.total_received, 0)'))
            ->limit(8)
            ->get([
                'users.id',
                'users.name',
                'users.phone',
                'users.balance',
                'users.status',
                'host_data.hosting_type',
                'agencies.name as agency_name',
                DB::raw('COALESCE(gift_totals.total_received, 0) as total_recived_gifts'),
            ]);

        return view('author.home', compact('dashboard', 'recentUsers', 'topHosts'));
    }

    public function Ranking()
    {
        $countryId = (int) Auth::user()->country_id;
        $country = DB::table('countries')->where('id', $countryId)->first();
        $giftTotals = $this->giftTotalsSubquery();

        $hosts = User::leftJoin('host_data', 'host_data.user_id', '=', 'users.id')
            ->leftJoin('agencies', 'agencies.code', '=', 'host_data.agency_code')
            ->leftJoinSub($giftTotals, 'gift_totals', function ($join) {
                $join->on('gift_totals.reciever_id', '=', 'users.id');
            })
            ->where('users.country_id', $countryId)
            ->where('users.is_host_id', 1)
            ->orderByDesc(DB::raw('COALESCE(gift_totals.total_received, 0)'))
            ->orderBy('users.id', 'desc')
            ->get([
                'users.id',
                'users.name',
                'users.phone',
                'users.profile',
                'users.balance',
                'users.status',
                'host_data.hosting_type',
                'agencies.name as agency_name',
                DB::raw('COALESCE(gift_totals.total_received, 0) as total_recived_gifts'),
            ]);

        return view('author.host.ranking', compact('hosts', 'country', 'countryId'));
    }

    protected function giftTotalsSubquery()
    {
        return DB::table('gifts')
            ->select('reciever_id', DB::raw('SUM(value) as total_received'))
            ->groupBy('reciever_id');
    }
}
