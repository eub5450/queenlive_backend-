<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Country;
class ContryController extends Controller
{
    public function Index()
    {
    	$data=Country::all();
    	return view('backend.country.index',compact('data'));
    }
    public function Store(Request $request)
    {
    	$validated = $request->validate([
            'name' => 'required',
            'flag' => 'required',
        ]);
        if($request->hasFile('flag')){
            $image = $request->file('flag');
            $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
            $image_path = 'store/country/';
            $flag_url = $image_path.$image_name;
            $image->move(base_path($image_path), $image_name);
            }else{
            $flag_url = null;
            }

            $data=new Country;
            $data->name=$request->name;
            $data->flag=$flag_url;
            $data->save();
             $notification=array(
                'messege'=>'Country Added SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}

