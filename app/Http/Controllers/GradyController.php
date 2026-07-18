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
use App\Models\GreedyPattern;
use App\Models\BanDevice;
use App\Models\GameBalanceWithdraw;
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

                    // Add a unique constraint on tray_id column
                    $gamestart = new GradyTray;
                        $gamestart->tray_id = $datas;
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

                // Add a unique constraint on tray_id column
                $gamestart = new GradyTray;
                $gamestart->tray_id = $datas;
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
       
        return response()->json(['balance' => $user_data->balance ,'st' => true]);

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
            $check_bid=GradyPots::where('user_id',Auth::user()->id)->where('tray_id',$value->tray_id)->count();
                        if($check_bid>7){
                             GradyPots::where('user_id', Auth::id())
                                ->where('tray_id', $value->tray_id)
                                ->update(['status' => 10]);
                        }
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

    if (GradyTray::where('tray_id', $trayId)->exists()) {
        DB::transaction(function () use ($amount, $loginUser, $trayId, $boardName) {
            $existingGameStart = GradyPots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)
                ->where('pot_no', $boardName)
                ->first();
             
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



    public function BettingInsert()
    {
        return view('game.grady.index');
    } 
  
    public function WinnerDecission($tray_id)
    {
         $find = GradyTray::where('tray_id',$tray_id)->where('status',0)->first();
          
            if($find){ 
                 if($find->tray_id && $find->status==0 ){
                       $apple = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $grapes = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'grapes')->sum('amount');
                    $banana = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'banana')->sum('amount');
                    $lemon = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lemon')->sum('amount');
                    $lion = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lion')->sum('amount');
                    $cat = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'cat')->sum('amount');
                    $tiger = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'tiger')->sum('amount');
                    $horse = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'horse')->sum('amount');

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
                    $traybalance = GradyPots::where('tray_id', $find->tray_id)->sum('amount');
                    $five_tray_balance=$apple+$grapes+$banana+$lemon+$cat;
                    $six_tray_balance=$apple+$grapes+$banana+$lemon+$cat+$horse;
                   //dd($traybalance);
                   $fortunesetting = GradySetting::first();
                   $game_balance= $fortunesetting->game_balance;
                   $first_pattrn_balance=$five_tray_balance;
                    $seconrandomPercentage = mt_rand(1,5) / 100; 
                    $five_tray_balance=$apple+$grapes+$banana+$lemon+$cat;
                    $six_tray_balance=$apple+$grapes+$banana+$lemon+$cat+$horse;

                   $secendtenPercent = $game_balance * $seconrandomPercentage;
                   $now_game_balance_after_percent=$game_balance-$secendtenPercent;

                  $big_pots_balance=($game_balance+$fortunesetting->second_balance)-$secendtenPercent;
                  $uniqueUserCount = GradyPots::where('tray_id', $find->tray_id)
                                    ->distinct('user_id')
                                    ->count('user_id');
                    $currentMinute = date('i'); 
                   switch (true) {
                                case ($currentMinute >= 7 && $currentMinute <= 8) || ($currentMinute >= 27 && $currentMinute <= 28) || ($currentMinute >= 47 && $currentMinute <= 48) || $currentMinute == 1 || $currentMinute == 14 || $currentMinute == 21 || $currentMinute == 34 || $currentMinute == 41 || $currentMinute == 54:
                                    $randomValue = $this->getRandomValue($five_tray_balance, $first, $second, $third, $fouth, $five, $six, $seven, $eight);
                                    $pattern = '1st Pattern Five Tray balance';
                                    break;

                                case ($currentMinute >= 3 && $currentMinute <= 4) || ($currentMinute >= 11 && $currentMinute <= 12) || ($currentMinute >= 16 && $currentMinute <= 17) || ($currentMinute >= 23 && $currentMinute <= 24) || ($currentMinute >= 31 && $currentMinute <= 32) || ($currentMinute >= 36 && $currentMinute <= 37) || ($currentMinute >= 43 && $currentMinute <= 44) || ($currentMinute >= 51 && $currentMinute <= 52) || ($currentMinute >= 56 && $currentMinute <= 57) || $currentMinute == 9 || $currentMinute == 20 || $currentMinute == 29 || $currentMinute == 40 || $currentMinute == 49 || $currentMinute == 60:
                                    $randomValue = $this->getRandomValue($six_tray_balance, $first, $second, $third, $fouth, $five, $six, $seven, $eight);
                                    $pattern = '2nd Pattern Six Tray balance';
                                    break;

                                case ($currentMinute >= 5 && $currentMinute <= 6) || ($currentMinute >= 25 && $currentMinute <= 26) || ($currentMinute >= 45 && $currentMinute <= 46) || $currentMinute == 2 || $currentMinute == 10 || $currentMinute == 13 || $currentMinute == 15 || $currentMinute == 19 || $currentMinute == 22 || $currentMinute == 30 || $currentMinute == 33 || $currentMinute == 35 || $currentMinute == 39 || $currentMinute == 42 || $currentMinute == 50 || $currentMinute == 53 || $currentMinute == 55 || $currentMinute == 59:
                                    $randomValue = $this->getRandomValue($now_game_balance_after_percent, $first, $second, $third, $fouth, $five, $six, $seven, $eight);
                                    $pattern = '3rd Pattern All Game balance + Tray';
                                    break;

                                default:
                                    $check_winners = GradyTray::where('result_status', 1)
                                        ->where('status', 1)
                                        ->orderByDesc('id')
                                        ->limit($fortunesetting->bid_brack)
                                        ->get();
                                    
                                    $check_winnerNames = $check_winners->pluck('winner')->toArray();
                                    
                                    if (!in_array('animals', $check_winnerNames) && $animal_need_serve < $big_pots_balance && $uniqueUserCount > 2) {
                                        $randomValue = $animal_need_serve;
                                        $pattern = 'Bigger Pattern With Pizza & Salad Need '.$animal_need_serve.' Balance '.$big_pots_balance.' active user '.$uniqueUserCount;
                                    } elseif (!in_array('vegetable', $check_winnerNames) && $frutis_need_serve < $big_pots_balance && $uniqueUserCount > 2) {
                                        $randomValue = $frutis_need_serve;
                                       $pattern = 'Bigger Pattern With Pizza & Salad Need '.$frutis_need_serve.' Balance '.$big_pots_balance.' active user '.$uniqueUserCount;
                                    } elseif (!in_array('lion', $check_winnerNames) && $lion_need_serve < $big_pots_balance && $uniqueUserCount > 2) {
                                        $randomValue = $lion_need_serve;
                                      $pattern = 'Bigger Pattern With Pizza & Salad Need '.$lion_need_serve.' Balance '.$big_pots_balance.' active user '.$uniqueUserCount;
                                    } else {
                                        $randomValue = $eight;
                                        $pattern = 'Bigger Pattern With Pizza & Salad Need Balance '.$big_pots_balance.' active user '.$uniqueUserCount;
                                    }
                                    
                                    break;
                    }
                    if ($randomValue !== null) {
                        // Check if all the elements are equal
                        if ($first == $second && $second == $third && $third == $fouth && $fouth == $five && $five == $six && $six == $seven && $seven == $eight) {
                            $arr = ["apple", "banana", "grapes", "lemon", "lion", "tiger", "cat", "horse"];
                            $randomPot = $arr[array_rand($arr)];
                        } else {
                            $potValues = [
                                'apple' => $apple_need_serve,
                                'banana' => $banana_need_serve,
                                'grapes' => $grapes_need_serve,
                                'lemon' => $lemon_need_serve,
                                'lion' => $lion_need_serve,
                                'cat' => $cat_need_serve,
                                'tiger' => $tiger_need_serve,
                                'horse' => $horse_need_serve,
                                'animals' => $animal_need_serve,
                                'vegetable' => $frutis_need_serve,
                            ];

                            // Determine the randomPot based on the randomValue
                            $randomPot = array_search($randomValue, $potValues) ?: 'default';

                            // Get recent winners
                            $winners = GradyTray::where('result_status', 1)->where('status', 1)
                                ->orderByDesc('id')
                                ->limit(rand(2, 3))
                                ->get();
                            $winnerNames = $winners->pluck('winner')->toArray();

                            // Check if there's a repetition
                            $it_repet = count(array_unique($winnerNames)) === 1 && reset($winnerNames) === $randomPot ? 1 : 0;

                            if ($it_repet == 1) {
                                $potAmounts = [
                                    'apple' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount'),
                                    'grapes' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'grapes')->sum('amount'),
                                    'banana' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'banana')->sum('amount'),
                                    'lemon' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lemon')->sum('amount'),
                                    'lion' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lion')->sum('amount'),
                                    'cat' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'cat')->sum('amount'),
                                    'tiger' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'tiger')->sum('amount'),
                                    'horse' => GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'horse')->sum('amount'),
                                ];

                                $needServe = [
                                    'apple' => $potAmounts['apple'] * 5,
                                    'banana' => $potAmounts['banana'] * 5,
                                    'grapes' => $potAmounts['grapes'] * 5,
                                    'lemon' => $potAmounts['lemon'] * 5,
                                    'lion' => $potAmounts['lion'] * 45,
                                    'cat' => $potAmounts['cat'] * 10,
                                    'tiger' => $potAmounts['tiger'] * 25,
                                    'horse' => $potAmounts['horse'] * 15,
                                ];

                                $selectrandomPot = $potAmounts[$randomPot] ?? 0;
                                $rvalues = array_diff_key($needServe, [$randomPot => $selectrandomPot]);
                                rsort($rvalues);

                                // Get the highest value less than the balance
                                foreach ($rvalues as $value) {
                                    if ($value < $now_game_balance_after_percent) {
                                        $randomValue = $value;
                                        break;
                                    }
                                }

                                $randomPot = array_search($randomValue, $needServe) ?: 'default';
                            }
                        }

                        // Set the winner and win_balance
                        $find->winner = $randomPot;
                        $find->win_balance = $potValues[$randomPot] ?? 0;
                    } else {
                        $values = [
                            $apple_need_serve, $banana_need_serve, $grapes_need_serve,
                            $lemon_need_serve, $horse_need_serve, $tiger_need_serve,
                            $cat_need_serve, $lion_need_serve
                        ];
                        $lowestValue = min($values);

                        $winner = array_search($lowestValue, $values);
                        $find->winner = $winner;
                        $find->win_balance = $lowestValue;
                    }

                    
                    $find->game_balance=$traybalance;
                    $find->apple=$apple;
                    $find->banana=$banana;
                    $find->grapes=$grapes;
                    $find->lemon=$lemon;
                    $find->lion=$lion;
                    $find->cat=$cat;
                    $find->tiger=$tiger;
                    $find->horse=$horse;
                    $find->randomPercentage=$pattern;
                    $find->save();
                    
                    
                }
             
            }
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
        }
        // $find->winner='lion';
        // $find->save();
                 $traybalance = GradyPots::where('tray_id', $find->tray_id)->sum('amount');
                    $fortunesetting = GradySetting::first();
                      $game_balance= $fortunesetting->game_balance;
                     $percentage = $fortunesetting->take_parcenage/100; // 5%
                      $amount5Percent = $traybalance *$percentage;
                      $cut_amount=$game_balance-$amount5Percent;
                      $fortunesetting->game_balance-=$amount5Percent;
                      $fortunesetting->second_balance+=$amount5Percent;
                      $fortunesetting->save(); 
                    $apple = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->sum('amount');
                    $grapes = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'grapes')->sum('amount');
                    $banana = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'banana')->sum('amount');
                    $lemon = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lemon')->sum('amount');
                    $lion = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'lion')->sum('amount');
                    $cat = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'cat')->sum('amount');
                    $tiger = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'tiger')->sum('amount');
                    $horse = GradyPots::where('tray_id', $find->tray_id)->where('pot_no', 'horse')->sum('amount');
        if (in_array($find->winner, ['apple', 'banana', 'grapes', 'lemon'])) {
            if ($apple == $banana && $banana == $grapes && $grapes == $lemon) {
                $arr = ["apple", "banana", "grapes", "lemon"];
                $find->winner = $arr[array_rand($arr)];
                $find->save();
            }
        } elseif (in_array($find->winner, ['vegetable', 'animals', 'lion'])) {
            $access_balance = GradySetting::first();
            $check_game_balance = $access_balance->game_balance;
            $currentMinute = date('i');
        if ($currentMinute == 18  || $currentMinute == 38 || $currentMinute == 58) {
            if ($find->win_balance > $check_game_balance) {
                $need_balance = $find->win_balance - $check_game_balance;

                if ($access_balance->second_balance > $need_balance) {
                    $access_balance->second_balance -= $need_balance;
                    $access_balance->game_balance += $need_balance;
                    $access_balance->save();
                }
            }
            }
            // Uncomment the lines below if the winner needs to be updated
            // $find->winner = 'vegetable';
            // $find->save();
        }
         

      
 
        $find->status=1;
        $find->save();
       // self::WinnerDecission($find->tray_id);
                 
      

        
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
                     $check_bid=GradyPots::where('user_id',Auth::user()->id)->where('tray_id',$get_tray_id)->count();
                        if($check_bid>7){
                             GradyPots::where('user_id', Auth::id())
                                ->where('tray_id', $get_tray_id)
                                ->update(['status' => 10]);
                        }
                return response()->json(['data' => $find->winner,'st' => true]);
                  //return response()->json(['data' => $randomSelection,'st' => true]);
                    }
                    
                }

                
            }
        
    }
     public  function getRandomValue($balance, $first, $second, $third, $fouth, $five, $six, $seven, $eight) {
        if ($first < $balance) {
            return $first;
        } elseif ($second < $balance) {
            return $second;
        } elseif ($third < $balance) {
            return $third;
        } elseif ($fouth < $balance) {
            return $fouth;
        } elseif ($five < $balance) {
            return $five;
        } elseif ($six < $balance) {
            return $six;
        } elseif ($seven < $balance) {
            return $seven;
        } else {
            return $eight;
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
            self::GameWinnerpusher($find->tray_id);
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
    public function GameWinnerpusher($tray_id){
          $setting=GradySetting::find(1);
            $take_margin = $setting->third_take_margin;
            $half_give_margin = $setting->third_helf_give_margin;
            $all_give_margin = $setting->third_full_give_margin;
         $total_pots_amount = GradyPots::where('tray_id', $tray_id)->sum('amount');
         $tray = GradyTray::where('tray_id', $tray_id)->first();
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
    }
    
    public function checkthirdbalace(){
         $setting=GradySetting::find(1);
        $take_margin = $setting->third_take_margin;
        $half_give_margin = $setting->third_helf_give_margin;
        $all_give_margin = $setting->third_full_give_margin;
        $last_three_tray = GradyTray::where('status', 1)
                   ->latest()
                   ->take(2)
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
