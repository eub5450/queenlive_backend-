<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Hash;
use DB;
use App\Models\User;
use App\Models\Setting;
use App\Models\BanDevice;
use App\Models\ImieHistory;
use App\Support\SystemSettingValueHelper;
use Pusher;
use Illuminate\Support\Facades\Cache;
use RedisCacheFunction;
class AuthController extends Controller
{
    function login(Request $request){
        $token = $request->access_token;
      $device_id = $request->device_id;
      $imei_number = $request->imei_number;
      
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $request->validate([
                "email"=>'required',
                "password"=>'required'
            ]);
            $check_verify=User::where('email',$request->email)->first();
            $ban_device=BanDevice::where('device_id',$device_id)->first();
            $imie_ban_device=BanDevice::where('device_id',$imei_number)->first();
            $banned=User::where('ban_type','!=',Null)->where('email',$request->email)->first();

            if(!$imie_ban_device){
            if(!$banned){
            if($check_verify){
                if ($check_verify->status==1) {
                    
                    $credentials = request(['email','password']);

                    if(!Auth::attempt($credentials)){
                        array_push($response,array('message'=>'Unauthorized password','code'=>'401'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
                    // return response()->json(['message'=>"Unauthorized"],401);
                    }
                  
                 $user = $request->user();
                 if (!Hash::check($request->password, $user->password)) {
                    array_push($response,array('message'=>'Unauthorized password','code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
                //info('User Login: ' . $user);
                // Generate token
                
                $to = $user->createToken('apptoken')->plainTextToken;
                  info('Login Time Portal Transfer', [
                        'ip' => $request->header('CF-Connecting-IP') ?? $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'time' => now()->toDateTimeString()
                    ]);
                // Check if any other user has the same IMEI
                $check_main_id = User::where('imei_number', $imei_number)
                    ->where('id', '!=', $user->id)
                    ->value('id'); // fetch only ID for efficiency
                
                // Update current user
                $user->device_id = $device_id;
                $user->imei_number = ($user->id == 1111) ? 'd4e9aeb782727b07ef' : $imei_number;
                $user->main_id_number = ($check_main_id) ? (($user->id == 1111) ? '1111' : $check_main_id) : $user->main_id_number;
                
                

                 if ($imei_number) {
                // Check if IMEI already exists for another user
                $exists = ImieHistory::where('imie', $imei_number)
                    ->where('user_id', '!=', $user->id)
                    ->exists();
            
                if (!$exists) {
                    ImieHistory::create([
                        'imie' => $imei_number,
                        'user_id' => $user->id,
                    ]);
                }
            }

                  
                // Save updates
                $user->save();
               	// 	  $is_host=DB::table('host_data')->join('users','users.id','host_data.user_id')->where('users.is_host_id',1)->where('users.id',$user->id)->select('host_data.hosting_type')->first();
                //   $host_type=0;
                //   if($is_host){
                //   	$host_type=$is_host->hosting_type;
                //   }
                  $host_type = DB::table('host_data')
                ->where('user_id', $user->id)
                ->value('hosting_type') ?? 0;
            //Balance
                    array_push($response,array('message'=>'Login Successfully ','password'=>$user->password,'profile'=>$user->profile,'id'=>$user->id,'name'=>$user->name,
                        'balance'=>$user->balance,'email'=>$user->email,'phone'=>$user->phone,'level'=>$user->level,'is_host_id'=>$user->is_host_id,'is_agency'=>$user->is_agency,'status'=>$user->status,'role'=>$user->role,'image'=>$user->profile,'device_id'=>$user->device_id,'token'=>$to,'brd_off_power'=>$user->brd_off_power,'can_invisible'=>$user->is_invisible,'host_type'=>$host_type,'sceen_short_power'=>$user->sceen_short_power,'comment_mute_power'=>$user->comment_mute_power,'kick_power'=>$user->kick_power,'lock_brd_entry'=>$user->lock_brd_entry,'code'=>'200'));
                  
         
                 //   $data['message'] = $log_user->device_id;
                //  $pusher->trigger('login_device', $user->id, $response);
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
                  
                  
                }elseif($check_verify->status==2){
                    array_push($response,array('message'=>'Your Account suspended for violation Trams & Conditions. Thank You','code'=>'401'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Please Verify Your Account . Redirect verification Page"],400);
                }else{
                    array_push($response,array('message'=>'Please Verify Your Account .','code'=>'403'));
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Please Verify Your Account . Redirect verification Page"],400);
                }
            }else{
                array_push($response,array('message'=>'User Not Found','code'=>'404'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            } 
        }else{
            
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A.', 'code' => '404'));
              }
        
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
            
        }else{
            array_push($response,array('message'=>'Device Banned','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Unauthorized"],401);
        } }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Unauthorized"],401);
        }
    }
    public function Logout(Request $request)
    {
        $token = $request->access_token;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $request->user()->currentAccessToken()->delete();

            array_push($response,array('message'=>'Successfully Logout','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized Token Miss match','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Unauthorized Token Miss match"],401);
        }

    } 
    public function UserRegister(Request $request)
    {
       $response = array();
        $token = $request->access_token;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){

            $phone=$request->phone;
            $email=$request->email;
            
            $user_check=User::where('phone',$phone)->first();
            $user_check_email=User::where('email',$email)->first();
            if ($user_check_email) {
                array_push($response,array('message'=>'User Already Exits This Email','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                
                   $user=new User;
                   $user->name=$request->name;
                   $user->phone=$request->phone;
                   $user->email=$request->email;
                   $user->device_id=$request->device_id;
                   $user->level=1;
                   $user->is_vip=0;
                   $user->profile='https://queenlive.site/store/profile/default.png';
                   $user->balance=0;
                   $user->entry_level=0;
                   $user->role=2;
                   $user->status=1;
                   $user->password=Hash::make($request->password);
                 // $user->save();
                 array_push($response,array('message'=>'Device Banned','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
               
           }


       }
       else{
        array_push($response,array('message'=>'Unauthorized Token Missmatch','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
}
     public function ChangePassword(Request $request)
    {
        $token = $request->access_token;
        $new_password = $request->new_password;
       $user_id = $request->user_id;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $user=RedisCacheFunction::UserfindById($user_id);
            $user->password=Hash::make($new_password);
            $user->save();

            array_push($response,array('message'=>'Password Change Successfully','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized Token Miss match','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Unauthorized Token Miss match"],401);
        }

    } 
 public function GoogleLogin(Request $request)
    {
        $token = $request->access_token;
        $email = $request->email;
        $name = $request->name;
        $device_id=$request->device_id;
        $imei_number=$request->imei_number;
        $response = array();
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $imie_ban_device=BanDevice::where('device_id',$imei_number)->first();
            $ban_device=BanDevice::where('device_id',$device_id)->first();
            if($imie_ban_device ){
                array_push($response,array('message'=>'Device Banned','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
            $check_data=User::where('email',$email)->orderby('id','desc')->first();
           
            $banned=User::where('ban_type','!=',Null)->where('email',$request->email)->first();
            if ($check_data) {
              if(!$banned){
                  $check_main_id=User::where('imei_number',$imei_number)->first();
              $user=Auth::login($check_data);
              $user = $request->user();
                    //return $user;
                $to=$user->createToken('apptoken')->plainTextToken;
                $loginuser=User::find($user->id);
                $loginuser->device_id=$device_id;
               if ($check_main_id) {
            $loginuser->main_id_number = ($user->id == 1111) ? '1111' : $check_main_id->id;
            }
            $loginuser->imei_number = ($user->id == 1111) ? 'd4e9aeb782727b07ef' : $imei_number;

                $loginuser->save();
                //info('Google User Login: ' . $loginuser);
                if($imei_number){
                  $check_old_imie=ImieHistory::where('imie',$imei_number)->where('user_id','!=',$loginuser->id)->first();
                  if(!$check_old_imie){
                      $new_imie=new ImieHistory;
                      $new_imie->imie=$imei_number;
                      $new_imie->user_id=$loginuser->id;
                      $new_imie->save();
                  }
                  }
               
                  	  $is_host=DB::table('host_data')->join('users','users.id','host_data.user_id')->where('users.is_host_id',1)->where('users.id',$user->id)->select('host_data.hosting_type')->first();
                  $host_type=0;
                  if($is_host){
                  	$host_type=$is_host->hosting_type;
                  }
        array_push($response,array('message'=>'Login Successfully ','password'=>$user->password,'id'=>$user->id,'name'=>$user->name,'profile'=>$user->profile,
            'balance'=>$user->balance,'email'=>$user->email,'phone'=>$user->phone,'level'=>$user->level,'is_host_id'=>$user->is_host_id,'is_agency'=>$user->is_agency,'status'=>$user->status,'brd_off_power'=>$user->brd_off_power,'can_invisible'=>$user->is_invisible,'host_type'=>$host_type,'role'=>$user->role,'image'=>$user->profile,'token'=>$to,'sceen_short_power'=>$user->sceen_short_power,'comment_mute_power'=>$user->comment_mute_power,'kick_power'=>$user->kick_power,'code'=>'200'));

            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            // info('banned: ' . $banned->ban_type);
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D.Banned Open Time :- ' .$banned->open_time, 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A.', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
            
          }else{
          $lastId = User::latest('id')->value('id');
            $pass = 123456;
            
            // Create new user using mass assignment
            $new_user = User::create([
                
                'name' => $name,
                'device_id' => $request->device_id,
                'imei_number' => $request->imei_number,
                'phone' => $lastId + 1,
                'email' => $request->email,
                'level' => 1,
                'is_vip' => 0,
                'is_agency' => 0,
                'comment_mute_power' => 0,
                'sceen_short_power' => 0,
                'is_coin_protal_active' => 0,
                'kick_power' => 0,
                'is_host_id' => 0,
                'profile' => 'https://queenlive.site/store/profile/default.png',
                'balance' => 0,
                'entry_level' => 0,
                'role' => 2,
                'status' => 1,
                'password' => Hash::make($pass),
            ]);
            
            // Optional: log in the user
            Auth::login($new_user);
            
            // Get current user
            $user = $request->user();
            
                    //return $user;
        $to=$user->createToken('apptoken')->plainTextToken;
       if ($request->imei_number) {
            $imei = $request->imei_number;
        
            // Check if the IMEI already exists for another user
            $exists = ImieHistory::where('imie', $imei)
                ->where('user_id', '!=', $user->id)
                ->exists();
        
            if (!$exists) {
                ImieHistory::create([
                    'imie' => $imei,
                    'user_id' => $user->id,
                ]);
            }
        }

                   // Get host type directly
        $host_type = DB::table('host_data')
            ->where('user_id', $user->id)
            ->value('hosting_type') ?? 0;

       array_push($response,array('message'=>'Login Successfully ','password'=>$user->password,'id'=>$user->id,'name'=>$user->name,'profile'=>$user->profile,
            'balance'=>$user->balance,'email'=>$user->email,'phone'=>$user->phone,'level'=>$user->level,'is_host_id'=>$user->is_host_id,'is_agency'=>$user->is_agency,'status'=>$user->status,'brd_off_power'=>$user->brd_off_power,'can_invisible'=>$user->is_invisible,'host_type'=>$host_type,'role'=>$user->role,'image'=>$user->profile,'token'=>$to,'sceen_short_power'=>$user->sceen_short_power,'comment_mute_power'=>$user->comment_mute_power,'kick_power'=>$user->kick_power,'lock_brd_entry'=>$user->lock_brd_entry,'code'=>'200'));

        return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        

    }else{
        array_push($response,array('message'=>'Unauthorized Token Miss match','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"Unauthorized Token Miss match"],401);
    }

}    
   public function VarsionInfo(Request $request)
{
    if ($request->access_token !== "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
        return response()->json([
            ['message' => 'Unauthorized Token Miss match', 'code' => '401']
        ], 401, [], JSON_UNESCAPED_UNICODE);
    }


        $setting =RedisCacheFunction::getSetting();
        try {
            $exchangeCutPercentage = Cache::store('redis')->get('queenlive_exchange_cut_parcentage');
        } catch (\Throwable $exception) {
            $exchangeCutPercentage = null;
        }
        if (!is_numeric($exchangeCutPercentage)) {
            $exchangeCutPercentage = $setting->exchange_cut_parcentage ?? 30;
        }
        $exchangeCutPercentage = max(0, min(100, round((float) $exchangeCutPercentage, 2)));

        return [[
            'message' => 'Version Info Find',
            'version' => $setting->app_version,
            'flutter_version' => $setting->flutter_version,
            'online_recharge' => $setting->online_recharge,
            'sdk' => $setting->sdk,
            'pusher_app_id' => $setting->app_id,
            'pusher_key' => $setting->key,
            'agora_appId' => $setting->appId,
            'agora_appCertificate' => $setting->appCertificate,
            'pusher_cluster' => $setting->cluster,
            'web_socket' => $setting->web_socket,
            'pusher_secret' => $setting->secret,
            'coin_beg' => $setting->coin_beg,
            'apps_background' => $setting->apps_background,
            'brd_scroll' => $setting->brd_scroll,
            'reward_banner' => $setting->reward_banner,
            'vip_price_discount' => $setting->vip_discount,
            'vip_price_discount_percentage' => SystemSettingValueHelper::vipDiscountPercentage($setting),
            'portal_min_recharge_amount' => SystemSettingValueHelper::portalMinRechargeAmount($setting),
            'recharge_offer_reward' => $setting->recharge_offer_reward ?? 0,
            'recharge_offer_reward_percentage' => SystemSettingValueHelper::rechargeOfferRewardPercentage($setting),
            'exchange_cut_parcentage' => number_format($exchangeCutPercentage, 2, '.', ''),
            'code' => '200'
        ]];
    

    return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
}

}
