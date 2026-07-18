<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Battle\TeenPattiTray;
use App\Models\Battle\TeenPattiPots;
use App\Models\Battle\TeenPattiSetting;
use App\Models\Battle\TeenPattiUser;
use App\Models\FortuneLock;
use App\Models\GameBannner;
use App\Models\Gift;
use Pusher;
use Auth;
use Carbon\Carbon;
use App\Models\FruitsGamePattan;
use App\Models\BanDevice;
use DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use App\Models\GameBalanceWithdraw;
class TeenPattiController extends Controller
{
    public function Index(Request $request)
    {
       //   $exitCode = Artisan::call('cache:clear');


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
       
       $is_another_id_lock=FortuneLock::where('imei_number',$user->imei_number)->where('type',0)->first();
        if($is_another_id_lock){
            $check_id_have_already=FortuneLock::where('user_id',$user->id)->where('type',0)->first();
            if(!$check_id_have_already){
             $data=new FortuneLock;
            $data->user_id=$user->id;
            $data->parcentage=$is_another_id_lock->parcentage;
            $data->type=0;
            $data->imei_number=$user->imei_number;
            $data->save();
            }
            
        }
        
         $is_another_id_lock_win=FortuneLock::where('imei_number',$user->imei_number)->where('auto_lock_active',null)->where('type',1)->first();
        if($is_another_id_lock_win){
            $check_id_have_win_already=FortuneLock::where('user_id',$user->id)->where('type',1)->first();
            if(!$check_id_have_win_already){
             $data_win=new FortuneLock;
            $data_win->user_id=$user->id;
            $data_win->parcentage=$is_another_id_lock_win->parcentage;
            $data_win->type=1;
            $data_win->imei_number=$user->imei_number;
            $data_win->save();
            }
            
        }
        
         $date=date('Y-m-d');
        if($date!=$user->game_balance_date && $user->id!=555555){
            $today_gift=Gift::where('sander_id',$user->id)->whereDate('date',$date)->sum('value');
            $today_amount=$user->balance-$today_gift;
            $user->game_balance_date=$date;
            $user->date_wise_balance=$today_amount;
            $user->save();
        }
        //dd($authtoken);
        return view('game.teenpatti.index',compact('authtoken','authkey'));
    }
   public function TimeCall(Request $request)
{
    $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass !== $user->password) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    Auth::login($user);

    $setting = TeenPattiSetting::find(1);
    if ($setting->game_status == 1 && $request->has('tray_id')) {
        $get_tray_id = $request->get('tray_id');

        try {
            DB::beginTransaction();

            $find = TeenPattiTray::where('status', 0)->latest('id')->first();
            $datas = strtotime("+34 seconds");
                $currentTimeInSeconds = Carbon::now()->timestamp;
            if ($find && $find->tray_id >= $get_tray_id) {
                
                DB::commit();
                return response()->json([
                    'data' => $find->tray_id,
                    'currentTimeInSeconds' => $currentTimeInSeconds,
                    'st' => true
                ]);
            } else {
                

                $pots_setting = TeenPattiSetting::find(1);
                $pattarn = FruitsGamePattan::find($pots_setting->running_pattarn + 1) ?? FruitsGamePattan::first();
                $selected_pots = $pattarn->pots;

                $pots_setting->pots_name = $selected_pots;
                $pots_setting->save();
                 $recheck = TeenPattiTray::where('tray_id', $datas)->first();
                if (!$recheck) {
                    $gamestart = TeenPattiTray::create([
                        'tray_id' => $datas,
                        'winner' => $selected_pots,
                        'status' => false
                    ]);
                }
                 
                DB::commit();
                
                return response()->json([
                    'data' => $datas,
                    'currentTimeInSeconds' => $currentTimeInSeconds,
                    'st' => true
                ]);
            }
        } catch (QueryException $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e;
        }
    }

    return response()->json(['error' => 'Invalid request'], 400);

    
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
        $fortunesetting = TeenPattiSetting::first();
        $findpots = TeenPattiPots::where('user_id', Auth::user()->id)->where('status',0)->get();
     
        if($user){
            foreach ($findpots as $key => $value) {
                $find = TeenPattiTray::where('tray_id', $value->tray_id)->where('status',1)->first();
                if($find){
                if($value->pot_no == $find->winner ){
                   $win_balance=$value->amount*3;
                     $user->increment('balance', $win_balance);
                     $user->save();
                     $value->serve_balance = $win_balance;
                     $value->status = 1;
                     $value->save();
                     $fortunesetting->decrement('game_balance', $win_balance);
                     $fortunesetting->save();
                }
                else{
                    $value->status = 10;
                    $value->save();
                }
            }
           
            }

        
        }
        $user_data = User::find(Auth::user()->id);
        return response()->json(['balance' => $user_data->balance ,'st' => true]);
    
    }
  public function BidInsart(Request $request)
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

    if (TeenPattiTray::where('tray_id', $trayId)->exists()) {
        DB::transaction(function () use ($amount, $loginUser, $trayId, $boardName) {
            $existingGameStart = TeenPattiPots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)
                ->where('pot_no', $boardName)
                ->lockForUpdate()
                ->first();
                if ($loginUser->balance>=$amount) {
                  
                 
             $options = array(
                  'cluster' => 'ap1',
                  'useTLS' => true
              );
              $pusher = new Pusher\Pusher(
                  'e0a794422509d8ef1454',
                  'e01ce0f49fa9ec96c01c',
                  '1892202',
                  $options
              );
              $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => $boardName, 'bord_amount' => $amount]); 
           
         $check_lock_list=FortuneLock::where('user_id',$loginUser->id)->where('type',0)->first();
                  if($check_lock_list){
                      $check_lock=TeenPattiSetting::find(1);
                      if($check_lock->block_id==0 ){
                          $check_lock->block_id=$loginUser->id;
                          $check_lock->lock_parcent=$check_lock_list->parcentage;
                          $check_lock->save();
                      }
                  }
                  
                  $check_winning_list=FortuneLock::where('user_id',$loginUser->id)->where('type',1)->first();
                  if($check_winning_list){
                      $check_lock_winner=TeenPattiSetting::find(1);
                      if($check_lock_winner->winner_id==0 ){
                          $check_lock_winner->winner_id=$loginUser->id;
                          $check_lock_winner->lock_parcent=$check_winning_list->parcentage;
                          $check_lock_winner->save();
                      }
                  }
            if ($existingGameStart) {
                $existingGameStart->amount += $amount;
                $existingGameStart->win_balance =intval(round($existingGameStart->amount  * 3));
                $existingGameStart->now_user_balance = $loginUser->balance - $amount;
                $existingGameStart->save();
                $loginUser->decrement('balance', $amount);
                $fortunesetting = TeenPattiSetting::first();
                $fortunesetting->increment('game_balance', $amount);
           
            } else {
                $gameStart = new TeenPattiPots();
                $gameStart->tray_id = $trayId;
                $gameStart->user_id = $loginUser->id;
                $gameStart->amount = $amount;
                $gameStart->win_balance =intval(round($amount * 3));
                $gameStart->pot_no = $boardName;
                $gameStart->now_user_balance = $loginUser->balance - $amount;
                $gameStart->status = 0;
                $gameStart->save();

                $loginUser->decrement('balance', $amount);
                $fortunesetting = TeenPattiSetting::first();
                $fortunesetting->increment('game_balance', $amount);
               
            }
            
        }
        });
    }
    
    }

    public function BettingInsert()
    {
        return view('game.teenpatti.index');
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
            $find = TeenPattiTray::where('tray_id',$get_tray_id)->where('result_status',0)->first();
          
            if($find){ 
                 if($find->tray_id == $get_tray_id ){
                    if ($find->result_status==0) {
                        
                        $tray_update=TeenPattiTray::where('tray_id',$find->tray_id)->first();
                        $apple = TeenPattiPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                        $watermelon = TeenPattiPots::where('tray_id', $find->tray_id)->where('pot_no', 'watermelon')->sum('amount');
                        $lemon = TeenPattiPots::where('tray_id', $find->tray_id)->where('pot_no', 'saven_win')->sum('amount');
                        $tray_update->no_one_pots=$apple;
                        $tray_update->no_two_pots=$watermelon;
                        $tray_update->no_three_pots=$lemon;
                        $tray_update->save();
                        $traybalance = TeenPattiPots::where('tray_id', $find->tray_id)->sum('amount');
                       $fortunesetting = TeenPattiSetting::first();
                        $currentMinute = date('i'); // Get the current minute
                          if (($currentMinute >= 1 && $currentMinute <= 5) || ($currentMinute >= 11 && $currentMinute <= 15) || ($currentMinute >= 21 && $currentMinute <= 25)|| ($currentMinute >= 30 && $currentMinute <= 35) || ($currentMinute >= 40 && $currentMinute <= 45)|| ($currentMinute >= 50 && $currentMinute <= 55)) 
                            {
                           
                              if ($traybalance>$fortunesetting->tray_margin) {
                                  if($traybalance>200000){
                           $percentage = 5/100*$traybalance; // 5%
                                  }else{
                                       $percentage =5/100*$traybalance; // 5%
                                  }
                           $fortunesetting->game_balance-=$percentage;
                           $fortunesetting->second_balance+=$percentage;
                           $fortunesetting->save();
                         }
                        }else{
                            $serve_parcent=$fortunesetting->second_balance/100*3;
                             if($serve_parcent<=$fortunesetting->second_balance){
                               $fortunesetting->game_balance+=$serve_parcent;
                               $fortunesetting->second_balance-=$serve_parcent;
                               $fortunesetting->save();
                             }
                         }
                         $withdraw_percentage = 1.9/100*$traybalance; 
                           $profit_withdraw=GameBalanceWithdraw::find(1);
                            $profit_withdraw->amount+=$withdraw_percentage;

                            if($profit_withdraw->save()){
                                $fortunesetting->game_balance-=$withdraw_percentage;
                                $fortunesetting->save();
                            }
                     // self::WinnerDecission($find->tray_id);
                    }
                   $find->result_status=1;
                    $find->save();
                    
                   
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

        $loginUser=Auth::login($user);
            
            if($request->has('tray_id')){
                $get_tray_id = $request->get('tray_id');
                $tray = TeenPattiTray::where('tray_id',$get_tray_id)->where('status',0)->first();
              
                if($tray){
                     if($tray->tray_id == $get_tray_id){
                          self::WinnerDecission($tray->tray_id);
                         $tray->status = 1;
                         
                         $fortunesetting = TeenPattiSetting::first(); 
                        $patarn_check_is_update = TeenPattiTray::where('tray_id', $get_tray_id)
                        ->where('status', '!=',1)
                        ->where('result_status',1)
                        ->exists();
                        if ($patarn_check_is_update) {
                        $fortunesetting->running_pattarn += 1;
                        if (!FruitsGamePattan::find($fortunesetting->running_pattarn)) {
                            $fortunesetting->running_pattarn = 0;
                        }
                        $fortunesetting->save();
                        }
                        $tray->save();
                     }
                }
            }

    }
    public function GameWinner(Request $request)
    {
        // $exitCode = Artisan::call('cache:clear');
        $email = $request->authtoken;
        $pass = $request->authkey;
        $user = User::where('email', $email)->first();

        if (!$user || $pass != $user->password) {
            return false;
        }

        Auth::login($user);
            if($request->has('tray_id')){
                $get_tray_id = $request->get('tray_id');
              
                $findallusers = TeenPattiTray::where('tray_id',$get_tray_id)->where('status',1)->where('result_status',1)->first();
                $arraydata = array(json_decode($findallusers->cards, true));
                    return response()->json(['data' => $arraydata, 'st' => true]);
            }
    }
    public function WinnerDecission($tray_id)
    {
        $result = TeenPattiTray::where('tray_id',$tray_id)->where('status',0)->first();
        if ($result) {
               $fortunesetting = TeenPattiSetting::first();             
                    $select_pots= TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no',$fortunesetting->pots_name)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                        $apple = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'apple')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                    $watermelon = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'watermelon')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                    $lemon = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'saven_win')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');

                    $apple_need_serve = $apple*3;
                    $watermelon_need_serve= $watermelon*3;
                    $lemon_need_serve= $lemon*3;
                    $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                    rsort($values);

                    $first = $values[0];     // Highest value
                    $second = $values[1];    // Second highest value
                    $third = $values[2];     // Lowest value

                    $randomValue = null;
                    $randomPot = null;
                    $traybalance = TeenPattiPots::where('tray_id', $result->tray_id)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                      $select_pots_need_serve = $select_pots*4;
                 $currentMinute = date('i'); // Get the current minute
                 $game_balance= $fortunesetting->game_balance;
                
                if ($select_pots_need_serve < $game_balance) {
                   
                    $result->randomPercentage = 'Pattern';
                    
                }else if ($apple === $watermelon && $watermelon === $lemon ) {
                     $fruits = ['saven_win', 'apple', 'watermelon'];
                    $randomFruit = $fruits[array_rand($fruits)];
                    $result->winner=$randomFruit;
                     $result->randomPercentage = 'equal rendom';
                }
                else if($game_balance<0 && $traybalance>0){
                     $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                            $lowestValue = min($values);
                                if ($lowestValue === $apple_need_serve) {
                                $result->winner='apple';
                            } elseif ($lowestValue === $watermelon_need_serve) {
                                $result->winner='watermelon';                           
                            } elseif ($lowestValue === $lemon_need_serve) {
                                $result->winner='saven_win';
                            }
                            $result->randomPercentage = 'Minmum Bcz Balance Minus' .$game_balance;
                }else{
                    $seconrandomPercentage = mt_rand(20, 30) / 100; 
                    $secendtenPercent = $game_balance * $seconrandomPercentage;
                    $secondnow_game_balance_after_percent=$game_balance-$secendtenPercent;
                         if ($first < $secondnow_game_balance_after_percent) {
                        $randomValue = $first;
                        } elseif ($second < $secondnow_game_balance_after_percent) {
                            $randomValue =$second ;
                        } else {
                            $randomValue = $third;
                        }
                         $pattarn='pattern Change % :'.$seconrandomPercentage;
                         if ($randomValue !== null) {
                                if ($randomValue === $apple_need_serve) {
                                    $randomPot = 'apple';
                                } elseif ($randomValue === $lemon_need_serve) {
                                    $randomPot = 'saven_win';
                                } elseif ($randomValue === $watermelon_need_serve) {
                                    $randomPot = 'watermelon';
                                }
                            if ($randomPot === 'apple') {
                                $result->winner='apple';
                            } elseif ($randomPot === 'saven_win') {
                                $result->winner='saven_win';
                            } elseif ($randomPot === 'watermelon') {
                                $result->winner='watermelon';
                            }
                        } else {
                             $values = [$apple_need_serve, $watermelon_need_serve, $lemon_need_serve];
                            $lowestValue = min($values);
                                if ($lowestValue === $apple_need_serve) {
                                $result->winner='apple';
                            } elseif ($lowestValue === $watermelon_need_serve) {
                                $result->winner='watermelon';                           
                            } elseif ($lowestValue === $lemon_need_serve) {
                                $result->winner='saven_win';
                            }
                        }
                        $result->randomPercentage = $pattarn;
                        
                    }//winning condition end
                    $fortunesetting = TeenPattiSetting::first();
                     if($fortunesetting->block_id!=0 )
                    {
                        $lastThreeRecords = TeenPattiTray::where('result_status', 1)
                            ->where('status', 1)
                            ->orderByDesc('id')
                            ->limit(rand(2,4))
                            ->pluck('block_id')
                            ->toArray();
                        $it_repet = (count(array_unique($lastThreeRecords)) === 1 && reset($lastThreeRecords) === $fortunesetting->block_id) ? 0 : 1;
                        if ($it_repet== 1) {
                            
                              $block_apple = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'apple')->where('user_id',$fortunesetting->block_id)->sum('amount');
                                $block_watermelon = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'watermelon')->where('user_id',$fortunesetting->block_id)->sum('amount');
                                $block_lemon = TeenPattiPots::where('tray_id', $result->tray_id)->where('pot_no', 'saven_win')->where('user_id',$fortunesetting->block_id)->sum('amount');
                                $block_tray_balance=$block_apple+$block_watermelon+$block_lemon;

                                $first_block_two_pots=$block_apple+$block_watermelon;
                                $sec_block_two_pots=$block_apple+$block_lemon;
                                $third_block_two_pots=$block_lemon+$block_watermelon;
                               
                                $check_block_parcentage=$traybalance/100*$fortunesetting->lock_parcent;
                                
                                if ($block_tray_balance>$check_block_parcentage) {
                                    if($block_apple==$block_tray_balance){
                                        $arr = ["saven_win", "watermelon"];
                                        $randomPot = $arr[array_rand($arr)];
                                       
                                        $result->winner=$randomPot;
                                        $result->block_id=$fortunesetting->block_id;
                                         $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;

                                    }elseif ($block_watermelon==$block_tray_balance) {
                                        $arr = ["saven_win", "apple"];
                                        $randomPot = $arr[array_rand($arr)];
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($block_lemon==$block_tray_balance) {
                                        $arr = ["watermelon", "apple"];
                                        $randomPot = $arr[array_rand($arr)];
                                        $result->winner=$randomPot;
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($first_block_two_pots==$block_tray_balance) {
                                       
                                        $result->winner='saven_win';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($sec_block_two_pots==$block_tray_balance) {
                                       
                                        $result->winner='watermelon';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }else {
                                       
                                        $result->winner='apple';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }
                                }
                            }
                        }
                    
                           
                     if ($fortunesetting->winner_id != 0 && $fortunesetting->game_balance > 0) {
                            $lastThreeRecords = TeenPattiTray::where('result_status', 1)
                                ->where('status', 1)
                                ->orderByDesc('id')
                                ->limit(rand(2,4))
                                ->pluck('winner_id')
                                ->toArray();

                            $it_winner_repet = (count(array_unique($lastThreeRecords)) === 1 && reset($lastThreeRecords) === $fortunesetting->winner_id) ? 0 : 1;

                            if ($it_winner_repet == 1) {
                                $winner_pots = TeenPattiPots::where('tray_id', $result->tray_id)
                                    ->whereIn('pot_no', ['apple', 'watermelon', 'saven_win'])
                                    ->where('user_id', $fortunesetting->winner_id)
                                    ->get()
                                    ->groupBy('pot_no')
                                    ->map(function ($pot) {
                                        return $pot->sum('amount');
                                    });

                                $winner_tray_balance = $winner_pots->sum();
                                $pot_combinations = $this->generateCombinations($winner_pots->keys()->toArray(), 2);

                                $check_winner_percentage = $traybalance / 100 * $fortunesetting->lock_parcent;

                                if ($winner_tray_balance > $check_winner_percentage) {
                                    $winning_pot = '';
                                    foreach ($pot_combinations as $combination) {
                                        if ($winner_tray_balance == array_sum($combination)) {
                                            $winning_pot = implode('_', $combination);
                                            break;
                                        }
                                    }

                                    if ($winning_pot == '') {
                                        $winning_pot = $winner_pots->keys()->random();
                                    }

                                    $result->winner = $winning_pot;
                                    $result->winner_id = $fortunesetting->winner_id;
                                    $result->randomPercentage = 'Pattern Change For winner ID ' . $fortunesetting->winner_id;
                                }
                            }
                        }

                            $last_record_for_repet_check = TeenPattiTray::latest()->take(rand(6,9))->pluck('winner')->toArray();
                            if (count(array_unique($last_record_for_repet_check)) === 1) {
                            
                            $uniqueLastRecords = array_unique($last_record_for_repet_check);
                        
                            if (count($uniqueLastRecords) === 1) {
                                $avoidValue = reset($uniqueLastRecords);
                        
                                $availableOptions = collect(["apple", "watermelon", "saven_win"]);
                                $availableOptions = $availableOptions->reject(function ($option) use ($avoidValue) {
                                    return $option === $avoidValue;
                                });
                        
                                $randomPot = $availableOptions->random();
                                $result->winner = $randomPot;
                                $result->randomPercentage = 'Change Repet ' . ucfirst($avoidValue);
                            }
                         }
                         $re_result = TeenPattiTray::where('tray_id',$tray_id)->where('status',0)->first();
                         if ($re_result) {
                           
                             if($result->winner=='apple'){
                                $winnerRanks = 1; // Change this to set the winner (1, 2, or 3)
                            }elseif ($result->winner=='saven_win') {
                                $winnerRanks = 2; // Change this to set the winner (1, 2, or 3)
                            }else{
                                $winnerRanks = 3; // Change this to set the winner (1, 2, or 3) 
                            }

                            $hands = $this->generateGameHands($winnerRanks);
                            $hands['Table']= $winnerRanks;
                            $result->cards=$hands;
                             $result->save();
                         }

        } //main coinditions    
    }
    private function generateCombinations($items, $length) {
    $result = [];

    $this->combinationsHelper($items, $result, 0, [], $length);

    return $result;
    }

    private function combinationsHelper($items, &$result, $start, $tempList, $length) {
        if (count($tempList) == $length) {
            $result[] = $tempList;
            return;
        }

        for ($i = $start; $i < count($items); $i++) {
            $tempList[] = $items[$i];
            $this->combinationsHelper($items, $result, $i + 1, $tempList, $length);
            array_pop($tempList);
        }
    }

    public function generateGameHands($winnerRanks)
    {
        $sets = ['set1','set2', 'set3', 'set4', 'set5', 'set6', 'set7', 'set8', 'set9', 'set10', 'set11', 'set12'];
        $randomKey = array_rand($sets);
        $winset = $sets[$randomKey];
        //$winset = $winnerRanks;

        if($winset == 'set1'){
            $winnerpair = 'set1rank1';
            $winnerRank = 'High Card';
            $secondwinnerpair = 'set1rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set1rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set2'){
            $winnerpair = 'set2rank1';
            $winnerRank = 'High Card';
            $secondwinnerpair = 'set2rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set2rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set3'){
            $winnerpair = 'set3rank1';
            $winnerRank = 'Straight';
            $secondwinnerpair = 'set3rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set3rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set4'){
            $winnerpair = 'set4rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set4rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set4rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set5'){
            $winnerpair = 'set5rank1';
            $winnerRank = 'Flush';
            $secondwinnerpair = 'set5rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set5rank3';
            $thirdwinnerpairRank = 'Pair';
        }
        elseif($winset == 'set6'){
            $winnerpair = 'set6rank1';
            $winnerRank = 'Flush';
            $secondwinnerpair = 'set6rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set6rank3';
            $thirdwinnerpairRank = 'Pair';
        }
        elseif($winset == 'set7'){
            $winnerpair = 'set7rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set7rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set7rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set8'){
            $winnerpair = 'set8rank1';
            $winnerRank = 'Straight';
            $secondwinnerpair = 'set8rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set8rank3';
            $thirdwinnerpairRank = 'Pair';
        }
        elseif($winset == 'set9'){
            $winnerpair = 'set9rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set9rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set9rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set10'){
            $winnerpair = 'set10rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set10rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set10rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set11'){
            $winnerpair = 'set11rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set11rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set11rank3';
            $thirdwinnerpairRank = 'High Card';
        }
        elseif($winset == 'set12'){
            $winnerpair = 'set12rank1';
            $winnerRank = 'Pair';
            $secondwinnerpair = 'set12rank2';
            $secondwinnerpairRank = 'High Card';
            $thirdwinnerpair = 'set12rank3';
            $thirdwinnerpairRank = 'High Card';
        }

        
        if($winnerRanks == 1){
            return [
                'FirstPair' => $winnerRank,
                'FirstPairCards' => $winnerpair,
                'SecondPair' =>  $secondwinnerpairRank,
                'SecondPairCards' => $secondwinnerpair,
                'ThirdPair' =>  $thirdwinnerpairRank,
                'ThirdPairCards' => $thirdwinnerpair,
                'Winner' => $winnerRank,
            ];
        }else if($winnerRanks == 2){
            return [
                'FirstPair' => $secondwinnerpairRank,
                'FirstPairCards' => $secondwinnerpair,
                'SecondPair' =>  $winnerRank,
                'SecondPairCards' => $winnerpair,
                'ThirdPair' =>  $thirdwinnerpairRank,
                'ThirdPairCards' => $thirdwinnerpair,
                'Winner' => $winnerRank,
            ];
        }else if($winnerRanks == 3){
            return [
                'FirstPair' => $thirdwinnerpairRank,
                'FirstPairCards' => $thirdwinnerpair,
                'SecondPair' =>  $secondwinnerpairRank,
                'SecondPairCards' => $secondwinnerpair,
                'ThirdPair' =>  $winnerRank,
                'ThirdPairCards' => $winnerpair,
                'Winner' => $winnerRank,
            ];
        }


    }


 
    public function LastGameWinner(Request $request)
    {
    //   $exitCode = Artisan::call('cache:clear');
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
         $data = TeenPattiTray::where('status','!=',0)->limit(14)->orderby('id','desc')->get();
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
        
        $my_tota_bet = 0; 
        $my_tota_bet_winning = 0;

        $latestTeenPattiTray = TeenPattiTray::latest()->first();
        $find = TeenPattiTray::where('id', '<', $latestTeenPattiTray->id)->latest()->first();

 
        $my_tota_bet = TeenPattiPots::where('tray_id',$find->tray_id)->where('user_id',$user->id)->sum('amount');
        $my_tota_bet_winning =TeenPattiPots::where('tray_id', $find->tray_id)->where('status', 1)->where('user_id', $user->id)->sum('win_balance');
        self::GameWinnerpusher($find->tray_id);
        return response()->json([
            'my_tota_bet' => $my_tota_bet,
            'my_tota_bet_winning' => $my_tota_bet_winning,
            'last_winner_image' => $find->winner,
        ]);
    }

     public function GameWinnerpusher()
    {
        $rk_game_soket=array();
         $fortunesetting = TeenPattiSetting::first();
         if($fortunesetting->presser_lock==0){
              $fortunesetting->block_id=0;
              $fortunesetting->winner_id=0;
             $fortunesetting->save();
         }
       
        $users_1st_name = 0; 
        $users_1st_amount_bet = 0;

        $users_2nd_name = 0; 
        $users_2nd_amount_bet = 0;

        $users_3rd_name = 0; 
        $users_3rd_amount_bet = 0;

        $latestFortuneTray = TeenPattiTray::latest()->first();
        if ($latestFortuneTray->push == 0) {
            $find = TeenPattiTray::where('id', '<', $latestFortuneTray->id)->latest()->first();
           // self::WorkSecondBalanceUp($find->tray_id);
            $getdetails = TeenPattiPots::where('tray_id', $find->tray_id)
                ->where('pot_no', $find->winner)
                ->orderBy('amount', 'DESC')
                ->get();
        
            $winners = [];
            foreach ($getdetails as $key => $value) {
                if ($value->user_id != '22222' ) {
                    $total = TeenPattiPots::where('tray_id', $find->tray_id)
                        ->where('user_id', $value->user_id)
                        ->sum('amount');
        
                    $finduser = User::find($value->user_id);
                    $user_name = $finduser->name;
                    $user_amount_bet = ($value->amount * 3);
        
                    // Add the user to the winners array
                    $winners[] = [
                        'name' => $user_name,
                        'amount_bet' => $user_amount_bet,
                        'user_id' => $finduser->id,
                    ];
                    if ($finduser) {
                        $check_old = GameBannner::where('user_id', $finduser->id)
                            ->where('tray_id', $find->tray_id)
                            ->exists();
                    
                        if (!$check_old) {
                            $words = explode(' ', $finduser->name); // Split name into words
                            $firstEightWords = implode(' ', array_slice($words, 0, 8));
                            $data = new GameBannner;
                            $data->user_id = $finduser->id;
                            $data->name = $firstEightWords;
                            $data->level = $finduser->level;
                            $data->tray_id = $find->tray_id;
                            $data->message = 'Win ' . $user_amount_bet;
                            $data->game = 'Teen Patti';
                            $data->banner_color = 'red';
                            $data->save();
                        }
                    }
                    // Limit the number of winners to 10
                    if (count($winners) >= 20) {
                        break;
                    }
                }
            }
        
            $message = '';
        
            foreach ($winners as $index => $winner) {
                if ($winner['amount_bet']>0) {
                    $message .= $winner['name'] . ' win ' . $winner['amount_bet'] . ' From Fruits Game';
                    
                }
                
        
                if ($index < count($winners) - 1) {
                    $message .= ', ';
                }
            }
        
            // Add a period at the end of the message
            $message .= '.';
           $latestFortuneTray->push=1;
            $latestFortuneTray->save();
            
             //THIRD BALANCE
             $setting=TeenPattiSetting::find(1);
            $take_margin = $setting->third_take_margin;
            $half_give_margin = $setting->third_helf_give_margin;
            $all_give_margin = $setting->third_full_give_margin;
            $total_pots_amount = TeenPattiPots::where('tray_id', $find->tray_id)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');

            $tray = TeenPattiTray::where('tray_id', $find->tray_id)->first();
            $tray->tray_margin = $total_pots_amount;

            if ($total_pots_amount < $take_margin) {
                $tray->third_balance_calculation = 1;
            } elseif ($total_pots_amount >= $half_give_margin && $total_pots_amount < $all_give_margin) {
                $tray->third_balance_calculation = 2;
            } elseif ($total_pots_amount >= $all_give_margin) {
                $tray->third_balance_calculation = 3;
            }else{
                $tray->third_balance_calculation = 1;
            }
            $tray->save();

            self::checkthirdbalace();
          
           self::check_winner_lock_id($tray->tray_id);

        }

    }
     public function checkthirdbalace(){
         $setting=TeenPattiSetting::find(1);
        $take_margin = $setting->third_take_margin;
        $half_give_margin = $setting->third_helf_give_margin;
        $all_give_margin = $setting->third_full_give_margin;
        $last_three_tray = TeenPattiTray::where('status', 1)
                   ->latest()
                   ->take(3)
                   ->get();

        if ($last_three_tray->pluck('third_balance_calculation')->unique()->count() === 1) {
            $third_balance_calculation = $last_three_tray->first()->third_balance_calculation;

            if ($third_balance_calculation == 1 && $setting->third_balance_status == 0) {
                $balance_take_amount = $setting->game_balance *($setting->third_take_parcentage / 100);;
                $setting->game_balance -= $balance_take_amount;
                $setting->third_balance += $balance_take_amount;
                $setting->third_balance_status=1;
            } elseif ($third_balance_calculation == 2 && $setting->third_balance_status == 1) {
                 $balance_take_amount = $setting->third_balance *($setting->third_helf_given_parcentage / 100);;
                $setting->game_balance += $balance_take_amount;
                $setting->third_balance -= $balance_take_amount;
                $setting->third_balance_status=0;
            } elseif ($third_balance_calculation == 3 && $setting->third_balance_status == 1) {
                $balance_take_amount = $setting->third_balance *($setting->third_full_given_parcentage / 100);;
                $setting->game_balance += $balance_take_amount;
                $setting->third_balance -= $balance_take_amount;
                $setting->third_balance_status=0;
            }

            // Save the updated setting if necessary
            $setting->save();
        }

    }
     public function check_winner_lock_id($tray_id){
        $tray = TeenPattiTray::where('tray_id', $tray_id)->first();
        $pot_users =TeenPattiPots::where('tray_id', $tray->tray_id)->groupBy('user_id')->select('user_id')->distinct()->get();
        foreach ($pot_users as $key => $pot_user) {
            if($pot_user->user_id!=555555){
            $user=User::find($pot_user->user_id);
            $today_sanding=Gift::where('sander_id',$user->id)->whereDate('date',date('Y-m-d'))->sum('value');
            $total_balance=$today_sanding+$user->balance;
            $today_balance=$user->date_wise_balance;
            if ($total_balance < 0.5 * $today_balance) {
             $check_id_have_already=FortuneLock::where('user_id',$user->id)->first();
             if(!$check_id_have_already){
            $data = new FortuneLock();
            $data->user_id = $user->id;
            $data->imei_number = $user->imei_number;
            $data->auto_lock_active = 'auto win';
            $data->parcentage = 15; // Note: The correct spelling is 'percentage'
            $data->type = 1; // Note: The correct spelling is 'percentage'
            $data->save();
            }
            } elseif ($total_balance > 1.1 * $today_balance) {
                // Remove the data if it exists
                FortuneLock::where('user_id', $user->id)->where('type',1)->where('auto_lock_active','!=',null)
                    ->delete();
            }

        }
        }
       // self::check_block_lock_id($tray->tray_id);

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
        $old_data = TeenPattiUser::where('user_id', Auth::user()->id)
        ->where('tray_id', $request->tray_id)
        ->first();

        if (!$old_data) {
            
            $data = new TeenPattiUser;
            $data->user_id = Auth::user()->id;
            $data->tray_id = $request->tray_id;
            $data->save();
            
            
            TeenPattiUser::where('user_id', Auth::user()->id)
                ->where('tray_id','!=', $request->tray_id)
                ->delete();
           
        } else {
            

            $all_users = TeenPattiUser::where('tray_id','!=', $request->tray_id)->get();
            foreach ($all_users as $key => $all_user) {
                $all_user->delete();
            }
        }
        $top_four = DB::table('teen_patti_users')
            ->join('users', 'users.id', 'teen_patti_users.user_id')
            ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile','users.id')
            ->orderBy('users.balance', 'desc')
            ->get();


       

        return response()->json(['data' => $top_four]);

    }
    public function AllActiveUser()
    {
        $data= DB::table('teen_patti_users')
    ->join('users', 'users.id', 'teen_patti_users.user_id')
    ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile')
    ->orderBy('users.balance', 'desc')
    ->get();
       return response()->json(['data' => $data]);
    }
   
  public function UserResult(Request $request)
    {
        $email = $request->authtoken;
        $pass = $request->authkey;
        $user = User::where('email', $email)->first();

        if (!$user || $pass != $user->password) {
            return false;
        }

       Auth::login($user);
        
       $firust_game=TeenPattiPots::orderby('id','desc')->where('user_id',Auth::id())->orderby('id','desc')->limit(18)->get();
       return response()->json(['data' => $firust_game]);
    }
}
