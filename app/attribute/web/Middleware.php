<?php

namespace app\attribute\web;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Middleware
{
    public function __construct(
        public string|array $middleware,
        public int $priority = 0
    ) {
        if (is_string($this->middleware)) {
            $this->middleware = [$this->middleware];
        }
    }
}
