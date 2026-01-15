# PHP 8 日志属性使用指南

本指南介绍如何使用 PHP 8 Attributes 来实现声明式日志记录。

## 概述

日志属性系统提供了一种声明式的方式来记录方法执行、性能监控和异常处理，无需在代码中手动编写日志语句。

## 可用的日志属性

### 1. `#[Loggable]` - 类级别日志

标记整个类需要进行日志记录。

```php
#[Loggable(channel: 'business', level: 'info', logParams: true, logResult: false)]
class UserService
{
    public function createUser(array $data): array
    {
        // 自动记录方法开始和结束
        return $this->repository->create($data);
    }
}
```

**参数：**
- `channel` - 日志通道（默认：null）
- `level` - 日志级别（默认：info）
- `logParams` - 是否记录参数（默认：true）
- `logResult` - 是否记录返回结果（默认：false）
- `logException` - 是否记录异常（默认：true）
- `context` - 额外的上下文信息

### 2. `#[Log]` - 方法级别日志

在方法上添加特定的日志记录。

```php
#[Log(message: '开始用户注册', before: true, includeParams: true)]
#[Log(message: '用户注册完成', after: true, includeResult: true)]
public function registerUser(string $email, string $password): array
{
    // 在方法执行前后记录日志
}
```

**参数：**
- `message` - 日志消息
- `level` - 日志级别（默认：info）
- `channel` - 日志通道
- `context` - 额外上下文
- `includeParams` - 是否包含参数（默认：false）
- `includeResult` - 是否包含结果（默认：false）
- `before` - 是否在执行前记录（默认：false）
- `after` - 是否在执行后记录（默认：true）

### 3. `#[LogPerformance]` - 性能监控

监控方法执行性能。

```php
#[LogPerformance(message: '用户注册性能', threshold: 0.5)]
public function registerUser(string $email, string $password): array
{
    // 记录执行时间，如果超过阈值则记录警告
}
```

**参数：**
- `message` - 日志消息
- `channel` - 日志通道
- `threshold` - 性能阈值，单位秒（默认：0.1）
- `logSlowOnly` - 只记录超过阈值的调用（默认：false）
- `includeParams` - 是否包含参数（默认：false）
- `includeResult` - 是否包含结果（默认：false）
- `context` - 额外上下文

### 4. `#[LogException]` - 异常日志

专门记录异常信息。

```php
#[LogException(message: '用户注册失败', includeParams: true, includeTrace: true)]
public function registerUser(string $email, string $password): array
{
    // 发生异常时自动记录详细信息
}
```

**参数：**
- `message` - 日志消息
- `channel` - 日志通道
- `includeTrace` - 是否包含堆栈跟踪（默认：false）
- `includeParams` - 是否包含参数（默认：true）
- `context` - 额外上下文

### 5. `#[LogContext]` - 日志上下文

为日志添加上下文信息。

```php
#[LogContext(key: 'user_email')] // 将参数值添加到日志上下文
public function registerUser(string $email, string $password): array
{
    // email 参数会作为 user_email 添加到日志上下文
}

#[LogContext(['operation' => 'user_registration'])] // 添加静态上下文
public function registerUser(string $email, string $password): array
{
    // operation: user_registration 会添加到日志上下文
}
```

## 使用示例

### 基础使用

```php
<?php

use app\attribute\dependency\Service;
use app\attribute\log\{Loggable, Log, LogPerformance, LogException, LogContext};

#[Service]
#[Loggable(channel: 'business')]
class UserService
{
    #[Log(message: '开始用户注册', before: true)]
    #[LogPerformance(threshold: 1.0)]
    #[LogException(message: '用户注册失败')]
    public function registerUser(
        #[LogContext(key: 'user_email')] string $email,
        string $password
    ): array {
        // 业务逻辑
        return ['id' => 1, 'email' => $email];
    }
}
```

### 组合使用

```php
#[Service]
#[Loggable(channel: 'payment', logParams: true)]
class PaymentService
{
    #[Log(message: '处理支付', before: true, includeParams: true)]
    #[LogPerformance(message: '支付处理性能', threshold: 2.0)]
    #[Log(message: '支付完成', after: true, includeResult: true)]
    public function processPayment(
        #[LogContext(key: 'payment_amount')] float $amount,
        #[LogContext(['currency' => 'USD'])] string $method
    ): array {
        // 复杂的支付逻辑
        sleep(1);
        
        return [
            'success' => true,
            'transaction_id' => uniqid(),
            'amount' => $amount,
            'method' => $method
        ];
    }
}
```

## 测试接口

创建了以下测试接口来演示日志功能：

### 1. 用户注册测试
```bash
GET /log-demo/register?email=user@example.com&password=123456
```

### 2. 数据处理测试
```bash
POST /log-demo/process
Content-Type: application/json

{"data": ["item1", "item2", "item3"]}
```

### 3. 简单任务测试
```bash
GET /log-demo/simple?task=my_task
```

### 4. 复杂业务逻辑测试
```bash
POST /log-demo/complex
Content-Type: application/json

{"type": "payment", "amount": 100, "options": {"priority": "high"}}
```

### 5. 静默方法测试
```bash
GET /log-demo/silent?data=test_data
```

### 6. 异常测试
```bash
GET /log-demo/error
```

## 日志输出示例

### 成功执行的日志
```
[2024-01-15 10:30:00] business.INFO: Executing UserService::registerUser() {"user_email":"user@example.com","params":{"email":"user@example.com","password":"***MASKED***"}}
[2024-01-15 10:30:01] business.INFO: 开始用户注册 {"user_email":"user@example.com"}
[2024-01-15 10:30:01] business.INFO: Performance: UserService::registerUser() {"execution_time_ms":1050.00,"threshold_ms":1000.00}
[2024-01-15 10:30:01] business.INFO: 用户注册完成 {"user_email":"user@example.com","result":{"id":1,"email":"user@example.com"}}
[2024-01-15 10:30:01] business.INFO: Completed UserService::registerUser() {"user_email":"user@example.com","execution_time":"1050.00ms"}
```

### 异常执行的日志
```
[2024-01-15 10:31:00] business.ERROR: 用户注册失败 {"user_email":"test@example.com","exception":"Exception","message":"Email already exists","file":"/path/to/UserService.php","line":25,"params":{"email":"test@example.com","password":"***MASKED***"}}
[2024-01-15 10:31:00] business.ERROR: Exception in UserService::registerUser(): Email already exists {"user_email":"test@example.com","exception":"Exception","file":"/path/to/UserService.php","line":25,"trace":"..."}
```

## 最佳实践

### 1. 合理使用日志级别
- `debug` - 详细的调试信息
- `info` - 一般信息（默认）
- `warning` - 警告信息（如性能问题）
- `error` - 错误信息

### 2. 敏感信息处理
系统会自动屏蔽包含以下关键词的参数：
- password
- token
- secret
- key
- api_key

### 3. 性能监控
- 为关键业务方法设置合理的性能阈值
- 使用 `logSlowOnly` 只关注慢查询
- 在生产环境中适当提高阈值

### 4. 上下文信息
- 使用 `#[LogContext]` 添加业务相关的上下文
- 为关键参数添加有意义的键名
- 避免记录过多无用的上下文信息

## 注意事项

1. **性能影响**：日志拦截会增加少量性能开销，建议在生产环境中合理使用
2. **日志量**：避免在循环或高频调用的方法中使用详细日志
3. **敏感信息**：系统会自动屏蔽敏感参数，但仍需注意不要记录过多敏感信息
4. **循环引用**：系统会自动处理循环引用和大型对象的截断

## 扩展

可以通过修改 `LogInterceptor` 类来扩展功能：
- 添加自定义的日志格式
- 集成外部日志系统
- 添加更多的日志属性
- 自定义敏感信息过滤规则
