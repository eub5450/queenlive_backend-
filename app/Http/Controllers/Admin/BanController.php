<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Auth;
use App\Models\BanDevice;
use Carbon\Carbon;
class BanController extends Controller
{
    public function Index()
{
    // For dropdown (just ID + Name, lightweight)
    

    // For banned users (with pagination)
    $ban_ids = User::where('status', 0)
                   ->whereNotNull('ban_type')
                   ->select('id','name','profile','level','ban_type','ban_proved')
                   ->get();

    return view('backend.power.ban', compact('ban_ids'));
}
public function search(Request $request)
    {
        $search = $request->get('search');
        
        if (empty($search)) {
            return response()->json([]);
        }
        
        $users = User::where('id', 'LIKE', "%{$search}%")
                    
                    ->limit(10)
                    ->get(['id', 'name', 'email', 'phone','balance']);
        
        return response()->json($users);
    }

    public function Active(Request $request)
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
               $device->device_id=$data->imei_number;
               $device->save(); 
            }elseif($ban_type=='B'){
            $data->open_time=Carbon::now()->addDays(30)->format('Y-m-d H:i:s');
            }elseif($ban_type=='C'){
              $data->open_time=Carbon::now()->addDays(1)->format('Y-m-d H:i:s');
            }else{
            $data->open_time=Carbon::now()->addHour(6)->format('Y-m-d H:i:s');
            }
            $data->save();
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
     public function Reject($id)
    {

            $data=User::find($id);
            $data->status=1;
            $data->ban_proved=null;
            $data->ban_type=null;
            $data->ban_by=null;
       		$data->open_time=null;
                $devices=BanDevice::where('device_id',$data->imei_number)->get();
                if($devices){
               foreach($devices as $device){
                   $device->delete();
               }
            }
            
            $data->save();
            $notification=array(
                'messege'=>'ID Actived SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}

