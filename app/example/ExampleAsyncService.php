<?php

namespace app\example;

use app\attribute\dependency\Service;
use app\attribute\async\Async;
use support\Log;

#[Service]
class ExampleAsyncService
{
    #[Async]
    public function sendEmail(string $to, string $subject, string $body): void
    {
        // 模拟耗时的邮件发送操作
        sleep(2);
        
        Log::info("Email sent to {$to}: {$subject}");
        echo "Email sent to {$to}: {$subject}\n";
    }
    
    #[Async]
    public function processImage(string $imagePath): void
    {
        // 模拟图片处理
        sleep(3);
        
        Log::info("Image processed: {$imagePath}");
        echo "Image processed: {$imagePath}\n";
    }
    
    #[Async]
    public function generateReport(int $userId): void
    {
        // 模拟报表生成
        sleep(5);
        
        Log::info("Report generated for user {$userId}");
        echo "Report generated for user {$userId}\n";
    }
    
    // 普通同步方法
    public function quickOperation(): string
    {
        return "This is executed synchronously";
    }
}
