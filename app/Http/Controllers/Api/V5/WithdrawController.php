<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon;
use App\Models\ChildAgency;
use App\Models\Agency;
use App\Models\Withdraw;
use App\Models\User;
use App\Models\DayTime;
use App\Models\WithdrawConvartAgency;
use App\Models\PortalRecharge;
use App\Models\Setting;
use DateInterval;
use DateTime; 
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Helpers\PerformanceLogger;
use App\Support\SystemSettingValueHelper;
class WithdrawController extends Controller
{
   
 const CACHE_AGENCY_LIST = 3600;        // 1 hour
    const CACHE_SETTINGS = 86400;           // 24 hours
    const CACHE_USER_DATA = 300;             // 5 minutes
    const CACHE_HOSTING_TYPE = 3600;         // 1 hour
    const CACHE_DAY_TIMES = 600;              // 10 minutes
    const CACHE_AGENCY_DETAILS = 3600;        // 1 hour
    const CACHE_MASTER_AGENCY = 3600;         // 1 hour

    /**
     * Index/Dashboard endpoint
     */
    public function Index(Request $request)
    {
        $perf = new PerformanceLogger();
        $perf->start();
        
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $cacheStatus = [];

        // Validate request
        $validationResult = $this->validateRequest($token, $user_id);
        if ($validationResult !== true) {
            $perf->end($user_id, ['auth' => 'failed'], $validationResult);
            return json_encode($validationResult, JSON_UNESCAPED_UNICODE);
        }

        // Get financial data (without previous_coin)
        $financialData = $this->getUserFinancialData($user_id);
        $setting = $this->getCachedSettings($cacheStatus);
        $withdrawAmounts = $this->getConfiguredWithdrawAmounts($setting);
        $blockedDays = $this->getConfiguredBlockedDays($setting);
        $requiredDayCount = $this->getRequiredWithdrawDayCount($setting);
        
        // Get hosting type
        $hostingData = $this->getUserHostingData($user_id, $cacheStatus);
        
        // Get duration data and calculate running days
        $durationData = $this->getUserDurationData($user_id, $hostingData['type'], null, true, $cacheStatus);
        
        // Get cached agency list
        $lists = $this->getCachedAgencyList($cacheStatus);

        // Prepare response - EXACT same format
        array_push($response, array(
            'message' => 'Host Withraw Data',
            'code' => '200',
            'super_agency_list' => $lists,
            'available_balance' => $financialData['available_balance'],
            'time' => $durationData['total_time'],
            'hosting_type' => $hostingData['hosting'],
            'day' => $durationData['running_day_count'],
            'withdraw_amount_list' => $withdrawAmounts,
            'withdraw_blocked_days' => $blockedDays,
            'withdraw_day_requirement' => $requiredDayCount
        ));
        
        $perf->end($user_id, $cacheStatus, $response);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Super Agency Withdraw endpoint
     */
    public function SuperAgencyWithdraw(Request $request)
    {
        $perf = new PerformanceLogger();
        $perf->start();
        
        $response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;
        $amount = (int) str_replace(',', '', trim((string) $request->amount));
        $super_agency_code = $request->super_agency_code;
        $super_agency_id = $request->super_agency_id;
        
        $cacheStatus = [];

        // Validate request
        $validationResult = $this->validateRequest($token, $user_id);
        if ($validationResult !== true) {
            $perf->end($user_id, ['auth' => 'failed'], $validationResult);
            return json_encode($validationResult, JSON_UNESCAPED_UNICODE);
        }

        // Get settings with cache
        $setting = $this->getCachedSettings($cacheStatus);

        if (!$setting) {
            Log::error('Withdraw settings missing', ['user_id' => $user_id]);
            return $this->errorResponse('Something Wrong . Please Contact with Support', '401', $user_id, $cacheStatus, $perf);
        }

        if ($setting->withdraw_active != 1) {
            return $this->errorResponse('Server maintenance. please try again later', '401', $user_id, $cacheStatus, $perf);
        }

        // Pre-calculation
        $pre_apps_percentage = 5;
        $pre_percentage_amount = ($pre_apps_percentage / 100) * $amount;
        $need_amount = $amount + $pre_percentage_amount;

        // Get user data
        $user = $this->getUserData($user_id, $cacheStatus);

        if (!$user) {
            Log::error('Withdraw user missing', ['user_id' => $user_id]);
            return $this->errorResponse('Something Wrong . Please Contact with Support', '401', $user_id, $cacheStatus, $perf);
        }
        
        // Get financial data (without previous_coin)
        $financialData = $this->getUserFinancialData($user_id);
        
        // Get hosting type
        $hostingData = $this->getUserHostingData($user_id, $cacheStatus);

        $allowedAmounts = $this->getConfiguredWithdrawAmounts($setting);
        $requiredDayCount = $this->getRequiredWithdrawDayCount($setting);
        $blockedDays = $this->getConfiguredBlockedDays($setting);

        // Get duration data with day calculation
        $durationData = $this->getUserDurationData($user_id, $hostingData['type'], $user, false, $cacheStatus);
        $day = $durationData['running_day_count'];

        $agencyDetails = $this->getUserAgencyDetails($user_id, $cacheStatus);
        [$scopeAllowed, $scopeOverride] = $this->evaluateWithdrawScope($user, $setting, $agencyDetails);
        if (!$scopeAllowed) {
            return $this->errorResponse('Withdraw is not available for this ID.', '401', $user_id, $cacheStatus, $perf);
        }

        // Date validation
        $today = Carbon\Carbon::now();
        $aj_day = $today->day;

        info('Withdraw Date List : ' . json_encode($blockedDays));
        info('Today Day : ' . $aj_day);

        if (!in_array($aj_day, $blockedDays, true) || $scopeOverride) {
            if ($day >= $requiredDayCount || $scopeOverride || $user->is_agency == 1) {
                if ($financialData['available_balance'] >= $need_amount) {
                    if (!empty($allowedAmounts) && in_array($amount, $allowedAmounts, true)) {
                        if ($agencyDetails || $user->is_agency == 1 || $scopeOverride) {
                            return $this->processWithdrawal(
                                $user_id, $amount, $super_agency_id, $super_agency_code,
                                $user, $agencyDetails, $hostingData['type'], 
                                $pre_percentage_amount, $need_amount, 
                                $financialData['available_balance'], $cacheStatus, $perf
                            );
                        } else {
                            return $this->errorResponse('Agency Not Found .Contact with Admin', '401', $user_id, $cacheStatus, $perf);
                        }
                    } else {
                        return $this->errorResponse('Please select a valid withdraw amount from the admin list.', '401', $user_id, $cacheStatus, $perf);
                    }
                } else {
                    return $this->errorResponse('Insufficient Balance.', '401', $user_id, $cacheStatus, $perf);
                }
            } else {
                return $this->errorResponse('Please Complete Your Total Day Time Need :' . $requiredDayCount, '401', $user_id, $cacheStatus, $perf);
            }
        } else {
            return $this->errorResponse('Today Withdraw Off.Try Again Tomorrow .', '401', $user_id, $cacheStatus, $perf);
        }
    }

    /**
     * ==================== SHARED HELPER METHODS ====================
     */

    /**
     * Validate request token and user
     */
    private function validateRequest($token, $user_id)
    {
        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            $response = array();
            array_push($response, array('message' => 'Unauthorized', 'code' => '401'));
            return $response;
        }

        if ($user_id != Auth::id()) {
            $response = array();
            array_push($response, array('message' => 'Login User And Sand User ID Not Same', 'code' => '401'));
            return $response;
        }

        return true;
    }

    /**
     * Get cached settings
     */
    private function getCachedSettings(&$cacheStatus)
    {
        $cacheKey = 'app_settings';
        $cacheStatus['settings'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        return Cache::remember($cacheKey, self::CACHE_SETTINGS, function() {
            return Setting::find(1);
        });
    }

    /**
     * Get user data with caching
     */
    private function getUserData($user_id, &$cacheStatus)
    {
        $cacheKey = "user_data_{$user_id}";
        $cacheStatus['user'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        return Cache::remember($cacheKey, self::CACHE_USER_DATA, function() use ($user_id) {
            return User::find($user_id);
        });
    }

    /**
     * Get user financial data (NO previous_coin)
     */
    private function getUserFinancialData($user_id)
    {
        $start_date = date('Y-m') . '-01';
        $end_date = date('Y-m') . '-31';

        $total_coin = DB::table('gifts')
            ->where('reciever_id', $user_id)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('value');

        $total_withdraw = Withdraw::where('host_id', $user_id)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->sum('total');

        return [
            'total_coin' => $total_coin,
            'total_withdraw' => $total_withdraw,
            'available_balance' => $total_coin - $total_withdraw  // NO previous_coin
        ];
    }

    /**
     * Get user hosting type
     */
    private function getUserHostingData($user_id, &$cacheStatus)
    {
        $cacheKey = "user_hosting_{$user_id}";
        $cacheStatus['hosting'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        $my_agency = Cache::remember($cacheKey, self::CACHE_HOSTING_TYPE, function() use ($user_id) {
            return DB::table('host_data')
                ->join('users', 'users.id', 'host_data.user_id')
                ->where('users.is_host_id', 1)
                ->where('users.id', $user_id)
                ->select('host_data.hosting_type')
                ->first();
        });

        $hosting = 'Audio';
        $type = 1;
        
        if ($my_agency && $my_agency->hosting_type == 2) {
            $hosting = 'Video';
            $type = 2;
        }

        return [
            'hosting' => $hosting,
            'type' => $type,
            'my_agency' => $my_agency
        ];
    }

    /**
     * Get user duration data and calculate running days
     */
    private function getUserDurationData($user_id, $type, $user = null, $filterByType = true, &$cacheStatus)
    {
        $cacheKey = "user_durations_{$user_id}_" . date('Y-m-d');
        $cacheStatus['durations'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        $query = DB::table('day_times')
            ->where('user_id', $user_id)
            ->select('live_time', 'day_times');
        
        if ($user && !($user->is_agency == 1 || $user->withdraw_active == 1)) {
            $query->where('brd_type', $type);
        } elseif ($filterByType) {
            // Index endpoint filters by type and > 00:14:59
            $query->where('brd_type', $type)
                  ->where('day_times', '>', '00:14:59');
        }
        
        $durations = Cache::remember($cacheKey, self::CACHE_DAY_TIMES, function() use ($query) {
            return $query->get();
        });

        // Calculate running day count
        $running_day_count = 0;
        $current_date = null;
        $total_duration = 0;
        $totalSeconds = 0;
        
        if (count($durations) > 0) {
            foreach ($durations as $duration) {
                $date = Carbon\Carbon::parse($duration->live_time)->toDateString();
                $time = $duration->day_times;
                
                if ($current_date === null || $current_date !== $date) {
                    if ($current_date !== null && $total_duration >= 3600) {
                        $running_day_count++;
                    }
                    $current_date = $date;
                    $total_duration = 0;
                }
                
                $parts = explode(':', (string) $time);
                if (count($parts) !== 3) {
                    Log::warning('Withdraw day time format invalid', [
                        'user_id' => $user_id,
                        'day_time_id' => $duration->id ?? null,
                        'day_times' => $time,
                    ]);
                    $parts = array(0, 0, 0);
                }
                $seconds = ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
                $total_duration += $seconds;
                $totalSeconds += $seconds;
            }
            
            if ($total_duration >= 3600) {
                $running_day_count++;
            }
        }

        // Calculate total time (HH:MM:SS format)
        $hours = floor($totalSeconds / 3600);
        $minutes = floor(($totalSeconds % 3600) / 60);
        $seconds = $totalSeconds % 60;
        $total_time = sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);

        return [
            'running_day_count' => $running_day_count,
            'total_time' => $total_time,
            'durations' => $durations
        ];
    }

    /**
     * Get cached agency list
     */
    private function getCachedAgencyList(&$cacheStatus)
    {
        $cacheKey = 'master_agencies_complete_list';
        $cacheStatus['agency_list'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        return Cache::remember($cacheKey, self::CACHE_AGENCY_LIST, function() {
            $processedList = array();
            
            $agencies = DB::table('child_agencies')
                ->join('agencies', 'agencies.id', '=', 'child_agencies.master_agency_id')
                ->select(
                    'agencies.id',
                    'agencies.name as master_agency',
                    'agencies.code as master_agency_code'
                )
                ->groupBy('agencies.id', 'agencies.name', 'agencies.code')
                ->orderBy('agencies.name')
                ->get();
            
            foreach ($agencies as $agency) {
                array_push($processedList, array(
                    'master_agency' => $agency->master_agency,
                    'master_agency_code' => $agency->master_agency_code,
                    'id' => $agency->id,
                ));
            }
            
            return $processedList;
        });
    }

    /**
     * Get user agency details
     */
    private function getUserAgencyDetails($user_id, &$cacheStatus)
    {
        $cacheKey = "agency_details_{$user_id}";
        $cacheStatus['agency_details'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        return Cache::remember($cacheKey, self::CACHE_AGENCY_DETAILS, function() use ($user_id) {
            $hostAgency = DB::table('host_data')
                ->join('users', 'users.id', 'host_data.user_id')
                ->join('agencies', 'agencies.code', 'host_data.agency_code')
                ->where('users.is_host_id', 1)
                ->where('users.id', $user_id)
                ->select('host_data.hosting_type', 'agencies.id as agency_id', 'agencies.code as agency_code', 'agencies.name', 'agencies.user_id')
                ->first();

            if ($hostAgency) {
                return $hostAgency;
            }

            $agencyOwner = Agency::where('user_id', $user_id)
                ->select('id as agency_id', 'code as agency_code', 'name', 'user_id')
                ->first();

            if ($agencyOwner) {
                $agencyOwner->hosting_type = 1;
            }

            return $agencyOwner;
        });
    }

    private function getConfiguredWithdrawAmounts($setting): array
    {
        $amounts = SystemSettingValueHelper::withdrawAllowedAmounts($setting, SystemSettingValueHelper::defaultWithdrawAmounts());
        $validAmounts = array_keys($this->getAmountMap(1));
        $amounts = array_values(array_intersect($amounts, $validAmounts));

        return !empty($amounts) ? $amounts : SystemSettingValueHelper::defaultWithdrawAmounts();
    }

    private function getConfiguredBlockedDays($setting): array
    {
        return SystemSettingValueHelper::withdrawBlockedDays($setting);
    }

    private function getRequiredWithdrawDayCount($setting): int
    {
        if ((int) ($setting->withdraw_without_day ?? 0) === 1) {
            return 0;
        }

        return SystemSettingValueHelper::withdrawDayRequirement($setting);
    }

    private function evaluateWithdrawScope($user, $setting, $agencyDetails): array
    {
        $userId = (int) ($user->id ?? 0);
        $blockedIds = SystemSettingValueHelper::withdrawBlockedUserIds($setting);
        if (in_array($userId, $blockedIds, true)) {
            return [false, false];
        }

        if ((int) ($user->withdraw_active ?? 0) === 1) {
            return [true, true];
        }

        $allowedIds = SystemSettingValueHelper::withdrawAllowedUserIds($setting);
        if (in_array($userId, $allowedIds, true)) {
            return [true, true];
        }

        $scope = SystemSettingValueHelper::withdrawScopeType($setting);
        if ($scope === 'all_agency_owners') {
            return [(int) ($user->is_agency ?? 0) === 1, false];
        }

        if ($scope === 'agency_hosts') {
            $configuredAgencyId = SystemSettingValueHelper::withdrawScopeAgencyId($setting);
            if (!$configuredAgencyId || !$agencyDetails) {
                return [false, false];
            }

            return [(int) ($agencyDetails->agency_id ?? 0) === $configuredAgencyId && (int) ($user->is_host_id ?? 0) === 1, false];
        }

        return [(int) ($user->is_host_id ?? 0) === 1 || (int) ($user->is_agency ?? 0) === 1, false];
    }

    /**
     * Get master agency
     */
    private function getMasterAgency($super_agency_id, &$cacheStatus)
    {
        $cacheKey = "master_agency_{$super_agency_id}";
        $cacheStatus['master_agency'] = Cache::has($cacheKey) ? 'hit' : 'miss';
        
        return Cache::remember($cacheKey, self::CACHE_MASTER_AGENCY, function() use ($super_agency_id) {
            return Agency::find($super_agency_id);
        });
    }

    /**
     * Process withdrawal
     */
    private function processWithdrawal($user_id, $amount, $super_agency_id, $super_agency_code,
                                  $user, $agencyDetails, $hosting_type, 
                                  $pre_percentage_amount, $need_amount, 
                                  $available_balance, &$cacheStatus, $perf)
{
    $response = array();
    
    $data = new Withdraw;
    $data->txid = uniqid('withdraw_'.$user_id.'_'.date('Y_m_d').'_');
    $data->host_id = $user_id;
    $data->host_name = $user->name;
    $data->agency_id = $agencyDetails->user_id;
    
    if ($super_agency_id != 0) {
        $data->is_super_agency_withdraw = 1;
        $master_agency = $this->getMasterAgency($super_agency_id, $cacheStatus);
        if (!$master_agency) {
            Log::warning('Withdraw master agency missing', [
                'user_id' => $user_id,
                'super_agency_id' => $super_agency_id,
                'super_agency_code' => $super_agency_code,
            ]);
            return $this->errorResponse('Agency Not Found .Contact with Admin', '401', $user_id, $cacheStatus, $perf);
        }
        $data->super_agency_id = $master_agency->user_id;
    } else {
        $data->withdraw_agency_id = $agencyDetails->user_id;
    }
    
    // Set amount mapping based on hosting type
    $amount_map = $this->getAmountMap($hosting_type);
    
    if (isset($amount_map[$amount])) {
        $data->basic_coin = $amount_map[$amount]['basic_coin'];
        $data->agency_profit = $amount_map[$amount]['agency_profit'];
        $data->apps_profit = $amount - $data->basic_coin - $data->agency_profit + $pre_percentage_amount;
        $data->total = $amount + $pre_percentage_amount;
    } else {
        // LOG THIS - Amount mapping missing
        \Log::error('Amount mapping not found', [
            'user_id' => $user_id,
            'amount' => $amount,
            'hosting_type' => $hosting_type,
            'available_amounts' => array_keys($amount_map)
        ]);
        return $this->errorResponse('Invalid withdrawal amount', '401', $user_id, $cacheStatus, $perf);
    }

    $data->status = 0;
    $data->date = date('Y-m-d');
    
    DB::beginTransaction();
    try {
        if ($data->save()) {
            // Bulk delete day times
            DayTime::where('user_id', $user_id)->delete();
            
            // Clear user-specific caches
            Cache::forget("user_data_{$user_id}");
            Cache::forget("user_durations_{$user_id}_" . date('Y-m-d'));
            
            DB::commit();
            
            $message = ($super_agency_id != 0) 
                ? 'Host Withraw From Super Agency' 
                : 'Host Withraw From Agency';
                
            array_push($response, array(
                'message' => $message,
                'code' => '200',
                'amount' => $amount,
                'user_id' => $user_id,
                'super_agency_id' => $super_agency_id,
                'super_agency_code' => $super_agency_code
            ));
            
            $perf->end($user_id, $cacheStatus, $response);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        // LOG THIS - Save failed without exception
        \Log::error('Withdraw save failed', [
            'user_id' => $user_id,
            'data' => $data->toArray(),
            'dirty' => $data->getDirty(),
            'exists' => $data->exists,
        ]);
        
        DB::rollBack();
        return $this->errorResponse('Something Wrong . Please Contact with Support', '401', $user_id, $cacheStatus, $perf);
        
    } catch (\Throwable $e) {
        DB::rollBack();
        
        // LOG THE EXCEPTION DETAILS
        \Log::error('Withdrawal exception', [
            'user_id' => $user_id,
            'amount' => $amount,
            'error_message' => $e->getMessage(),
            'error_file' => $e->getFile(),
            'error_line' => $e->getLine(),
            'error_trace' => $e->getTraceAsString()
        ]);
        
        return $this->errorResponse('Something Wrong . Please Contact with Support', '401', $user_id, $cacheStatus, $perf);
    }
}

    /**
     * Get amount map based on hosting type
     */
    private function getAmountMap($hosting_type)
    {
        if ($hosting_type == 2) {
            return [
                300000 => ['basic_coin' => 241071, 'agency_profit' => 28173],
                500000 => ['basic_coin' => 401785, 'agency_profit' => 46956],
                700000 => ['basic_coin' => 562500, 'agency_profit' => 65739],
                1000000 => ['basic_coin' => 803571, 'agency_profit' => 93913],
                1500000 => ['basic_coin' => 1205357, 'agency_profit' => 140869],
                2000000 => ['basic_coin' => 1607142, 'agency_profit' => 187826],
                3000000 => ['basic_coin' => 2410714, 'agency_profit' => 352173],
                4000000 => ['basic_coin' => 3214285, 'agency_profit' => 469565],
                5000000 => ['basic_coin' => 4017857, 'agency_profit' => 586956],
                6500000 => ['basic_coin' => 5223214, 'agency_profit' => 763043],
                8000000 => ['basic_coin' => 6428571, 'agency_profit' => 939130],
                10000000 => ['basic_coin' => 8035714, 'agency_profit' => 1408695],
                20000000 => ['basic_coin' => 16071428, 'agency_profit' => 2817391],
                50000000 => ['basic_coin' => 40178571, 'agency_profit' => 7826086],
                100000000 => ['basic_coin' => 80357142, 'agency_profit' => 15652173],
            ];
        } else {
            return [
                300000 => ['basic_coin' => 187500, 'agency_profit' => 28173],
                500000 => ['basic_coin' => 312500, 'agency_profit' => 46956],
                700000 => ['basic_coin' => 437500, 'agency_profit' => 65739],
                1000000 => ['basic_coin' => 625000, 'agency_profit' => 93913],
                1500000 => ['basic_coin' => 937500, 'agency_profit' => 140869],
                2000000 => ['basic_coin' => 1250000, 'agency_profit' => 187826],
                3000000 => ['basic_coin' => 1875000, 'agency_profit' => 352173],
                4000000 => ['basic_coin' => 2500000, 'agency_profit' => 469565],
                5000000 => ['basic_coin' => 3125000, 'agency_profit' => 586956],
                6500000 => ['basic_coin' => 4062500, 'agency_profit' => 763043],
                8000000 => ['basic_coin' => 5000000, 'agency_profit' => 939130],
                10000000 => ['basic_coin' => 6250000, 'agency_profit' => 1408695],
                20000000 => ['basic_coin' => 12500000, 'agency_profit' => 2817391],
                50000000 => ['basic_coin' => 31250000, 'agency_profit' => 7826086],
                100000000 => ['basic_coin' => 62500000, 'agency_profit' => 15652173],
            ];
        }
    }

    /**
     * Error response helper
     */
    private function errorResponse($message, $code, $user_id, $cacheStatus, $perf)
    {
        $response = array();
        array_push($response, array('message' => $message, 'code' => $code));
        $perf->end($user_id, $cacheStatus, $response);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }
     public function AgencyWallet(Request $request)
    {
    	$response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;       

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        		if($user_id==Auth::id()){
                        $pending_list = Withdraw::where(function ($query) use ($user_id) {
                            $query->where('withdraw_agency_id', $user_id)
                                ->where('super_agency_id', null)
                                ->where('is_super_agency_withdraw', 0)
                                ->where('status', 0);
                        })
                        ->orWhere(function ($query) use ($user_id) {
                            $query->where('super_agency_id', $user_id)
                                ->where('is_super_agency_withdraw', 1)
                                ->where('status', 0);
                        }) ->orderBy('created_at', 'desc')
                        ->get();

                        //
                        $approved_list = Withdraw::where(function ($query) use ($user_id) {
                            $query->where('withdraw_agency_id', $user_id)
                                ->where('super_agency_id', null)
                                ->where('is_super_agency_withdraw', 0)
                                ->where('status', 1);
                        })
                        ->orWhere(function ($query) use ($user_id) {
                            $query->where('super_agency_id', $user_id)
                                ->whereNotNull('super_agency_id')
                                ->where('is_super_agency_withdraw', 1)
                                ->where('status', 1);
                        })
                        ->orderBy('created_at', 'desc')
                        ->get();

        	         	$convart_list=WithdrawConvartAgency::where('agency_id',$user_id)->orderby('id','desc')->get();
        	         	
        		$approved_balance=Withdraw::where('agency_id',$user_id)->where('status',1)->sum('agency_profit');
        		$agency_convart_balance=WithdrawConvartAgency::where('agency_id',$user_id)->sum('amount');
        		$available_balance=round($approved_balance-$agency_convart_balance);
			    array_push($response,array('message'=>'Agency Withdraw wallet','code'=>'200','approved_list'=>$approved_list,'pending_list'=>$pending_list,'convart_list'=>$convart_list,'available_balance'=>$available_balance));
		 	    return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
        	array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
        }else{
        	array_push($response,array('message'=>'Unauthorized','code'=>'401'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    public function Convart(Request $request)
    {
    	$response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;       
        $amount = $request->amount;       

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
                $start_date = date('Y-m') . '-01';
                $end_date = date('Y-m') . '-31';
                $total_host_withdraw=Withdraw::where('agency_id', $user_id)->whereDate('date', '>=', $start_date)->whereDate('date', '<=', $end_date)->distinct('host_id')->count('host_id');
                if($total_host_withdraw>4){
            	$approved_balance=Withdraw::where('agency_id',$user_id)->where('status',1)->sum('agency_profit');
        		$agency_convart_balance=WithdrawConvartAgency::where('agency_id',$user_id)->sum('amount');
        		$available_balance=$approved_balance-$agency_convart_balance;
        		if ($available_balance>=$amount) {
        			$WithdrawConvartAgency=new WithdrawConvartAgency;
        			$WithdrawConvartAgency->trxid=uniqid('convart_');
        			$WithdrawConvartAgency->agency_id=$user_id;
        			$WithdrawConvartAgency->date=date('Y-m-d');
        			$WithdrawConvartAgency->amount=$amount;
        			if($WithdrawConvartAgency->save()){
        			    $users=User::where('status',1)->where('is_coin_protal_active',1)->where('id',$user_id)->first();
        			    if($users){
        			        
        			    $deposit=new PortalRecharge;
                        $deposit->user_id=$users->id;
                        $deposit->trxid=uniqid('convart_recharge_'.$user_id.'_'.date('Y_m_d').'_');
                        $deposit->amount=$amount;
                        $deposit->date=date('Y-m-d');
                        $deposit->recharge_by=Auth::id();
                        $deposit->status='Approved';
                        $deposit->is_withdraw=1;
                        $deposit->save();
                        
        		        array_push($response,array('message'=>'Convart Successfully Done!','code'=>'200'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
        			    }else{
        			        $WithdrawConvartAgency->delete();
        			    array_push($response,array('message'=>'Protal Not Actived','code'=>'200'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
        			    }
        			}else{
        			 array_push($response,array('message'=>'Must Be 4 Withdraw needed','code'=>'401'));
                     return json_encode($response,JSON_UNESCAPED_UNICODE); 
        			}
        	
        		
        		}else{
        		array_push($response,array('message'=>'insufficient Balance','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
        		}
                
            }else{
        		array_push($response,array('message'=>'Total Host Withdraw Need More then 5 .','code'=>'401'));
                return json_encode($response,JSON_UNESCAPED_UNICODE);
        		}
            }else{
             array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
             return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
        	array_push($response,array('message'=>'Unauthorized','code'=>'401'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }

    public function Approved(Request $request)
    {
    	$response = array();
        $token = $request->access_token;
        $user_id = $request->user_id;       
        $amount = $request->amount;       
        $id = $request->id;       

        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            if($user_id==Auth::id()){
            	$data=Withdraw::where('id',$id)->where('status',0)->first();
            	if ($data) {
            		$data->status=1;
            		if($data->save()){
            		    //
            		    $users=User::where('status',1)->where('is_coin_protal_active',1)->where('id',$user_id)->first();
        			    if($users){
        			        
        			    $deposit=new PortalRecharge;
                        $deposit->user_id=$users->id;
                        $deposit->withdraw_id=$data->id;
                        $deposit->trxid=uniqid('withdraw_recharge_'.$user_id.'_'.date('Y_m_d').'_');
                        $deposit->amount=$data->basic_coin;
                        $deposit->date=date('Y-m-d');
                        $deposit->recharge_by=Auth::id();
                        $deposit->status='Approved';
                        $deposit->is_withdraw=1;
                        $deposit->save();
                        
        		        array_push($response,array('message'=>'Withdraw Approved Successfully!','code'=>'200'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
        			    }else{
        			       $data->status=0;
        			       $data->save();
        			    array_push($response,array('message'=>'Protal Not Actived','code'=>'200'));
                        return json_encode($response,JSON_UNESCAPED_UNICODE);
        			    }

            		}else{
            		  array_push($response,array('message'=>'Something Wrong','code'=>'200'));
                      return json_encode($response,JSON_UNESCAPED_UNICODE); 
            		}
            		
            	}else{
                array_push($response,array('message'=>'All Ready Approved','code'=>'401'));
               return json_encode($response,JSON_UNESCAPED_UNICODE);
            	}
            }else{
             array_push($response,array('message'=>'Login User And Sand User ID Not Same','code'=>'401'));
             return json_encode($response,JSON_UNESCAPED_UNICODE);
            }
        }else{
        	array_push($response,array('message'=>'Unauthorized','code'=>'401'));
           return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
}
