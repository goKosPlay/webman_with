<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Configuration
{
    public function __construct(
        public ?string $prefix = null
    ) {}
}
