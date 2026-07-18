<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserBlock;
use App\Models\User;
use DB;
class BlockController extends Controller
{
       public function Store(Request $request)
       {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $block_user_id = $request->block_user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=new UserBlock;
            $data->user_id=$user_id;
            $data->block_user_id=$block_user_id;
            $data->save();
            array_push($response,array('message'=>'User Blocked Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function Index(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $data=DB::table('user_blocks')->join('users','users.id','user_blocks.block_user_id')->where('user_blocks.user_id',$user_id)->select('users.*')->get();
            array_push($response,array('message'=>'User Blocked List Showing Successfully ','code'=>'200','data'=>$data));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function UnBlock(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $unblock_user_id = $request->unblock_user_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $user=User::find($unblock_user_id);
            $unblock=UserBlock::where('block_user_id',$unblock_user_id)->where('user_id',$user_id)->get();
            foreach ($unblock as $key => $value) {
                $value->delete();
            }
            array_push($response,array('message'=>'User UnBlocked Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
