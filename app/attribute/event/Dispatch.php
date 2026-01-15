<?php

namespace app\attribute\event;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Dispatch
{
    public function __construct(
        public ?string $queue = null,
        public ?int $delay = null,
        public ?int $priority = null
    ) {}
}
