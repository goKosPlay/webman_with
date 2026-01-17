<?php

declare(strict_types=1);

namespace app\attribute\routing;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
class Route
{
    public readonly array $methods;
    
    /**
     * @param string|array $methods HTTP methods (GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY)
     * @param string $path Route path (e.g., '/user/{id}')
     * @param string|null $name Route name for url generation
     * @param array $middleware Middleware list
     */
    public function __construct(
        string|array $methods = 'GET',
        public readonly string $path = '',
        public readonly ?string $name = null,
        public readonly array $middleware = []
    ) {
        $this->methods = match(true) {
            is_array($methods) => array_map('strtoupper', $methods),
            strtoupper($methods) === 'ANY' => ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'HEAD', 'OPTIONS'],
            default => [strtoupper($methods)]
        };
    }
}
