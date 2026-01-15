<?php

namespace app\job;

use app\attribute\queue\QueueJob;
use support\Log;

#[QueueJob(queue: 'exports', maxRetries: 2)]
class ExportDataJob
{
    public function handle(array $data): void
    {
        $format = $data['format'] ?? 'csv';
        $filters = $data['filters'] ?? [];
        $userId = $data['user_id'] ?? null;
        
        echo "📤 Exporting data to {$format} format\n";
        echo "  Filters: " . json_encode($filters) . "\n";
        
        // 模拟数据导出
        sleep(2);
        
        $filename = "export_{$userId}_" . time() . ".{$format}";
        
        Log::info("Data exported", [
            'user_id' => $userId,
            'format' => $format,
            'filename' => $filename
        ]);
        
        echo "✅ Data exported to {$filename}\n";
    }
}
