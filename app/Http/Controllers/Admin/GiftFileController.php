<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GiftFile;
use App\Models\BrdBackground;
use Illuminate\Validation\ValidationException;
use App\Support\MediaPathHelper;

class GiftFileController extends Controller
{
    public function index(){
        $gifts=GiftFile::get();
        foreach($gifts as $gift){
            $gift->amount=$gift->value;
            $gift->save();
        }
        return view('backend.gift.index',compact('gifts'));
    }
    
    public function Store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required',
            'name' => 'required',
            'value' => 'required',
            'image' => 'required',
            'svga' => 'required',
            'svga_name' => 'required',
            'image_name' => 'required',
        ]);
        $category=$request->category;
        $name=$request->name;
        $value=$request->value;
        $image_name=$request->image_name;
        $svga_name=$request->svga_name;
        
       if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image_name = $image->getClientOriginalName(); // Keep the original file name
                    $image_path = 'store/gift/image/';
                    $image_url = $image_path . $image_name;
                    
                    $image->move(base_path($image_path), $image_name);
                }
        if($request->hasFile('svga')){
            $svga_image = $request->file('svga');
            $svga_image_name = $svga_image->getClientOriginalName(); 
            $svga_image_path = 'store/gift/svga/';
            $svga_url = $svga_image_path.$svga_image_name;
            $svga_image->move(base_path($svga_image_path), $svga_image_name);
        }
        
            $data=new GiftFile();
            $data->category=$category;
            $data->name=$name;
            $data->svga_name=$svga_name;
            $data->image_name=$image_name;
            $data->value=$value;
            $data->image=$image_url;
            $data->svga=$svga_url;
            $data->save();
            $notification=array(
                'messege'=>'Gift Store Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
        
    }
    
    public function Delete($id)
    {
        $gifts=GiftFile::find($id);
        $gifts->delete();
        $notification=array(
                'messege'=>'Gift Removed Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function Update($id,Request $request)
    {
        $validated = $request->validate([
            'value' => 'required',
            'name' => 'required',
            'category' => 'required',
        ]);
        $gifts=GiftFile::find($id);
        $gifts->value=$request->value;
        $gifts->name=$request->name;
        $gifts->category=$request->category;
        $gifts->save();
        $notification=array(
                'messege'=>'Gift Update Successfully',
                'alert-type'=>'success'
            );
            return Redirect()->back()->with($notification);
    }
    public function AudioBrdBackgroundIndex()
    {
        $data = BrdBackground::where('is_defult', 1)
            ->orderBy('id', 'asc')
            ->get();

        return view('backend.setting.audio_brd_background',compact('data'));
    }
    
    public function AudioBrdBackgroundUpdate(Request $request, $id)
    {
        $item = BrdBackground::findOrFail($id);

        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $imageUrl = $this->storeAudioBrdBackgroundImage($request->file('image'));

        if ($imageUrl === null) {
            return redirect()->back()->with('error', 'Background upload failed. Please use a valid image file.');
        }

        $this->deleteLocalAudioBrdBackground($item->image);
        $item->image = $imageUrl;
        $item->save();

        return redirect()->back()->with('success', 'Audio board background updated successfully.');
    }

    private function storeAudioBrdBackgroundImage($image)
    {
        if (!$image || !$image->isValid()) {
            return null;
        }

        $relativePath = MediaPathHelper::moveUploadedFile(
            $image,
            'store/gift/brd_backgorund',
            'audio-brd-background-' . gmdate('YmdHis') . '-' . uniqid()
        );

        return MediaPathHelper::publicUrl($relativePath);
    }

    private function deleteLocalAudioBrdBackground($imageUrl)
    {
        MediaPathHelper::deleteLocalFile($imageUrl, ['store/gift/brd_backgorund']);
    }
}

