<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Validation\ValidationException;

class BannerController extends Controller
{
    public function Index()
    {
        $sliders = Slider::orderBy('id', 'desc')->get();

        return view('author.banner.index', compact('sliders'));
    }

    public function Store(Request $request)
    {
        if (!$request->hasFile('image')) {
            return Redirect()->back()->with([
                'messege' => 'No image uploaded',
                'alert-type' => 'error',
            ]);
        }

        $image = $request->file('image');
        if (!$image->isValid()) {
            throw ValidationException::withMessages(['image' => 'Banner upload failed.']);
        }

        $extension = strtolower($image->getClientOriginalExtension());
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
            throw ValidationException::withMessages(['image' => 'Invalid banner image type.']);
        }

        $imageName = uniqid() . '.' . $extension;
        $imagePath = 'store/banner/';
        $targetDir = base_path($imagePath);

        if (!File::isDirectory($targetDir)) {
            File::makeDirectory($targetDir, 0755, true, true);
        }

        $image->move($targetDir, $imageName);

        $slider = new Slider;
        $slider->image = $imagePath . $imageName;
        $slider->save();

        return Redirect()->back()->with([
            'messege' => 'Banner added successfully',
            'alert-type' => 'success',
        ]);
    }

    public function Remove($id)
    {
        $slider = Slider::find($id);
        if ($slider) {
            $slider->delete();
        }

        return Redirect()->back()->with([
            'messege' => 'Banner removed successfully',
            'alert-type' => 'success',
        ]);
    }
}
