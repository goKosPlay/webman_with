<?php

namespace app\support;

use app\attribute\async\Async;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionClass;
use ReflectionMethod;
use Workerman\Timer;

class AsyncManager
{
    protected static ?self $instance = null;
    protected array $asyncMethods = [];
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 扫描并注册异步方法
     */
    public function scanAndRegister(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->loadClassFromFile($file->getPathname());
            }
        }
    }
    
    /**
     * 从文件加载类并注册异步方法
     */
    protected function loadClassFromFile(string $filePath): void
    {
        $className = $this->getClassNameFromFile($filePath);
        
        if (!$className || !class_exists($className)) {
            return;
        }
        
        try {
            $reflection = new ReflectionClass($className);
            
            if ($reflection->isAbstract() || $reflection->isInterface() || $reflection->isTrait()) {
                return;
            }
            
            $this->registerAsyncMethods($reflection);
        } catch (\Throwable $e) {
            error_log("AsyncManager: Failed to load async methods from {$className}: " . $e->getMessage());
        }
    }
    
    /**
     * 注册类中的异步方法
     */
    protected function registerAsyncMethods(ReflectionClass $class): void
    {
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);
        
        foreach ($methods as $method) {
            if ($method->isStatic() || $method->isConstructor() || $method->isDestructor()) {
                continue;
            }
            
            $attributes = $method->getAttributes(Async::class);
            
            foreach ($attributes as $attribute) {
                try {
                    $asyncAttr = $attribute->newInstance();
                    
                    $key = $class->getName() . '::' . $method->getName();
                    $this->asyncMethods[$key] = [
                        'class' => $class->getName(),
                        'method' => $method->getName(),
                    ];
                } catch (\Throwable $e) {
                    error_log("AsyncManager: Failed to register async method {$class->getName()}::{$method->getName()}: " . $e->getMessage());
                }
            }
        }
    }
    
    /**
     * 异步执行方法
     */
    public function executeAsync(string $className, string $methodName, array $args = []): void
    {
        Timer::add(0.001, function() use ($className, $methodName, $args) {
            try {
                $container = Container::getInstance();
                $instance = $container->make($className);
                
                call_user_func_array([$instance, $methodName], $args);
            } catch (\Throwable $e) {
                error_log("AsyncManager: Error executing async method {$className}::{$methodName}: " . $e->getMessage());
            }
        }, [], false);
    }
    
    /**
     * 获取类名从文件路径
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = file_get_contents($filePath);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return null;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return null;
        }
        
        return $namespaceMatch[1] . '\\' . $classMatch[1];
    }
    
    /**
     * 获取所有注册的异步方法
     */
    public function getAsyncMethods(): array
    {
        return $this->asyncMethods;
    }
}
