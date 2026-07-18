<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PortalRecharge;
use App\Models\Setting;
use Auth;
use Illuminate\Support\Facades\DB;
use App\Support\SystemSettingValueHelper;
class ReCallController extends Controller
{
    public function Create()
    {
    	$data['users']=User::all();
    	$data['protals']=User::where('is_coin_protal_active',1)->get();
    	return view('backend.protal.recall_create')->with($data);
    }
    public function GetData($id)
    {
    	$data=User::find($id);
    	return response()->json(['success' => 'User Find','data'=>$data]);
    }
    public function RecallStore(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|min:1',
            'protal_id' => 'required|integer|min:1',
            'amount' => 'required|numeric|min:1',
        ]);

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
            DB::transaction(function () use ($request, $amount, $portalPercent, $companyPercent, $companyUserId) {
                $user = User::where('id', $request->user_id)->lockForUpdate()->firstOrFail();
                $portalUser = User::where('id', $request->protal_id)->lockForUpdate()->firstOrFail();
                $companyUser = null;

                if ($companyPercent > 0 && $companyUserId) {
                    $companyUser = User::where('id', $companyUserId)->lockForUpdate()->first();
                }

                if ($user->balance < $amount) {
                    throw new \RuntimeException('USER_BALANCE_LOW');
                }

                if ($companyPercent > 0 && !$companyUser) {
                    throw new \RuntimeException('COMPANY_USER_MISSING');
                }

                $portalAmount = (int) round($amount * ($portalPercent / 100));
                $companyAmount = max(0, $amount - $portalAmount);

                $user->balance -= $amount;
                $user->save();

                if ($portalAmount > 0) {
                    $deposit = new PortalRecharge;
                    $deposit->user_id = $portalUser->id;
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
            $message = $exception->getMessage() === 'USER_BALANCE_LOW'
                ? 'User Balance Not Avaliabel'
                : 'Recall company user not found in system setting.';

            return Redirect()->back()->with([
                'messege' => $message,
                'alert-type' => $exception->getMessage() === 'USER_BALANCE_LOW' ? 'warning' : 'error',
            ]);
        } catch (\Throwable $exception) {
            return Redirect()->back()->with([
                'messege' => 'Somnthing Wrong!!',
                'alert-type' => 'error'
            ]);
        }

        return Redirect()->back()->with([
            'messege' => 'Protal Recall SuccessFully With deposit!',
            'alert-type' => 'success'
        ]);
    }
    public function Index()
    {
    	$data=PortalRecharge::where('is_recall',1)->orderby('id','desc')->get();
    	return view('backend.protal.recall_index',compact('data'));
    }
   
}
