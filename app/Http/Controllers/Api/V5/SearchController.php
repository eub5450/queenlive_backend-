<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Follower;
class SearchController extends Controller
{
    public function Search(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $search = $request->search;
        $user_id = $request->user_id;
        $data=array();

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $user_lists=User::where ('id', 'LIKE', '%' . $search . '%' )->orWhere ( 'name', 'LIKE', '%' . $search . '%' )->orderby('id','desc')->get();
            foreach($user_lists as $user_list){
                $follow=Follower::where('user_id',$user_id)->where('follower_id',$user_list->id)->first();
                if($follow){
                    //Yes Following
                    $friend=Follower::where('user_id',$user_list->id)->where('follower_id',$user_id)->first();
                    if( $friend){
                       $is_i_follow=2;
                    }else{
                      // NOt Friend
                      $is_i_follow=1;
                    }
                  }
                  else{
                    // Not Following
                    $is_i_follow=0;
                  }
                  $row = array();
                  $row['is_i_follow']=$is_i_follow;
                  $row['name']=$user_list->name;
                  $row['id']=$user_list->id;
                  $row['name']=$user_list->name;
                  $row['email']=$user_list->email;
                  $row['balance']=$user_list->balance;
                  $row['imei_number']=$user_list->imei_number;
                  $row['email_verified_at']=$user_list->email_verified_at;
                  $row['profile']=$user_list->profile;
                  $row['level']=$user_list->level;
                  $row['is_vip']=$user_list->is_vip;
                  $row['vip_timeline']=$user_list->vip_timeline;
                  $row['is_admin']=$user_list->is_admin;
                  $row['status']=$user_list->status;
                  $row['entry_level']=$user_list->entry_level;
                  $row['role']=$user_list->role;
                  $row['phone']=$user_list->phone;
                  $row['device_id']=$user_list->device_id;
                  $row['day_count']=$user_list->day_count;
                  $row['hours_count']=$user_list->hours_count;
                  $row['created_at']=$user_list->created_at;
                  $row['updated_at']=$user_list->updated_at;
                  $row['is_host_id']=$user_list->is_host_id;
                  $row['is_agency']=$user_list->is_agency;
                  $row['date_of_birth']=$user_list->date_of_birth;
                  $row['gender']=$user_list->gender;
                  $row['bio']=$user_list->bio;
                  $row['api_token']=$user_list->api_token;
                  $row['brd_off_power']=$user_list->brd_off_power;
                  $row['is_coin_protal_active']=$user_list->is_coin_protal_active;
                  $row['is_invisible']=$user_list->is_invisible;
                  $row['is_official_id']=$user_list->is_official_id;
                  $row['ban_type']=$user_list->ban_type;
                  $row['open_time']=$user_list->open_time;
                  $row['is_device_ban']=$user_list->is_device_ban;
                  $row['ban_proved']=$user_list->ban_proved;
                  $row['ban_by']=$user_list->ban_by;
                  $row['is_invisible_active']=$user_list->is_invisible_active;
                  $row['prosss_top']=$user_list->prosss_top;
                  $row['country_id']=$user_list->country_id;
                  $row['master_protal_id']=$user_list->master_protal_id;
                  $row['host_badge']=$user_list->host_badge;
                   array_push($data, $row);
            }
            
           array_push($response,array('message'=>'Profile Update Successfully ','code'=>'200','data'=>$data));
           return json_encode($response,JSON_UNESCAPED_UNICODE);

       }else{
        array_push($response,array('message'=>'Unauthorized','code'=>'401'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    }
    }
}
