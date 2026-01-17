<?php

declare(strict_types=1);

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Log
{
    public function __construct(
        public readonly string $message = '',
        public readonly string $level = 'info',
        public readonly ?string $channel = null,
        public readonly array $context = [],
        public readonly bool $includeParams = false,
        public readonly bool $includeResult = false,
        public readonly bool $before = false,
        public readonly bool $after = true
    ) {}
}
