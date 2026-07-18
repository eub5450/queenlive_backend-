<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\PortalRecharge;
use App\Models\PortalTransfer;
use App\Models\Setting;
use App\Models\User;
use App\Support\SystemSettingValueHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProtalController extends Controller
{
    public function profile()
    {
        $id = Auth::id();
        $countryId = (int) Auth::user()->country_id;

        $data['protal_recharge'] = PortalRecharge::where('user_id', $id)
            ->where('master_protal_id', $id)
            ->where('is_recall', 0)
            ->sum('amount');

        $data['protal_transfer'] = PortalRecharge::where('recharge_by', $id)->sum('amount');
        $data['protal_recharge_details'] = PortalRecharge::where('user_id', $id)
            ->where('master_protal_id', $id)
            ->where('is_recall', 0)
            ->orderBy('id', 'desc')
            ->get();

        $data['protal_transfer_details'] = PortalRecharge::where('recharge_by', $id)
            ->orderBy('id', 'desc')
            ->get();

        $data['protal_users'] = User::where('status', 1)
            ->where('is_coin_protal_active', 1)
            ->where('country_id', $countryId)
            ->where('id', '!=', $id)
            ->get();

        return view('author.protal.index')->with($data);
    }

    public function TransferStore(Request $request)
    {
        $request->validate([
            'protal_id' => 'required',
            'deposit' => 'required|numeric|min:1',
        ]);

        $setting = Setting::find(1) ?: Setting::query()->first();
        $minimumRechargeAmount = SystemSettingValueHelper::portalMinRechargeAmount($setting);
        if ((int) round((float) $request->deposit) < $minimumRechargeAmount) {
            return Redirect()->back()->with([
                'messege' => 'Minimum Recharge Amount ' . $minimumRechargeAmount,
                'alert-type' => 'error',
            ]);
        }

        $id = Auth::id();
        $countryId = (int) Auth::user()->country_id;
        $targetPortal = User::where('id', $request->protal_id)
            ->where('status', 1)
            ->where('is_coin_protal_active', 1)
            ->where('country_id', $countryId)
            ->first();

        if (!$targetPortal) {
            return Redirect()->back()->with([
                'messege' => 'Portal user not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $protalRecharge = PortalRecharge::where('user_id', $id)
            ->where('master_protal_id', $id)
            ->where('is_recall', 0)
            ->sum('amount');

        $protalTransfer = PortalRecharge::where('recharge_by', $id)->sum('amount');
        $balance = $protalRecharge - $protalTransfer;

        if ($balance < $request->deposit) {
            return Redirect()->back()->with([
                'messege' => 'Please check your balance',
                'alert-type' => 'error',
            ]);
        }

        $deposit = new PortalRecharge;
        $deposit->user_id = $targetPortal->id;
        $deposit->trxid = 'mp-' . rand(2586, 589898);
        $deposit->amount = $request->deposit;
        $deposit->date = date('Y-m-d');
        $deposit->recharge_by = $id;
        $deposit->status = 'Approved';
        $deposit->save();

        return Redirect()->back()->with([
            'messege' => 'Portal deposit successful',
            'alert-type' => 'success',
        ]);
    }

    public function Index()
    {
        $id = Auth::id();
        $countryId = (int) Auth::user()->country_id;

        $data = User::where('status', 1)
            ->where('is_coin_protal_active', 1)
            ->where('country_id', $countryId)
            ->where('id', '!=', $id)
            ->get();

        return view('author.protal.manage', compact('data'));
    }

    public function RecallCreate()
    {
        $countryId = (int) Auth::user()->country_id;
        $users = User::where('country_id', $countryId)->where('status', 1)->orderBy('id')->get(['id', 'name', 'balance']);
        $protals = User::where('country_id', $countryId)->where('is_coin_protal_active', 1)->where('status', 1)->orderBy('id')->get(['id', 'name']);

        return view('author.protal.recall_create', compact('users', 'protals'));
    }

    public function RecallStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|numeric',
            'amount' => 'required|numeric|min:1',
            'protal_id' => 'required|numeric',
        ]);

        $countryId = (int) Auth::user()->country_id;
        $user = User::where('id', $request->user_id)->where('country_id', $countryId)->first();
        $portal = User::where('id', $request->protal_id)
            ->where('country_id', $countryId)
            ->where('is_coin_protal_active', 1)
            ->first();

        if (!$user || !$portal) {
            return Redirect()->back()->with([
                'messege' => 'User or portal not found for this country',
                'alert-type' => 'error',
            ]);
        }

        if ($user->balance < $request->amount) {
            return Redirect()->back()->with([
                'messege' => 'User balance is not enough',
                'alert-type' => 'warning',
            ]);
        }

        $setting = Setting::find(1) ?: Setting::query()->first();
        $portalPercent = SystemSettingValueHelper::recallPortalPercentage($setting);
        $companyPercent = SystemSettingValueHelper::recallCompanyPercentage($setting);
        $companyUserId = SystemSettingValueHelper::recallCompanyUserId($setting);

        if (abs(($portalPercent + $companyPercent) - 100) > 0.01) {
            return Redirect()->back()->with([
                'messege' => 'Recall setting is invalid. Portal and company percentage must total 100.',
                'alert-type' => 'error',
            ]);
        }

        if ($companyPercent > 0 && !$companyUserId) {
            return Redirect()->back()->with([
                'messege' => 'Recall company user id is missing in system setting.',
                'alert-type' => 'error',
            ]);
        }

        $amount = (int) round((float) $request->amount);

        try {
            DB::transaction(function () use ($user, $portal, $amount, $portalPercent, $companyPercent, $companyUserId) {
                $lockedUser = User::where('id', $user->id)->lockForUpdate()->firstOrFail();
                $lockedPortal = User::where('id', $portal->id)->lockForUpdate()->firstOrFail();
                $companyUser = null;

                if ($companyPercent > 0 && $companyUserId) {
                    $companyUser = User::where('id', $companyUserId)->lockForUpdate()->first();
                }

                if ($lockedUser->balance < $amount) {
                    throw new \RuntimeException('USER_BALANCE_LOW');
                }

                if ($companyPercent > 0 && !$companyUser) {
                    throw new \RuntimeException('COMPANY_USER_MISSING');
                }

                $portalAmount = (int) round($amount * ($portalPercent / 100));
                $companyAmount = max(0, $amount - $portalAmount);

                $lockedUser->balance -= $amount;
                $lockedUser->save();

                if ($portalAmount > 0) {
                    $deposit = new PortalRecharge;
                    $deposit->user_id = $lockedPortal->id;
                    $deposit->trxid = 'recall-' . rand(2586, 589898);
                    $deposit->amount = $portalAmount;
                    $deposit->date = date('Y-m-d');
                    $deposit->recharge_by = Auth::id();
                    $deposit->status = 'Approved';
                    $deposit->is_recall = 1;
                    $deposit->save();
                }

                if ($companyAmount > 0 && $companyUser) {
                    $companyUser->balance += $companyAmount;
                    $companyUser->save();
                }
            });
        } catch (\RuntimeException $exception) {
            return Redirect()->back()->with([
                'messege' => $exception->getMessage() === 'USER_BALANCE_LOW'
                    ? 'User balance is not enough'
                    : 'Recall company user not found in system setting',
                'alert-type' => $exception->getMessage() === 'USER_BALANCE_LOW' ? 'warning' : 'error',
            ]);
        } catch (\Throwable $exception) {
            return Redirect()->back()->with([
                'messege' => 'Something went wrong during recall',
                'alert-type' => 'error',
            ]);
        }

        return Redirect()->route('country.author.protal-recall-list')->with([
            'messege' => 'Location recall successful',
            'alert-type' => 'success',
        ]);
    }

    public function RecallIndex()
    {
        $countryId = (int) Auth::user()->country_id;
        $data = PortalRecharge::join('users', 'users.id', '=', 'portal_recharges.user_id')
            ->where('portal_recharges.is_recall', 1)
            ->where('users.country_id', $countryId)
            ->orderBy('portal_recharges.id', 'desc')
            ->select('portal_recharges.*', 'users.name as portal_name')
            ->get();

        return view('author.protal.recall_index', compact('data'));
    }
}
