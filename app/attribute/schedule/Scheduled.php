<?php

declare(strict_types=1);

namespace app\attribute\schedule;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Scheduled
{
    public function __construct(
        public readonly ?string $cron = null,
        public readonly ?int $fixedDelay = null,
        public readonly ?int $fixedRate = null,
        public readonly ?int $initialDelay = 0,
        public readonly string $timeZone = 'UTC',
        public readonly bool $enabled = true
    ) {
        if (!$this->cron && !$this->fixedDelay && !$this->fixedRate) {
            throw new \InvalidArgumentException('Must specify cron, fixedDelay, or fixedRate');
        }
    }
}
