<?php

declare(strict_types=1);

namespace app\attribute\cache;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Cacheable
{
    public function __construct(
        public readonly ?string $key = null,
        public readonly ?int $ttl = 3600,
        public readonly ?string $store = null,
        public readonly ?string $condition = null,
        public readonly ?string $unless = null
    ) {}
}
