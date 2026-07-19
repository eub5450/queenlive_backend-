<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agency;
use App\Models\User;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Image;
class AgencyController extends Controller
{
    private const AGENCY_CODE_START = 1000;

    public function Create()
    {
        return view('backend.agency.create');
    }
    public function Store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'agency_name' => 'required|string|max:255',
            'agency_code' => 'nullable|string|max:255',
            'phone' => 'required',
            'photo_id' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
            'selfie' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $check_user=User::find(trim((string) $request->user_id));
        if($check_user){
           
            $photo_id_url = $this->storeAgencyImageAsWebp($request->file('photo_id'));
            $selfie_url = $this->storeAgencyImageAsWebp($request->file('selfie'));

            DB::transaction(function () use ($request, $check_user, $photo_id_url, $selfie_url) {
                $agency = new Agency;
                $agency->user_id = $check_user->id;
                $agency->name = $request->agency_name;
                $agency->code = $this->resolveAgencyCode($request->agency_code);
                $agency->logo = trim((string) $check_user->profile) !== '' ? $check_user->profile : 'store/profile/default.png';
                $agency->selfie = $selfie_url;
                $agency->photo_id = $photo_id_url;
                $agency->phone = $request->phone;
                // BUGFIX 2026-07-18: nid/country_id/status were never set here.
                // country_id silently defaulted to the schema default (1) for
                // every admin-created agency regardless of the real user's
                // country; status silently defaulted to 0 (pending) even
                // though this action's own message claims "Active
                // Successfully" and the user's is_agency flag below is set
                // immediately (Active() — the dedicated activation action —
                // sets status=1 for the same claim, so this Store() path
                // should match it).
                $agency->nid = trim((string) $request->nid);
                $agency->country_id = (int) ($check_user->country_id ?: 1);
                $agency->status = 1;
                $agency->save();

                $check_user->is_agency = 1;
                $check_user->is_coin_protal_active = 1;
                $check_user->save();
            });
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
    public function Index()
    {
        $countryId = (int)(\Auth::user()->is_admin ?? 0) === 2
            ? (int)(\Auth::user()->country_id ?? 0)
            : null;

        $agencys = Agency::orderby('id', 'desc')
            ->when($countryId, fn($q) => $q->where('country_id', $countryId))
            ->get();
        return view('backend.agency.index', compact('agencys'));
    }
    public function AgencyOff($id)
    {
      $user=User::find($id);
      $user->is_agency=0;
      $user->save();
      $notification=array(
                'messege'=>'Agency Inactive SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }public function AgencyOn($id)
    {
      $user=User::find($id);
      $user->is_agency=1;
      $user->save();
      $notification=array(
                'messege'=>'Agency Active SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function Active($id)
    {
      $agency=Agency::find($id);
      if ($agency) {
        $check_user=User::find($agency->user_id);
        if ($check_user) {
          $agency->status=1;
          $agency->save();
          $check_user->is_agency=1;
          $check_user->is_coin_protal_active=1;
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
    public function Reject($id)
    {
     $agency = Agency::find($id);

        if ($agency) {
            $check_user = User::find($agency->user_id);
        
            if ($check_user) {
                $check_user->update([
                    'is_agency' => 0,
                    'is_coin_protal_active' => 0,
                    'host_badge' => 0,
                    'comment_badge' => 0,
                    'frame' => null,
                ]);
            }
        
            $agency->delete();
        
        
        $notification=array(
                'messege'=>'Agency Reject SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
      }else{
         $notification=array(
                'messege'=>'Somthing Wrong',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
      }
    }
    public function Update($id,Request $request)
    {
        if($request->hasFile('logo')){
                $image_url = $this->storeAgencyImageAsWebp($request->file('logo'));
            }else{
                $image_url = $request->old_logo ?: 'store/profile/default.png';
            }
            $user=Agency::find($id);
            $user->name=$request->name;
            $user->logo=$image_url;
            $user->save();
            $notification=array(
                'messege'=>'Profile Update Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
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
            ->lockForUpdate()
            ->first();

        $highestCode = $latest ? max((int) $latest->code, self::AGENCY_CODE_START - 1) : self::AGENCY_CODE_START - 1;
        $next = $highestCode + 1;
        while (Agency::where('code', (string) $next)->exists()) {
            $next++;
        }

        return (string) $next;
    }

    private function storeAgencyImageAsWebp($file)
    {
        $directory = public_path('store/agency');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $fileName = gmdate('YmdHis').'-'.uniqid().'.webp';
        $relativePath = 'store/agency/'.$fileName;
        $absolutePath = public_path($relativePath);

        Image::make($file->getRealPath())
            ->orientate()
            ->resize(1400, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 60)
            ->save($absolutePath);

        return $relativePath;
    }
}

