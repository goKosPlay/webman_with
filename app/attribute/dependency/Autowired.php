<?php

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Autowired
{
    public function __construct(
        public ?string $name = null,
        public bool $required = true
    ) {}
}
