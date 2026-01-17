<?php

declare(strict_types=1);

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Email
{
    public function __construct(
        public readonly ?string $message = null
    ) {}
}
