<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lavel;
use App\Models\Gift;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use RedisCacheFunction;
class LavelController extends Controller
{
    public function Index(Request $request)
    {
    	$response = array();
         $token = $request->access_token;
         $user_id = $request->user_id;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            
    	 $data=Lavel::select('amount','update_lavel')->orderby('amount','asc')->get();
    	 // Agent SC1 / 2026-06-28: DISPLAY-ONLY level + progress via the V5
    	 // versioned state cache. Same formula as before (users.level +
    	 // total_sent/next_threshold*100 from the Lavel ladder); reused across
    	 // requests until a gift bump. NOT a spend check.
    	 $v5State = \App\Services\V5\V5UserStateCache::get((string) $user_id);
    	 $my_running_lavel = (int) $v5State['level'];
    	 $next_level = $my_running_lavel + 1;
    	 $need_parcent = (int) $v5State['level_progress_pct'];

    	array_push($response,array('message'=>'Data Found Successfully!','list'=>$data,'my_running_lavel'=>$my_running_lavel,'next_level'=>$next_level,'complete_parcentage'=>$need_parcent,'code'=>'200'));
        return json_encode($response,JSON_UNESCAPED_UNICODE);
    	}
    	else{
    		array_push($response,array('message'=>'Unauthorizeddd','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
    	}


    }
}
