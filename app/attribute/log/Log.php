<?php

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Log
{
    public function __construct(
        public string $message = '',
        public string $level = 'info',
        public ?string $channel = null,
        public array $context = [],
        public bool $includeParams = false,
        public bool $includeResult = false,
        public bool $before = false,
        public bool $after = true
    ) {}
}
