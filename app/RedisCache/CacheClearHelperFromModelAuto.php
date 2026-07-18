<?php

namespace App\RedisCache;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class CacheClearHelperFromModelAuto
{
    private static $prefix = 'queenlive:'; // আপনার Redis prefix

    /**
     * 🎯 Clear User related caches - সরাসরি Redis ব্যবহার করে
     */
    public static function clearUserCaches($user, $event = 'updated')
    {
        $userId = is_object($user) ? $user->id : $user;
        
        try {
            $patterns = [];
            $clearedReasons = [];
            
            if (is_object($user)) {
                $changedFields = $user->getChanges();
                
                // 1. Check main user fields
                $mainUserFields = [
                    'name',
                    'password',
                    'balance',
                    'level',
                    'frame',
                    'is_vip',
                    'entry',
                    'profile',
                    'is_invisible',
                    'is_invisible_active',
                    'brd_off_power',
                    'kick_power',
                    'comment_mute_power',
                    'sceen_short_power',
                    'withdraw_active',
                    'agora_access',
                ];
                $mainChanged = array_intersect($mainUserFields, array_keys($changedFields));
                
                if (!empty($mainChanged)) {
                    $patterns = array_merge($patterns, [
                        "*user:{$userId}*",
                        "*auth_user_{$userId}*",
                        "*profile_view_{$userId}*",
                        "*profile:{$userId}*",
                    ]);
                    $clearedReasons[] = 'User fields: ' . implode(', ', $mainChanged);
                }
                
                
                
                // 3. Check hostData relationship
                if ($user->relationLoaded('hostData') && $user->hostData && $user->hostData->isDirty('hosting_type')) {
                    $patterns[] = "*hosting_type_{$userId}*";
                    $clearedReasons[] = 'Hosting type updated';
                }
                
                
                
            } else {
                // If only ID is provided, clear all as fallback
                $patterns = [
                    "*user:{$userId}*",
                    "*auth_user_{$userId}*",
                    "*profile_view_{$userId}*",
                    "*profile:{$userId}*",
                    "*avatar:{$userId}*",
                    "*hosting_type_{$userId}*",
                ];
                $clearedReasons[] = 'Full clear (only ID provided)';
            }
            
            // Remove duplicates
            $patterns = array_unique($patterns);
            
            // Clear caches
            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }
            
            // Enhanced logging
            // Log::info("User cache cleared", [
            //     'user_id' => $userId,
            //     'event' => $event,
            //     'keys_cleared' => $totalCleared,
            //     'patterns' => $patterns,
            //     'reasons' => $clearedReasons,
            //     'changed_fields' => is_object($user) ? $user->getChanges() : []
            // ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear user caches for ID: {$userId}", [
                'error' => $e->getMessage()
            ]);
        }
        
        return true; 
    }
    
    public static function clearUserBalance($user, $event = 'updated')
    {
        $userId = is_object($user) ? $user->id : $user;
        
        try {
            $patterns = [];
            $clearedReasons = [];
            
            if (is_object($user)) {
                $changedFields = $user->getChanges();
                
                // 1. Check main user fields
                $mainUserFields = ['password', 'balance'];
                $mainChanged = array_intersect($mainUserFields, array_keys($changedFields));
                
                if (!empty($mainChanged)) {
                    $patterns = array_merge($patterns, [
                        "*user:{$userId}*",
                        "*auth_user_{$userId}*",
                        "*profile_view_{$userId}*",
                        "*profile:{$userId}*",
                    ]);
                    $clearedReasons[] = 'User Update From Web Route fields: ' . implode(', ', $mainChanged);
                }
                
                
                
                // 3. Check hostData relationship
                if ($user->relationLoaded('hostData') && $user->hostData && $user->hostData->isDirty('hosting_type')) {
                    $patterns[] = "*hosting_type_{$userId}*";
                    $clearedReasons[] = 'Hosting type updated';
                }
                
                
                
            } else {
                // If only ID is provided, clear all as fallback
                $patterns = [
                    "*user:{$userId}*",
                    "*auth_user_{$userId}*",
                    "*profile_view_{$userId}*",
                    "*profile:{$userId}*",
                    
                ];
                $clearedReasons[] = 'Full clear (only ID provided)';
            }
            
            // Remove duplicates
            $patterns = array_unique($patterns);
            
            // Clear caches
            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }
            
            // Enhanced logging
            // Log::info("User cache cleared", [
            //     'user_id' => $userId,
            //     'event' => $event,
            //     'keys_cleared' => $totalCleared,
            //     'patterns' => $patterns,
            //     'reasons' => $clearedReasons,
            //     'changed_fields' => is_object($user) ? $user->getChanges() : []
            // ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear user caches for ID: {$userId}", [
                'error' => $e->getMessage()
            ]);
        }
        
        return true; 
    }

    /**
     * 🎯 Clear Gift related caches - সরাসরি Redis ব্যবহার করে
     */
    public static function clearGiftCaches($gift, $event = 'created')
    {
        try {
            $senderId = $gift->sander_id ?? $gift->sender_id ?? null;
            $receiverId = $gift->reciever_id ?? $gift->reciever_id ?? null;
            $channelName = $gift->channelName ?? null;
            
            $totalCleared = 0;
            $patterns = [];

            // Sender related patterns
            if ($senderId) {
                $patterns = array_merge($patterns, [
                    "*sent_gift:{$senderId}*",
                    "*sander_total:{$senderId}*",
                    "*gift_range:{$senderId}*",
                    "*user_sent_gift_total_{$senderId}*",
                    "*sander_total_gift_{$senderId}*",
                     "*user:{$senderId}*",
                    "*auth_user_{$senderId}*",
                    "*profile_view_{$senderId}*",
                    "*profile:{$senderId}*",
                    "*channel_gift:{$senderId}:{$channelName}*",
                ]);
            }

            // Receiver related patterns
            if ($receiverId) {
                $patterns = array_merge($patterns, [
                    "*received_gift:{$receiverId}*",
                    "*user_received_gift_total_{$receiverId}*",
                    "*today_gift:{$receiverId}:*",
                    "*top_profile:{$receiverId}*",
                    "*gift_range:{$receiverId}:*",
                    "*live:{$receiverId}:{$channelName}*",
                    "*channel_gift:{$receiverId}:{$channelName}*",
                ]);
            }
            if ($senderId==1) {
                $patterns = array_merge($patterns, [
                    "*total_reward:{$receiverId}*",
                ]);
            }

            // Channel related patterns
            if ($channelName) {
                $patterns = array_merge($patterns, [
                    
                    "*call_details_{$receiverId}_{$channelName}*",
                    "*call_details_{$senderId}_{$channelName}*",
                    "*channalwise_recived_gift:{$receiverId}:{$channelName}*",
                    "*live:{$senderId}:{$channelName}*",
                    "ranking_sander_*",
                    "ranking_receiver_*",
                    "ranking_family_*",
                    
                ]);
            }

            // Remove duplicates
            $patterns = array_unique($patterns);
            
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Gift', $gift->id ?? 0, $event, $totalCleared, [
                'sender' => $senderId,
                'receiver' => $receiverId,
                'channel' => $channelName
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to clear gift caches", [
                'gift_id' => $gift->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * 🎯 Clear UserLive related caches - সরাসরি Redis ব্যবহার করে
     */
    public static function clearUserLiveCaches($userLive, $event = 'updated')
    {
        $userId = $userLive->user_id ?? null;
        $channelName = $userLive->channelName ?? null;
        
        if (!$userId || !$channelName) return false;
        
        try {
            $patterns = [
               "*live_accept:{$userId}:{$channelName}*",
                "*preload:{$userId}:{$channelName}*",
                "*Video_Brd_Call_Details_{$userId}_{$channelName}*",
                "*pending_call_count_{$channelName}*",
                "*call_list_{$userId}_{$channelName}_*"

            ];
            
            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('UserLiveModel', $userId, $event, $totalCleared, [
                'channel' => $channelName,
                'type' => $userLive->type ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear UserLive caches", [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear LiveCall related caches - সরাসরি Redis ব্যবহার করে
     */
    public static function clearLiveCallCaches($liveCall, $event = 'updated')
    {
        $hostId = $liveCall->host_id ?? null;
        $channelName = $liveCall->channelName ?? null;
        $coHostId = $liveCall->co_host_id ?? null;
        
        if (!$hostId || !$channelName) return false;
        
        try {
            $patterns = [

                "*live_accept:{$hostId}:{$channelName}*",
                "*preload:{$hostId}:{$channelName}*",
                "*Video_Brd_Call_Details_{$hostId}_{$channelName}*",
                "*pending_call_count_{$channelName}*",
                "*call_list_{$hostId}_{$channelName}_*"
            ];
            
            if ($coHostId) {
                $patterns[] = "*live_call_status_{$hostId}_{$channelName}_{$coHostId}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('LiveCall', $liveCall->id ?? 0, $event, $totalCleared, [
                'host' => $hostId,
                'co_host' => $coHostId,
                'channel' => $channelName,
                'status' => $liveCall->status ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear LiveCall caches", [
                'live_call_id' => $liveCall->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear Kick related caches - সরাসরি Redis ব্যবহার করে
     */
    public static function clearKickCaches($kick, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($kick->user_id)) {
                $patterns[] = "*user_kicks_{$kick->user_id}*";
                $patterns[] = "*user_kicks_count_{$kick->user_id}*";
            }
            
            if (isset($kick->host_id) && isset($kick->channelName)) {
                $patterns[] = "*Video_Brd_Call_Details_{$kick->host_id}_{$kick->channelName}*";
                $patterns[] = "*live:{$kick->host_id}:{$kick->channelName}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Kick', $kick->id, $event, $totalCleared, [
                'user' => $kick->user_id ?? null,
                'host' => $kick->host_id ?? null,
                'channel' => $kick->channelName ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear Kick caches", [
                'kick_id' => $kick->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear DayTime related caches
     */
    public static function clearDayTimeCaches($dayTime, $event = 'created')
    {
        // try {
        //     $patterns = [];
            
        //     if (isset($dayTime->user_id)) {
        //         $patterns[] = "*auth_user_{$dayTime->user_id}*";
        //         $patterns[] = "*profile_view_{$dayTime->user_id}*";
        //         $patterns[] = "*host_data_{$dayTime->user_id}*";
        //     }
            
        //     if (isset($dayTime->channelName)) {
        //         $patterns[] = "*{$dayTime->channelName}*";
        //     }

        //     $totalCleared = 0;
        //     foreach ($patterns as $pattern) {
        //         $keys = Redis::keys(self::$prefix . $pattern);
        //         if (!empty($keys)) {
        //             $deleted = Redis::del($keys);
        //             $totalCleared += $deleted;
        //         }
        //     }

        //     self::log('DayTime', $dayTime->id, $event, $totalCleared, [
        //         'user' => $dayTime->user_id ?? null,
        //         'channel' => $dayTime->channelName ?? null
        //     ]);
            
        // } catch (\Exception $e) {
        //     Log::error("Failed to clear DayTime caches", [
        //         'daytime_id' => $dayTime->id,
        //         'error' => $e->getMessage()
        //     ]);
        // }
        
        return true;
    }

    /**
     * 🎯 Clear Withdraw related caches
     */
    public static function clearWithdrawCaches($withdraw, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($withdraw->host_id)) {
               
               
                $patterns[] = "*user_withdraw_{$withdraw->host_id}*";
                $patterns[] = "*today_withdraw:{$withdraw->host_id}*";
                $patterns[] = "*withdraw_range:{$withdraw->host_id}*";
                $patterns[] = "*total_withdraw:{$withdraw->host_id}*";
                $patterns[] = "*live_stats:{$withdraw->host_id}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Withdraw', $withdraw->id, $event, $totalCleared, [
                'host' => $withdraw->host_id ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear Withdraw caches", [
                'withdraw_id' => $withdraw->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear Comment related caches
     */
    public static function clearCommentCaches($comment, $event = 'created')
    {
        // try {
        //     $patterns = [];
            
        //     if (isset($comment->channelName)) {
        //         $patterns[] = "*{$comment->channelName}*";
        //     }
            
        //     if (isset($comment->user_id)) {
        //         $patterns[] = "*auth_user_{$comment->user_id}*";
        //         $patterns[] = "*profile_view_{$comment->user_id}*";
        //     }

        //     $totalCleared = 0;
        //     foreach ($patterns as $pattern) {
        //         $keys = Redis::keys(self::$prefix . $pattern);
        //         if (!empty($keys)) {
        //             $deleted = Redis::del($keys);
        //             $totalCleared += $deleted;
        //         }
        //     }

        //     self::log('Comment', $comment->id, $event, $totalCleared, [
        //         'user' => $comment->user_id ?? null,
        //         'channel' => $comment->channelName ?? null
        //     ]);
            
        // } catch (\Exception $e) {
        //     Log::error("Failed to clear Comment caches", [
        //         'comment_id' => $comment->id,
        //         'error' => $e->getMessage()
        //     ]);
        // }
        
        return true;
    }

    /**
     * 🎯 Clear Follower related caches
     */
    public static function clearFollowerCaches($follower, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($follower->user_id)) {
                $patterns[] = "*profile_view_{$follower->user_id}*";
                $patterns[] = "*user_followers_{$follower->user_id}*";
                $patterns[] = "*user_following_{$follower->user_id}*";
            }
            
            if (isset($follower->follower_id)) {
                $patterns[] = "*profile_view_{$follower->follower_id}*";
                $patterns[] = "*user_followers_{$follower->follower_id}*";
                $patterns[] = "*user_following_{$follower->follower_id}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Follower', $follower->id, $event, $totalCleared, [
                'user' => $follower->user_id ?? null,
                'follower' => $follower->follower_id ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear Follower caches", [
                'follower_id' => $follower->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear BrdAdmin related caches
     */
    public static function clearBrdAdminCaches($brdAdmin, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($brdAdmin->user_id)) {
                $patterns[] = "*brd_admin_{$brdAdmin->user_id}_*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('BrdAdmin', $brdAdmin->id, $event, $totalCleared, [
                'user' => $brdAdmin->user_id ?? null,
                'admin' => $brdAdmin->admin_id ?? null,
                'type' => $brdAdmin->type ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear BrdAdmin caches", [
                'brdadmin_id' => $brdAdmin->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear Avatar related caches
     */
    public static function clearAvatarCaches($avatar, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($avatar->user_id)) {
                $patterns[] = "*avatar:{$avatar->user_id}*";
               
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Avatar', $avatar->id, $event, $totalCleared, [
                'user' => $avatar->user_id ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear Avatar caches", [
                'avatar_id' => $avatar->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear Setting related caches
     */
    public static function clearSettingCaches($setting, $event = 'updated')
    {
        try {
            $patterns = [
                "*app_setting*",
                "*setting*",
                "*agora_setting*"
            ];

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Setting', $setting->id, $event, $totalCleared, []);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear setting cache", [
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear Slider related caches
     */
    public static function clearSliderCaches($slider, $event = 'created')
    {
        try {
            $patterns = ["*slider*"];

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Slider', $slider->id, $event, $totalCleared, []);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear slider cache", [
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear BedWord related caches
     */
    public static function clearBedWordCaches($event = 'created')
    {
        try {
            $patterns = [
                "*bad_words_list*",
                "*bad_words*"
            ];

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('BedWord', 0, $event, $totalCleared, []);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear bad words cache", [
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear OldGift related caches
     */
    public static function clearOldGiftCaches($oldGift, $event = 'created')
    {
        try {
            $patterns = [];
            
            if (isset($oldGift->sander_id)) {
                $patterns[] = "*sander_total_gift_{$oldGift->sander_id}*";
                $patterns[] = "*user_sent_gift_total_{$oldGift->sander_id}*";
            }
            
            if (isset($oldGift->reciever_id)) {
                $patterns[] = "*user_received_gift_total_{$oldGift->reciever_id}*";
                $patterns[] = "*total_reward_{$oldGift->reciever_id}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('OldGift', $oldGift->id, $event, $totalCleared, [
                'sander' => $oldGift->sander_id ?? null,
                'receiver' => $oldGift->reciever_id ?? null
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear OldGift caches", [
                'oldgift_id' => $oldGift->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 🎯 Clear AudienceJoin related caches
     */
    public static function clearAudienceJoinCaches($audienceJoin, $event = 'created')
    {
        // try {
        //     $patterns = [
        //         "*live_users_type_1*",
        //         "*live_frined_home*",
        //         "*live_top_list*",
        //         "*live_list_page_*"
        //     ];
            
        //     if (isset($audienceJoin->channelName)) {
        //         $patterns[] = "*{$audienceJoin->channelName}*";
        //     }
            
        //     if (isset($audienceJoin->user_id)) {
        //         $patterns[] = "*auth_user_{$audienceJoin->user_id}*";
        //         $patterns[] = "*profile_view_{$audienceJoin->user_id}*";
        //     }

        //     $totalCleared = 0;
        //     foreach ($patterns as $pattern) {
        //         $keys = Redis::keys(self::$prefix . $pattern);
        //         if (!empty($keys)) {
        //             $deleted = Redis::del($keys);
        //             $totalCleared += $deleted;
        //         }
        //     }

        //     self::log('AudienceJoin', $audienceJoin->id, $event, $totalCleared, [
        //         'user' => $audienceJoin->user_id ?? null,
        //         'channel' => $audienceJoin->channelName ?? null
        //     ]);
            
        // } catch (\Exception $e) {
        //     Log::error("Failed to clear AudienceJoin caches", [
        //         'audiencejoin_id' => $audienceJoin->id,
        //         'error' => $e->getMessage()
        //     ]);
        // }
        
        return true;
    }

    /**
     * 🎯 Clear Agency related caches
     */
    public static function clearAgencyCaches($agency, $event = 'updated')
    {
        // Agency affects all hosts under it
        // For now, just log it
        self::log('Agency', $agency->code ?? 0, $event, 0, [
            'name' => $agency->name ?? null
        ]);
        
        return true;
    }

    /**
     * 🎯 Bulk clear all caches for a channel (when live ends)
     */
    public static function clearChannelCaches($channelName, $hostId = null)
    {
        if (!$channelName) return false;
        
        try {
            $patterns = ["*{$channelName}*"];
            
            if ($hostId) {
                $patterns[] = "*live:{$hostId}:{$channelName}*";
                $patterns[] = "*Video_Brd_Call_Details_{$hostId}_{$channelName}*";
                $patterns[] = "*user:{$hostId}*";
            }

            $totalCleared = 0;
            foreach ($patterns as $pattern) {
                $keys = Redis::keys(self::$prefix . $pattern);
                if (!empty($keys)) {
                    $deleted = Redis::del($keys);
                    $totalCleared += $deleted;
                }
            }

            self::log('Channel', $channelName, 'ended', $totalCleared, [
                'host' => $hostId
            ]);
            
        } catch (\Exception $e) {
            Log::error("Failed to clear channel caches", [
                'channel' => $channelName,
                'error' => $e->getMessage()
            ]);
        }
        
        return true;
    }

    /**
     * 📝 Log cache clear events
     */
    private static function log($model, $id, $event, $cleared, $extra = [])
    {
        // Make sure cache_clear channel exists in config/logging.php
        Log::channel('cache_clear')->info("{$model} cache cleared", array_merge([
            'model_id' => $id,
            'event' => $event,
            'keys_cleared' => $cleared,
            'time' => now()->toDateTimeString()
        ], $extra));
    }
}
