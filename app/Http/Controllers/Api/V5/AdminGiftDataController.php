<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\V5\Concerns\AdminActorAuthorization;
use App\Models\BrdBackground;
use App\Models\GiftFile;
use App\Support\MediaPathHelper;
use Illuminate\Http\Request;

class AdminGiftDataController extends Controller
{
    use AdminActorAuthorization;

    public function index(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $gifts = GiftFile::orderBy('category')->orderBy('amount', 'asc')->orderBy('id')->get();

        return $this->success('Gift data loaded successfully', [
            'data' => [
                'gifts' => $gifts,
                'grouped' => [
                    'propulars' => $gifts->where('category', 1)->values(),
                    'luxerys' => $gifts->where('category', 2)->values(),
                    'fastival' => $gifts->where('category', 3)->values(),
                ],
                'audio_brd_backgrounds' => BrdBackground::where('is_defult', 1)->orderBy('id', 'asc')->get(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $validated = $request->validate([
            'category' => 'required|integer',
            'name' => 'required|string|max:255',
            'value' => 'required|numeric|min:0',
            'image' => 'required|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'svga' => 'required|file|max:10240',
        ]);

        $image = $request->file('image');
        $svga = $request->file('svga');

        $gift = new GiftFile();
        $gift->category = (int) $validated['category'];
        $gift->name = $validated['name'];
        $gift->svga_name = $svga->getClientOriginalName();
        $gift->image_name = $image->getClientOriginalName();
        $gift->value = $validated['value'];
        $gift->amount = $validated['value'];
        $gift->image = MediaPathHelper::moveUploadedFile($image, 'store/gift/image', null, true);
        $gift->svga = MediaPathHelper::moveUploadedFile($svga, 'store/gift/svga', null, true);
        $gift->save();

        return $this->success('Gift Store Successfully', [
            'data' => $gift->fresh(),
        ]);
    }

    public function update(Request $request, $id = null)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $giftId = $id ?: $request->id;
        $gift = GiftFile::find($giftId);
        if (!$gift) {
            return $this->error('Gift not found', '404');
        }

        $validated = $request->validate([
            'value' => 'required|numeric|min:0',
            'name' => 'required|string|max:255',
            'category' => 'required|integer',
            'image' => 'nullable|file|mimes:jpg,jpeg,png,webp,gif|max:5120',
            'svga' => 'nullable|file|max:10240',
        ]);

        $gift->value = $validated['value'];
        $gift->amount = $validated['value'];
        $gift->name = $validated['name'];
        $gift->category = (int) $validated['category'];

        if ($request->hasFile('image')) {
            MediaPathHelper::deleteLocalFile($gift->image, ['store/gift/image']);
            $image = $request->file('image');
            $gift->image = MediaPathHelper::moveUploadedFile($image, 'store/gift/image', null, true);
            $gift->image_name = $image->getClientOriginalName();
        }

        if ($request->hasFile('svga')) {
            MediaPathHelper::deleteLocalFile($gift->svga, ['store/gift/svga']);
            $svga = $request->file('svga');
            $gift->svga = MediaPathHelper::moveUploadedFile($svga, 'store/gift/svga', null, true);
            $gift->svga_name = $svga->getClientOriginalName();
        }

        $gift->save();

        return $this->success('Gift Update Successfully', [
            'data' => $gift->fresh(),
        ]);
    }

    public function delete(Request $request, $id = null)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $giftId = $id ?: $request->id;
        $gift = GiftFile::find($giftId);
        if (!$gift) {
            return $this->error('Gift not found', '404');
        }

        MediaPathHelper::deleteLocalFile($gift->image, ['store/gift/image']);
        MediaPathHelper::deleteLocalFile($gift->svga, ['store/gift/svga']);
        $gift->delete();

        return $this->success('Gift Removed Successfully');
    }

    public function audioBackgroundIndex(Request $request)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        return $this->success('Audio board background data loaded successfully', [
            'data' => BrdBackground::where('is_defult', 1)->orderBy('id', 'asc')->get(),
        ]);
    }

    public function audioBackgroundUpdate(Request $request, $id)
    {
        if (!$this->authorizedActor($request)) {
            return $this->unauthorized();
        }

        $item = BrdBackground::find($id);
        if (!$item) {
            return $this->error('Background not found', '404');
        }

        $request->validate([
            'image' => 'required|file|mimes:jpg,jpeg,png,gif,webp|max:5120',
        ]);

        $image = $request->file('image');
        $imageUrl = MediaPathHelper::moveUploadedFile(
            $image,
            'store/gift/brd_backgorund',
            'audio-brd-background-' . gmdate('YmdHis') . '-' . uniqid()
        );

        MediaPathHelper::deleteLocalFile($item->image, ['store/gift/brd_backgorund']);
        $item->image = MediaPathHelper::publicUrl($imageUrl);
        $item->save();

        return $this->success('Audio board background updated successfully.', [
            'data' => $item->fresh(),
        ]);
    }
}
