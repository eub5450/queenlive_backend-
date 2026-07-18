<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\SystemSettingRuntimeStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class FunStickerSettingController extends Controller
{
    private const ALLOWED_TYPES = ['webp', 'gif', 'svga', 'image'];

    public function index()
    {
        $stickers = $this->loadCatalogForEditor();

        return view('backend.setting.fun_sticker', compact('stickers'));
    }

    public function save(Request $request)
    {
        $ids = $request->input('sticker_id', []);
        $types = $request->input('sticker_type', []);
        $urls = $request->input('sticker_url', []);

        if (!is_array($ids) || !is_array($types) || !is_array($urls)) {
            return redirect()->back()->withInput()->with([
                'messege' => 'Invalid fun sticker form data.',
                'alert-type' => 'error',
            ]);
        }

        $count = max(count($ids), count($types), count($urls));
        $rows = [];

        for ($i = 0; $i < $count; $i++) {
            $id = trim((string) ($ids[$i] ?? ''));
            $type = strtolower(trim((string) ($types[$i] ?? 'image')));
            $url = trim((string) ($urls[$i] ?? ''));

            if ($id === '' && $url === '') {
                continue;
            }

            if ($id === '' || $url === '') {
                return redirect()->back()->withInput()->with([
                    'messege' => 'Every active row needs both sticker id and sticker url/path.',
                    'alert-type' => 'error',
                ]);
            }

            if (!in_array($type, self::ALLOWED_TYPES, true)) {
                return redirect()->back()->withInput()->with([
                    'messege' => 'Sticker type must be one of: webp, gif, svga, image.',
                    'alert-type' => 'error',
                ]);
            }

            $rows[] = [
                'id' => $id,
                'type' => $type,
                'url' => $url,
            ];
        }

        SystemSettingRuntimeStore::putMany([
            'fun_sticker_catalog' => array_values($rows),
        ]);

        if (empty($rows)) {
            Cache::forget('fun_sticker_catalog');
        } else {
            Cache::forever('fun_sticker_catalog', array_values($rows));
        }

        return redirect()->route('admin.fun_sticker.index')->with([
            'messege' => empty($rows)
                ? 'Fun Sticker catalog cleared. API will use the default fallback stickers.'
                : 'Fun Sticker catalog updated successfully.',
            'alert-type' => 'success',
        ]);
    }

    private function loadCatalogForEditor(): array
    {
        try {
            $store = SystemSettingRuntimeStore::all();
            if (array_key_exists('fun_sticker_catalog', $store)) {
                $rows = $this->normalizeCatalog($store['fun_sticker_catalog']);
                if (!empty($rows)) {
                    return $rows;
                }
            }
        } catch (\Throwable $throwable) {
            // fall through
        }

        try {
            $rows = $this->normalizeCatalog(Cache::get('fun_sticker_catalog'));
            if (!empty($rows)) {
                return $rows;
            }
        } catch (\Throwable $throwable) {
            // fall through
        }

        return $this->defaultCatalog();
    }

    private function normalizeCatalog($raw): array
    {
        if (!is_array($raw)) {
            return [];
        }

        $rows = [];
        foreach ($raw as $row) {
            if (!is_array($row)) {
                continue;
            }

            $id = trim((string) ($row['id'] ?? ''));
            $type = strtolower(trim((string) ($row['type'] ?? 'image')));
            $url = trim((string) ($row['url'] ?? ''));

            if ($id === '' || $url === '' || !in_array($type, self::ALLOWED_TYPES, true)) {
                continue;
            }

            $rows[] = [
                'id' => $id,
                'type' => $type,
                'url' => $url,
                'preview_url' => $this->previewUrl($url),
            ];
        }

        return $rows;
    }

    private function defaultCatalog(): array
    {
        return $this->normalizeCatalog([
            ['id' => 'fun_offer_pop', 'type' => 'webp', 'url' => 'store/banner/offer.webp'],
            ['id' => 'fun_eid_spark', 'type' => 'webp', 'url' => 'store/banner/eid.png.webp'],
            ['id' => 'fun_gold_wave', 'type' => 'webp', 'url' => 'store/banner/681313218872c.webp'],
            ['id' => 'fun_game_pop', 'type' => 'webp', 'url' => 'game/greedy.png.webp'],
            ['id' => 'fun_fruit_flash', 'type' => 'webp', 'url' => 'game/fruitsloops.png.webp'],
            ['id' => 'fun_smile_card', 'type' => 'image', 'url' => 'backend/it-solutionsbd/assets/dist/img/wow-slider-logo.png'],
        ]);
    }

    private function previewUrl(string $path): string
    {
        $trimmed = trim($path);
        if ($trimmed === '') {
            return '';
        }

        if (preg_match('#^https?://#i', $trimmed)) {
            return $trimmed;
        }

        return url('/' . ltrim($trimmed, '/'));
    }
}
