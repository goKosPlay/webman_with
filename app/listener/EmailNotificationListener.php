<?php

namespace app\listener;

use app\attribute\dependency\Service;
use app\attribute\event\EventListener;
use app\attribute\async\Async;
use support\Log;

#[Service]
class EmailNotificationListener
{
    #[EventListener(events: 'user.created', priority: 5)]
    #[Async]
    public function sendWelcomeEmail($event)
    {
        $email = $event['email'] ?? null;
        
        if (!$email) {
            return;
        }
        
        Log::info('Sending welcome email', ['email' => $email]);
        
        echo "📧 Sending welcome email to: {$email}\n";
    }
    
    #[EventListener(events: 'order.paid', priority: 5)]
    #[Async]
    public function sendOrderConfirmation($event)
    {
        $orderId = $event['order_id'] ?? null;
        $email = $event['email'] ?? null;
        
        if (!$email) {
            return;
        }
        
        Log::info('Sending order confirmation email', [
            'order_id' => $orderId,
            'email' => $email
        ]);
        
        echo "📧 Sending order confirmation to: {$email}\n";
    }
    
    #[EventListener(events: 'order.shipped', priority: 3)]
    #[Async]
    public function sendShippingNotification($event)
    {
        $orderId = $event['order_id'] ?? null;
        $email = $event['email'] ?? null;
        $trackingNumber = $event['tracking_number'] ?? null;
        
        if (!$email) {
            return;
        }
        
        Log::info('Sending shipping notification', [
            'order_id' => $orderId,
            'email' => $email,
            'tracking_number' => $trackingNumber
        ]);
        
        echo "📧 Sending shipping notification to: {$email}\n";
    }
    
    #[EventListener(events: 'user.password.reset', priority: 20)]
    public function sendPasswordResetEmail($event)
    {
        $email = $event['email'] ?? null;
        $token = $event['token'] ?? null;
        
        if (!$email || !$token) {
            return;
        }
        
        Log::info('Sending password reset email', ['email' => $email]);
        
        echo "📧 Sending password reset email to: {$email}\n";
    }
}
