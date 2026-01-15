<?php

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class GetMapping
{
    public function __construct(
        public string $path = '',
        public ?string $name = null,
        public array $middleware = []
    ) {}
}
