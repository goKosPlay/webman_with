<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Numeric
{
    public function __construct(
        public ?string $message = null
    ) {}
}
