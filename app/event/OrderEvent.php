<?php

namespace app\event;

class OrderEvent
{
    public static function created(array $orderData): array
    {
        return [
            'action' => 'created',
            'order_id' => $orderData['id'] ?? null,
            'user_id' => $orderData['user_id'] ?? null,
            'email' => $orderData['email'] ?? null,
            'total' => $orderData['total'] ?? 0,
            'items' => $orderData['items'] ?? [],
            'timestamp' => time(),
        ];
    }
    
    public static function updated(int $orderId, array $changes, ?int $userId = null): array
    {
        return [
            'action' => 'updated',
            'order_id' => $orderId,
            'user_id' => $userId,
            'changes' => $changes,
            'timestamp' => time(),
        ];
    }
    
    public static function paid(int $orderId, string $paymentMethod, ?string $email = null): array
    {
        return [
            'action' => 'paid',
            'order_id' => $orderId,
            'payment_method' => $paymentMethod,
            'email' => $email,
            'timestamp' => time(),
        ];
    }
    
    public static function shipped(int $orderId, string $trackingNumber, ?string $email = null): array
    {
        return [
            'action' => 'shipped',
            'order_id' => $orderId,
            'tracking_number' => $trackingNumber,
            'email' => $email,
            'timestamp' => time(),
        ];
    }
    
    public static function cancelled(int $orderId, ?string $reason = null, ?int $userId = null): array
    {
        return [
            'action' => 'cancelled',
            'order_id' => $orderId,
            'user_id' => $userId,
            'reason' => $reason,
            'timestamp' => time(),
        ];
    }
    
    public static function delivered(int $orderId, ?string $email = null): array
    {
        return [
            'action' => 'delivered',
            'order_id' => $orderId,
            'email' => $email,
            'timestamp' => time(),
        ];
    }
}
