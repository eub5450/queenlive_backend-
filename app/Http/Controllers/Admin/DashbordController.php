<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\GameBalanceWithdraw;
use App\Models\Game\Fivestar\FivestarSetting;
use App\Models\Game\Grady\GradySetting;
use App\Models\Gift;
use App\Models\PortalRecharge;
use App\Models\PortalTransfer;
use App\Models\Battle\Fortune\FortuneSetting;
use App\Models\Setting;
use App\Models\Agency;
use App\Models\Comment;
use App\Models\Withdraw;
use App\Models\WithdrawConvartAgency;
use App\Models\PortalRecall;
use App\Models\LuckyGiftSetting;
use App\Models\EntryFrameProfit;
use App\Models\Battle\TeenPattiSetting;
use Kreait\Firebase\Contract\Database;
use App\Models\BanDevice;
use App\Models\OldGift;
use Auth;
use App\Models\Chat;
use App\Models\UserLive;
use App\Models\CoinBeg;
use App\Models\CoinBegRecived;
use App\Models\Kick;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Pusher;
class DashbordController extends Controller
{
    private const THOMAS_DASHBOARD_CONNECTION = 'thomas_game_dashboard';

     public function __construct(Database $database)
    {
        $this->database = $database;
    }
    function restartVultrServer()
    {
        $apiKey = '6R2EUIZTIAIB3UN4VMBSJ74UCF5226BVCFIA';
        $instanceId = '12a0e3a6-a7e1-4f3d-9a86-95f34e076792';
    
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type'  => 'application/json',
        ])->post("https://api.vultr.com/v2/instances/{$instanceId}/reboot");
    
          $notification=array(
                'messege'=>'Real Time Server Reboot SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
 

 public function index()
    {
        $data = $this->getDashboardData();
       
        
        // Get initial chat data (only 20 items)
        $chats = Chat::select('id', 'sander_id', 'receiver_id', 'text', 'created_at')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        $chatUsers = $this->getChatUsersByIds($chats);
        
        $data['initialChats'] = $chats->map(function($chat) use ($chatUsers) {
            $sender = $chatUsers->get($chat->sander_id);
            $receiver = $chatUsers->get($chat->receiver_id);

            return [
                'id' => $chat->id,
                'sender_id' => $chat->sander_id,
                'sender_name' => $sender ? $this->truncateText($sender->name, 15) : 'Unknown User',
                'receiver_id' => $chat->receiver_id,
                'receiver_name' => $receiver ? $this->truncateText($receiver->name, 15) : 'Unknown User',
                'private_channel' => $this->privateChatChannel($chat->sander_id, $chat->receiver_id),
                'message' => $this->truncateText($chat->text, 40),
                'full_message' => $chat->text,
                'time' => $chat->created_at->diffForHumans(),
                'timestamp' => $chat->created_at->timestamp
            ];
        });
        
        // Get initial comment data (only 15 items)
        $comments = Comment::with(['user:id,name,profile', 'receiver:id,name,profile'])
            ->where('message', 'NOT LIKE', '%Has Joined%')
            ->where('message', 'NOT LIKE', '%Has Leaved%')
            ->orderBy('id', 'desc')
            ->limit(15)
            ->get();
        
        $data['initialComments'] = $comments->map(function($comment) {
            return [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => $comment->user->name ?? 'Deleted User',
                'user_profile' => $comment->user->profile ?? url('default-profile.png'),
                'receiver_id' => $comment->reciever_id,
                'receiver_name' => $comment->receiver->name ?? 'Deleted User',
                'receiver_profile' => $comment->receiver->profile ?? url('default-profile.png'),
                'receiver_online' =>  false,
                'channel_name' => $comment->channelName ?? 'General',
                'private_channel' => $this->privateRoomChannel($comment->channelName ?? ''),
                'message' => $this->truncateText($comment->message, 50),
                'full_message' => $comment->message,
                'time' => $comment->created_at->diffForHumans(),
                'timestamp' => $comment->created_at->timestamp
            ];
        });
        
        return view('backend.home', $data);
    }

    /**
     * Get New Chats via AJAX Polling
     */
    public function getNewChats(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        
        $newChats = Chat::select('id', 'sander_id', 'receiver_id', 'text', 'created_at')
            ->where('id', '>', $lastId)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        $chatUsers = $this->getChatUsersByIds($newChats);

        $newChats = $newChats->map(function($chat) use ($chatUsers) {
                $sender = $chatUsers->get($chat->sander_id);
                $receiver = $chatUsers->get($chat->receiver_id);

                return [
                    'id' => $chat->id,
                    'sender_id' => $chat->sander_id,
                    'sender_name' => $sender ? $this->truncateText($sender->name, 15) : 'Unknown User',
                    'receiver_id' => $chat->receiver_id,
                    'receiver_name' => $receiver ? $this->truncateText($receiver->name, 15) : 'Unknown User',
                    'private_channel' => $this->privateChatChannel($chat->sander_id, $chat->receiver_id),
                    'message' => $this->truncateText($chat->text, 40),
                    'full_message' => $chat->text,
                    'time' => $chat->created_at->diffForHumans()
                ];
            });
        
        if ($newChats->isEmpty()) {
            return response()->json(['html' => '', 'last_id' => $lastId]);
        }
        
        $html = view('backend.partials.chat-items', ['chats' => $newChats])->render();
        
        return response()->json([
            'html' => $html,
            'last_id' => $newChats->max('id'),
            'count' => $newChats->count()
        ]);
    }

    /**
     * Get New Comments via AJAX Polling
     */
    public function getNewComments(Request $request)
    {
        $lastId = $request->get('last_id', 0);
        
        $newComments = Comment::with(['user:id,name,profile', 'receiver:id,name,profile'])
            ->where('id', '>', $lastId)
            ->where('message', 'NOT LIKE', '%Has Joined%')
            ->where('message', 'NOT LIKE', '%Has Leaved%')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function($comment) {
                return [
                    'id' => $comment->id,
                    'user_id' => $comment->user_id,
                    'user_name' => $comment->user->name ?? 'Deleted User',
                    'user_profile' => $comment->user->profile ?? url('default-profile.png'),
                    'receiver_id' => $comment->reciever_id,
                    'receiver_name' => $comment->receiver->name ?? 'Deleted User',
                    'receiver_profile' => $comment->receiver->profile ?? url('default-profile.png'),
                    'receiver_online' =>  false,
                    'channel_name' => $comment->channelName ?? 'General',
                    'private_channel' => $this->privateRoomChannel($comment->channelName ?? ''),
                    'message' => $this->truncateText($comment->message, 50),
                    'full_message' => $comment->message,
                    'time' => $comment->created_at->diffForHumans()
                ];
            });
        
        if ($newComments->isEmpty()) {
            return response()->json(['html' => '', 'last_id' => $lastId]);
        }
        
        $html = view('backend.partials.comment-items', ['comments' => $newComments])->render();
        
        return response()->json([
            'html' => $html,
            'last_id' => $newComments->max('id'),
            'count' => $newComments->count()
        ]);
    }

    /**
     * Get Dashboard Statistics Data (Cached)
     */
    private function getDashboardData()
    {
        $today = now()->toDateString();
        $cached = Cache::remember('admin.dashboard.metrics.v2', now()->addSeconds(30), function () use ($today) {
            $gift = Gift::sum('value');
            $oldGift = OldGift::sum('value');
            $todayGift = Gift::whereDate('date', $today)->sum('value');
            $todayOldGift = OldGift::whereDate('date', $today)->sum('value');
            $todayGiftSum = $todayGift + $todayOldGift;

            return [
                'total_coin_beg' => CoinBeg::sum('amount'),
                'today_coin_beg' => CoinBeg::whereDate('created_at', $today)->sum('amount'),
                'users_balance' => User::sum('balance'),
                'today_user' => User::whereDate('created_at', $today)->count(),
                'total_gift' => $oldGift + $gift,
                'today_sanding' => Gift::whereNotNull('sander_id')->whereDate('date', $today)->sum('value')
                    + OldGift::whereNotNull('sander_id')->whereDate('date', $today)->sum('value'),
                'today_reciving' => Gift::whereNotNull('reciever_id')->whereDate('date', $today)->sum('value')
                    + OldGift::whereNotNull('reciever_id')->whereDate('date', $today)->sum('value'),
                'today_gift_sum' => $todayGiftSum,
                'total_portal_recharge' => PortalRecharge::sum('amount'),
                'total_withdraw_generate' => PortalRecharge::where('is_withdraw', 1)->sum('amount'),
                'total_recall' => PortalRecharge::where('is_recall', 1)->sum('amount'),
                'total_portal_transfer' => PortalTransfer::sum('amount'),
                'today_portal_transfer' => PortalTransfer::whereDate('created_at', $today)->sum('amount'),
                'today_withdraw' => Withdraw::whereDate('created_at', $today)->sum('total'),
                'total_portal_recall' => PortalRecall::sum('amount'),
                'total_agency' => Agency::count(),
                'active_host' => User::where('is_host_id', 1)->count(),
                'total_users' => User::count(),
                'game_balance_withdraw' => GameBalanceWithdraw::where('type', 'withdraw')->sum('amount'),
                'game_balance_deposit' => GameBalanceWithdraw::where('type', 'deposit')->sum('amount'),
                'today_game_balance_withdraw' => GameBalanceWithdraw::where('type', 'withdraw')
                    ->whereDate('created_at', $today)
                    ->sum('amount'),
                'today_game_balance_deposit' => GameBalanceWithdraw::where('type', 'deposit')
                    ->whereDate('created_at', $today)
                    ->sum('amount'),
                'EntryFrameProfit' => EntryFrameProfit::sum('amount'),
                'approved_balance' => Withdraw::where('status', 1)->sum('agency_profit'),
                'withdraw_app_profit_today' => Withdraw::where('status', 1)
                    ->whereDate('created_at', $today)
                    ->sum('apps_profit'),
                'withdraw_app_profit_total' => Withdraw::where('status', 1)
                    ->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
                    ->sum('apps_profit'),
                'agency_convart_balance' => WithdrawConvartAgency::sum('amount'),
            ];
        });

        $data = $cached;
        $data['protal_sand'] = $data['total_portal_transfer'] - $data['total_recall'];

        // Game balances stay fresh so admin toggles reflect immediately after redirect.
        $data['fruts_game_balance'] = FortuneSetting::find(1);
        $data['setting'] = Setting::find(1);
        $data['five_game_balance'] = FivestarSetting::find(1);
        $data['greedy_game_balance'] = GradySetting::find(1);
        $data['teenpatti_game_balance'] = TeenPattiSetting::find(1);
        $data['lucky_gift'] = LuckyGiftSetting::find(1);

        $data['game_pro_balance'] = round(
            ($data['fruts_game_balance']->game_balance ?? 0) +
            ($data['fruts_game_balance']->second_balance ?? 0) +
            ($data['fruts_game_balance']->third_balance ?? 0) +
            ($data['five_game_balance']->game_balance ?? 0) +
            ($data['greedy_game_balance']->game_balance ?? 0) +
            ($data['greedy_game_balance']->second_balance ?? 0) +
            ($data['greedy_game_balance']->third_balance ?? 0) +
            ($data['teenpatti_game_balance']->game_balance ?? 0) +
            ($data['teenpatti_game_balance']->second_balance ?? 0) +
            ($data['teenpatti_game_balance']->third_balance ?? 0) +
            ($data['lucky_gift']->balance ?? 0)
        );

        $thomasGameProfit = $this->getThomasGameProfitMetrics();
        $localGameProfitTotal = (float) $data['game_balance_deposit'] - (float) $data['game_balance_withdraw'];
        $localGameProfitToday = (float) $data['today_game_balance_deposit'] - (float) $data['today_game_balance_withdraw'];

        $data['game_profit_total'] = round($localGameProfitTotal + $thomasGameProfit['total_profit']);
        $data['game_profit_today'] = round($localGameProfitToday + $thomasGameProfit['today_profit']);
        $data['game_profit_breakdown'] = [
            'local_total' => round($localGameProfitTotal),
            'local_today' => round($localGameProfitToday),
            'thomas_total' => round($thomasGameProfit['total_profit']),
            'thomas_today' => round($thomasGameProfit['today_profit']),
            'thomas_total_bet' => round($thomasGameProfit['total_bet']),
            'thomas_total_payout' => round($thomasGameProfit['total_payout']),
        ];
        $data['game_profit_status'] = $thomasGameProfit['status'];

        $data['total_serve'] = round(
            $data['users_balance'] + 
            $data['total_gift'] + 
            ($data['fruts_game_balance']->game_balance ?? 0) + 
            ($data['fruts_game_balance']->third_balance ?? 0) + 
            ($data['five_game_balance']->game_balance ?? 0) + 
            ($data['greedy_game_balance']->game_balance ?? 0) + 
            ($data['teenpatti_game_balance']->third_balance ?? 0) + 
            ($data['teenpatti_game_balance']->game_balance ?? 0) + 
            ($data['greedy_game_balance']->third_balance ?? 0) + 
            ($data['greedy_game_balance']->second_balance ?? 0) + 
            ($data['fruts_game_balance']->second_balance ?? 0) + 
            ($data['teenpatti_game_balance']->second_balance ?? 0) + 
            ($data['lucky_gift']->balance ?? 0)
        );
        
        // Calculate loss/profit
        $data['loss_profit'] = $data['protal_sand'] - $data['total_serve'];
        
        return $data;
    }

    private function getThomasGameProfitMetrics(): array
    {
        $empty = [
            'total_profit' => 0.0,
            'today_profit' => 0.0,
            'total_bet' => 0.0,
            'total_payout' => 0.0,
            'status' => 'Thomas data unavailable',
        ];

        try {
            $conn = $this->thomasGameConnection();
            if (!Schema::connection(self::THOMAS_DASHBOARD_CONNECTION)->hasTable('bd_game_final_settlements')) {
                return $empty;
            }

            $query = $conn->table('bd_game_final_settlements');
            $todayQuery = $conn->table('bd_game_final_settlements');
            $hasCreatedAt = Schema::connection(self::THOMAS_DASHBOARD_CONNECTION)
                ->hasColumn('bd_game_final_settlements', 'created_at');

            if ($hasCreatedAt) {
                $todayQuery->whereDate('created_at', now()->toDateString());
            } else {
                $todayQuery->whereRaw('1 = 0');
            }

            return [
                'total_profit' => (float) $query->sum('net_house_result'),
                'today_profit' => (float) $todayQuery->sum('net_house_result'),
                'total_bet' => (float) $conn->table('bd_game_final_settlements')->sum('total_bet_amount'),
                'total_payout' => (float) $conn->table('bd_game_final_settlements')->sum('total_payout_amount'),
                'status' => 'Live Thomas settlement data included',
            ];
        } catch (\Throwable $e) {
            return $empty;
        }
    }

    private function thomasGameConnection()
    {
        $env = $this->readEnvFile('/var/www/queenlive/subdomains/thomasgamecompanyltd.queenlive.site/current/.env');

        Config::set('database.connections.' . self::THOMAS_DASHBOARD_CONNECTION, [
            'driver' => 'mysql',
            'host' => $env['DB_HOST'] ?? '127.0.0.1',
            'port' => $env['DB_PORT'] ?? '3306',
            'database' => $env['DB_DATABASE'] ?? '',
            'username' => $env['DB_USERNAME'] ?? '',
            'password' => $env['DB_PASSWORD'] ?? '',
            'unix_socket' => $env['DB_SOCKET'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
            'engine' => null,
        ]);

        DB::purge(self::THOMAS_DASHBOARD_CONNECTION);

        return DB::connection(self::THOMAS_DASHBOARD_CONNECTION);
    }

    private function readEnvFile(string $path): array
    {
        if (!is_readable($path)) {
            return [];
        }

        $values = [];
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $value = trim($value);
            if ((strpos($value, '"') === 0 && substr($value, -1) === '"') || (strpos($value, "'") === 0 && substr($value, -1) === "'")) {
                $value = substr($value, 1, -1);
            }

            $values[trim($key)] = $value;
        }

        return $values;
    }

    /**
     * Truncate Text Helper
     */
    private function truncateText($text, $limit = 20)
    {
        if (strlen($text) <= $limit) return $text;
        return substr($text, 0, $limit) . '...';
    }

    private function getChatUsersByIds($chats)
    {
        $userIds = $chats->pluck('sander_id')
            ->merge($chats->pluck('receiver_id'))
            ->filter()
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('id', $userIds)
            ->get(['id', 'name'])
            ->keyBy('id');
    }

    private function privateRoomChannel($channelName): string
    {
        $channelName = trim((string) $channelName);
        if ($channelName === '') {
            return 'private-*-room.channel';
        }

        $roomType = '*';
        try {
            $live = UserLive::where('channelName', $channelName)->select('type')->first();
            $roomType = $this->normalizeRoomType($live->type ?? '');
        } catch (\Throwable $e) {
            $roomType = '*';
        }

        return 'private-' . $roomType . '-room.' . $channelName;
    }

    private function privateChatChannel($senderId, $receiverId): string
    {
        $ids = array_values(array_filter([
            trim((string) $senderId),
            trim((string) $receiverId),
        ], function ($id) {
            return $id !== '';
        }));

        sort($ids, SORT_NATURAL);

        return !empty($ids)
            ? 'private-chat.' . implode('.', $ids)
            : 'private-chat.channel';
    }

    private function normalizeRoomType($type): string
    {
        $type = strtolower(trim((string) $type));
        if (in_array($type, ['1', 'audio', 'audio_room', 'audioroom'], true)) {
            return 'audio';
        }
        if (in_array($type, ['2', 'video', 'video_room', 'videoroom'], true)) {
            return 'video';
        }
        if (in_array($type, ['3', 'multi', 'multi_room', 'multiroom'], true)) {
            return 'multi';
        }

        return '*';
    }
  public function Version()
  {
  	$data=Setting::find(1);
    $data->app_version=70;
    $data->save();
    $notification=array(
                'messege'=>'App Version Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }
   public function FortuneIDBlock(Request $request)
  {
  	$data = FortuneSetting::find(1);

        $data->block_id = $request->block_id;
        $data->winner_id = $request->winner_id;
        $data->lock_parcent = $request->lock_parcent;
        
        // Logic to determine presser_lock value
        if ($request->block_id == 0 && $request->winner_id == 0) {
            $data->presser_lock = 0;
        } else {
            $data->presser_lock = 1;
        }
        
        $data->save();

    $notification=array(
                'messege'=>'ID Block  SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }
  public function VIPoffer(Request $request)
  {
  	$data=Setting::find(1);

    
    if($data->vip_discount==0){
    $data->vip_discount=1;
    }else{
      $data->vip_discount=0;
    }
    $data->save();
    $notification=array(
                'messege'=>'Vip Operation SuccessFully Done',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }   
  public function WithdrawWithoutDay(Request $request)
  {
  	$data=Setting::find(1);

    
    if($data->withdraw_without_day==0){
    $data->withdraw_without_day=1;
    }else{
      $data->withdraw_without_day=0;
    }
    $data->save();
    Cache::forget('app_settings');
    $notification=array(
                'messege'=>'Withdraw System Change SuccessFully ',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  } 
  public function RechargeOffer(Request $request)
  {
  	$data=Setting::find(1);

    
    if($data->recharge_offer_reward==0){
    $data->recharge_offer_reward=1;
    }else{
      $data->recharge_offer_reward=0;
    }
    $data->save();
    $notification=array(
                'messege'=>'Recharge Offer SuccessFully Changerd',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }public function WithdrawActive(Request $request)
  {
  	$data=Setting::find(1);

    
    if($data->withdraw_active==0){
    $data->withdraw_active=1;
    }else{
      $data->withdraw_active=0;
    }
    $data->save();
    Cache::forget('app_settings');
    $notification=array(
                'messege'=>'Withdraw Status SuccessFully Changerd',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
  }
  public function CommentUpdate()
  {
      $comments=Comment::where('message','!=','Has Joined')->where('message','!=','Has Leaved')->orderby('id','desc')->limit(15)->get();
      return view('backend.comment',compact('comments'));
  }
   public function chat()
{
   
        $chat_data_all=Chat::all();
        return view('backend.chat', compact('chat_data_all'));
        
   
}

private function deleteOldRecordsAsync($record_keys)
{
    try {
        foreach ($record_keys as $key) {
            $this->database->getReference('PartnerChats/' . $key)->remove();
        }
    } catch (\Exception $e) {
        \Log::error('Failed to delete old records: ' . $e->getMessage());
    }
}
  public function EmargencyIDBanned($id,$host){
      $user=User::find($id);
      $device=new BanDevice;
      $device->device_id=$user->device_id;
      $device->save(); 
      $user->is_invisible=0;
      $user->status=0;
      $user->ban_type="A";
      $user->ban_by=Auth::id();
      $user->save();
      $check_live=UserLive::where('user_id',$host)->first();
      if($check_live){
          $response = array();
            $kick=new Kick;
            $kick->user_id=$id;
            $kick->channelName=$check_live->channelName;
            $kick->host_id=$host;
            $kick->save();
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
                array_push($response,array('message'=>'Kick Successfully ','channelName'=>$check_live->channelName,'user_id'=>$id,'code'=>'200'));
                  $pusher->trigger('audio_kick', $check_live->channelName, $response);
      }
      $notification=array(
                'messege'=>'ID Banned SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
      
  }
  
public function WeeklyUser()
{
    $users = User::where('created_at', '>=', now()->subDays(7))
        ->whereHas('portalTransfers') // First filter users with transfers
        ->withCount(['portalTransfers as recharge_count'])
        ->withSum('portalTransfers as recharge_sum', 'amount')
        ->select('id', 'name', 'balance', 'level', 'profile', 'created_at')
        ->get()
        ->map(function ($user) {
            return [
                'user_id' => $user->id,
                'profile' => $user->profile,
                'name' => $user->name,
                'balance' => $user->balance,
                'level' => $user->level,
                'recharge_count' => $user->recharge_count,
                'recharge_sum' => $user->recharge_sum ?: 0,
                'date' => $user->created_at->format('Y-m-d H:i')
            ];
        });

    return view('backend.profile.weekly_user', ['users' => $users]);
}

}
