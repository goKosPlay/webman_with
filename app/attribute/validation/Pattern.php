<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Pattern
{
    public function __construct(
        public string $pattern,
        public ?string $message = null
    ) {}
}
