<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChildAgency;
use App\Models\Agency;
use DB;
use Carbon;
class MasterAgencyController extends Controller
{
    public function Index(){

    	$data = ChildAgency::select('master_agency_id', DB::raw('COUNT(*) as count'))
		    ->groupBy('master_agency_id')
		    ->get();

		$lists = [];
		foreach ($data as $value) {
		    $master_agency = Agency::find($value->master_agency_id);
		    if($master_agency){
		    $row = [
		        'master_agency' => $master_agency->name,
		        'master_agency_code' => $master_agency->code,
		        'count' => $value->count,
		        'id' => $master_agency->id,
		    ];
		    array_push($lists, $row);
		    }
		}
		$agencys=Agency::all();

		return view('backend.agency.master_agency', compact('lists','agencys'));


    }

    public function View($id)
    {
    	$master_agency = Agency::find($id);
    	$data = ChildAgency::where('master_agency_id',$id)->get();
    	$lists = [];
    	 $date = Carbon\Carbon::now(); // Replace this with your desired date

                 $start_date = date('Y-m') . '-01';

                $end_date = date('Y-m') . '-31';
		foreach ($data as $value) {

		    $agency = Agency::find($value->child_agency_id);
		    if($agency){
		     $host_gift_sum = DB::table('host_data')->join('gifts','gifts.reciever_id','host_data.user_id')->whereDate('gifts.date', '>=', $start_date)->whereDate('gifts.date', '<=', $end_date) ->where('host_data.agency_code',$agency->code)->sum('value');
		    $row = [
		        'agency' => $agency->name,
		        'agency_code' => $agency->code,
		        'id' => $value->child_agency_id,
		        'total_target' => $host_gift_sum,
		    ];
		    array_push($lists, $row);
		    }
		}
		return view('backend.agency.master_agency_view',compact('lists','master_agency'));
    }
    public function RemoveChild($id)
    {
    	 ChildAgency::where('child_agency_id', $id)->delete();
    	 $notification=array(
                'messege'=>'ChildAgency Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }

   
    public function Store(Request $request)
{
    // Validate input
    $request->validate([
        'master_agency_id' => 'required|exists:agencies,id',
        'child_agency_id' => 'required|exists:agencies,id|different:master_agency_id',
    ]);
    
    // Check if child agency exists in ANY master agency
    $exists = ChildAgency::where('child_agency_id', $request->child_agency_id)->exists();
    
    if ($exists) {
        // Delete ALL existing records for this child
        ChildAgency::where('child_agency_id', $request->child_agency_id)->delete();
        
        $message = 'Child Agency was already assigned. All previous assignments removed.';
        $type = 'warning';
    } else {
        // Create new record
        ChildAgency::create([
            'master_agency_id' => $request->master_agency_id,
            'child_agency_id' => $request->child_agency_id,
        ]);
        
        $message = 'Child Agency Added Successfully';
        $type = 'success';
    }
    
    return Redirect()->back()->with([
        'messege' => $message,
        'alert-type' => $type
    ]);
}
}
