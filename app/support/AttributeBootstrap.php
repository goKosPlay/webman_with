<?php

namespace app\support;

use Webman\Bootstrap;

class AttributeBootstrap implements Bootstrap
{
    protected static bool $initialized = false;
    
    public static function start($worker): void
    {
        if (self::$initialized) {
            return;
        }
        
        self::$initialized = true;
        
        $baseDir = base_path() . '/app';
        
        $container = Container::getInstance();
        
        $serviceDirs = [
            $baseDir . '/service',
            $baseDir . '/repository',
            $baseDir . '/component',
            $baseDir . '/example',
        ];
        
        foreach ($serviceDirs as $dir) {
            if (is_dir($dir)) {
                $container->scanAndRegister($dir);
            }
        }
        
        $eventManager = EventListenerManager::getInstance();
        
        $listenerDirs = [
            $baseDir . '/listener',
            $baseDir . '/service',
        ];
        
        foreach ($listenerDirs as $dir) {
            if (is_dir($dir)) {
                $eventManager->scanAndRegister($dir);
            }
        }
        
        // 定时任务只在第一个 worker 进程中注册，避免多进程重复执行
        if ($worker && $worker->id === 0) {
            $taskManager = ScheduledTaskManager::getInstance();
            
            // 只扫描 task 目录，避免重复注册
            $taskDir = $baseDir . '/task';
            if (is_dir($taskDir)) {
                $taskManager->scanAndRegister($taskDir);
            }
        }
        
        $cacheManager = CacheManager::getInstance();
        
        $cacheDirs = [
            $baseDir . '/service',
            $baseDir . '/repository',
        ];
        
        foreach ($cacheDirs as $dir) {
            if (is_dir($dir)) {
                $cacheManager->scanAndRegister($dir);
            }
        }
        
        $asyncManager = AsyncManager::getInstance();
        
        $asyncDirs = [
            $baseDir . '/service',
            $baseDir . '/task',
        ];
        
        foreach ($asyncDirs as $dir) {
            if (is_dir($dir)) {
                $asyncManager->scanAndRegister($dir);
            }
        }
        
        // 队列系统：扫描并注册任务类
        $queue = Queue::getInstance();
        
        $jobDir = $baseDir . '/job';
        if (is_dir($jobDir)) {
            $queue->scanAndRegister($jobDir);
        }
        
        // 队列工作器：只在 worker-0 中启动
        if ($worker && $worker->id === 0) {
            $queueWorker = QueueWorker::getInstance();
            
            // 启动多个队列的工作器
            $queueWorker->start(['default', 'emails', 'images', 'reports', 'exports']);
        }
    }
}
