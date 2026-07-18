<?php

namespace App\Http\Controllers\Api\V5;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Avater;
use App\Models\User;
use Image;
use Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use App\Support\ImageUploadStorageHelper;
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

                    if (!$user) {
                        \Log::warning("Avatar - store aborted: user not found for user_id={$user_id}");
                        array_push($response, array('message' => 'User not found', 'code' => '404'));
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
     * Store the edited avatar image without destructive resize/re-encode.
     */
    private function processAvatarImage($base64Image, $user)
    {
        try {
            return ImageUploadStorageHelper::storeBase64Image(
                $base64Image,
                'store/user_avatar',
                'avatar_' . $user->id
            );
        } catch (\Exception $e) {
            \Log::error("Avatar - Image storage failed: " . $e->getMessage());
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
