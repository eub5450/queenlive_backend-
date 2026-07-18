<?php

namespace App\Http\Controllers\Api\V4;

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

         // MM: cache full level table (admin-managed, slow-changing) — 24h TTL
         $data = Cache::remember('v4:queenlive:lavel_list_v1', 86400, function () {
             return Lavel::select('amount','update_lavel')->orderby('amount','asc')->get();
         });
         $total_sand_gift = RedisCacheFunction::getTotalUserSandingGift($user_id);
         $my_running_level = RedisCacheFunction::UserfindById($user_id);
         // MM: null guard — bad user_id used to fatal on ->level
         if (!$my_running_level) {
             array_push($response, array('message' => 'User Not Found', 'code' => '404'));
             return json_encode($response, JSON_UNESCAPED_UNICODE);
         }
         // Agent SC1 / 2026-06-28: DISPLAY-ONLY level + progress via the V5
         // versioned state cache (same users.level + Lavel-ladder formula),
         // reused until a gift bump. NOT a spend check.
         $v5State = \App\Services\V5\V5UserStateCache::get((string) $user_id);
         $current_level = (int) $v5State['level'];
         $next_level = $current_level + 1;
         // MM: cache next-level lookup by level number (admin-managed) — 24h TTL
         $next_level_amount = Cache::remember(
             'v4:queenlive:lavel_target_v1_' . $next_level,
             86400,
             function () use ($next_level) {
                 return Lavel::where('update_lavel', $next_level)->first();
             }
         );
         // MM: null guard — top-level users have no next row → fatal on ->amount
         if (!$next_level_amount || (int) $next_level_amount->amount <= 0) {
             array_push($response, array(
                 'message' => 'Data Found Successfully!',
                 'list' => $data,
                 'my_running_lavel' => $current_level,
                 'next_level' => $next_level,
                 'complete_parcentage' => 100,
                 'code' => '200',
             ));
             return json_encode($response, JSON_UNESCAPED_UNICODE);
         }
         $next_amount = (int) $next_level_amount->amount;
         $need_parcent = (int) $v5State['level_progress_pct']; // Agent SC1: cache-sourced display progress

         array_push($response, array(
             'message' => 'Data Found Successfully!',
             'list' => $data,
             'my_running_lavel' => $current_level,
             'next_level' => $next_level,
             'complete_parcentage' => $need_parcent,
             'code' => '200',
         ));
         return json_encode($response, JSON_UNESCAPED_UNICODE);
    	}
    	else{
    		array_push($response,array('message'=>'Unauthorizeddd','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
    	}


    }
}
