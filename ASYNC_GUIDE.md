# Async 异步执行指南

## 📖 概述

`#[Async]` 属性允许您将方法标记为异步执行，方法调用会立即返回，实际执行在后台进行。

## ✅ 功能特性

- ✅ 异步方法执行
- ✅ 非阻塞调用
- ✅ 基于 Workerman Timer 实现
- ✅ 自动错误处理和日志记录
- ✅ 支持依赖注入

## 🎯 使用场景

### 适合使用异步的场景

1. **邮件发送** - 不需要等待邮件发送完成
2. **图片/视频处理** - 耗时的媒体处理操作
3. **报表生成** - 复杂的数据统计和报表
4. **日志记录** - 异步写入日志
5. **消息推送** - 发送通知和消息
6. **数据同步** - 后台数据同步任务

### 不适合使用异步的场景

1. **需要返回值** - 异步方法无法返回值给调用者
2. **事务操作** - 需要保证原子性的数据库操作
3. **实时响应** - 用户需要立即看到结果的操作

## 📝 基本用法

### 1. 定义异步方法

```php
<?php

namespace app\service;

use app\attribute\{Service, Async};
use support\Log;

#[Service]
class EmailService
{
    #[Async]
    public function sendWelcomeEmail(string $email, string $name): void
    {
        // 这个方法会异步执行
        sleep(2); // 模拟耗时操作
        
        // 发送邮件
        mail($email, "Welcome!", "Hello {$name}!");
        
        Log::info("Welcome email sent to {$email}");
    }
    
    // 普通同步方法
    public function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}
```

### 2. 调用异步方法

```php
<?php

namespace app\controller;

use app\attribute\{RestController, PostMapping};
use app\service\EmailService;
use support\Request;

#[RestController(prefix: '/api/users')]
class UserController
{
    public function __construct(
        private EmailService $emailService
    ) {}
    
    #[PostMapping(path: '/register')]
    public function register(Request $request)
    {
        $email = $request->input('email');
        $name = $request->input('name');
        
        // 创建用户（同步）
        $user = $this->createUser($email, $name);
        
        // 发送欢迎邮件（异步，立即返回）
        $this->emailService->sendWelcomeEmail($email, $name);
        
        // 立即返回响应，不等待邮件发送完成
        return json([
            'code' => 0,
            'msg' => 'User registered successfully',
            'data' => $user
        ]);
    }
}
```

## 🎨 完整示例

### 示例 1: 图片处理服务

```php
<?php

namespace app\service;

use app\attribute\{Service, Async};
use support\Log;

#[Service]
class ImageService
{
    #[Async]
    public function processUploadedImage(string $imagePath): void
    {
        // 生成缩略图
        $this->generateThumbnail($imagePath);
        
        // 压缩图片
        $this->compressImage($imagePath);
        
        // 添加水印
        $this->addWatermark($imagePath);
        
        Log::info("Image processing completed: {$imagePath}");
    }
    
    private function generateThumbnail(string $path): void
    {
        // 缩略图生成逻辑
        sleep(1);
    }
    
    private function compressImage(string $path): void
    {
        // 图片压缩逻辑
        sleep(1);
    }
    
    private function addWatermark(string $path): void
    {
        // 水印添加逻辑
        sleep(1);
    }
}
```

使用：

```php
#[PostMapping(path: '/upload')]
public function uploadImage(Request $request)
{
    $file = $request->file('image');
    $path = $file->move(public_path('uploads'));
    
    // 异步处理图片
    $this->imageService->processUploadedImage($path);
    
    // 立即返回，不等待处理完成
    return json([
        'code' => 0,
        'msg' => 'Image uploaded, processing in background',
        'path' => $path
    ]);
}
```

### 示例 2: 报表生成

```php
<?php

namespace app\service;

use app\attribute\{Service, Async, Autowired};
use app\repository\OrderRepository;
use support\Log;

#[Service]
class ReportService
{
    #[Autowired]
    private OrderRepository $orderRepository;
    
    #[Async]
    public function generateMonthlyReport(int $year, int $month): void
    {
        Log::info("Starting monthly report generation for {$year}-{$month}");
        
        // 获取数据
        $orders = $this->orderRepository->getByMonth($year, $month);
        
        // 计算统计数据
        $stats = $this->calculateStats($orders);
        
        // 生成 PDF
        $pdfPath = $this->generatePDF($stats, $year, $month);
        
        // 发送邮件通知
        $this->sendReportEmail($pdfPath);
        
        Log::info("Monthly report generated: {$pdfPath}");
    }
    
    private function calculateStats(array $orders): array
    {
        // 复杂的统计计算
        sleep(3);
        return [
            'total_orders' => count($orders),
            'total_revenue' => array_sum(array_column($orders, 'amount')),
        ];
    }
    
    private function generatePDF(array $stats, int $year, int $month): string
    {
        // PDF 生成逻辑
        sleep(2);
        return "/reports/{$year}-{$month}.pdf";
    }
    
    private function sendReportEmail(string $pdfPath): void
    {
        // 发送邮件
        sleep(1);
    }
}
```

### 示例 3: 数据同步

```php
<?php

namespace app\service;

use app\attribute\{Service, Async, Scheduled};
use support\Log;

#[Service]
class SyncService
{
    #[Async]
    public function syncUserData(int $userId): void
    {
        // 从第三方 API 同步用户数据
        $data = $this->fetchFromExternalAPI($userId);
        
        // 更新本地数据库
        $this->updateLocalDatabase($userId, $data);
        
        Log::info("User data synced for user {$userId}");
    }
    
    #[Scheduled(cron: '0 */6 * * *')]
    #[Async]
    public function syncAllUsers(): void
    {
        // 每6小时同步所有用户
        $userIds = $this->getAllUserIds();
        
        foreach ($userIds as $userId) {
            $this->syncUserData($userId);
        }
        
        Log::info("All users synced");
    }
    
    private function fetchFromExternalAPI(int $userId): array
    {
        // API 调用
        sleep(2);
        return ['name' => 'User ' . $userId];
    }
    
    private function updateLocalDatabase(int $userId, array $data): void
    {
        // 数据库更新
        sleep(1);
    }
    
    private function getAllUserIds(): array
    {
        return [1, 2, 3, 4, 5];
    }
}
```

## ⚠️ 重要注意事项

### 1. 异步方法不能返回值

```php
// ❌ 错误：异步方法返回值会丢失
#[Async]
public function calculateTotal(): int
{
    return 100; // 调用者无法获取这个返回值
}

// ✅ 正确：使用回调或事件
#[Async]
public function calculateTotal(): void
{
    $total = 100;
    Event::emit('total.calculated', ['total' => $total]);
}
```

### 2. 异步方法中的异常处理

```php
#[Async]
public function riskyOperation(): void
{
    try {
        // 可能抛出异常的代码
        $this->doSomethingRisky();
    } catch (\Exception $e) {
        // 必须在方法内部处理异常
        Log::error("Async operation failed: " . $e->getMessage());
    }
}
```

### 3. 数据库事务

```php
// ❌ 错误：异步方法中的事务可能不会生效
#[Async]
public function updateWithTransaction(): void
{
    Db::beginTransaction();
    // ... 数据库操作
    Db::commit();
}

// ✅ 正确：在同步方法中完成事务
public function updateData(): void
{
    Db::beginTransaction();
    try {
        // 同步执行事务操作
        $this->doUpdate();
        Db::commit();
        
        // 事务完成后，异步执行其他操作
        $this->sendNotification();
    } catch (\Exception $e) {
        Db::rollback();
        throw $e;
    }
}

#[Async]
private function sendNotification(): void
{
    // 异步发送通知
}
```

## 🔍 调试异步方法

### 查看日志

```php
#[Async]
public function debugAsyncMethod(): void
{
    Log::info("Async method started");
    
    // 执行操作
    sleep(2);
    
    Log::info("Async method completed");
}
```

### 检查异步方法是否注册

```php
$asyncManager = AsyncManager::getInstance();
$methods = $asyncManager->getAsyncMethods();

foreach ($methods as $key => $info) {
    echo "{$key}\n";
}
```

## 💡 最佳实践

### 1. 合理使用异步

```php
#[Service]
class OrderService
{
    // 同步：需要立即返回结果
    public function createOrder(array $data): array
    {
        $order = $this->saveOrder($data);
        
        // 异步：发送确认邮件
        $this->sendOrderConfirmation($order);
        
        return $order;
    }
    
    #[Async]
    private function sendOrderConfirmation(array $order): void
    {
        // 邮件发送不影响订单创建的响应
        mail($order['email'], 'Order Confirmation', '...');
    }
}
```

### 2. 组合使用 Async 和 Scheduled

```php
#[Service]
class CleanupService
{
    #[Scheduled(cron: '0 2 * * *')]
    #[Async]
    public function dailyCleanup(): void
    {
        // 每天凌晨2点异步执行清理
        $this->cleanTempFiles();
        $this->cleanExpiredSessions();
        $this->cleanOldLogs();
    }
}
```

### 3. 错误处理和日志

```php
#[Async]
public function processData(array $data): void
{
    try {
        Log::info("Processing started", ['data' => $data]);
        
        $result = $this->doProcess($data);
        
        Log::info("Processing completed", ['result' => $result]);
    } catch (\Exception $e) {
        Log::error("Processing failed", [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
```

## 🎯 性能优化

### 批量异步处理

```php
#[Service]
class BatchService
{
    public function processBatch(array $items): void
    {
        foreach ($items as $item) {
            $this->processItem($item);
        }
    }
    
    #[Async]
    private function processItem(array $item): void
    {
        // 每个项目异步处理
        sleep(1);
        Log::info("Item processed: " . $item['id']);
    }
}
```

## 📚 相关文档

- [Attributes 使用指南](./ATTRIBUTES_USAGE.md)
- [事件监听器指南](./EVENT_LISTENER_GUIDE.md)
- [定时任务指南](./ATTRIBUTES_INTEGRATION.md#scheduled-tasks)

## 🎉 总结

`#[Async]` 属性让您可以轻松实现异步操作，提升应用响应速度：

- ✅ 简单易用，只需添加属性
- ✅ 自动处理异步执行
- ✅ 支持依赖注入
- ✅ 完善的错误处理

记住：异步方法适合不需要立即返回结果的耗时操作！
