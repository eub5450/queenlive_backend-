<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\HostData;
use App\Models\Withdraw;
use App\Models\User;
use DB;
use Carbon;
use DateTime;
use DatePeriod;
use DateInterval;
use Image;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redis;
use App\RedisCache\RedisCache as RedisCacheStore;
use App\Support\MediaPathHelper;
class AgencyController extends Controller
{
    private $prefix = 'queenlive:';

    public function MyHost(Request $request)
    {
        if (!$this->hasValidAccessToken($request)) {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $actorId = $this->resolveActorId($request);
        if (!$actorId) {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $agency = $this->findAgencyByOwner($actorId);
        if (!$agency) {
            return response()->json([['message' => 'Agency Not Found', 'code' => '404', 'host_list' => [], 'agency' => null]], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $cacheKey = $this->prefix . "agency_host_list_{$agency->code}";

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return response($cached, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
            }
        } catch (\Exception $e) {
        }

        $hosts = DB::table('host_data as host_data')
            ->join('users', 'users.id', '=', 'host_data.user_id')
            ->where('host_data.agency_code', $agency->code)
            ->select('users.id', 'users.name', 'users.status', 'users.profile', 'users.level')
            ->orderByDesc('users.id')
            ->get()
            ->map(function ($host) {
                return array(
                    'id' => (int) $host->id,
                    'name' => (string) $host->name,
                    'status' => (string) $host->status,
                    'profile' => MediaPathHelper::publicUrl($host->profile),
                    'level' => (string) $host->level,
                );
            })
            ->values()
            ->all();

        $payload = [[
            'message' => 'Host Data Show',
            'code' => '200',
            'host_list' => $hosts,
            'agency' => $this->serializeAgency($agency),
        ]];

        $this->cacheJsonPayload($cacheKey, $payload, 300);

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function MyHostData(Request $request)
    {
        if (!$this->hasValidAccessToken($request)) {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $actorId = $this->resolveActorId($request);
        if (!$actorId) {
            return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
        }

        $agency = $this->findAgencyByOwner($actorId);
        if (!$agency) {
            return response()->json([['message' => 'Agency Not Found', 'code' => '404', 'host_list' => [], 'agency' => null]], 404, [], JSON_UNESCAPED_UNICODE);
        }

        $monthKey = date('Y-m');
        $cacheKey = $this->prefix . "agency_host_data_{$agency->code}_{$monthKey}";

        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                return response($cached, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
            }
        } catch (\Exception $e) {
        }

        $startDate = date('Y-m') . '-01';
        $endDate = date('Y-m-t');

        $hosts = DB::table('host_data as host_data')
            ->join('users', 'users.id', '=', 'host_data.user_id')
            ->where('host_data.agency_code', $agency->code)
            ->select(
                'users.id',
                'users.name',
                'users.status',
                'users.profile',
                'users.level',
                'host_data.hosting_type'
            )
            ->orderByDesc('users.id')
            ->get();

        $hostIds = $hosts->pluck('id')->map(function ($id) {
            return (int) $id;
        })->all();

        $hostList = array();

        if (!empty($hostIds)) {
            $hostingTypes = array();
            foreach ($hosts as $host) {
                $hostingTypes[(int) $host->id] = (int) ($host->hosting_type ?: 1);
            }

            $coinTotals = DB::table('gifts')
                ->whereIn('reciever_id', $hostIds)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->select('reciever_id', DB::raw('SUM(value) as total_coin'))
                ->groupBy('reciever_id')
                ->pluck('total_coin', 'reciever_id');

            $withdrawTotals = Withdraw::whereIn('host_id', $hostIds)
                ->whereDate('date', '>=', $startDate)
                ->whereDate('date', '<=', $endDate)
                ->select('host_id', DB::raw('SUM(total) as total_withdraw'))
                ->groupBy('host_id')
                ->pluck('total_withdraw', 'host_id');

            $dayTimeRows = DB::table('day_times')
                ->whereIn('user_id', $hostIds)
                ->where('live_time', '>=', $startDate)
                ->where('live_time', '<=', $endDate)
                ->where('day_times', '>', '00:14:59')
                ->select('user_id', 'brd_type', 'live_time', 'day_times')
                ->orderBy('user_id')
                ->orderBy('live_time')
                ->get();

            $hostMetrics = array();

            foreach ($dayTimeRows as $row) {
                $userId = (int) $row->user_id;
                $expectedBrdType = $hostingTypes[$userId] ?? null;

                if ($expectedBrdType !== null && (int) $row->brd_type !== (int) $expectedBrdType) {
                    continue;
                }

                $parts = explode(':', (string) $row->day_times);
                if (count($parts) !== 3) {
                    continue;
                }

                $seconds = (((int) $parts[0]) * 3600) + (((int) $parts[1]) * 60) + (int) $parts[2];
                $dateKey = Carbon\Carbon::parse($row->live_time)->toDateString();

                if (!isset($hostMetrics[$userId])) {
                    $hostMetrics[$userId] = array(
                        'total_seconds' => 0,
                        'day_totals' => array(),
                    );
                }

                $hostMetrics[$userId]['total_seconds'] += $seconds;

                if (!isset($hostMetrics[$userId]['day_totals'][$dateKey])) {
                    $hostMetrics[$userId]['day_totals'][$dateKey] = 0;
                }

                $hostMetrics[$userId]['day_totals'][$dateKey] += $seconds;
            }

            foreach ($hosts as $host) {
                $userId = (int) $host->id;
                $metric = $hostMetrics[$userId] ?? array('day_totals' => array());
                $dayCount = 0;

                foreach ($metric['day_totals'] as $secondsForDay) {
                    if ((int) $secondsForDay >= 3660) {
                        $dayCount++;
                    }
                }

                $coinHave = (int) ($coinTotals[$userId] ?? 0) - (int) ($withdrawTotals[$userId] ?? 0);

                $hostList[] = array(
                    'id' => $userId,
                    'name' => (string) $host->name,
                    'status' => (string) $host->status,
                    'profile' => MediaPathHelper::publicUrl($host->profile),
                    'level' => (string) $host->level,
                    'day' => $dayCount,
                    'coin_have' => $coinHave,
                );
            }
        }

        $payload = [[
            'message' => 'Host Data Show',
            'code' => '200',
            'host_list' => $hostList,
            'agency' => $this->serializeAgency($agency),
        ]];

        $this->cacheJsonPayload($cacheKey, $payload, 300);

        return response()->json($payload, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function AddHost(Request $request)
    {
        try {
            if (!$this->hasValidAccessToken($request)) {
                return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $actorId = $this->resolveActorId($request);
            if (!$actorId) {
                return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $code = trim((string) $request->code);
            $hostId = (int) $request->host_id;
            $phoneNumber = trim((string) $request->phone_number);
            $hostingType = (int) $request->hosting_type;

            if ($code === '' || $hostId <= 0 || $phoneNumber === '' || $hostingType <= 0) {
                return response()->json([['message' => 'Required field missing', 'code' => '422']], 422, [], JSON_UNESCAPED_UNICODE);
            }

            $agency = Agency::where('user_id', $actorId)->where('code', $code)->select('name', 'code', 'logo', 'country_id')->first();
            if (!$agency) {
                return response()->json([['message' => 'Agency Not Found', 'code' => '404']], 404, [], JSON_UNESCAPED_UNICODE);
            }

            $user = User::find($hostId);
            if (!$user) {
                return response()->json([['message' => 'User Not Found', 'code' => '404']], 404, [], JSON_UNESCAPED_UNICODE);
            }

            if ((int) $user->is_host_id === 1 || ((int) $user->is_host_id === 2 && HostData::where('user_id', $hostId)->exists())) {
                return response()->json([['message' => 'Host Already Added Or Pending', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $hostData = HostData::where('user_id', $hostId)->first();
            if (!$hostData) {
                $hostData = new HostData();
                $hostData->user_id = $hostId;
            }

            $phoneExists = HostData::where('phone', $phoneNumber)
                ->when($hostData->exists, function ($query) use ($hostData) {
                    $query->where('id', '!=', $hostData->id);
                })
                ->exists();

            if ($phoneExists) {
                return response()->json([['message' => 'Phone Number All Ready Used !!!!', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $imageUrl = $this->resolveHostImage($request->input('image'), $user);

            DB::beginTransaction();

            $hostData->agency_code = $agency->code;
            $hostData->name = $user->name;
            $hostData->phone = $phoneNumber;
            $hostData->hosting_type = $hostingType;
            $hostData->image = $imageUrl;
            $hostData->country_id = (int) ($agency->country_id ?: ($user->country_id ?: 1));
            if (!$hostData->age) {
                $hostData->age = 18;
            }
            $hostData->save();

            $user->is_host_id = 2;
            $user->country_id = $hostData->country_id;
            $user->save();

            DB::commit();

            $this->clearAgencyEndpointCaches($agency->code, [$hostId, $actorId]);

            return response()->json([['message' => 'Hosting Apply Successfully Submit ', 'code' => '200']], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([['message' => 'Internal Server Error', 'code' => '500']], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    public function HostVerify(Request $request)
    {
        try {
            if (!$this->hasValidAccessToken($request)) {
                return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $actorId = $this->resolveActorId($request);
            $userId = (int) $request->user_id;

            if (!$actorId || (int) $actorId !== $userId) {
                return response()->json([['message' => 'Unauthorized', 'code' => '401']], 401, [], JSON_UNESCAPED_UNICODE);
            }

            $agencyCode = trim((string) $request->agency_code);
            $phoneNumber = trim((string) $request->phone_number);
            $nid = trim((string) $request->nid);
            $hostingType = (int) $request->hosting_type;

            if ($agencyCode === '' || $userId <= 0 || $phoneNumber === '' || $nid === '' || $hostingType <= 0) {
                return response()->json([['message' => 'Required field missing', 'code' => '422']], 422, [], JSON_UNESCAPED_UNICODE);
            }

            $agency = Agency::where('code', $agencyCode)->first();
            if (!$agency) {
                return response()->json([['message' => 'Agency Code Invelid', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $user = User::find($userId);
            if (!$user) {
                return response()->json([['message' => 'User Not Found', 'code' => '404']], 404, [], JSON_UNESCAPED_UNICODE);
            }

            if ((int) $user->is_host_id !== 0 && HostData::where('user_id', $userId)->exists()) {
                return response()->json([['message' => 'Your Status is Waiting For Approved', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $hostData = HostData::where('user_id', $userId)->first();
            if (!$hostData) {
                $hostData = new HostData();
                $hostData->user_id = $userId;
            }

            $nidExists = HostData::where('nid', $nid)
                ->when($hostData->exists, function ($query) use ($hostData) {
                    $query->where('id', '!=', $hostData->id);
                })
                ->exists();

            if ($nidExists) {
                return response()->json([['message' => 'NID Already Used', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $phoneExists = HostData::where('phone', $phoneNumber)
                ->when($hostData->exists, function ($query) use ($hostData) {
                    $query->where('id', '!=', $hostData->id);
                })
                ->exists();

            if ($phoneExists) {
                return response()->json([['message' => 'Phone Number All Ready Used !!!!', 'code' => '401']], 200, [], JSON_UNESCAPED_UNICODE);
            }

            $imageUrl = $this->resolveHostImage($request->input('image'), $user);

            DB::beginTransaction();

            $hostData->image = $imageUrl;
            $hostData->user_id = $userId;
            $hostData->name = $user->name;
            $hostData->hosting_type = $hostingType;
            $hostData->agency_code = $agencyCode;
            $hostData->phone = $phoneNumber;
            $hostData->nid = $nid;
            $hostData->country_id = (int) ($agency->country_id ?: ($user->country_id ?: 1));
            if (!$hostData->age) {
                $hostData->age = 18;
            }
            $hostData->save();

            $user->is_host_id = 2;
            $user->country_id = $hostData->country_id;
            $user->save();

            DB::commit();

            $this->clearAgencyEndpointCaches($agencyCode, [$userId]);

            return response()->json([['message' => 'Host  Data Submit Succssfully ', 'code' => '200']], 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([['message' => 'Internal Server Error', 'code' => '500']], 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    private function hasValidAccessToken(Request $request)
    {
        return $request->access_token === "0411f0028cfb768b3a3d96ac3aa37dw3e5";
    }

    private function resolveActorId(Request $request)
    {
        $authId = Auth::id();
        $requestUserId = trim((string) $request->user_id);

        if ($authId && $requestUserId !== '' && (string) $authId !== $requestUserId) {
            return null;
        }

        if ($authId) {
            return (string) $authId;
        }

        return $requestUserId !== '' ? $requestUserId : null;
    }

    private function findAgencyByOwner($ownerId)
    {
        return Agency::where('user_id', $ownerId)
            ->select('name', 'code', 'logo', 'country_id')
            ->first();
    }

    private function serializeAgency($agency)
    {
        if (!$agency) {
            return null;
        }

        return array(
            'name' => (string) $agency->name,
            'code' => (string) $agency->code,
            'logo' => MediaPathHelper::publicUrl($agency->logo),
        );
    }

    private function resolveHostImage($base64Image, $user)
    {
        $rawImage = trim((string) $base64Image);
        if ($rawImage === '') {
            return trim((string) $user->profile) !== ''
                ? MediaPathHelper::publicUrl($user->profile)
                : MediaPathHelper::publicUrl('store/profile/default.png');
        }

        try {
            if (stripos($rawImage, 'base64,') !== false) {
                $parts = explode(',', $rawImage, 2);
                $rawImage = $parts[1] ?? '';
            }

            $binary = base64_decode($rawImage, true);
            if (!$binary) {
                return trim((string) $user->profile) !== ''
                    ? MediaPathHelper::publicUrl($user->profile)
                    : MediaPathHelper::publicUrl('store/profile/default.png');
            }

            $image = Image::make($binary)->resize(700, 700);
            $image->encode('jpg', 80);

            $targetDirectory = MediaPathHelper::ensureDirectory('store/host');
            $fileName = 'host_' . $user->id . '_' . time() . '_' . Str::random(8) . '.jpg';
            $filePath = $targetDirectory . DIRECTORY_SEPARATOR . $fileName;
            $image->save($filePath);

            return MediaPathHelper::publicUrl('store/host/' . $fileName);
        } catch (\Exception $e) {
            return trim((string) $user->profile) !== ''
                ? MediaPathHelper::publicUrl($user->profile)
                : MediaPathHelper::publicUrl('store/profile/default.png');
        }
    }

    private function clearAgencyEndpointCaches($agencyCode, array $userIds = array())
    {
        if ($agencyCode) {
            try {
                $keys = array(
                    $this->prefix . "agency_host_list_{$agencyCode}",
                    $this->prefix . "agency_host_data_{$agencyCode}_" . date('Y-m'),
                );

                foreach ((array) Redis::keys($this->prefix . "agency_host_data_{$agencyCode}_*") as $cacheKey) {
                    $keys[] = $cacheKey;
                }

                foreach (array_unique(array_filter($keys)) as $cacheKey) {
                    Redis::del($cacheKey);
                }
            } catch (\Exception $e) {
            }
        }

        foreach (array_unique(array_filter($userIds)) as $userId) {
            RedisCacheStore::clearUserCache($userId);
        }
    }

    private function cacheJsonPayload($cacheKey, array $payload, $ttlSeconds = 300)
    {
        try {
            Redis::setex($cacheKey, (int) $ttlSeconds, json_encode($payload, JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
        }
    }

    public function MyHostProfile(Request $request)
    {
         $response = array();
         $token = $request->access_token;
         $user_id = $request->user_id;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
               $user=User::find($user_id);
              
               
        
        //$user_id = 8;
        
          $date = Carbon\Carbon::now(); // Replace this with your desired date

                 $start_date = date('Y-m') . '-01';
                $end_date = date('Y-m') . '-15';
                $type=DB::table('users')->join('host_data','host_data.user_id','users.id')->where('users.id',$user_id)->select('host_data.hosting_type')->first();
                if($type)
                {
                   $hosting_type= $type->hosting_type;
                }else{
                    $hosting_type=1;
                }
            	$durations = DB::table('day_times')
				->where('user_id', $user_id)
				->where('live_time', '>=', $start_date)
                ->where('live_time', '<=', $end_date)
				->where('brd_type',$hosting_type)
				->where('day_times', '>', '00:14:59')
				->select('day_times')
				->get();

	        $totalDuration = Carbon\Carbon::createFromTime(0, 0, 0);

	        foreach ($durations as $duration) {
				$parts = explode(':', $duration->day_times);

				$hours = intval($parts[0]);
				$minutes = intval($parts[1]);
				$seconds = intval($parts[2]);

				$interval = new DateInterval("PT{$hours}H{$minutes}M{$seconds}S");
				$totalDuration->add($interval);
	        }

	        $totalDurationFormatted = $totalDuration->format('H:i:s');

	          $day_time_duration = DB::table('day_times')
                                        ->where('user_id', $user_id)
                                        ->where('live_time', '>=', $start_date)
                                        ->where('live_time', '<=', $end_date)
                                        ->where('brd_type', $type->hosting_type)
                                    ->where('day_times', '>', '00:14:59')
                                        ->select('live_time', 'day_times')
                                        ->get();
                                    
                                    $day_count = 0;
                                    $current_date = null;
                                    $total_duration = 0;
                                    
                                    foreach ($day_time_duration as $day_time_duration) {
                                        $date = Carbon\Carbon::parse($day_time_duration->live_time)->toDateString();
                                        $time = $day_time_duration->day_times;
                                    
                                        if ($current_date === null || $current_date !== $date) {
                                            // Check if the previous day's total duration exceeds 01:01:00
                                            if ($current_date !== null && $total_duration >= 3660) { // 3660 seconds = 1 hour 1 minute
                                                $day_count++;
                                            }
                                    
                                            $current_date = $date;
                                            $total_duration = 0;
                                        }
                                    
                                        $duration_parts = explode(':', $time);
                                        $hours = intval($duration_parts[0]);
                                        $minutes = intval($duration_parts[1]);
                                        $seconds = intval($duration_parts[2]);
                                        $total_duration += ($hours * 3660) + ($minutes * 60) + $seconds;
                                    }
                                    
                                    // Check the total duration of the last date
                                    if ($total_duration >= 3660) { // 3660 seconds = 1 hour 1 minute
                                        $day_count++;
                                    }

               $total_coin= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->sum('value');
                    
                    $gift_list= DB::table('gifts')->join('users','users.id','gifts.sander_id')->where('gifts.reciever_id',$user_id)->whereDate('date', '>=', $start_date)
                    ->whereDate('date', '<=', $end_date)->select('users.profile','users.name','gifts.value')->get();
              
               $total_day_count=$day_count;
               $total_hours_count=$totalDurationFormatted;
               $lest_update_profile=Carbon\Carbon::parse($user->updated_at);

               array_push($response,array('message'=>'Live Data Store  Successfully ','gift_list'=>$gift_list,'total_coin'=>$total_coin,'total_day_count'=>$total_day_count,'total_hours_count'=>$total_hours_count,'lest_update_profile'=>$lest_update_profile,'profile'=>$user->profile,'name'=>$user->name,'join'=>$user->created_at,'code'=>'200'));
               return json_encode($response,JSON_UNESCAPED_UNICODE);
                
            
        }else{
            array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        
    }
}

