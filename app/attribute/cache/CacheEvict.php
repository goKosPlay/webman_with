<?php

namespace app\attribute\cache;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class CacheEvict
{
    public function __construct(
        public ?string $key = null,
        public bool $allEntries = false,
        public ?string $store = null,
        public bool $beforeInvocation = false
    ) {}
}
