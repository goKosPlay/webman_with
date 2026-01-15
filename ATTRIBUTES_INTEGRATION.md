# Attributes 集成说明

## ✅ 已完成的集成

所有 PHP 8 Attributes 已经完全集成到 `app/support` 目录中，并通过 `AttributeBootstrap` 自动加载。

## 📁 Support 目录结构

```
app/support/
├── AttributeBootstrap.php      # 主引导文件，整合所有处理器
├── AttributeRouteLoader.php    # 路由映射处理器（已扩展）
├── Container.php               # 依赖注入容器
├── CacheManager.php            # 缓存管理器
├── ScheduledTaskManager.php    # 定时任务调度器
└── EventListenerManager.php    # 事件监听管理器
```

## 🔧 核心组件说明

### 1. AttributeBootstrap
**位置**: `app/support/AttributeBootstrap.php`

主引导文件，在应用启动时自动执行以下操作：
- 扫描并注册所有服务（Service、Component、Repository）
- 加载所有路由映射（Controller、RestController、RequestMapping 等）
- 注册事件监听器（EventListener）
- 注册定时任务（Scheduled）
- 扫描缓存处理器（Cacheable、CachePut、CacheEvict）

**已添加到**: `config/bootstrap.php`

### 2. Container（依赖注入容器）
**位置**: `app/support/Container.php`

**支持的 Attributes**:
- `#[Autowired]` - 自动装配依赖
- `#[Inject]` - 通用注入
- `#[Lazy]` - 懒加载注入（创建代理对象）
- `#[Value]` - 配置值注入
- `#[Qualifier]` - 指定具体实现
- `#[Service]` - 服务层组件
- `#[Component]` - 通用组件
- `#[Repository]` - 数据仓库
- `#[Configuration]` - 配置类
- `#[Bean]` - Bean 工厂方法
- `#[Scope]` - 作用域定义
- `#[Primary]` - 主要实现

**功能**:
- 自动扫描并注册带有 Service/Component/Repository 的类
- 支持构造函数注入和属性注入
- 支持懒加载（Lazy）代理
- 支持配置值注入（Value）
- 支持单例和原型作用域

### 3. AttributeRouteLoader（路由加载器）
**位置**: `app/support/AttributeRouteLoader.php`

**支持的 Attributes**:
- `#[Route]` - 基础路由
- `#[RequestMapping]` - 通用请求映射
- `#[GetMapping]` - GET 请求
- `#[PostMapping]` - POST 请求
- `#[PutMapping]` - PUT 请求
- `#[DeleteMapping]` - DELETE 请求
- `#[PatchMapping]` - PATCH 请求
- `#[Controller]` - 控制器（类级别）
- `#[RestController]` - REST 控制器（类级别）

**功能**:
- 自动扫描控制器并注册路由
- 支持类级别的路由前缀
- 支持类级别和方法级别的中间件
- 自动生成路由名称

### 4. CacheManager（缓存管理器）
**位置**: `app/support/CacheManager.php`

**支持的 Attributes**:
- `#[Cacheable]` - 缓存方法返回值
- `#[CachePut]` - 更新缓存
- `#[CacheEvict]` - 清除缓存

**功能**:
- 自动缓存方法返回值
- 支持自定义缓存键模板
- 支持条件缓存（condition、unless）
- 支持多种缓存存储（Redis、默认缓存）
- 支持 TTL 设置
- 支持清除单个或所有缓存

### 5. ScheduledTaskManager（定时任务调度器）
**位置**: `app/support/ScheduledTaskManager.php`

**支持的 Attributes**:
- `#[Scheduled]` - 定时任务

**功能**:
- 支持 Cron 表达式
- 支持固定延迟（fixedDelay）
- 支持固定频率（fixedRate）
- 支持初始延迟（initialDelay）
- 支持时区设置
- 支持启用/禁用任务

### 6. EventListenerManager（事件监听管理器）
**位置**: `app/support/EventListenerManager.php`

**支持的 Attributes**:
- `#[EventListener]` - 事件监听器

**功能**:
- 自动注册事件监听器
- 支持多个事件
- 支持优先级设置
- 集成 Webman Event 系统

## 🚀 使用方法

### 1. 依赖注入示例

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired, Lazy, Value};
use app\repository\UserRepository;

#[Service(singleton: true)]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Lazy]
    private EmailService $emailService;
    
    #[Value(key: 'app.name', default: 'MyApp')]
    private string $appName;
    
    public function getUsers()
    {
        return $this->repository->findAll();
    }
}
```

### 2. 路由映射示例

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping, PostMapping};

#[RestController(prefix: '/api/users', middleware: ['auth'])]
class UserController
{
    #[GetMapping(path: '/{id}', name: 'users.show')]
    public function show($id)
    {
        return ['id' => $id];
    }
    
    #[PostMapping(path: '', middleware: ['throttle:10,1'])]
    public function store()
    {
        return ['status' => 'created'];
    }
}
```

### 3. 缓存示例

```php
<?php

namespace app\service;

use app\attribute\{Service, Cacheable, CacheEvict};

#[Service]
class ProductService
{
    #[Cacheable(key: 'product:{id}', ttl: 3600)]
    public function getProduct($id)
    {
        return Product::find($id);
    }
    
    #[CacheEvict(key: 'product:{id}')]
    public function updateProduct($id, $data)
    {
        return Product::update($id, $data);
    }
}
```

### 4. 定时任务示例

```php
<?php

namespace app\task;

use app\attribute\{Service, Scheduled};

#[Service]
class CleanupTask
{
    #[Scheduled(cron: '0 0 * * *', timeZone: 'Asia/Shanghai')]
    public function dailyCleanup()
    {
        // 每天凌晨执行
    }
    
    #[Scheduled(fixedRate: 60000)]
    public function updateStats()
    {
        // 每分钟执行一次
    }
}
```

### 5. 事件监听示例

```php
<?php

namespace app\listener;

use app\attribute\{Service, EventListener};

#[Service]
class UserEventListener
{
    #[EventListener(events: 'user.created', priority: 10)]
    public function onUserCreated($event)
    {
        // 处理用户创建事件
    }
}
```

## 📂 推荐的目录结构

```
app/
├── controller/          # 控制器（使用 #[Controller] 或 #[RestController]）
├── service/            # 服务层（使用 #[Service]）
├── repository/         # 数据仓库（使用 #[Repository]）
├── component/          # 通用组件（使用 #[Component]）
├── task/              # 定时任务（使用 #[Scheduled]）
├── listener/          # 事件监听器（使用 #[EventListener]）
├── config/            # 配置类（使用 #[Configuration]）
└── attribute/         # Attribute 定义
```

## ⚙️ 配置说明

### Bootstrap 配置
已在 `config/bootstrap.php` 中添加：

```php
return [
    support\bootstrap\Session::class,
    app\support\AttributeBootstrap::class,  // ← 新增
];
```

### 扫描目录
`AttributeBootstrap` 会自动扫描以下目录：

**服务注册**:
- `app/service`
- `app/repository`
- `app/component`

**路由加载**:
- `app/admin/controller`
- `app/front/controller`
- `app/api/controller`
- `app/controller`

**事件监听**:
- `app/listener`
- `app/service`

**定时任务**:
- `app/task`
- `app/service`

**缓存处理**:
- `app/service`
- `app/repository`

## 🔍 调试信息

启动应用时，会在控制台输出以下信息：

```
=== Initializing Attribute System ===

1. Scanning and registering services...

2. Loading routes from attributes...

3. Registering event listeners...
Registered event listener: user.created -> app\listener\UserEventListener::onUserCreated (priority: 10)

4. Registering scheduled tasks...
Registered cron task: app\task\CleanupTask::dailyCleanup with schedule 0 0 * * *

5. Scanning cache handlers...

=== Attribute System Initialized ===
```

## 📝 注意事项

1. **性能**: 首次启动会扫描所有文件，建议在生产环境使用缓存
2. **命名空间**: 确保所有类的命名空间正确
3. **依赖**: 确保 `workerman/crontab` 已安装（定时任务需要）
4. **Redis**: 缓存功能默认使用 Redis，确保已配置
5. **事件**: 事件监听需要 `webman/event` 包

## 🎯 获取容器实例

在任何地方都可以通过以下方式获取容器实例：

```php
use app\support\Container;

$container = Container::getInstance();

// 获取服务
$userService = $container->make(UserService::class);

// 获取懒加载代理
$emailService = $container->makeLazy(EmailService::class);
```

## 🔗 相关文档

- [Attributes 使用指南](../ATTRIBUTES_USAGE.md)
- [Attribute 列表](../app/attribute/README.md)
- [示例代码](../app/example/)
