<?php

declare(strict_types=1);

namespace app\attribute\transaction;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Transactional
{
    public function __construct(
        public readonly ?string $connection = null,
        public readonly int $isolation = 0,
        public readonly int $timeout = -1,
        public readonly bool $readOnly = false
    ) {}
}
