<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Game\Grady\GradyTray;
use App\Models\Game\Grady\GradyPots;
use App\Models\Game\Grady\GradySetting;
use Pusher;
use App\Models\GradyActiveUser;
use App\Models\GameBannner;
use App\Models\GameBalanceWithdraw;
use App\Models\GreedyPattern;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
class GradyController extends Controller
{
    public function Index(Request $request)
    {
       // return $request->all();
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
        //dd($authtoken);
        return view('game.grady.index',compact('authtoken','authkey'));
    }
  public function TimeCall(Request $request)
{
    $findpots2 = GradyTray::where('status', 0)->latest('id')->first();
    if ($findpots2) {
        $findpots2->status = 0;
        $findpots2->save();
    }

    $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();
    if (!$user || $pass !== $user->password) {
        return false;
    }

    Auth::login($user);

    $setting = GradySetting::find(1);

    if ($setting->game_status == 1 && $request->has('tray_id')) {
        $get_tray_id = $request->get('tray_id');
  // return   $setting;

        // Wrap the code in a transaction
        DB::beginTransaction();

        try {
            $find = GradyTray::latest('id')->lockForUpdate()->first();
            if ($find && $find->status == 0) {
                if ($find->tray_id >= $get_tray_id) {
                    $currentTime = Carbon::now()->timestamp;
                    $currentTimeInSeconds = $currentTime;
                    return response()->json([
                        'data' => $find->tray_id,
                        'currentTimeInSeconds' => $currentTimeInSeconds,
                        'st' => true
                    ]);
                } else {
                    $data = time();
                    $datas = strtotime("+46 seconds", $data);
                    $currentTime = Carbon::now()->timestamp;
                    $currentTimeInSeconds = $currentTime;
                    $pots_setting = GradySetting::find(1);
                $pattarn = GreedyPattern::find($pots_setting->running_pattarn + 1) ?? GreedyPattern::first();
                $selected_pots = $pattarn->pots;

                $pots_setting->pots_name = $selected_pots;
                $pots_setting->save();
                    // Add a unique constraint on tray_id column
                    $gamestart = new GradyTray;
                    $gamestart->tray_id = $datas;
                    $gamestart->winner = $selected_pots;
                    $gamestart->status = false;
                    $gamestart->save();

                    DB::commit(); // Commit the transaction

                    return response()->json([
                        'data' => $datas,
                        'currentTimeInSeconds' => $currentTimeInSeconds,
                        'st' => true
                    ]);
                }
            } else {
                $data = time();
                $datas = strtotime("+46 seconds", $data);
                $currentTime = Carbon::now()->timestamp;
                $currentTimeInSeconds = $currentTime;
                $check_tray = GradyTray::where('tray_id', $datas)->latest('id')->first();
                $pots_setting = GradySetting::find(1);
                $pattarn = GreedyPattern::find($pots_setting->running_pattarn + 1) ?? GreedyPattern::first();
                $selected_pots = $pattarn->pots;

                $pots_setting->pots_name = $selected_pots;
                $pots_setting->save();
                // Add a unique constraint on tray_id column
                $gamestart = new GradyTray;
                $gamestart->tray_id = $datas;
                $gamestart->winner = $selected_pots;
                $gamestart->status = false;
                $gamestart->save();

                DB::commit(); // Commit the transaction

                return response()->json([
                    'data' => $datas,
                    'currentTimeInSeconds' => $currentTimeInSeconds,
                    'st' => true
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack(); // Rollback the transaction in case of an exception
            throw $e;
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
        if(Auth::id()==1111){

        return response()->json(['balance' => $user_data->balance ,'st' => true]);
        }
    }
   public function AccountList(Request $request)
    {
       
        
    $email = $request->authtoken;
    $pass = $request->authkey;
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }
   
        Auth::login($user);
        $user_data = Auth::user();
        $data = [];
        $results = GradyPots::select('tray_id')
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('SUM(CASE WHEN status = 1 THEN win_balance ELSE 0 END) as total_win_balance')
            ->where('user_id', 22222)
            ->groupBy('tray_id')
            ->limit(30)
            ->get();
        
        foreach ($results as $row) {
            $tray = GradyTray::where('tray_id', $row->tray_id)->first();
            $item = [
                'tray_id' => $tray->tray_id,
                'winner' => $tray->winner,
                'bet_amount' => $row->total_amount,
                'total_win_balance' => $row->total_win_balance,
            ];
        
            array_push($data, $item); // Add the $item to the $data array
        }
        
        
        return response()->json(['data' => $data ,'st' => true]);
        
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
    $fortunesetting = GradySetting::first();
    $findpots = GradyPots::where('user_id', Auth::user()->id)->where('status', 0)->get();

    if ($user) {
        foreach ($findpots as $key => $value) {
            $find = GradyTray::where('tray_id', $value->tray_id)->where('status', 1)->first();

            if ($find) {
                $winner = $find->winner;

                if (($winner == 'animals' && in_array($value->pot_no, ['lion', 'cat', 'tiger', 'horse']))
                    || ($winner == 'vegetable' && in_array($value->pot_no, ['apple', 'grapes', 'banana', 'lemon']))
                    || ($value->pot_no == $winner && $winner != 'animals' && $winner != 'vegetable')
                ) {
                    $user->increment('balance', $value->win_balance);
                    $value->status = 1;
                    $fortunesetting->decrement('game_balance', $value->win_balance);
                } else {
                    $value->status = 10;
                }

                $value->save();
                $user->save();
                $fortunesetting->save();
            }
        }
    }
}

  public function FortuneInsert(Request $request)
{
   // return $request->all();
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
if(Auth::id()==1111){
    if (GradyTray::where('tray_id', $trayId)->exists()) {
        DB::transaction(function () use ($amount, $loginUser, $trayId, $boardName) {
            $existingGameStart = GradyPots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)
                ->where('pot_no', $boardName)
                ->first();
                $check_third= GradyPots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)->count();
               
                if ($loginUser->balance>=$amount) {
                    
               // Self::PushUserData($boardName,$amount);
            if ($existingGameStart) {
                $existingGameStart->amount += $amount;
                if($boardName=='lion'){
                $existingGameStart->win_balance = $existingGameStart->amount * 45;
                }elseif ($boardName=='cat') {
                $existingGameStart->win_balance = $existingGameStart->amount * 10;
                }elseif ($boardName=='tiger') {
                $existingGameStart->win_balance = $existingGameStart->amount * 25;
                }elseif ($boardName=='horse') {
                $existingGameStart->win_balance = $existingGameStart->amount * 15;
                }else{
                $existingGameStart->win_balance = $existingGameStart->amount * 5;
                }
                $existingGameStart->now_user_balance = $loginUser->balance - $amount;
                $existingGameStart->save();
                $loginUser->decrement('balance', $amount);
                $fortunesetting = GradySetting::first();
                $fortunesetting->increment('game_balance', $amount);
           
            } else {
                $gameStart = new GradyPots();
                $gameStart->tray_id = $trayId;
                $gameStart->user_id = $loginUser->id;
                $gameStart->amount = $amount;
                if($boardName=='lion'){
                $gameStart->win_balance = $amount * 45;
                }elseif ($boardName=='cat') {
                $gameStart->win_balance = $amount * 10;
                }elseif ($boardName=='tiger') {
                $gameStart->win_balance = $amount * 25;
                }elseif ($boardName=='horse') {
                $gameStart->win_balance = $amount * 15;
                }else{
                $gameStart->win_balance = $amount * 5;
                }
                $gameStart->pot_no = $boardName;
                $gameStart->now_user_balance = $loginUser->balance - $amount;
                $gameStart->status = 0;
                $gameStart->save();

                $loginUser->decrement('balance', $amount);
                $fortunesetting = GradySetting::first();
                $fortunesetting->increment('game_balance', $amount);
               
            }
            
        }
        });
    }
}
    

}



    public function BettingInsert()
    {
        return view('game.grady.index');
    } 
     public function WinnerDecission($tray_id)
    {
        $result = GradyTray::where('tray_id',$tray_id)->where('status',0)->first();
        if ($result) {
                    $fortunesetting = GradySetting::first();             
                    $select_pots= GradyPots::where('tray_id', $result->tray_id)->where('pot_no',$fortunesetting->pots_name)->sum('amount');
                    $apple = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $grapes = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'grapes')->sum('amount');
                    $banana = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'banana')->sum('amount');
                    $lemon = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'lemon')->sum('amount');
                    $lion = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'lion')->sum('amount');
                    $cat = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'cat')->sum('amount');
                    $tiger = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'tiger')->sum('amount');
                    $horse = GradyPots::where('tray_id', $result->tray_id)->where('pot_no', 'horse')->sum('amount');

                    $apple_need_serve = $apple*5;
                    $banana_need_serve= $banana*5;
                    $grapes_need_serve= $grapes*5;
                    $lemon_need_serve= $lemon*5;
                    $lion_need_serve= $lion*45;
                    $cat_need_serve= $cat*10;
                    $tiger_need_serve= $tiger*25;
                    $horse_need_serve= $horse*15;
                    $animal_need_serve=$lion_need_serve+$cat_need_serve+$tiger_need_serve+$horse_need_serve;
                    $frutis_need_serve=$apple_need_serve+$banana_need_serve+$grapes_need_serve+$lemon_need_serve;
                    $values = [$apple_need_serve, $banana_need_serve, $grapes_need_serve,$lemon_need_serve,$horse_need_serve,$tiger_need_serve,$cat_need_serve,$lion_need_serve];
                    rsort($values);
                    $first = $values[0];     // Highest value
                    $second = $values[1];    // Second highest value
                    $third = $values[2];     // Lowest value
                    $fouth = $values[3];     // Lowest value
                    $five = $values[4];     // Lowest value
                    $six = $values[5];     // Lowest value
                    $seven = $values[6];     // Lowest value
                    $eight = $values[7];     // Lowest value
                    $randomValue = null;
                    $randomPot = null;
                    $traybalance = GradyPots::where('tray_id', $result->tray_id)->sum('amount');
                     
                    $pot_multipliers = [
                        'apple' => 6,
                        'grapes' => 6,
                        'banana' => 6,
                        'lemon' => 6,
                        'lion' => 46,
                        'cat' => 11,
                        'tiger' => 26,
                        'horse' => 16,
                    ];

                    if (array_key_exists($fortunesetting->pots_name, $pot_multipliers)) {
                        $select_pots_need_serve = $select_pots * $pot_multipliers[$fortunesetting->pots_name];
                    } elseif ($fortunesetting->pots_name == 'animals') {
                        $select_pots_need_serve = $animal_need_serve;
                    } elseif ($fortunesetting->pots_name == 'vegetable') {
                        $select_pots_need_serve = $frutis_need_serve;
                    } else {
                        $select_pots_need_serve = $select_pots * 13;
                    }

                 $currentMinute = date('i'); // Get the current minute
                 $game_balance= $fortunesetting->game_balance;
                
               if ($select_pots_need_serve < $game_balance) {
                   
                    $result->randomPercentage = 'Pattern';
                    
                }else{
                    $seconrandomPercentage = mt_rand(2, 10) / 100; 
                    $secendtenPercent = $game_balance * $seconrandomPercentage;
                    $secondnow_game_balance_after_percent=$game_balance-$secendtenPercent;
                         if ($first < $secondnow_game_balance_after_percent) {
                        $randomValue = $first;
                        } elseif ($second < $secondnow_game_balance_after_percent) {
                            $randomValue =$second ;
                        }elseif ($third < $secondnow_game_balance_after_percent) {
                            $randomValue =$third ;
                        }elseif ($fouth < $secondnow_game_balance_after_percent) {
                            $randomValue =$fouth ;
                        }elseif ($five < $secondnow_game_balance_after_percent) {
                            $randomValue =$five ;
                        }elseif ($six < $secondnow_game_balance_after_percent) {
                            $randomValue =$six ;
                        } elseif ($seven < $secondnow_game_balance_after_percent) {
                            $randomValue =$seven ;
                        } else {
                            $randomValue = $eight;
                        }
                         $pattarn='pattern Change % :'.$seconrandomPercentage;
                         if ($randomValue !== null) {
                                
                                $needServeMap = [
                                    $apple_need_serve => 'apple',
                                    $grapes_need_serve => 'grapes',
                                    $lemon_need_serve => 'lemon',
                                    $banana_need_serve => 'banana',
                                    $lion_need_serve => 'lion',
                                    $cat_need_serve => 'cat',
                                    $tiger_need_serve => 'tiger',
                                    $horse_need_serve => 'horse',
                                ];

                                $randomPot = $needServeMap[$randomValue] ?? 'apple';
                                $result->winner=$randomPot;
                            
                        } else {
                                    $needServeMap = [
                                    'apple' => $apple_need_serve,
                                    'grapes' => $grapes_need_serve,
                                    'lemon' => $lemon_need_serve,
                                    'banana' => $banana_need_serve,
                                    'lion' => $lion_need_serve,
                                    'cat' => $cat_need_serve,
                                    'tiger' => $tiger_need_serve,
                                    'horse' => $horse_need_serve,
                                ];

                                $randomPot = array_search(min($needServeMap), $needServeMap);

                                $result->winner=$randomPot;
                        }
                        $result->randomPercentage = $pattarn;
                        
                    }//winning condition end
                            $last_record_for_repet_check = GradyTray::latest()->take(rand(9,11))->pluck('winner')->toArray();
                            if (count(array_unique($last_record_for_repet_check)) === 1) {
                            
                            $uniqueLastRecords = array_unique($last_record_for_repet_check);
                        
                            if (count($uniqueLastRecords) === 1) {
                                $avoidValue = reset($uniqueLastRecords);
                        
                                $availableOptions = collect(["apple", "grapes", "lemon", "banana"]);
                                $availableOptions = $availableOptions->reject(function ($option) use ($avoidValue) {
                                    return $option === $avoidValue;
                                });
                        
                                $randomPot = $availableOptions->random();
                                $result->winner = $randomPot;
                                $result->randomPercentage = 'Change Repet ' . ucfirst($avoidValue);
                            }
                         }
                         $re_result = GradyTray::where('tray_id',$tray_id)->where('status',0)->first();
                         if ($re_result) {
                            $result->game_balance=$traybalance;
                            $result->apple=$apple;
                            $result->banana=$banana;
                            $result->grapes=$grapes;
                            $result->lemon=$lemon;
                            $result->lion=$lion;
                            $result->cat=$cat;
                            $result->tiger=$tiger;
                            $result->horse=$horse;
                            $result->save();
                         }

        } //main coinditions    
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
              $find = GradyTray::where('tray_id',$get_tray_id)->where('result_status',0)->first();
              if($find){
                self::WinnerDecission($find->tray_id);
                $find->result_status=1;
                $find->save();
                $traybalance = GradyPots::where('tray_id', $find->tray_id)->sum('amount');
                $fortunesetting = GradySetting::first();
                if($traybalance>200000){
                $withdraw_percentage = 2.1/100*$traybalance; 
                }else{
                $withdraw_percentage = 1.9/100*$traybalance; 
                }
                $profit_withdraw=GameBalanceWithdraw::find(1);
                $profit_withdraw->amount+=$withdraw_percentage;
                if($profit_withdraw->save()){
                  $fortunesetting->game_balance-=$withdraw_percentage;
                  $fortunesetting->save();
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
        
         if ($request->has('tray_id')) {
        $get_tray_id = $request->get('tray_id');
        $find = GradyTray::where('tray_id', $get_tray_id)->where('result_status', 1)->where('status',0)->first();
        
        if (!$find || $find->tray_id != $get_tray_id) {
            return;
        }               self::WinnerDecission($find->tray_id);
                        $fortunesetting = GradySetting::first(); 
                        $patarn_check_is_update = GradyTray::where('tray_id', $get_tray_id)
                        ->where('status', '!=',1)
                        ->where('result_status',1)
                        ->exists();
                        if ($patarn_check_is_update) {
                        $fortunesetting->running_pattarn += 1;
                        if (!GreedyPattern::find($fortunesetting->running_pattarn)) {
                            $fortunesetting->running_pattarn = 0;
                        }
                        $fortunesetting->save();
                        }
 
                        $find->status=1;
                        $find->save();
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
            $find = GradyTray::where('tray_id',$get_tray_id)->where('status',1)->where('result_status',1)->first();
           
          
            if($find){
                    if($find->tray_id == $get_tray_id){                   
                        return response()->json(['data' => $find->winner,'st' => true]);
                 
                    }
                    
                }

                
            }
        
    }
    public function PushUserData($boardName, $amount)
    {

        $options = array(
            'cluster' => 'ap1',
            'useTLS' => true
        );
        $pusher = new Pusher\Pusher(
            '220537e775c5be3c7165',
            '33865c734c50e6d693e8',
            '1670692',
            $options
        );

        $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => $boardName, 'bord_amount' => $amount]); 
 
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
         $data = GradyTray::where('status','!=',0)->limit(10)->orderby('id','desc')->get();
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

        $latestFortuneTray = GradyTray::latest()->first();
        $find = GradyTray::where('id', '<', $latestFortuneTray->id)->latest()->first();

       $getdetails = GradyPots::where('tray_id', $find->tray_id)
        ->where('status', 1)->where('user_id','!=','22222')
        ->groupBy('user_id')
        ->selectRaw('user_id, SUM(amount) as total_amount, SUM(win_balance) as total_win_balance')
        ->orderByDesc('total_amount')
        ->limit(3)
        ->get();
        foreach ($getdetails as $key => $value) {
            
            $win_user = User::find($value->user_id);
            if ($win_user) {
                        $check_old = GameBannner::where('user_id', $win_user->id)
                            ->where('tray_id', $find->tray_id)
                            ->exists();
                    
                        if (!$check_old) {
                            $data = new GameBannner;
                            $data->user_id = $win_user->id;
                            $data->name = Str::limit($win_user->name, 5 , ""); ;
                            $data->level = $win_user->level;
                            $data->tray_id = $find->tray_id;
                            $data->message = 'Win ' . $value->total_win_balance;
                            $data->game = 'Greedy';
                            $data->banner_color = 'red';
                            $data->save();
                        }
                    }
            if($key == 0){
                $total = GradyPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                $finduser = User::find($value->user_id);
                $users_1st_amount = $total;
                $users_1st_name = Str::limit($finduser->name, 5 , ""); 
                $users_1st_img = url($finduser->profile); 
                $users_1st_amount_bet = $value->total_win_balance;
                
            }
            if($key == 1){
                $total = GradyPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                $finduser = User::find($value->user_id);
                $users_2nd_amount = $total;
                $users_2nd_name = Str::limit($finduser->name, 5 , ""); 
                $users_2nd_img = url($finduser->profile); 
                $users_2nd_amount_bet = $value->total_win_balance;
            }
            if($key == 2){
                 
                $total = GradyPots::where('tray_id', $find->tray_id)->where('user_id', $value->user_id)->sum('amount');
                $finduser = User::find($value->user_id);
                $users_3rd_amount = $total;
                $users_3rd_name = Str::limit($finduser->name, 5 , ""); 
                $users_3rd_img = url($finduser->profile); 
                $users_3rd_amount_bet = $value->total_win_balance;
            }
        }
 
        $my_tota_bet = GradyPots::where('tray_id',$find->tray_id)->where('user_id',$user->id)->sum('amount');
        $my_tota_bet_winning =GradyPots::where('tray_id', $find->tray_id)->where('status', 1)->where('user_id', $user->id)->sum('win_balance');

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
            'last_winner_image' => $find->winner,
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
        $old_data = GradyActiveUser::where('user_id', Auth::user()->id)
        ->where('tray_id', $request->tray_id)
        ->first();

        if (!$old_data) {
            if(Auth::id()!='22222' || Auth::id()!='1111'){
            $data = new GradyActiveUser;
            $data->user_id = Auth::user()->id;
            $data->tray_id = $request->tray_id;
            $data->save();
            }
            GradyActiveUser::where('user_id', Auth::user()->id)
                ->where('tray_id','!=', $request->tray_id)
                ->delete();
        } else {
            

            $all_users = GradyActiveUser::where('tray_id','!=', $request->tray_id)->get();
            foreach ($all_users as $key => $all_user) {
                $all_user->delete();
            }
        }
        $top_four = DB::table('grady_active_users')
            ->join('users', 'users.id', 'grady_active_users.user_id')
            ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile')
            ->orderBy('users.balance', 'desc')
            ->get();


       

        return response()->json(['data' => $top_four]);

    }
    public function AllActiveUser()
    {
        $data= DB::table('grady_active_users')
    ->join('users', 'users.id', 'grady_active_users.user_id')
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
        
       $firust_game=GradyPots::orderby('id','desc')->where('user_id',Auth::id())->orderby('id','desc')->limit(18)->get();
       return response()->json(['data' => $firust_game]);
    }
}
