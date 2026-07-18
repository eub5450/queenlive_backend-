<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Help;
use Auth;
class HelpController extends Controller
{
     public function Store(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $message = trim((string) ($request->message ?? $request->problem ?? ''));


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id!=Auth::id()){
                array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

            if($message===''){
                array_push($response,array('message'=>'Message Required','code'=>'422'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

            $data=new Help;
            $data->user_id=$user_id;
            $data->problem=mb_substr($message, 0, 2000);
            $data->status=0;
            $data->save();
            array_push($response,array('message'=>'Support Request Sand Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
