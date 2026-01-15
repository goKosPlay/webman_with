<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\support\LogInterceptor;
use support\Log;

#[Service]
class LogDemoService
{
    /**
     * 用户注册 - 演示日志记录
     */
    public function registerUser(string $email, string $password, array $profile = []): array
    {
        $startTime = microtime(true);
        
        // 记录开始日志
        Log::info('开始用户注册', [
            'user_email' => $email,
            'params' => ['email' => $email, 'password' => '***MASKED***', 'profile' => $profile]
        ]);
        
        try {
            // 模拟用户注册逻辑
            sleep(1); // 模拟数据库操作
            
            // 模拟可能的异常
            if ($email === 'test@example.com') {
                throw new \Exception('Email already exists');
            }
            
            $user = [
                'id' => rand(1, 1000),
                'email' => $email,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // 记录成功日志
            $executionTime = microtime(true) - $startTime;
            Log::info('用户注册完成', [
                'user_email' => $email,
                'execution_time_ms' => round($executionTime * 1000, 2),
                'result' => ['id' => $user['id'], 'email' => $user['email']]
            ]);
            
            return $user;
            
        } catch (\Exception $e) {
            // 记录异常日志
            Log::error('用户注册失败', [
                'user_email' => $email,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            
            throw $e;
        }
    }
    
    /**
     * 数据处理 - 性能监控
     */
    public function processData(array $data): array
    {
        $startTime = microtime(true);
        
        // 模拟数据处理
        $result = [];
        
        foreach ($data as $item) {
            // 模拟处理时间
            usleep(rand(10000, 50000));
            $result[] = strtoupper($item);
        }
        
        $executionTime = microtime(true) - $startTime;
        $threshold = 0.1; // 100ms
        
        // 只记录超过阈值的调用
        if ($executionTime > $threshold) {
            Log::warning('数据处理性能警告', [
                'execution_time_ms' => round($executionTime * 1000, 2),
                'threshold_ms' => $threshold * 1000,
                'data_count' => count($data)
            ]);
        }
        
        return $result;
    }
    
    /**
     * 简单的日志记录
     */
    public function simpleTask(string $taskName): bool
    {
        Log::info('执行简单任务', ['task_name' => $taskName]);
        
        // 模拟简单任务
        echo "执行任务: {$taskName}\n";
        return true;
    }
    
    /**
     * 复杂业务逻辑 - 多种日志组合
     */
    public function complexBusinessLogic(string $type, int $amount, array $options = []): array
    {
        $startTime = microtime(true);
        
        Log::info('开始复杂业务处理', [
            'business_type' => $type,
            'amount' => $amount,
            'operation' => 'complex_calculation',
            'options' => $options
        ]);
        
        // 模拟复杂业务逻辑
        sleep(2);
        
        $result = [
            'type' => $type,
            'amount' => $amount,
            'processed_amount' => $amount * 1.1,
            'options' => $options,
            'timestamp' => time()
        ];
        
        $executionTime = microtime(true) - $startTime;
        $threshold = 1.0;
        
        Log::info('复杂业务处理完成', [
            'business_type' => $type,
            'execution_time_ms' => round($executionTime * 1000, 2),
            'threshold_ms' => $threshold * 1000,
            'result' => [
                'type' => $result['type'],
                'amount' => $result['amount'],
                'processed_amount' => $result['processed_amount']
            ]
        ]);
        
        return $result;
    }
    
    /**
     * 无日志的方法
     */
    public function silentMethod(string $data): string
    {
        return "Processed: " . $data;
    }
}
