<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Notification;
use App\Models\PortalRecharge;
use Auth;
use App\Models\PortalRecall;
class ProtalController extends Controller
{
    public function Create()
    {
        $users=User::where('status',1)->where('is_coin_protal_active',0)->get();
        return view('backend.protal.create',compact('users'));
    }
    public function Store(Request $request)
    {
        $id=$request->user_id;
        $user=User::where('id',$id)->where('master_protal_id','!=',1)->first();
        if ($user) {
            $user->is_coin_protal_active=1;
            if ($user->save()) {
               if($request->deposit==0){
                   $notification=array(
                    'messege'=>'Protal Active SuccessFully!',
                    'alert-type'=>'success'
                );
                   return Redirect()->back()->with($notification);
               }else{
                $deposit=new PortalRecharge;
                $deposit->user_id=$user->id;
                $deposit->trxid=rand(2586,589898);
                $deposit->amount=$request->deposit;
                $deposit->date=date('Y-m-d');
                $deposit->recharge_by=Auth::id();
                $deposit->status='Approved';
                $deposit->save();
                
                 $notification=new Notification;
                 $notification->user_id=$user->id;
                 $notification->date=date('Y-m-d');
                 $notification->message=$request->deposit.' Point Deposit Successfully Added On Your Protal .TrxID: '.$deposit->trxid;
                 $notification->save();
                $notification=array(
                    'messege'=>'Protal Active SuccessFully With deposit!',
                    'alert-type'=>'success'
                );
                   return Redirect()->back()->with($notification);
               }
            }else{
                $notification=array(
                'messege'=>'Something Wrong!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }

        }else{
            $notification=array(
                'messege'=>'User Data Not Found!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }

    }
    public function Index()
    {
        $users=User::where('status',1)->where('is_coin_protal_active',1)->get();
        return view('backend.protal.index',compact('users'));
    }
     public function PortalRechargeIndex()
    {
        return $this->Recharge();
    }

    public function checkOTP(Request $request)
    {
        return redirect()->to('recharge_otp');
    }

        public function Recharge($sessionOTP = null)
        {
             $users=User::where('status',1)->where('is_coin_protal_active',1)->get();
                return view('backend.protal.recharge_create',compact('users'));
    }


    public function RechargeStore(Request $request)
    {
        $canRecharge = Auth::check() && (
            (int) Auth::id() === 1111120 ||
            \App\Models\AdminParmisiton::allowed(Auth::id(), 'sidebar_protal_recharge', false)
        );

        if ($canRecharge) {
         
        $deposit=new PortalRecharge;
        $deposit->user_id=$request->user_id;
        $deposit->trxid=rand(2586,589898);
        $deposit->amount=$request->deposit;
        $deposit->date=date('Y-m-d');
        $deposit->recharge_by=Auth::id();
        $deposit->status='Approved';
        $deposit->save();
        $notification=new Notification;
        $notification->user_id=$request->user_id;
        $notification->date=date('Y-m-d');
        $notification->message=$request->deposit.' Point Deposit Successfully Added On Your Protal .TrxID: '.$deposit->trxid;
        $notification->save();
        $notification=array(
            'messege'=>'Protal Active SuccessFully With deposit!',
            'alert-type'=>'success'
        );
         return redirect()->to('recharge_otp')->with($notification);
           # code...
        }else{
             $notification = new Notification;
            $notification->user_id = 22222;
            $notification->date = date('Y-m-d');
            $notification->message = 'UnAuthorize User Submit A Coin Genrator Request '. optional(Auth::user())->name .'& id Number :-' .Auth::id();
            $notification->save();

            return Redirect()->back()->with([
                'messege' => 'You do not have portal recharge permission.',
                'alert-type' => 'error',
            ]);

        }
    }
    public function RechargeIndex()
    {
        $data=PortalRecharge::orderby('id','desc')->get();
        return view('backend.protal.recharge_index',compact('data'));
    }
  public function Recall(Request $request)
    {
        $data=new PortalRecall;
        $data->amount=$request->amount;
        $data->protal_id=$request->user_id;
        $data->date=date('Y-m-d');
        $data->user_id=Auth::id();
        if ($data->save()) {
        $deposit=new PortalRecharge;
        $deposit->user_id='22222';
        $deposit->trxid='Recall_'.rand(2586,589898);
        $deposit->amount=$request->amount;
        $deposit->date=date('Y-m-d');
        $deposit->recharge_by=Auth::id();
        $deposit->status='Approved';
        $deposit->save();
        }

         $notification=array(
            'messege'=>'Protal Recall SuccessFully With deposit!',
            'alert-type'=>'success'
        );
        return Redirect()->back()->with($notification);
        
    } 
     public function MasterRecharge()
    {
        $users=User::where('master_protal_id',1)->get();
        return view('backend.protal.master_protal',compact('users'));
    } 
    public function MasterRechargeStore(Request $request)
    {
        $deposit=new PortalRecharge;
        $deposit->user_id=$request->user_id;
        $deposit->master_protal_id=$request->user_id;
        $deposit->trxid='master_reseller-'.rand(2586,589898);
        $deposit->amount=$request->deposit;
        $deposit->date=date('Y-m-d');
        $deposit->recharge_by=Auth::id();
        $deposit->status='Approved';
        $deposit->save();
        $notification=array(
            'messege'=>'Master Protal Deposit SuccessFully ',
            'alert-type'=>'success'
        );
        return Redirect()->back()->with($notification);
    }
}
