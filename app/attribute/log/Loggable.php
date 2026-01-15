<?php

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Loggable
{
    public function __construct(
        public ?string $channel = null,
        public ?string $level = null,
        public bool $logParams = true,
        public bool $logResult = false,
        public bool $logException = true,
        public array $context = []
    ) {}
}
