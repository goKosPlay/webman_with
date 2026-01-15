<?php

namespace app\support;

use Workerman\Timer;

class QueueWorker
{
    protected static ?self $instance = null;
    protected array $workers = [];
    protected bool $running = false;
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 启动队列工作器
     */
    public function start(array $queues = ['default'], int $interval = 1): void
    {
        if ($this->running) {
            return;
        }
        
        $this->running = true;
        
        foreach ($queues as $queueName) {
            $this->startWorker($queueName, $interval);
        }
    }
    
    /**
     * 启动单个队列的工作器
     */
    protected function startWorker(string $queue, int $interval): void
    {
        $timerId = Timer::add($interval, function() use ($queue) {
            $this->processQueue($queue);
        });
        
        $this->workers[$queue] = $timerId;
    }
    
    /**
     * 处理队列中的任务
     */
    protected function processQueue(string $queue): void
    {
        $queueManager = Queue::getInstance();
        
        $job = $queueManager->pop($queue);
        
        if ($job) {
            $queueManager->process($job);
        }
    }
    
    /**
     * 停止队列工作器
     */
    public function stop(?string $queue = null): void
    {
        if ($queue) {
            if (isset($this->workers[$queue])) {
                Timer::del($this->workers[$queue]);
                unset($this->workers[$queue]);
            }
        } else {
            foreach ($this->workers as $queueName => $timerId) {
                Timer::del($timerId);
            }
            $this->workers = [];
            $this->running = false;
        }
    }
    
    /**
     * 获取运行状态
     */
    public function isRunning(): bool
    {
        return $this->running;
    }
    
    /**
     * 获取活跃的工作器
     */
    public function getWorkers(): array
    {
        return array_keys($this->workers);
    }
}
