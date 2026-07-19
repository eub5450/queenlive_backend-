<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AdminParmisiton;
use App\Models\Agency;
use App\Models\HostData;
use App\Models\DayTime;
use App\Models\PortalRecharge;
use App\Models\PortalTransfer;
use App\Models\Game\Fivestar\FivestarPots;
use App\Models\Battle\Fortune\FortunePots;
use App\Models\Gift;
use App\Models\PortalRecall;
use App\Models\VipList;
use App\Models\FuritsPotsBackup;
use App\Models\ProfilePending;
use App\Models\BanDevice;
use App\Models\Withdraw;
use App\Models\WithdrawConvartAgency;
use App\Models\Game\Grady\GradyPots;
use App\Models\ProtalToPTransfer;
use App\Models\Battle\TeenPattiPots;
use App\Models\EntryFrame;
use App\Models\OldGift;
use App\Models\MyBeg;
use App\RedisCache\CacheClearHelperFromModelAuto;
use App\Support\MediaPathHelper;
use DB;
use Carbon;
use Auth;
use Hash;
class ProfileController extends Controller
{
   private const PROFILE_HISTORY_LIMIT = 100;

   private function ensureCan(string $permissionKey): void
   {
       if (!AdminParmisiton::allowed(Auth::id(), $permissionKey, false)) {
           abort(403, 'This admin action is not allowed for this account.');
       }
   }

   public function index(Request $request)
    {
        $this->ensureCan('profile_search');

        // Validate ID parameter
        $id = $request->id;
        if (!$id) {
            return redirect()->back()->with([
                'messege' => 'Please Enter ID Number',
                'alert-type' => 'error'
            ]);
        }

        // Find user (country admins may only view users from their own country)
        $countryId = (int)(\Auth::user()->is_admin ?? 0) === 2
            ? (int)(\Auth::user()->country_id ?? 0)
            : null;

        $user = User::where('id', $id)
            ->when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->first();
        if (!$user) {
            return redirect()->back()->with([
                'messege' => 'User Not Found!!',
                'alert-type' => 'warning'
            ]);
        }

        // Get agency data
        $agency = Agency::where('user_id', $id)->first();

        // Prepare all data
        $data = [
            'user' => $user,
            'agency' => $agency,
            'agency_info' => $this->getAgencyInfo($id),
            'protal_recharge' => $this->getPortalRecharge($id),
            'recall_protal_recharge' => $this->getPortalRecall($id),
            'protal_transfer' => $this->getPortalTransfer($id),
            'protal_recharge_details' => $this->getPortalRechargeDetails($id),
            'protal_transfer_details' => $this->getPortalTransferDetails($id),
            'recharge_historys' => $this->getRechargeHistory($id),
            'monthly_recharge_historys' => $this->getMonthlyRechargeHistory($id),
            'info' => $this->getHostData($id),
            'type' => $this->getHostType($id),
            'day_time_data' => $this->getDayTimeData($id),
            'convart_history' => $this->getConvartHistory($id),
            'agency_commisiion' => $this->getAgencyCommission($id),
            'approved_balance' => $this->getApprovedBalance($id),
            'agency_convart_balance' => $this->getAgencyConvartBalance($id),
            'protal_to_protal_transfer' => $this->getPortalToPortalTransfer($id),
            'entry_frame_list' => EntryFrame::all(),
            'my_begs' => MyBeg::where('user_id', $id)->get(),
            'my_vips' => VipList::where('user_id', $id)->get(),
            'protal_to_protal_transfer_recived' => $this->getPortalToPortalTransferReceived($id),
            'check_host_balance' => $this->getHostBalanceCount($id),
            'game_history' => $this->getGameHistory($id),
            'sanding_historys' => $this->getGiftHistory($id),
            'reciving_historys' => $this->getReceivedGifts($id),
            'old_sum_sending_historys' => $this->getOldeSendingSum($id),
            'old_sum_reciving_historys' => $this->getOldeReceivedSum($id),
            'host_lists' => $agency ? $this->getHostLists($agency->code) : collect()
        ];

        return view('backend.profile.index', $data);
    }

    public function ClearDeviceIds($id)
    {
        $this->ensureCan('profile_other_ids');

        $user = User::find($id);
        if (!$user) {
            return Redirect()->back()->with([
                'messege' => 'User Not Found!!',
                'alert-type' => 'warning',
            ]);
        }

        $deviceId = $user->device_id;
        $imeiNumber = $user->imei_number;
        if (empty($deviceId) && empty($imeiNumber)) {
            return Redirect()->to('id_search?id='.$user->id)->with([
                'messege' => 'No device id found for this user',
                'alert-type' => 'warning',
            ]);
        }

        $matchedIds = User::where('id', '!=', $user->id)
            ->where(function ($query) use ($deviceId, $imeiNumber) {
                if (!empty($deviceId)) {
                    $query->orWhere('device_id', $deviceId);
                }
                if (!empty($imeiNumber)) {
                    $query->orWhere('imei_number', $imeiNumber);
                }
            })
            ->pluck('id');

        $updated = 0;
        if ($matchedIds->isNotEmpty()) {
            $updated = User::whereIn('id', $matchedIds)->update([
                'device_id' => null,
                'imei_number' => null,
            ]);

            foreach ($matchedIds as $matchedId) {
                $this->clearUserRuntimeCache($matchedId);
            }
        }

        return Redirect()->to('id_search?id='.$user->id)->with([
            'messege' => 'Cleared device id from '.$updated.' other user(s)',
            'alert-type' => 'success',
        ]);
    }

    // Helper Methods
    protected function getDayTimeData($userId)
{
    return DB::table('day_times')
        ->where('user_id', $userId)
        ->orderBy('id', 'desc')
        ->limit(self::PROFILE_HISTORY_LIMIT)
        ->get();
}
    protected function getAgencyInfo($userId)
    {
        return DB::table('host_data')
            ->join('agencies', 'agencies.code', 'host_data.agency_code')
            ->select('agencies.*')
            ->where('host_data.user_id', $userId)
            ->first();
    }

    protected function getPortalRecharge($userId)
    {
        return PortalRecharge::where('user_id', $userId)
            ->where('status', 'Approved')
            ->sum('amount');
    }

    protected function getPortalRecall($userId)
    {
        return PortalRecall::where('protal_id', $userId)
            ->sum('amount');
    }

    protected function getPortalTransfer($userId)
    {
        return PortalTransfer::where('portal_user_id', $userId)
            ->sum('amount');
    }

    protected function getPortalRechargeDetails($userId)
    {
        return PortalRecharge::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getPortalTransferDetails($userId)
    {
        return PortalTransfer::where('portal_user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getRechargeHistory($userId)
    {
        return PortalTransfer::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }
    protected function getMonthlyRechargeHistory($userId)
    {
        $start = Carbon\Carbon::now()->startOfMonth();
        $end   = Carbon\Carbon::now()->endOfMonth();
        return PortalTransfer::where('user_id', $userId)
        ->whereBetween('created_at', [$start, $end])
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getHostData($userId)
    {
        return DB::table('host_data')
            ->where('user_id', $userId)
            ->first();
    }

    protected function getHostType($userId)
    {
        return DB::table('host_data')
            ->join('users', 'users.id', 'host_data.user_id')
            ->where('users.id', $userId)
            ->select('host_data.hosting_type', 'host_data.id')
            ->first();
    }

    protected function getConvartHistory($userId)
    {
        return DB::table('withdraw_convart_agencies')
            ->where('agency_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getAgencyCommission($userId)
    {
        return DB::table('withdraw_convart_agencies')
            ->where('agency_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getApprovedBalance($userId)
    {
        return Withdraw::where('agency_id', $userId)
            ->where('status', 1)
            ->sum('agency_profit');
    }

    protected function getAgencyConvartBalance($userId)
    {
        return WithdrawConvartAgency::where('agency_id', $userId)
            ->sum('amount');
    }

    protected function getPortalToPortalTransfer($userId)
    {
        return ProtalToPTransfer::where('user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getPortalToPortalTransferReceived($userId)
    {
        return ProtalToPTransfer::where('portal_user_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getHostBalanceCount($userId)
    {
        $start_date = date('Y-m') . '-01';
        $end_date = date('Y-m') . '-31';
        
        return Withdraw::where('agency_id', $userId)
            ->whereDate('date', '>=', $start_date)
            ->whereDate('date', '<=', $end_date)
            ->distinct('host_id')
            ->count('host_id');
    }

    protected function getGameHistory($userId)
    {
        $currentDate = Carbon\Carbon::now()->format('Y-m-d');
        $twoDaysAgo = Carbon\Carbon::now()->subDays(2)->format('Y-m-d');
        
        $games = collect();
        
        // Get recent games (last 2 days)
        $games = $games->merge(
            FortunePots::where('user_id', $userId)
                ->whereDate('created_at', '>=', $twoDaysAgo)
                ->whereDate('created_at', '<=', $currentDate)
                ->get()
                ->map(function ($item) {
                    $item->game_type = 'firust';
                    return $item;
                })
        );
        
        $games = $games->merge(
            FivestarPots::where('user_id', $userId)
                ->whereDate('created_at', '>=', $twoDaysAgo)
                ->whereDate('created_at', '<=', $currentDate)
                ->get()
                ->map(function ($item) {
                    $item->game_type = 'five_game';
                    return $item;
                })
        );
        
        $games = $games->merge(
            GradyPots::where('user_id', $userId)
                ->whereDate('created_at', '>=', $twoDaysAgo)
                ->whereDate('created_at', '<=', $currentDate)
                ->get()
                ->map(function ($item) {
                    $item->game_type = 'greedy';
                    return $item;
                })
        );
        
        $games = $games->merge(
            TeenPattiPots::where('user_id', $userId)
                ->whereDate('created_at', '>=', $twoDaysAgo)
                ->whereDate('created_at', '<=', $currentDate)
                ->get()
                ->map(function ($item) {
                    $item->game_type = 'Teen_patti';
                    return $item;
                })
        );
        
        // Get backup games
        $games = $games->merge(
            FuritsPotsBackup::where('user_id', $userId)
                ->latest('id')
                ->limit(self::PROFILE_HISTORY_LIMIT)
                ->get()
                ->map(function ($item) {
                   
                    $item->game_type = $item->game_name;
                    return $item;
                })
        );
        
        return $games->sortByDesc('created_at');
    }

   protected function getGiftHistory($userId)
    {
       return $gifts = Gift::where('sander_id', $userId)
            ->orderBy('date', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
            
        // $oldGifts = OldGift::where('sander_id', $userId)
        //     ->orderBy('date', 'desc')
        //     ->get();

       
    }
    protected function getOldeReceivedSum($userId)
    {
       return $gifts = OldGift::where('reciever_id', $userId)
            ->orderBy('date', 'desc')
            ->sum('value');
            
        // $oldGifts = OldGift::where('sander_id', $userId)
        //     ->orderBy('date', 'desc')
        //     ->get();

       
    }protected function getOldeSendingSum($userId)
    {
       return $gifts = OldGift::where('sander_id', $userId)
            ->orderBy('date', 'desc')
            ->sum('value');
            
        // $oldGifts = OldGift::where('sander_id', $userId)
        //     ->orderBy('date', 'desc')
        //     ->get();

       
    }

    protected function getReceivedGifts($userId)
    {
        return Gift::where('reciever_id', $userId)
            ->orderBy('id', 'desc')
            ->limit(self::PROFILE_HISTORY_LIMIT)
            ->get();
    }

    protected function getHostLists($agencyCode)
    {
        return DB::table('host_data')
            ->join('users', 'users.id', 'host_data.user_id')
            ->where('agency_code', $agencyCode)
            ->select(
                'users.id',
                'users.name',
                'users.balance',
                'users.phone',
                'host_data.*'
            )
            ->get()
            ->map(function ($host) {
                $dayTimeData = $this->calculateHostDayTime($host->id);
                
                return (object) array_merge((array) $host, [
                    'day_count' => $dayTimeData['day_count'],
                    'totalDurationFormatted' => $dayTimeData['totalDurationFormatted']
                ]);
            });
    }

    protected function calculateHostDayTime($hostId)
    {
        $start_date = date('Y-m') . '-01';
        $end_date = date('Y-m') . '-31';
        
        $type = DB::table('host_data')
            ->where('user_id', $hostId)
            ->first();
        
        if (!$type) {
            return [
                'day_count' => 0,
                'totalDurationFormatted' => '00:00:00'
            ];
        }

        $durations = DB::table('day_times')
            ->where('user_id', $hostId)
            ->where('live_time', '>=', $start_date)
            ->where('live_time', '<=', $end_date)
            ->where('brd_type', $type->hosting_type)
            ->where('day_times', '>', '00:19:59')
            ->select('day_times', 'live_time')
            ->get();

        $totalDuration = '00:00:00';
        $day_count = 0;
        $current_date = null;
        $total_duration = 0;

        foreach ($durations as $duration) {
            $totalDuration = $this->addDurations($totalDuration, $duration->day_times);
            
            $date = Carbon\Carbon::parse($duration->live_time)->toDateString();
            
            if ($current_date === null || $current_date !== $date) {
                if ($current_date !== null && $total_duration >= 3660) {
                    $day_count++;
                }
                $current_date = $date;
                $total_duration = 0;
            }
            
            $duration_parts = explode(':', $duration->day_times);
            $total_duration += ($duration_parts[0] * 3600) + ($duration_parts[1] * 60) + $duration_parts[2];
        }

        if ($total_duration >= 3660) {
            $day_count++;
        }

        return [
            'day_count' => $day_count,
            'totalDurationFormatted' => $totalDuration
        ];
    }

    protected function addDurations($duration1, $duration2)
    {
        $time1 = explode(':', $duration1);
        $time2 = explode(':', $duration2);

        $seconds = $time1[2] + $time2[2];
        $minutes = $time1[1] + $time2[1];
        $hours = $time1[0] + $time2[0];

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
    public function RemoveVip($id)
    {
        $vip=VipList::find($id);
        if($vip){
            $user=User::find($vip->user_id);
           $user->frame=null;
           $user->is_vip=0;
           $user->entry=null;
           $user->is_invisible=0;
           $user->is_invisible_active=0;
           $user->save();
           $vip->delete();
        }

       $notification=array(
                'messege'=>'Vip Removed Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function AdminRoleUpdate($id, $role)
    {
      $this->ensureCan('profile_power_buttons');

      $user = User::find($id);
      if (!$user) {
          return Redirect()->back()->with(['messege' => 'User not found', 'alert-type' => 'error']);
      }

      $role = (int) $role;
      if (!in_array($role, [0, 1, 2, 3], true)) {
          return Redirect()->back()->with(['messege' => 'Invalid admin role', 'alert-type' => 'error']);
      }

      if ((int) $user->id === 1111120) {
          return back()->with(['messege' => 'Cannot modify superuser account.', 'alert-type' => 'error']);
      }

      if ((int) $user->id === (int) Auth::id() && (int) $user->is_admin === 1 && $role !== 1) {
          return Redirect()->back()->with(['messege' => 'You cannot remove your own main admin access', 'alert-type' => 'error']);
      }

      $user->is_admin = $role;
      $user->role = $role === 0 ? 2 : $role;
      $user->status = 1;

      if ($role === 1) {
          $user->is_bd_admin = 1;
          $user->is_app_admin = 1;
          $user->can_banned = 1;
          $user->can_call_cut = 1;
          $user->brd_off_power = 1;
          $user->comment_mute_power = 1;
          $user->kick_power = 1;
          $user->agora_access = 1;
      } elseif ($role === 0) {
          $user->is_bd_admin = 0;
          $user->is_app_admin = 0;
      }

      $user->save();
      // Keep the panel-permission row in sync so a Sub Admin / Country role set
      // from here actually gets working (limited) permissions instead of being
      // locked out or needing Main Admin (which bypasses all permissions).
      \App\Http\Controllers\Admin\AdminSettingController::applyRolePermissions($user, $role);
      $this->clearUserRuntimeCache($user->id);

      $labels = [0 => 'Normal User', 1 => 'Main Admin', 2 => 'Country Admin', 3 => 'Sub Admin'];
      return Redirect()->back()->with([
          'messege' => 'Admin role changed to ' . $labels[$role],
          'alert-type' => 'success',
      ]);
    }
    public function BrdPowerOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->brd_off_power=1;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Brd Power On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function withdraw_active($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      if($user->withdraw_active==1){
          $user->withdraw_active=0;
      }else{
          $user->withdraw_active=1;
      }
      
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Auto Withdraw Active On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function AgoraAccess($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      if($user->agora_access==1){
          $user->agora_access=0;
      }else{
          $user->agora_access=1;
      }
      
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Agora Access Status Changed Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function officalFrameAtive($id) { $this->ensureCan('profile_vip_frames_edit'); return $this->activateSpecialFrame($id, 'is_official_frame', 'official.svga', 'Official Frame'); }
    public function AdminFrameAtive($id) { $this->ensureCan('profile_vip_frames_edit'); return $this->activateSpecialFrame($id, 'is_admin_frame', 'admin.svga', 'Admin Frame'); }
    public function OfficialFrameInactive($id) { $this->ensureCan('profile_vip_frames_edit'); return $this->deactivateSpecialFrame($id, 'is_official_frame', 'official.svga', 'Official Frame'); }
    public function AdminFrameInactive($id) { $this->ensureCan('profile_vip_frames_edit'); return $this->deactivateSpecialFrame($id, 'is_admin_frame', 'admin.svga', 'Admin Frame'); }
    public function PasswordChange(Request $request, $id){
        $this->ensureCan('profile_password_daytime');

        $user=User::find($id);
        $newPassword = $request->input('new_password', $request->input('password', ''));
        if (strlen($newPassword) < 6) {
            return back()->with(['messege' => 'Password must be at least 6 characters.', 'alert-type' => 'error']);
        }
        $user->password = Hash::make($newPassword);
        $user->save();
        $notification=array(
                'messege'=>'Password Changed',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function VipActived($id,$vip)
    {
      $this->ensureCan('profile_vip_frames_edit');

      $vip=(int) $vip;
      $user=User::find($id);
      if(!$user){ return Redirect()->back()->with(['messege'=>'User not found','alert-type'=>'error']); }

      $vipRow=VipList::where('user_id',$user->id)->where('vip_no',$vip)->first();
      if($vipRow && (int)$vipRow->is_active === 1){
          $vipRow->is_active=0;
          $vipRow->save();
          $user->is_vip=0;
          if($user->frame === 'vip'.$vip.'frame.svga'){ $user->frame=''; }
          if($user->entry === 'vip'.$vip.'entry.svga'){ $user->entry=''; }
          $user->save();
          $this->clearUserRuntimeCache($user->id);
          return Redirect()->back()->with(['messege'=>'VIP Deactivated Successfully','alert-type'=>'success']);
      }

      if(!$vipRow){
          $vipRow=new VipList;
          $vipRow->vip_no=$vip;
          $vipRow->user_id=$user->id;
          $vipRow->image='store/vip/'.$vip.'.png';
          $vipRow->end_date=Carbon\Carbon::now()->addDays(15);
      }

      VipList::where('user_id',$user->id)->update(['is_active'=>0]);
      $vipRow->is_active=1;
      $vipRow->active_date=Carbon\Carbon::now();
      if(!$vipRow->end_date){ $vipRow->end_date=Carbon\Carbon::now()->addDays(15); }
      $vipRow->save();
      $user->is_vip=1;
      $user->frame='vip'.$vip.'frame.svga';
      $user->entry='vip'.$vip.'entry.svga';
      $user->save();
      $this->clearUserRuntimeCache($user->id);
      return Redirect()->back()->with(['messege'=>'VIP Activated Successfully','alert-type'=>'success']);
    }

    public function EffectActive($user_id,$id){
        $this->ensureCan('profile_vip_frames_edit');

        $user=User::find($user_id);
        $effect=EntryFrame::find($id);
        if(!$user || !$effect){ return Redirect()->back()->with(['messege'=>'User or effect not found','alert-type'=>'error']); }

        $row=MyBeg::where('user_id',$user_id)->where('store_id',$id)->first();
        if($row && (int)$row->status === 1){
            $row->status=0;
            $row->save();
            if((int)$effect->type === 1 && $user->entry === $row->effect){ $user->entry=''; }
            if((int)$effect->type !== 1 && $user->frame === $row->effect){ $user->frame=''; }
            $user->save();
            $this->clearUserRuntimeCache($user_id);
            return Redirect()->back()->with(['messege'=>'Effect Deactivated Successfully','alert-type'=>'success']);
        }

        if(!$row){
            $row=new MyBeg;
            $row->store_id=$id;
            $row->user_id=$user_id;
            $row->active_time=Carbon\Carbon::now();
            $row->expaire_time=Carbon\Carbon::now()->addDays(15);
            $row->name=$effect->name;
            $row->image=$effect->image;
            $row->effect=$effect->effect;
            $row->type=$effect->type;
        }

        MyBeg::where('user_id',$user_id)->where('type',$effect->type)->update(['status'=>0]);
        $row->status=1;
        $row->save();
        if((int)$effect->type === 1){ $user->entry=$effect->effect; } else { $user->frame=$effect->effect; }
        $user->save();
        $this->clearUserRuntimeCache($user_id);
        return Redirect()->back()->with(['messege'=>'Effect Activated Successfully','alert-type'=>'success']);
    }
    public function BrdPowerOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->brd_off_power=0;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Brd Power Off Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function sceenshortOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->sceen_short_power=1;
      $user->save();
       $notification=array(
                'messege'=>'Sceen Short On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function sceenshortOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->sceen_short_power=0;
      $user->save();
       $notification=array(
                'messege'=>'Sceen Short Off Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function OfficialIDOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->is_official_id=1;
      $user->save();
       $notification=array(
                'messege'=>'Official ID Active  Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function OfficialIDOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->is_official_id=0;
      $user->save();
       $notification=array(
                'messege'=>'Official ID InActive Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function KickPowerOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->kick_power=1;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Kick On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function KickPowerOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->kick_power=0;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Kick Off Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function CommentMuteOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->comment_mute_power=1;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Comment Mute On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } 
    public function CommentMuteOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->comment_mute_power=0;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Comment Mute Off Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function invisibalOn($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->is_invisible=1;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Invisibal On Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    } public function invisibalOff($id)
    {
      $this->ensureCan('profile_power_buttons');

      $user=User::find($id);
      $user->is_invisible=0;
      $user->is_invisible_active=0;
      $user->save();
      $this->clearUserRuntimeCache($id);
       $notification=array(
                'messege'=>'Invisibal Off Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function User()
    {
        $countryId = (int)(\Auth::user()->is_admin ?? 0) === 2
            ? (int)(\Auth::user()->country_id ?? 0)
            : null;

        $users = User::where('balance', '!=', 0)
            ->whereNotIn('id', [23825, 23826, 23827])
            ->when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->orderby('balance', 'desc')
            ->get();
        return view('backend.profile.user_balance_list',compact('users'));
    }
   public function Rank()
    {
           $countryId = (int)(\Auth::user()->is_admin ?? 0) === 2
               ? (int)(\Auth::user()->country_id ?? 0)
               : null;

           $date = Carbon\Carbon::now();

                   


            // Current Month
            $start_date = $date->copy()->startOfMonth()->format('Y-m-d');
            $end_date = $date->copy()->endOfMonth()->format('Y-m-d');
            
            // Previous Month (Dynamic)
            $previous_start_date = $date->copy()->subMonth()->startOfMonth()->format('Y-m-d');
            $previous_end_date   = $date->copy()->subMonth()->endOfMonth()->format('Y-m-d');
               
                    $data['totalSands'] = Gift::join('users', 'gifts.sander_id', '=', 'users.id')
                    ->whereDate('gifts.date', '>=', $start_date)
                    ->whereDate('gifts.date', '<=', $end_date)
                      ->when($countryId, fn($q) => $q->where('users.country_id', $countryId))
                      ->groupBy('sander_id', 'users.name', 'users.profile','users.id')
                      ->selectRaw('sander_id, sum(value) as total_sand, users.name, users.id, users.profile')
                      ->orderByDesc('total_sand')

                      ->get(); 
         
         $data['totalReciveds'] = Gift::join('users', 'gifts.reciever_id', '=', 'users.id')->whereDate('gifts.date', '>=', $start_date)
                        ->whereDate('gifts.date', '<=', $end_date)
                        ->when($countryId, fn($q) => $q->where('users.country_id', $countryId))
                        ->groupBy('reciever_id', 'users.name', 'users.profile','users.id')
                        ->selectRaw('reciever_id, sum(value) as total_sand, users.name, users.id, users.profile')
                        ->orderByDesc('total_sand')
                        ->get();

        $data['totalfamillyReciveds'] = Gift::join('agencies', 'gifts.agency_code', '=', 'agencies.code')->whereDate('gifts.date', '>=', $start_date)
                        ->whereDate('gifts.date', '<=', $end_date)
                        ->when($countryId, fn($q) => $q->where('agencies.country_id', $countryId))
                        ->groupBy('agencies.name', 'agencies.logo','agencies.code')
                        ->selectRaw('sum(value) as total_sand, agencies.code, agencies.name, agencies.logo')
                        ->orderByDesc('total_sand')
                        ->get();  
         $data['previous_totalSands'] = Gift::join('users', 'gifts.sander_id', '=', 'users.id')
                    ->whereDate('date', '>=', $previous_start_date)
                        ->whereDate('date', '<=', $previous_end_date)
                      ->when($countryId, fn($q) => $q->where('users.country_id', $countryId))
                      ->groupBy('sander_id', 'users.name', 'users.profile','users.id')
                      ->selectRaw('sander_id, sum(value) as total_sand, users.name, users.id, users.profile')
                      ->orderByDesc('total_sand')

                      ->get();
         $data['previous_totalReciveds'] = Gift::join('users', 'gifts.reciever_id', '=', 'users.id')->whereDate('date', '>=', $previous_start_date)
                        ->whereDate('date', '<=', $previous_end_date)
                        ->when($countryId, fn($q) => $q->where('users.country_id', $countryId))
                        ->groupBy('reciever_id', 'users.name', 'users.profile','users.id')
                        ->selectRaw('reciever_id, sum(value) as total_sand, users.name, users.id, users.profile')
                        ->orderByDesc('total_sand')
                        ->get();

         $data['previous_totalfamillyReciveds'] =  Gift::join('agencies', 'gifts.agency_code', '=', 'agencies.code')->whereDate('gifts.date', '>=', $previous_start_date)
                        ->whereDate('gifts.date', '<=', $previous_end_date)
                        ->when($countryId, fn($q) => $q->where('agencies.country_id', $countryId))
                        ->groupBy('agencies.name', 'agencies.logo','agencies.code')
                        ->selectRaw('sum(value) as total_sand, agencies.code, agencies.name, agencies.logo')
                        ->orderByDesc('total_sand')
                        ->get();
                
            
         
        
        return view('backend.profile.rankingList')->with($data);
    }
    public function Update(Request $request,$id)
    {
            $this->ensureCan('profile_vip_frames_edit');

            $user=User::find($id);
            if (!$user) {
                $notification=array(
                    'messege'=>'User Not Found!!',
                    'alert-type'=>'warning'
                );
                return Redirect()->back()->with($notification);
            }

            $image_url = $request->old_profile ?: $user->profile;
            $profileUploaded = false;
            if($request->hasFile('profile')){
                $request->validate([
                    'profile' => 'image|mimes:jpeg,jpg,png,webp,gif|max:5120',
                ]);

                $image = $request->file('profile');
                $extension = strtolower($image->getClientOriginalExtension() ?: $image->extension() ?: 'jpg');
                if ($extension === 'jpeg') {
                    $extension = 'jpg';
                }

                $image_name = 'profile_'.$user->id.'_'.time().'_'.bin2hex(random_bytes(4)).'.'.$extension;
                $image_path = 'store/user';
                $target_dir = base_path($image_path);

                if (!is_dir($target_dir) && !mkdir($target_dir, 0775, true) && !is_dir($target_dir)) {
                    throw new \RuntimeException('Unable to create profile upload directory: '.$target_dir);
                }

                $image->move($target_dir, $image_name);
                @chmod($target_dir.DIRECTORY_SEPARATOR.$image_name, 0664);
                $image_url = $image_path.'/'.$image_name;
                $profileUploaded = true;

                MediaPathHelper::deleteLocalFile($request->old_profile ?: $user->profile, ['store/user', 'store/profile', 'store/agency']);
            }
            $user->name=$request->name;
            if($request->teg){
            $user->host_badge=$request->teg;
            }else{
              $user->host_badge=0;  
            }
            if($request->top_value){
            $user->top_value=$request->top_value;
            }
            $user->profile=$image_url;
            if ($profileUploaded) {
                $user->new_profile='/'.ltrim($image_url, '/');
                $user->transfer_profile=0;
            }
            $user->save();
            $this->clearUserRuntimeCache($user->id);
            $notification=array(
                'messege'=>'Profile Update Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function ChangeHostingType($id)
    {
        $data=HostData::find($id);
        if($data){
            if ((int)(\Auth::user()->is_admin ?? 0) === 2) {
                $countryId = (int)(\Auth::user()->country_id ?? 0);
                $targetUser = \App\Models\User::where('id', $data->user_id)->where('country_id', $countryId)->first();
                if (!$targetUser) {
                    return back()->with(['messege' => 'Access denied: host belongs to a different country.', 'alert-type' => 'error']);
                }
            }
            if($data->hosting_type==2){
                $data->hosting_type=1;
            }else{
                $data->hosting_type=2;
            }
            $data->save();
             $notification=array(
                'messege'=>'Hosting Type Change Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
             $notification=array(
                'messege'=>'Something Wrong Data not Found!!!!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
    }
     public function DeviceBan($id)
    {
        $data=User::find($id);
        if($data){
            $ban_device=new BanDevice;
            $ban_device->device_id=$data->device_id;
            $ban_device->save();
             $notification=array(
                'messege'=>'Device Banned Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
             $notification=array(
                'messege'=>'Something Wrong Data not Found!!!!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
    }
    public function Pending()
    {
      $data=ProfilePending::all();
      return view('backend.profile.pending',compact('data'));
    }
    public function ApprovedImage($id)
    {
      $data=ProfilePending::find($id);
      if($data){
        $user=User::find($data->user_id);
        if($data->image){
        $user->profile=$data->image;
        }
        if($data->name){
        $user->name=$data->name;
        }
        $user->save();
        $data->delete();
        $notification=array(
                'messege'=>'Image Approved Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
      }else{
         $notification=array(
                'messege'=>'Something Wrong Data not Found!!!!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }

    } 
    public function RejectImage($id)
    {
      $data=ProfilePending::find($id);
      if($data){
        $data->delete();
        $notification=array(
                'messege'=>'Image Reject Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
      }else{
         $notification=array(
                'messege'=>'Something Wrong Data not Found!!!!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }

    }
      public function ChangePass(Request $request)
    {
        if (!$request->filled('password') || strlen($request->password) < 6) {
            return back()->with(['messege' => 'Password must be at least 6 characters.', 'alert-type' => 'error']);
        }
        $data=User::find(Auth::id());
        $data->password=Hash::make($request->password);
        $data->save();
        $notification=array(
            'messege'=>'Password change successfull!',
           'alert-type'=>'success'
       );
        return redirect()->back()->with($notification);
    }
    public function TopPosition($id)
    {
        $this->ensureCan('profile_power_buttons');

        $data=User::find($id);
        if($data->prosss_top==1){
          $data->prosss_top=0; 
          $data->top_value=0; 
        }else{
         $data->prosss_top=1;
         $data->top_value=1500000;
        }
        
       
        $data->save();
        $notification=array(
            'messege'=>'Top Position Changed!',
           'alert-type'=>'success'
       );
        return redirect()->back()->with($notification);
    }
    public function ProtalActive($id)
    {
        $this->ensureCan('profile_power_buttons');

        $data=User::find($id);
        $data->is_coin_protal_active=1;
        $data->save();
        $notification=array(
            'messege'=>'Protal Active Successfully',
           'alert-type'=>'success'
       );
        return redirect()->back()->with($notification);
    }public function ProtalReject($id)
    {
        $this->ensureCan('profile_power_buttons');

        $data=User::find($id);
        $data->is_coin_protal_active=0;
        $data->save();
        $notification=array(
            'messege'=>'Protal Active Successfully',
           'alert-type'=>'success'
       );
        return redirect()->back()->with($notification);
    }
    
    public function AddDayTime(Request $request,$id)
    {
        $this->ensureCan('profile_password_daytime');

        $data=new DayTime;
        $data->user_id=$id;
        $data->channelName = $request->input('channelName', '');
        $data->live_time=$request->date;
        $data->day_times=$request->time;
        $data->brd_type=$request->brd_type;
        $data->save();
        $notification=array(
            'messege'=>'Day Added  Successfully',
           'alert-type'=>'success'
       );
        return redirect()->back()->with($notification);
    }

    private function activateSpecialFrame($id, $flagColumn, $frame, $label)
    {
        $user=User::find($id);
        if(!$user){ return Redirect()->back()->with(['messege'=>'User not found','alert-type'=>'error']); }

        foreach(['is_admin_frame','is_official_frame'] as $column){
            $user->{$column}=0;
        }

        $user->{$flagColumn}=1;
        $user->frame=$frame;
        $user->save();
        $this->clearUserRuntimeCache($id);

        return Redirect()->back()->with(['messege'=>$label.' Activated','alert-type'=>'success']);
    }

    private function deactivateSpecialFrame($id, $flagColumn, $frame, $label)
    {
        $user=User::find($id);
        if(!$user){ return Redirect()->back()->with(['messege'=>'User not found','alert-type'=>'error']); }

        $user->{$flagColumn}=0;
        if($user->frame === $frame){ $user->frame=''; }
        $user->save();
        $this->clearUserRuntimeCache($id);

        return Redirect()->back()->with(['messege'=>$label.' Deactivated','alert-type'=>'success']);
    }

    private function clearUserRuntimeCache($userId)
    {
        try {
            CacheClearHelperFromModelAuto::clearUserCaches((int) $userId, 'admin-profile-updated');
        } catch (\Throwable $error) {
            // Cache clear failure must not block the admin state change.
        }
    }
}
