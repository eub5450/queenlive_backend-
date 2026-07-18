<?php

namespace App\Http\Controllers\Game;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PortalTransfer;
use App\Models\Gift;
use App\Models\BanDevice;
use App\Models\User;
use App\Models\FortuneLock;
use App\Models\Game\GameFruitsActiveUser;
use App\Models\Game\Fruits\FruitsLastWinningPot;
use App\Models\Game\Fruits\FruitsTransaction;
use App\Models\Game\Fruits\FruitsCoinServe;
use Kreait\Firebase\Contract\Database;
use Pusher;
use App\Models\CoinBegRecived;
use DB;
use Auth;
use Hash;
use Illuminate\Support\Facades\Cache;
class FruitsGameController extends Controller
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function Game(Request $request)
    {
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
        
          $id = Auth::id();
        $login_user = User::find($id);
     // if($login_user->level>1){
            if ($login_user) {
            $protal_recharge= PortalTransfer::where('user_id', $id)->sum('amount');
            $coin_beg= CoinBegRecived::where('user_id', $id)->sum('amount');
            $check_recharge =$protal_recharge+$coin_beg;
            $check_balance = $login_user->balance; // Assuming balance is a field in User model
            $check_gift = Gift::where('sander_id', $id)->sum('value');
            $total_value = $check_balance + $check_gift;
            if($check_recharge<2000000){
            $rendom=4;
            }elseif($check_recharge>5000000){
            $rendom=2;
            }else{
               $rendom=3; 
            }
            $user_profit = $check_recharge * $rendom;

            if ($user_profit < $total_value) {
                $is_another_id_lock = FortuneLock::where('imei_number', $login_user->imei_number)->first();
                 $check_id_have_already=FortuneLock::where('user_id',$login_user->id)->where('type',0)->first();
                if(!$check_id_have_already){
                $data = new FortuneLock();
                $data->user_id = $login_user->id;
                $data->type = 0;
                $data->imei_number = $login_user->imei_number;
                $data->auto_lock_active = 'Auto Lock Bcz Value Geter Then X 3';
                $data->parcentage = $is_another_id_lock ? $is_another_id_lock->parcentage : 9;
                $data->save();
            }else{
                $check_id_have_already=FortuneLock::where('user_id',$login_user->id)->where('auto_lock_active','!=',null)->first();
                if($check_id_have_already){
                    $check_id_have_already->delete();
                }
            }
            }else{
                $check_id_have_already=FortuneLock::where('user_id',$login_user->id)->where('auto_lock_active','!=',null)->first();
                if($check_id_have_already){
                    $check_id_have_already->delete();
                }
            }
             return view('game.index',compact('authtoken','authkey'));
        }
        //dd($authtoken);
       
       // return view('game.index');
    //}
    }
   
}
