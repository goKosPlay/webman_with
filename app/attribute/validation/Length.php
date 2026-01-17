<?php

declare(strict_types=1);

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Length
{
    public function __construct(
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?string $message = null
    ) {}
}
