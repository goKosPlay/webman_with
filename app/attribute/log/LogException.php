<?php

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class LogException
{
    public function __construct(
        public string $message = '',
        public ?string $channel = null,
        public bool $includeTrace = false,
        public bool $includeParams = true,
        public array $context = []
    ) {}
}
