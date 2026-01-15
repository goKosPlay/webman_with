<?php

namespace app\attribute\cache;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable
{
    public function __construct(
        public ?string $key = null,
        public ?int $ttl = 3600,
        public ?string $store = null,
        public ?string $condition = null,
        public ?string $unless = null
    ) {}
}
