<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Gift;
use App\Models\User;
use DB;
use Carbon\Carbon;
use App\Models\Withdraw;
use App\Models\Exchange;
use App\Models\GiftFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redis;
use RedisCacheFunction;
class GiftController extends Controller
{
    public function HostBalanceChack(Request $request)
    {
         $response = array();
         $token = $request->access_token;
         $host_id = $request->host_id;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $user=RedisCacheFunction::UserfindById($host_id);
           $today_data = DB::table('gifts')
            ->join('users', 'users.id', '=', 'gifts.sander_id')
            ->where('gifts.reciever_id',$host_id)
            ->whereDate('date', now()->toDateString())
            ->select('users.profile', 'users.name', 'users.id', 'users.level')
            ->selectRaw('SUM(gifts.value) as total_value')
            ->groupBy('users.profile', 'users.name', 'users.id', 'users.level')
            ->orderByDesc('total_value')
            ->skip(3)
            ->take(100)
            ->get();
            
            $top_today_data = DB::table('gifts')
            ->join('users', 'users.id', '=', 'gifts.sander_id')
            ->where('gifts.reciever_id',$host_id)
            ->whereDate('date', now()->toDateString())
            ->select('users.profile', 'users.name', 'users.id', 'users.level')
            ->selectRaw('SUM(gifts.value) as total_value')
            ->groupBy('users.profile', 'users.name', 'users.id', 'users.level')
            ->orderByDesc('total_value')
            ->limit(3)
            ->get();

            $today_total=RedisCacheFunction::getUserTodayGiftSum($host_id);
            $date = Carbon::now(); // Replace this with your desired date


                $start_date = now()->startOfMonth()->toDateString();
                    $end_date = now()->endOfMonth()->toDateString();
                
                $total_data =DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('gifts.reciever_id',$host_id)
                ->whereDate('gifts.date', '>=', $start_date)
                ->whereDate('gifts.date', '<=', $end_date)
                ->select('users.profile', 'users.name', 'users.id', 'users.level', DB::raw('SUM(gifts.value) as total_value'))
                ->groupBy('users.profile', 'users.name', 'users.id', 'users.level')
                ->orderByDesc('total_value')
                ->skip(3)
                ->take(500)
                ->get();
                
                $top_total_data =DB::table('gifts')
                ->join('users', 'users.id', '=', 'gifts.sander_id')
                ->where('gifts.reciever_id',$host_id)
                ->whereDate('gifts.date', '>=', $start_date)
                ->whereDate('gifts.date', '<=', $end_date)
                ->select('users.profile', 'users.name', 'users.id', 'users.level', DB::raw('SUM(gifts.value) as total_value'))
                ->groupBy('users.profile', 'users.name', 'users.id', 'users.level')
                ->orderByDesc('total_value')
                ->limit(3) 
                ->get();
                
             $total_gift_coin=RedisCacheFunction::getGiftBetweenSumDates($host_id,$start_date,$end_date);
               $total_withdraw=RedisCacheFunction::getUserWithdrawSumBetweenDates($host_id,$start_date,$end_date);
               $total_data_sum= $total_gift_coin-$total_withdraw;

             array_push($response,array('message'=>'Gift List Showing ','today_data'=>$today_data,'today_total'=>$today_total,'total_data'=>$total_data,'total_data_sum'=>$total_data_sum,'top_total_data'=>$top_total_data,'top_today_data'=>$top_today_data,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }
    
    public function GiftData(Request $request)
    {
         $response = array();
         $token = $request->access_token;
         if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
            $propulars=Cache::remember("gift_data_propulars", now()->addDays(2), function () {
                    return GiftFile::where('category',1)->orderBy('amount', 'asc')->get();
            });
            $luxerys=Cache::remember("gift_data_luxerys", now()->addDays(2), function () {
                return GiftFile::where('category',2)->orderBy('amount', 'asc')->get();
                });
            $fastival=Cache::remember("gift_data_fastival", now()->addDays(2), function () { 
                return GiftFile::where('category',3)->orderBy('amount', 'asc')->get(); 
                
            });
            array_push($response,array('message'=>'Gift List Showing ','propulars'=>$propulars,'luxerys'=>$luxerys,'fastival'=>$fastival,'code'=>'200'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
         }else{
          array_push($response,array('message'=>'Unauthorized','code'=>'401'));
          return json_encode($response,JSON_UNESCAPED_UNICODE);
         }
    }

    public function exchange(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = (int) $request->user_id;

        $validation = $this->validateExchangeRequest($token, $user_id);
        if ($validation !== true) {
            array_push($response, $validation);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $wallet = $this->buildExchangeWalletData($user_id);

        array_push($response, array(
            'status' => true,
            'message' => 'Exchange data found',
            'total_gift_coin' => $wallet['total_gift_coin'],
            'total_withdraw' => $wallet['total_withdraw'],
            'total_exchange' => $wallet['total_exchange'],
            'available_balance' => $wallet['available_balance'],
            'cut_percent' => $wallet['cut_percent'],
            'history' => $wallet['history'],
            'code' => '200',
        ));

        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    public function exchangestore(Request $request)
    {
        $response = array();
        $token = $request->access_token;
        $user_id = (int) $request->user_id;
        $raw_amount = $request->convert_amount ?? $request->amount;

        $validation = $this->validateExchangeRequest($token, $user_id);
        if ($validation !== true) {
            array_push($response, $validation);
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        if (!is_numeric($raw_amount)) {
            array_push($response, array(
                'status' => false,
                'message' => 'Convert amount must be numeric',
                'code' => '401',
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $convert_amount = round((float) $raw_amount, 2);
        if ($convert_amount < 1) {
            array_push($response, array(
                'status' => false,
                'message' => 'Minimum convert amount is 1',
                'code' => '401',
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $lock_key = 'queenlive:exchange_submit_lock:' . $user_id;
        $lock_value = uniqid('exchange_' . $user_id . '_', true);
        $lock_acquired = $this->acquireExchangeLock($lock_key, $lock_value, 8);
        if (!$lock_acquired) {
            array_push($response, array(
                'status' => false,
                'message' => 'Exchange request already processing. Please wait.',
                'code' => '429',
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        try {
            $result = DB::transaction(function () use ($user_id, $convert_amount) {
                $wallet = $this->buildExchangeWalletData($user_id);
                if ($wallet['available_balance'] < $convert_amount) {
                    return array(
                        'status' => false,
                        'message' => 'Insufficient balance.',
                        'code' => '401',
                    );
                }

                $cut_percent = $this->getExchangeCutPercentage();
                $receive_amount = round(
                    $convert_amount - (($convert_amount * $cut_percent) / 100),
                    2
                );

                Exchange::create(array(
                    'host_id' => $user_id,
                    'convert_amount' => $convert_amount,
                    'cut_percent' => $cut_percent,
                    'receive_amount' => $receive_amount,
                    'month_key' => Carbon::now()->format('Y-m'),
                    'status' => 1,
                    'message' => 'Exchange request submitted',
                ));

                $fresh_wallet = $this->buildExchangeWalletData($user_id);

                return array(
                    'status' => true,
                    'message' => 'Exchange request submitted successfully',
                    'total_gift_coin' => $fresh_wallet['total_gift_coin'],
                    'total_withdraw' => $fresh_wallet['total_withdraw'],
                    'total_exchange' => $fresh_wallet['total_exchange'],
                    'available_balance' => $fresh_wallet['available_balance'],
                    'cut_percent' => $fresh_wallet['cut_percent'],
                    'receive_amount' => $receive_amount,
                    'history' => $fresh_wallet['history'],
                    'code' => '200',
                );
            });
        } catch (\Throwable $exception) {
            Log::error('Exchange store failed', array(
                'user_id' => $user_id,
                'convert_amount' => $convert_amount,
                'error' => $exception->getMessage(),
            ));

            $this->releaseExchangeLock($lock_key, $lock_value);

            array_push($response, array(
                'status' => false,
                'message' => 'Exchange request failed. Please try again.',
                'code' => '500',
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        $this->releaseExchangeLock($lock_key, $lock_value);
        array_push($response, $result);
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    private function validateExchangeRequest($token, $user_id)
    {
        if ($token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            return array('status' => false, 'message' => 'Unauthorized', 'code' => '401');
        }

        if ($user_id <= 0 || $user_id != Auth::id()) {
            return array(
                'status' => false,
                'message' => 'Login User And Sand User ID Not Same',
                'code' => '401',
            );
        }

        return true;
    }

    private function buildExchangeWalletData($host_id)
    {
        $start_date = Carbon::now()->startOfMonth()->toDateString();
        $end_date = Carbon::now()->toDateString();
        $month_key = Carbon::now()->format('Y-m');
        $cut_percent = $this->getExchangeCutPercentage();

        $total_gift_coin = (float) RedisCacheFunction::getGiftBetweenSumDates(
            $host_id,
            $start_date,
            $end_date
        );
        $total_withdraw = (float) RedisCacheFunction::getUserWithdrawSumBetweenDates(
            $host_id,
            $start_date,
            $end_date
        );
        $total_exchange = (float) Exchange::where('host_id', $host_id)
            ->where('month_key', $month_key)
            ->where('status', 1)
            ->sum('convert_amount');
        $available_balance = round(
            $total_gift_coin - $total_withdraw - $total_exchange,
            2
        );

        $history = Exchange::where('host_id', $host_id)
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(function ($item) {
                return array(
                    'id' => $item->id,
                    'host_id' => (string) $item->host_id,
                    'convert_amount' => (string) $item->convert_amount,
                    'cut_percent' => (string) $item->cut_percent,
                    'receive_amount' => (string) $item->receive_amount,
                    'month_key' => (string) $item->month_key,
                    'status' => (string) $item->status,
                    'message' => $item->message,
                    'created_at' => optional($item->created_at)->toDateTimeString(),
                    'updated_at' => optional($item->updated_at)->toDateTimeString(),
                );
            })
            ->values();

        return array(
            'total_gift_coin' => round($total_gift_coin, 2),
            'total_withdraw' => round($total_withdraw, 2),
            'total_exchange' => round($total_exchange, 2),
            'available_balance' => $available_balance < 0 ? 0 : $available_balance,
            'cut_percent' => number_format($cut_percent, 2, '.', ''),
            'history' => $history,
        );
    }

    private function getExchangeCutPercentage()
    {
        try {
            $cached_percent = Cache::store('redis')->get('queenlive_exchange_cut_parcentage');
            if (is_numeric($cached_percent)) {
                return $this->normalizeExchangeCutPercentage($cached_percent);
            }
        } catch (\Throwable $exception) {
            Log::warning('Exchange cut cache read failed', array(
                'error' => $exception->getMessage(),
            ));
        }

        try {
            $setting = RedisCacheFunction::getSetting();
        } catch (\Throwable $exception) {
            Log::warning('Exchange cut percentage fallback used', array(
                'error' => $exception->getMessage(),
            ));
            $setting = null;
        }

        $raw_percent = optional($setting)->exchange_cut_parcentage;
        return $this->normalizeExchangeCutPercentage($raw_percent);
    }

    private function normalizeExchangeCutPercentage($value)
    {
        if (!is_numeric($value)) {
            return 30.00;
        }

        $normalized = round((float) $value, 2);
        if ($normalized < 0) {
            return 0.00;
        }

        if ($normalized > 100) {
            return 100.00;
        }

        return $normalized;
    }

    private function acquireExchangeLock($lock_key, $lock_value, $ttl)
    {
        try {
            return Redis::set($lock_key, $lock_value, 'EX', $ttl, 'NX') === true;
        } catch (\Throwable $exception) {
            Log::warning('Exchange lock fallback failed', array(
                'lock_key' => $lock_key,
                'error' => $exception->getMessage(),
            ));
            return Cache::add($lock_key, $lock_value, now()->addSeconds($ttl));
        }
    }

    private function releaseExchangeLock($lock_key, $lock_value)
    {
        try {
            if (Redis::get($lock_key) === $lock_value) {
                Redis::del($lock_key);
                return;
            }
            // Redis is up but didn't hold the lock — it may have been
            // acquired via the Cache fallback during a Redis outage, so
            // fall through to release the Cache lock below (was leaking
            // until TTL because of an unconditional early return here).
        } catch (\Throwable $exception) {
            Log::warning('Exchange unlock fallback failed', array(
                'lock_key' => $lock_key,
                'error' => $exception->getMessage(),
            ));
        }

        if (Cache::get($lock_key) === $lock_value) {
            Cache::forget($lock_key);
        }
    }
}
