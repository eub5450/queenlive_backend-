<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Gift;
use App\Models\HostData;
use App\Models\PortalRecharge;
use App\Models\PortalTransfer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HostController extends Controller
{
    public function Create()
    {
        $countryId = $this->countryId();

        $agencys = Agency::where('country_id', $countryId)
            ->orderBy('name')
            ->get(['name', 'code']);

        $host = User::where('is_host_id', 0)
            ->where('id', '!=', Auth::id())
            ->where(function ($query) use ($countryId) {
                $query->whereNull('country_id')->orWhere('country_id', $countryId);
            })
            ->orderBy('id', 'desc')
            ->get(['id', 'name', 'email', 'phone', 'country_id']);

        return view('author.host.create', compact('agencys', 'host'));
    }

    public function Store(Request $request)
    {
        $countryId = $this->countryId();
        $agency = Agency::where('code', $request->agency_id)
            ->where('country_id', $countryId)
            ->first();

        if (!$agency) {
            return Redirect()->back()->with([
                'messege' => 'Agency not found for this country',
                'alert-type' => 'error',
            ]);
        }

        if (HostData::where('user_id', $request->host_id)->exists()) {
            return Redirect()->back()->with([
                'messege' => 'Host data already exists',
                'alert-type' => 'error',
            ]);
        }

        $user = User::find($request->host_id);
        if (!$user) {
            return Redirect()->back()->with([
                'messege' => 'User not found',
                'alert-type' => 'error',
            ]);
        }

        if (!empty($user->country_id) && (int) $user->country_id !== $countryId) {
            return Redirect()->back()->with([
                'messege' => 'This user belongs to another country',
                'alert-type' => 'error',
            ]);
        }

        if (HostData::where('nid', $request->nid)->exists()) {
            return Redirect()->back()->with([
                'messege' => 'NID already used by another host',
                'alert-type' => 'error',
            ]);
        }

        $data = new HostData;
        $data->user_id = $request->host_id;
        $data->agency_code = $request->agency_id;
        $data->name = $user->name;
        $data->phone = $request->phone_number;
        $data->photo_id = $this->storeHostFile($request, 'nid');
        $data->selfie = $this->storeHostFile($request, 'selfie');
        $data->image = $this->storeHostFile($request, 'image');
        $data->nid = $request->nid;
        $data->hosting_type = (int) $request->hosting_type === 2 ? 2 : 1;
        $data->age = 18;
        $data->country_id = $agency->country_id;
        $data->save();

        $user->is_host_id = 2;
        $user->country_id = $agency->country_id;
        $user->save();

        return Redirect()->back()->with([
            'messege' => 'Host request added under this country',
            'alert-type' => 'success',
        ]);
    }

    public function Index()
    {
        $users = User::where('is_host_id', 1)
            ->where('status', 1)
            ->where('id', '!=', Auth::id())
            ->where('country_id', $this->countryId())
            ->orderBy('id', 'desc')
            ->get();

        return view('author.host.index', compact('users'));
    }

    public function Pending()
    {
        $users = DB::table('users')
            ->join('host_data', 'host_data.user_id', '=', 'users.id')
            ->select('users.*', 'host_data.country_id')
            ->where('host_data.country_id', $this->countryId())
            ->where('users.is_host_id', 2)
            ->where('users.status', 1)
            ->orderBy('host_data.id', 'desc')
            ->get();

        return view('author.host.pending_host', compact('users'));
    }

    public function Search()
    {
        return view('author.host.search');
    }

    public function Profle(Request $request)
    {
        if (!$request->id) {
            return Redirect()->back()->with([
                'messege' => 'Please enter a user ID',
                'alert-type' => 'error',
            ]);
        }

        return $this->renderProfile((int) $request->id);
    }

    public function PendingProfle($id)
    {
        return $this->renderProfile((int) $id);
    }

    public function Active($id)
    {
        $countryId = $this->countryId();
        $user = User::where('id', $id)->where('country_id', $countryId)->first();

        if (!$user) {
            return Redirect()->back()->with([
                'messege' => 'Host not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $user->is_host_id = 1;
        $user->country_id = $countryId;
        $user->save();

        HostData::where('user_id', $id)->update(['country_id' => $countryId]);

        return Redirect()->back()->with([
            'messege' => 'Host activated successfully',
            'alert-type' => 'success',
        ]);
    }

    public function InActive($id)
    {
        $countryId = $this->countryId();
        $user = User::where('id', $id)->where('country_id', $countryId)->first();

        if (!$user) {
            return Redirect()->back()->with([
                'messege' => 'Host not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $user->is_host_id = 0;
        $user->country_id = $countryId;
        $user->save();

        HostData::where('user_id', $id)->where('country_id', $countryId)->delete();

        return Redirect()->back()->with([
            'messege' => 'Host rejected successfully',
            'alert-type' => 'success',
        ]);
    }

    public function ToggleHostingType($id)
    {
        $hostData = HostData::where('user_id', $id)
            ->where('country_id', $this->countryId())
            ->first();

        if (!$hostData) {
            return Redirect()->back()->with([
                'messege' => 'Host data not found for this country',
                'alert-type' => 'error',
            ]);
        }

        $hostData->hosting_type = (int) $hostData->hosting_type === 2 ? 1 : 2;
        $hostData->save();

        return Redirect()->back()->with([
            'messege' => 'Host audio/video type updated successfully',
            'alert-type' => 'success',
        ]);
    }

    protected function renderProfile($id)
    {
        $countryId = $this->countryId();
        $user = User::where('id', $id)->where('country_id', $countryId)->first();

        if (!$user) {
            return Redirect()->back()->with([
                'messege' => 'User not found for this country',
                'alert-type' => 'warning',
            ]);
        }

        $agency = Agency::where('user_id', $id)->first();
        $info = HostData::where('user_id', $id)->where('country_id', $countryId)->first();
        $agencyInfo = $info ? Agency::where('code', $info->agency_code)->first() : null;
        $country = DB::table('countries')->where('id', $user->country_id)->first();

        $protalRecharge = PortalRecharge::where('user_id', $id)->where('is_recall', 0)->sum('amount');
        $recallProtalRecharge = PortalRecharge::where('user_id', $id)->where('is_recall', 1)->sum('amount');
        $protalTransfer = PortalTransfer::where('portal_user_id', $id)->sum('amount');

        $protalRechargeDetails = PortalRecharge::where('user_id', $id)
            ->where('is_recall', 0)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $protalTransferDetails = PortalTransfer::where('portal_user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $rechargeHistorys = PortalTransfer::where('user_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $sandingHistorys = Gift::where('sander_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $recivingHistorys = Gift::where('reciever_id', $id)
            ->orderBy('id', 'desc')
            ->limit(20)
            ->get();

        $liveSummary = $info ? $this->buildLiveSummary($id, (int) $info->hosting_type) : null;

        return view('author.host.profile', compact(
            'user',
            'agency',
            'agencyInfo',
            'info',
            'country',
            'protalRecharge',
            'recallProtalRecharge',
            'protalTransfer',
            'protalRechargeDetails',
            'protalTransferDetails',
            'rechargeHistorys',
            'sandingHistorys',
            'recivingHistorys',
            'liveSummary'
        ));
    }

    protected function buildLiveSummary($userId, $hostingType)
    {
        $startDate = Carbon::now()->subDays(30)->toDateString();
        $endDate = Carbon::now()->toDateString();

        $rows = DB::table('day_times')
            ->where('user_id', $userId)
            ->where('brd_type', $hostingType)
            ->whereDate('live_time', '>=', $startDate)
            ->whereDate('live_time', '<=', $endDate)
            ->get(['day_times', 'live_time']);

        $totalSeconds = 0;
        $days = [];
        foreach ($rows as $row) {
            $parts = explode(':', (string) $row->day_times);
            if (count($parts) !== 3) {
                continue;
            }

            $seconds = ((int) $parts[0] * 3600) + ((int) $parts[1] * 60) + (int) $parts[2];
            $totalSeconds += $seconds;
            if ($seconds > 1199) {
                $days[$row->live_time] = true;
            }
        }

        $points = Gift::where('reciever_id', $userId)
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->sum('value');

        return [
            'hosting_type' => (int) $hostingType,
            'active_days' => count($days),
            'duration' => gmdate('H:i:s', $totalSeconds),
            'points' => $points,
            'date_range' => $startDate . ' to ' . $endDate,
        ];
    }

    protected function countryId()
    {
        return (int) Auth::user()->country_id;
    }

    protected function storeHostFile(Request $request, $field)
    {
        if (!$request->hasFile($field)) {
            return 'store/profile/default.png';
        }

        $file = $request->file($field);
        $name = uniqid() . '.' . strtolower($file->getClientOriginalExtension());
        $path = 'store/agency/';
        $file->move($path, $name);

        return $path . $name;
    }
}
