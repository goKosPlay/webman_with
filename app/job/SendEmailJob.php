<?php

namespace app\job;

use app\attribute\queue\QueueJob;
use app\attribute\dependency\Autowired;
use support\Log;

#[QueueJob(queue: 'emails', maxRetries: 3, retryDelay: 60)]
class SendEmailJob
{
    #[Autowired]
    private ?\app\service\UserService $userService;
    
    public function handle(array $data): void
    {
        $to = $data['to'] ?? null;
        $subject = $data['subject'] ?? null;
        $body = $data['body'] ?? null;
        
        if (!$to || !$subject) {
            throw new \InvalidArgumentException('Missing required email parameters');
        }
        
        // 模拟发送邮件
        sleep(1);
        
        Log::info("Email sent", [
            'to' => $to,
            'subject' => $subject
        ]);
        
        echo "📧 Email sent to {$to}: {$subject}\n";
    }
}
