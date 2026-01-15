<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Length
{
    public function __construct(
        public ?int $min = null,
        public ?int $max = null,
        public ?string $message = null
    ) {}
}
