<?php

namespace app\attribute\schedule;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Scheduled
{
    public function __construct(
        public ?string $cron = null,
        public ?int $fixedDelay = null,
        public ?int $fixedRate = null,
        public ?int $initialDelay = 0,
        public string $timeZone = 'UTC',
        public bool $enabled = true
    ) {
        if (!$this->cron && !$this->fixedDelay && !$this->fixedRate) {
            throw new \InvalidArgumentException('Must specify cron, fixedDelay, or fixedRate');
        }
    }
}
