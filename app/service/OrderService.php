<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\attribute\transaction\Transactional;
use app\event\OrderEvent;
use Webman\Event\Event;
use support\Log;

#[Service(singleton: true)]
class OrderService
{
    #[Transactional]
    public function createOrder(array $data): array
    {
        $order = [
            'id' => rand(10000, 99999),
            'user_id' => $data['user_id'] ?? null,
            'email' => $data['email'] ?? null,
            'total' => $data['total'] ?? 0,
            'items' => $data['items'] ?? [],
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        Log::info('Order created in database', $order);
        
        Event::emit('order.created', OrderEvent::created($order));
        
        return $order;
    }
    
    #[Transactional]
    public function updateOrder(int $orderId, array $changes, ?int $userId = null): bool
    {
        Log::info('Order updated in database', [
            'order_id' => $orderId,
            'changes' => $changes
        ]);
        
        Event::emit('order.updated', OrderEvent::updated($orderId, $changes, $userId));
        
        return true;
    }
    
    #[Transactional]
    public function markAsPaid(int $orderId, string $paymentMethod, ?string $email = null): bool
    {
        Log::info('Order marked as paid', [
            'order_id' => $orderId,
            'payment_method' => $paymentMethod
        ]);
        
        Event::emit('order.paid', OrderEvent::paid($orderId, $paymentMethod, $email));
        
        return true;
    }
    
    #[Transactional]
    public function shipOrder(int $orderId, string $trackingNumber, ?string $email = null): bool
    {
        Log::info('Order shipped', [
            'order_id' => $orderId,
            'tracking_number' => $trackingNumber
        ]);
        
        Event::emit('order.shipped', OrderEvent::shipped($orderId, $trackingNumber, $email));
        
        return true;
    }
    
    #[Transactional]
    public function cancelOrder(int $orderId, ?string $reason = null, ?int $userId = null): bool
    {
        Log::info('Order cancelled', [
            'order_id' => $orderId,
            'reason' => $reason
        ]);
        
        Event::emit('order.cancelled', OrderEvent::cancelled($orderId, $reason, $userId));
        
        return true;
    }
    
    public function markAsDelivered(int $orderId, ?string $email = null): bool
    {
        Log::info('Order delivered', ['order_id' => $orderId]);
        
        Event::emit('order.delivered', OrderEvent::delivered($orderId, $email));
        
        return true;
    }
}
