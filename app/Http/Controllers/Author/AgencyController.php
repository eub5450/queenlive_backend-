<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\HostData;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Image;

class AgencyController extends Controller
{
    private const AGENCY_CODE_START = 1000;

    public function Create()
    {
        return view('author.agency.create');
    }

    public function Store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'agency_name' => 'required|string|max:255',
            'agency_code' => 'required',
            'phone' => 'required',
            'photo_id' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
            'selfie' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $countryId = (int) Auth::user()->country_id;
        $checkUser = User::find($request->user_id);

        if (!$checkUser) {
            return Redirect()->back()->with([
                'messege' => 'User not found for this ID',
                'alert-type' => 'error',
            ]);
        }

        if (!empty($checkUser->country_id) && (int) $checkUser->country_id !== $countryId) {
            return Redirect()->back()->with([
                'messege' => 'This user belongs to another country',
                'alert-type' => 'error',
            ]);
        }

        if (Agency::where('user_id', $checkUser->id)->exists()) {
            return Redirect()->back()->with([
                'messege' => 'Agency already exists for this user',
                'alert-type' => 'error',
            ]);
        }

        $photoIdUrl = $this->storeAgencyFile($request, 'photo_id');
        $selfieUrl = $this->storeAgencyFile($request, 'selfie');

        $agency = new Agency;
        $agency->user_id = $checkUser->id;
        $agency->name = $request->agency_name;
        $agency->code = $this->resolveAgencyCode($request->agency_code);
        $agency->logo = 'store/profile/default.png';
        $agency->selfie = $selfieUrl;
        $agency->photo_id = $photoIdUrl;
        $agency->phone = $request->phone;
        $agency->country_id = $countryId;
        $agency->status = 0;
        $agency->save();

        $checkUser->country_id = $countryId;
        $checkUser->is_agency = 0;
        $checkUser->save();

        return Redirect()->back()->with([
            'messege' => 'Agency request added under this country',
            'alert-type' => 'success',
        ]);
    }

    public function Index()
    {
        $agencys = Agency::orderBy('id', 'desc')
            ->where('country_id', Auth::user()->country_id)
            ->get();

        return view('author.agency.index', compact('agencys'));
    }

    public function Info(Request $request)
    {
        $countryId = (int) Auth::user()->country_id;
        $user = User::find($request->id);
        $nextAgencyCode = $this->nextAgencyCode();

        if (!$user) {
            return response()->json(['error' => 'User not found']);
        }

        if (!empty($user->country_id) && (int) $user->country_id !== $countryId) {
            return response()->json(['error' => 'User belongs to another country']);
        }

        return response()->json([
            'success' => 'User found',
            'user' => $user,
            'next_agency_code' => $nextAgencyCode,
        ]);
    }

    public function Active(Request $request)
    {
        $agency = Agency::where('id', $request->id)
            ->where('country_id', Auth::user()->country_id)
            ->first();

        if (!$agency) {
            return Redirect()->back()->with([
                'messege' => 'Agency not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $checkUser = User::find($agency->user_id);
        if (!$checkUser) {
            return Redirect()->back()->with([
                'messege' => 'Agency owner user not found',
                'alert-type' => 'error',
            ]);
        }

        $agency->status = 1;
        $agency->save();

        $checkUser->is_agency = 1;
        $checkUser->country_id = $agency->country_id;
        $checkUser->save();

        $hostIds = HostData::where('agency_code', $agency->code)->pluck('user_id')->all();
        HostData::where('agency_code', $agency->code)->update(['country_id' => $agency->country_id]);
        if (!empty($hostIds)) {
            User::whereIn('id', $hostIds)->update(['country_id' => $agency->country_id]);
        }

        return Redirect()->back()->with([
            'messege' => 'Agency activated and country propagated',
            'alert-type' => 'success',
        ]);
    }

    public function Reject(Request $request)
    {
        $agency = Agency::where('id', $request->id)
            ->where('country_id', Auth::user()->country_id)
            ->first();

        if (!$agency) {
            return Redirect()->back()->with([
                'messege' => 'Agency not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $owner = User::find($agency->user_id);
        if ($owner && (int) $owner->country_id === (int) $agency->country_id) {
            $owner->is_agency = 0;
            $owner->save();
        }

        $agency->delete();

        return Redirect()->back()->with([
            'messege' => 'Agency rejected successfully',
            'alert-type' => 'success',
        ]);
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
    private function storeAgencyFile(Request $request, $field)
    {
        if (!$request->hasFile($field)) {
            return 'store/profile/default.png';
        }

        $file = $request->file($field);
        $directory = public_path('store/agency');
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $name = gmdate('YmdHis') . '-' . uniqid() . '.webp';
        $relativePath = 'store/agency/' . $name;
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
