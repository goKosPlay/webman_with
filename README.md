# Webman Project Template

基于 **webman** 的高性能 PHP 应用模板，内置 **属性驱动的依赖注入、事件、队列、异步、缓存、定时任务**，以及 **多 Provider 短信服务**，开箱即用。

---

## ✨ 核心特性

### 🚀 多 Provider 短信服务
- **统一接口**：`SmsProviderInterface`，支持自由文本与模板短信
- **内置 Provider**：`log`（开发日志）、`custom_http`（自建网关）、`aliyun`、`tencentcloud`、`twilio`
- **自动 fallback**：可配置默认 provider + 备用链，或调用时指定多 provider 顺序
- **使用示例**
  ```php
  $this->smsService->sendOtp('08123456789', '123456'); // 默认
  $this->smsService->send('08123456789', 'hello', null, 'aliyun,tencentcloud,twilio'); // 多 fallback
  ```

### 🧩 属性驱动架构
- **依赖注入**：`#[Service]` + `#[Autowired]`，自动扫描注册
- **路由**：`#[RestController]` + `#[GetMapping]` / `#[PostMapping]`
- **事件监听**：`#[EventListener]`，自动绑定
- **缓存**：`#[Cacheable]` / `#[CacheEvict]`，声明式缓存
- **异步**：`#[Async]`，后台任务
- **定时任务**：`#[Scheduled]`，Cron 表达式

### 📦 队列系统
- **任务类**：`#[Job]` 标记，自动注册
- **多队列**：支持 `default`、`emails`、`images`、`reports`、`exports`
- **Worker**：内置多进程消费，支持失败重试

### 📝 日志与监控
- **结构化日志**：`Log::info('event', $context)`
- **日志通道**：可配置多 channel
- **异常追踪**：自动记录堆栈与上下文

---

## 🛠 快速开始

### 1) 环境与安装
```bash
composer install
cp .env.example .env  # 配置数据库等
php webman start
```

### 2) 添加自己的 Service
```php
<?php
namespace app\service;

use app\attribute\dependency\Service;

#[Service]
class MyService
{
    public function hello(): string
    {
        return 'Hello, Webman!';
    }
}
```

### 3) 在 Controller 里注入
```php
<?php
namespace app\front\controller;

use app\attribute\dependency\Autowired;
use app\service\MyService;

#[RestController('/api')]
class DemoController
{
    #[Autowired]
    private MyService $myService;

    #[GetMapping('/hello')]
    public function hello()
    {
        return json(['msg' => $this->myService->hello()]);
    }
}
```

### 4) 发送短信（示例）
```php
// 验证码（OTP）
$this->smsService->sendOtp('08123456789', '123456');

// 指定多 provider 顺序
$this->smsService->send('08123456789', 'hello', null, 'aliyun,tencentcloud,twilio');
```

---

## 📁 目录结构（关键部分）

```
app/
 ├─ attribute/          # PHP 8 属性定义
 ├─ service/            # 业务服务层
 │   ├─ sms/           # SMS Provider 实现
 │   └─ *.php
 ├─ controller/        # 控制器
 ├─ model/             # Eloquent 模型
 ├─ listener/          # 事件监听器
 ├─ job/               # 队列任务
 ├─ task/              # 定时任务
 └─ support/           # 框架扩展（DI、事件、缓存、异步、队列等）

config/
 ├─ sms.php            # SMS Provider 配置
 ├─ database.php       # 数据库配置
 └─ *.php
```

---

## ⚙️ 配置要点

### SMS Service 配置（`config/sms.php`）
```php
'default_provider' => 'log',
'fallback_providers' => ['aliyun', 'tencentcloud', 'twilio'],
'providers' => [
    'aliyun' => [
        'class' => \app\service\sms\AliyunSmsProvider::class,
        'options' => [
            'access_key_id' => '',
            'access_key_secret' => '',
            'sign_name' => '',
            'template_code' => '',
        ],
    ],
    // ... tencentcloud / twilio / custom_http
],
```

---

## 📚 更多文档

- **属性驱动指南**：`ATTRIBUTES_INTEGRATION.md`
- **依赖注入**：`DEPENDENCY_INJECTION_GUIDE.md`
- **事件监听**：`EVENT_LISTENER_GUIDE.md`
- **队列**：`QUEUE_GUIDE.md`
- **定时任务**：`SCHEDULED_TASK_GUIDE.md`
- **异步**：`ASYNC_GUIDE.md`
- **缓存**：`CACHE_GUIDE.md`
- **验证**：`VALIDATION_GUIDE.md`
- **日志**：`PHP8_LOGGING_ATTRIBUTES_GUIDE.md`

---

## 📄 License

MIT License

---

> 本模板基于 **webman**，并扩展了属性驱动的现代化开发体验。欢迎提交 Issue 与 PR。
