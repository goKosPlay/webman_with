# 队列依赖注入使用示例

## 概述

`Queue` 服务已标记为 `#[Service(singleton: true)]`，可以在控制器、服务类中通过 `#[Autowired]` 注入使用。

## 在控制器中使用

### 方式 1：依赖注入（推荐）

```php
<?php

namespace app\controller;

use app\attribute\{Route, Autowired};
use app\support\Queue;
use app\job\SendEmailJob;
use support\Request;

class UserController
{
    #[Autowired]
    private Queue $queue;
    
    #[Route('POST', '/users/register', 'users.register')]
    public function register(Request $request)
    {
        // 创建用户
        $user = $this->createUser($request->all());
        
        // 使用注入的队列实例分发任务
        $this->queue->push(SendEmailJob::class, [
            'to' => $user['email'],
            'subject' => 'Welcome!',
            'body' => "Welcome {$user['name']}!"
        ]);
        
        return json(['user' => $user]);
    }
}
```

### 方式 2：静态方法（兼容）

```php
use app\support\Queue;

// 仍然可以使用静态方法
$jobId = Queue::getInstance()->push(SendEmailJob::class, $data);
```

## 在服务类中使用

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired};
use app\support\Queue;
use app\job\{SendEmailJob, GenerateReportJob};

#[Service]
class OrderService
{
    #[Autowired]
    private Queue $queue;
    
    #[Autowired]
    private UserService $userService;
    
    public function createOrder(array $data): array
    {
        $order = $this->saveOrder($data);
        
        // 异步发送订单确认邮件
        $this->queue->push(SendEmailJob::class, [
            'to' => $order['email'],
            'subject' => 'Order Confirmation',
            'body' => "Your order #{$order['id']} has been confirmed"
        ], 'emails');
        
        return $order;
    }
    
    public function completeOrder(int $orderId): void
    {
        $order = $this->findOrder($orderId);
        
        // 延迟 24 小时后生成报表
        $this->queue->push(
            GenerateReportJob::class,
            ['order_id' => $orderId],
            'reports',
            86400  // 24小时
        );
    }
}
```

## 完整示例：用户注册流程

```php
<?php

namespace app\controller;

use app\attribute\{Route, Autowired};
use app\support\Queue;
use app\service\UserService;
use app\job\{SendEmailJob, ProcessImageJob};
use support\Request;

class UserController
{
    #[Autowired]
    private Queue $queue;
    
    #[Autowired]
    private UserService $userService;
    
    #[Route('POST', '/users/register', 'users.register')]
    public function register(Request $request)
    {
        // 1. 创建用户
        $user = $this->userService->create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => $request->input('password')
        ]);
        
        // 2. 异步发送欢迎邮件
        $this->queue->push(SendEmailJob::class, [
            'to' => $user['email'],
            'subject' => 'Welcome to Our Platform',
            'body' => "Hi {$user['name']}, welcome!"
        ], 'emails');
        
        // 3. 如果上传了头像，异步处理
        if ($request->file('avatar')) {
            $avatarPath = $this->saveAvatar($request->file('avatar'));
            
            $this->queue->push(ProcessImageJob::class, [
                'path' => $avatarPath,
                'operations' => ['resize', 'compress']
            ], 'images');
        }
        
        return json([
            'message' => 'Registration successful',
            'user' => $user
        ]);
    }
    
    #[Route('POST', '/users/{id}/avatar', 'users.avatar')]
    public function updateAvatar(Request $request, $id)
    {
        $user = $this->userService->find($id);
        $avatarPath = $this->saveAvatar($request->file('avatar'));
        
        // 异步处理头像
        $jobId = $this->queue->push(ProcessImageJob::class, [
            'path' => $avatarPath,
            'user_id' => $id,
            'operations' => ['resize', 'compress', 'watermark']
        ], 'images');
        
        return json([
            'message' => 'Avatar upload successful, processing...',
            'job_id' => $jobId
        ]);
    }
    
    private function saveAvatar($file): string
    {
        // 保存文件逻辑
        return '/uploads/avatars/' . uniqid() . '.jpg';
    }
}
```

## 多个队列实例注入

如果需要为不同队列创建专门的服务：

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired};
use app\support\Queue;

#[Service]
class EmailQueueService
{
    #[Autowired]
    private Queue $queue;
    
    public function sendWelcomeEmail(string $email, string $name): string
    {
        return $this->queue->push(SendEmailJob::class, [
            'to' => $email,
            'subject' => 'Welcome',
            'body' => "Welcome {$name}!"
        ], 'emails');
    }
    
    public function sendPasswordReset(string $email, string $token): string
    {
        return $this->queue->push(SendEmailJob::class, [
            'to' => $email,
            'subject' => 'Password Reset',
            'body' => "Your reset token: {$token}"
        ], 'emails', 0);  // 立即发送
    }
    
    public function scheduleNewsletter(array $recipients, int $delay = 3600): array
    {
        $jobIds = [];
        
        foreach ($recipients as $recipient) {
            $jobIds[] = $this->queue->push(SendEmailJob::class, [
                'to' => $recipient['email'],
                'subject' => 'Newsletter',
                'body' => 'Latest news...'
            ], 'newsletters', $delay);
        }
        
        return $jobIds;
    }
}
```

使用封装的服务：

```php
#[Route('POST', '/users/register')]
public function register(Request $request)
{
    #[Autowired]
    private EmailQueueService $emailQueue;
    
    $user = $this->createUser($request->all());
    
    // 使用专门的邮件队列服务
    $this->emailQueue->sendWelcomeEmail(
        $user['email'],
        $user['name']
    );
    
    return json(['user' => $user]);
}
```

## 在任务类中使用队列

任务也可以分发其他任务：

```php
<?php

namespace app\job;

use app\attribute\{QueueJob, Autowired};
use app\support\Queue;

#[QueueJob(queue: 'batch')]
class ProcessBatchJob
{
    #[Autowired]
    private Queue $queue;
    
    public function handle(array $data): void
    {
        $items = $data['items'];
        
        // 为每个项目创建单独的任务
        foreach ($items as $item) {
            $this->queue->push(ProcessSingleItemJob::class, [
                'item' => $item
            ], 'default', 5);  // 延迟5秒
        }
    }
}
```

## 优势

### 使用依赖注入的好处：

1. **更好的测试性** - 可以轻松 mock Queue 实例
2. **符合 SOLID 原则** - 依赖倒置
3. **统一的代码风格** - 与其他服务注入一致
4. **IDE 支持更好** - 类型提示和自动补全
5. **解耦** - 不依赖静态方法

### 示例：单元测试

```php
class UserControllerTest extends TestCase
{
    public function testRegisterDispatchesEmailJob()
    {
        // Mock Queue
        $mockQueue = $this->createMock(Queue::class);
        $mockQueue->expects($this->once())
            ->method('push')
            ->with(
                SendEmailJob::class,
                $this->arrayHasKey('to')
            );
        
        // 注入 mock
        $controller = new UserController();
        // ... 设置 mock queue
        
        $controller->register($request);
    }
}
```

## 两种方式对比

| 特性 | 依赖注入 | 静态方法 |
|------|---------|---------|
| 代码风格 | ✅ 现代化 | ⚠️ 传统 |
| 可测试性 | ✅ 容易 mock | ❌ 难以测试 |
| IDE 支持 | ✅ 完整 | ✅ 完整 |
| 性能 | ✅ 相同 | ✅ 相同 |
| 灵活性 | ✅ 高 | ⚠️ 中 |
| 学习曲线 | ⚠️ 需要理解 DI | ✅ 简单 |

## 推荐用法

- ✅ **控制器和服务类**：使用 `#[Autowired]` 注入
- ✅ **简单脚本或工具**：使用 `Queue::getInstance()`
- ✅ **任务类内部**：使用 `#[Autowired]` 注入

## 总结

通过 `#[Service(singleton: true)]` 标记，`Queue` 已经成为一个可注入的服务。在控制器和服务类中推荐使用 `#[Autowired]` 注入方式，这样代码更清晰、更易测试、更符合现代 PHP 开发规范。
