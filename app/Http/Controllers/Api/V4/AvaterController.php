<?php

namespace App\Http\Controllers\Api\V4;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Avater;
use App\Models\User;
use Image;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
class AvaterController extends Controller
{
    public function Avater(Request $request)
    {
    	$response = array();
         $token = $request->access_token;
         $user_id = $request->user_id;
        if($token=="0411f0028cfb768b3a3d96ac3aa37dw3e5"){
        	$avater=Avater::where('user_id',$user_id)->first();
        	 array_push($response,array('message'=>'Brd Avater','image'=>$avater,'code'=>'200'));
            		return json_encode($response,JSON_UNESCAPED_UNICODE);
        }else{
             array_push($response,array('message'=>'Unauthorized','code'=>'401'));
            return json_encode($response,JSON_UNESCAPED_UNICODE);
        }
    }

      public function store(Request $request)
    {
        $access_token = $request->access_token;
        $user_id = $request->user_id;
        $response = array();
    
        try {
            if ($access_token == "0411f0028cfb768b3a3d96ac3aa37dw3e5") {
                
                if ($request->has('image')) {
                    $user = User::find($user_id);
                    // MM: null guard — $user is dereferenced in processAvatarImage as $user->id
                    if (!$user) {
                        array_push($response, array('message' => 'User Not Found', 'code' => '404'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }

                    // Check if an old image exists and delete it
                    $old_image = Avater::where('user_id', $user_id)->first();
                    if ($old_image) {
                        // Delete physical file
                        $oldPath = $this->extractPathFromUrl($old_image->image);
                        $fullOldPath = base_path() . '/' . ltrim($oldPath, '/');
                        $fullOldPath = str_replace('/public_html/public/', '/public_html/', $fullOldPath);
                        
                        if (File::exists($fullOldPath)) {
                            File::delete($fullOldPath);
                            \Log::info("✅ Old avatar deleted: " . $fullOldPath);
                        }
                        
                        $old_image->delete();
                    }
                    
                    // Process new avatar image - WebP, 150-180px, under 8KB
                    $image_url = $this->processAvatarImage($request->input('image'), $user);
                    
                    if (!$image_url) {
                        array_push($response, array('message' => 'Failed to process image', 'code' => '400'));
                        return json_encode($response, JSON_UNESCAPED_UNICODE);
                    }
                    
                } else {
                    // Default profile image if none is uploaded
                    $image_url = 'https://queenlive.site/store/profile/default.png';
                }
                
                // Store the new image details in the database
                $new_image = new Avater;
                $new_image->user_id = $user_id;
                $new_image->image = $image_url;
                $new_image->save();
    
                $avatar = Avater::where('user_id', $user_id)->first();
                array_push($response, array('message' => 'Avatar Store Successfully', 'image' => $avatar, 'code' => '200'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
                
            } else {
                array_push($response, array('message' => 'Unauthorized access_token', 'code' => '401'));
                return json_encode($response, JSON_UNESCAPED_UNICODE);
            }
        } catch (\Exception $e) {
            array_push($response, array(
                'message' => 'Internal Server Error', 
                'code' => '500', 
                'error' => $e->getMessage()
            ));
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }
    }
    
    /**
     * Process avatar image - WebP, 150-180px, under 8KB, /store/user_avatar/ path
     */
    private function processAvatarImage($base64Image, $user)
    {
        try {
            $base64Image = $this->normalizeBase64Image($base64Image);
            $file = base64_decode($base64Image, true);
            if (!$file) {
                \Log::error("Avatar: Base64 decode failed");
                return null;
            }
            
            // Create image
            $img = Image::make($file);
            
            // ============================================
            // RESIZE TO 150-180px RANGE
            // ============================================
            $targetSize = 165; // Default middle ground
            
            // If image is smaller than 150px, don't upscale too much
            if ($img->width() < 150) {
                $targetSize = $img->width();
            } else {
                // Choose size between 150-180px
                $targetSize = rand(150, 180); // Or use fixed: 165
            }
            
            $img->resize($targetSize, null, function ($constraint) {
                $constraint->aspectRatio();
            });
            
            // Limit height to max 180px
            if ($img->height() > 180) {
                $img->resize(null, 180, function ($constraint) {
                    $constraint->aspectRatio();
                });
            }
            
            // ============================================
            // USE /store/user_avatar/ PATH
            // ============================================
            $newDir = base_path('store/user_avatar');
            
            // Debug log
            \Log::info("Avatar - Base path: " . base_path());
            \Log::info("Avatar - Full directory path: " . $newDir);
            
            // Create directory if not exists
            if (!file_exists($newDir)) {
                mkdir($newDir, 0755, true);
                \Log::info("Avatar - Directory created: " . $newDir);
            }
            
            // Check if directory is writable
            if (!is_writable($newDir)) {
                chmod($newDir, 0755);
                \Log::info("Avatar - Permissions set to 755 for: " . $newDir);
            }
            
            // Generate filename
            $filename = 'avatar_' . $user->id . '_' . time() . '_' . uniqid() . '.webp';
            $filepath = $newDir . '/' . $filename;
            
            \Log::info("Avatar - Attempting to save file: " . $filepath);
            
            // ============================================
            // FORCE UNDER 8KB - TRY MULTIPLE QUALITIES
            // ============================================
            
            $saved = false;
            $finalSize = 999;
            
            // Try different qualities from highest to lowest - TARGET 8KB
            $qualities = [40, 38, 36, 34, 32, 30, 28, 26, 24, 22, 20, 18, 16, 14, 12, 10, 8, 6, 5];
            
            foreach ($qualities as $quality) {
                // Encode and save
                $img->encode('webp', $quality)->save($filepath);
                
                if (file_exists($filepath)) {
                    $currentSize = round(filesize($filepath) / 1024, 2);
                    \Log::info("Avatar - Quality {$quality}: {$currentSize}KB");
                    
                    if ($currentSize <= 8) {
                        $finalSize = $currentSize;
                        $saved = true;
                        \Log::info("Avatar - ✅ Target 8KB achieved at quality {$quality}: {$currentSize}KB");
                        break;
                    }
                }
            }
            
            // If still over 8KB after all attempts, use the smallest we got
            if (!$saved && file_exists($filepath)) {
                $finalSize = round(filesize($filepath) / 1024, 2);
                \Log::info("Avatar - ⚠️ Could not get under 8KB. Smallest size: {$finalSize}KB");
                $saved = true;
            }
            
            // Check if file exists
            if ($saved && file_exists($filepath)) {
                // Return URL
                $image_url = 'https://queenlive.site/store/user_avatar/' . $filename;
                \Log::info("Avatar - ✅ File saved! URL: " . $image_url . " Size: {$finalSize}KB");
                
                return $image_url;
                
            } else {
                \Log::error("Avatar - ❌ File NOT saved! File does not exist after save attempt");
                
                // Try alternative save method
                $encoded = $img->encode('webp', 20);
                $bytes = file_put_contents($filepath, $encoded);
                
                if ($bytes && file_exists($filepath)) {
                    $finalSize = round(filesize($filepath) / 1024, 2);
                    \Log::info("Avatar - ✅ Alternative save method worked! Size: {$finalSize}KB");
                    $image_url = 'https://queenlive.site/store/user_avatar/' . $filename;
                    return $image_url;
                }
                
                return null;
            }
            
        } catch (\Exception $e) {
            \Log::error("Avatar - Image processing failed: " . $e->getMessage());
            \Log::error("Avatar - Stack trace: " . $e->getTraceAsString());
            return null;
        }
    }
    
    /**
     * Extract path from URL
     */
    private function extractPathFromUrl($url)
    {
        $parsed = parse_url($url);
        return $parsed['path'] ?? '';
    }

    private function normalizeBase64Image($value)
    {
        $value = trim((string) $value);
        if (strpos($value, ',') !== false) {
            $value = substr($value, strpos($value, ',') + 1);
        }

        return str_replace(' ', '+', $value);
    }
}
