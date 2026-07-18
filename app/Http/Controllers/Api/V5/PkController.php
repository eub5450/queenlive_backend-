<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLive;
use App\Models\PkLive;
use Carbon;
use DB;
use Auth;
class PkController extends Controller
{
    public function PkUserList(Request $request)
    {
        $data = UserLive::where('type',2)->where('user_id',22222)->select('user_id','channelName','name','avatar')->limit(5)->get();
    
        $response = [
            'message'  => 'Pk User List',
            'pk_play_user_list' => $data,
            'code'     => 200,
        ];
    
        return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
    }
    
    public function PkPlayRequest(Request $request){
        $response = array();
        $token = $request->access_token;
        $host_id = $request->host_id;
        $host_channelName = $request->host_channelName;
        $opponent_id = $request->opponent_id;
        $opponent_channelName = $request->opponent_channelName;
          if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $host = User::select('id', 'name', 'profile')->find($host_id);
            // Remove any prior un-accepted PK request from this host so a
            // repeat tap doesn't accumulate duplicate pending PkLive rows.
            PkLive::where('host_id', $host_id)->where('status', 'pending')->delete();
            $data=new PkLive;
            $data->host_id=$host_id;
            $data->host_channelName=$host_channelName;
            $data->opponent_channelName=$opponent_channelName;
            $data->opponent_id=$opponent_id;
            $data->status='pending';
            $data->save();
               
             array_push($response,array('message'=>'Have A Pk Request ','host_id'=>$host_id,'opponent_id'=>$opponent_id,'opponent_channelName'=>$opponent_channelName,'host_channelName'=>$host_channelName,'host_data'=>$host,'code'=>'200','event_type' => 'room.pk.requested'));
              self::Websoket($response);
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }
        
    }
    public function PkRequestAccept(Request $request){
        $response = array();
        $token = $request->access_token;
        $host_id = $request->host_id;
        $host_channelName = $request->host_channelName;
        $opponent_id = $request->opponent_id;
        $opponent_channelName = $request->opponent_channelName;
          if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            //   $data_for_check=new PkLive;
            //     $data_for_check->opponent_id=$host_id;
            //     $data_for_check->opponent_channelName=$host_channelName;
            //     $data_for_check->host_channelName=$opponent_channelName;
            //     $data_for_check->host_id=$opponent_id;
            //     $data_for_check->status='pending';
            //     $data_for_check->save();
              $data=PkLive::where('host_id',$host_id)->where('opponent_id',$opponent_id)->first();
              if($data){
                  $data->status='active';
               $start = Carbon\Carbon::now();
                $end   = $start->copy()->addMinutes(2);
                
                $data->pk_start_time = $start;
                $data->pk_end_time   = $end;
                  $data->save();
                  $host = User::select('id', 'name', 'profile')->find($host_id);
                   array_push($response,array('message'=>'Pk Request Accepted','host_id'=>$host_id,'opponent_id'=>$opponent_id,'opponent_channelName'=>$opponent_channelName,'host_channelName'=>$host_channelName,'host_data'=>$host,'data'=>$data,'code'=>'200','event_type' => 'room.pk.accepted'));
                    self::Websoket($response);
                    return json_encode($response,JSON_UNESCAPED_UNICODE);
              }else{
                  array_push($response,array('message'=>'No Pk Request Actived','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
              }
          }else{
               array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }
    }
    public function PkPlayerSearchProfile(Request $request){
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $channelName = $request->channelName;
          if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
              $data = UserLive::where('type',2)->select('avatar')->limit(5)->get();
              
             array_push($response,array('message'=>'Rounding Profile List','avaters'=>$data,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
          }
        
    }
    
private function Websoket($data) {
    try {
        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'PkController']);
    } catch (\Throwable $th) {
        info('PK named websocket exception: ' . $th->getMessage());
    }
}
}
