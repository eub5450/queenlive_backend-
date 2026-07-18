<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LiveCall;
use App\Models\Setting;
use App\Models\User;
use App\Models\UserLive;
use Kreait\Firebase\Contract\Database;
use Pusher;
use DB;
class LiveCoHostController extends Controller
{
   public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function JoinCall(Request $request)
    {
         $banned=User::where('ban_type','!=',Null)->where('id',$request->co_host_id)->first();
        
            if(!$banned){
             $setting=Setting::find(1);
             $access_token = $request->access_token;
             $host_id = $request->host_id;
             $channelName = $request->channelName;
             $co_host_id = $request->co_host_id;
              $set_no = $request->set_no;
             $response = array();
            if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
                $remove_old_call=LiveCall::where('co_host_id',$co_host_id)->first();
           if($remove_old_call){
            $remove_old_call->delete();
            }
              $check_call=LiveCall::where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('host_id',$host_id)->first();
              if($check_call){
                 array_push($response,array('message'=>'Call Already Sand ','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
              }else{
                $live=UserLive::where('channelName','=',$channelName)->where('user_id',$host_id)->first();
                if($live){
                    $check_call_set=LiveCall::where('channelName','=',$channelName)->where('type',1)->where('host_id',$host_id)->where('set_no',$set_no)->first();
                    if($check_call_set){
                          array_push($response,array('message'=>'Set Allready Booked','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }else{
                $data=new LiveCall;
                $data->co_host_id=$co_host_id;
                $data->channelName=$channelName;
                $data->type=$live->type;
                $data->host_id=$host_id;
                if($set_no!=null){
                  $data->set_no=$set_no;
                }else{
                  $data->set_no=0;
                }
                
                $data->status='pending';
                $data->super_mute='0';
                $data->save();
    
                 
    
                $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.set_no')->where('live_calls.channelName',$channelName)->get();
                $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
                $key = $channelName;
                 $count=[
                 'call_count'=>strval($call_count),
                ];
               $push_count_ref = $this->database->getReference('call_request/' . $key);
               $push_count_ref->set($count);
    
                array_push($response,array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
                    }
                }else{
                    array_push($response,array('message'=>'Live Off Already','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
                }
                
              }
            }else{
                array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
              if($banned->ban_type=="B"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For One Month . violation Rules B', 'code' => '404'));
              }elseif($banned->ban_type=="C"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 24 Hours . violation Rules C', 'code' => '404'));
              }
              elseif($banned->ban_type=="D"){
                array_push($response, array('message' => 'Opps !! Your ID Banned For 1 Hours . violation Rules D', 'code' => '404'));
              }else{
               array_push($response, array('message' => 'Opps !!  You Are Permanent Benned . violation Rules A', 'code' => '404'));
              }
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            // return response()->json(['message'=>"User Not Found"],404);
            }
    }
   public function CallList(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
                        LiveCall::where('channelName',$channelName)->where('status','pending')->where('created_at','<',\Carbon\Carbon::now()->subSeconds(30))->delete();
            $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('status','pending')->get();

            $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','pending')->get();

            array_push($response,array('message'=>'Call Request Sand Successfully ','data'=>$list,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function CallAccept(Request $request)
    {
      //return $request->all();
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $co_host_id = $request->co_host_id;
         $set_no = $request->set_no;
         $channelName = $request->channelName;
         $response = array();
         $accept = array();
      
     
      
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','pending')->first();
       		if($data){
            $data->status='Accept';
            $data->save(); 
            }
         
			$accept_list=DB::table('live_calls')->where('host_id',$host_id)->where('channelName','=',$channelName)->where('status','Accept')->get();
            $list=DB::table('live_calls')->where('host_id',$host_id)->where('co_host_id',$co_host_id)->where('channelName','=',$channelName)->where('status','Accept')->first();
           	$key = $channelName;
			
          	$host_data=User::find($host_id);
			$host=array();
			$host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
             $host['balance'] = 0;
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
    		$host['co_host_id'] = strval($host_data->id);
    		$push_count_ref = $this->database->getReference('host_list/' . $key .'/'. 0);
    		$push_count_ref->set($host);
          	$row = array();
          	$audio_call = array();
          	$i=0;
			foreach ($accept_list as $call) {
			    
    		
			    ///End new Code need type condition
              $co_host=User::find($call->co_host_id);
              
    		$row['channelName'] = $channelName;
            $row['profile'] = $co_host->profile;
             $row['balance'] = 0;
            // $row['mute'] = strval( $call->mute);
            $row['co_host_name'] = $co_host->name;
            $row['set_no'] = $call->set_no;
    		$row['co_host_id'] = strval($call->co_host_id);
    		$push_count_ref = $this->database->getReference('call_accept/' . $key .'/'. $call->co_host_id);
    		$push_count_ref->set($row);
    		$push_count_ref = $this->database->getReference('host_list/' . $key .'/'. ++$i);
    		$push_count_ref->set($row);
			    
    		
			}
          
          if($set_no!=0){
		 //starrrt new code 
			$audio_co_host=User::find($co_host_id);
    		$audio_call['channelName'] = $channelName;
            $audio_call['profile'] = $audio_co_host->profile;
            $audio_call['balance'] = 0;
            $audio_call['co_host_name'] = $audio_co_host->name;
            $audio_call['set_no'] =  strval($set_no);
            $audio_call['mute'] = strval(1);
    		$audio_call['co_host_id'] = strval($audio_co_host->id);
    		$audio_call_data = $this->database->getReference('audiocall_accept/' . $key .'/'. $audio_co_host->id);
    		$audio_call_data->set($audio_call);
          }
			
			 $call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
            $key = $channelName;
             $count=[
             'call_count'=>strval($call_count),
            ];
           $push_count_ref = $this->database->getReference('call_request/' . $key);
           $push_count_ref->set($count);
           
          
            array_push($response,array('message'=>'Call Request Accept and list sand Successfully ','data'=>$list,'accept_list'=>$accept_list,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
  public function CallRemoved(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
        $co_host_id = $request->co_host_id;
         $channelName = $request->channelName;
    	$set_no = $request->set_no;
        $response = array();
        $accept = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
              

           
          $data=LiveCall::where('host_id',$host_id)->where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','Accept')->first();
		if($data){
            if($data->delete()){  
              $key = $channelName;
              
             $set=[
             'set_no'=>strval($set_no),
            ];
           $push_count_ref = $this->database->getReference('set_remove/' . $key .'/'. $host_id);
           $push_count_ref->set($set);
               
            }
            }
          $key = $channelName;
              $co_host=$co_host_id;
          $audio_call_accept = $this->database->getReference('audiocall_accept/' . $key );
				$audio_call_accept->remove();
          		$audio_call_mute_remove = $this->database->getReference('audio_call_mute/' . $key .'/'. $co_host);
				$audio_call_mute_remove->remove();
              	$callAcceptRef = $this->database->getReference('call_accept/' . $key .'/'. $co_host);
				$callAcceptRef->remove();
				$hostcallAcceptRef = $this->database->getReference('host_list/' . $key);
				$hostcallAcceptRef->remove();
          $accept_list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','Accept')->get();
           
        	$key = $channelName;
        	$row = array();
        	
        	$i=0;
			foreach ($accept_list as $call) {
             $co_host=User::find($call->co_host_id);
    		$row['channelName'] = $channelName;
    		$row['balance'] = 0;
            $row['profile'] = $call->profile;
            $row['co_host_name'] = $co_host->name;
            $row['set_no'] = $call->set_no;
    		$row['co_host_id'] = strval($call->co_host_id);
    		$push_count_ref = $this->database->getReference('call_accept/' . $key .'/'. $call->co_host_id);
    		$push_count_ref->set($row);
    		$push_host_count_ref = $this->database->getReference('host_list/' . $key .'/'. ++$i);
    		$push_host_count_ref->set($row);
    		if($call->type==1){
    		    $mute_data_entry = array();
    		$mute_data_entry['channelName'] = $channelName;
    		$mute_data_entry['co_host_id'] = strval($call->co_host_id);
            $mute_data_entry['set_no'] = strval($call->set_no);
          	$mute_data_entry['status'] = strval($call->mute);
    		$call_mute = $this->database->getReference('audio_call_mute/' . $channelName .'/'. $call->co_host_id);
    		$call_mute->set($mute_data_entry);
    		}
			
			}
			$host_data=User::find($host_id);
			$host=array();
		
            $host['channelName'] = $channelName;
            $host['profile'] = $host_data->profile;
             $host['balance'] = 0;
            $host['co_host_name'] = $host_data->name;
            $host['set_no'] = "0";
    		$host['co_host_id'] = strval($host_data->id);
    		$host_list_host_entry = $this->database->getReference('host_list/' . $key .'/'. 0);
    		$host_list_host_entry->set($host);
			$call_count=DB::table('live_calls')->where('status','pending')->where('channelName',$channelName)->count();
            $key = $channelName;
             $count=[
             'call_count'=>strval($call_count),
            ];
           $push_count_ref = $this->database->getReference('call_request/' . $key);
           $push_count_ref->set($count);
           
           

            array_push($response,array('message'=>'Call Removed Successfully','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function CallAcceptList(Request $request)
    {
         $access_token = $request->access_token;
         $host_id = $request->host_id;
         $channelName = $request->channelName;
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            

            $list=DB::table('live_calls')->join('users','users.id','live_calls.co_host_id')->select('users.name','users.profile','live_calls.channelName','live_calls.co_host_id','live_calls.status','live_calls.set_no')->where('live_calls.host_id',$host_id)->where('live_calls.channelName',$channelName)->where('live_calls.status','Accept')->get();
           
            array_push($response,array('message'=>'Call  Accept List Successfully ','data'=>$list,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function CallMute(Request $request)
    {
        //for video 
         $access_token = $request->access_token;
         $co_host_id = $request->co_host_id;
         $channelName = $request->channelName;
        $response = array();
        if($access_token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
         $data=LiveCall::where('channelName',$channelName)->where('co_host_id',$co_host_id)->where('status','Accept')->first();
          if($data){
          if($data->mute==0){
          $data->mute=1;
          }else{
          $data->mute=0;
          }
            $data->save();
          }
            
          if($data){
          
            $list=$co_host_id;
            $list_data=LiveCall::where('channelName',$channelName)->where('status','Accept')->get();
            $key = $channelName;
        	$row = array();

			foreach ($list_data as $call) {
		    
    		$row['channelName'] = $channelName;
    		$row['co_host_id'] = strval($call->co_host_id);
            $row['mute_status'] = strval($call->mute);
    		$push_count_ref = $this->database->getReference('call_mute/' . $key .'/'. $call->co_host_id);
    	    $push_count_ref->set($row);
    		
			}

            array_push($response,array('message'=>' Co Host Call Mute Successfully','data'=>$list,'channelName'=>$channelName,'mute_status'=>$data,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
            $list=$co_host_id;
            array_push($response,array('message'=>'Sorry CoHost Not Found ','data'=>$list,'channelName'=>$channelName,'mute_status'=>0,'code'=>'401'));
           

            array_push($response,array('message'=>'Sorry CoHost Not Found','data'=>$list,'code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }
        }else{
            array_push($response,array('message'=>'Unauthorized access_token','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
  
}
