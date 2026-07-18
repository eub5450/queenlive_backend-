<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\ProfilePending;
use App\Models\Withdraw;
use App\Models\Agency;
use App\Models\Visitor;
use DB;
use Carbon;
use Image;
use Illuminate\Support\Str;
use DateInterval;
use DateTime; 
use Illuminate\Support\Facades\File;
use App\RedisCache\RedisCache as RedisCacheStore;
use App\Support\MediaPathHelper;
use App\Support\ImageUploadStorageHelper;
use RedisCacheFunction;
class ProfileController extends Controller
{
    public function ProfileLiveData(Request $request)
    {
        $response = array();
         $token = $request->access_token;
         $user_id = $request->user_id;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if (Auth::id()==$user_id) {
               $user=RedisCacheFunction::UserfindById($user_id);
              if (!$user) {
                  array_push($response,array('message'=>'User not found','code'=>'404'));
                  return json_encode($response,JSON_UNESCAPED_UNICODE);
              }
              
               if(!$user->ban_type!=null){ 
        
        //$user_id = 8;
        
          $date = Carbon\Carbon::now(); // Replace this with your desired date

        
           $start_date = date('Y-m') . '-01';
               
          $end_date = date('Y-m') . '-31';
          $running_day_count = 0;
          $totalDuration = '00:00:00';
           $type = DB::table('users')
                            ->join('host_data', 'host_data.user_id', 'users.id')
                            ->where('users.id', $user_id)
                            ->select('host_data.hosting_type','host_data.id')
                            ->first();

                                if ($type) {
                                  $dayTimeHistory = DB::table('day_times')
                                ->where('user_id', $user_id)
                              
                                ->get();
                                	$running_durations = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				
                    				->where('brd_type',$type->hosting_type)
                    				
                                    ->where('day_times', '>', '00:14:59')
                    				->select('day_times')
                    				->get();
                    
                                    function addDurations($duration1, $duration2) {
                                    $time1 = explode(':', $duration1);
                                    $time2 = explode(':', $duration2);
                            
                                    $hours = intval($time1[0]) + intval($time2[0]);
                                    $minutes = intval($time1[1]) + intval($time2[1]);
                                    $seconds = intval($time1[2]) + intval($time2[2]);
                            
                                    if ($seconds >= 60) {
                                        $minutes += 1;
                                        $seconds -= 60;
                                    }
                            
                                    if ($minutes >= 60) {
                                        $hours += 1;
                                        $minutes -= 60;
                                    }
                            
                                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                }
                                
                                $totalDuration = '00:00:00';
                                foreach ($running_durations as $duration){
                        
                                // Parse the duration as a DateTime object
                                $durationTime = new DateTime($duration->day_times);
                        
                                // Add the current duration to the total
                                $totalDuration = addDurations($totalDuration, $durationTime->format('H:i:s'));
                                }
                           
                    

                    	          
                                    
                    				$total_coin= RedisCacheFunction::getGiftBetweenSumDates($user_id,$start_date,$end_date);
                                    
                      

                                  $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $user_id)
                                      
                                        ->where('brd_type', $type->hosting_type)
                                        
                                        ->where('day_times', '>', '00:14:59')
                                        ->select('live_time', 'day_times')
                                        ->get();
                                    
                                    $running_day_count = 0;
                                    $current_date = null;
                                    $total_duration = 0;
                                    
                                    foreach ($day_time_duration as $day_time_duration) {
                                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                        $time = $day_time_duration->day_times;
                                    
                                        if ($current_date === null || $current_date !== $date) {
                                            // Check if the previous day's total duration exceeds 01:01:00
                                            if ($current_date !== null && $total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                                $running_day_count++;
                                            }
                                    
                                            $current_date = $date;
                                            $total_duration = 0;
                                        }
                                    
                                        $duration_parts = explode(':', $time);

                                        // Ensure array has at least three parts (set missing values to 0)
                                        $hours = isset($duration_parts[0]) ? intval($duration_parts[0]) : 0;
                                        $minutes = isset($duration_parts[1]) ? intval($duration_parts[1]) : 0;
                                        $seconds = isset($duration_parts[2]) ? intval($duration_parts[2]) : 0;
                                        
                                        $total_duration += ($hours * 3600) + ($minutes * 60) + $seconds;

                                    }
                                    
                                    // Check the total duration of the last date
                                    if ($total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                        $running_day_count++;
                                    }
                    				}
                    				$day_time = "00:00:00";
                            list($hours, $minutes, $seconds) = explode(':', $day_time);
                            
                            $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
                
             $first_ten= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->get();
               $second_ten= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->get();
               $three_ten= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->get();
               $total_gift_coinRedisCacheFunction::getGiftBetweenSumDates($user_id,$start_date,$end_date);
               $total_withdraw=RedisCacheFunction::getUserWithdrawSumBetweenDates($user_id,$start_date,$end_date);
               $total_coin= $total_gift_coin-$total_withdraw;
              
               $total_day_count=$running_day_count;
               $total_hours_count=$totalDuration;
               $lest_update_profile=Carbon\Carbon::parse($user->updated_at);

               array_push($response,array('message'=>'Live Data Store  Successfully ','first_ten_days'=>$first_ten,'second_ten_days'=>$second_ten,'third_ten_days'=>$three_ten,'total_coin'=>$total_coin,'total_day_count'=>$total_day_count,'total_hours_count'=>$total_hours_count,'lest_update_profile'=>$lest_update_profile,'profile'=>$user->profile,'name'=>$user->name,'join'=>$user->created_at,'code'=>'200'));
               return json_encode($response,JSON_UNESCAPED_UNICODE);
            }else{
                       array_push($response,array('message'=>'Your Device Banned','code'=>'401'));
                       return json_encode($response,JSON_UNESCAPED_UNICODE);
                   }
            }else{
                //$request->user()->currentAccessToken()->delete();
                array_push($response,array('message'=>'Something Wrong!','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
            }

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }

    private function resolveProfileCountryId($country)
    {
        $raw = trim((string) $country);
        if ($raw === '' || strtolower($raw) === 'null') {
            return null;
        }
        if (is_numeric($raw)) {
            return (int) $raw;
        }

        $codeMap = [
            'BD' => 'Bangladesh', 'IN' => 'India', 'PK' => 'Pakistan',
            'NP' => 'Nepal', 'LK' => 'Sri Lanka', 'BT' => 'Bhutan',
            'MV' => 'Maldives', 'SA' => 'Saudi Arabia',
            'AE' => 'United Arab Emirates', 'QA' => 'Qatar',
            'KW' => 'Kuwait', 'BH' => 'Bahrain', 'OM' => 'Oman',
            'MY' => 'Malaysia', 'ID' => 'Indonesia', 'SG' => 'Singapore',
            'TH' => 'Thailand', 'GB' => 'United Kingdom',
            'UK' => 'United Kingdom', 'US' => 'United States',
            'CA' => 'Canada', 'AU' => 'Australia',
        ];
        $lookup = $codeMap[strtoupper($raw)] ?? $raw;
        $normalized = strtolower($lookup);

        $countryRow = DB::table('countries')
            ->whereRaw('LOWER(name) = ?', [$normalized])
            ->first();
        if (!$countryRow) {
            $countryRow = DB::table('countries')
                ->where('name', 'like', '%' . $lookup . '%')
                ->first();
        }

        return $countryRow ? (int) $countryRow->id : null;
    }

    private function profileCountryName($countryId)
    {
        if (!$countryId) {
            return null;
        }
        $country = DB::table('countries')->where('id', $countryId)->first();
        return $country ? $country->name : null;
    }

    public function ProfileUpdate(Request $request)
    {
        try {
            $response = array();
            $token = $request->access_token;
            $user_id = $request->user_id;
            $name = $request->name;
            $bio = $request->bio;
            $profile = $request->profile;
            $date_of_birth = $request->date_of_birth;
            $gender = $request->gender;
            $country = $request->country ?? $request->country_id;
    
            if ($token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
                if (Auth::id() == $user_id) {
                    $user = RedisCacheFunction::UserfindById(Auth::id());
    
                    // Check if user is in ProfilePending
                    $check_is_pending = ProfilePending::where('user_id', $user_id)->first();
                    $hasProfileImage = ($request->hasFile('profile') && $request->file('profile')->isValid())
                        || ($request->has('profile') && $profile != null);

                    if ($hasProfileImage) {
                        $image_url = $request->hasFile('profile')
                            ? $this->storeUploadedProfileImage($request->file('profile'), $user)
                            : $this->processProfileImageUnder10KB($request->input('profile'), $user);

                        if (!$image_url) {
                            array_push($response, array('message' => 'Failed to process image', 'code' => '400'));
                            return response()->json($response, 400, [], JSON_UNESCAPED_UNICODE);
                        }

                        if ($user->profile != 'store/profile/default.png' &&
                            strpos((string) $user->profile, 'default.png') === false) {
                            MediaPathHelper::deleteLocalFile($user->profile, ['store/user']);
                        }
                    } else {
                        $image_url = $user->profile;
                    }
    
                    if ($bio) {
                        $user->bio = $bio;
                    }
                    if ($image_url) {
                        $user->profile = $image_url;
                    }
                    if ($name) {
                        $user->name = $name;
                    }
                    if ($request->has('date_of_birth')) {
                        $user->date_of_birth = trim((string) $date_of_birth);
                    }
                    if ($request->has('gender')) {
                        $normalizedGender = strtolower(trim((string) $gender));
                        if (in_array($normalizedGender, ['1', 'm', 'man', 'men', 'boy'], true)) {
                            $normalizedGender = 'male';
                        } elseif (in_array($normalizedGender, ['2', 'f', 'woman', 'women', 'girl'], true)) {
                            $normalizedGender = 'female';
                        }
                        $user->gender = $normalizedGender;
                    }
                    if ($request->has('country') || $request->has('country_id')) {
                        $countryId = $this->resolveProfileCountryId($country);
                        if ($countryId) {
                            $user->country_id = $countryId;
                        }
                    }
    
                    $user->save();
                    
                    $check_agency = Agency::where('user_id', $user->id)->first();
                    if ($check_agency && $image_url) {
                        $check_agency->logo = $image_url;
                        $check_agency->save();
                    }

                    RedisCacheStore::clearUserCache($user->id);
                    UserDataController::forgetProfileCachesForPair($user->id, $user->id);

                    array_push($response, ['message' => 'Profile Update Successfully', 'code' => '200', 'data' => ['gender' => $user->gender, 'date_of_birth' => $user->date_of_birth, 'country_id' => $user->country_id, 'country' => $this->profileCountryName($user->country_id)]]);
                    return response()->json($response, 200, [], JSON_UNESCAPED_UNICODE);
                    
                } else {
                    array_push($response, array('message' => 'Something Wrong!', 'code' => '401'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
            } else {
                array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            array_push($response, array(
                'message' => 'Internal Server Error', 
                'code' => '500', 
                'error' => $e->getMessage()
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    /**
     * Store the edited profile image without server-side resize/re-encode.
     */
    private function processProfileImageUnder10KB($base64Image, $user)
    {
        try {
            return ImageUploadStorageHelper::storeBase64Image(
                $base64Image,
                'store/user',
                'profile_' . $user->id
            );
        } catch (\Exception $e) {
            \Log::error("Profile - Image storage failed: " . $e->getMessage());
            return null;
        }
    }

    private function storeUploadedProfileImage($file, $user)
    {
        try {
            return ImageUploadStorageHelper::storeUploadedImage(
                $file,
                'store/user',
                'profile_' . $user->id
            );
        } catch (\Exception $e) {
            \Log::error("Profile - Uploaded image storage failed: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Extract path from URL
     */
    private function extractPathFromUrl($url)
    {
        return MediaPathHelper::localRelativePath($url);
    }
    
    public function Visitor(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $receiver_id = $request->receiver_id;


        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $check_old_data=Visitor::where('user_id',$user_id)->where('receiver_id',$receiver_id)->first();
            if(!$check_old_data){
            $data=new Visitor;
            $data->user_id=$user_id;
            $data->receiver_id=$receiver_id;
            $data->save();
            }
            array_push($response,array('message'=>'Visitor Store Successfully ','code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);

        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function LiveData(Request $request){
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
         if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
              $user=RedisCacheFunction::UserfindById(Auth::id());

        //$user_id = 8;

          $date = Carbon\Carbon::now(); // Replace this with your desired date

           $start_date = date('Y-m') . '-01';
          $end_date = date('Y-m') . '-31';
          $running_day_count = 0;
          $totalDuration = '00:00:00';
          $total_audio_time = '00:00:00';
           $type = DB::table('users')
                            ->join('host_data', 'host_data.user_id', 'users.id')
                            ->where('users.id', $user_id)
                            ->select('host_data.hosting_type','host_data.id')
                            ->first();

                                if ($type) {
                                  $dayTimeHistory = DB::table('day_times')
                                ->where('user_id', $user_id)
                                ->get();
                                	$running_durations = DB::table('day_times')
                    				->where('user_id', $user_id)
                    				->where('brd_type',2)
                    				->where('day_times', '>', '00:14:59')
                    				->select('day_times')
                    				->get();

                                    function addDurations($duration1, $duration2) {
                                    $time1 = explode(':', $duration1);
                                    $time2 = explode(':', $duration2);

                                    $hours = intval($time1[0]) + intval($time2[0]);
                                    $minutes = intval($time1[1]) + intval($time2[1]);
                                    $seconds = intval($time1[2]) + intval($time2[2]);

                                    if ($seconds >= 60) {
                                        $minutes += 1;
                                        $seconds -= 60;
                                    }

                                    if ($minutes >= 60) {
                                        $hours += 1;
                                        $minutes -= 60;
                                    }

                                    return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
                                }

                                $totalDuration = '00:00:00';
                                foreach ($running_durations as $duration){

                                // Parse the duration as a DateTime object
                                $durationTime = new DateTime($duration->day_times);

                                // Add the current duration to the total
                                $totalDuration = addDurations($totalDuration, $durationTime->format('H:i:s'));
                                }

                    				$total_coin= RedisCacheFunction::getGiftBetweenSumDates($user_id,$start_date,$end_date);

                                  // Boss 2026-07-18: NO brd_type filter here -- a "live day" counts
                                  // whether the user hosted audio or video that day (was hardcoded to
                                  // brd_type=2/video-only on one node, silently dropping audio-only days).
                                  $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $user_id)
                                        ->where('day_times', '>', '00:14:59')
                                        ->select('live_time', 'day_times')
                                        ->get();

                                    $running_day_count = 0;
                                    $current_date = null;
                                    $total_duration = 0;

                                    foreach ($day_time_duration as $day_time_duration) {
                                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                        $time = $day_time_duration->day_times;

                                        if ($current_date === null || $current_date !== $date) {
                                            // Check if the previous day's total duration exceeds 01:01:00
                                            if ($current_date !== null && $total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                                $running_day_count++;
                                            }

                                            $current_date = $date;
                                            $total_duration = 0;
                                        }

                                        $duration_parts = explode(':', $time);
                                        $hours = intval($duration_parts[0]);
                                        $minutes = intval($duration_parts[1]);
                                        $seconds = intval($duration_parts[2]);
                                        $total_duration += ($hours * 3600) + ($minutes * 60) + $seconds;
                                    }

                                    // Check the total duration of the last date
                                    if ($total_duration >= 3600) { // 3660 seconds = 1 hour 1 minute
                                        $running_day_count++;
                                    }
                    				}
                    				$day_time = "00:00:00";
                            list($hours, $minutes, $seconds) = explode(':', $day_time);

                            $total_seconds = ($hours * 3600) + ($minutes * 60) + $seconds;
               $total_coin_list= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->get();

                $today_coin_list= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date',now()->toDateString())->select('users.profile','users.name','gifts.value')->get();
               $total_gift_coin=RedisCacheFunction::getGiftBetweenSumDates($user_id,$start_date,$end_date);
               $total_withdraw=RedisCacheFunction::getUserWithdrawSumBetweenDates($user_id,$start_date,$end_date);
               $total_coin= $total_gift_coin-$total_withdraw;

               $total_day_count=$running_day_count;
               // Boss 2026-07-18: total_hours_count is the combined
               // (audio+video, all room types) total -- computed
               // independently so it can never be silently aliased to a
               // single room type's duration the way it was before
               // (total_video_time below stays the video-only one).
               $combinedDurationRow = DB::table('day_times')
                   ->where('user_id', $user_id)
                   ->where('day_times', '>', '00:14:59')
                   ->selectRaw("TIME_FORMAT(SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(day_times)),0)), '%H:%i:%s') as total_all_time")
                   ->first();
               $total_hours_count = ($combinedDurationRow && $combinedDurationRow->total_all_time)
                   ? $combinedDurationRow->total_all_time
                   : $totalDuration;
               $lest_update_profile=Carbon\Carbon::parse($user->updated_at);

               $audioDurationRow = DB::table('day_times')
                    ->where('user_id', $user_id)
                    ->where('brd_type',1)
                    ->where('day_times', '>', '00:14:59')
                    ->selectRaw("TIME_FORMAT(SEC_TO_TIME(COALESCE(SUM(TIME_TO_SEC(day_times)),0)), '%H:%i:%s') as total_audio_time")
                    ->first();
               if ($audioDurationRow && $audioDurationRow->total_audio_time) {
                   $total_audio_time = $audioDurationRow->total_audio_time;
               }
               $total_video_time=$totalDuration;
                array_push($response,array('message'=>'Live Data Store  Successfully ','total_coin_list'=>$total_coin_list,'today_coin_list'=>$today_coin_list,'total_coin'=>$total_coin,'total_day_count'=>$total_day_count,'total_hours_count'=>$total_hours_count,'lest_update_profile'=>$lest_update_profile,'profile'=>$user->profile,'name'=>$user->name,'join'=>$user->created_at,'total_video_time'=>$total_video_time,'total_audio_time'=>$total_audio_time,'code'=>'200'));
               return json_encode($response,JSON_UNESCAPED_UNICODE);

         }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
