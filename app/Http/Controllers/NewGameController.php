<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Battle\Fortune\FortuneTray;
use App\Models\Battle\Fortune\FortunePots;
use App\Models\Battle\Fortune\FortuneSetting;
use Pusher;
use App\Models\Setting;
use App\Models\FortuneUser;
use App\Models\FortuneLock;
use App\Models\AudienceJoin;
use App\Models\PortalTransfer;
use App\Models\Gift;
use App\Models\GameBannner;
use App\Models\FruitsGamePattan;
use Auth;
use Carbon\Carbon;
use App\Models\UserLive;
use App\Models\BanDevice;
use App\Models\PusherKey;
use DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use App\Models\GameBalanceWithdraw;
use Kreait\Firebase\Contract\Database;
use Illuminate\Support\Facades\File;
class NewGameController extends Controller
{
     public function __construct(Database $database)
    {
        $this->database = $database;
    }

    protected function resolveFruitsEntryUser(Request $request): array
    {
        $email = strtolower(trim((string) $request->query('user', '')));
        $token = trim((string) $request->query('token', ''));
        $authKey = trim((string) $request->query('authkey', ''));

        if ($email === '' && $token !== '' && strpos($token, '@') !== false) {
            $email = strtolower($token);
            $token = $authKey;
        }

        if ($email !== '') {
            $user = User::where('email', $email)->first();
            if (!$user) {
                return [null, 'user_not_found'];
            }

            if ($token === '' || !hash_equals((string) $user->password, $token)) {
                return [null, 'invalid_game_access'];
            }

            return [$user, 'query_token'];
        }

        if (Auth::check()) {
            return [Auth::user(), 'auth_session'];
        }

        $sessionUser = session('user');
        $sessionUserId = is_object($sessionUser) ? (int) ($sessionUser->id ?? 0) : (int) $request->session()->get('user.id', 0);
        if ($sessionUserId > 0) {
            $user = User::find($sessionUserId);
            if ($user) {
                return [$user, 'stored_session'];
            }
        }

        return [null, 'missing_game_access'];
    }

    protected function fruitsAccessRequiredResponse(string $reason)
    {
        $message = $reason === 'invalid_game_access'
            ? 'Invalid game access. Re-open the game from the app or main account session.'
            : 'Game access required. Re-open the game from the app or login session.';

        $html = '<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1">'
            . '<title>Game Access Required</title>'
            . '<style>html,body{margin:0;min-height:100%;background:#12051d;color:#fff;font-family:Arial,sans-serif}'
            . 'body{display:grid;place-items:center;padding:16px}.card{width:min(92vw,460px);padding:24px;border-radius:20px;'
            . 'border:1px solid rgba(255,214,100,.55);background:linear-gradient(145deg,#2b1040,#12051d);'
            . 'box-shadow:0 22px 70px rgba(0,0,0,.45);text-align:center}.title{font-size:24px;font-weight:900;color:#ffd76d}'
            . '.msg{margin-top:12px;line-height:1.45;color:#f5e9ff}.hint{margin-top:16px;font-size:12px;opacity:.72}</style>'
            . '</head><body><div class="card"><div class="title">Fruits Game</div><div class="msg">'
            . e($message)
            . '</div><div class="hint">No token or password is shown on this page.</div></div></body></html>';

        return response($html, $reason === 'invalid_game_access' ? 403 : 401)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function SignedIndex(Request $request, $user)
    {
        $user = User::find((int) $user);
        if (!$user) {
            return $this->fruitsAccessRequiredResponse('user_not_found');
        }

        Auth::login($user);
        session(['user' => $user]);

        $authtoken = strtolower(trim((string) $user->email));
        $authkey = (string) $user->password;
        $fortunesetting = FortuneSetting::select('pusher_id')->first();

        return response()
            ->view('game.new_game.index', compact('authtoken', 'authkey', 'fortunesetting'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
            ->header('Pragma', 'no-cache');
    }

    public function Index(Request $request)
    {
       //   $exitCode = Artisan::call('cache:clear');

        [$user, $accessReason] = $this->resolveFruitsEntryUser($request);
        if (!$user) {
            return $this->fruitsAccessRequiredResponse($accessReason);
        }

        $authtoken = strtolower(trim((string) $user->email));
        $authkey = (string) $user->password;
        if (!Auth::check() || (int) Auth::id() !== (int) $user->id) {
            Auth::login($user);
        }
        session(['user' => $user]);
       
        //dd($authtoken);
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
        if($date!=$user->game_balance_date ){
            $today_gift=Gift::where('sander_id',$user->id)->whereDate('date',$date)->sum('value');
            $today_amount=$user->balance-$today_gift;
            $user->game_balance_date=$date;
            $user->date_wise_balance=$today_amount;
            $user->save();
        }
        $fortunesetting = FortuneSetting::select(
    'pusher_id'
)->first();
        return view('game.new_game.index',compact('authtoken','authkey','fortunesetting'));
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

    $setting = FortuneSetting::find(1);
    if ($setting->game_status == 1 && $request->has('tray_id')) {
        $get_tray_id = $request->get('tray_id');

        try {
            DB::beginTransaction();

            $find = FortuneTray::where('status', 0)->latest('id')->first();
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
                

                $pots_setting = FortuneSetting::find(1);
                $pattarn = FruitsGamePattan::find($pots_setting->running_pattarn + 1) ?? FruitsGamePattan::first();
                $selected_pots = $pattarn->pots;

                $pots_setting->pots_name = $selected_pots;
                $pots_setting->save();
                 $recheck = FortuneTray::where('tray_id', $datas)->first();
                if (!$recheck) {
                    $gamestart = FortuneTray::create([
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
        $user=User::find(Auth::id());
        
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
        $fortunesetting = FortuneSetting::first();
        $findpots = FortunePots::where('user_id', Auth::user()->id)->where('status',0)->get();
     
        if($user){
            foreach ($findpots as $key => $value) {
                $find = FortuneTray::where('tray_id', $value->tray_id)->where('status',1)->first();
                $froud_check = FortunePots::where('tray_id', $value->tray_id)->where('user_id', Auth::user()->id)->count();
                if($froud_check!=3){
                if($find){
                if($value->pot_no == $find->winner ){
                   $win_balance=$value->amount*3;
                     $user->increment('balance', $win_balance);
                     $user->save();
                     $value->serve_balance = $win_balance;
                     $value->status = 1;
                     $value->now_user_balance = ($user->balance-$win_balance);
                     $value->save();
                     $fortunesetting->decrement('game_balance', $win_balance);
                     $fortunesetting->save();
                }
                else{
                    $value->status = 10;
                   
                    $value->save();
                }
            }
            }else{
                $value->status = 0;
                $value->save();
            }
            }

        
        }
        $user_data = User::find(Auth::user()->id);
        return response()->json(['balance' => $user_data->balance ,'st' => true]);
    
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

    if (FortuneTray::where('tray_id', $trayId)->exists()) {
        DB::transaction(function () use ($amount, $loginUser, $trayId, $boardName) {
            $existingGameStart = FortunePots::where('tray_id', $trayId)
                ->where('user_id', $loginUser->id)
                ->where('pot_no', $boardName)
                ->lockForUpdate()
                ->first();
                
                if ($loginUser->balance>=$amount) {
              $fortunesetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();      
            
             
               $options = [
                    'cluster' => $fortunesetting->cluster_pusher,
                    'useTLS'  => true,
                ];
            
                $pusher = new \Pusher\Pusher(
                    $fortunesetting->key_pusher,
                    $fortunesetting->secret_pusher,
                    $fortunesetting->app_id_pusher,
                    $options
                );
        try {  
              $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => $boardName, 'bord_amount' => $amount]);
            }
            catch (Pusher\ApiErrorException $e) {
                       // Switch to a new Pusher account
                    $switched = $this->switchPusherAccount($fortunesetting->pusher_id);
                    
                    if ($switched) {
                        // Get the updated settings with the new Pusher credentials
                        $newSetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();;
                        
                        // Create new Pusher instance with updated credentials
                        $newOptions = [
                            'cluster' => $newSetting->cluster_pusher,
                            'useTLS'  => true,
                        ];
                        
                        $newPusher = new Pusher\Pusher(
                            $newSetting->key_pusher,
                            $newSetting->secret_pusher,
                            $newSetting->app_id_pusher,
                            $newOptions
                        );
                        
                        // Retry the trigger with the new account
                        try {
                           return $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => $boardName, 'bord_amount' => $amount]);
                        } catch (\Exception $retryException) {
                            // Log the retry failure
                            
                            return false;
                        }
                    }
                
                
                
                
                     return false;
            } 
            // --- End Pusher ---
            
               
                $check_lock_list=FortuneLock::where('user_id',$loginUser->id)->where('type',0)->first();
                  if($check_lock_list){
                      $check_lock=FortuneSetting::find(1);
                      if($check_lock->block_id==0 ){
                          $check_lock->block_id=$loginUser->id;
                          $check_lock->lock_parcent=$check_lock_list->parcentage;
                          $check_lock->save();
                      }
                  }
               
            if ($existingGameStart) {
                $existingGameStart->amount += $amount;
                $existingGameStart->win_balance = $existingGameStart->amount * 3;
                $existingGameStart->now_user_balance = $loginUser->balance - $amount;
                $existingGameStart->save();
                $loginUser->decrement('balance', $amount);
                $fortunesetting = FortuneSetting::first();
                $fortunesetting->increment('game_balance', $amount);
                
           
            } else {
                FortunePots::create([
                    'tray_id' => $trayId,
                    'user_id' => $loginUser->id,
                    'amount' => $amount,
                    'win_balance' => $amount * 3,
                    'pot_no' => $boardName,
                    'now_user_balance' => $loginUser->balance - $amount,
                    'status' => 0
                ]);
                $loginUser->decrement('balance', $amount);
                $fortunesetting = FortuneSetting::first();
                $fortunesetting->increment('game_balance', $amount);
                
               
               
            }
            
        }
        });
    }
    
    }

    public function BettingInsert()
    {
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
            $find = FortuneTray::where('tray_id',$get_tray_id)->where('result_status',0)->first();
          
            if($find){ 
                 if($find->tray_id == $get_tray_id ){
                    if ($find->result_status==0) {
                       
                        $tray_update=FortuneTray::where('tray_id',$find->tray_id)->first();
                        $apple = FortunePots::where('tray_id', $find->tray_id)->where('pot_no', 'apple')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                        $watermelon = FortunePots::where('tray_id', $find->tray_id)->where('pot_no', 'watermelon')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                        $lemon = FortunePots::where('tray_id', $find->tray_id)->where('pot_no', 'saven_win')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                        $tray_update->apple_serve=$apple;
                        $tray_update->watermalon_serve=$watermelon;
                        $tray_update->lemon_serve=$lemon;
                        $tray_update->save();
                        $traybalance = FortunePots::where('tray_id', $find->tray_id)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                       $fortunesetting = FortuneSetting::first();
                        $currentMinute = date('i'); // Get the current minute
                        $minuteCheck = ($currentMinute % 3 != 0); // Matches pattern 1,2,4,5,7,8,10,11, etc.

                        if ($minuteCheck) {
                        if ($traybalance>$fortunesetting->tray_margin) {
                           $percentage = 0.09 * $traybalance; // 3%
                           $fortunesetting->game_balance-=$percentage;
                           $fortunesetting->second_balance+=$percentage;
                           $fortunesetting->save();
                         }
                        }else{
                            $serve_parcent=$fortunesetting->second_balance/100*95;
                             if($serve_parcent<=$fortunesetting->second_balance){
                               $fortunesetting->game_balance+=$serve_parcent;
                               $fortunesetting->second_balance-=$serve_parcent;
                               $fortunesetting->save();
                             }
                         }
                         
                        $withdraw_percentage = $fortunesetting->fruits_game_withdraw_parcentage/100*$traybalance; 
                         
                           $profit_withdraw=GameBalanceWithdraw::find(1);
                            $profit_withdraw->amount+=$withdraw_percentage;
                            if($profit_withdraw->save()){
                                $fortunesetting->game_balance-=$withdraw_percentage;
                                $fortunesetting->save();
                            }
                     // 
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
                $tray = FortuneTray::where('tray_id',$get_tray_id)->where('status',0)->first();
              
                if($tray){
                     if($tray->tray_id == $get_tray_id){
                          self::WinnerDecission($tray->tray_id);
                         $tray->status = 1;
                         
                         $fortunesetting = FortuneSetting::first(); 
                        $patarn_check_is_update = FortuneTray::where('tray_id', $get_tray_id)
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
                $tray = FortuneTray::where('tray_id',$get_tray_id)->first();
                return response()->json(['data' => $tray->winner,'st' => true]);
            }
    }
    public function WinnerDecission($tray_id)
    {
        $result = FortuneTray::where('tray_id',$tray_id)->where('status',0)->first();
        if ($result) {
               $fortunesetting = FortuneSetting::first();             
                    $select_pots= FortunePots::where('tray_id', $result->tray_id)->where('pot_no',$fortunesetting->pots_name)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                        $apple = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'apple')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                    $watermelon = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'watermelon')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
                    $lemon = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'saven_win')->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');

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
                    $traybalance = FortunePots::where('tray_id', $result->tray_id)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');
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
                    $seconrandomPercentage = mt_rand(2, 10) / 100; 
                    $secendtenPercent = $game_balance * $seconrandomPercentage;
                    $secondnow_game_balance_after_percent=$game_balance-$secendtenPercent;
                         if ($first < $secondnow_game_balance_after_percent) {
                        $randomValue = $first;
                        } elseif ($second < $secondnow_game_balance_after_percent) {
                            $randomValue =$second ;
                        } else {
                            $randomValue = $third;
                        }
                         $pattarn='Pattern Change % :'.$seconrandomPercentage;
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
                    $fortunesetting = FortuneSetting::first();
                     if($fortunesetting->block_id!=0 )
                    {
                        $lastThreeRecords = FortuneTray::where('result_status', 1)
                            ->where('status', 1)
                            ->orderByDesc('id')
                            ->limit(rand(1,4))
                            ->pluck('block_id')
                            ->toArray();
                        $it_repet = (count(array_unique($lastThreeRecords)) === 1 && reset($lastThreeRecords) === $fortunesetting->block_id) ? 0 : 1;
                        if ($it_repet== 1) {
                            
                              $block_apple = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'apple')->where('user_id',$fortunesetting->block_id)->sum('amount');
                                $block_watermelon = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'watermelon')->where('user_id',$fortunesetting->block_id)->sum('amount');
                                $block_lemon = FortunePots::where('tray_id', $result->tray_id)->where('pot_no', 'saven_win')->where('user_id',$fortunesetting->block_id)->sum('amount');
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
                                         $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;

                                    }elseif ($block_watermelon==$block_tray_balance) {
                                        $arr = ["saven_win", "apple"];
                                        $randomPot = $arr[array_rand($arr)];
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($block_lemon==$block_tray_balance) {
                                        $arr = ["watermelon", "apple"];
                                        $randomPot = $arr[array_rand($arr)];
                                        $result->winner=$randomPot;
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($first_block_two_pots==$block_tray_balance) {
                                       
                                        $result->winner='saven_win';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }elseif ($sec_block_two_pots==$block_tray_balance) {
                                       
                                        $result->winner='watermelon';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }else {
                                       
                                        $result->winner='apple';
                                        $result->block_id=$fortunesetting->block_id;
                                        $result->randomPercentage='Pattarn Change For Block ID ' .$fortunesetting->block_id;
                                    }
                                }
                            }
                        }
                    
                           
                     if ($fortunesetting->winner_id != 0 && $game_balance > 0) {
                            $lastThreeRecords = FortuneTray::where('result_status', 1)
                                ->where('status', 1)
                                ->orderByDesc('id')
                                ->limit(rand(1,3))
                                ->pluck('winner_id')
                                ->toArray();

                            $it_winner_repet = (count(array_unique($lastThreeRecords)) === 1 && reset($lastThreeRecords) === $fortunesetting->winner_id) ? 0 : 1;

                            if ($it_winner_repet == 1) {
                                $winner_pots = FortunePots::where('tray_id', $result->tray_id)
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
                               $need_balace_for_wi = ($fortunesetting->game_minus_status == 1) ? 0 : $winner_tray_balance * 3;

                                
                                if ($winner_tray_balance > $check_winner_percentage && $need_balace_for_wi <= $game_balance) {
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
                                }else{
                                    $result->randomPercentage = 'Pattern Change For Adjust Winner ID ' . $fortunesetting->winner_id;
                                }
                            }
                        }

                            $last_record_for_repet_check = FortuneTray::latest()->take(rand(9,11))->pluck('winner')->toArray();
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
                         $re_result = FortuneTray::where('tray_id',$tray_id)->where('status',0)->first();
                         if ($re_result) {
                            $result->save();
                         }

        } //main coinditions    
    }
  public function WinIDLock(Request $request){
        $trayId = $request->tray_id;

        $winner_list_data = FortuneLock::where('type', 1)
            ->select('id', 'parcentage', 'user_id','count')
            ->get();

        $check_lock_winner = FortuneSetting::find(1);
        
        if ($winner_list_data->isNotEmpty() && $check_lock_winner && $check_lock_winner->winner_id == 0 && $check_lock_winner->presser_lock == 0) {

            $userIds = $winner_list_data->pluck('user_id');

            $bet_pots_user_ids = FortunePots::where('tray_id', $trayId)
                ->whereIn('user_id', $userIds)
                ->pluck('user_id')
                ->unique()
                ->values();

            if ($bet_pots_user_ids->isNotEmpty()) {
                $matched_winners = $winner_list_data->whereIn('user_id', $bet_pots_user_ids);

                // Sort by lowest parcentage
                $sorted_winners = $matched_winners->sortBy('parcentage');

                // Total tray balance excluding specific users
                $traybalance = FortunePots::where('tray_id', $trayId)
                    ->whereNotIn('user_id', [23825, 23826, 23827])
                    ->sum('amount');
                      
                // Loop through each sorted winner to find the valid one
                foreach ($sorted_winners as $winner_data) {
                    $lowestUserId = $winner_data->user_id;

                    $winner_pots = FortunePots::where('tray_id', $trayId)
                        ->whereIn('pot_no', ['apple', 'watermelon', 'saven_win'])
                        ->where('user_id', $lowestUserId)
                        ->get()
                        ->groupBy('pot_no')
                        ->map(function ($pot) {
                            return $pot->sum('amount');
                        });
                    $winner_tray_balance = $winner_pots->sum();
                    $check_winner_percentage = $traybalance / 100 * $winner_data->parcentage;
                    
                    if ($winner_tray_balance > $check_winner_percentage) {
                        $check_lock_winner->winner_id = $lowestUserId;
                        $check_lock_winner->lock_parcent = $winner_data->parcentage;
                        $check_lock_winner->save();
                   

                        break; // Stop after finding the first eligible winner
                    }
                }
            }
        }
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
    public function LastGameWinner(Request $request)
    {
    //   $exitCode = Artisan::call('cache:clear');
    //   $exitCode = Artisan::call('cache:clear');
    //   $exitCode = Artisan::call('cache:clear');
    //   $exitCode = Artisan::call('cache:clear');
    //   $exitCode = Artisan::call('cache:clear');
    //   $exitCode = Artisan::call('cache:clear');
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
         $data = FortuneTray::where('status','!=',0)->limit(14)->orderby('id','desc')->get();
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
        // $exitCode = Artisan::call('cache:clear');
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
        $topone = $request->topone;
        $toptwo =$request->toptwo;
        $topthree = $request->topthree;
        $topfour = $request->topfour;
        $first_final_win = 0;
        $second_final_win = 0;
        $third_final_win = 0;
        $fourth_final_win = 0;
        $top_one_bet = 0;
        $top_two_bet = 0;
        $top_three_bet = 0;
        $top_four_bet = 0;
        $latestFortuneTray = FortuneTray::latest()->first();
        $find = FortuneTray::where('id', '<', $latestFortuneTray->id)->latest()->first();

        //start 1st topper
        $get_first_winner_serve = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topone)->sum('amount');
        $get_first_winner_win_check = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topone)->where('pot_no', $find->winner)->first();
         if($get_first_winner_serve){
            $top_one_bet=1;
        }
        if($get_first_winner_win_check){
        $get_first_winner_win = $get_first_winner_win_check->amount*3;
        $first_final_win=$get_first_winner_win-$get_first_winner_serve;
        }else{
        $get_first_winner_win = 0;
        $first_final_win=$get_first_winner_win-$get_first_winner_serve;
        }
        //end 1st topper
        //start 2nd topper

        $get_second_winner_serve = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$toptwo)->sum('amount');
        $get_second_winner_win_check = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$toptwo)->where('pot_no', $find->winner)->first();
        if($get_second_winner_serve){
            $top_two_bet=1;
        }
        if($get_second_winner_win_check){
        $get_second_winner_win = $get_second_winner_win_check->amount*3;
        $second_final_win=$get_second_winner_win-$get_second_winner_serve;
        }else{
        $get_second_winner_win = 0;
        $second_final_win=$get_second_winner_win-$get_second_winner_serve;
        }
        //end second topper

        //start 3rd topper

        $get_third_winner_serve = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topthree)->sum('amount');
        $get_third_winner_win_check = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topthree)->where('pot_no', $find->winner)->first();
          if($get_third_winner_serve){
            $top_three_bet=1;
        }
        if($get_third_winner_win_check){
        $get_third_winner_win = $get_third_winner_win_check->amount*3;
        $third_final_win=$get_third_winner_win-$get_third_winner_serve;
        }else{
        $get_third_winner_win = 0;
        $third_final_win=$get_third_winner_win-$get_third_winner_serve;
        }
        //end 3rd topper

        //start 4th topper

        $get_fourth_winner_serve = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topfour)->sum('amount');
        $get_fourth_winner_win_check = FortunePots::where('tray_id', $find->tray_id)->where('user_id',$topfour)->where('pot_no', $find->winner)->first();
         if($get_fourth_winner_serve){
            $top_four_bet=1;
        }
        if($get_fourth_winner_win_check){
        $get_fourth_winner_win = $get_fourth_winner_win_check->amount*3;
        $fourth_final_win=$get_fourth_winner_win-$get_fourth_winner_serve;
        }else{
        $get_fourth_winner_win = 0;
        $fourth_final_win=$get_fourth_winner_win-$get_fourth_winner_serve;
        }
        //end 3rd topper

        self::GameWinnerpusher();
        return response()->json([
            'top_one_bet' => $top_one_bet,
            'top_two_bet' => $top_two_bet,
            'top_three_bet' => $top_three_bet,
            'top_four_bet' => $top_four_bet,
            'topone' => $first_final_win,
            'toptwo' => $second_final_win,
            'topthree' => $third_final_win,
            'topfour' => $fourth_final_win,
        ]);

    }
     public function GameWinnerpusher()
    {
        $rk_game_soket=array();
         $fortunesetting = FortuneSetting::first();
         if($fortunesetting->presser_lock==0){
              $fortunesetting->block_id=0;
              $fortunesetting->winner_id=0;
              $fortunesetting->lock_parcent =0;
             $fortunesetting->save();
         }
       
        $users_1st_name = 0; 
        $users_1st_amount_bet = 0;

        $users_2nd_name = 0; 
        $users_2nd_amount_bet = 0;

        $users_3rd_name = 0; 
        $users_3rd_amount_bet = 0;

        $latestFortuneTray = FortuneTray::latest()->first();
        if ($latestFortuneTray->push == 0) {
            $find = FortuneTray::where('id', '<', $latestFortuneTray->id)->latest()->first();
           // self::WorkSecondBalanceUp($find->tray_id);
            $getdetails = FortunePots::where('tray_id', $find->tray_id)
                ->where('pot_no', $find->winner)
                ->orderBy('amount', 'DESC')
                ->get();
        
            $winners = [];
            foreach ($getdetails as $key => $value) {
                if ($value->user_id != '22222' ) {
                    $total = FortunePots::where('tray_id', $find->tray_id)
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
                            $data->game = 'Fruits';
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
             $setting=FortuneSetting::find(1);
            $take_margin = $setting->third_take_margin;
            $half_give_margin = $setting->third_helf_give_margin;
            $all_give_margin = $setting->third_full_give_margin;
            $total_pots_amount = FortunePots::where('tray_id', $find->tray_id)->whereNotIn('user_id', [23825, 23826, 23827])->sum('amount');

            $tray = FortuneTray::where('tray_id', $find->tray_id)->first();
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
         $setting=FortuneSetting::find(1);
        $take_margin = $setting->third_take_margin;
        $half_give_margin = $setting->third_helf_give_margin;
        $all_give_margin = $setting->third_full_give_margin;
        $last_three_tray = FortuneTray::where('status', 1)
                   ->latest()
                   ->take(2)
                   ->get();

        if ($last_three_tray->pluck('third_balance_calculation')->unique()->count() === 1) {
        $third_balance_calculation = $last_three_tray->first()->third_balance_calculation;
    
        switch ($third_balance_calculation) {
            case 1:
                if ($setting->third_balance_status == 0) {
                    $balance_take_amount = $setting->game_balance * ($setting->third_take_parcentage / 100);
                    $setting->game_balance -= $balance_take_amount;
                    $setting->third_balance += $balance_take_amount;
                    $setting->third_balance_status = 1;
                }
                break;
    
            case 2:
                if ($setting->third_balance_status == 1) {
                    $balance_take_amount = $setting->third_balance * ($setting->third_helf_given_parcentage / 100);
                    $setting->game_balance += $balance_take_amount;
                    $setting->third_balance -= $balance_take_amount;
                    $setting->third_balance_status = 0;
                }
                break;
    
            case 3:
                if ($setting->third_balance_status == 1) {
                    $balance_take_amount = $setting->third_balance * ($setting->third_full_given_parcentage / 100);
                    $setting->game_balance += $balance_take_amount;
                    $setting->third_balance -= $balance_take_amount;
                    $setting->third_balance_status = 0;
                }
                break;
        }
    
        $setting->save();
    }

    }
    
     public function check_winner_lock_id($tray_id){
        $tray = FortuneTray::where('tray_id', $tray_id)->first();
        $pot_users =FortunePots::where('tray_id', $tray->tray_id) ->groupBy('user_id')->select('user_id')->distinct()->get();
        foreach ($pot_users as $key => $pot_user) {
            $user=User::find($pot_user->user_id);
             if($user->auto_lock_status==0){
            $today_sanding=Gift::where('sander_id',$user->id)->whereDate('date',date('Y-m-d'))->sum('value');
            $total_balance=$today_sanding+$user->balance;
            $today_balance=$user->date_wise_balance;
            if ($total_balance < 0.7 * $today_balance) {
             $check_id_have_already=FortuneLock::where('user_id',$user->id)->first();
             if(!$check_id_have_already){
            $data = new FortuneLock();
            $data->user_id = $user->id;
            $data->imei_number = $user->imei_number;
            $data->auto_lock_active = 'auto win';
            $data->parcentage = 10; // Note: The correct spelling is 'percentage'
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
        $old_data = FortuneUser::where('user_id', Auth::user()->id)
        ->where('tray_id', $request->tray_id)
        ->first();

        if (!$old_data) {
            if(Auth::id()!='22222'){
            $data = new FortuneUser;
            $data->user_id = Auth::user()->id;
            $data->tray_id = $request->tray_id;
            $data->save();
            }
            
            FortuneUser::where('user_id', Auth::user()->id)
                ->where('tray_id','!=', $request->tray_id)
                ->delete();
           
        } else {
            

            $all_users = FortuneUser::where('tray_id','!=', $request->tray_id)->get();
            foreach ($all_users as $key => $all_user) {
                $all_user->delete();
            }
        }
        $top_four = DB::table('fortune_users')
            ->join('users', 'users.id', 'fortune_users.user_id')
            ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile','users.id')
            ->orderBy('users.balance', 'desc')
            ->get();


       

        return response()->json(['data' => $top_four]);

    }
    public function AllActiveUser()
    {
        $data= DB::table('fortune_users')
    ->join('users', 'users.id', 'fortune_users.user_id')
    ->select(DB::raw('SUBSTRING(users.name, 1, 3) AS name'), 'users.profile')
    ->orderBy('users.balance', 'desc')
    ->get();
       return response()->json(['data' => $data]);
    }
   public function Robot(Request $request)
    {

    
    $email = $request->authtoken;
    $pass = $request->authkey;
    $trayId = $request->tray_id;
   
   $setting=FortuneSetting::find(1);
  // self::BIDReturn($email,$pass);
    if ($setting->robot_on==1) {
        
   $duplicateTrays = FortuneTray::where('tray_id', $request->tray_id)->orderBy('id')->get();
                if ($duplicateTrays->count() > 1) {
                    $duplicateTrays->shift(); // Keep the first entry
                    foreach ($duplicateTrays as $duplicateTray) {
                        $duplicateTray->delete();
                    }
                }
    $loginUser_one = User::find(23825);
    $loginUser_two = User::find(23826);
    $loginUser_three = User::find(23827);

    
   $now_time= (int) Carbon::now()->format('H');


     if ($now_time >= 14 && $now_time <= 23) {
         $numbers = [206300,115000,311500,278500,259000,146000,285500];
     }else{
         $numbers = [98500,115000,101500,88500,56000];
     }
            
            $randomNumber = Arr::random($numbers);
            $amount = $randomNumber;
            $count_user=FortuneUser::count();
        if (FortuneTray::where('tray_id', $trayId)->exists()) {
            
            DB::transaction(function () use ($amount, $loginUser_one, $trayId) {
                $existingGameStart = FortunePots::where('tray_id', $trayId)
                    ->where('user_id', $loginUser_one->id)
                    ->where('pot_no', 'apple')
                    ->lockForUpdate()
                    ->first();
                    
                   
                    $fortunesetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();      
            
             
                       $options = [
                            'cluster' => $fortunesetting->cluster_pusher,
                            'useTLS'  => true,
                        ];
                    
                        $pusher = new \Pusher\Pusher(
                            $fortunesetting->key_pusher,
                            $fortunesetting->secret_pusher,
                            $fortunesetting->app_id_pusher,
                            $options
                        );
                   
                    
                if (!$existingGameStart) {
                   
               if($amount)
                  {
                       try {
                  $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => 'apple', 'bord_amount' =>$amount]); 
                       }catch (Pusher\ApiErrorException $e) {
                       // error_log("Pusher API error: " . $e->getMessage());
                    } catch (Exception $e) {
                     //   error_log("General error: " . $e->getMessage());
                    }
                }
               $existingGameStart = FortunePots::where('tray_id', $trayId)
                    ->where('user_id', $loginUser_one->id)
                    ->where('pot_no', 'apple')
                    ->lockForUpdate()
                    ->first();
                    $gameStart = new FortunePots();
                    $gameStart->tray_id = $trayId;
                    $gameStart->user_id = $loginUser_one->id;
                    $gameStart->amount = $amount;
                    $gameStart->win_balance = $amount * 3;
                    $gameStart->pot_no = 'apple';
                    $gameStart->now_user_balance = $loginUser_one->balance - $amount;
                    $gameStart->status = 10;
                    if(!$existingGameStart){
                    $gameStart->save();
                    $currentMinute = date('i');
                    $robot_one=User::find($loginUser_one->id);

                    if (($currentMinute >= 1 && $currentMinute <= 28)) 
                     {
                        $robot_one->name='Shovo';
                        $robot_one->profile='store/profile/default.png';
                        $robot_one->save();
                    }elseif (($currentMinute >= 29 && $currentMinute <= 38)) {
                       $robot_one->name='Sinha';
                        $robot_one->profile='store/profile/default.png';
                        $robot_one->save();
                    }
                    elseif (($currentMinute >= 39 && $currentMinute <= 47)) {
                       $robot_one->name='Saifa';
                        $robot_one->profile='store/profile/default.png';
                        $robot_one->save();
                    }
                    elseif (($currentMinute >= 48 && $currentMinute <= 55)) {
                        $robot_one->name='Raza';
                        $robot_one->profile='store/profile/default.png';
                        $robot_one->save();
                    }elseif (($currentMinute >= 56 && $currentMinute <= 60)) {
                         $robot_one->name='Hamza';
                        $robot_one->profile='store/profile/default.png';
                        $robot_one->save();
                    }
                    $data = new FortuneUser;
                    $data->user_id = $loginUser_one->id;
                    $data->tray_id = $trayId;
                    $data->save();
                    FortuneUser::where('user_id', $loginUser_one->id)
                    ->where('tray_id','!=', $trayId)
                    ->delete();
                }
                } 
                
            });
        } 
        if (FortuneTray::where('tray_id', $trayId)->exists()) {
           
            DB::transaction(function () use ($amount, $loginUser_two, $trayId) {
                $existingGameStart = FortunePots::where('tray_id', $trayId)
                    ->where('user_id', $loginUser_two->id)
                    ->where('pot_no', 'saven_win')
                    ->lockForUpdate()
                    ->first();
                   $fortunesetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();      
            
             
               $options = [
                    'cluster' => $fortunesetting->cluster_pusher,
                    'useTLS'  => true,
                ];
            
                $pusher = new \Pusher\Pusher(
                    $fortunesetting->key_pusher,
                    $fortunesetting->secret_pusher,
                    $fortunesetting->app_id_pusher,
                    $options
                );
                if (!$existingGameStart) {
                   if($amount)
                      {
                          try{
                            $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => 'saven_win', 'bord_amount' =>$amount]); 
                          }catch (Pusher\ApiErrorException $e) {
                       // error_log("Pusher API error: " . $e->getMessage());
                    } catch (Exception $e) {
                     //   error_log("General error: " . $e->getMessage());
                    }
                      }
                   $existingGameStart = FortunePots::where('tray_id', $trayId)
                        ->where('user_id', $loginUser_two->id)
                        ->where('pot_no', 'saven_win')
                        ->lockForUpdate()
                        ->first();
                        $gameStart = new FortunePots();
                        $gameStart->tray_id = $trayId;
                        $gameStart->user_id = $loginUser_two->id;
                        $gameStart->amount = $amount;
                        $gameStart->win_balance = $amount * 3;
                        $gameStart->pot_no = 'saven_win';
                        $gameStart->now_user_balance = $loginUser_two->balance - $amount;
                        $gameStart->status = 10;
                        if(!$existingGameStart){
                        $gameStart->save();
                        $currentMinute = date('i');
                        $robot_two_name=User::find($loginUser_two->id);
                        if (($currentMinute >= 1 && $currentMinute <= 20)) 
                        {
                            $robot_two_name->name='Tamal Mondal';
                            $robot_two_name->profile='store/profile/default.png';
                            $robot_two_name->save();
                        }elseif (($currentMinute >= 21 && $currentMinute <= 33)) {
                           $robot_two_name->name='Cool Down';
                           $robot_two_name->level=22;
                            $robot_two_name->profile='store/profile/default.png';
                            $robot_two_name->save();
                        }
                        elseif (($currentMinute >= 34 && $currentMinute <= 42)) {
                           $robot_two_name->name='Robel';
                           $robot_two_name->level=13;
                            $robot_two_name->profile='store/profile/default.png';
                            $robot_two_name->save();
                        }
                        elseif (($currentMinute >= 43 && $currentMinute <= 55)) {
                           
                           $robot_two_name->name='সবুজ';
                           $robot_two_name->level=15;
                            $robot_two_name->profile='store/profile/default.png';
                            $robot_two_name->save();
                        }elseif (($currentMinute >= 56 && $currentMinute <= 60)) {
                             $robot_two_name->name='𝑺𝑴:𝑺𝑼𝑴𝑶𝑵';
                             $robot_two_name->level=17;
                            $robot_two_name->profile='store/profile/default.png';
                            $robot_two_name->save();
                        } 
                         FortuneUser::where('user_id', $loginUser_two->id)
                        ->where('tray_id','!=', $trayId)
                        ->delete();
                         $data = new FortuneUser;
                        $data->user_id = $loginUser_two->id;
                        $data->tray_id = $trayId;
                        $data->save();
                    }
                }
            });
        }
         if (FortuneTray::where('tray_id', $trayId)->exists()) {
           
            DB::transaction(function () use ($amount, $loginUser_three, $trayId) {
                $existingGameStart = FortunePots::where('tray_id', $trayId)
                    ->where('user_id', $loginUser_three->id)
                    ->where('pot_no', 'watermelon')
                    ->lockForUpdate()
                    ->first();                   
               
                    
                if (!$existingGameStart) {
                   $fortunesetting = FortuneSetting::select('app_id_pusher','key_pusher','secret_pusher','cluster_pusher','pusher_id')->first();      
            
             
               $options = [
                    'cluster' => $fortunesetting->cluster_pusher,
                    'useTLS'  => true,
                ];
            
                $pusher = new \Pusher\Pusher(
                    $fortunesetting->key_pusher,
                    $fortunesetting->secret_pusher,
                    $fortunesetting->app_id_pusher,
                    $options
                );
                 if($amount)
                  {
                      try{

                  $pusher->trigger('users_amount_name', 'users_amount_event', ['bord_name' => 'watermelon', 'bord_amount' =>$amount]); 
                      }catch (Pusher\ApiErrorException $e) {
                       // error_log("Pusher API error: " . $e->getMessage());
                    } catch (Exception $e) {
                     //   error_log("General error: " . $e->getMessage());
                    }
                  }
              $existingGameStart = FortunePots::where('tray_id', $trayId)
                    ->where('user_id', $loginUser_three->id)
                    ->where('pot_no', 'watermelon')
                    ->lockForUpdate()
                    ->first();
                    $gameStart = new FortunePots();
                    $gameStart->tray_id = $trayId;
                    $gameStart->user_id = $loginUser_three->id;
                    $gameStart->amount = $amount;
                    $gameStart->win_balance = $amount * 3;
                    $gameStart->pot_no = 'watermelon';
                    $gameStart->now_user_balance = $loginUser_three->balance - $amount;
                    $gameStart->status = 10;
                    if(!$existingGameStart){
                    $gameStart->save();
                    $currentMinute = date('i');
                    $robot_three=User::find($loginUser_three->id);
                    if (($currentMinute >= 1 && $currentMinute <= 20)) 
                    {
                        $robot_three->name='Akash';
                        $robot_three->profile='store/profile/default.png';
                        $robot_three->save();
                    }elseif (($currentMinute >= 21 && $currentMinute <= 33)) {
                       $robot_three->name='Maria Khatun';
                        $robot_three->profile='store/profile/default.png';
                        $robot_three->save();
                    }
                    elseif (($currentMinute >= 34 && $currentMinute <= 42)) {
                       $robot_three->name='Mayer';
                        $robot_three->profile='store/profile/default.png';
                        $robot_three->save();
                    }
                    elseif (($currentMinute >= 43 && $currentMinute <= 55)) {
                       
                       $robot_three->name='sobuj';
                        $robot_three->profile='store/profile/default.png';
                        $robot_three->save();
                    }elseif (($currentMinute >= 56 && $currentMinute <= 60)) {
                         $robot_three->name='Adnan';
                        $robot_three->profile='store/profile/default.png';
                        $robot_three->save();
                    } 
                     FortuneUser::where('user_id', $loginUser_three->id)
                    ->where('tray_id','!=', $trayId)
                    ->delete();  
                    $data = new FortuneUser;
                    $data->user_id = $loginUser_three->id;
                    $data->tray_id = $trayId;
                    $data->save();
                                     
                 }
               
                } 
                
            });
        } 
    }
    }
    public function BIDReturn($email ,$pass)
{
  
    $user = User::where('email', $email)->first();

    if (!$user || $pass != $user->password) {
        return false;
    }

    Auth::login($user);  
     $user = User::find(Auth::user()->id);
        $fortunesetting = FortuneSetting::first();
        $findpots = FortunePots::where('user_id', Auth::user()->id)->where('status',1)->get();
        if($user){
            foreach ($findpots as $key => $value) {
                $find = FortuneTray::where('tray_id', $value->tray_id)->where('status',1)->first();

                if($find){
                if($value->pot_no == $find->winner ){
                   $win_balance=$value->win_balance;
                   $serve_balance=$value->serve_balance;
                     if ($win_balance>$serve_balance) {
                         
                      $difference = $win_balance - $serve_balance;
                      $value->serve_balance += $difference;
                      $value->balance_give = $difference;
                      $value->total_serve = $value->serve_balance-$difference;
                     $value->save();
                    // info('Loss Balance: ' . $value->user_id .' differecnce ' .$difference .' tray_id '.$find->tray_id);
                     $user->increment('balance', $difference);
                     $user->save();
                      $fortunesetting->decrement('game_balance', $difference);
                     $fortunesetting->save();
                     }
                }
            }
          
            }

        
        }
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
        
       $firust_game=FortunePots::orderby('id','desc')->where('user_id',Auth::id())->orderby('id','desc')->limit(18)->get();
       return response()->json(['data' => $firust_game]);
    }
    
    private function switchPusherAccount($pusher_id)
{
    return Cache::lock('pusher-switch-global-lock', 5)->block(3, function () use ($pusher_id) {
        $current = PusherKey::find($pusher_id);

        if ($current && $current->pusher_active_time) {
            $activeDuration = now()->diffInMinutes($current->pusher_active_time);
            if ($activeDuration < 5) {
                return false;
            }
        }

        try {
            DB::transaction(function () use ($current) {
                if ($current) {
                    $current->update([
                        'pusher_status'        => 2,
                        'pusher_deactive_time' => now(),
                    ]);
                }

                $nextPusher = PusherKey::where('used',1)->where('pusher_status', 0)
                    ->when($current, function ($query) use ($current) {
                        return $query->where('id', '>', $current->id);
                    })
                    ->orderBy('id', 'asc')
                    ->first();

                if (!$nextPusher) {
                    $nextPusher = PusherKey::where('used',1)->where('pusher_status', 0)
                        ->orderBy('id', 'asc')
                        ->first();
                }

                if (!$nextPusher) {
                    throw new \Exception('No available Pusher accounts found');
                }

                FortuneSetting::where('id', 1)->update([
                    'pusher_id'        => $nextPusher->id,
                    'key_pusher'       => $nextPusher->pusher_key,
                    'secret_pusher'    => $nextPusher->pusher_secret,
                    'app_id_pusher'    => $nextPusher->pusher_app_id,
                    'cluster_pusher'   => $nextPusher->pusher_cluster,
                ]);

                $nextPusher->update([
                    'pusher_active_time' => now(),
                    'pusher_status'      => 1,
                ]);
            });
            
            return true;
        } catch (\Exception $e) {
           info('Fruits Game Swiching Problem ' . $e->getMessage());
            return false;
        }
    });
}

   
    
}
