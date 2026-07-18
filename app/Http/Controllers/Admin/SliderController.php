<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Slider;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
class SliderController extends Controller
{
     private $prefix = 'queenlive:';
    public function Index()
    {
    	$sliders=Slider::all();
    	return view('backend.slider.index',compact('sliders'));
    }
    public function Store(Request $request)
    {
    	if($request->hasFile('image')){
                $image = $request->file('image');
                $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
                $image_path = 'store/banner/';
                $image_url = $image_path.$image_name;
                $image->move(base_path($image_path), $image_name);
            }else{
                $notification=array(
                'messege'=>'No Image Upload',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
            }
            $slider=new Slider;
            $slider->image='https://queenlive.site/'.$image_url;
            $slider->save();
            Redis::del($this->prefix . "slider");
             $notification=array(
                'messege'=>'Slider Update Successfully!',
                'alert-type'=>'error'
            );
            return Redirect()->back()->with($notification);
    }
    public function Remove($id)
    {
    	 $slider=Slider::find($id);
         $slider->delete();
         Redis::del($this->prefix . "slider");
         $notification=array(
                'messege'=>'Slider Removed Successfully!',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}

