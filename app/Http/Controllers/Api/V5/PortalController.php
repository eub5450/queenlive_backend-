<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\PortalRecharge;
use App\Models\PortalTransfer;
use App\Models\VipList;
use App\Models\User;
use App\Models\Notification;
use Carbon\Carbon;
use App\Models\PortalRecall;
use App\Models\ProtalToPTransfer;
use App\Models\InviteReward;
use App\Models\Setting;
use App\Support\SystemSettingValueHelper;
use DB;
use Illuminate\Support\Facades\Mail;
class PortalController extends Controller
{
    public function __construct()
{
    $this->middleware('auth');
}
    public function Index(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
 if (!Auth::check()) {
        return response()->json([
            'message' => 'Unauthorized. Please login first.',
            'code' => 401
        ], 401);
    }

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $setting = Setting::find(1) ?: Setting::query()->first();
                $recharge=PortalRecharge::where('user_id',$user_id)->where('status','Approved')->sum('amount');
                $transfer=PortalTransfer::where('portal_user_id',$user_id)->sum('amount');
                $recall=PortalRecall::where('protal_id',$user_id)->sum('amount');
                $ProtalToPTransfer=ProtalToPTransfer::where('user_id',$user_id)->sum('amount');
                $ProtalToPTransferRecived=ProtalToPTransfer::where('portal_user_id',$user_id)->sum('amount');
                $balance=($recharge+$ProtalToPTransferRecived)-($transfer+$recall+$ProtalToPTransfer);
                $recharge_list=PortalRecharge::where('user_id',$user_id)->where('status','Approved')->orderby('id','desc')->limit(20)->get();
                $transfer_list = DB::table('portal_transfers')
                ->join('users', 'users.id', '=', 'portal_transfers.user_id')
                ->select(
                    'portal_transfers.*', 
                    DB::raw('LEFT(users.name, 7) as name')
                )
                ->where('portal_transfers.portal_user_id', $user_id)
                ->orderBy('portal_transfers.id', 'desc')
                ->limit(50)
                ->get();
                $protal_list=ProtalToPTransfer::where('user_id',$user_id)->orderby('id','desc')->limit(20)->get();
                $protal_transfer_received_list=ProtalToPTransfer::where('portal_user_id',$user_id)->orderby('id','desc')->limit(20)->get();
                array_push($response,array(
                    'message'=>'Data Found! ',
                    'balance'=>$balance,
                    'recharge_list'=>$recharge_list,
                    'transfer_list'=>$transfer_list,
                    'protal_list'=>$protal_list,
                    'protal_transfer_received_list'=>$protal_transfer_received_list,
                    'minimum_recharge_amount' => SystemSettingValueHelper::portalMinRechargeAmount($setting),
                    'vip_offer_active' => SystemSettingValueHelper::vipDiscountEnabled($setting) ? 1 : 0,
                    'vip_offer_percentage' => SystemSettingValueHelper::vipDiscountPercentage($setting),
                    'recharge_offer_bonus_active' => SystemSettingValueHelper::rechargeOfferRewardEnabled($setting) ? 1 : 0,
                    'recharge_offer_bonus_percentage' => SystemSettingValueHelper::rechargeOfferRewardPercentage($setting),
                    'code'=>'200'
                ));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

        }else{
           array_push($response,array('message'=>'Unauthorized','code'=>'401'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
  public function Transfer(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user()->id;
        $amount = $request->amount;
        $transfer_member_id = $request->transfer_member_id;
       $transactionId = Auth::id() . '-'.$user_id.'-' . uniqid('Recharge_') . '_Api_'.$transfer_member_id;

        info('📍 Portal Transfer', [
            'transaction_id' => $transactionId,
            'user_id' => $user_id,
            'receiver_id' => $request->transfer_member_id,
            'amount' => $request->amount,
            'ip' => $request->header('CF-Connecting-IP') ?? $request->ip(),
            'user_agent' => $request->userAgent(),
            'user_agent_data' => $request->header(),
            'time' => now()->toDateTimeString()
        ]);
        

 if (!Auth::check()) {
        return response()->json([
            'message' => 'Unauthorized. Please login first.',
            'code' => 401
        ], 401);
    }
    if (Auth::id() != $user_id) {
    return response()->json([
        'message' => 'Unauthorized. User ID mismatch.',
        'code' => 403
    ], 403);
}
     info('TransferLog: ' . $user_id);
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                //  $otp = rand(100000, 999999);
        
                //     Mail::raw("Your OTP is: {$otp}", function($message) {
                //         $message->to('jahirvevo@gmail.com')
                //                 ->subject('OTP Verification');
                //     });
                    
                    // Mail::raw('Test Email', function ($message) {
                    //     $message->to('jahirvevo@gmail.com')
                    //             ->subject('Test Mail');
                    // });
                // P0-B5 FIX (Agent SF 2026-06-28): portal balance read from
                // unlocked SUMs + recipient credit with no lock/txn (lost-update
                // + SUM-check race -> portal double-spend). Wrap the whole
                // mutation in a txn; lock the PORTAL user row as a serialization
                // mutex so concurrent transfers can't both pass the SUM check,
                // and lock the RECIPIENT row for the balance credit.
                $result = DB::transaction(function () use ($user_id, $amount, $transfer_member_id, $transactionId, $response) {
                // Mutex: serialize all transfers for this portal user.
                User::where('id', $user_id)->lockForUpdate()->first();
                $setting=Setting::find(1) ?: Setting::query()->first();
                $minimumRechargeAmount = SystemSettingValueHelper::portalMinRechargeAmount($setting);
                $recharge=PortalRecharge::where('user_id',$user_id)->where('status','Approved')->sum('amount');
                $transfer=PortalTransfer::where('portal_user_id',$user_id)->sum('amount');
                $recall=PortalRecall::where('protal_id',$user_id)->sum('amount');
                $ProtalToPTransfer=ProtalToPTransfer::where('user_id',$user_id)->sum('amount');
                $ProtalToPTransferRecived=ProtalToPTransfer::where('portal_user_id',$user_id)->sum('amount');
                $balance=($recharge+$ProtalToPTransferRecived)-($transfer+$recall+$ProtalToPTransfer);
            if($balance>=$amount){
              if($amount >= $minimumRechargeAmount){
                    $user=User::where('id',$transfer_member_id)->lockForUpdate()->first();
                if($user)
                {
                
                 $transfer=new PortalTransfer;
                 $transfer->portal_user_id=$user_id;
                 $transfer->user_id=$transfer_member_id;
                 $transfer->amount=$amount;
                $transfer->trxid = $transactionId;
                 $transfer->date=date('Y-m-d');
                 $user->balance+=$amount;
               
            $this->applyVipRechargeBenefits($user, (int) $amount, $setting);
                
                $transfer->save();
                 $user->save();
                 
                 if($user->invited_by){
                     $invite_by=User::find($user->invited_by);
                     if($invite_by && $user->invite_recharge_reward_provide==0){
                         if($amount<1000000){
                          $invite_reward_amount = $amount *0.10;
                         }else{
                          $invite_reward_amount = 100000;
                         }
                        $invite_reward=new InviteReward;
                		$invite_reward->refer_id=$invite_by->id;
                		$invite_reward->new_user_id=$user->id;
                		$invite_reward->amount=$invite_reward_amount;
                		$invite_reward->date=date('Y-m-d');
                		$invite_reward->device_id=$user->imei_number;
                		$invite_reward->trxid=uniqid('Invite_reward_' . $user->id . '_amount_' . $amount . '_reward_' . $invite_reward_amount);;
                		$invite_reward->save();
                		$user->recharge_reward_provide=1;
                		$user->invite_recharge_reward_provide=1;
                		$user->save();
                		
                     }
                 }
                 $reward_amount = 0;
                 if(SystemSettingValueHelper::rechargeOfferRewardEnabled($setting)){
                    $rewardPercentage = SystemSettingValueHelper::rechargeOfferRewardPercentage($setting);
                    $reward_amount = (int) round($amount * ($rewardPercentage / 100));
                    if ($reward_amount > 0) {
                    $reward_transfer=new PortalTransfer;
                    $reward_transfer->portal_user_id=22286;
                    $reward_transfer->user_id=$user->id;
                    $reward_transfer->amount=$reward_amount;
                    $reward_transfer->trxid = uniqid('Recharge_Bounus_portal_recharge_id_22286_user_' . $user->id . '_amount_' . $amount . '_reward_' . $reward_amount);
                    $reward_transfer->date=date('Y-m-d');
                    $reward_transfer->save();
                    $user->balance+=$reward_amount;
                    $user->save();
                     $notification=new Notification;
                     $notification->user_id=$user->id;
                     $notification->date=date('Y-m-d');
                     $notification->message=$reward_amount.'Claim Recharge Bonus Successfully From BD Point Reseller';
                     $notification->save();
                    }
                 }
                 $date=date('Y-m-d');
                  $check_game_balance_update_date=$user->game_balance_date;
                  if ($check_game_balance_update_date==$date) {
                      $update_game=User::find($user->id);
                      $user->date_wise_balance+=$amount;
                      $user->save();
                  }
                
                
                 $notification=new Notification;
                 $notification->user_id=$user->id;
                 $notification->date=date('Y-m-d');
                 $notification->message=$amount.'Point  Recharge Successfully From BD Point Reseller';
                 $notification->save();
                 
                 array_push($response,array('message'=>'Transfer SuccessFull! ','code'=>'200'));
                 return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    array_push($response,array('message'=>'This Member Not Found','code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
              }else{
                  array_push($response,array('message'=>'Minimum Recharge Amount '.$minimumRechargeAmount,'code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
              }
                
                }else{
                      array_push($response,array('message'=>'You Do Not Have This Balance','code'=>'401'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                  }
                });
                return $result;
            }else{
                array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

        }else{
          array_push($response,array('message'=>'Unauthorized','code'=>'401'));
          return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function ProtalTransfer(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $amount = $request->amount;
        $transfer_member_id = $request->transfer_member_id;

 if (!Auth::check()) {
        return response()->json([
            'message' => 'Unauthorized. Please login first.',
            'code' => 401
        ], 401);
    }
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $target=[500000,600000,700000,800000,900000,1000000,1100000,1200000,1300000,1400000,1500000,1600000,1700000,1800000,1900000,2000000,2100000,2200000,2300000,2400000,2500000,2600000,2700000,2800000,2900000,3000000,3100000,3200000,3300000,3400000,3500000,3600000,3700000,3800000,3900000,4000000,4100000,4200000,4300000,4400000,4500000,4600000,4700000,4800000,4900000,5000000,5100000,5200000,5300000,5400000,5500000,5600000,5700000,5800000,5900000,6000000,6100000,6200000,6300000,6400000,6500000,6600000,6700000,6800000,6900000,7000000,7100000,7200000,7300000,7400000,7500000,7600000,7700000,7800000,7900000,8000000,8100000,8200000,8300000,8400000,8500000,8600000,8700000,8800000,8900000,9000000,9100000,9200000,9300000,9400000,9500000,9600000,9700000,9800000,9900000,10000000];
                if (in_array($amount, $target)) {
                // P0-B5 FIX (Agent SF 2026-06-28): portal-to-portal transfer read
                // balance from unlocked SUMs and inserted with no lock/txn
                // (SUM-check race -> portal double-spend). Wrap in a txn and lock
                // the PORTAL user row as a serialization mutex.
                $result = DB::transaction(function () use ($user_id, $amount, $transfer_member_id, $response) {
                User::where('id', $user_id)->lockForUpdate()->first();
                $recharge=PortalRecharge::where('user_id',$user_id)->where('status','Approved')->sum('amount');
                $transfer=PortalTransfer::where('portal_user_id',$user_id)->sum('amount');
                $recall=PortalRecall::where('protal_id',$user_id)->sum('amount');
                $ProtalToPTransfer=ProtalToPTransfer::where('user_id',$user_id)->sum('amount');
                $ProtalToPTransferRecived=ProtalToPTransfer::where('portal_user_id',$user_id)->sum('amount');
                $balance=($recharge+$ProtalToPTransferRecived)-($transfer+$recall+$ProtalToPTransfer);
                if($balance>=$amount){
                $user=User::where('id',$transfer_member_id)->lockForUpdate()->first();
                if($user)
                {
                    if ($user->is_coin_protal_active==1) {
                        $ProtalToPTransfer=new ProtalToPTransfer;
                        $ProtalToPTransfer->user_id=$user_id;
                        $ProtalToPTransfer->portal_user_id=$transfer_member_id;
                        $ProtalToPTransfer->amount=$amount;
                        $ProtalToPTransfer->date=date('Y-m-d');
                        $ProtalToPTransfer->trxid=uniqid('protal_to_protal_');
                        $ProtalToPTransfer->save();
                    array_push($response,array('message'=>'Protal Balance Transfer Successfully','code'=>'200'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }else{
                    array_push($response,array('message'=>'User Protal Not Actived','code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }

                }else{
                    array_push($response,array('message'=>'ID Not Found','code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE); 
                } 
            }else{
                 array_push($response,array('message'=>'Balance Not Available','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE); 
            }
                });
                return $result;
            }else{
                array_push($response, array('message' => 'Min Amount 5 Laks', 'code' => '401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE); 
            }

            }else{
                array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    private function applyVipRechargeBenefits(User $user, int $amount, $setting): void
    {
        $vipNo = SystemSettingValueHelper::determineVipLevelFromRecharge($amount, $setting);

        if ($vipNo <= 0) {
            $user->is_vip = 0;
            $user->is_invisible = 0;
            return;
        }

        $user->is_vip = $vipNo;
        $user->vip_timeline = Carbon::now()->addDays(15);
        $user->is_invisible = $vipNo === 7 ? 1 : 0;

        $vip = VipList::firstOrNew([
            'user_id' => $user->id,
            'vip_no' => $vipNo,
        ]);

        $vip->image = "store/vip/{$vipNo}.png";
        $vip->active_date = Carbon::now();
        $vip->is_active = 1;
        $vip->end_date = Carbon::now()->addDays(15);
        $vip->save();
    }
    
}
