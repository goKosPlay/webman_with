<?php

declare(strict_types=1);

namespace app\support;

use app\attribute\queue\QueueJob;
use app\attribute\event\Dispatch;
use app\attribute\dependency\Service;
use ReflectionClass;
use ReflectionMethod;

#[Service(singleton: true)]
class Queue
{
    protected static ?self $instance = null;
    protected array $queues = [];
    protected array $jobs = [];
    protected array $jobClasses = [];
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
    
    /**
     * 推送任务到队列
     */
    public function push(string $jobClass, array $data = [], ?string $queue = null, ?int $delay = null): string
    {
        $jobId = $this->generateJobId();
        
        $job = [
            'id' => $jobId,
            'class' => $jobClass,
            'data' => $data,
            'queue' => $queue ?? 'default',
            'delay' => $delay,
            'attempts' => 0,
            'max_retries' => 3,
            'created_at' => time(),
            'available_at' => $delay ? time() + $delay : time(),
            'status' => 'pending',
        ];
        
        // 获取任务类的配置
        if (class_exists($jobClass)) {
            try {
                $reflection = new ReflectionClass($jobClass);
                $attributes = $this->attributeCache->getClassAttributes($reflection, QueueJob::class);
                
                if (!empty($attributes)) {
                    $queueJobAttr = $this->attributeCache->getAttributeInstance($attributes[0]);
                    $job['queue'] = $queue ?? $queueJobAttr->queue;
                    $job['max_retries'] = $queueJobAttr->maxRetries;
                    $job['timeout'] = $queueJobAttr->timeout;
                }
            } catch (\Exception $e) {
                error_log("Queue: Failed to get job attributes: " . $e->getMessage());
            }
        }
        
        $queueName = $job['queue'];
        if (!isset($this->queues[$queueName])) {
            $this->queues[$queueName] = [];
        }
        
        $this->queues[$queueName][] = $job;
        $this->jobs[$jobId] = $job;
        
        return $jobId;
    }
    
    /**
     * 从队列中获取下一个任务
     */
    public function pop(string $queue = 'default'): ?array
    {
        if (!isset($this->queues[$queue]) || empty($this->queues[$queue])) {
            return null;
        }
        
        $now = time();
        
        foreach ($this->queues[$queue] as $index => $job) {
            if ($job['status'] === 'pending' && $job['available_at'] <= $now) {
                $this->queues[$queue][$index]['status'] = 'processing';
                $this->jobs[$job['id']]['status'] = 'processing';
                
                return $this->queues[$queue][$index];
            }
        }
        
        return null;
    }
    
    /**
     * 执行任务
     */
    public function process(array $job): bool
    {
        try {
            $jobClass = $job['class'];
            
            if (!class_exists($jobClass)) {
                throw new \Exception("Job class {$jobClass} not found");
            }
            
            $container = Container::getInstance();
            $instance = $container->make($jobClass);
            
            if (!method_exists($instance, 'handle')) {
                throw new \Exception("Job class {$jobClass} must have a handle() method");
            }
            
            $instance->handle($job['data']);
            
            $this->markAsCompleted($job['id']);
            
            return true;
        } catch (\Exception $e) {
            error_log("Queue: Job {$job['id']} failed: " . $e->getMessage());
            
            $this->markAsFailed($job['id'], $e->getMessage());
            
            // 重试逻辑
            if ($job['attempts'] < $job['max_retries']) {
                $this->retry($job['id']);
            }
            
            return false;
        }
    }
    
    /**
     * 标记任务为已完成
     */
    protected function markAsCompleted(string $jobId): void
    {
        if (isset($this->jobs[$jobId])) {
            $this->jobs[$jobId]['status'] = 'completed';
            $this->jobs[$jobId]['completed_at'] = time();
        }
    }
    
    /**
     * 标记任务为失败
     */
    protected function markAsFailed(string $jobId, string $error): void
    {
        if (isset($this->jobs[$jobId])) {
            $this->jobs[$jobId]['status'] = 'failed';
            $this->jobs[$jobId]['error'] = $error;
            $this->jobs[$jobId]['failed_at'] = time();
        }
    }
    
    /**
     * 重试任务
     */
    protected function retry(string $jobId): void
    {
        if (!isset($this->jobs[$jobId])) {
            return;
        }
        
        $job = &$this->jobs[$jobId];
        $job['attempts']++;
        $job['status'] = 'pending';
        $job['available_at'] = time() + ($job['retry_delay'] ?? 60);
    }
    
    /**
     * 获取队列统计信息
     */
    public function stats(string $queue = 'default'): array
    {
        $stats = [
            'pending' => 0,
            'processing' => 0,
            'completed' => 0,
            'failed' => 0,
            'total' => 0,
        ];
        
        if (!isset($this->queues[$queue])) {
            return $stats;
        }
        
        foreach ($this->queues[$queue] as $job) {
            $stats['total']++;
            $stats[$job['status']] = ($stats[$job['status']] ?? 0) + 1;
        }
        
        return $stats;
    }
    
    /**
     * 获取任务详情
     */
    public function getJob(string $jobId): ?array
    {
        return $this->jobs[$jobId] ?? null;
    }
    
    /**
     * 清空队列
     */
    public function flush(string $queue = 'default'): void
    {
        if (isset($this->queues[$queue])) {
            $this->queues[$queue] = [];
        }
    }
    
    /**
     * 生成任务ID
     */
    protected function generateJobId(): string
    {
        return uniqid('job_', true);
    }
    
    /**
     * 注册任务类
     */
    public function registerJobClass(string $className): void
    {
        try {
            $reflection = new ReflectionClass($className);
            $attributes = $this->attributeCache->getClassAttributes($reflection, QueueJob::class);
            
            if (!empty($attributes)) {
                $this->jobClasses[$className] = $this->attributeCache->getAttributeInstance($attributes[0]);
            }
        } catch (\Exception $e) {
            error_log("Queue: Failed to register job class {$className}: " . $e->getMessage());
        }
    }
    
    /**
     * 扫描并注册任务类
     */
    public function scanAndRegister(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $className = $this->getClassNameFromFile($file->getPathname());
                
                if ($className && class_exists($className)) {
                    $this->registerJobClass($className);
                }
            }
        }
    }
    
    /**
     * 从文件获取类名
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
}
