<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\OnlineRecharge;
use App\Models\PortalTransfer;
use App\Models\VipList;
use App\Models\Setting;
use App\Models\Notification;
use App\Models\OnlineCoinProduct;
use DB;
use Carbon;
class OnlinePaymentController extends Controller
{
    
    public function OnlinePayment(Request $request)
    {
    	$token = $request->access_token;
         $user_id=$request->user_id;
       $reward=$request->coin;
         $amount_bdt=$request->amount_bdt;
         $txid=$request->txid;
         $product_id=$request->product_id;
         $status=$request->status;
         $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $user=User::find($user_id);
            
            if ($user_id==Auth::id()) {
            if ($user) {
                $product=OnlineCoinProduct::where('product_id',$product_id)->first();
                $amount=$product->coin;
				    $transfer = new PortalTransfer;
				    $transfer->portal_user_id =1 ;
				    $transfer->user_id = $user_id;
				    $transfer->amount = $amount;
				    $transfer->trxid = uniqid('google'.$reward.'_pay_online_recharge_');
				    $transfer->date = date('Y-m-d');
				 // $transfer->save();
				    	$online=new OnlineRecharge;
				    	$online->user_id=$user_id;
				    	$online->amount_bdt=$amount_bdt;
				    	$online->txid=$txid;
				    	$online->product_id=$product_id;
				    	$online->status=$status;
				    //	$online->save();
				    
				      $notification=new Notification;
                         $notification->user_id=$user->id;
                         $notification->date=date('Y-m-d');
                         $notification->message=$amount . ' Points Got From Online Recharge';
                         $notification->save();
				    //$user->balance += $amount;
				    $setting = Setting::find(1);

				    $vipMapping = [
				        ['min' => 500000, 'max' => 999999, 'vip' => 1, 'duration' => 15, 'image' => 'store/vip/1.png'],
				        ['min' => 1000000, 'max' => 1499999, 'vip' => 2, 'duration' => 15, 'image' => 'store/vip/2.png'],
				        ['min' => 1500000, 'max' => 1999999, 'vip' => 3, 'duration' => 15, 'image' => 'store/vip/3.png'],
				        ['min' => 2000000, 'max' => 2999999, 'vip' => 4, 'duration' => 30, 'image' => 'store/vip/4.png'],
				        ['min' => 3000000, 'max' => 3999999, 'vip' => 5, 'duration' => 15, 'image' => 'store/vip/5.png'],
				        ['min' => 4000000, 'max' => 4999999, 'vip' => 6, 'duration' => 15, 'image' => 'store/vip/6.png'],
				        ['min' => 5000000, 'max' => PHP_INT_MAX, 'vip' => 7, 'duration' => 15, 'image' => 'store/vip/7.png', 'invisible' => 1]
				    ];

				    foreach ($vipMapping as $vipLevel) {
				        if ($amount >= $vipLevel['min'] && $amount <= $vipLevel['max']) {
				            $user->is_vip = $vipLevel['vip'];
				            $user->vip_timeline = Carbon::now()->addDays($vipLevel['duration']);
				            if (isset($vipLevel['invisible'])) {
				                $user->is_invisible = $vipLevel['invisible'];
				            }

				            $check_exit_vip = VipList::where('user_id', $user->id)->where('vip_no', $vipLevel['vip'])->first();
				            if ($check_exit_vip) {
				                $check_exit_vip->active_date = Carbon::now();
				                $check_exit_vip->is_active = 1;
				                $check_exit_vip->end_date = Carbon::now()->addDays($vipLevel['duration']);
				               // $check_exit_vip->save();
				            } else {
				                $vip_store = new VipList;
				                $vip_store->user_id = $user->id;
				                $vip_store->vip_no = $vipLevel['vip'];
				                $vip_store->image = $vipLevel['image'];
				                $vip_store->active_date = Carbon::now();
				                $vip_store->is_active = 1;
				                $vip_store->end_date = Carbon::now()->addDays($vipLevel['duration']);
				              //  $vip_store->save();
				            }
				            break;
				        }
				    }

				    if ($amount < 500000) {
				        $user->is_vip = 0;
				    }

				   // $user->save();
				    array_push($response,array('message'=>'Coin Purchase Successfully!!','code'=>'200'));
            		return json_encode($response,JSON_UNESCAPED_UNICODE);
				}else{
					 array_push($response,array('message'=>'User Not Found','code'=>'401'));
            		return json_encode($response,JSON_UNESCAPED_UNICODE);
				}

            }else{
            array_push($response,array('message'=>'User And Login ID Not Same','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        
        }

    }
}
