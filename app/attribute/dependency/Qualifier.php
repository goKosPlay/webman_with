<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Qualifier
{
    public function __construct(
        public string $name
    ) {}
}
