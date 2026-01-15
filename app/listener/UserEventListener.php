<?php

namespace app\listener;

use app\attribute\dependency\Service;
use app\attribute\event\EventListener;
use support\Log;

#[Service]
class UserEventListener
{
    #[EventListener(events: 'user.created', priority: 10)]
    public function onUserCreated($event)
    {
        Log::info('User created event triggered', [
            'user_id' => $event['user_id'] ?? null,
            'email' => $event['email'] ?? null,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        
        echo "✓ User created: {$event['email']}\n";
    }
    
    #[EventListener(events: 'user.updated', priority: 5)]
    public function onUserUpdated($event)
    {
        Log::info('User updated event triggered', [
            'user_id' => $event['user_id'] ?? null,
            'changes' => $event['changes'] ?? [],
        ]);
        
        echo "✓ User updated: ID {$event['user_id']}\n";
    }
    
    #[EventListener(events: 'user.deleted', priority: 5)]
    public function onUserDeleted($event)
    {
        Log::info('User deleted event triggered', [
            'user_id' => $event['user_id'] ?? null,
        ]);
        
        echo "✓ User deleted: ID {$event['user_id']}\n";
    }
    
    #[EventListener(events: ['user.login', 'user.logout'], priority: 15)]
    public function onUserAuthEvent($event)
    {
        $action = $event['action'] ?? 'unknown';
        $userId = $event['user_id'] ?? null;
        
        Log::info("User {$action} event", [
            'user_id' => $userId,
            'ip' => $event['ip'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
        ]);
        
        echo "✓ User {$action}: ID {$userId}\n";
    }
}
