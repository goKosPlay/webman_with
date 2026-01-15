<?php

namespace app\listener;

use app\attribute\dependency\Service;
use app\attribute\event\EventListener;
use support\Redis;
use support\Log;

#[Service]
class CacheInvalidationListener
{
    #[EventListener(events: 'user.updated', priority: 100)]
    public function invalidateUserCache($event)
    {
        $userId = $event['user_id'] ?? null;
        
        if (!$userId) {
            return;
        }
        
        $cacheKey = "user:{$userId}";
        
        try {
            Redis::del($cacheKey);
            Log::info('Cache invalidated', ['key' => $cacheKey]);
            echo "🗑️  Cache invalidated: {$cacheKey}\n";
        } catch (\Exception $e) {
            Log::error('Failed to invalidate cache', [
                'key' => $cacheKey,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    #[EventListener(events: 'user.deleted', priority: 100)]
    public function deleteUserCache($event)
    {
        $userId = $event['user_id'] ?? null;
        
        if (!$userId) {
            return;
        }
        
        $cacheKeys = [
            "user:{$userId}",
            "user:{$userId}:profile",
            "user:{$userId}:settings",
        ];
        
        try {
            foreach ($cacheKeys as $key) {
                Redis::del($key);
            }
            Log::info('User cache deleted', ['user_id' => $userId]);
            echo "🗑️  User cache deleted for ID: {$userId}\n";
        } catch (\Exception $e) {
            Log::error('Failed to delete user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    #[EventListener(events: ['order.updated', 'order.cancelled'], priority: 50)]
    public function invalidateOrderCache($event)
    {
        $orderId = $event['order_id'] ?? null;
        $userId = $event['user_id'] ?? null;
        
        if (!$orderId) {
            return;
        }
        
        $cacheKeys = [
            "order:{$orderId}",
        ];
        
        if ($userId) {
            $cacheKeys[] = "user:{$userId}:orders";
        }
        
        try {
            foreach ($cacheKeys as $key) {
                Redis::del($key);
            }
            Log::info('Order cache invalidated', ['order_id' => $orderId]);
            echo "🗑️  Order cache invalidated: #{$orderId}\n";
        } catch (\Exception $e) {
            Log::error('Failed to invalidate order cache', [
                'order_id' => $orderId,
                'error' => $e->getMessage()
            ]);
        }
    }
}
