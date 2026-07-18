<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserLive;
use App\Models\User;
class UserInLiveLink extends Controller
{
    public function NewLive($id,$ChannalName,$brd_type)
    {
        //return $id;
        $user=User::find($id);
        
         $type=$brd_type; 
        return view('live_share',compact('user','type'));
    }
    public function Live($id,$ChannalName)
    {
        //return $id;
        $user=User::find($id);
 
        return view('live_share',compact('user'));
    }
   public  function send_push_notification(){

        $url = 'https://fcm.googleapis.com/fcm/send';
$serverKey = 'AAAAd7PU44s:APA91bEjM26iXg_0tksuEmwQ5N6UBAWCdsm01Ym66dI3IYeHo92JBMOjB4VWyGsnZbWRqIvFkJqxxCOYI7FOhnWWzYBT9hSd3eic2S3RNJ5C8jqphRDpjp2EYEUKNLiDQtnfKhKD_edK';

$data = [
    'to' => 'dsyVR2oeQLyE1erR32kMMf:APA91bF5364OP3JuY5oF8ANmMd_OgEG2vzEvbJ4XtISu4tv-9tk1qvWDda5LFbWBJvNNyZNgCvYXCbaFNzN71S6lbzR2jmR_7ychWcS4_mLaLtgEqA2oHwZPrthTRFBZcDVMAmyVcaoD', // Replace 'device_id' with the actual device ID
    'data' => [
        'link' => "https://lindaapp.in/new_live/share/v2/1/1/1",
    ],
    'notification' => [
         'body' => "You Have A Recharge 2000000 Points.",
        'title' => 'BP Live',
        'click_action' => 'MYNoti',
    ],
];

$headers = [
    'Authorization: key=' . $serverKey,
    'Content-Type: application/json',
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

$response = curl_exec($ch);
curl_close($ch);
   }
   
   public function job()
   {
       $users = User::where('is_official_id',1)->where('status', 1)->get();
foreach ($users as $user) {

        $user->comment_badge = 'Official';
        $user->save();
   
    
}
echo "done";

   }
    
}
