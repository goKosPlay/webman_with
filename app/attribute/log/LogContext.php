<?php

namespace app\attribute\log;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_PARAMETER)]
class LogContext
{
    public function __construct(
        public array $context = [],
        public ?string $key = null
    ) {}
}
