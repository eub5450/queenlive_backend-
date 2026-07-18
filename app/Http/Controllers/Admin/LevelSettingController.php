<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lavel;
use App\Services\V5\MetaBootstrapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LevelSettingController extends Controller
{
    public function index()
    {
        $levels = Lavel::orderBy('update_lavel', 'asc')
            ->orderBy('amount', 'asc')
            ->get();
        $nextLevel = ((int) $levels->max('update_lavel')) + 1;

        return view('backend.setting.level', compact('levels', 'nextLevel'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'update_lavel' => 'required|integer|min:1|max:1000|unique:lavels,update_lavel',
            'amount' => 'required|integer|min:0|max:2147483647',
        ]);

        $level = new Lavel();
        $level->update_lavel = (int) $validated['update_lavel'];
        $level->amount = (int) $validated['amount'];
        $level->save();

        $this->clearLevelCaches();

        return redirect()->route('admin.level_setting.index')->with([
            'messege' => 'Level added successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function update(Request $request, $id)
    {
        $level = Lavel::findOrFail($id);

        $validated = $request->validate([
            'update_lavel' => 'required|integer|min:1|max:1000|unique:lavels,update_lavel,' . $level->id,
            'amount' => 'required|integer|min:0|max:2147483647',
        ]);

        $level->update_lavel = (int) $validated['update_lavel'];
        $level->amount = (int) $validated['amount'];
        $level->save();

        $this->clearLevelCaches();

        return redirect()->route('admin.level_setting.index')->with([
            'messege' => 'Level updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    public function destroy($id)
    {
        $level = Lavel::findOrFail($id);
        $level->delete();

        $this->clearLevelCaches();

        return redirect()->route('admin.level_setting.index')->with([
            'messege' => 'Level removed successfully.',
            'alert-type' => 'success',
        ]);
    }

    private function clearLevelCaches(): void
    {
        Cache::forget('v4:queenlive:lavel_list_v1');
        Cache::forget(MetaBootstrapService::VERSION_CACHE_KEY);

        $maxLevel = (int) Lavel::max('update_lavel');
        $limit = max($maxLevel + 2, 100);

        for ($level = 1; $level <= $limit; $level++) {
            Cache::forget('v4:queenlive:lavel_target_v1_' . $level);
            Cache::forget('v5:queenlive:lavel_target_v1_' . $level);
        }
    }
}
