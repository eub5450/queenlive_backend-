<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\EntryFrame;
use App\Models\MyBeg;
use App\Models\VipId;
use App\Models\User;
use Image;
class StoreController extends Controller
{
    public function Index()
    {
        $data=EntryFrame::orderBy('id', 'desc')->get();
        $usageCounts = MyBeg::whereNotNull('store_id')
            ->selectRaw('store_id, count(*) as total')
            ->groupBy('store_id')
            ->pluck('total', 'store_id');
        return view('backend.setting.store',compact('data', 'usageCounts'));
    }
   public function Store(Request $request)
    {
        // Validate request
        $request->validate([
            'effect' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'time' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'type' => 'required|in:0,1',
            'is_show' => 'required|in:0,1',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Handle image upload
         if($request->hasFile('image')){
            $image = $request->file('image');
            $image_name = uniqid().'.'.strtolower($image->getClientOriginalExtension());
            $image_path = 'store/vip/';
            $proved_url = $image_path.$image_name;
            $image->move(base_path($image_path), $image_name);
            }else{
            return redirect()->back()->with('error', 'Image Not Added');
            }

        // Create store item
        EntryFrame::create([
            'name' => $request->name,
            'time' => $request->time,
            'price' => $request->price,
            'effect' => $request->effect,
            'type' => $request->type,
            'is_show' => $request->is_show,
            'image' => $proved_url,
        ]);

        return redirect()->back()->with('success', 'Item added successfully!');
    }
    public function Update($id,Request $request)
    {
        
        $data=EntryFrame::find($id);
        $data->name=$request->name;
        $data->price=$request->amount;
        $data->time=$request->time;
        $data->is_show=$request->is_show;
        $data->type=$request->type;
        $data->save();
          $notification=array(
                'messege'=>'Effect Update SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }

    public function ToggleShow($id)
    {
        $data = EntryFrame::find($id);
        if (!$data) {
            return Redirect()->back()->with([
                'messege' => 'Effect not found',
                'alert-type' => 'error',
            ]);
        }

        $data->is_show = (int) $data->is_show === 1 ? 0 : 1;
        $data->save();

        return Redirect()->back()->with([
            'messege' => $data->is_show ? 'Effect is now visible in app store' : 'Effect is now hidden from app store',
            'alert-type' => 'success',
        ]);
    }

    public function Destroy($id)
    {
        $data = EntryFrame::find($id);
        if (!$data) {
            return Redirect()->back()->with([
                'messege' => 'Effect not found',
                'alert-type' => 'error',
            ]);
        }

        $usageCount = MyBeg::where('store_id', $data->id)->count();
        if ($usageCount > 0) {
            $data->is_show = 0;
            $data->save();

            return Redirect()->back()->with([
                'messege' => 'Effect is used by '.$usageCount.' user inventory row(s), so it was hidden instead of deleted.',
                'alert-type' => 'warning',
            ]);
        }

        $data->delete();

        return Redirect()->back()->with([
            'messege' => 'Unused effect deleted successfully',
            'alert-type' => 'success',
        ]);
    }
    public function luckyIDStore(Request $request)
    {
        $user=User::find($request->id_number);
        if($user){
            $notification=array(
                'messege'=>'User ID Already Have',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        }else{
         
        $data=new VipId;
        $data->id_number=$request->id_number;
        $data->price=$request->price;
        $data->is_purchase=0;
        $data->save();
          $notification=array(
                'messege'=>'Lucky ID Store SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
               
        }
    }
    
    public function LuckyIndex(){
        $data=VipId::get();
        return view('backend.setting.lucky_id',compact('data'));
    } 
    public function LuckyRemoved($id){
        $data=VipId::find($id);
        $data->delete();
        $notification=array(
                'messege'=>'Lucky ID Removed SuccessFully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
}

