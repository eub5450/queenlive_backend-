<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PrivatCall;
class CallController extends Controller
{
    public function VideoCall(Request $request){
    	 $response = array();
         $token = $request->access_token;
         $caller_id = $request->caller_id;
         $reciver_id = $request->reciver_id;
         $channel = $request->channel;
         $time = $request->live_time;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$caller_user=User::find($caller_id);
        	$reciver_user=User::find($reciver_id);
        	$officia_id=User::find(11111);
        	if ($caller_user->balance>334) {
        		$caller_user->balance-=334;
        		$reciver_user->balance+=265;
        		$officia_id->balance+=67.6;
        		$caller_user->save();
			    $reciver_user->save();
			    $officia_id->save();
			    $check_day_time=PrivatCall::where('reciver_id',$reciver_id)->where('caller_id',$caller_id)->where('channel',$channel)->first();
			        if($check_day_time){
			            $check_day_time->live_time=$time;
			             $check_day_time->save();
			        }else{
			          $daytime=new PrivatCall;
			          $daytime->reciver_id=$reciver_id;
			          $daytime->caller_id=$caller_id;
			          $daytime->channel=$channel;
			          $daytime->live_time=$time;
			          $daytime->type=1;
			          $daytime->save(); 
			        }
			    array_push($response,array('message'=>'Successffly Connect','code'=>'200'));
            	return json_encode($response,JSON_UNESCAPED_UNICODE);
        	}else{
        	array_push($response,array('message'=>'Inseficiant Balance','code'=>'606'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        	}
        }else{
        	array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
public function AudioCall(Request $request){
    	 $response = array();
         $token = $request->access_token;
         $caller_id = $request->caller_id;
         $reciver_id = $request->reciver_id;
         $time = $request->live_time;
         $channel = $request->channel;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$caller_user=User::find($caller_id);
        	$reciver_user=User::find($reciver_id);
        	$officia_id=User::find(11111);
        	if ($caller_user->balance>85) {
        		$caller_user->balance-=85;
        		$reciver_user->balance+=65;
        		$officia_id->balance+=20;
        		$caller_user->save();
			    $reciver_user->save();
			    $officia_id->save();
			    $check_day_time=PrivatCall::where('reciver_id',$reciver_id)->where('caller_id',$caller_id)->where('channel',$channel)->first();
			        if($check_day_time){
			            $check_day_time->live_time=$time;
			             $check_day_time->save();
			        }else{
			          $daytime=new PrivatCall;
			          $daytime->reciver_id=$reciver_id;
			          $daytime->caller_id=$caller_id;
			          $daytime->channel=$channel;
			          $daytime->live_time=$time;
			          $daytime->type=2;
			          $daytime->save(); 
			        }
			    array_push($response,array('message'=>'Successffly Connect','code'=>'200'));
            	return json_encode($response,JSON_UNESCAPED_UNICODE);
        	}else{
        	array_push($response,array('message'=>'Inseficiant Balance','code'=>'606'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        	}
        }else{
        	array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
