<?php

namespace app\attribute\transaction;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Transactional
{
    public function __construct(
        public ?string $connection = null,
        public int $isolation = 0,
        public int $timeout = -1,
        public bool $readOnly = false
    ) {}
}
