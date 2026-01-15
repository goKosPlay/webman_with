<?php

namespace app\job;

use app\attribute\queue\QueueJob;
use support\Log;

#[QueueJob(queue: 'images', maxRetries: 2, timeout: 600)]
class ProcessImageJob
{
    public function handle(array $data): void
    {
        $imagePath = $data['path'] ?? null;
        $operations = $data['operations'] ?? [];
        
        if (!$imagePath) {
            throw new \InvalidArgumentException('Image path is required');
        }
        
        echo "🖼️  Processing image: {$imagePath}\n";
        
        foreach ($operations as $operation) {
            echo "  - Applying operation: {$operation}\n";
            sleep(1);
        }
        
        Log::info("Image processed", [
            'path' => $imagePath,
            'operations' => $operations
        ]);
        
        echo "✅ Image processing completed\n";
    }
}
