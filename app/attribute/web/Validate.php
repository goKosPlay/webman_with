<?php

namespace app\attribute\web;

use Attribute;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class Validate
{
    public function __construct(
        public array $rules = [],
        public ?string $message = null
    ) {}
}
