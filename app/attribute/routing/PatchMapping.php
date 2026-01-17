<?php

declare(strict_types=1);

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping
{
    public function __construct(
        public readonly string $path = '',
        public readonly ?string $name = null,
        public readonly array $middleware = []
    ) {}
}
