<?php

namespace app\support;

use app\attribute\schedule\Scheduled;
use ReflectionClass;
use ReflectionMethod;
use Workerman\Crontab\Crontab;
use Workerman\Timer;

class ScheduledTaskManager
{
    protected static ?self $instance = null;
    protected array $tasks = [];
    protected array $timers = [];
    protected array $scannedFiles = [];
    
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
        // 检查文件是否已经扫描过
        $realPath = realpath($filePath);
        if (isset($this->scannedFiles[$realPath])) {
            return;
        }
        $this->scannedFiles[$realPath] = true;
        
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
            error_log("ScheduledTaskManager: Failed to register {$className}: " . $e->getMessage());
        }
    }
    
    protected function registerClass(ReflectionClass $reflector): void
    {
        foreach ($reflector->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $attributes = $method->getAttributes(Scheduled::class);
            
            foreach ($attributes as $attribute) {
                try {
                    $scheduled = $attribute->newInstance();
                    
                    if (!$scheduled->enabled) {
                        continue;
                    }
                    
                    $this->registerTask($reflector, $method, $scheduled);
                } catch (\Exception $e) {
                    error_log("ScheduledTaskManager: Failed to register task {$reflector->getName()}::{$method->getName()}: " . $e->getMessage());
                }
            }
        }
    }
    
    protected function registerTask(ReflectionClass $class, ReflectionMethod $method, Scheduled $scheduled): void
    {
        $className = $class->getName();
        $methodName = $method->getName();
        $taskKey = "{$className}::{$methodName}";
        
        // 检查是否已注册，避免重复
        if (isset($this->tasks[$taskKey])) {
            return;
        }
        
        $callback = function() use ($className, $methodName) {
            try {
                $instance = Container::getInstance()->make($className);
                $instance->$methodName();
            } catch (\Exception $e) {
                error_log("ScheduledTask: Error executing {$className}::{$methodName}: " . $e->getMessage());
            }
        };
        
        if ($scheduled->cron) {
            $crontab = new Crontab($scheduled->cron, $callback);
            $this->tasks[$taskKey] = [
                'type' => 'cron',
                'crontab' => $crontab,
                'schedule' => $scheduled->cron,
            ];
        } elseif ($scheduled->fixedRate) {
            $interval = $scheduled->fixedRate / 1000;
            
            if ($scheduled->initialDelay) {
                Timer::add($scheduled->initialDelay / 1000, function() use ($callback, $interval, $taskKey) {
                    $callback();
                    
                    $timerId = Timer::add($interval, $callback);
                    $this->timers[$taskKey] = $timerId;
                }, [], false);
            } else {
                $timerId = Timer::add($interval, $callback);
                $this->timers[$taskKey] = $timerId;
            }
            
            $this->tasks[$taskKey] = [
                'type' => 'fixedRate',
                'interval' => $interval,
            ];
        } elseif ($scheduled->fixedDelay) {
            $delay = $scheduled->fixedDelay / 1000;
            
            $delayedCallback = function() use ($callback, $delay, &$delayedCallback, $taskKey) {
                $callback();
                
                Timer::add($delay, $delayedCallback, [], false);
            };
            
            if ($scheduled->initialDelay) {
                Timer::add($scheduled->initialDelay / 1000, $delayedCallback, [], false);
            } else {
                Timer::add($delay, $delayedCallback, [], false);
            }
            
            $this->tasks[$taskKey] = [
                'type' => 'fixedDelay',
                'delay' => $delay,
            ];
        }
    }
    
    public function getTasks(): array
    {
        return $this->tasks;
    }
    
    public function stopTask(string $taskKey): bool
    {
        if (isset($this->timers[$taskKey])) {
            Timer::del($this->timers[$taskKey]);
            unset($this->timers[$taskKey]);
            unset($this->tasks[$taskKey]);
            return true;
        }
        
        if (isset($this->tasks[$taskKey])) {
            unset($this->tasks[$taskKey]);
            return true;
        }
        
        return false;
    }
    
    public function stopAll(): void
    {
        foreach ($this->timers as $timerId) {
            Timer::del($timerId);
        }
        
        $this->timers = [];
        $this->tasks = [];
    }
}
