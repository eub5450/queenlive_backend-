<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Withdraw;
use App\Models\User;
use App\Models\Agency;
class WithdrawController extends Controller
{
   public function Index()
   {
        $start_date = date('Y-m') . '-01';
        $end_date = date('Y-m') . '-31';
        $data = Withdraw::orderBy('id', 'desc')
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->get();

        $users = User::whereIn('id', $data->pluck('host_id')->filter()->unique()->values())
            ->get(['id', 'name', 'profile'])
            ->keyBy('id');

        $agencyUserIds = $data->pluck('agency_id')
            ->merge($data->pluck('super_agency_id'))
            ->filter()
            ->unique()
            ->values();

        $agencies = Agency::whereIn('user_id', $agencyUserIds)
            ->get(['user_id', 'name'])
            ->keyBy('user_id');

        $summary = [
            'total_withdrawals' => $data->count(),
            'approved_count' => $data->where('status', 1)->count(),
            'pending_count' => $data->where('status', '!=', 1)->count(),
            'total_basic' => $data->sum('basic_coin'),
            'total_agency_profit' => $data->sum('agency_profit'),
            'total_apps_profit' => $data->sum('apps_profit'),
            'total_points' => $data->sum('total'),
        ];

        return view('backend.withdraw.index', compact('data', 'users', 'agencies', 'summary'));
   }
}

