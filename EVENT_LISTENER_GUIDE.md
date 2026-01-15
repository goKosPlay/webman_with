# 事件监听器完整实现指南

## 📋 概述

本项目已完整实现基于 PHP 8 Attributes 的事件监听系统，包括：
- ✅ 事件监听器自动注册
- ✅ 优先级支持
- ✅ 多事件监听
- ✅ 完整的示例代码
- ✅ 测试控制器

## 🏗️ 项目结构

```
app/
├── listener/                           # 事件监听器目录
│   ├── UserEventListener.php          # 用户事件监听器
│   ├── OrderEventListener.php         # 订单事件监听器
│   ├── EmailNotificationListener.php  # 邮件通知监听器
│   ├── CacheInvalidationListener.php  # 缓存失效监听器
│   └── AuditLogListener.php           # 审计日志监听器
├── event/                             # 事件类定义
│   ├── UserEvent.php                  # 用户事件
│   └── OrderEvent.php                 # 订单事件
├── service/                           # 服务层（触发事件）
│   ├── UserService.php                # 用户服务
│   └── OrderService.php               # 订单服务
├── controller/                        # 控制器
│   └── EventTestController.php        # 事件测试控制器
└── support/                           # 支持类
    └── EventListenerManager.php       # 事件监听管理器
```

## 🎯 核心组件

### 1. EventListenerManager（事件监听管理器）

**位置**: `app/support/EventListenerManager.php`

**功能**:
- 自动扫描并注册带有 `#[EventListener]` 的方法
- 支持多个事件监听
- 支持优先级设置
- 集成 Webman Event 系统

**自动扫描目录**:
- `app/listener/`
- `app/service/`

### 2. EventListener Attribute

**定义**: `app/attribute/EventListener.php`

**参数**:
- `events`: string|array - 监听的事件名称（支持单个或多个）
- `priority`: int - 优先级（数字越大优先级越高，默认 0）

## 📝 使用方法

### 1. 创建事件监听器

```php
<?php

namespace app\listener;

use app\attribute\{Service, EventListener};
use support\Log;

#[Service]
class UserEventListener
{
    // 监听单个事件
    #[EventListener(events: 'user.created', priority: 10)]
    public function onUserCreated($event)
    {
        Log::info('User created', $event);
        echo "✓ User created: {$event['email']}\n";
    }
    
    // 监听多个事件
    #[EventListener(events: ['user.login', 'user.logout'], priority: 15)]
    public function onUserAuthEvent($event)
    {
        $action = $event['action'] ?? 'unknown';
        Log::info("User {$action}", $event);
    }
    
    // 高优先级监听器（先执行）
    #[EventListener(events: 'user.updated', priority: 100)]
    public function invalidateCache($event)
    {
        // 清除缓存
    }
}
```

### 2. 创建事件类（可选但推荐）

```php
<?php

namespace app\event;

class UserEvent
{
    public static function created(array $userData): array
    {
        return [
            'action' => 'created',
            'user_id' => $userData['id'] ?? null,
            'email' => $userData['email'] ?? null,
            'name' => $userData['name'] ?? null,
            'ip' => request()?->getRealIp() ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function updated(int $userId, array $changes): array
    {
        return [
            'action' => 'updated',
            'user_id' => $userId,
            'changes' => $changes,
            'timestamp' => time(),
        ];
    }
}
```

### 3. 在服务中触发事件

```php
<?php

namespace app\service;

use app\attribute\Service;
use app\event\UserEvent;
use Webman\Event\Event;

#[Service]
class UserService
{
    public function createUser(array $data): array
    {
        // 创建用户逻辑
        $user = [
            'id' => 1001,
            'name' => $data['name'],
            'email' => $data['email'],
        ];
        
        // 触发事件
        Event::emit('user.created', UserEvent::created($user));
        
        return $user;
    }
    
    public function updateUser(int $userId, array $data): bool
    {
        // 更新用户逻辑
        $changes = $data;
        
        // 触发事件
        Event::emit('user.updated', UserEvent::updated($userId, $changes));
        
        return true;
    }
}
```

## 🎨 已实现的监听器示例

### 1. UserEventListener（用户事件监听器）

**监听事件**:
- `user.created` - 用户创建
- `user.updated` - 用户更新
- `user.deleted` - 用户删除
- `user.login` - 用户登录
- `user.logout` - 用户登出

**功能**: 记录用户活动日志

### 2. OrderEventListener（订单事件监听器）

**监听事件**:
- `order.created` - 订单创建
- `order.paid` - 订单支付
- `order.shipped` - 订单发货
- `order.cancelled` - 订单取消

**功能**: 记录订单状态变更

### 3. EmailNotificationListener（邮件通知监听器）

**监听事件**:
- `user.created` - 发送欢迎邮件
- `order.paid` - 发送订单确认邮件
- `order.shipped` - 发送发货通知
- `user.password.reset` - 发送密码重置邮件

**功能**: 异步发送邮件通知

### 4. CacheInvalidationListener（缓存失效监听器）

**监听事件**:
- `user.updated` - 清除用户缓存
- `user.deleted` - 删除用户缓存
- `order.updated` - 清除订单缓存
- `order.cancelled` - 清除订单缓存

**功能**: 自动清除相关缓存

### 5. AuditLogListener（审计日志监听器）

**监听事件**:
- 所有用户相关事件
- 所有订单相关事件
- 所有认证相关事件

**功能**: 记录审计日志（优先级最低，最后执行）

## 🧪 测试事件系统

### 方法 1: 使用测试控制器

已创建 `EventTestController`，提供以下测试接口：

```bash
# 创建用户（触发 user.created 事件）
curl -X POST http://localhost:8787/api/event-test/user/create \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'

# 更新用户（触发 user.updated 事件）
curl -X POST http://localhost:8787/api/event-test/user/1001/update \
  -H "Content-Type: application/json" \
  -d '{"name":"John Smith"}'

# 删除用户（触发 user.deleted 事件）
curl -X POST http://localhost:8787/api/event-test/user/1001/delete

# 用户登录（触发 user.login 事件）
curl -X POST http://localhost:8787/api/event-test/user/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# 创建订单（触发 order.created 事件）
curl -X POST http://localhost:8787/api/event-test/order/create \
  -H "Content-Type: application/json" \
  -d '{"user_id":1001,"email":"customer@example.com","total":99.99}'

# 支付订单（触发 order.paid 事件）
curl -X POST http://localhost:8787/api/event-test/order/10001/pay \
  -H "Content-Type: application/json" \
  -d '{"payment_method":"stripe","email":"customer@example.com"}'

# 发货订单（触发 order.shipped 事件）
curl -X POST http://localhost:8787/api/event-test/order/10001/ship \
  -H "Content-Type: application/json" \
  -d '{"tracking_number":"TRACK123","email":"customer@example.com"}'

# 触发所有事件（测试完整流程）
curl http://localhost:8787/api/event-test/trigger-all
```

### 方法 2: 直接在代码中触发

```php
use Webman\Event\Event;
use app\event\UserEvent;

// 触发用户创建事件
Event::emit('user.created', UserEvent::created([
    'id' => 1001,
    'name' => 'Test User',
    'email' => 'test@example.com',
]));

// 触发订单支付事件
Event::emit('order.paid', [
    'order_id' => 10001,
    'payment_method' => 'stripe',
    'email' => 'customer@example.com',
]);
```

## 📊 事件优先级说明

优先级数字越大，越先执行：

| 优先级 | 用途 | 示例 |
|--------|------|------|
| 100 | 关键操作（缓存清除、数据验证） | CacheInvalidationListener |
| 50 | 重要业务逻辑 | - |
| 20 | 通知发送 | EmailNotificationListener（密码重置） |
| 10 | 一般业务逻辑 | UserEventListener, OrderEventListener |
| 5 | 次要操作 | EmailNotificationListener（一般通知） |
| 1 | 日志记录 | AuditLogListener |
| 0 | 默认优先级 | - |

## 🔍 调试和日志

### 启动时的输出

当应用启动时，会看到类似输出：

```
=== Initializing Attribute System ===

3. Registering event listeners...
Registered event listener: user.created -> app\listener\UserEventListener::onUserCreated (priority: 10)
Registered event listener: user.updated -> app\listener\UserEventListener::onUserUpdated (priority: 5)
Registered event listener: user.deleted -> app\listener\UserEventListener::onUserDeleted (priority: 5)
Registered event listener: user.login -> app\listener\UserEventListener::onUserAuthEvent (priority: 15)
Registered event listener: user.logout -> app\listener\UserEventListener::onUserAuthEvent (priority: 15)
Registered event listener: order.created -> app\listener\OrderEventListener::onOrderCreated (priority: 10)
...
```

### 事件触发时的输出

```
✓ User created: john@example.com
📧 Sending welcome email to: john@example.com
📝 Audit log: user.created
```

### 日志文件

所有事件都会记录到日志文件中：

```
[2026-01-14 22:16:00] INFO: User created event triggered {"user_id":1001,"email":"john@example.com"}
[2026-01-14 22:16:01] INFO: Sending welcome email {"email":"john@example.com"}
[2026-01-14 22:16:02] INFO: Audit log created {"entity_type":"user","action":"created"}
```

## 💡 最佳实践

### 1. 事件命名规范

使用 `实体.动作` 格式：
- `user.created`
- `user.updated`
- `user.deleted`
- `order.paid`
- `order.shipped`

### 2. 事件数据结构

保持一致的数据结构：
```php
[
    'action' => 'created',        // 动作
    'entity_id' => 1001,          // 实体ID
    'timestamp' => time(),        // 时间戳
    'ip' => '127.0.0.1',         // IP地址
    'user_agent' => '...',       // User Agent
    // ... 其他相关数据
]
```

### 3. 监听器职责单一

每个监听器只负责一类功能：
- `UserEventListener` - 用户业务逻辑
- `EmailNotificationListener` - 邮件发送
- `CacheInvalidationListener` - 缓存管理
- `AuditLogListener` - 日志记录

### 4. 异步处理

对于耗时操作（如发送邮件），使用 `#[Async]` 标记：

```php
#[EventListener(events: 'user.created', priority: 5)]
#[Async]
public function sendWelcomeEmail($event)
{
    // 异步发送邮件
}
```

### 5. 错误处理

在监听器中添加异常处理：

```php
#[EventListener(events: 'user.created')]
public function onUserCreated($event)
{
    try {
        // 处理逻辑
    } catch (\Exception $e) {
        Log::error('Event listener failed', [
            'event' => 'user.created',
            'error' => $e->getMessage()
        ]);
    }
}
```

## 🚀 扩展功能

### 添加新的监听器

1. 在 `app/listener/` 创建新文件
2. 添加 `#[Service]` 类属性
3. 添加 `#[EventListener]` 方法属性
4. 重启应用，自动注册

### 添加新的事件

1. 在 `app/event/` 创建事件类
2. 定义静态方法返回事件数据
3. 在服务中使用 `Event::emit()` 触发

### 自定义事件处理

```php
use Webman\Event\Event;

// 注册自定义监听器
Event::on('custom.event', function($data) {
    // 处理逻辑
}, $priority);

// 触发事件
Event::emit('custom.event', ['key' => 'value']);
```

## 📚 相关文档

- [Attributes 使用指南](./ATTRIBUTES_USAGE.md)
- [Attributes 集成说明](./ATTRIBUTES_INTEGRATION.md)
- [Webman Event 文档](https://www.workerman.net/doc/webman/event.html)

## ⚠️ 注意事项

1. **性能**: 监听器会在事件触发时同步执行，避免在监听器中执行耗时操作
2. **顺序**: 监听器按优先级执行，相同优先级的执行顺序不保证
3. **异常**: 监听器中的异常不会影响事件触发者，但会记录日志
4. **依赖**: 需要安装 `webman/event` 包
5. **重启**: 添加新监听器后需要重启应用

## 🎯 总结

事件监听系统已完全实现并可以使用：

✅ 自动扫描和注册监听器  
✅ 支持优先级  
✅ 支持多事件监听  
✅ 完整的示例代码  
✅ 测试接口  
✅ 详细的文档  

开始使用：
1. 创建监听器类并添加 `#[EventListener]` 属性
2. 在服务中使用 `Event::emit()` 触发事件
3. 重启应用，监听器自动生效
