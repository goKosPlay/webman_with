<?php

namespace app\support;

class ControllerProxy
{
    private string $className;
    private string $methodName;
    private static array $instances = [];
    
    public function __construct(string $className, string $methodName)
    {
        $this->className = $className;
        $this->methodName = $methodName;
    }
    
    public function __invoke(...$args)
    {
        $injector = ControllerInjector::getInstance();
        
        // 每次请求都创建新的控制器实例
        if ($injector->needsInjection($this->className)) {
            $instance = $injector->make($this->className);
        } else {
            $instance = new $this->className();
        }
        
        // 调用方法
        return call_user_func_array([$instance, $this->methodName], $args);
    }
}
