<?php

namespace App\Http\Controllers\SubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProfilePending;
use App\Models\HostData;
use App\Models\User;
use App\Models\Agency;
use App\Models\Gift;
use Auth;
use DB;
use Carbon;
use App\Models\UserLive;
use Kreait\Firebase\Contract\Database;

class DashbordController extends Controller
{
  public function __construct(Database $database)
    {
        $this->database = $database;
    }
    public function Home()
    {
      $total_agency = Agency::count();
      $active_agency = Agency::where('status', 1)->count();
      $pending_agency = Agency::where(function ($query) {
        $query->whereNull('status')->orWhere('status', 0);
      })->count();
      $active_host = User::where('is_host_id', 1)->count();
      $pending_host = User::where('is_host_id', 2)->where('status', 1)->count();
      $total_users = User::count();
      $pending_profile = ProfilePending::count();
      $active_live = UserLive::count();
      $data = ProfilePending::orderBy('id', 'desc')->limit(12)->get();

      return view('subadmin.home', compact(
        'data',
        'total_agency',
        'active_agency',
        'pending_agency',
        'active_host',
        'pending_host',
        'total_users',
        'pending_profile',
        'active_live'
      ));
    }
    public function PendingProfile()
    {
      $data=ProfilePending::all();
      return view('subadmin.profile_pending',compact('data'));
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
    public function PendingHost()
    {
        $users=DB::table('users')->join('host_data','host_data.user_id','users.id')->select('users.*','host_data.country_id')->where('users.is_host_id',2)->where('users.status',1)->orderby('host_data.id','desc')->get();
        return view('subadmin.pending_host',compact('users'));
    }
    public function Ranking()
    {
           $date = Carbon\Carbon::now(); // Replace this with your desired date
           

                   $start_date = date('Y-m') . '-01';
         
               $end_date = date('Y-m') . '-31';
                   $data['totalSands'] = Gift::join('users', 'gifts.sander_id', '=', 'users.id')
                    ->whereDate('gifts.date', '>=', $start_date)
                    ->whereDate('gifts.date', '<=', $end_date) 
                      ->groupBy('sander_id', 'users.name', 'users.profile','users.id') 
                      ->selectRaw('sander_id, sum(value) as total_sand, users.name, users.id, users.profile') 
                      ->orderByDesc('total_sand')
           
                      ->get(); 
         
         $data['totalReciveds'] = Gift::join('users', 'gifts.reciever_id', '=', 'users.id')->whereDate('gifts.date', '>=', $start_date)
                        ->whereDate('gifts.date', '<=', $end_date) 
                        ->groupBy('reciever_id', 'users.name', 'users.profile','users.id') 
                        ->selectRaw('reciever_id, sum(value) as total_sand, users.name, users.id, users.profile') 
                        ->orderByDesc('total_sand') 
                        ->get();                                                     
           
         $data['totalfamillyReciveds'] = Gift::join('host_data', 'gifts.reciever_id', '=', 'host_data.user_id')->join('agencies', 'host_data.agency_code', '=', 'agencies.code')->whereDate('gifts.date', '>=', $start_date)
                        ->whereDate('gifts.date', '<=', $end_date) 
                       ->groupBy('host_data.agency_code', 'agencies.name', 'agencies.logo','agencies.code') 
                       ->selectRaw('sum(value) as total_sand, agencies.name, agencies.code, agencies.logo') 
                       ->orderByDesc('total_sand')                         
                       ->get();
                
             
         
        
        return view('subadmin.rankingList')->with($data);
    }
    public function BannedIndex()
    {
        $users=User::all();
        $ban_ids=User::where('status',0)->where('ban_type','!=',null)->get();
        return view('subadmin.id_banned',compact('users','ban_ids'));
    }
    public function BannedActive(Request $request)
    {
       
        $validated = $request->validate([
            'user_id' => 'required',
            'id_number' => 'required',
            'ban_type' => 'required',
            'proved' => 'required',
        ]);
        $user_id=$request->user_id;
        $id_number=$request->id_number;
        $ban_type=$request->ban_type;
        if ($user_id==$id_number){
            
             if($request->hasFile('proved')){
            $image = $request->file('proved');
            $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
            $image_path = 'store/bannedproved/';
            $proved_url = $image_path.$image_name;
            $image->move(base_path($image_path), $image_name);
            }else{
            $proved_url = null;
            }
            $data=User::find($user_id);
            $data->is_invisible=0;
            $data->status=0;
            $data->ban_type=$ban_type;
            $data->ban_proved=$proved_url;
            $data->ban_by=Auth::id();
          
            if($ban_type=='A'){
               $device=new BanDevice;
               $device->device_id=$data->device_id;
               $device->save(); 
            }elseif($ban_type=='B'){
            $data->open_time=Carbon\Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
            }elseif($ban_type=='C'){
              $data->open_time=Carbon\Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            }else{
            $data->open_time=Carbon\Carbon::now()->addHour()->format('Y-m-d H:i:s');
            }
            $data->save();
            // return $data;
            $notification=array(
                'messege'=>'ID Banned SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
            $notification=array(
                'messege'=>'Id Not Mached!!',
                'alert-type'=>'warning'
            );
            return Redirect()->back()->with($notification);
        }
    }
     public function BannedReject($id)
    {

            $data=User::find($id);
            $data->status=1;
            $data->ban_proved=null;
            $data->ban_type=null;
            $data->ban_by=null;
          $data->open_time=null;
            if($data->ban_type=='A'){
               $device=new BanDevice;
               $device->device_id=$data->device_id;
               $device->save(); 
            }
            $data->save();
            $notification=array(
                'messege'=>'ID Actived SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function LiveList()
    {
       $lives=UserLive::orderby('id','desc')->get();
      return view('subadmin.live_index',compact('lives'));
    }
    
    public function LiveOff($id)
    {
      $live=UserLive::find($id);
      
        $row = array();
    
        $row['channelName'] = $live->channelName;
        $row['status'] = strval(0);
        $row['host_id'] = strval($live->user_id);
        $push_count_ref = $this->database->getReference('official_brd_off/' . $live->channelName .'/'. $live->user_id);
        $push_count_ref->set($row);
          $live->delete();
       $notification=array(
                'messege'=>'Live Remove SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    
    }

    public function ProfileView($id)
    {
      if ($id) {
        $user=User::find($id);
        if ($user) {

          $data['user']=User::find($id);
            $data['agency']=Agency::where('user_id',$id)->first();
            $data['agency_info']=DB::table('host_data')->join('agencies','agencies.code','host_data.agency_code')->select('agencies.*')->where('host_data.user_id',$id)->first();
            $data['info']=DB::table('host_data')->where('user_id',$id)->first();
          
            return view('subadmin.profile')->with($data);
        }else{
           $notification=array(
                'messege'=>'User Not Found!!',
                'alert-type'=>'warning'
            );
            return Redirect()->back()->with($notification);
        }
      
      }else{
         $notification=array(
                'messege'=>'Please Enter ID Number',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }
    }
     public function SearchProfileView(Request $request)
    {
      $id=$request->id;
      if ($id) {
        $user=User::find($id);
        if ($user) {

          $data['user']=User::find($id);
            $data['agency']=Agency::where('user_id',$id)->first();
            $data['agency_info']=DB::table('host_data')->join('agencies','agencies.code','host_data.agency_code')->select('agencies.*')->where('host_data.user_id',$id)->first();
            $data['info']=DB::table('host_data')->where('user_id',$id)->first();

            return view('subadmin.profile')->with($data);
        }else{
           $notification=array(
                'messege'=>'User Not Found!!',
                'alert-type'=>'warning'
            );
            return Redirect()->back()->with($notification);
        }
      
      }else{
         $notification=array(
                'messege'=>'Please Enter ID Number',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }
        
    }
    public function RejectHost($id)
    {
        $user=User::find($id);
        $user->is_host_id=0;
        $user->save();
        $data=HostData::where('user_id',$id)->first();
        if ($data) {
            $data->delete();
            // code...
        }

        $notification=array(
                'messege'=>'Host Reject SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function ActiveHost($id)
    {
        $user=User::find($id);
        $user->is_host_id=1;
        $user->save();
        $notification=array(
                'messege'=>'Host Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}

