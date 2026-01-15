<?php

namespace app\support;

use app\attribute\dependency\{Autowired, Inject, Lazy};
use ReflectionClass;
use ReflectionParameter;

class ControllerInjector
{
    protected static ?self $instance = null;
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 创建控制器实例并自动注入依赖
     */
    public function make(string $className): object
    {
        $container = Container::getInstance();
        
        // 使用容器的 make 方法，它会处理构造函数注入和属性注入
        return $container->make($className);
    }
    
    /**
     * 解析构造函数依赖
     */
    protected function resolveDependencies(array $parameters): array
    {
        $dependencies = [];
        $container = Container::getInstance();
        
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            // 如果没有类型或是内置类型，使用默认值
            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {
                    throw new \Exception("Cannot resolve parameter {$parameter->getName()} in {$parameter->getDeclaringClass()->getName()}");
                }
                continue;
            }
            
            // 获取类型名称
            $typeName = $type->getName();
            
            try {
                // 从容器中获取依赖
                $dependencies[] = $container->make($typeName);
            } catch (\Exception $e) {
                // 如果参数可选，使用 null
                if ($parameter->isOptional() || $parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new \Exception("Cannot resolve dependency {$typeName} for parameter {$parameter->getName()}: " . $e->getMessage());
                }
            }
        }
        
        return $dependencies;
    }
    
    /**
     * 检查类是否需要依赖注入
     */
    public function needsInjection(string $className): bool
    {
        try {
            $reflector = new ReflectionClass($className);
            
            // 检查构造函数参数
            $constructor = $reflector->getConstructor();
            if ($constructor !== null && count($constructor->getParameters()) > 0) {
                return true;
            }
            
            // 检查属性注入
            foreach ($reflector->getProperties() as $property) {
                $autowired = $property->getAttributes(Autowired::class);
                $inject = $property->getAttributes(Inject::class);
                $lazy = $property->getAttributes(Lazy::class);
                
                if (!empty($autowired) || !empty($inject) || !empty($lazy)) {
                    return true;
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
