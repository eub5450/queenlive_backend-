<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\HostData;
use App\Models\Agency;
use App\Models\luckyStar;
use App\Support\MediaPathHelper;
use DB;
use Pusher;
class HostController extends Controller
{
    public function index()
    {
        $users=User::where('is_host_id',1)->where('status',1)->orderby('id','desc')->get();
        return view('backend.host.index',compact('users'));
    }
    public function Pending()
    {
        $users=DB::table('users')->join('host_data','host_data.user_id','users.id')->select('users.*','host_data.country_id')->where('users.is_host_id',2)->where('users.status',1)->orderby('host_data.id','desc')->get();
        return view('backend.host.pending_host',compact('users'));
    }
    public function LuckyStarPending()
    {
       $users = DB::table('lucky_stars')
    ->join('users', 'users.id', 'lucky_stars.host_id')
    ->join('host_data', 'host_data.user_id', 'users.id')
    ->select('users.*', 'host_data.country_id')
    ->where('lucky_stars.status', 0)
    ->where('users.status', 1)
    ->orderBy('lucky_stars.host_id', 'desc')
    ->get();
        return view('backend.host.lucky_star_pending',compact('users'));
    }
    public function LuckyStarActiveList()
    {
       $users = DB::table('lucky_stars')
    ->join('users', 'users.id', 'lucky_stars.host_id')
    ->join('host_data', 'host_data.user_id', 'users.id')
    ->select('users.*', 'host_data.country_id')
    ->where('lucky_stars.status',1)
    ->where('users.status', 1)
    ->orderBy('lucky_stars.host_id', 'desc')
    ->get();
        return view('backend.host.lucky_star',compact('users'));
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
     public function LuckyStarActived($id)
    {
        $user=User::find($id);
        $user->host_badge='BP Lucky Star';
        $user->prosss_top=1;
        $user->save();
        $lucky=luckyStar::where('host_id',$id)->first();
        $lucky->status=1;
        $lucky->save();
         $global_txt = array();
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
                        $message = "$user->name Now BP Lucky Star.";
                        $image='https://queenlive.site/store/profile/default.png';
                      
                        array_push($global_txt,array('message'=>$message,'image'=>$image));
                        $pusher->trigger('golbal_banner','golbal_banner',$global_txt);
        $notification=array(
                'messege'=>'Lucky Star Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
     public function LuckyStarReject($id)
    {
          $user=User::find($id);
        $user->host_badge='0';
        $user->prosss_top=0;
        $user->save();
        $lucky=luckyStar::where('host_id',$id)->first();
        $lucky->delete();
      
        $notification=array(
                'messege'=>'Lucky Star Reject SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
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
            return Redirect('/pending_host')->with($notification);
    }
    public function View($id)
    {
        $user=User::find($id);
         $data=DB::table('host_data')->join('agencies','agencies.code','host_data.agency_code')->select('host_data.*','agencies.code','agencies.logo')->where('host_data.user_id',$id)->first();
        return view('backend.host.view',compact('user','data'));
    }
    public function Tranfer()
    {
        $users=User::where('is_host_id',1)->where('status',1)->orderby('id','desc')->get();
        $agencys=Agency::orderby('id','desc')->get();
        return view('backend.host.transfer',compact('users','agencys'));
    }
    public function AgencyInfo($id)
    {
        $host_data=HostData::where('user_id',$id)->first();
        $data=Agency::where('code',$host_data->agency_code)->first();
        return response()->json(['success' => 'Data Find','data'=>$data]);
    }
    public function HostTransferd(Request $request)
    {
        $host_id=$request->host_id;
        $agency_id=$request->agency_id;
        $user=User::find($host_id);
        $agency=Agency::find($agency_id);
        if ($user) {
            $host_data=HostData::where('user_id',$host_id)->first();
            if ($host_data) {
                $host_data->agency_code=$agency->code;
                $host_data->save();
                $notification=array(
                'messege'=>'Host Tranfer SuccessFully!',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
            }else{
                $notification=array(
                'messege'=>'Host Data Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }
            // code...
        }else{
            $notification=array(
                'messege'=>'Host User Data Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
    }
    public function Create()
    {
        $agencys=DB::table('users')->join('agencies','agencies.user_id','users.id')->where('users.is_agency',1)->select('agencies.name','agencies.code')->get();
        $host=User::where('is_host_id',0)->get();
        return view('backend.host.create',compact('agencys','host'));
    }
    public function Store(Request $request)
    {
        $check_host_data=HostData::where('user_id',$request->host_id)->first();
        if ($check_host_data) {
             $notification=array(
                'messege'=>'Allready Have Host Data!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }else{
            $user=User::find($request->host_id);
            if (!$user) {
               $notification=array(
                'messege'=>'Host User Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }

            $nidNumber = trim((string) $request->input('nid_number'));
            $phoneNumber = trim((string) $request->input('phone_number'));
            $agency = Agency::where('code', $request->agency_id)->first();

            if ($nidNumber === '' || $phoneNumber === '') {
               $notification=array(
                'messege'=>'Phone number and NID number are required!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }

            $nid_check=HostData::where('nid',$nidNumber)->first();
            if ($nid_check) {
               $notification=array(
                'messege'=>'Allready Nid Used Have Host Data!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }else{
            if($request->hasFile('image')){
                $image_url = MediaPathHelper::moveUploadedFile($request->file('image'), 'store/host');
            }else{
                $image_url = trim((string) $user->profile) !== '' ? $user->profile : 'store/profile/default.png';
            }
             if($request->hasFile('nid')){
                $photo_id_url = MediaPathHelper::moveUploadedFile($request->file('nid'), 'store/host');
            }else{
                $photo_id_url = 'store/profile/default.png';
            }
            if($request->hasFile('selfie')){
                $selfie_url = MediaPathHelper::moveUploadedFile($request->file('selfie'), 'store/host');
            }else{
                $selfie_url = 'store/profile/default.png';
            }
            $data=new HostData;
           $data->user_id=$request->host_id;
           $data->agency_code=$request->agency_id;
           $data->name=$user->name;
           $data->phone=$phoneNumber;
           $data->photo_id=$photo_id_url;
           $data->selfie=$selfie_url;
           $data->image=$image_url;
           $data->nid=$nidNumber;
           $data->hosting_type=$request->hosting_type;
           $data->age=18;
           $data->country_id = $agency ? $agency->country_id : $user->country_id;
           $data->save();
           $user->is_host_id=1;
           if ($data->country_id) {
                $user->country_id = $data->country_id;
           }
           $user->save();
            $notification=array(
                'messege'=>'host Approved SuccessFully!',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
            }
           
        }
    }
    
    
}

