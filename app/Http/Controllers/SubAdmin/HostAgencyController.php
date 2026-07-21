<?php

namespace App\Http\Controllers\SubAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use App\Models\User;
use App\Models\HostData;
use App\Models\Agency;
class HostAgencyController extends Controller
{
    private const AGENCY_CODE_START = 1000;

    public function HostCreate()
    {
        $agencys=DB::table('users')->join('agencies','agencies.user_id','users.id')->where('users.is_agency',1)->select('agencies.name','agencies.code')->get();
        $host=User::where('is_host_id',0)->get();
        return view('subadmin.create_host',compact('agencys','host'));
    }
    public function HostStore(Request $request)
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
            $nid_check=HostData::where('nid',$request->nid)->first();
            if ($nid_check) {
               $notification=array(
                'messege'=>'Allready Nid Used Have Host Data!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }else{
            if($request->hasFile('image')){
                $image = $request->file('image');
                $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $image_url = $image_path.$image_name;
                $image->move(base_path($image_path), $image_name);
            }else{
                $image_url = 'store/profile/default.png';
            }
             if($request->hasFile('nid')){
                $photo_id = $request->file('nid');
                $photo_id_name = uniqid().'.'.strtolower($photo_id->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $photo_id_url = $image_path.$photo_id_name;
                $photo_id->move(base_path($image_path), $photo_id_name);
            }else{
                $photo_id_url = 'store/profile/default.png';
            }
            if($request->hasFile('selfie')){
                $selfie = $request->file('selfie');
                $selfie_name = uniqid().'.'.strtolower($selfie->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $selfie_url = $image_path.$selfie_name;
                $selfie->move(base_path($image_path), $selfie_name);
            }else{
                $selfie_url = 'store/profile/default.png';
            }
            // BUGFIX 2026-07-18: country_id was never derived here, unlike
           // Admin\HostController::Store's identical flow, so every
           // subadmin-created host silently got the schema default (1)
           // instead of the target agency's real country.
           $agency=Agency::where('code',$request->agency_id)->first();
           $data=new HostData;
           $data->user_id=$request->host_id;
           $data->agency_code=$request->agency_id;
           $data->name=$user->name;
           $data->phone=$request->phone_number;
           $data->photo_id=$photo_id_url;
           $data->selfie=$selfie_url;
           $data->image=$image_url;
           $data->nid=$request->nid;
           $data->hosting_type=$request->hosting_type;
           $data->age=18;
           $data->country_id=$agency ? $agency->country_id : $user->country_id;
           $data->save();
           $user->is_host_id=1;
           if ($data->country_id) {
                $user->country_id=$data->country_id;
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
     public function AgencyCreate()
    {
        return view('subadmin.agency_create');
    }
    public function AgencyStore(Request $request)
    {
        $check_user=User::find($request->user_id);
        if($check_user){
           
             if($request->hasFile('photo_id')){
                $photo_id = $request->file('photo_id');
                $photo_id_name = uniqid().'.'.strtolower($photo_id->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $photo_id_url = $image_path.$photo_id_name;
                $photo_id->move(base_path($image_path), $photo_id_name);
            }else{
                $photo_id_url = 'store/profile/default.png';
            }
            if($request->hasFile('selfie')){
                $selfie = $request->file('selfie');
                $selfie_name = uniqid().'.'.strtolower($selfie->getClientOriginalExtension());
                $image_path = 'store/agency/';
                $selfie_url = $image_path.$selfie_name;
                $selfie->move(base_path($image_path), $selfie_name);
            }else{
                $selfie_url = 'store/profile/default.png';
            }
           $agency=new Agency;
           $agency->user_id=$check_user->id;
           $agency->name=$request->agency_name;
           $agency->code=$this->resolveAgencyCode($request->agency_code);
           $agency->logo='store/profile/default.png';
           $agency->selfie=$selfie_url;
           $agency->photo_id=$photo_id_url;
           $agency->phone=$request->phone;
           // BUGFIX 2026-07-18: same gap as Admin\AgencyController::Store —
           // nid/country_id/status were never set, so country_id silently
           // defaulted to 1 for every subadmin-created agency and status
           // silently stayed 0 (pending) despite the "Active Successfully"
           // message and the immediate is_agency=1 below.
           $agency->nid=trim((string) $request->nid);
           $agency->country_id=(int) ($check_user->country_id ?: 1);
           $agency->status=1;
           $agency->save();
           $check_user->is_agency=1;
           $check_user->save();
           $notification=array(
                'messege'=>'Agency Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
            $notification=array(
                'messege'=>'User Not Found This ID',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
       

    }
    public function AgencyIndex()
    {
        $agencys=Agency::orderby('id','desc')->get();
        return view('subadmin.agency_index',compact('agencys'));
    }
    public function GetUser($id)
    {
        $user=User::find($id);
        $next_agency_code=$this->nextAgencyCode();
        return response()->json(['success' => 'User Find','user'=>$user,'next_agency_code'=>$next_agency_code]);
    }
    private function resolveAgencyCode($requestedCode = null)
    {
        $code = trim((string) $requestedCode);
        $normalizedCode = ctype_digit($code) ? (string) ((int) $code) : '';
        if ($normalizedCode !== '' && (int) $normalizedCode >= self::AGENCY_CODE_START && !Agency::where('code', $normalizedCode)->exists()) {
            return $normalizedCode;
        }

        return $this->nextAgencyCode();
    }

    private function nextAgencyCode()
    {
        $latest = Agency::query()
            ->whereNotNull('code')
            ->orderByRaw('CAST(code AS UNSIGNED) DESC')
            ->first();

        $highestCode = $latest ? max((int) $latest->code, self::AGENCY_CODE_START - 1) : self::AGENCY_CODE_START - 1;
        $next = $highestCode + 1;
        while (Agency::where('code', (string) $next)->exists()) {
            $next++;
        }

        return (string) $next;
    }

    public function AgencyActive($id)
    {
      $agency=Agency::find($id);
      if ($agency) {
        $check_user=User::find($agency->user_id);
        if ($check_user) {
          $agency->status=1;
          $agency->save();
          $check_user->is_agency=1;
          // Portal decoupled from agency (boss): do not auto-enable the coin
          // portal on agency activation — use the dedicated admin portal action.
          $check_user->save();
          $notification=array(
                'messege'=>'Agency Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
          $notification=array(
                'messege'=>'User Not Found',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
        }
      }else{
         $notification=array(
                'messege'=>'Somthing Wrong',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }
    }
    public function ChangeHostingType($id){
        $data=HostData::find($id);
        if($data){
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
}

