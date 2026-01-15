<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Max
{
    public function __construct(
        public int|float $value,
        public ?string $message = null
    ) {}
}
