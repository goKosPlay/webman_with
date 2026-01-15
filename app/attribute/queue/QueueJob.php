<?php

namespace app\attribute\queue;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class QueueJob
{
    public function __construct(
        public string $queue = 'default',
        public int $maxRetries = 3,
        public int $retryDelay = 60,
        public int $timeout = 300
    ) {}
}
