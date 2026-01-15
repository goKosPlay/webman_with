# Scheduled 定时任务使用指南

## 概述

`#[Scheduled]` 属性用于在服务类中声明定时任务方法，系统会自动扫描并注册这些任务。

## 基本用法

### 1. 创建任务服务类

在 `app/service` 或 `app/task` 目录下创建服务类，使用 `#[Service]` 标记：

```php
<?php

namespace app\task;

use app\attribute\{Service, Scheduled};

#[Service]
class MyTaskService
{
    #[Scheduled(cron: '0 0 * * *')]
    public function dailyTask()
    {
        // 每天凌晨执行
        echo "Daily task executed\n";
    }
}
```

## 调度方式

### 1. Cron 表达式（推荐）

使用标准的 Cron 表达式来定义任务执行时间：

```php
#[Scheduled(cron: '0 0 * * *')]  // 每天凌晨 00:00
public function dailyBackup() { }

#[Scheduled(cron: '*/5 * * * *')]  // 每 5 分钟
public function checkHealth() { }

#[Scheduled(cron: '0 */6 * * *')]  // 每 6 小时
public function cleanupFiles() { }

#[Scheduled(cron: '0 9 * * 1')]  // 每周一上午 9:00
public function weeklyReport() { }

#[Scheduled(cron: '0 0 1 * *')]  // 每月 1 号凌晨
public function monthlyReport() { }
```

**Cron 表达式格式：**
```
* * * * *
│ │ │ │ │
│ │ │ │ └─ 星期几 (0-7, 0 和 7 都表示周日)
│ │ │ └─── 月份 (1-12)
│ │ └───── 日期 (1-31)
│ └─────── 小时 (0-23)
└───────── 分钟 (0-59)
```

### 2. fixedRate（固定频率）

任务以固定的时间间隔执行，**不管上次任务是否完成**：

```php
#[Scheduled(fixedRate: 60000)]  // 每 60 秒执行一次
public function updateMetrics() { }

#[Scheduled(fixedRate: 5000, initialDelay: 10000)]  // 启动后 10 秒开始，然后每 5 秒执行
public function heartbeat() { }
```

- 单位：**毫秒**
- 适用场景：需要严格按时间间隔执行的任务

### 3. fixedDelay（固定延迟）

任务在**上次执行完成后**等待指定时间再执行：

```php
#[Scheduled(fixedDelay: 5000)]  // 上次完成后等待 5 秒
public function processQueue() { }

#[Scheduled(fixedDelay: 10000, initialDelay: 1000)]  // 启动后 1 秒开始
public function syncData() { }
```

- 单位：**毫秒**
- 适用场景：任务执行时间不固定，需要避免重叠执行

## 参数说明

| 参数 | 类型 | 说明 | 默认值 |
|------|------|------|--------|
| `cron` | string | Cron 表达式 | null |
| `fixedRate` | int | 固定频率（毫秒） | null |
| `fixedDelay` | int | 固定延迟（毫秒） | null |
| `initialDelay` | int | 初始延迟（毫秒） | 0 |
| `timeZone` | string | 时区 | 'UTC' |
| `enabled` | bool | 是否启用 | true |

**注意：** `cron`、`fixedRate`、`fixedDelay` 三者必须指定其中一个。

## 完整示例

```php
<?php

namespace app\task;

use app\attribute\{Service, Scheduled, Autowired};
use app\service\UserService;
use app\service\LogService;

#[Service]
class ScheduledTasks
{
    #[Autowired]
    private UserService $userService;
    
    #[Autowired]
    private LogService $logService;
    
    /**
     * 每天凌晨 2 点备份数据库
     */
    #[Scheduled(cron: '0 2 * * *', timeZone: 'Asia/Shanghai')]
    public function dailyDatabaseBackup()
    {
        $this->logService->info('开始数据库备份');
        
        // 执行备份逻辑
        $result = $this->performBackup();
        
        $this->logService->info('数据库备份完成', ['result' => $result]);
    }
    
    /**
     * 每 5 分钟检查系统健康状态
     */
    #[Scheduled(cron: '*/5 * * * *')]
    public function checkSystemHealth()
    {
        $health = [
            'cpu' => $this->getCpuUsage(),
            'memory' => $this->getMemoryUsage(),
            'disk' => $this->getDiskUsage(),
        ];
        
        if ($health['cpu'] > 80) {
            $this->logService->warning('CPU 使用率过高', $health);
        }
    }
    
    /**
     * 每小时清理过期会话
     */
    #[Scheduled(cron: '0 * * * *')]
    public function cleanExpiredSessions()
    {
        $count = $this->userService->cleanExpiredSessions();
        $this->logService->info("清理了 {$count} 个过期会话");
    }
    
    /**
     * 每 30 秒处理消息队列（固定频率）
     */
    #[Scheduled(fixedRate: 30000)]
    public function processMessageQueue()
    {
        $messages = $this->getQueueMessages();
        
        foreach ($messages as $message) {
            $this->processMessage($message);
        }
    }
    
    /**
     * 处理任务队列（固定延迟，避免重叠）
     */
    #[Scheduled(fixedDelay: 5000, initialDelay: 1000)]
    public function processTaskQueue()
    {
        // 获取待处理任务
        $task = $this->getNextTask();
        
        if ($task) {
            // 处理任务（可能耗时不固定）
            $this->executeTask($task);
        }
    }
    
    /**
     * 每周一上午 9 点生成周报
     */
    #[Scheduled(cron: '0 9 * * 1', timeZone: 'Asia/Shanghai')]
    public function generateWeeklyReport()
    {
        $report = $this->userService->generateWeeklyReport();
        $this->sendReportEmail($report);
    }
    
    /**
     * 禁用的任务示例
     */
    #[Scheduled(cron: '0 0 * * *', enabled: false)]
    public function disabledTask()
    {
        // 这个任务不会被执行
    }
    
    private function performBackup() { /* ... */ }
    private function getCpuUsage() { return 50; }
    private function getMemoryUsage() { return 60; }
    private function getDiskUsage() { return 70; }
    private function getQueueMessages() { return []; }
    private function processMessage($message) { }
    private function getNextTask() { return null; }
    private function executeTask($task) { }
    private function sendReportEmail($report) { }
}
```

## 系统配置

### 1. 确保 AttributeBootstrap 已启用

在 `config/bootstrap.php` 中确认：

```php
return [
    // ...
    app\support\AttributeBootstrap::class,
];
```

### 2. 任务扫描目录

系统会自动扫描以下目录：
- `app/task/` - 专门存放定时任务
- `app/service/` - 服务类中的定时任务

## 依赖注入

定时任务方法支持完整的依赖注入功能：

```php
#[Service]
class TaskService
{
    #[Autowired]
    private UserRepository $userRepo;
    
    #[Autowired]
    private CacheService $cache;
    
    #[Lazy]
    private EmailService $emailService;
    
    #[Value(key: 'app.name')]
    private string $appName;
    
    #[Scheduled(cron: '0 1 * * *')]
    public function cleanupUsers()
    {
        // 可以直接使用注入的依赖
        $inactiveUsers = $this->userRepo->findInactive();
        
        foreach ($inactiveUsers as $user) {
            $this->cache->delete("user:{$user->id}");
        }
    }
}
```

## 常见 Cron 表达式

```php
// 每分钟
'* * * * *'

// 每 5 分钟
'*/5 * * * *'

// 每小时
'0 * * * *'

// 每天凌晨
'0 0 * * *'

// 每天上午 8 点
'0 8 * * *'

// 每天上午 8 点和下午 6 点
'0 8,18 * * *'

// 工作日上午 9 点
'0 9 * * 1-5'

// 每周一上午 9 点
'0 9 * * 1'

// 每月 1 号凌晨
'0 0 1 * *'

// 每季度第一天凌晨（1月、4月、7月、10月）
'0 0 1 1,4,7,10 *'

// 每年 1 月 1 日凌晨
'0 0 1 1 *'
```

## 时区设置

```php
// 使用上海时区
#[Scheduled(cron: '0 9 * * *', timeZone: 'Asia/Shanghai')]
public function task() { }

// 使用东京时区
#[Scheduled(cron: '0 9 * * *', timeZone: 'Asia/Tokyo')]
public function task() { }

// 使用纽约时区
#[Scheduled(cron: '0 9 * * *', timeZone: 'America/New_York')]
public function task() { }
```

## 任务管理

### 查看已注册的任务

```php
$taskManager = \app\support\ScheduledTaskManager::getInstance();
$tasks = $taskManager->getTasks();

foreach ($tasks as $taskKey => $taskInfo) {
    echo "Task: {$taskKey}\n";
    echo "Type: {$taskInfo['type']}\n";
}
```

### 停止特定任务

```php
$taskManager = \app\support\ScheduledTaskManager::getInstance();
$taskManager->stopTask('app\\task\\MyTaskService::dailyTask');
```

### 停止所有任务

```php
$taskManager = \app\support\ScheduledTaskManager::getInstance();
$taskManager->stopAll();
```

## 最佳实践

### 1. 任务应该是幂等的

确保任务可以安全地重复执行：

```php
#[Scheduled(cron: '0 * * * *')]
public function processOrders()
{
    // 只处理未处理的订单
    $orders = Order::where('status', 'pending')->get();
    
    foreach ($orders as $order) {
        if (!$this->isProcessed($order)) {
            $this->process($order);
        }
    }
}
```

### 2. 添加错误处理

```php
#[Scheduled(cron: '0 0 * * *')]
public function dailyBackup()
{
    try {
        $this->performBackup();
        $this->logService->info('备份成功');
    } catch (\Exception $e) {
        $this->logService->error('备份失败', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
}
```

### 3. 避免长时间运行的任务

如果任务可能运行很长时间，考虑使用异步处理：

```php
#[Scheduled(cron: '0 0 * * *')]
public function triggerBigTask()
{
    // 将大任务放入队列异步处理
    Queue::push(new BigTaskJob());
}
```

### 4. 使用 fixedDelay 避免任务重叠

```php
// 不推荐：可能导致任务重叠
#[Scheduled(fixedRate: 5000)]
public function slowTask() {
    sleep(10);  // 任务执行时间超过间隔
}

// 推荐：等待上次完成后再执行
#[Scheduled(fixedDelay: 5000)]
public function slowTask() {
    sleep(10);  // 安全，不会重叠
}
```

### 5. 合理设置时区

```php
// 明确指定时区，避免混淆
#[Scheduled(cron: '0 9 * * *', timeZone: 'Asia/Shanghai')]
public function morningTask() { }
```

## 注意事项

1. **必须使用 `#[Service]` 标记类**，否则任务不会被扫描
2. **任务方法必须是 public**
3. **时间单位**：
   - Cron 表达式：标准 Cron 格式
   - fixedRate/fixedDelay：毫秒
4. **系统会在 Webman 启动时自动扫描并注册任务**
5. **任务执行失败会记录到错误日志**，不会影响其他任务
6. **支持依赖注入**，可以使用 `#[Autowired]`、`#[Inject]`、`#[Lazy]` 等

## 调试

启动 Webman 时会输出任务注册信息：

```bash
php start.php start
```

输出示例：
```
Registered cron task: app\task\MyTaskService::dailyTask with schedule 0 0 * * *
Registered fixed delay task: app\task\MyTaskService::processQueue with delay 5s
```

## 总结

`#[Scheduled]` 提供了三种调度方式：

1. **Cron 表达式** - 适合按固定时间点执行的任务
2. **fixedRate** - 适合需要严格按时间间隔执行的任务
3. **fixedDelay** - 适合执行时间不固定、需要避免重叠的任务

选择合适的调度方式，配合依赖注入和错误处理，可以轻松实现强大的定时任务功能。
