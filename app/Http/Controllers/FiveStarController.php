<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Game\Fivestar\FivestarTray;
use App\Models\Game\Fivestar\FivestarPots;
use App\Models\Game\Fivestar\FivestarSetting;
use Pusher;
use Auth;
use DB;
use Carbon\Carbon;
use App\Models\FivestarUser;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
class FiveStarController extends Controller
{

    public function Index(Request $request)
    {
         
        Cache::flush();
        $email=$request->user;
        $pass=$request->token;
        $authtoken=$email;
        $authkey= $pass;
        if (Auth::check()) {
            // code...
            Auth::logout();
        }

        $user = User::where('email', $email)->first(); 

        if(!$user) {
            return false;
        }
        if ($pass==$user->password) {
            Auth::login($user);
            session(['user' => $user]);
        }
        //dd($authtoken);
        return view('game.fiver_star.index',compact('authtoken','authkey'));
    }
    public function TimeCall(Request $request)
    {
         

        $findpots2 = FivestarTray::where('status', 0)->Orderby('id','desc')->first();
        if($findpots2){
        $findpots2->status = 0;
        $findpots2->save();
        }
        
        $email=$request->authtoken;
        $pass=$request->authkey;
        $user = User::where('email', $email)->first(); 
        if(!$user) {
            return false;
        }
        if ($pass==$user->password) {
            Auth::login($user);
        }
         $setting=FivestarSetting::find(1);
      if($setting->game_status==1){
        
        if($request->has('tray_id')){
          
            $get_tray_id = $request->get('tray_id');
            $find = FivestarTray::orderby('id','desc')->first();
            if($find){
              if($find->status==0){
                if($find->tray_id >= $get_tray_id){
                    $currentTime = Carbon::now()->timestamp;
                    $currentTimeInSeconds = $currentTime;
                    return response()->json(['data' => $find->tray_id,'currentTimeInSeconds' => $currentTimeInSeconds,'st' => true]);
                }
                else{
                        $data = time();
                         $datas = strtotime("+34 seconds", $data);
                        $gamestart = new FivestarTray;
                        $gamestart->tray_id = $datas;
                      
                        $gamestart->status = false;
                        $gamestart->save();
                        $currentTime = Carbon::now()->timestamp;
                        $currentTimeInSeconds = $currentTime;
                        return response()->json(['data' => $datas,'currentTimeInSeconds' => $currentTimeInSeconds,'st' => true]);
                    }
                }else{

                        $data = time();
                         $datas = strtotime("+34 seconds", $data);
                        $gamestart = new FivestarTray;
                        $gamestart->tray_id = $datas;
                      
                        $gamestart->status = false;
                        $gamestart->save();
                        $currentTime = Carbon::now()->timestamp;
                        $currentTimeInSeconds = $currentTime;
                        return response()->json(['data' => $datas,'currentTimeInSeconds' => $currentTimeInSeconds,'st' => true]);
                }
                
              }
              else{
                $data = time();
                $datas = strtotime("+34 seconds", $data);
                $gamestart = new FivestarTray;
                $gamestart->tray_id = $datas;
               
                $gamestart->status = false;
                $gamestart->save();
                $currentTime = Carbon::now()->timestamp;
                        $currentTimeInSeconds = $currentTime;
                return response()->json(['data' => $datas,'currentTimeInSeconds' => $currentTimeInSeconds,'st' => true]);
              }
            }
        }
          
        

    }
    public function UserData(Request $request)
    {
       
        $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);
        $user_data = Auth::user();


        return response()->json(['balance' => $user_data->balance ,'st' => true]);
        
    }
  
    public function WinOrLoss(Request $request)
    {
        $email = $request->authtoken;
        $pass = $request->authkey;
        $user = User::where('email', $email)->first();
    
        if (!$user || $pass != $user->password) {
            return false;
        }
    
        Auth::login($user);
        $user = User::find(Auth::user()->id);

        $FivestarSetting = FivestarSetting::first();
        $findpots = FivestarPots::where('user_id', Auth::user()->id)->where('status',0)->get();
        //dd($findpots);
        if($user){
            foreach ($findpots as $key => $value) {
                $find = FivestarTray::where('tray_id', $value->tray_id)->where('status',1)->first();
                if($find){
                if($value->pot_no == $find->winner ){
                    $user->increment('balance', $value->win_balance);
                    $value->status = 1;
                    $FivestarSetting->decrement('game_balance', $value->win_balance);
                    $value->save();
                    $user->save();
                    $FivestarSetting->save();
                }
                else{
                    $value->status = 10;
                    $value->save();
                }
            }
            }

        
        }
    }
  public function FortuneInsert(Request $request)
{
    $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);

    $trayId = $request->tray_id;
    $amount = $request->amount;
    $boardName = $request->bord_name;
    $loginUser = Auth::user();

    if (FivestarTray::where('tray_id', $trayId)->exists()) {
        DB::transaction(function () use ($amount, $loginUser, $trayId, $boardName) {
            $existingGameStart = FivestarPots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)
                ->where('pot_no', $boardName)
                ->lockForUpdate()
                ->first();
                if ($loginUser->balance>=$amount) {
                  
            if ($existingGameStart) {
                $existingGameStart->amount += $amount;
                if($boardName=='saven_win'){
                $existingGameStart->win_balance = $existingGameStart->amount * 5;
                }else{
                $existingGameStart->win_balance = $existingGameStart->amount * 2;
                }
                $existingGameStart->now_user_balance = $loginUser->balance - $amount;
                $existingGameStart->save();
                $loginUser->decrement('balance', $amount);
                $FivestarSetting = FivestarSetting::first();
                $FivestarSetting->increment('game_balance', $amount);
            } else {
                $gameStart = new FivestarPots();
                $gameStart->tray_id = $trayId;
                $gameStart->user_id = $loginUser->id;
                $gameStart->amount = $amount;
                if($boardName=='saven_win'){

                $gameStart->win_balance = $amount * 5;
            }else{
                $gameStart->win_balance = $amount * 2;

            }
                $gameStart->pot_no = $boardName;
                $gameStart->now_user_balance = $loginUser->balance - $amount;
                $gameStart->status = 0;
                $gameStart->save();
                $loginUser->decrement('balance', $amount);
                $FivestarSetting = FivestarSetting::first();
                $FivestarSetting->increment('game_balance', $amount);
            }
            }
        });
    }

}

    public function BettingInsert()
    {
         $exitCode = Artisan::call('cache:clear');
            $exitCode = Artisan::call('route:cache');
            $exitCode = Artisan::call('route:clear');
            $exitCode = Artisan::call('view:clear');
            $exitCode = Artisan::call('config:cache');
            $exitCode = Artisan::call('optimize');
        return view('game.new_game.index');
    } 
    public function WinManpu(Request $request)
    {
       
        $email=$request->authtoken;
        $pass=$request->authkey;
        $user = User::where('email', $email)->first(); 
        if(!$user) {
            return false;
        }
        if ($pass==$user->password) {
            Auth::login($user);
        }
        
        if($request->has('tray_id')){
            $get_tray_id = $request->get('tray_id');
            $find = FivestarTray::where('tray_id',$get_tray_id)->where('result_status','!=',1)->first();
          
            if($find){ 
                 if($find->tray_id == $get_tray_id ){
                   
                    
                    
                    $apple = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $watermelon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'watermelon')->sum('amount');
                    $lemon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'saven_win')->sum('amount');

                    $apple_need_serve = $apple*3;
                    $watermelon_need_serve= $watermelon*3;
                    $lemon_need_serve= $lemon*5;
                    $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                    rsort($values);

                    $first = $values[0];     // Highest value
                    $second = $values[1];    // Second highest value
                    $third = $values[2];     // Lowest value

                    $randomValue = null;
                    $randomPot = null;
                    $traybalance = FivestarPots::where('tray_id', $find->tray_id)->sum('amount');
                   //dd($traybalance);
                   $FivestarSetting = FivestarSetting::first();
                   $game_balance= $FivestarSetting->game_balance;

                       if ($first < $game_balance) {
                        $randomValue =$first ;
                    } elseif ($second < $game_balance) {
                        $randomValue =random_int(0, 1) ? $second : $third;
                    } else {
                        $randomValue = $third;
                    }
                
                    // Check which pot the random value belongs to
                    if ($randomValue !== null) {

                        if($first == $second && $second == $third){
                            $arr = ["saven_win","watermelon","apple"];
                            $randomPot = $arr[array_rand($arr)];
                        }else{
                            if ($randomValue === $apple_need_serve) {
                                $randomPot = 'apple';
                            } elseif ($randomValue === $lemon_need_serve) {
                                $randomPot = 'saven_win';
                            } elseif ($randomValue === $watermelon_need_serve) {
                                $randomPot = 'watermelon';
                            }
                        }
                        

                        // dd($randomPot);
                        if ($randomPot === 'apple') {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                            $find->status=2;
                        } elseif ($randomPot === 'saven_win') {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                        
                            $find->status=3;
                        
                        
                        } elseif ($randomPot === 'watermelon') {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                            $find->status=4;
                        }
                    } else {
                         $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                        $lowestValue = min($values);
                            if ($lowestValue === $apple_need_serve) {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                            $find->status=5;
                        } elseif ($lowestValue === $watermelon_need_serve) {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                            $find->status=6;
                        } elseif ($lowestValue === $lemon_need_serve) {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                            
                            $find->status=7;
                        }
                    }
                     
                    $find->game_balance=$traybalance;
                    $find->save();

                   //return response()->json(['data' => $find->winner,'st' => true]);
                }
            }
        }
    }
    public function Result_lock(Request $request)
    {
       

$email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);
        
        if($request->has('tray_id')){
            $get_tray_id = $request->get('tray_id');
            $find = FivestarTray::where('tray_id',$get_tray_id)->where('result_status',0)->first();
          
            if($find){
                 if($find->tray_id == $get_tray_id){
                      $FivestarSetting = FivestarSetting::first();
                    
                    $apple = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $watermelon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'watermelon')->sum('amount');
                    $lemon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'saven_win')->sum('amount');

                    $apple_need_serve = $apple*2;
                    $watermelon_need_serve= $watermelon*2;
                    $lemon_need_serve= $lemon*5;
                    $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                    rsort($values);

                    $first = $values[0];     // Highest value
                    $second = $values[1];    // Second highest value
                    $third = $values[2];     // Lowest value

                    $randomValue = null;
                    $randomPot = null;
                    $traybalance = FivestarPots::where('tray_id', $find->tray_id)->sum('amount');
                   //dd($traybalance);
                   $game_balance= $FivestarSetting->game_balance;

                      if ($first < $game_balance) {
                        $randomValue =$first ;
                    } elseif ($second < $game_balance) {
                        $randomValue =random_int(0, 1) ? $second : $third;
                       
                    } else {
                        $randomValue = $third;
                    }
                
                    // Check which pot the random value belongs to
                    if ($randomValue !== null) {

                       if($first == $second && $second == $third){
                            $arr = ["saven_win","watermelon","apple"];
                            $randomPot = $arr[array_rand($arr)];
                        }else{
                            if ($randomValue === $apple_need_serve) {
                                $randomPot = 'apple';
                            } elseif ($randomValue === $lemon_need_serve) {
                                $randomPot = 'saven_win';
                            } elseif ($randomValue === $watermelon_need_serve) {
                                $randomPot = 'watermelon';
                            }
                        }
                        

                        // dd($randomPot);
                        if ($randomPot === 'apple') {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                        } elseif ($randomPot === 'saven_win') {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                        
                          
                        
                        
                        } elseif ($randomPot === 'watermelon') {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                          
                        }
                    } else {
                         $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                        $lowestValue = min($values);
                            if ($lowestValue === $apple_need_serve) {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                           
                        } elseif ($lowestValue === $watermelon_need_serve) {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                           
                        } elseif ($lowestValue === $lemon_need_serve) {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                            
                           
                        }
                    }
                   
                    //$find->save();
                    //$FivestarSetting = FivestarSetting::first();
                    $find->result_status=1;
                    $find->save();
                    
                   
                 }
             }
         }

    }
    public function GameWinner(Request $request)
    {
        
        $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);
        if($request->has('tray_id')){
            $get_tray_id = $request->get('tray_id');
            $find = FivestarTray::where('tray_id',$get_tray_id)->where('status','!=',1)->where('result_status',1)->first();
            $findallusers = FivestarTray::where('tray_id',$get_tray_id)->where('status',1)->where('result_status',1)->first();
          
            if($find){
                    if($find->tray_id == $get_tray_id){
                   
                    
                    $apple = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $watermelon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'watermelon')->sum('amount');
                    $lemon = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', 'saven_win')->sum('amount');

                    $apple_need_serve = $apple*2;
                    $watermelon_need_serve= $watermelon*2;
                    $lemon_need_serve= $lemon*5;
                    $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                    rsort($values);

                    $first = $values[0];     // Highest value
                    $second = $values[1];    // Second highest value
                    $third = $values[2];     // Lowest value

                    $randomValue = null;
                    $randomPot = null;
                    $traybalance = FivestarPots::where('tray_id', $find->tray_id)->sum('amount');
                   //dd($traybalance);
                    $FivestarSetting = FivestarSetting::first();
                   $game_balance= $FivestarSetting->game_balance;

                      if ($first < $game_balance) {
                         $randomValue =$first ;
                    } elseif ($second < $game_balance) {
                        $randomValue =random_int(0, 1) ? $second : $third;
                       
                    } else {
                        $randomValue = $third;
                    }
                    

                    if ($randomValue !== null) {

                       if($first == $second && $second == $third){
                            $arr = ["saven_win","watermelon","apple"];
                            $randomPot = $arr[array_rand($arr)];
                        }else{
                            if ($randomValue === $apple_need_serve) {
                                $randomPot = 'apple';
                            } elseif ($randomValue === $lemon_need_serve) {
                                $randomPot = 'saven_win';
                            } elseif ($randomValue === $watermelon_need_serve) {
                                $randomPot = 'watermelon';
                            }
                        }
                        // dd($randomPot);
                        if ($randomPot === 'apple') {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                            $find->status=1;
                        } elseif ($randomPot === 'saven_win') {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                        
                            $find->status=1;
                        
                        
                        } elseif ($randomPot === 'watermelon') {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                            $find->status=1;
                        }
                    } else {
                         $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                        $lowestValue = min($values);
                            if ($lowestValue === $apple_need_serve) {
                            $find->winner='apple';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$apple_need_serve;
                        
                            $find->status=1;
                        } elseif ($lowestValue === $watermelon_need_serve) {
                            $find->winner='watermelon';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$watermelon_need_serve;
                        
                            $find->status=1;
                        } elseif ($lowestValue === $lemon_need_serve) {
                            $find->winner='saven_win';
                            $find->apple_serve=$apple;
                            $find->lemon_serve=$lemon;
                            $find->watermalon_serve=$watermelon;
                            $find->win_balance=$lemon_need_serve;
                            
                            $find->status=1;
                        }
                    }

                
                    //$find->save();
                    $FivestarSetting = FivestarSetting::first();
                    $find->result_status=1;
                    $find->game_balance=$traybalance;
                    $find->real_game_balance=$FivestarSetting->game_balance;
                    $find->after_win_balance=$FivestarSetting->game_balance-$find->win_balance;
                    $find->save();
                  
                }

                    return response()->json(['data' => $find->winner,'st' => true]);
                }else{
                    return response()->json(['data' => $findallusers->winner,'st' => true]);
                }
            }
        
    }
    public function PushUserData(Request $request)
    {

        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher\Pusher(
            'd7e4ee369f1bf1d4f6ff',
            'fec29c10c9f6b408a675',
            '1618587',
            $options
        );

        $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => $request->bord_name, 'bord_amount' => $request->bord_amount]); 
 
    }
  
    public function LastGameWinner(Request $request)
    {
         
       $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);
        $response = array();
        $apple=0;
        $lamon=0;
        $watermellon=0;
         $data = FivestarTray::where('status','!=',0)->limit(14)->orderby('id','desc')->get();
         foreach ($data as $key => $value) {
             $row['winner']=$value->winner;
             array_push($response,$row);
             if($value->winner=='apple'){
               $apple+=1; 
             }if($value->winner=='saven_win'){
               $lamon+=1; 
             }if($value->winner=='watermelon'){
               $watermellon+=1; 
             }
         }
         $apple_parcentage=$apple*100/20;
         $lamon_parcentage=$lamon*100/20;
         $watermellon_parcentage=$watermellon*100/20;
        return response()->json(['data' => $response,'apple_parcentage' => $apple_parcentage,'watermellon_parcentage' => $watermellon_parcentage,'lamon_parcentage' => $lamon_parcentage]);
    }
    public function GameWinnerInfo(Request $request)
    {
         
        //Auth::logout();
           $email=$request->authtoken;
            $pass=$request->authkey;
            $user = User::where('email', $email)->first(); 
            if(!$user) {
                return false;
            }
            if ($pass==$user->password) {
                Auth::login($user);
            }
        $user = Auth::user();
        

        $users_1st_amount = 0;
        $users_1st_name = 0; 
        $users_1st_img = 0; 
        $users_1st_amount_bet = 0;

        $users_2nd_amount = 0;
        $users_2nd_name = 0; 
        $users_2nd_img = 0; 
        $users_2nd_amount_bet = 0;

        $users_3rd_amount = 0;
        $users_3rd_name = 0; 
        $users_3rd_img = 0; 
        $users_3rd_amount_bet = 0;

        $my_tota_bet = 0; 
        $my_tota_bet_winning = 0;

        $latestFivestarTray = FivestarTray::latest()->first();
        $find = FivestarTray::where('id', '<', $latestFivestarTray->id)->latest()->first();
        $getdetails = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', $find->winner)->orderBy('amount', 'DESC')->limit(3)->get();
         foreach ($getdetails as $key => $value) {

           // return $value;
            if($key == 0){
                $total = FivestarPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                
                $finduser = User::find($value->user_id);
                $users_1st_amount = $total;
                $users_1st_name = Str::limit($finduser->name, 5 , ""); 
                $users_1st_img = $finduser->profile; 
                if($value->pot_no == 'saven_win'){
                    $users_1st_amount_bet = $value->amount*5;
                }else{
                    $users_1st_amount_bet = $value->amount*2;
                }
            }
            if($key == 1){
                $total = FivestarPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                $finduser = User::find($value->user_id);
                $users_2nd_amount = $total;
                $users_2nd_name = Str::limit($finduser->name, 5 , ""); 
                $users_2nd_img = $finduser->profile; 
                if($value->pot_no == 'saven_win'){
                    $users_2nd_amount_bet = $value->amount*5;
                }else{
                    $users_2nd_amount_bet = $value->amount*2;
                }
                
            }
            if($key == 2){
                $total = FivestarPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                $finduser = User::find($value->user_id);
                $users_3rd_amount = $total;
                $users_3rd_name = Str::limit($finduser->name, 5 , ""); 
                $users_3rd_img = $finduser->profile; 
                if($value->pot_no == 'saven_win'){
                    $users_3rd_amount_bet = $value->amount*5;
                }else{
                    $users_3rd_amount_bet = $value->amount*2;
                }
            }
        }

        $my_tota_bet = FivestarPots::where('tray_id',$find->tray_id)->where('user_id',$user->id)->sum('amount');
        $bet_winning = FivestarPots::where('tray_id', $find->tray_id)->where('pot_no', $find->winner)->where('user_id', $user->id)->sum('amount');
        $my_tota_bet_winning = $bet_winning*2;

        return response()->json([
            'users_1st_amount' => $users_1st_amount,
            'users_1st_name' => $users_1st_name,
            'users_1st_img' => $users_1st_img,
            'users_1st_amount_bet' => $users_1st_amount_bet,
            'users_2nd_amount' => $users_2nd_amount,
            'users_2nd_name' => $users_2nd_name,
            'users_2nd_img' => $users_2nd_img,
            'users_2nd_amount_bet' => $users_2nd_amount_bet,
            'users_3rd_amount' => $users_3rd_amount,
            'users_3rd_name' => $users_3rd_name,
            'users_3rd_amount_bet' => $users_3rd_amount_bet,
            'users_3rd_img' => $users_3rd_img,
            'my_tota_bet' => $my_tota_bet,
            'my_tota_bet_winning' => $my_tota_bet_winning,
        ]);

    }
     public function UserAvtivity(Request $request)
    {
        $email=$request->authtoken;
        $pass=$request->authkey;
        $user = User::where('email', $email)->first(); 
        if(!$user) {
            return false;
        }
        if ($pass==$user->password) {
            Auth::login($user);
        }
        $old_data = FivestarUser::where('user_id', Auth::user()->id)
        ->where('tray_id', $request->tray_id)
        ->first();

        if (!$old_data) {
            
            $data = new FivestarUser;
            $data->user_id = Auth::user()->id;
            $data->tray_id = $request->tray_id;
            $data->save();
            FivestarUser::where('user_id', Auth::user()->id)
                ->where('tray_id','!=', $request->tray_id)
                ->delete();
        } else {
            

            $all_users = FivestarUser::where('tray_id','!=', $request->tray_id)->get();
            foreach ($all_users as $key => $all_user) {
                $all_user->delete();
            }
        }
        $top_four = DB::table('fivestar_users')
            ->join('users', 'users.id', 'fivestar_users.user_id')
            ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile')
            ->orderBy('users.balance', 'desc')
            ->get();


       

        return response()->json(['data' => $top_four]);

    }
    public function AllActiveUser()
    {
        $data=DB::table('fivestar_users')->join('users','users.id','fivestar_users.user_id')->select('users.name','users.profile')->orderby('users.balance','desc')->get();
       return response()->json(['data' => $data]);
    }
}
