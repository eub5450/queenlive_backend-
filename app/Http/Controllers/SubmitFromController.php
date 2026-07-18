<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HostData;
use App\Models\User;
use App\Models\Agency;
use App\Models\Follower;
use App\Models\Gift;
use App\Models\OldGift;
use App\Models\VipList;
use App\Models\ChildAgency;
use App\Models\UserLive;
use App\Models\DayTime;
use App\Models\BrdBackground;
use App\Models\LiveCall;
use App\Models\Slider;
use App\Models\HourlyRanking;
use App\Models\FuritsPotsBackup;
use Carbon;
use DB;
use Pusher;
use App\Models\Country;
use App\Models\Avater;
use App\Models\DeviceLockInvite;
use App\Models\Comment;
use DateTime;
use App\Models\luckyStar;
use App\Models\TopReward;
use App\Models\Setting;
use App\Models\MyBeg;
use App\Models\GameBannner;
use App\Models\Withdraw;
use Kreait\Firebase\Contract\Database;
use Session;
use App\Models\AgoraKeys;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Image;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class SubmitFromController extends Controller
{
    private const AGENCY_CODE_START = 1000;

     public function __construct(Database $database)
    {
        $this->database = $database;
    }
    
    public function getAgoraUsage() {
        $startTs = Carbon\Carbon::now('UTC')->startOfMonth()->timestamp;
        $endTs   = Carbon\Carbon::now('UTC')->timestamp;
        $appId="5df45575d2cd4751babe433fc58d00e4";
        $customerId = '35f70ad6dbbf47c88f31062c12a017f8';
        $customerSecret = '522ce3044ad44d718d699ebb42fe7fe8';
        $auth = base64_encode($customerId . ":" . $customerSecret);
    
        $response = Http::withHeaders([
            "Authorization" => "Basic $auth",
        ])->get("https://api.agora.io/beta/insight/usage/by_time", [
            "appid"   => $appId,
            "startTs" => $startTs,
            "endTs"   => $endTs,
            "metric"  => "sessionCount", // or userCount
        ]);
    
        return $response->json();
    }
    
    public function EvryMonthUpdate()
    {


  
    
        $date = Carbon\Carbon::now();
        $withdraw_start_date = $date->format('Y') . '-04-01';
        $start_date = $date->format('Y') . '-04-01';
        $end_date = $date->format('Y') . '-04-31';
     

         
          
//         //1 st frame Remove basic frame
//           User::where('frame', 'moon_frame8.svga')->update(['frame' => null]);
//           User::where('frame', 'new_frame_6.svga')->update(['frame' => null]);
//         //end 1st
//         //2nd add new frame based on target
        
//         $total_gift_collects = DB::table('gifts')
//         ->select('reciever_id', DB::raw('SUM(value) as total_value'))
//         ->whereBetween('date', [$start_date, $end_date])
//         ->groupBy('reciever_id')
//         ->having('total_value', '>', 999999)
//         ->get();
//         foreach($total_gift_collects as $total_gift_collect){
//             $add_frame_users=User::find($total_gift_collect->reciever_id);
//             $add_frame_users->frame="new_frame_6.svga";
//             $add_frame_users->save();
//             $data=new MyBeg;
//             $data->store_id=30;
//             $data->user_id=$add_frame_users->id;
//             $data->status=1;
//             $data->active_time=Carbon\Carbon::now();
//             $data->expaire_time=Carbon\Carbon::now()->addDays(22);
//             $data->name="Host Frame";
//             $data->image='store/vip/new_frame_6.png';
//             $data->effect='new_frame_6.svga';
//             $data->type='0';
//             $data->save();
//         }
        
//       ////  end 2nd
       
//   ////   3rd remove top agency top host frame
//              $top_agency_hosts = DB::table('gifts')
//             ->join('users', 'users.id', '=', 'gifts.reciever_id')
//             ->join('host_data', 'host_data.user_id', '=', 'users.id')
//             ->where('host_data.agency_code', 1268)
//             ->whereBetween('gifts.date', [$start_date, $end_date])
//             ->groupBy('gifts.reciever_id')
//             ->select('gifts.reciever_id as id', DB::raw('SUM(gifts.value) as total_value'))
//             ->having('total_value', '>', 999999)
//             ->get();
//             foreach($top_agency_hosts as $top_agency_host){
//                 $add_frame_users=User::find($top_agency_host->id);
//                 $add_frame_users->frame="moon_frame10.svga";
//                 $add_frame_users->save();
//                 $data=new MyBeg;
//                 $data->store_id=45;
//                 $data->user_id=$add_frame_users->id;
//                 $data->status=1;
//                 $data->active_time=Carbon\Carbon::now();
//                 $data->expaire_time=Carbon\Carbon::now()->addDays(22);
//                 $data->name="Top Family Frame";
//                 $data->image='store/vip/moon_frame10.png';
//                 $data->effect='moon_frame10.svga';
//                 $data->type='0';
//                 $data->save();
//             }
//     //end 3rd
    
   
    //5th level fall
    //   $user_levels=User::where('is_host_id',0)->where('level','>',1)->select('id','level')->get();
    // foreach($user_levels as $user_level)
    // {
        
    //     $gifts=Gift::where('sander_id',$user_level->id)->whereBetween('date', [$start_date, $end_date])->first();
    //     if(!$gifts){
    //         $user_level->level=1;
    //         $user_level->save();
    //     }
    // }
    //5th end 


//   DB::beginTransaction();

//     try {

//         // 1. Get all old data
//         $oldData = DB::table('old_pots_backups')->get()
//             ->map(function ($item) {
//                 return (array) $item;
//             });

//         // 2. Chunk insert (avoid placeholders limit)
//         foreach ($oldData->chunk(100) as $chunk) {
//             FuritsPotsBackup::insert($chunk->toArray());
//         }

//         // 3. Delete old data
//         DB::table('old_pots_backups')->delete();

//         DB::commit();
//         return "Data moved successfully in chunks!";

//     } catch (\Exception $e) {

//         DB::rollBack();
//         return "Error: " . $e->getMessage();
//     }
    }
    
private function bulkUpdateGifts($updates)
{
    if (empty($updates)) {
        return 0;
    }

    $updated = 0;
    
    foreach ($updates as $update) {
        $result = DB::table('gifts')
            ->where('id', $update['id'])
            ->update(['agency_code' => $update['agency_code']]);
        
        if ($result) {
            $updated++;
        }
    }
    
    return $updated;
}
    public function Index()
    {
    // Redis::flushall();
        $updatedCount = 0;
    $chunkSize = 100;
    
    // Process in chunks to avoid memory issues
    Gift::whereNull('agency_code')
        ->chunk($chunkSize, function($gifts) use (&$updatedCount) {
            
            $receiverIds = $gifts->pluck('reciever_id')->unique()->toArray();
            
            $hostData = DB::table('host_data')
                ->whereIn('user_id', $receiverIds)
                ->whereNotNull('agency_code')
                ->get()
                ->keyBy('user_id');
            
            $updates = [];
            
            foreach ($gifts as $gift) {
                if (isset($hostData[$gift->reciever_id])) {
                    $updates[] = [
                        'id' => $gift->id,
                        'agency_code' => $hostData[$gift->reciever_id]->agency_code
                    ];
                    $updatedCount++;
                }
            }
            
            if (!empty($updates)) {
                $this->bulkUpdateGifts($updates);
            }
        });
//         $agoras = AgoraKeys::where('Status', 2)
//     ->whereNull('note')
//     ->get();

// foreach ($agoras as $agora) {
//     $agora->delete(); // fires model events
// }


        
        // $my_vips = VipList::get();

        // foreach ($my_vips as $my_vip) {
        //     $endDate = Carbon\Carbon::parse($my_vip->active_date)->addDays(15);
        //     $my_vip->end_date = $endDate;
        //     $my_vip->save();
        
        //     if ($my_vip->is_active == 1) {
        //         $user = User::find($my_vip->user_id);
        
        //         if ($user && $user->is_vip == $my_vip->vip_no) {
        //             $user->vip_timeline = $endDate;
        //             $user->save();
        //         }
        //     }
        // }
      
    //   $push_comment_ref = $this->database->getReference('Comments/');
    //   $push_comment_ref->remove(); 
    //   $push_comment_ref = $this->database->getReference('svga_audiogifts/');
    //   $push_comment_ref->remove();
    //   $push_comment_ref = $this->database->getReference('audiogifts/');
    //   $push_comment_ref->remove();
    //   $push_comment_ref = $this->database->getReference('call_request/');
    //   $push_comment_ref->remove(); 
    //   $push_comment_ref = $this->database->getReference('official_brd_off/');
    //   $push_comment_ref->remove();
    //     $push_comment_ref = $this->database->getReference('PartnerChats/');
    // $push_comment_ref->remove(); 
    // $push_comment_ref = $this->database->getReference('AudioCalls/');
    // $push_comment_ref->remove();
    // $push_comment_ref = $this->database->getReference('audience_counter/');
    // $push_comment_ref->remove();
    //     $push_comment_ref = $this->database->getReference('audience_profile/');
    // $push_comment_ref->remove();
     $contry=Country::all();
 
      
//   $agencys=Agency::all();
//   foreach($agencys as $agency)
//   {
//       $user=User::find($agency->user_id);
//       if($user){
//           $agency->logo=$user->profile;
//           $agency->save();
//       }
//   }
      
    	return view('from.add_host',compact('contry'));
    }
    
    public function AgoraIndex(){
       $data=AgoraKeys::where('status','!=',2)->where('note',null)->get();
        return view('from.agora',compact('data'));
    }
    
    public function FontAgoraStore(Request $request)
    {
        //return $request->all();
        // ✅ Step 1: Validate input
        $request->validate([
            'appId'              => 'required|string|max:255',
            'appCertificate'     => 'required|string|max:255',
            'AgoraEmail'         => 'required|email|max:255',
            'AgoraEmailPassword' => 'required|string|max:255',
        ]);

        // ✅ Step 2: Save to database
        AgoraKeys::create([
            'appId'              => $request->appId,
            'appCertificate'     => $request->appCertificate,
            'AgoraEmail'         => $request->AgoraEmail,
            'type'               => $request->account_type,
            'main_email'         => $request->account_type == 1 
                                    ? str_replace('.', '', strstr($request->AgoraEmail, '@', true)) 
                                    : $request->AgoraEmail,
            'AgoraEmailPassword' => $request->AgoraEmailPassword,
        ]);

        // ✅ Step 3: Redirect with success message
        return redirect()->back()->with('success', 'Agora Account saved successfully!');
    }
    
    public function FontAgoraAccountActive($id)
    {
        $keys=AgoraKeys::where('Status',1)->get();
        foreach($keys as $item){
            $item->Status=2;
            $item->save();
        }
        $data=AgoraKeys::find($id);
        $data->Status=1;
        if($data->save()){
            $setting=Setting::find(1);
            $setting->appId=$data->appId;
            $setting->appCertificate=$data->appCertificate;
            $setting->save();
        }
        $notification=array(
                'messege'=>'Agorea Key Change Successfully !',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    
    public function Store(Request $request)
    {
        $request->validate([
            'agency_code' => 'required',
            'host_id' => 'required',
            'phone_number' => 'required',
            'hosting_type' => 'required',
        ]);
    	$agency=Agency::where('code',$request->agency_code)->first();
    	if ($agency) {
    	
    	$check_host_data=HostData::where('user_id',$request->host_id)->first();
        if ($check_host_data) {
             $notification=array(
                'messege'=>'Allready Have Host Data!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }else{
            $user=User::find($request->host_id);
            
            if($request->hasFile('image')){
                $image = $request->file('image');
                $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $image_url = $image_path.$image_name;
                $image->move($image_path, $image_name);
            }else{
                $image_url = 'store/profile/default.png';
            }
             if($request->hasFile('nid_image')){
                $photo_id = $request->file('nid_image');
                $photo_id_name = uniqid().'.'.strtolower($photo_id->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $photo_id_url = $image_path.$photo_id_name;
                $photo_id->move($image_path, $photo_id_name);
            }else{
                $photo_id_url = 'store/profile/default.png';
            }
         
            $data=new HostData;
           $data->user_id=$request->host_id;
           $data->agency_code=$request->agency_code;
           $data->name=$user->name;
           $data->phone=$request->phone_number;
           $data->photo_id=$photo_id_url;
           $data->image=$image_url;
           $data->country_id=1;
           $data->hosting_type=$request->hosting_type;
           $data->age=18;
           $data->save();
           $user->is_host_id=2;
           $user->country_id=1;
           $user->save();
            $notification=array(
                'messege'=>'Wait For Host Approved SuccessFully!',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
            
           
        }
        	# code...
    	}else{
    		$notification=array(
                'messege'=>'Agency Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
    	}
    }
    public function agencyIndex()
    {
      
      $data=Country::all();
      return view('from.add_agency',compact('data'));
    }
    public function agencyStore(Request $request)
    {
      $request->validate([
        'user_id' => 'required',
        'agency_name' => 'required|string|max:255',
        'phone' => 'required',
        'country_id' => 'required',
        'photo_id' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        'selfie' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        'nid' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
      ]);

      $check_user=User::find($request->user_id);
      if ($check_user) {
        
      $agency=Agency::where('user_id',$request->user_id)->first();
      if ($agency) {
            $notification=array(
                'messege'=>'Allready Have Agency This ID!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }else{
            $photo_id_url = $this->storeAgencyImageAsWebp($request->file('photo_id'));
            $selfie_url = $this->storeAgencyImageAsWebp($request->file('selfie'));
            $nid_url = $this->storeAgencyImageAsWebp($request->file('nid'));
            $agency=new Agency;
           $agency->user_id=$check_user->id;
           $agency->name=$request->agency_name;
           $agency->code=$this->nextAgencyCode();
           $agency->logo='store/profile/default.png';
           $agency->selfie=$selfie_url;
           $agency->photo_id=$photo_id_url;
           $agency->nid=$nid_url;
           $agency->bank_details=$request->bank_details;
           $agency->phone=$request->phone;
           $agency->country_id=$request->country_id;
           $agency->status=0;
           $agency->save();
           $check_user->country_id=$request->country_id;
           $check_user->save();
           $notification=array(
                'messege'=>'Agency Data Sand SuccessFully please Wait For Approval' ,
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
      }
      # code...
      }else{
          $notification=array(
                'messege'=>'User Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }
      
    }
    private function resolveAgencyCode($requestedCode = null)
    {
        $code = trim((string) $requestedCode);
        $normalizedCode = ctype_digit($code) ? (string) ((int) $code) : '';
        if ($normalizedCode !== '' && (int) $normalizedCode >= self::AGENCY_CODE_START && !Agency::where('code', $normalizedCode)->exists()) {
            return $normalizedCode;
        }

        return $this->nextAgencyCode();
    }

    private function nextAgencyCode()
    {
        $latest = Agency::query()
            ->whereNotNull('code')
            ->orderByRaw('CAST(code AS UNSIGNED) DESC')
            ->first();

        $highestCode = $latest ? max((int) $latest->code, self::AGENCY_CODE_START - 1) : self::AGENCY_CODE_START - 1;
        $next = $highestCode + 1;
        while (Agency::where('code', (string) $next)->exists()) {
            $next++;
        }

        return (string) $next;
    }
    private function storeAgencyImageAsWebp($file)
    {
      $directory = public_path('store/agency');
      if (!File::exists($directory)) {
        File::makeDirectory($directory, 0755, true);
      }

      $file_name = gmdate('YmdHis').'-'.uniqid().'.webp';
      $relative_path = 'store/agency/'.$file_name;
      $absolute_path = public_path($relative_path);

      $image = Image::make($file->getRealPath())->orientate()->resize(1400, null, function ($constraint) {
        $constraint->aspectRatio();
        $constraint->upsize();
      });

      $image->encode('webp', 60)->save($absolute_path);

      return $relative_path;
    }
  public function ActiveBanned()
  {
    $users=User::where('ban_type','!=',null)->where('ban_type','!=','A')->get();
    foreach($users as $user)
    {
      $time=Carbon\Carbon::now()->format('Y-m-d H:i:s');
    	if($user->open_time<$time){
          $user->ban_type=null;
          $user->open_time=null;
          $user->save();
        }
      
    }
    return Carbon\Carbon::now()->format('Y-m-d H:i:s');
    echo "Done";
  }
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

    private function businessNow()
    {
        return Carbon\Carbon::now(config('app.timezone', 'Europe/London'));
    }

    private function previousSalaryMonthRange()
    {
        $month = $this->businessNow()->subMonth();
        $startDate = $month->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $month->copy()->endOfMonth()->format('Y-m-d');

        return array($startDate, $endDate, $startDate . ' 00:00:00', $endDate . ' 23:59:59');
    }

    private function currentBusinessMonthRange()
    {
        $month = $this->businessNow();
        $startDate = $month->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $month->copy()->endOfMonth()->format('Y-m-d');

        return array($startDate, $endDate, $startDate . ' 00:00:00', $endDate . ' 23:59:59');
    }

  public function Salary($id)
    {
        $data=array();
        $agency=Agency::where('code',$id)->first();
        if ($agency) {
             list($start_date, $end_date, $start_at, $end_at) = $this->previousSalaryMonthRange();
          
             $hosts=HostData::join('users','users.id','host_data.user_id')->where('agency_code',$id)->select('users.id','users.name','host_data.hosting_type')->get();
             
             foreach($hosts as $host){
                   $type = DB::table('users')
                            ->join('host_data', 'host_data.user_id', 'users.id')
                            ->where('users.id', $host->id)
                            ->select('host_data.hosting_type','host_data.id')
                            ->first();

                              
                    
                               
                                 if ($type) {
                                  $dayTimeHistory = DB::table('day_times')
                                ->where('user_id', $host->id)
                                ->where('live_time', '>=', $start_at)
                                ->where('live_time', '<=', $end_at)
                                ->get();
                                	$running_durations = DB::table('day_times')
                    				->where('user_id', $host->id)
                    				->where('live_time', '>=', $start_at)
                                    ->where('live_time', '<=', $end_at)
                    				->where('brd_type',$type->hosting_type)
                    				->where('day_times', '>', '00:19:59')
                    				->select('day_times')
                    				->get();
                    
                                   
                                
                                $totalDuration = '00:00:00';
                                foreach ($running_durations as $duration){
                        
                                // Parse the duration as a DateTime object
                                $durationTime = new DateTime($duration->day_times);
                        
                                // Add the current duration to the total
                                $totalDuration = self::addDurations($totalDuration,$durationTime->format('H:i:s'));
                                }
                            
                    
                                  
                                    
                                    $total_coin= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$host->id)->where('date', '>=', $start_at)
                                    ->where('date', '<=', $end_at)->select('users.profile','users.name','gifts.value')->sum('value');
                                    
                                   $day_time_hostory = DB::table('day_times')
                    				->where('user_id', $host->id)
                    				->where('live_time', '>=', $start_at)
                                    ->where('live_time', '<=', $end_at)
                                    ->orderby('id','desc')
                    				->get();
                    				
                    				
               $day_time_data = DB::table('day_times')
                    				->where('user_id', $host->id)
                    				->where('live_time', '>=', $start_at)
                                    ->where('live_time', '<=', $end_at)
                                    ->orderby('id','desc')
                    				->get();

                                  $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $host->id)
                                        ->where('live_time', '>=', $start_at)
                                        ->where('live_time', '<=', $end_at)
                                        ->where('brd_type', $type->hosting_type)
                                        ->where('day_times', '>', '00:19:59')
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
                                $gift=Gift::where('reciever_id',$host->id)->where('date', '>=', $start_at)
                                        ->where('date', '<=', $end_at)->sum('value');
                    if($gift>0){
                       
                    $list = array();
                    $list['name'] = $host->name;
                    $list['id'] = $host->id;
                    $list['hosting_type'] =($host->hosting_type == 1) ? 'Audio' : 'Video';
                    $list['day'] =$running_day_count;
                    $list['time'] =$totalDuration;
                    $list['gift'] =$gift;
                   
                    $extra_points_array = [200000,400000,700000,1000000,1500000,2000000,3000000,4000000,5000000,7000000,10000000,15000000,20000000,30000000,50000000,100000000,150000000,200000000];
                
                    // Find the first value in the array that is greater than or equal to $gift
                    $extra_point = 0;
                    foreach ($extra_points_array as $value) {
                        if ($gift >= $value) {
                            $extra_point = $value;
                        } else {
                            break; // Stop iterating if we found a value greater than $gift
                        }
                    }
                
                    $list['extra_point'] = $gift - $extra_point;
                    $list['basic_point'] = $extra_point;
                
                    // Calculate basic_salary based on hosting_type and extra_point
                    if ($list['hosting_type'] == 'Video') {
                        $basic_salary = [
                            200000 => 1800,
                            400000 => 3600,
                            700000 => 6300,
                            1000000 => 9000,
                            1500000 => 13500,
                            2000000 => 18000,
                            3000000 => 27000,
                            4000000 => 36000,
                            5000000 => 45000,
                            7000000 => 63000,
                            10000000 => 90000,
                            15000000 => 135000,
                            20000000 => 180000,
                            30000000 => 270000,
                            50000000 => 450000,
                            100000000 => 900000,
                            150000000 => 1350000,
                            200000000 => 18000000,
                        ];
                    } else {
                        $basic_salary = [
                            200000 => 1600,
                            400000 => 3200,
                            700000 => 5600,
                            1000000 => 8000,
                            1500000 => 12000,
                            2000000 => 16000,
                            3000000 => 24000,
                            4000000 => 32000,
                            5000000 => 40000,
                            7000000 => 56000,
                            10000000 => 80000,
                            15000000 => 120000,
                            20000000 => 160000,
                            30000000 => 240000,
                            50000000 => 400000,
                            100000000 => 800000,
                            150000000 => 1200000,
                            200000000 => 16000000,
                        ];
                    }
                
                    // Check if extra_point exists in basic_salary array and calculate basic_salary
                    if (isset($basic_salary[$extra_point])) {
                        $list['basic_salary'] = $basic_salary[$extra_point];
                    } else {
                        // Set a default value if extra_point does not exist in the array
                        $list['basic_salary'] = 0;
                    }
                    //return $basic_salary[$extra_point];
                      
                  array_push($data, $list);
         }


             }
             $gifts = array_column($data, 'gift');

            // Sort the $data array based on 'gift' values
            array_multisort($gifts, SORT_DESC, $data);
//return $data;
            return view('salary_sheet',compact('agency','data','start_date','end_date'));
        }else{
            $notification=array(
                'messege'=>'Agency Not Found',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
    }
    public function Pusher()
    {
        $game_winner_text=array();
         $options = array(
                    'cluster' => 'ap1',
                    'useTLS' => true
                );
                  $pusher = new Pusher\Pusher(
                    '9ce9d96701d6600b426e',
                    '71aedfa829b4eb09c453',
                    '1618585',
                    $options
                );
                $message = "The Daily Prothom Alo is a daily newspaper in Bangladesh, published from Dhaka in the Bengali language.";
                      
                         array_push($game_winner_text,array('message'=>$message));
                          $pusher->trigger('game_winner_pusher', 'game_winner_pusher',$game_winner_text);
    }
     
    public function LuckStar()
  {
       
       Gift::whereBetween('created_at', ['2023-06-02 23:08:50', '2025-03-31 23:59:59'])
    ->chunk(10000, function ($gifts) {
        $oldGifts = $gifts->map(function ($gift) {
            return [
                'sander_id'     => $gift->sander_id,
                'reciever_id'   => $gift->reciever_id,
                'name'          => $gift->name,
                'value'         => $gift->value,
                'date'          => $gift->date,
                'channelName'   => $gift->channelName,
                'reaward_time'  => $gift->reaward_time,
            ];
        })->toArray();

        // Bulk insert into OldGift
        OldGift::insert($oldGifts);

        // Bulk delete from Gift
       Gift::whereIn('id', $gifts->pluck('id'))->delete();
    });
  // self::compressAndResizeImages();
   

    return view('from.lucky_star');
  }
  public function LuckyStarStore(Request $request)
  {
    $validated = $request->validate([
        'host_id' => 'required|unique:lucky_stars',
        'agency_code' => 'required',
    ]);
    $data=new luckyStar;
    $data->host_id=$request->host_id;
    $data->agency_code=$request->agency_code;
    $data->status=0;
    $data->save();
    Session::flash('message', 'Data Submit SuccessFully!'); 
    Session::flash('alert-class', 'alert-danger'); 
    return Redirect()->back();

  }
  public function CommentRemoved(){
        $currentDate = Carbon\Carbon::now();
        
        $my_begs = MyBeg::where('expaire_time', '<', $currentDate)->get();
        
        foreach ($my_begs as $my_beg) {
            $user = User::find($my_beg->user_id);
            if (!$user) { 
                $my_beg->delete();
                continue; 
            }
        
            if ($my_beg->type == 1) {
                // If 'entry' is a relation with FK 'entry_id'
                if (method_exists($user, 'entry')) {
                    // Compare against the FK value, not the relation
                    if ((string)$user->entry_id === (string)$my_beg->effect) {
                        $user->entry()->dissociate();   // clears entry_id to NULL
                    }
                } else {
                    // If it's a plain column named 'entry'
                    if ((string)$user->entry === (string)$my_beg->effect) {
                        $user->entry = null;
                    }
                }
            } else {
                // type != 1 -> frame
                if (method_exists($user, 'frame')) {
                    if ((string)$user->frame_id === (string)$my_beg->effect) {
                        $user->frame()->dissociate();   // clears frame_id to NULL
                    }
                } else {
                    if ((string)$user->frame === (string)$my_beg->effect) {
                        $user->frame = null;
                    }
                }
            }
        
            $user->save();
            $my_beg->delete();
        }

       $my_vips = VipList::where('end_date', '<', $currentDate)->get();

        foreach ($my_vips as $my_vip) {
            $user = User::find($my_vip->user_id);
            if (!$user) { $my_vip->delete(); continue; }
        
            if ($user->is_vip == $my_vip->vip_no) {
                // If is_vip==7 logic must run BEFORE setting is_vip=0
                if ($my_vip->vip_no == 7) {
                    $user->is_invisible = 0;
                    $user->is_invisible_active = 0;
                }
        
                // Clear entry/frame
                if (method_exists($user, 'entry')) {
                    $user->entry()->dissociate();     // clears entry_id
                } else {
                    $user->entry = null;              // if it's a real column
                }
        
                if (method_exists($user, 'frame')) {
                    $user->frame()->dissociate();     // clears frame_id
                } else {
                    $user->frame = null;              // if it's a real column
                }
        
                $user->is_vip = 0;
                $user->save();
            }
        
            $my_vip->delete();
        }

        Comment::truncate();
    
       $push_comment_ref = $this->database->getReference('Comments/');
       $push_comment_ref->remove(); 
       $push_comment_ref = $this->database->getReference('svga_audiogifts/');
       $push_comment_ref->remove();
       $push_comment_ref = $this->database->getReference('audiogifts/');
       $push_comment_ref->remove();
       $push_comment_ref = $this->database->getReference('call_request/');
       $push_comment_ref->remove(); 
       $push_comment_ref = $this->database->getReference('official_brd_off/');
       $push_comment_ref->remove();
      
  }
  public function SupperAgency($id)
  {
      $master_agency = Agency::where('code',$id)->first();
    	$data = ChildAgency::where('master_agency_id',$master_agency->id)->get();
    	$lists = [];
    	 list($start_date, $end_date, $start_at, $end_at) = $this->currentBusinessMonthRange();
		foreach ($data as $value) {

		    $agency = Agency::find($value->child_agency_id);
		    if($agency){
		     $host_gift_sum = DB::table('host_data')->join('gifts','gifts.reciever_id','host_data.user_id')->where('gifts.date', '>=', $start_at)->where('gifts.date', '<=', $end_at) ->where('host_data.agency_code',$agency->code)->sum('value');
		    $row = [
		        'agency' => $agency->name,
		        'agency_code' => $agency->code,
		        'id' => $value->child_agency_id,
		        'total_target' => $host_gift_sum,
		    ];
		    array_push($lists, $row);
		    }
		}
		 return view('supper_agency',compact('master_agency','lists','start_date','end_date'));
  }
  
 public function compressAndResizeImages()
{
    //     $response = array();
    //     $pusher_response = array();
      $global_txt = array();
    //     $websocket_call = array();
      $global_websoket = array();
      $hourly_ranking_global_websoket = array();
      $hourly_ranking_global_txt = array();
        $message = " ðŸ‘‘Ð¼á—©Ð¶ðŸ‘‘~Oronno Got 3219.88 Reward For Top Sender";
                         
       $channelName='68a9765cdee148948a70f431d6346f12';
      $GameBannner=GameBannner::first();
    
    
    array_push($global_txt,array('message'=>$message));
        array_push($global_websoket,array('message'=>'top_reward_banner','channelName'=>$channelName,'data'=>$global_txt,'code'=>'200','channel_type' => '49'));
     //$this->Websoket($global_websoket);

}
public function Event()
{
    list($startDate, $endDate, $startAt, $endAt) = $this->currentBusinessMonthRange();
    $cacheKey = 'event_data_' . str_replace('-', '_', substr($startDate, 0, 7));
    $lockKey = $cacheKey . '_lock';

    // Use cache lock to prevent multiple simultaneous regenerations
    $host_results = Cache::lock($lockKey, 10)->block(3, function() use ($cacheKey, $startDate, $endDate, $startAt, $endAt) {
        return Cache::remember($cacheKey, $this->businessNow()->addMinutes(3), function() use ($startDate, $endDate, $startAt, $endAt) {
            // Your existing data processing logic
            $gifts = Gift::whereBetween(DB::raw('DATE(date)'), [$startDate, $endDate])
                ->select('sander_id', 'id', 'imie')
                ->get();
                
            foreach ($gifts as $gift) {
                $user = User::find($gift->sander_id);
                if ($user && $user->imei_number != null) {
                    $gift->imie = $user->imei_number;
                    $gift->save();
                }
            }

            Gift::whereNotBetween(DB::raw('DATE(date)'), [$startDate, $endDate])
                ->update(['imie' => null]);

            $subquery = DB::table(DB::raw('
                (SELECT *, 
                        ROW_NUMBER() OVER (PARTITION BY reciever_id, imie ORDER BY id) as rn 
                 FROM gifts) as filtered_gifts
            '))
            ->where('rn', '<=', 2);

            return DB::table(DB::raw("({$subquery->toSql()}) as g"))
                ->mergeBindings($subquery)
                ->select(
                    'g.reciever_id',
                    'u.name',
                    'u.profile',
                    DB::raw('COUNT(DISTINCT g.sander_id) as sander_count'),
                    DB::raw('SUM(g.value) as total_value'),
                    DB::raw("CASE WHEN SUM(g.value) >= 2000000 AND COUNT(DISTINCT g.sander_id) >= 20 THEN 1 ELSE 0 END as color")
                )
                ->leftJoin('users as u', 'g.reciever_id', '=', 'u.id')
                ->whereBetween('g.date', [$startAt, $endAt])
                ->groupBy('g.reciever_id', 'u.name', 'u.profile')
                ->orderByDesc('sander_count')
                ->orderByDesc('total_value')
                ->limit(10)
                ->get();
        });
    });
//return  $host_results;
    return view('event', compact('host_results', 'startDate', 'endDate'));
}
private function compressAndResizeImage($source, $destination, $quality, $new_width, $new_height)
{
    $info = getimagesize($source);
    
    // Load the image based on its MIME type
    if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
        $image = imagecreatefromjpeg($source);
    } elseif ($info['mime'] == 'image/png') {
        $image = imagecreatefrompng($source);
    }

    // Get original dimensions
    list($width, $height) = getimagesize($source);

    // Maintain aspect ratio
    $aspect_ratio = $width / $height;

    if ($new_width / $new_height > $aspect_ratio) {
        $new_width = $new_height * $aspect_ratio;
    } else {
        $new_height = $new_width / $aspect_ratio;
    }

    // Create a new true color image with the resized dimensions
    $new_image = imagecreatetruecolor($new_width, $new_height);

    // Retain transparency for PNG
    if ($info['mime'] == 'image/png') {
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    // Resample the image
    imagecopyresampled($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);

    // Save the resized and compressed image
    if ($info['mime'] == 'image/jpeg' || $info['mime'] == 'image/jpg') {
        imagejpeg($new_image, $destination, $quality); // For JPEG, use the quality parameter
    } elseif ($info['mime'] == 'image/png') {
        $png_compression_level = 9 - floor($quality / 10); // Convert JPEG quality to PNG compression level
        imagepng($new_image, $destination, $png_compression_level); // For PNG, use compression level
    }

    // Free up memory
    imagedestroy($image);
    imagedestroy($new_image);
}

public function Reward()
{
    $today=date('Y-m-d');
    $now = Carbon\Carbon::now();

  

// Get the start and end of the current day
$startOfDay = $now->copy()->startOfDay(); // 00:00:00
$endOfDay = $now->copy()->endOfDay();  
    
    // Query gifts within the current hour
      $running_gift = Gift::select(
        'gifts.sander_id',
        DB::raw('SUM(gifts.value) as total_value'),
        'users.name',
        'users.profile'
    )
    ->join('users', 'users.id', '=', 'gifts.sander_id')
    ->where('gifts.sander_id', '!=', 1)
    ->whereBetween('gifts.date', [$startOfDay, $endOfDay])
    ->groupBy('gifts.sander_id', 'users.name', 'users.profile')
    ->orderByDesc('total_value')
    ->get();
    $hourly_top_threes = $running_gift->take(3);
$hourly_others = $running_gift->slice(3, 7)->values();

   $top_reward_claims = TopReward::select(
        'top_rewards.user_id',
        'users.name',
        'users.profile',
        DB::raw('SUM(top_rewards.amount) as total_amount')
    )
    ->join('users', 'users.id', '=', 'top_rewards.user_id')
    ->groupBy('top_rewards.user_id', 'users.name', 'users.profile')
    ->orderByDesc('total_amount')
      ->limit(10)
     ->get();
$total_reward=TopReward::sum('amount');
    return view('top_reward',compact('hourly_top_threes','hourly_others','top_reward_claims','total_reward'));
}
  private function RealTime($response,$roomName,$channelName)
    {
      $setting = Setting::find(1);
      $options = array(
              'cluster' => $setting->cluster,
              'useTLS' => true
          );
          $pusher = new Pusher\Pusher(
              $setting->key,
              $setting->secret,
              $setting->app_id,
              $options
          );
        $pusher->trigger($roomName,$channelName,$response);
    }
 private function Websoket($data) {
    try {
        app(\App\Services\AudioRoom\AudioRoomRealtimeService::class)
            ->broadcastLegacyWithRoomScoped($data, ['source' => 'SubmitFromController']);
    } catch (\Throwable $th) {
        info('SubmitFrom named websocket exception: ' . $th->getMessage());
    }
}
 public function getGmailEmails(Request $request)
{
    // Gmail credentials from .env
    $email = env('IMAP_USERNAME', 'sdafasdfafdg@gmail.com');
    $password = env('IMAP_PASSWORD', 'cgjr qhag aris ponj');
    
    // Connect to Gmail INBOX
    $mailbox = function_exists('imap_open') ? imap_open('{imap.gmail.com:993/imap/ssl}INBOX', $email, $password) : false;
    
    if (!$mailbox) {
        return response()->json([
            'error' => 'Failed to connect: ' . imap_last_error()
        ]);
    }
    
    // Get all emails
    $emails = imap_search($mailbox, 'ALL');
    
    if (!$emails) {
        imap_close($mailbox);
        return response()->json([]);
    }
    
    // Sort by newest first and limit to 20
    rsort($emails);
    $latestEmails = array_slice($emails, 0, 20);
    
    $result = [];
    
    foreach ($latestEmails as $emailId) {
        $header = imap_headerinfo($mailbox, $emailId);
        
        // Get FROM
        $from = '';
        if (isset($header->from[0])) {
            $fromObj = $header->from[0];
            $fromName = isset($fromObj->personal) ? imap_utf8($fromObj->personal) : '';
            $fromEmail = $fromObj->mailbox . '@' . $fromObj->host;
            $from = $fromName ? "$fromName <$fromEmail>" : $fromEmail;
        }
        
        // Get TO
        $to = [];
        if (isset($header->to)) {
            foreach ($header->to as $t) {
                $to[] = $t->mailbox . '@' . $t->host;
            }
        }
        
        // Get subject
        $subject = isset($header->subject) ? imap_utf8($header->subject) : '(No Subject)';
        
        // Get body
        $body = imap_fetchbody($mailbox, $emailId, 1.2);
        if ($body == '') {
            $body = imap_fetchbody($mailbox, $emailId, 1);
        }
        
        // Decode if base64
        $encoding = imap_fetchstructure($mailbox, $emailId)->encoding;
        if ($encoding == 3) {
            $body = base64_decode($body);
        }
        
        $body = imap_qprint($body);
        $plainText = strip_tags($body);
        
        // Get date
        $date = isset($header->date) ? date('Y-m-d H:i:s', strtotime($header->date)) : null;
        
        $result[] = [
            'id' => $emailId,
            'subject' => $subject,
            'from' => $from,
            'to' => $to,
            'date' => $date,
            'body' => $plainText,
            'body_html' => $body,
            'seen' => !isset($header->Unseen)
        ];
    }
    
    imap_close($mailbox);
    
    return response()->json($result);
}
}
