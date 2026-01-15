<?php

namespace app\listener;

use app\attribute\dependency\Service;
use app\attribute\event\EventListener;
use support\Db;
use support\Log;

#[Service]
class AuditLogListener
{
    #[EventListener(events: ['user.created', 'user.updated', 'user.deleted'], priority: 1)]
    public function logUserActivity($event)
    {
        $this->createAuditLog([
            'entity_type' => 'user',
            'entity_id' => $event['user_id'] ?? null,
            'action' => $event['action'] ?? 'unknown',
            'user_id' => $event['operator_id'] ?? null,
            'ip_address' => $event['ip'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
            'changes' => json_encode($event['changes'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    #[EventListener(events: ['order.created', 'order.updated', 'order.cancelled'], priority: 1)]
    public function logOrderActivity($event)
    {
        $this->createAuditLog([
            'entity_type' => 'order',
            'entity_id' => $event['order_id'] ?? null,
            'action' => $event['action'] ?? 'unknown',
            'user_id' => $event['user_id'] ?? null,
            'ip_address' => $event['ip'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
            'changes' => json_encode($event['changes'] ?? []),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    #[EventListener(events: ['user.login', 'user.logout', 'user.login.failed'], priority: 1)]
    public function logAuthActivity($event)
    {
        $this->createAuditLog([
            'entity_type' => 'auth',
            'entity_id' => $event['user_id'] ?? null,
            'action' => $event['action'] ?? 'unknown',
            'user_id' => $event['user_id'] ?? null,
            'ip_address' => $event['ip'] ?? null,
            'user_agent' => $event['user_agent'] ?? null,
            'changes' => json_encode([
                'success' => $event['success'] ?? true,
                'reason' => $event['reason'] ?? null,
            ]),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
    
    protected function createAuditLog(array $data): void
    {
        try {
            Log::info('Audit log created', $data);
            echo "📝 Audit log: {$data['entity_type']}.{$data['action']}\n";
        } catch (\Exception $e) {
            Log::error('Failed to create audit log', [
                'error' => $e->getMessage(),
                'data' => $data
            ]);
        }
    }
}
