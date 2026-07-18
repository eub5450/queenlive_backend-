<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VipList;
use App\Models\EntryFrameProfit;
use App\Models\EntryFrame;
use App\Models\MyBeg;
use App\Models\VipId;
use App\Models\Notification;
use Auth;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Support\SystemSettingValueHelper;
use RedisCacheFunction;
class VipController extends Controller
{
    public function Index(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $data = array();

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                 $data = Cache::remember("vip_lists_{$user_id}", now()->addMinutes(30), function() use ($user_id) {
                        return VipList::where('user_id', $user_id)
                            ->orderByDesc('vip_no')
                            ->get()
                            ->map(function($vip) {
                                return [
                                    'id' => $vip->id,
                                    'user_id' => $vip->user_id,
                                    'vip_no' => $vip->vip_no,
                                    'is_active' => $vip->is_active,
                                    'active_date' => $vip->active_date,
                                    'expaire_date' => $vip->end_date,
                                    'image' => $vip->image,
                                ];
                            })->toArray();
                    });
                 
                array_push($response,array('message'=>'Data Found! ','vip_list'=>$data,'code'=>'200'));
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

    public function vipPackages(Request $request)
    {
        $token = $request->input('access_token');

        if ($token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return response()->json([
                'message' => 'Unauthorized',
                'code' => '401',
            ], 401);
        }

        $setting = Setting::find(1) ?: Setting::query()->first();
        $packages = SystemSettingValueHelper::baseVipPackages();

        try {
            if (DB::getSchemaBuilder()->hasTable('vip_packages')) {
                $dbPackages = DB::table('vip_packages')
                    ->select('vip_no', 'normal_price')
                    ->orderBy('vip_no')
                    ->get()
                    ->map(function ($package) {
                        return [
                            'vip_no' => (int) $package->vip_no,
                            'normal_price' => (int) $package->normal_price,
                        ];
                    })
                    ->values()
                    ->all();

                if (!empty($dbPackages)) {
                    $packages = $dbPackages;
                }
            }
        } catch (\Throwable $exception) {
            Log::warning('vipPackages fallback to static payload', [
                'message' => $exception->getMessage(),
            ]);
        }

        $packages = SystemSettingValueHelper::applyVipDiscountToPackages($packages, $setting);

        return response()->json([
            'message' => 'success',
            'code' => '200',
            'packages' => $packages,
        ]);
    }

    public function Active(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $vip_no = $request->vip_no;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $user=RedisCacheFunction::UserfindById($user_id);
                $check_exit_vip=VipList::where('user_id',$user->id)->where('vip_no',$vip_no)->first();
                if($check_exit_vip && $vip_no!=0){
                    $check_exit_vip->is_active=1;
                    $check_exit_vip->save();
                    if($vip_no==7){
                      $user->is_invisible=1;  
                    }
                    $user->is_vip=$vip_no;
                    $user->save();
                }else{
                     $vip_lists=VipList::where('user_id',$user->id)->get();
                     foreach($vip_lists as $vip_list){
                         $vip_list->is_active=0;
                         $vip_list->save();
                     }
                   // $user->is_vip=$vip_no;
                    $user->is_invisible=0;
                    $user->save();
                }
                 
                array_push($response,array('message'=>'Vip Active Successfull!','code'=>'200'));
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
   
    public function Notification(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $snapshot = $this->notificationSnapshot($user_id);
                array_push($response,array(
                    'message'=>'Data Found! ',
                    'data'=>$snapshot['data'],
                    'unread_count'=>$snapshot['unread_count'],
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

    public function UpdateNotificationState(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $notificationId = $request->notification_id;
        $markAll = (int) $request->mark_all === 1;
        $isRead = $request->has('is_read') ? (int) $request->is_read : 1;

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $query = Notification::where('user_id', $user_id);

                if (!$markAll) {
                    if (empty($notificationId)) {
                        array_push($response,array('message'=>'Notification ID Required','code'=>'422'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }

                    $query->where('id', $notificationId);
                }

                $notificationExists = (clone $query)->exists();
                if (!$notificationExists) {
                    array_push($response,array('message'=>'Notification Not Found','code'=>'404'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }

                $updatedCount = $query->update([
                    'is_read' => $isRead === 1 ? 1 : 0,
                    'read_at' => $isRead === 1 ? Carbon::now() : null,
                ]);

                $this->forgetNotificationCache($user_id);
                $snapshot = $this->notificationSnapshot($user_id);

                array_push($response,array(
                    'message'=>'Notification State Updated',
                    'updated_count'=>$updatedCount,
                    'unread_count'=>$snapshot['unread_count'],
                    'data'=>$snapshot['data'],
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
    public function BuyEntry(Request $request)
    {
        
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $id = $request->id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $check_old_purchase=MyBeg::where('user_id',$user_id)->where('store_id',$id)->first();
                if(!$check_old_purchase){
                    $data = EntryFrame::find($id);
                    if (!$data) {
                        array_push($response, array('message' => 'Entry not found', 'code' => '404'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    try {
                        DB::transaction(function () use ($user_id, $id, $data) {
                            $u = User::where('id', $user_id)->lockForUpdate()->first();
                            if (!$u) {
                                throw new \RuntimeException('USER_NOT_FOUND');
                            }
                            if ($u->balance < $data->price) {
                                throw new \RuntimeException('INSUFFICIENT_BALANCE');
                            }
                            $my_beg = new MyBeg;
                            $my_beg->user_id = $user_id;
                            $my_beg->store_id = $id;
                            $my_beg->name = $data->name;
                            $my_beg->image = $data->image;
                            $my_beg->active_time = Carbon::now();
                            $my_beg->expaire_time = Carbon::now()->addDays($data->time);
                            $my_beg->effect = $data->effect;
                            $my_beg->type = $data->type;
                            $my_beg->save();
                            $profite = new EntryFrameProfit;
                            $profite->user_id = $user_id;
                            $profite->store_id = $id;
                            $profite->amount = $data->price;
                            $profite->date = Carbon::now();
                            $profite->save();
                            if ($data->type == 1) {
                                $u->entry = $data->effect;
                            } else {
                                $u->frame = $data->effect;
                            }
                            $u->balance -= $data->price;
                            $u->save();
                        });
                    } catch (\RuntimeException $e) {
                        if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                            array_push($response, array('message' => 'Insufficient Balance ', 'code' => '401'));
                        } elseif ($e->getMessage() === 'USER_NOT_FOUND') {
                            array_push($response, array('message' => 'User Not Found', 'code' => '404'));
                        } else {
                            array_push($response, array('message' => $e->getMessage(), 'code' => '422'));
                        }
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    } catch (\Throwable $e) {
                        \Log::error('VipController BuyEntry transaction failed', [
                            'user_id' => $user_id,
                            'id' => $id,
                            'error' => $e->getMessage(),
                        ]);
                        array_push($response, array('message' => 'Entry purchase failed', 'code' => '500'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    array_push($response, array('message' => 'Entry Purchase Successfully ', 'code' => '200'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                    // -- moved into transaction: previously had `}else{ Insufficient Balance` branch handled by RuntimeException above --
                                    }else{
                    array_push($response,array('message'=>'This Entry Already Have In Your My VIP List','code'=>'401'));
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
    public function EntryFrame(Request $request)
    {
        
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
               

                // Entry effects (type 1)
                $entry_effects = Cache::remember("entry_effects_type_1", now()->addDays(10), function () {
                    return EntryFrame::where('type', 1)
                        ->where('is_show', 1)
                        ->orderByDesc('id')
                        ->get();
                });
                
                // Frame effects (type 0)
                $frame_effects = Cache::remember("frame_effects_type_0", now()->addDays(10), function () {
                    return EntryFrame::where('type', 0)
                        ->where('is_show', 1)
                        ->orderByDesc('id')
                        ->get();
                });
                
                // VIP IDs
                $vip_ids = Cache::remember("vip_ids_unpurchased", now()->addDays(30), function () {
                    return VipId::where('is_purchase', 0)
                        ->select('id_number', 'price')
                        ->get();
                });
                
                // My effects (user-specific)
                $my_effects = Cache::remember("my_effects_{$user_id}", now()->addMinutes(30), function () use ($user_id) {
                    return MyBeg::where('user_id', $user_id)->get();
                });
                array_push($response,array('message'=>'Stor Date','entry_effects'=>$entry_effects,'frame_effects'=>$frame_effects,'my_effects'=>$my_effects,'vip_ids'=>$vip_ids,'code'=>'200'));
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
    public function VipIdBuy(Request $request){
         $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $id_number = $request->id_number;
        $email = $request->email;
         if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $vip_id = VipId::where('id_number', $id_number)->first();
                if (!$vip_id) {
                    array_push($response, array('message' => 'VIP id not found', 'code' => '404'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                try {
                    DB::transaction(function () use ($user_id, $id_number, $email) {
                        $u = User::where('id', $user_id)->lockForUpdate()->first();
                        if (!$u) {
                            throw new \RuntimeException('USER_NOT_FOUND');
                        }
                        $vipLocked = VipId::where('id_number', $id_number)->lockForUpdate()->first();
                        if (!$vipLocked) {
                            throw new \RuntimeException('VIP_NOT_FOUND');
                        }
                        if ($vipLocked->is_purchase != 0) {
                            throw new \RuntimeException('VIP_ALREADY_TAKEN');
                        }
                        if ($u->balance <= $vipLocked->price) {
                            throw new \RuntimeException('INSUFFICIENT_BALANCE');
                        }
                        $vipLocked->is_purchase = 1;
                        $vipLocked->email = $email;
                        $vipLocked->save();
                        $u->balance -= $vipLocked->price;
                        $u->save();
                    });
                } catch (\RuntimeException $e) {
                    if ($e->getMessage() === 'INSUFFICIENT_BALANCE') {
                        array_push($response, array('message' => 'Inseficent Balance', 'code' => '401'));
                    } elseif ($e->getMessage() === 'VIP_ALREADY_TAKEN') {
                        array_push($response, array('message' => 'Lucky Id already taken', 'code' => '409'));
                    } elseif ($e->getMessage() === 'VIP_NOT_FOUND') {
                        array_push($response, array('message' => 'VIP id not found', 'code' => '404'));
                    } else {
                        array_push($response, array('message' => $e->getMessage(), 'code' => '422'));
                    }
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                } catch (\Throwable $e) {
                    \Log::error('VipController VipIdBuy transaction failed', [
                        'user_id' => $user_id,
                        'id_number' => $id_number,
                        'error' => $e->getMessage(),
                    ]);
                    array_push($response, array('message' => 'VIP id purchase failed', 'code' => '500'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                array_push($response, array('message' => 'Lucky Id Purchase Successfull . Wait For Activated', 'code' => '200'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            }else{
                array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

        }else{
           array_push($response,array('message'=>'Unauthorized','code'=>'401'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function EntryFrameActiveInactive(Request $request)
    {
        
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $id = $request->id;
        $status = $request->status;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $my_effects=MyBeg::where('user_id',$user_id)->where('id',$id)->first();
                
                if($my_effects){
                    $all_effects=MyBeg::where('user_id',$user_id)->where('type',$my_effects->type)->get();
                    foreach($all_effects as $all_effect){
                        $all_effect->status=0;
                        $all_effect->save();
                    }
                    $vip_lists=VipList::where('user_id',$user_id)->get();
                     foreach($vip_lists as $vip_list){
                         $vip_list->is_active=0;
                         $vip_list->save();
                         
                     }
                    $my_effects->status=$status;
                    $my_effects->save();
                    $user=RedisCacheFunction::UserfindById($user_id);

                    if($my_effects->type==1){
                        $effect_type='Entry';
                        if($my_effects->type==1 && $status==1){
                      $user->entry=$my_effects->effect;
                        }elseif($my_effects->type==1 && $status==0){
                            DB::table('users')
                        ->where('id', $user_id)
                        ->update(['entry' => null, 'is_invisible' => 0]);
                        }else{
                              DB::table('users')
                        ->where('id', $user_id)
                        ->update(['entry' => null, 'is_invisible' => 0]);
                        }
                    }else{
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                        if($my_effects->type==0 && $status==1){
                            $user->frame=$my_effects->effect; 
                        }elseif($my_effects->type==0 && $status==0){
                             DB::table('users')
                    ->where('id', $user_id)
                    ->update(['frame' => null, 'is_invisible' => 0]);
                        }else{
                           DB::table('users')
                    ->where('id', $user_id)
                    ->update(['frame' => null, 'is_invisible' => 0]);
                        }
                    }
                      $effect_type='Frame';
                    }
                    $user->save();
                    if($status==1){
                        $do='Active';
                    }else{
                       $do='Inactive'; 
                    }
                    
                  $message = "$effect_type Effect $do Successfully";
                    array_push($response,array('message'=>$message,'code'=>'200'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }else{
                    array_push($response,array('message'=>'Effect Not Found In Your Store','code'=>'401'));
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
     public function VIPActive(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $id = $request->id;
        $status = $request->status;
        $vip_no = $request->vip_no;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $user=RedisCacheFunction::UserfindById($user_id);
                $check_exit_vip=VipList::where('user_id',$user->id)->where('id',$id)->first();
                 if($check_exit_vip){
                     $all_effects=MyBeg::where('user_id',$user_id)->get();
                    foreach($all_effects as $all_effect){
                        $all_effect->status=0;
                        $all_effect->save();
                    }

                    $user->update([
                        'entry' => null, 
                        'frame' => null, 
                        'is_invisible' => 0
                    ]);
                     $all_vips=VipList::where('user_id',$user_id)->get();
                    foreach($all_vips as $all_vip){
                        $all_vip->is_active=0;
                        $all_vip->save();
                    }
                    $check_exit_vip->is_active=$status;
                   $check_exit_vip->save();
                    $user->is_vip=$status;
                    if($vip_no==7 && $status==1 ){
                      $user->is_vip=7;  
                      $user->is_invisible=1; 
                      if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                      $user->frame='vip7.svga';  
                      }
                      $user->entry='vip7entry.svga';  
                    }elseif($vip_no==6 && $status==1){
                        $user->is_vip=6; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                       $user->frame='vip6.svga';  
                        }
                      $user->entry='vip6entry.svga'; 
                    }elseif($vip_no==5 && $status==1){
                        $user->is_vip=5; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                        $user->frame='vip5.svga';  
                        }
                      $user->entry='vip5entry.svga';
                    }elseif($vip_no==4 && $status==1){
                        $user->is_vip=4; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                        $user->frame='vip4.svga'; 
                        }
                      $user->entry='vip4entry.svga';
                    }elseif($vip_no==3 && $status==1){
                        $user->is_vip=3; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                        $user->frame='vip3.svga'; 
                        }
                      $user->entry='vip3entry.svga';
                    }elseif($vip_no==2 && $status==1){
                        $user->is_vip=2; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                      $user->frame='vip2.svga'; 
                        }
                      $user->entry='vip2entry.svga';  
                    }elseif($vip_no==1 && $status==1){
                        $user->is_vip=1; 
                        if($user->frame!='official.svga'|| $user->frame!='admin.svga' ||$user->frame!='marchant.svga'){
                        $user->frame='vip1.svga';  
                        }
                      $user->entry='vip1entry.svga';
                    }
                    $user->save();
                    
                     array_push($response,array('message'=>'Vip Active Successfull!','code'=>'200'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                 }else{
                     array_push($response,array('message'=>'Vip Not Found In Your VIP Store','code'=>'401'));
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

    private function notificationSnapshot($userId)
    {
        $cacheKey = $this->notificationCacheKey($userId);
        $data = Cache::remember($cacheKey, now()->addMinutes(5), function() use ($userId) {
            return Notification::where('user_id', $userId)
                ->orderByDesc('id')
                ->get()
                ->map(function($notification) {
                    $type = $this->notificationType($notification);

                    return [
                        'id' => (int) $notification->id,
                        'user_id' => (string) $notification->user_id,
                        'title' => $notification->title ?: $this->notificationTitle($type),
                        'message' => (string) ($notification->message ?? ''),
                        'date' => (string) ($notification->date ?: optional($notification->created_at)->format('Y-m-d')),
                        'created_at' => optional($notification->created_at)->toDateTimeString(),
                        'updated_at' => optional($notification->updated_at)->toDateTimeString(),
                        'notification_type' => $type,
                        'accent_color' => $notification->accent_color ?: $this->notificationAccentColor($type),
                        'is_read' => (int) ($notification->is_read ?? 0),
                        'read_at' => optional($notification->read_at)->toDateTimeString(),
                        'help_id' => $notification->help_id ? (int) $notification->help_id : null,
                    ];
                })
                ->values()
                ->all();
        });

        return [
            'data' => $data,
            'unread_count' => collect($data)->where('is_read', 0)->count(),
        ];
    }

    private function notificationCacheKey($userId)
    {
        return "notifications_v2_{$userId}";
    }

    private function forgetNotificationCache($userId)
    {
        Cache::forget("notifications_{$userId}");
        Cache::forget($this->notificationCacheKey($userId));
    }

    private function notificationType($notification)
    {
        if (!empty($notification->notification_type)) {
            return (string) $notification->notification_type;
        }

        $message = strtolower((string) ($notification->message ?? ''));

        if (strpos($message, 'help') !== false || strpos($message, 'support') !== false || strpos($message, 'ticket') !== false) {
            return 'help_desk';
        }

        if (strpos($message, 'withdraw') !== false || strpos($message, 'recharge') !== false || strpos($message, 'wallet') !== false || strpos($message, 'coin') !== false) {
            return 'wallet';
        }

        if (strpos($message, 'live') !== false || strpos($message, 'room') !== false || strpos($message, 'host') !== false) {
            return 'live';
        }

        return 'system';
    }

    private function notificationTitle($type)
    {
        switch ($type) {
            case 'help_desk':
                return 'Help Desk Reply';
            case 'wallet':
                return 'Wallet Update';
            case 'live':
                return 'Live Update';
            default:
                return 'QueenLive Notification';
        }
    }

    private function notificationAccentColor($type)
    {
        switch ($type) {
            case 'help_desk':
                return '#FF5AA5';
            case 'wallet':
                return '#FF8A3D';
            case 'live':
                return '#7B61FF';
            default:
                return '#F24CA6';
        }
    }
}
