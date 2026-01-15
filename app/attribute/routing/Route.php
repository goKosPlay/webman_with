<?php

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    /**
     * @param string|array $methods HTTP methods (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY)
     * @param string $path Route path (e.g., '/user/{id}')
     * @param string|null $name Route name for url generation
     * @param array $middleware Middleware list
     */
    public function __construct(
        public string|array $methods = 'GET',
        public string $path = '',
        public ?string $name = null,
        public array $middleware = []
    ) {
        if (is_string($this->methods)) {
            $this->methods = strtoupper($this->methods) === 'ANY' 
                ? ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS']
                : [strtoupper($this->methods)];
        } else {
            $this->methods = array_map('strtoupper', $this->methods);
        }
    }
}
