<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Battle\Fortune\FortuneTray;
use App\Models\Battle\Fortune\FortunePots;
use App\Models\Battle\Fortune\FortuneSetting;
use App\Models\Game\Fivestar\FivestarTray;
use App\Models\Game\Fivestar\FivestarPots;
use App\Models\Game\Fivestar\FivestarSetting;
use App\Models\FruitsGamePattan;
use App\Models\GreedyPattern;
use App\Models\FortuneLock;
use App\Models\User;
use App\Models\Game\Grady\GradyTray;
use App\Models\Game\Grady\GradyPots;
use App\Models\Game\Grady\GradySetting;
use App\Models\Battle\TeenPattiTray;
use App\Models\Battle\TeenPattiPots;
use App\Models\Battle\TeenPattiSetting;
class GameController extends Controller

{
    public function FruitsControl()
    {
    	$data['balance']=FortuneSetting::find(1);
    	$data['game_serve_details']=FortuneTray::orderby('id','desc')->limit(100)->get();
    	$data['game_serve_users_details']=FortunePots::orderby('id','desc')->whereNotIn('user_id', [23825, 23826, 23827])->limit(50)->get();
    	return view('backend.game.fruits')->with($data);
    }
    public function LockManage()
    {
    	$data['fruits_lock_lists']=FortuneLock::where('type',0)->orderby('id','desc')->get();
    	$data['fruits_win_lists']=FortuneLock::where('type',1)->orderby('id','desc')->get();
    	$data['lock_off_ids']=User::where('auto_lock_status',1)->orderby('id','desc')->get();
    	return view('backend.game.fruits_lock_list')->with($data);
    }
    public function LockListStore(Request $request)
    {
        $user=User::find($request->block_id);
        if($user){
        $data=new FortuneLock;
        $data->user_id=$request->block_id;
        $data->type=$request->type;
        $data->parcentage=$request->parcentage;
        $data->imei_number=$user->imei_number;
        $data->save();
         $notification=array(
                'messege'=>'Game Lock ID List Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
            $notification=array(
                'messege'=>'Wrong Id Try To Submit',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification); 
        }
    } 
    public function LockOffStore(Request $request)
    {
        $user=User::find($request->block_id);
        if($user){
        $user->auto_lock_status=1;
        $user->save();
         $notification=array(
                'messege'=>'Game Lock Off List Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
            $notification=array(
                'messege'=>'Wrong Id Try To Submit',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification); 
        }
    }
    public function GreedySetting(Request $request)
    {
    
     $request->validate([
            'tray_margin' => 'required|numeric',
            'take_parcenage' => 'required',
            'bid_brack' => 'required|numeric',
            'third_full_given_parcentage' => 'required',
            'third_helf_given_parcentage' => 'required',
            'third_take_parcentage' => 'required',
            'third_full_give_margin' => 'required',
            'third_take_margin' => 'required',
           
        ]);
        $data=GradySetting::find(1);
        $data->tray_margin=$request->tray_margin;
        $data->take_parcenage=$request->take_parcenage;
        $data->bid_brack=$request->bid_brack;
        $data->third_full_give_margin=$request->third_full_give_margin;
        $data->third_take_parcentage=$request->third_take_parcentage;
        $data->third_helf_given_parcentage=$request->third_helf_given_parcentage;
        $data->third_full_given_parcentage=$request->third_full_given_parcentage;
        $data->third_take_margin=$request->third_take_margin;
        $data->third_helf_give_margin=$request->third_helf_give_margin;
        $data->save();
         $notification=array(
                'messege'=>'Greedy Setting Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);

    }
    public function FruitsThirdBalanceSetting(Request $request)
    {
        // Validate the request data
        $request->validate([
            'third_take_margin' => 'required|numeric',
            'third_helf_give_margin' => 'required|numeric',
            'third_full_give_margin' => 'required|numeric',
            'third_take_parcentage' => 'required|numeric',
            'third_helf_given_parcentage' => 'required|numeric',
            'third_full_given_parcentage' => 'required|numeric',
            'fruits_game_withdraw_parcentage' => 'required',
        ]);

        // Find the balance record by ID
        $balance = FortuneSetting::find(1);

        // Update the balance fields
        $balance->third_take_margin = $request->input('third_take_margin');
        $balance->third_helf_give_margin = $request->input('third_helf_give_margin');
        $balance->third_full_give_margin = $request->input('third_full_give_margin');
        $balance->third_take_parcentage = $request->input('third_take_parcentage');
        $balance->third_helf_given_parcentage = $request->input('third_helf_given_parcentage');
        $balance->third_full_given_parcentage = $request->input('third_full_given_parcentage');
        $balance->fruits_game_withdraw_parcentage = $request->input('fruits_game_withdraw_parcentage');

        // Save the updated balance
        $balance->save();

        // Redirect or return a response
        $notification=array(
                'messege'=>'Third Balance Setting Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function LockListDelete($id)
    {
    	$data=FortuneLock::find($id);
    	$data->delete();
    	 $notification=array(
                'messege'=>'Game Lock ID Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function LockOffDelete($id)
    {  //return $id;
    	$data=User::find($id);
    	$data->auto_lock_status=0;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Lock Off ID  Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function ON()
    {
    	$data=FortuneSetting::find(1);
    	$data->game_status=1;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Start SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function Off()
    {
    	$data=FortuneSetting::find(1);
    	$data->game_status=0;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function AutoLockON()
    {
    $data=FortuneSetting::find(1);
    	$data->auto_lock=0;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Auto Lock On SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function AutoLockOff()
    {
    	$data=FortuneSetting::find(1);
    	$data->auto_lock=1;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Auto Lock Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
  public function GameON()
    {
    	$data=FortuneSetting::find(1);
    	$data->robot_on=1;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game  Robot Start SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function GameOff()
    {
    	$data=FortuneSetting::find(1);
    	$data->robot_on=0;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Robot Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function FiveControl()
    {
    	$data['balance']=FivestarSetting::find(1);
    	$data['game_serve_details']=FivestarTray::orderby('id','desc')->limit(22)->get();
    	$data['game_serve_users_details']=FivestarPots::orderby('id','desc')->limit(50)->get();
    	return view('backend.game.fivestare')->with($data);
    }
    public function FiveON()
    {
    	$data=FivestarSetting::find(1);
    	$data->game_status=1;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Start SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function FiveOff()
    {
    	$data=FivestarSetting::find(1);
    	$data->game_status=0;
    	$data->save();
    	 $notification=array(
                'messege'=>'Game Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
     public function FruitsPattarn()
    {
        $data=FruitsGamePattan::all();
        return view('backend.game.furits_pattarn',compact('data'));
    }public function GreedyPattarn()
    {
        $data=GreedyPattern::all();
        return view('backend.game.greedy_pattarn',compact('data'));
    }
    public function GreedyPattarnStore(Request $request)
    {
        $data=new GreedyPattern;
        $data->pots=$request->pots;
        $data->save();
       $notification=array(
                'messege'=>'Game Pots Addedd SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function FruitsPattarnStore(Request $request)
    {
        $data=new FruitsGamePattan;
        $data->pots=$request->pots;
        $data->save();
       $notification=array(
                'messege'=>'Game Pots Addedd SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function GreedyPattarnDelete($id)
    {
        $data=GreedyPattern::find($id);
        $data->delete();
       $notification=array(
                'messege'=>'Game Pots Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function GreedyPattarnUpdate($id,Request $request)
    {
        $data=GreedyPattern::find($id);
        $data->pots=$request->pots;
        $data->save();
       $notification=array(
                'messege'=>'Game Pots Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function FruitsPattarnDelete($id)
    {
        $data=FruitsGamePattan::find($id);
        $data->delete();
       $notification=array(
                'messege'=>'Game Pots Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function FruitsPattarnUpdate($id,Request $request)
    {
        $data=FruitsGamePattan::find($id);
        $data->pots=$request->pots;
        $data->save();
       $notification=array(
                'messege'=>'Game Pots Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function GradyControl()
    {
        $data['balance']=GradySetting::find(1);
        $data['game_serve_details']=GradyTray::orderby('id','desc')->limit(22)->get();
        $data['game_serve_users_details']=GradyPots::orderby('id','desc')->limit(50)->get();
        return view('backend.game.grady')->with($data);
    }

      public function TeenPattiControl()
    {
        $data['balance']=TeenPattiSetting::find(1);
        $data['game_serve_details']=TeenPattiTray::orderby('id','desc')->limit(30)->get();
        $data['game_serve_users_details']=TeenPattiPots::orderby('id','desc')->limit(50)->get();
        return view('backend.game.teenpatti')->with($data);
    }
     public function TeenPattiON()
    {
        $data=TeenPattiSetting::find(1);
        $data->game_status=1;
        $data->save();
         $notification=array(
                'messege'=>'Game Start SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function TeenPattiOff()
    {
        $data=TeenPattiSetting::find(1);
        $data->game_status=0;
        $data->save();
         $notification=array(
                'messege'=>'Game Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function TeenPattiIDBlock(Request $request)
  {
  	$data=TeenPattiSetting::find(1);

    $data->block_id=$request->block_id;
    if($request->block_id==0){
        return $request->block_id;
    $data->presser_lock=0;
    }else{
      $data->presser_lock=1;
    }
    $data->lock_parcent=$request->lock_parcent;

    $data->winner_id=$request->winner_id;
    
    $data->save();
    $notification=array(
                'messege'=>'ID Block  SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }
    public function AjaxData()
    {
        $data=GradySetting::find(1);
         // Check if the data was found
        if ($data) {
            // Return the data as JSON response
            return response()->json($data);
        } else {
            // Return an error response if the data is not found
            return response()->json(['message' => 'Data not found'], 404);
        }
        
    }public function FrutsAjaxData()
    {
        $data=FortuneSetting::find(1);
         // Check if the data was found
        if ($data) {
            // Return the data as JSON response
            return response()->json($data);
        } else {
            // Return an error response if the data is not found
            return response()->json(['message' => 'Data not found'], 404);
        }
        
    }
    public function GradyON()
    {
        $data=GradySetting::find(1);
        $data->game_status=1;
        $data->save();
         $notification=array(
                'messege'=>'Game Start SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function GradyOff()
    {
        $data=GradySetting::find(1);
        $data->game_status=0;
        $data->save();
         $notification=array(
                'messege'=>'Game Stop SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}
