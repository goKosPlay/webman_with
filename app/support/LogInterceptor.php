<?php

namespace app\support;

use app\attribute\log\{Loggable, Log, LogPerformance, LogException, LogContext};
use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;
use support\Log as BaseLog;

class LogInterceptor
{
    protected static ?self $instance = null;
    protected array $context = [];
    
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * 拦截方法调用并处理日志属性
     */
    public function intercept(object $instance, string $method, array $params = [])
    {
        $reflection = new ReflectionClass($instance);
        $methodReflection = $reflection->getMethod($method);
        
        // 收集日志上下文
        $context = $this->collectContext($methodReflection, $params);
        
        // 处理 Loggable 属性
        $loggableAttrs = array_merge(
            $reflection->getAttributes(Loggable::class),
            $methodReflection->getAttributes(Loggable::class)
        );
        
        if (!empty($loggableAttrs)) {
            $loggable = $loggableAttrs[0]->newInstance();
            return $this->handleLoggable($instance, $methodReflection, $params, $loggable, $context);
        }
        
        // 处理其他日志属性
        return $this->handleMethodLogging($instance, $methodReflection, $params, $context);
    }
    
    /**
     * 处理 Loggable 属性
     */
    protected function handleLoggable(object $instance, ReflectionMethod $method, array $params, Loggable $loggable, array $context): mixed
    {
        $startTime = microtime(true);
        $methodSignature = $this->getMethodSignature($method);
        
        // 记录开始日志
        if ($loggable->logParams) {
            $this->log($loggable->level, "Executing {$methodSignature}", array_merge($context, [
                'params' => $this->sanitizeParams($params)
            ]), $loggable->channel);
        } else {
            $this->log($loggable->level, "Executing {$methodSignature}", $context, $loggable->channel);
        }
        
        try {
            $result = $method->invokeArgs($instance, $params);
            
            // 记录成功日志
            $executionTime = microtime(true) - $startTime;
            $logData = array_merge($context, [
                'execution_time' => round($executionTime * 1000, 2) . 'ms'
            ]);
            
            if ($loggable->logResult) {
                $logData['result'] = $this->sanitizeResult($result);
            }
            
            $this->log($loggable->level, "Completed {$methodSignature}", $logData, $loggable->channel);
            
            return $result;
            
        } catch (\Throwable $e) {
            // 记录异常日志
            if ($loggable->logException) {
                $this->log('error', "Exception in {$methodSignature}: " . $e->getMessage(), array_merge($context, [
                    'exception' => get_class($e),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]), $loggable->channel);
            }
            
            throw $e;
        }
    }
    
    /**
     * 处理方法级别的日志属性
     */
    protected function handleMethodLogging(object $instance, ReflectionMethod $method, array $params, array $context): mixed
    {
        $startTime = microtime(true);
        $methodSignature = $this->getMethodSignature($method);
        
        // 处理 Log 属性
        $logAttrs = $method->getAttributes(Log::class);
        foreach ($logAttrs as $attr) {
            $log = $attr->newInstance();
            if ($log->before) {
                $logData = $context;
                if ($log->includeParams) {
                    $logData['params'] = $this->sanitizeParams($params);
                }
                $this->log($log->level, $log->message ?: "Before {$methodSignature}", $logData, $log->channel);
            }
        }
        
        // 处理 LogPerformance 属性
        $perfAttrs = $method->getAttributes(LogPerformance::class);
        $performance = !empty($perfAttrs) ? $perfAttrs[0]->newInstance() : null;
        
        try {
            $result = $method->invokeArgs($instance, $params);
            $executionTime = microtime(true) - $startTime;
            
            // 处理 Log 属性（after）
            foreach ($logAttrs as $attr) {
                $log = $attr->newInstance();
                if ($log->after) {
                    $logData = $context;
                    if ($log->includeParams) {
                        $logData['params'] = $this->sanitizeParams($params);
                    }
                    if ($log->includeResult) {
                        $logData['result'] = $this->sanitizeResult($result);
                    }
                    $this->log($log->level, $log->message ?: "After {$methodSignature}", $logData, $log->channel);
                }
            }
            
            // 处理 LogPerformance 属性
            if ($performance) {
                $this->handlePerformance($methodSignature, $executionTime, $params, $result, $performance, $context);
            }
            
            return $result;
            
        } catch (\Throwable $e) {
            // 处理 LogException 属性
            $exceptionAttrs = $method->getAttributes(LogException::class);
            foreach ($exceptionAttrs as $attr) {
                $exception = $attr->newInstance();
                $exceptionData = array_merge($context, [
                    'exception' => get_class($e),
                    'message' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ]);
                
                if ($exception->includeTrace) {
                    $exceptionData['trace'] = $e->getTraceAsString();
                }
                
                if ($exception->includeParams) {
                    $exceptionData['params'] = $this->sanitizeParams($params);
                }
                
                $this->log('error', $exception->message ?: "Exception in {$methodSignature}", $exceptionData, $exception->channel);
            }
            
            throw $e;
        }
    }
    
    /**
     * 处理性能日志
     */
    protected function handlePerformance(string $methodSignature, float $executionTime, array $params, mixed $result, LogPerformance $performance, array $context): void
    {
        $executionMs = $executionTime * 1000;
        
        // 如果只记录慢查询且未超过阈值，则跳过
        if ($performance->logSlowOnly && $executionTime < $performance->threshold) {
            return;
        }
        
        $perfData = array_merge($context, [
            'execution_time_ms' => round($executionMs, 2),
            'threshold_ms' => $performance->threshold * 1000
        ]);
        
        if ($performance->includeParams) {
            $perfData['params'] = $this->sanitizeParams($params);
        }
        
        if ($performance->includeResult) {
            $perfData['result'] = $this->sanitizeResult($result);
        }
        
        $message = $performance->message ?: "Performance: {$methodSignature}";
        $level = $executionTime > $performance->threshold ? 'warning' : 'info';
        
        $this->log($level, $message, $perfData, $performance->channel);
    }
    
    /**
     * 收集日志上下文
     */
    protected function collectContext(ReflectionMethod $method, array $params): array
    {
        $context = [];
        
        // 从方法参数收集 LogContext
        foreach ($method->getParameters() as $param) {
            $contextAttrs = $param->getAttributes(LogContext::class);
            if (!empty($contextAttrs)) {
                $logContext = $contextAttrs[0]->newInstance();
                $paramIndex = $param->getPosition();
                $paramValue = $params[$paramIndex] ?? null;
                
                if ($logContext->key) {
                    $context[$logContext->key] = $paramValue;
                } else {
                    $context[$param->getName()] = $paramValue;
                }
                
                $context = array_merge($context, $logContext->context);
            }
        }
        
        // 从方法收集 LogContext
        $methodContextAttrs = $method->getAttributes(LogContext::class);
        foreach ($methodContextAttrs as $attr) {
            $logContext = $attr->newInstance();
            $context = array_merge($context, $logContext->context);
        }
        
        return $context;
    }
    
    /**
     * 获取方法签名
     */
    protected function getMethodSignature(ReflectionMethod $method): string
    {
        $className = $method->getDeclaringClass()->getShortName();
        $methodName = $method->getName();
        return "{$className}::{$methodName}()";
    }
    
    /**
     * 清理参数（移除敏感信息）
     */
    protected function sanitizeParams(array $params): array
    {
        $sensitiveKeys = ['password', 'token', 'secret', 'key', 'api_key'];
        
        return array_map(function($value) use ($sensitiveKeys) {
            if (is_array($value)) {
                return $this->sanitizeParams($value);
            }
            
            if (is_string($value)) {
                foreach ($sensitiveKeys as $key) {
                    if (str_contains(strtolower($value), $key)) {
                        return '***MASKED***';
                    }
                }
            }
            
            return $value;
        }, $params);
    }
    
    /**
     * 清理结果（限制大小）
     */
    protected function sanitizeResult(mixed $result): mixed
    {
        if (is_string($result) && strlen($result) > 1000) {
            return substr($result, 0, 1000) . '...[TRUNCATED]';
        }
        
        if (is_array($result) && count($result) > 100) {
            return array_slice($result, 0, 100) + ['...' => '[TRUNCATED]'];
        }
        
        return $result;
    }
    
    /**
     * 记录日志
     */
    protected function log(string $level, string $message, array $context = [], ?string $channel = null): void
    {
        $logger = $channel ? BaseLog::channel($channel) : BaseLog::class;
        $logger::{$level}($message, $context);
    }
    
    /**
     * 添加全局上下文
     */
    public function addContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }
    
    /**
     * 清除上下文
     */
    public function clearContext(): void
    {
        $this->context = [];
    }
}
