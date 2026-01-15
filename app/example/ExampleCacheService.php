<?php

namespace app\example;

use app\attribute\dependency\{Service, Value};

#[Service(singleton: true)]
class ExampleCacheService
{
    #[Value(key: 'cache.default', default: 'redis')]
    private string $driver;
    
    #[Value(key: 'cache.ttl', default: 3600)]
    private int $defaultTtl;
    
    private array $storage = [];
    private array $expiry = [];
    
    /**
     * 获取缓存值
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (!$this->has($key)) {
            return $default;
        }
        
        return $this->storage[$key];
    }
    
    /**
     * 设置缓存值
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->storage[$key] = $value;
        
        $ttl = $ttl ?? $this->defaultTtl;
        if ($ttl > 0) {
            $this->expiry[$key] = time() + $ttl;
        }
        
        return true;
    }
    
    /**
     * 检查缓存是否存在且未过期
     */
    public function has(string $key): bool
    {
        if (!isset($this->storage[$key])) {
            return false;
        }
        
        if (isset($this->expiry[$key]) && time() > $this->expiry[$key]) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    /**
     * 删除缓存
     */
    public function delete(string $key): bool
    {
        unset($this->storage[$key], $this->expiry[$key]);
        return true;
    }
    
    /**
     * 清空所有缓存
     */
    public function flush(): bool
    {
        $this->storage = [];
        $this->expiry = [];
        return true;
    }
    
    /**
     * 批量获取
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }
    
    /**
     * 批量设置
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        return true;
    }
    
    /**
     * 批量删除
     */
    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        return true;
    }
    
    /**
     * 递增
     */
    public function increment(string $key, int $value = 1): int|false
    {
        $current = $this->get($key, 0);
        
        if (!is_numeric($current)) {
            return false;
        }
        
        $new = (int)$current + $value;
        $this->set($key, $new);
        
        return $new;
    }
    
    /**
     * 递减
     */
    public function decrement(string $key, int $value = 1): int|false
    {
        return $this->increment($key, -$value);
    }
    
    /**
     * 记住：如果缓存不存在，则执行回调并缓存结果
     */
    public function remember(string $key, int $ttl, callable $callback): mixed
    {
        if ($this->has($key)) {
            return $this->get($key);
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * 永久记住（使用默认TTL）
     */
    public function rememberForever(string $key, callable $callback): mixed
    {
        return $this->remember($key, 0, $callback);
    }
    
    /**
     * 拉取：获取并删除
     */
    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->delete($key);
        return $value;
    }
    
    /**
     * 添加：仅当不存在时设置
     */
    public function add(string $key, mixed $value, ?int $ttl = null): bool
    {
        if ($this->has($key)) {
            return false;
        }
        
        return $this->set($key, $value, $ttl);
    }
    
    /**
     * 获取驱动类型
     */
    public function getDriver(): string
    {
        return $this->driver;
    }
    
    /**
     * 获取默认TTL
     */
    public function getDefaultTtl(): int
    {
        return $this->defaultTtl;
    }
    
    /**
     * 获取所有缓存键
     */
    public function keys(): array
    {
        $this->cleanExpired();
        return array_keys($this->storage);
    }
    
    /**
     * 获取缓存数量
     */
    public function count(): int
    {
        $this->cleanExpired();
        return count($this->storage);
    }
    
    /**
     * 清理过期缓存
     */
    protected function cleanExpired(): void
    {
        $now = time();
        foreach ($this->expiry as $key => $expireTime) {
            if ($now > $expireTime) {
                $this->delete($key);
            }
        }
    }
}
