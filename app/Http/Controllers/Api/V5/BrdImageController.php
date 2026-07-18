<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\BrdBackground;
use Image;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use App\Support\ImageUploadStorageHelper;
use RedisCacheFunction;

class BrdImageController extends Controller
{
    private $prefix = 'queenlive:';
    
    /**
     * Get background images list with Redis cache
     */
    public function Index(Request $request)
    {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $response = array();
        
        if ($access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        // Try Redis cache first
        $cacheKey = $this->prefix . "brd_backgrounds_{$user_id}";
        
        try {
            $cached = Redis::get($cacheKey);
            if ($cached) {
                $images = unserialize($cached);
                array_push($response, array(
                    'message' => 'Audio Brd Background Image List (Cached)',
                    'images' => $images,
                    'code' => '200'
                ));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            Log::error("Redis get failed for brd_backgrounds", [
                'error' => $e->getMessage(),
                'user_id' => $user_id
            ]);
        }
        
        // Cache miss - get from database
        $defults = BrdBackground::where('user_id', null)->get();
        $my_backgrounds = BrdBackground::where('user_id', $user_id)->get();
        
        $images = $this->formatBackgroundImages($defults, $my_backgrounds);
        
        // Save to Redis cache (10 minutes)
        try {
            Redis::setex($cacheKey, 1200, serialize($images));
        } catch (\Exception $e) {
            Log::error("Redis set failed for brd_backgrounds", [
                'error' => $e->getMessage(),
                'user_id' => $user_id
            ]);
        }
        
        array_push($response, array(
            'message' => 'Audio Brd Background Image List',
            'images' => $images,
            'code' => '200'
        ));
        
        return json_encode($response, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Delete background image
     */
    public function Delete(Request $request)
    {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $id = $request->id;
        
        $response = array();
        
        if ($access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
            array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        $background = BrdBackground::find($id);
        
        if (!$background) {
            array_push($response, array('message' => 'Record not found', 'code' => '404'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
        
        if ($background->user_id != null && $background->is_defult == 0) {
            // Delete the image file if it exists
            $this->deleteImageFile($background->image);
            
            $background->delete();
            
            // Clear Redis cache
            $this->clearBackgroundCache($user_id);
            
            // Get updated lists
            $defults = BrdBackground::where('user_id', null)->get();
            $my_backgrounds = BrdBackground::where('user_id', $user_id)->get();
            $images = $this->formatBackgroundImages($defults, $my_backgrounds);
            
            array_push($response, array(
                'message' => 'Audio Brd Background Removed',
                'images' => $images,
                'code' => '200'
            ));
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        } else {
            array_push($response, array('message' => 'You Cant remove this', 'code' => '401'));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }

    /**
     * Store new background image
     */
    public function Store(Request $request)
    {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $response = array();

        try {
            if ($access_token != "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
                array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
            $image_url = null;
            
            if ($request->has('image')) {
                $user = User::find($user_id);
                if (!$user) {
                    array_push($response, array('message' => 'User not found', 'code' => '404'));
                    return json_encode($response, JSON_UNESCAPED_UNICODE);
                }
                $image_url = $this->processAndSaveImage($request->input('image'), $user);
            }
            
            if (!$image_url) {
                $user = User::find($user_id);
                $image_url = $user ? $user->profile : null;
            }
            
            if (!$image_url) {
                array_push($response, array('message' => 'Failed to process image', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
            
            // Save to database
            $new_image = new BrdBackground;
            $new_image->user_id = $user_id;
            $new_image->image = $image_url;
            $new_image->is_defult = 0;
            $new_image->save();
            
            // Clear Redis cache
            $this->clearBackgroundCache($user_id);
            
            // Get updated lists
            $defults = BrdBackground::where('user_id', null)->get();
            $my_backgrounds = BrdBackground::where('user_id', $user_id)->get();
            $data = $this->formatBackgroundImages($defults, $my_backgrounds);
            
            array_push($response, array(
                'message' => 'Brd Background Added Successfully',
                'images' => $data,
                'code' => '200'
            ));
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            \Log::error("BrdImageController::Store error", [
                'error' => $e->getMessage(),
                'user_id' => $user_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            array_push($response, array(
                'message' => 'Internal Server Error',
                'code' => '500',
                'error' => $e->getMessage()
            ));
            
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * ==================== PRIVATE HELPER FUNCTIONS ====================
     */
    
    /**
     * Format background images for response
     */
    private function formatBackgroundImages($defults, $my_backgrounds)
    {
        $images = array();
        
        foreach ($defults as $defult) {
            $value = array();
            $value['id'] = $defult->id;
            $value['image'] = $defult->image;
            $value['is_defult'] = 1;
            array_push($images, $value);
        }

        foreach ($my_backgrounds as $my_background) {
            $row = array();
            $row['id'] = $my_background->id;
            $row['image'] = $my_background->image;
            $row['is_defult'] = 0;
            array_push($images, $row);
        }
        
        return $images;
    }
    /**
     * Store the edited room background without stretching or recompressing it.
     */
    private function processAndSaveImage($base64Image, $user)
    {
        try {
            return ImageUploadStorageHelper::storeBase64Image(
                $base64Image,
                'store/brdbackground',
                'bg_' . $user->id
            );
        } catch (\Exception $e) {
            \Log::error("Brd background - Image storage failed: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Delete image file from server
     */
    private function deleteImageFile($imageUrl)
    {
        try {
            if ($imageUrl && strpos($imageUrl, 'queenlive.site/') !== false) {
                $path = str_replace('https://queenlive.site/', '', $imageUrl);
                $fullPath = base_path(ltrim($path, '/'));
                if (File::exists($fullPath)) {
                    File::delete($fullPath);
                }
            }
        } catch (\Exception $e) {
            Log::error("Failed to delete image file", ['error' => $e->getMessage()]);
        }
    }

    private function normalizeBase64Image($value)
    {
        $value = trim((string) $value);
        if (strpos($value, ',') !== false) {
            $value = substr($value, strpos($value, ',') + 1);
        }

        return str_replace(' ', '+', $value);
    }
    
    /**
     * Clear background cache for user
     */
    private function clearBackgroundCache($user_id)
    {
        $cacheKey = $this->prefix . "brd_backgrounds_{$user_id}";
        
        try {
            Redis::del($cacheKey);
            Log::info("Cleared background cache for user: {$user_id}");
        } catch (\Exception $e) {
            Log::error("Failed to clear background cache", [
                'error' => $e->getMessage(),
                'user_id' => $user_id
            ]);
        }
    }
}
