<?php

namespace app\support;

use app\attribute\cache\{Cacheable, CachePut, CacheEvict};
use ReflectionClass;
use ReflectionMethod;
use support\Redis;

class CacheManager
{
    protected static ?self $instance = null;
    protected array $handlers = [];
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function handleCacheable(object $instance, ReflectionMethod $method, array $args): mixed
    {
        $attributes = $method->getAttributes(Cacheable::class);
        
        if (empty($attributes)) {
            return $method->invoke($instance, ...$args);
        }
        
        $attr = $attributes[0]->newInstance();
        
        if ($attr->condition && !$this->evaluateCondition($attr->condition, $args)) {
            return $method->invoke($instance, ...$args);
        }
        
        $key = $this->generateKey($attr->key, $method, $args);
        $store = $attr->store ?? 'default';
        
        $cached = $this->get($key, $store);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $result = $method->invoke($instance, ...$args);
        
        if ($attr->unless && $this->evaluateCondition($attr->unless, $args)) {
            return $result;
        }
        
        $this->set($key, $result, $attr->ttl, $store);
        
        return $result;
    }
    
    public function handleCachePut(object $instance, ReflectionMethod $method, array $args): mixed
    {
        $result = $method->invoke($instance, ...$args);
        
        $attributes = $method->getAttributes(CachePut::class);
        
        if (empty($attributes)) {
            return $result;
        }
        
        $attr = $attributes[0]->newInstance();
        
        if ($attr->condition && !$this->evaluateCondition($attr->condition, $args)) {
            return $result;
        }
        
        if ($attr->unless && $this->evaluateCondition($attr->unless, $args)) {
            return $result;
        }
        
        $key = $this->generateKey($attr->key, $method, $args);
        $store = $attr->store ?? 'default';
        
        $this->set($key, $result, $attr->ttl, $store);
        
        return $result;
    }
    
    public function handleCacheEvict(object $instance, ReflectionMethod $method, array $args): mixed
    {
        $attributes = $method->getAttributes(CacheEvict::class);
        
        if (empty($attributes)) {
            return $method->invoke($instance, ...$args);
        }
        
        $attr = $attributes[0]->newInstance();
        
        if ($attr->beforeInvocation) {
            $this->evictCache($attr, $method, $args);
            return $method->invoke($instance, ...$args);
        }
        
        $result = $method->invoke($instance, ...$args);
        $this->evictCache($attr, $method, $args);
        
        return $result;
    }
    
    protected function evictCache(CacheEvict $attr, ReflectionMethod $method, array $args): void
    {
        $store = $attr->store ?? 'default';
        
        if ($attr->allEntries) {
            $this->flush($store);
        } else {
            $key = $this->generateKey($attr->key, $method, $args);
            $this->delete($key, $store);
        }
    }
    
    protected function generateKey(?string $template, ReflectionMethod $method, array $args): string
    {
        if ($template === null) {
            $className = $method->getDeclaringClass()->getShortName();
            $methodName = $method->getName();
            return strtolower("{$className}:{$methodName}:" . md5(serialize($args)));
        }
        
        $params = $method->getParameters();
        $replacements = [];
        
        foreach ($params as $index => $param) {
            $name = $param->getName();
            $value = $args[$index] ?? null;
            $replacements["{{$name}}"] = $value;
        }
        
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }
    
    protected function evaluateCondition(string $condition, array $args): bool
    {
        return true;
    }
    
    protected function get(string $key, string $store): mixed
    {
        try {
            if ($store === 'redis' || $store === 'default') {
                $value = Redis::get($key);
                return $value ? unserialize($value) : null;
            }
            
            return cache()->get($key);
        } catch (\Throwable $e) {
            error_log("CacheManager: Failed to get cache key {$key}: " . $e->getMessage());
            return null;
        }
    }
    
    protected function set(string $key, mixed $value, ?int $ttl, string $store): bool
    {
        try {
            if ($store === 'redis' || $store === 'default') {
                if ($ttl) {
                    return Redis::setex($key, $ttl, serialize($value));
                }
                return Redis::set($key, serialize($value));
            }
            
            return cache()->set($key, $value, $ttl);
        } catch (\Throwable $e) {
            error_log("CacheManager: Failed to set cache key {$key}: " . $e->getMessage());
            return false;
        }
    }
    
    protected function delete(string $key, string $store): bool
    {
        try {
            if ($store === 'redis' || $store === 'default') {
                return Redis::del($key) > 0;
            }
            
            return cache()->delete($key);
        } catch (\Throwable $e) {
            error_log("CacheManager: Failed to delete cache key {$key}: " . $e->getMessage());
            return false;
        }
    }
    
    protected function flush(string $store): bool
    {
        try {
            if ($store === 'redis' || $store === 'default') {
                return Redis::flushDB();
            }
            
            return cache()->clear();
        } catch (\Throwable $e) {
            error_log("CacheManager: Failed to flush cache: " . $e->getMessage());
            return false;
        }
    }
    
    public function scanAndRegister(string $directory): void
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($files as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $this->registerClassFromFile($file->getPathname());
            }
        }
    }
    
    protected function registerClassFromFile(string $filePath): void
    {
        $content = file_get_contents($filePath);
        
        if (!preg_match('/namespace\s+([^;]+);/', $content, $namespaceMatch)) {
            return;
        }
        
        if (!preg_match('/class\s+(\w+)/', $content, $classMatch)) {
            return;
        }
        
        $className = $namespaceMatch[1] . '\\' . $classMatch[1];
        
        if (!class_exists($className)) {
            return;
        }
        
        try {
            $reflector = new ReflectionClass($className);
            
            foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
                $hasCacheAttr = !empty($method->getAttributes(Cacheable::class)) ||
                               !empty($method->getAttributes(CachePut::class)) ||
                               !empty($method->getAttributes(CacheEvict::class));
                
                if ($hasCacheAttr) {
                    $this->handlers[$className][$method->getName()] = true;
                }
            }
        } catch (\Exception $e) {
            error_log("CacheManager: Failed to register {$className}: " . $e->getMessage());
        }
    }
}
