<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class In
{
    public function __construct(
        public array $values,
        public ?string $message = null
    ) {}
}
