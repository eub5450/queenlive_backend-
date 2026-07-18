<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\RedisCache\CacheClearHelperFromModelAuto;
class InvisibalController extends Controller
{
    public function Index()
    {
        $users=User::where('status',1)->get();
        $invisible_users=User::where('status',1)->where('is_invisible',1)->get();
        return view('backend.power.id_invisial',compact('users','invisible_users'));
    }

    public function Active(Request $request)
    {
        $user_id=$request->user_id;
        $id_number=$request->id_number;
        if ($user_id==$id_number){
            $data=User::find($user_id);
            $data->is_invisible=1;
            $data->save();
            $this->clearUserRuntimeCache($data->id);
            $notification=array(
                'messege'=>'Invisible ID Active SuccessFully',
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
            $data->is_invisible_active=0;
            $data->save();
            $this->clearUserRuntimeCache($data->id);
            $notification=array(
                'messege'=>'Invisible ID Reject SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }

    private function clearUserRuntimeCache($userId)
    {
        try {
            CacheClearHelperFromModelAuto::clearUserCaches((int) $userId, 'admin-invisible-updated');
            \RedisCacheFunction::clearUserCache((int) $userId);
        } catch (\Throwable $error) {
            // Cache clear failure must not block the committed invisible state.
        }
    }
}
