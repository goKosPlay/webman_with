<?php

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping
{
    public function __construct(
        public string $path = '',
        public ?string $name = null,
        public array $middleware = []
    ) {}
}
