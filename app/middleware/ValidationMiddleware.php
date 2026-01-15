<?php

namespace app\middleware;

use app\support\Validator;
use Webman\MiddlewareInterface;
use Webman\Http\Response;
use Webman\Http\Request;

class ValidationMiddleware implements MiddlewareInterface
{
    public function process(Request $request, callable $next): Response
    {
        // 中间件逻辑可以在这里实现
        // 实际验证会在控制器方法中通过 Validator 进行
        
        return $next($request);
    }
}
