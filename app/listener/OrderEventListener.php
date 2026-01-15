<?php

namespace app\listener;

use app\attribute\dependency\Service;
use app\attribute\event\EventListener;
use support\Log;

#[Service]
class OrderEventListener
{
    #[EventListener(events: 'order.created', priority: 10)]
    public function onOrderCreated($event)
    {
        Log::info('Order created', [
            'order_id' => $event['order_id'] ?? null,
            'user_id' => $event['user_id'] ?? null,
            'total' => $event['total'] ?? 0,
        ]);
        
        echo "✓ Order created: #{$event['order_id']}, Total: {$event['total']}\n";
    }
    
    #[EventListener(events: 'order.paid', priority: 20)]
    public function onOrderPaid($event)
    {
        Log::info('Order paid', [
            'order_id' => $event['order_id'] ?? null,
            'payment_method' => $event['payment_method'] ?? null,
        ]);
        
        echo "✓ Order paid: #{$event['order_id']}\n";
    }
    
    #[EventListener(events: 'order.shipped', priority: 5)]
    public function onOrderShipped($event)
    {
        Log::info('Order shipped', [
            'order_id' => $event['order_id'] ?? null,
            'tracking_number' => $event['tracking_number'] ?? null,
        ]);
        
        echo "✓ Order shipped: #{$event['order_id']}, Tracking: {$event['tracking_number']}\n";
    }
    
    #[EventListener(events: 'order.cancelled', priority: 10)]
    public function onOrderCancelled($event)
    {
        Log::info('Order cancelled', [
            'order_id' => $event['order_id'] ?? null,
            'reason' => $event['reason'] ?? null,
        ]);
        
        echo "✓ Order cancelled: #{$event['order_id']}\n";
    }
}
