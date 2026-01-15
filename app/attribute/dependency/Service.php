<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Service
{
    public function __construct(
        public ?string $name = null,
        public bool $singleton = true
    ) {}
}
