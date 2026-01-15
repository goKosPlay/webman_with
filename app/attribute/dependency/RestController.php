<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class RestController
{
    public function __construct(
        public string $prefix = '',
        public array $middleware = []
    ) {}
}
