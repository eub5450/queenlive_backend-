<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BedWord;
class CommentSkipController extends Controller
{
    public function WordList(Request $request)
    {
    	 $response = array();
         $token = $request->access_token;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$data=BedWord::select('word')->get();
        	 array_push($response,array('message'=>'Comment Skip Word List','code'=>'200','data'=>$data));
            return json_encode($response,JSON_UNESCAPED_UNICODE); 
        }else{
        	  array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
