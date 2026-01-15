<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Bean
{
    public function __construct(
        public ?string $name = null,
        public bool $singleton = true
    ) {}
}
