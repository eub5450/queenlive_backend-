<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
class OfficialController extends Controller
{
    public function Index()
    {
        $users=User::where('status',1)->get();
        $official_ids=User::where('status',1)->where('is_official_id',1)->get();
        return view('backend.power.official_id',compact('users','official_ids'));
    }
    public function Active(Request $request)
    {
        $user_id=$request->user_id;
        $id_number=$request->id_number;
        if ($user_id==$id_number){
            $data=User::find($user_id);
            $data->is_invisible=1;
            $data->is_official_id=1;
            $data->ban_power=1;
            $data->brd_off_power=1;
            $data->save();
            $notification=array(
                'messege'=>'Official ID Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
            $notification=array(
                'messege'=>'Id Not Mached SuccessFully',
                'alert-type'=>'warning'
            );
            return Redirect()->back()->with($notification);
        }
    }
    public function Reject($id)
    {

            $data=User::find($id);
             $data->is_invisible=0;
            $data->is_official_id=0;
            $data->ban_power=0;
            $data->brd_off_power=0;
            $data->save();
            $notification=array(
                'messege'=>'Official ID Reject SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}
