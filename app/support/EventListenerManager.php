<?php

declare(strict_types=1);

namespace app\support;

use app\attribute\event\EventListener;
use ReflectionClass;
use ReflectionMethod;
use Webman\Event\Event;

class EventListenerManager
{
    protected static ?self $instance = null;
    protected array $listeners = [];
    protected AttributeCache $attributeCache;
    
    private function __construct()
    {
        $this->attributeCache = AttributeCache::getInstance();
    }
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
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
            $this->registerClass($reflector);
        } catch (\Exception $e) {
            error_log("EventListenerManager: Failed to register {$className}: " . $e->getMessage());
        }
    }
    
    protected function registerClass(ReflectionClass $reflector): void
    {
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $this->attributeCache->getMethodAttributes($method, EventListener::class);
            
            foreach ($attributes as $attribute) {
                try {
                    $listener = $this->attributeCache->getAttributeInstance($attribute);
                    $this->registerListener($reflector, $method, $listener);
                } catch (\Exception $e) {
                    error_log("EventListenerManager: Failed to register listener {$reflector->getName()}::{$method->getName()}: " . $e->getMessage());
                }
            }
        }
    }
    
    protected function registerListener(ReflectionClass $class, ReflectionMethod $method, EventListener $listener): void
    {
        $className = $class->getName();
        $methodName = $method->getName();
        
        $callback = function($event) use ($className, $methodName) {
            try {
                $instance = Container::getInstance()->make($className);
                $instance->$methodName($event);
            } catch (\Exception $e) {
                error_log("EventListener: Error executing {$className}::{$methodName}: " . $e->getMessage());
            }
        };
        
        foreach ($listener->events as $eventName) {
            Event::on($eventName, $callback, $listener->priority);
            
            if (!isset($this->listeners[$eventName])) {
                $this->listeners[$eventName] = [];
            }
            
            $this->listeners[$eventName][] = [
                'class' => $className,
                'method' => $methodName,
                'priority' => $listener->priority,
            ];
        }
    }
    
    public function getListeners(?string $eventName = null): array
    {
        if ($eventName === null) {
            return $this->listeners;
        }
        
        return $this->listeners[$eventName] ?? [];
    }
}
