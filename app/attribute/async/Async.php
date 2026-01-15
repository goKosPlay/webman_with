<?php

namespace app\attribute\async;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Async
{
    public function __construct(
        public ?string $executor = null
    ) {}
}
