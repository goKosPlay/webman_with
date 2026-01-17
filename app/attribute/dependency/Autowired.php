<?php

declare(strict_types=1);

namespace app\attribute\dependency;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Autowired
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $required = true
    ) {}
}
