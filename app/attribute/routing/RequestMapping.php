<?php

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
class RequestMapping
{
    public function __construct(
        public string $path = '',
        public string|array $methods = 'GET',
        public ?string $name = null,
        public array $middleware = []
    ) {
        if (is_string($this->methods)) {
            $this->methods = $this->methods === 'ANY' 
                ? ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']
                : [strtoupper($this->methods)];
        } else {
            $this->methods = array_map('strtoupper', $this->methods);
        }
    }
}
