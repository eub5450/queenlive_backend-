<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AgoraKeys;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
class AgoraController extends Controller
{
    private const EXCHANGE_CUT_CACHE_KEY = 'queenlive_exchange_cut_parcentage';

    public function Index()
    {
        $data=AgoraKeys::all();
        $setting = Setting::find(1);
        $exchangeCutPercentage = $this->getExchangeCutPercentage($setting);
        return view('backend.setting.agora_key',compact('data', 'setting', 'exchangeCutPercentage'));
    }
    public function Store(Request $request)
    {
        //return $request->all();
        // ✅ Step 1: Validate input
        $request->validate([
            'appId'              => 'required|string|max:255',
            'appCertificate'     => 'required|string|max:255',
            'AgoraEmail'         => 'required|email|max:255',
            'AgoraEmailPassword' => 'required|string|max:255',
        ]);

        // ✅ Step 2: Save to database
        AgoraKeys::create([
            'appId'              => $request->appId,
            'appCertificate'     => $request->appCertificate,
            'AgoraEmail'         => $request->AgoraEmail,
            'AgoraEmailPassword' => $request->AgoraEmailPassword,
        ]);

        // ✅ Step 3: Redirect with success message
        return redirect()->back()->with('success', 'Agora Account saved successfully!');
    }
    
    public function AgoraAccountActive($id)
    {
        $keys=AgoraKeys::where('Status',1)->get();
        foreach($keys as $item){
            $item->Status=2;
            $item->save();
        }
        $data=AgoraKeys::find($id);
        $data->Status=1;
        if($data->save()){
            $setting=Setting::find(1);
            $setting->appId=$data->appId;
            $setting->appCertificate=$data->appCertificate;
            $setting->save();
        }
        $notification=array(
                'messege'=>'Agorea Key Change Successfully !',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function PreAccountActive($id){
        $item=AgoraKeys::find($id);
        $item->other_use=2;
            $item->save();
            $notification=array(
                'messege'=>'Agorea Key Pre Active Successfully !',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }

    public function UpdateExchangeCutPercentage(Request $request)
    {
        $request->validate([
            'exchange_cut_parcentage' => 'required|numeric|min:0|max:100',
        ]);

        $exchangeCutPercentage = $this->normalizeExchangeCutPercentage(
            $request->exchange_cut_parcentage
        );
        try {
            Cache::store('redis')->forever(
                self::EXCHANGE_CUT_CACHE_KEY,
                number_format($exchangeCutPercentage, 2, '.', '')
            );
        } catch (\Throwable $exception) {
            return Redirect()->back()->withErrors([
                'exchange_cut_parcentage' => 'Exchange cut save failed. Please try again.',
            ]);
        }

        $setting = Setting::find(1);
        if (
            $setting &&
            \Schema::hasTable('settings') &&
            \Schema::hasColumn('settings', 'exchange_cut_parcentage')
        ) {
            $setting->exchange_cut_parcentage = $exchangeCutPercentage;
            $setting->save();
        }

        $notification=array(
                'messege'=>'Exchange cut percentage updated successfully!',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }

    private function normalizeExchangeCutPercentage($value)
    {
        if (!is_numeric($value)) {
            return 30.00;
        }

        $normalized = round((float) $value, 2);
        if ($normalized < 0) {
            return 0.00;
        }

        if ($normalized > 100) {
            return 100.00;
        }

        return $normalized;
    }

    private function getExchangeCutPercentage($setting = null)
    {
        try {
            $cachedValue = Cache::store('redis')->get(self::EXCHANGE_CUT_CACHE_KEY);
            if (is_numeric($cachedValue)) {
                return $this->normalizeExchangeCutPercentage($cachedValue);
            }
        } catch (\Throwable $exception) {
            // Fall back to the legacy settings row when Redis is temporarily unavailable.
        }

        return $this->normalizeExchangeCutPercentage(
            optional($setting)->exchange_cut_parcentage
        );
    }
}
