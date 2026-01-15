<?php

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class LogPerformance
{
    public function __construct(
        public string $message = '',
        public ?string $channel = null,
        public float $threshold = 0.1, // 秒
        public bool $logSlowOnly = false,
        public bool $includeParams = false,
        public bool $includeResult = false,
        public array $context = []
    ) {}
}
