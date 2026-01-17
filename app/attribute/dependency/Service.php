<?php

declare(strict_types=1);

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
class Service
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $singleton = true
    ) {}
}
