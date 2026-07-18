<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Auth;
use App\Models\DeviceLockInvite;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
class SliderController extends Controller
{
    public function Index(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = trim((string) ($request->query('user_id', $request->input('user_id', ''))));


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            // MM: cache slider catalog (admin-managed; Slider model already has saved/deleted hooks that clear *slider* keys)
            $slider = Cache::remember('v4:queenlive:slider_list_v1', 300, function () {
                return Slider::orderby('id','desc')->get();
            });
            $invite_popup = 0;
            $authUser = Auth::user();
            $resolvedUser = $authUser;

            if (!$resolvedUser && $user_id !== '') {
                $resolvedUser = User::find($user_id);
            }

            if ($resolvedUser) {
                $deviceLockInvite = null;
                if (!empty($resolvedUser->imei_number)) {
                    $deviceLockInvite = DeviceLockInvite::where('device_id', $resolvedUser->imei_number)->first();
                }

                if ((int) ($resolvedUser->invite_done ?? 0) === 0 && empty($deviceLockInvite)) {
                    $invite_popup = 1;
                }
            }
            array_push($response,array('message'=>'Slider Found','code'=>'200','data'=>$slider,'invite_popup'=>$invite_popup));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
