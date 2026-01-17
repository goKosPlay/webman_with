<?php

declare(strict_types=1);

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Pattern
{
    public function __construct(
        public readonly string $pattern,
        public readonly ?string $message = null
    ) {}
}
