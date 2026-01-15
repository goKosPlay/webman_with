<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Conditional
{
    public function __construct(
        public string $condition
    ) {}
}
