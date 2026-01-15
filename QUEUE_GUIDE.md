# 队列系统使用指南

## 概述

基于 PHP 8 Attributes 的队列系统，支持异步任务处理、自动重试、延迟执行等功能。

## 核心组件

### 1. Attributes

#### `#[QueueJob]` - 队列任务类标记

```php
#[QueueJob(
    queue: 'default',      // 队列名称
    maxRetries: 3,         // 最大重试次数
    retryDelay: 60,        // 重试延迟（秒）
    timeout: 300           // 超时时间（秒）
)]
class MyJob
{
    public function handle(array $data): void
    {
        // 任务处理逻辑
    }
}
```

#### `#[Dispatch]` - 方法级别的分发配置（可选）

```php
#[Dispatch(
    queue: 'emails',       // 指定队列
    delay: 60,             // 延迟执行（秒）
    priority: 10           // 优先级
)]
public function sendEmail() { }
```

### 2. 队列管理器 - `Queue`

负责任务的入队、出队和执行。

### 3. 队列工作器 - `QueueWorker`

负责持续处理队列中的任务。

## 创建队列任务

### 步骤 1：创建任务类

在 `app/job/` 目录下创建任务类：

```php
<?php

namespace app\job;

use app\attribute\{QueueJob, Autowired};
use support\Log;

#[QueueJob(queue: 'emails', maxRetries: 3)]
class SendEmailJob
{
    #[Autowired]
    private UserService $userService;
    
    public function handle(array $data): void
    {
        $to = $data['to'];
        $subject = $data['subject'];
        $body = $data['body'];
        
        // 发送邮件逻辑
        mail($to, $subject, $body);
        
        Log::info("Email sent to {$to}");
    }
}
```

### 步骤 2：分发任务到队列

```php
use app\support\Queue;
use app\job\SendEmailJob;

// 方式 1：直接推送
$jobId = Queue::getInstance()->push(SendEmailJob::class, [
    'to' => 'user@example.com',
    'subject' => 'Welcome!',
    'body' => 'Welcome to our platform!'
]);

// 方式 2：指定队列
$jobId = Queue::getInstance()->push(
    SendEmailJob::class,
    ['to' => 'user@example.com', 'subject' => 'Hello'],
    'high-priority'  // 队列名称
);

// 方式 3：延迟执行（60秒后）
$jobId = Queue::getInstance()->push(
    SendEmailJob::class,
    ['to' => 'user@example.com'],
    null,
    60  // 延迟秒数
);
```

## 启动队列工作器

### 在 Bootstrap 中启动

修改 `app/support/AttributeBootstrap.php`：

```php
// 在 worker-0 中启动队列工作器
if ($worker && $worker->id === 0) {
    $queueWorker = QueueWorker::getInstance();
    
    // 启动多个队列的工作器
    $queueWorker->start(['default', 'emails', 'images', 'reports']);
}
```

### 手动启动

```php
use app\support\QueueWorker;

$worker = QueueWorker::getInstance();

// 启动默认队列
$worker->start(['default']);

// 启动多个队列，每秒检查一次
$worker->start(['emails', 'images', 'reports'], 1);
```

## 完整示例

### 示例 1：发送邮件任务

```php
<?php

namespace app\job;

use app\attribute\{QueueJob, Autowired};
use app\service\EmailService;

#[QueueJob(queue: 'emails', maxRetries: 3, retryDelay: 60)]
class SendWelcomeEmailJob
{
    #[Autowired]
    private EmailService $emailService;
    
    public function handle(array $data): void
    {
        $user = $data['user'];
        
        $this->emailService->send(
            $user['email'],
            'Welcome to Our Platform',
            'welcome-email',
            ['name' => $user['name']]
        );
    }
}
```

使用：

```php
// 在控制器中
public function register(Request $request)
{
    $user = $this->userService->create($request->all());
    
    // 异步发送欢迎邮件
    Queue::getInstance()->push(SendWelcomeEmailJob::class, [
        'user' => $user
    ]);
    
    return json(['message' => 'Registration successful']);
}
```

### 示例 2：图片处理任务

```php
<?php

namespace app\job;

use app\attribute\QueueJob;

#[QueueJob(queue: 'images', maxRetries: 2, timeout: 600)]
class ProcessImageJob
{
    public function handle(array $data): void
    {
        $imagePath = $data['path'];
        
        // 生成缩略图
        $this->generateThumbnail($imagePath, 150, 150);
        
        // 压缩图片
        $this->compressImage($imagePath);
        
        // 添加水印
        $this->addWatermark($imagePath);
    }
    
    private function generateThumbnail($path, $width, $height) { }
    private function compressImage($path) { }
    private function addWatermark($path) { }
}
```

### 示例 3：报表生成任务

```php
<?php

namespace app\job;

use app\attribute\{QueueJob, Autowired};
use app\service\ReportService;

#[QueueJob(queue: 'reports', maxRetries: 1, timeout: 1800)]
class GenerateMonthlyReportJob
{
    #[Autowired]
    private ReportService $reportService;
    
    public function handle(array $data): void
    {
        $userId = $data['user_id'];
        $month = $data['month'];
        
        $report = $this->reportService->generateMonthlyReport($userId, $month);
        
        // 保存报表
        $this->reportService->save($report);
        
        // 发送通知
        Queue::getInstance()->push(SendEmailJob::class, [
            'to' => $data['email'],
            'subject' => 'Your Monthly Report is Ready',
            'body' => 'Your report has been generated.'
        ]);
    }
}
```

### 示例 4：批量任务

```php
<?php

namespace app\job;

use app\attribute\QueueJob;
use app\support\Queue;

#[QueueJob(queue: 'batch')]
class ProcessBatchUsersJob
{
    public function handle(array $data): void
    {
        $userIds = $data['user_ids'];
        
        foreach ($userIds as $userId) {
            // 为每个用户创建单独的任务
            Queue::getInstance()->push(ProcessSingleUserJob::class, [
                'user_id' => $userId
            ]);
        }
    }
}
```

## 队列管理

### 查看队列统计

```php
$queue = Queue::getInstance();

// 获取队列统计信息
$stats = $queue->stats('emails');

print_r($stats);
// 输出：
// [
//     'pending' => 5,
//     'processing' => 2,
//     'completed' => 100,
//     'failed' => 3,
//     'total' => 110
// ]
```

### 查看任务详情

```php
$job = $queue->getJob($jobId);

print_r($job);
// 输出：
// [
//     'id' => 'job_123',
//     'class' => 'app\job\SendEmailJob',
//     'data' => [...],
//     'status' => 'completed',
//     'attempts' => 1,
//     ...
// ]
```

### 清空队列

```php
// 清空指定队列
$queue->flush('emails');

// 清空默认队列
$queue->flush();
```

## 在控制器中使用

```php
<?php

namespace app\controller;

use app\attribute\{Route, Autowired};
use app\support\Queue;
use app\job\{SendEmailJob, ProcessImageJob, GenerateReportJob};
use support\Request;

class JobController
{
    #[Route('POST', '/jobs/email', 'jobs.email')]
    public function dispatchEmail(Request $request)
    {
        $jobId = Queue::getInstance()->push(SendEmailJob::class, [
            'to' => $request->input('to'),
            'subject' => $request->input('subject'),
            'body' => $request->input('body')
        ]);
        
        return json([
            'message' => 'Email job dispatched',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('POST', '/jobs/image', 'jobs.image')]
    public function processImage(Request $request)
    {
        $jobId = Queue::getInstance()->push(ProcessImageJob::class, [
            'path' => $request->input('image_path'),
            'operations' => ['resize', 'compress', 'watermark']
        ], 'images');
        
        return json([
            'message' => 'Image processing job dispatched',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('POST', '/jobs/report', 'jobs.report')]
    public function generateReport(Request $request)
    {
        // 延迟 5 分钟后执行
        $jobId = Queue::getInstance()->push(
            GenerateReportJob::class,
            [
                'user_id' => $request->input('user_id'),
                'type' => 'monthly'
            ],
            'reports',
            300  // 5分钟延迟
        );
        
        return json([
            'message' => 'Report generation scheduled',
            'job_id' => $jobId
        ]);
    }
    
    #[Route('GET', '/jobs/{id}', 'jobs.status')]
    public function jobStatus(Request $request, $id)
    {
        $job = Queue::getInstance()->getJob($id);
        
        if (!$job) {
            return json(['error' => 'Job not found'], 404);
        }
        
        return json($job);
    }
    
    #[Route('GET', '/queues/{queue}/stats', 'queues.stats')]
    public function queueStats(Request $request, $queue)
    {
        $stats = Queue::getInstance()->stats($queue);
        
        return json($stats);
    }
}
```

## 任务类最佳实践

### 1. 保持任务简单

```php
// ✅ 好的做法
#[QueueJob]
class SendEmailJob
{
    public function handle(array $data): void
    {
        $this->sendEmail($data);
    }
}

// ❌ 避免复杂逻辑
#[QueueJob]
class ComplexJob
{
    public function handle(array $data): void
    {
        // 太多逻辑，应该拆分成多个任务
        $this->step1();
        $this->step2();
        $this->step3();
    }
}
```

### 2. 使用依赖注入

```php
#[QueueJob]
class ProcessOrderJob
{
    #[Autowired]
    private OrderService $orderService;
    
    #[Autowired]
    private PaymentService $paymentService;
    
    public function handle(array $data): void
    {
        $order = $this->orderService->find($data['order_id']);
        $this->paymentService->process($order);
    }
}
```

### 3. 错误处理

```php
#[QueueJob(maxRetries: 3)]
class RobustJob
{
    public function handle(array $data): void
    {
        try {
            $this->doSomething($data);
        } catch (TemporaryException $e) {
            // 临时错误，抛出异常触发重试
            throw $e;
        } catch (PermanentException $e) {
            // 永久错误，记录日志但不重试
            Log::error('Job failed permanently', [
                'error' => $e->getMessage()
            ]);
        }
    }
}
```

### 4. 幂等性

确保任务可以安全地重试：

```php
#[QueueJob]
class IdempotentJob
{
    public function handle(array $data): void
    {
        $orderId = $data['order_id'];
        
        // 检查是否已处理
        if ($this->isProcessed($orderId)) {
            return;
        }
        
        // 处理订单
        $this->processOrder($orderId);
        
        // 标记为已处理
        $this->markAsProcessed($orderId);
    }
}
```

## 队列配置

### 不同队列用于不同优先级

```php
// 高优先级队列 - 更频繁检查
$worker->start(['critical'], 0.5);  // 每 0.5 秒

// 普通队列
$worker->start(['default', 'emails'], 1);  // 每秒

// 低优先级队列
$worker->start(['reports', 'cleanup'], 5);  // 每 5 秒
```

## 监控和调试

### 添加日志

```php
#[QueueJob]
class LoggedJob
{
    public function handle(array $data): void
    {
        Log::info('Job started', ['data' => $data]);
        
        try {
            $this->process($data);
            Log::info('Job completed successfully');
        } catch (\Exception $e) {
            Log::error('Job failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
```

## 注意事项

1. **任务类必须有 `handle(array $data)` 方法**
2. **使用 `#[QueueJob]` 属性标记任务类**
3. **任务应该是幂等的**（可以安全重试）
4. **避免在任务中执行长时间阻塞操作**
5. **合理设置超时时间和重试次数**
6. **队列工作器应该在独立的 worker 进程中运行**

## 总结

队列系统提供了：

- ✅ **基于 Attributes 的声明式配置**
- ✅ **自动重试机制**
- ✅ **延迟执行**
- ✅ **多队列支持**
- ✅ **依赖注入**
- ✅ **任务状态追踪**
- ✅ **简单易用的 API**

通过队列系统，可以轻松实现异步任务处理，提升应用性能和用户体验。
