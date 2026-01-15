# PHP 8 Attributes 快速开始指南

## 🚀 已完成的功能

您的 Webman 应用现在已经完全集成了 PHP 8 Attributes 系统，包括：

✅ **依赖注入** - 控制器和服务层的自动依赖注入  
✅ **路由映射** - 基于 Attributes 的路由定义  
✅ **事件监听** - 声明式事件监听器  
✅ **定时任务** - Cron 和定时任务调度  
✅ **缓存管理** - 方法级别的缓存控制  

## 📁 项目结构

```
app/
├── attribute/          # 28个 Attribute 定义
├── controller/         # 控制器（支持构造函数注入）
├── service/           # 服务层（支持属性注入）
├── repository/        # 数据仓库
├── listener/          # 事件监听器
├── event/            # 事件类定义
├── support/          # 核心支持类
│   ├── Container.php              # 依赖注入容器
│   ├── ControllerInjector.php     # 控制器注入器
│   ├── AttributeRouteLoader.php   # 路由加载器
│   ├── EventListenerManager.php   # 事件管理器
│   ├── ScheduledTaskManager.php   # 定时任务管理器
│   ├── CacheManager.php           # 缓存管理器
│   └── AttributeBootstrap.php     # 系统引导
└── example/          # 完整示例代码
```

## 🎯 快速示例

### 1. 创建控制器（支持自动注入）

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping, PostMapping};
use app\service\UserService;
use support\Request;

#[RestController(prefix: '/api/users', middleware: ['auth'])]
class UserController
{
    // ✅ 构造函数自动注入
    public function __construct(
        private UserService $userService
    ) {}
    
    #[GetMapping(path: '')]
    public function index()
    {
        return json($this->userService->getAll());
    }
    
    #[PostMapping(path: '')]
    public function store(Request $request)
    {
        $user = $this->userService->create($request->all());
        return json($user, 201);
    }
    
    #[GetMapping(path: '/{id}')]
    public function show(int $id)
    {
        return json($this->userService->findById($id));
    }
}
```

### 2. 创建服务（支持属性注入）

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired, Lazy, Cacheable, Transactional};
use app\repository\UserRepository;

#[Service(singleton: true)]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Lazy]
    private EmailService $emailService;
    
    #[Cacheable(key: 'users:all', ttl: 3600)]
    public function getAll(): array
    {
        return $this->repository->findAll();
    }
    
    #[Transactional]
    public function create(array $data): array
    {
        $user = $this->repository->create($data);
        $this->emailService->sendWelcome($user);
        return $user;
    }
}
```

### 3. 创建事件监听器

```php
<?php

namespace app\listener;

use app\attribute\{Service, EventListener};
use support\Log;

#[Service]
class UserEventListener
{
    #[EventListener(events: 'user.created', priority: 10)]
    public function onUserCreated($event)
    {
        Log::info('User created', $event);
    }
    
    #[EventListener(events: ['user.login', 'user.logout'])]
    public function onAuthEvent($event)
    {
        Log::info('Auth event', $event);
    }
}
```

### 4. 创建定时任务

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

## 🔧 启动应用

```bash
# 重启应用使更改生效
php start.php restart

# 或者停止后重新启动
php start.stop
php start.php start

# 守护进程模式
php start.php start -d
```

## 📊 启动时的输出

```
=== Initializing Attribute System ===

1. Scanning and registering services...

2. Routes will be loaded from config/route.php

3. Registering event listeners...
Registered event listener: user.created -> app\listener\UserEventListener::onUserCreated (priority: 10)
Registered event listener: user.login -> app\listener\UserEventListener::onAuthEvent (priority: 0)

4. Registering scheduled tasks...
Registered cron task: app\task\CleanupTask::dailyCleanup with schedule 0 0 * * *

5. Scanning cache handlers...

=== Attribute System Initialized ===
```

## 🧪 测试功能

### 测试路由和依赖注入

```bash
# 测试事件系统
curl http://localhost:8787/api/event-test/trigger-all

# 测试用户创建
curl -X POST http://localhost:8787/api/event-test/user/create \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com"}'
```

### 测试现有路由

```bash
# 测试管理后台
curl http://localhost:8787/admin
```

## 📚 详细文档

| 文档 | 说明 |
|------|------|
| `ATTRIBUTES_USAGE.md` | 所有 Attributes 的完整使用指南 |
| `ATTRIBUTES_INTEGRATION.md` | 系统集成说明和技术细节 |
| `AUTO_INJECTION_GUIDE.md` | 自动依赖注入完整指南 |
| `EVENT_LISTENER_GUIDE.md` | 事件监听器使用指南 |
| `ROUTE_FIX_GUIDE.md` | 路由问题修复指南 |
| `DEPENDENCY_INJECTION_GUIDE.md` | 依赖注入详细说明 |

## 🎨 可用的 Attributes

### 路由相关 (7个)
- `#[Route]` - 基础路由
- `#[RequestMapping]` - 通用请求映射
- `#[GetMapping]` - GET 请求
- `#[PostMapping]` - POST 请求
- `#[PutMapping]` - PUT 请求
- `#[DeleteMapping]` - DELETE 请求
- `#[PatchMapping]` - PATCH 请求

### 依赖注入 (5个)
- `#[Autowired]` - 自动装配
- `#[Inject]` - 通用注入
- `#[Lazy]` - 懒加载
- `#[Value]` - 配置值注入
- `#[Qualifier]` - 指定实现

### 组件定义 (9个)
- `#[Service]` - 服务层
- `#[Component]` - 通用组件
- `#[Repository]` - 数据仓库
- `#[Controller]` - 控制器
- `#[RestController]` - REST控制器
- `#[Configuration]` - 配置类
- `#[Bean]` - Bean工厂
- `#[Scope]` - 作用域
- `#[Primary]` - 主要实现

### 缓存管理 (3个)
- `#[Cacheable]` - 缓存结果
- `#[CachePut]` - 更新缓存
- `#[CacheEvict]` - 清除缓存

### 其他功能 (7个)
- `#[Scheduled]` - 定时任务
- `#[EventListener]` - 事件监听
- `#[Async]` - 异步执行
- `#[Transactional]` - 事务管理
- `#[Validated]` - 验证
- `#[Middleware]` - 中间件
- `#[Conditional]` - 条件化

## ⚡ 常见问题

### Q: 路由返回 404？
**A**: 检查路由路径和 HTTP 方法是否正确，重启应用。

### Q: 依赖注入不工作？
**A**: 确保服务类有 `#[Service]` 属性，且目录被扫描。

### Q: 事件监听器不触发？
**A**: 检查事件名称是否匹配，确认监听器已注册。

### Q: 定时任务不执行？
**A**: 检查 cron 表达式，确认 `workerman/crontab` 已安装。

## 🔍 调试技巧

### 查看日志

```bash
tail -f runtime/logs/webman.log
```

### 检查服务注册

在 `AttributeBootstrap` 中添加调试输出查看已注册的服务。

### 测试依赖注入

```php
$container = Container::getInstance();
$service = $container->make(UserService::class);
var_dump($service);
```

## 💡 最佳实践

1. **控制器轻量化** - 只负责接收请求和返回响应
2. **业务逻辑在服务层** - 使用 `#[Service]` 标记
3. **使用单例** - 对于无状态服务使用 `singleton: true`
4. **合理使用懒加载** - 对于可选或重量级依赖使用 `#[Lazy]`
5. **事件驱动** - 使用事件解耦业务逻辑
6. **缓存优化** - 对频繁查询使用 `#[Cacheable]`

## 🎯 下一步

1. 查看 `app/example/` 目录下的完整示例
2. 阅读详细文档了解高级功能
3. 根据需求创建自己的控制器和服务
4. 使用事件系统解耦业务逻辑
5. 添加定时任务处理后台作业

## 🚀 开始开发

现在您可以使用现代化的 PHP 8 Attributes 开发 Webman 应用了！

```php
// 创建新控制器
#[RestController(prefix: '/api/products')]
class ProductController
{
    public function __construct(
        private ProductService $productService
    ) {}
    
    #[GetMapping(path: '')]
    public function index()
    {
        return json($this->productService->getAll());
    }
}
```

享受开发吧！🎉
