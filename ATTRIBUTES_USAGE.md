# PHP 8 Attributes 使用指南

本项目已集成完整的 PHP 8 Attributes 支持，包括依赖注入、路由映射、缓存、定时任务等功能。

## 目录

1. [路由映射 Attributes](#路由映射-attributes)
2. [依赖注入 Attributes](#依赖注入-attributes)
3. [缓存 Attributes](#缓存-attributes)
4. [定时任务 Attributes](#定时任务-attributes)
5. [服务组件 Attributes](#服务组件-attributes)
6. [其他功能 Attributes](#其他功能-attributes)

---

## 路由映射 Attributes

### RequestMapping
类或方法级别的路由映射，支持多种 HTTP 方法。

```php
use app\attribute\RequestMapping;

#[RequestMapping(path: '/api', methods: ['GET', 'POST'])]
class ApiController
{
    #[RequestMapping(path: '/users', methods: 'GET')]
    public function getUsers() {}
}
```

### GetMapping / PostMapping / PutMapping / DeleteMapping / PatchMapping
快捷的 HTTP 方法映射。

```php
use app\attribute\{GetMapping, PostMapping, PutMapping, DeleteMapping, PatchMapping};

class UserController
{
    #[GetMapping(path: '/users/{id}', name: 'user.show')]
    public function show($id) {}
    
    #[PostMapping(path: '/users', middleware: ['auth'])]
    public function store() {}
    
    #[PutMapping(path: '/users/{id}')]
    public function update($id) {}
    
    #[DeleteMapping(path: '/users/{id}')]
    public function destroy($id) {}
    
    #[PatchMapping(path: '/users/{id}')]
    public function patch($id) {}
}
```

### Controller / RestController
控制器类标记。

```php
use app\attribute\{Controller, RestController};

#[Controller(prefix: '/admin', middleware: ['auth', 'admin'])]
class AdminController {}

#[RestController(prefix: '/api/v1')]
class ApiController {}
```

---

## 依赖注入 Attributes

### Autowired
自动注入依赖。

```php
use app\attribute\Autowired;
use app\attribute\Service;

#[Service]
class UserService
{
    #[Autowired]
    private UserRepository $userRepository;
    
    #[Autowired(required: false)]
    private ?CacheService $cache;
}
```

### Lazy
懒加载注入，只在首次使用时初始化。

```php
use app\attribute\Lazy;

class OrderService
{
    #[Lazy]
    private EmailService $emailService;
    
    #[Lazy(service: 'payment.gateway')]
    private PaymentGateway $gateway;
}
```

### Inject
通用注入标记。

```php
use app\attribute\Inject;

class ProductService
{
    #[Inject(name: 'redis.cache')]
    private $cache;
}
```

### Value
注入配置值。

```php
use app\attribute\Value;

class AppConfig
{
    #[Value(key: 'app.name', default: 'MyApp')]
    private string $appName;
    
    #[Value(key: 'app.debug')]
    private bool $debug;
}
```

### Qualifier
指定具体的依赖实现。

```php
use app\attribute\{Autowired, Qualifier};

class PaymentService
{
    #[Autowired]
    #[Qualifier(name: 'stripe')]
    private PaymentGateway $gateway;
}
```

---

## 缓存 Attributes

### Cacheable
缓存方法返回值。

```php
use app\attribute\Cacheable;

class UserService
{
    #[Cacheable(key: 'user:{id}', ttl: 3600)]
    public function getUserById($id)
    {
        return User::find($id);
    }
    
    #[Cacheable(
        key: 'users:list:{page}',
        ttl: 1800,
        condition: 'page > 0',
        store: 'redis'
    )]
    public function getUsers($page)
    {
        return User::paginate(20, ['*'], 'page', $page);
    }
}
```

### CachePut
更新缓存。

```php
use app\attribute\CachePut;

class UserService
{
    #[CachePut(key: 'user:{id}', ttl: 3600)]
    public function updateUser($id, $data)
    {
        $user = User::find($id);
        $user->update($data);
        return $user;
    }
}
```

### CacheEvict
清除缓存。

```php
use app\attribute\CacheEvict;

class UserService
{
    #[CacheEvict(key: 'user:{id}')]
    public function deleteUser($id)
    {
        User::destroy($id);
    }
    
    #[CacheEvict(allEntries: true, store: 'redis')]
    public function clearAllCache()
    {
        // 清除所有缓存
    }
}
```

---

## 定时任务 Attributes

### Scheduled
定时执行任务。

```php
use app\attribute\Scheduled;

class TaskService
{
    // 使用 Cron 表达式
    #[Scheduled(cron: '0 0 * * *', timeZone: 'Asia/Shanghai')]
    public function dailyCleanup()
    {
        // 每天凌晨执行
    }
    
    // 固定延迟（毫秒）
    #[Scheduled(fixedDelay: 5000, initialDelay: 1000)]
    public function checkStatus()
    {
        // 上次执行完成后延迟 5 秒再执行
    }
    
    // 固定频率（毫秒）
    #[Scheduled(fixedRate: 10000)]
    public function syncData()
    {
        // 每 10 秒执行一次
    }
    
    // 可控制的定时任务
    #[Scheduled(cron: '*/5 * * * *', enabled: true)]
    public function monitorSystem()
    {
        // 每 5 分钟执行一次
    }
}
```

---

## 服务组件 Attributes

### Service
标记服务类。

```php
use app\attribute\Service;

#[Service(name: 'user.service', singleton: true)]
class UserService
{
    public function getUsers() {}
}
```

### Component
通用组件标记。

```php
use app\attribute\Component;

#[Component(singleton: true)]
class EmailSender
{
    public function send($to, $message) {}
}
```

### Repository
数据仓库标记。

```php
use app\attribute\Repository;

#[Repository(name: 'user.repository')]
class UserRepository
{
    public function findById($id) {}
}
```

### Configuration
配置类标记。

```php
use app\attribute\{Configuration, Bean};

#[Configuration]
class DatabaseConfig
{
    #[Bean(name: 'db.connection', singleton: true)]
    public function createConnection()
    {
        return new PDO(...);
    }
}
```

### Bean
方法级别的 Bean 定义。

```php
use app\attribute\Bean;

class AppConfig
{
    #[Bean(name: 'cache.manager')]
    public function cacheManager()
    {
        return new CacheManager();
    }
}
```

### Scope
定义 Bean 的作用域。

```php
use app\attribute\{Service, Scope};

#[Service]
#[Scope(Scope::PROTOTYPE)]
class RequestLogger
{
    // 每次请求都创建新实例
}

#[Service]
#[Scope(Scope::SINGLETON)]
class ConfigManager
{
    // 单例模式
}
```

### Primary
标记主要实现。

```php
use app\attribute\{Service, Primary};

interface PaymentGateway {}

#[Service]
#[Primary]
class StripeGateway implements PaymentGateway {}

#[Service]
class PayPalGateway implements PaymentGateway {}
```

---

## 其他功能 Attributes

### Async
异步执行方法。

```php
use app\attribute\Async;

class NotificationService
{
    #[Async(executor: 'email.executor')]
    public function sendEmail($to, $message)
    {
        // 异步发送邮件
    }
}
```

### Transactional
事务管理。

```php
use app\attribute\Transactional;

class OrderService
{
    #[Transactional(connection: 'mysql', timeout: 30)]
    public function createOrder($data)
    {
        // 在事务中执行
    }
    
    #[Transactional(readOnly: true)]
    public function getOrderStats()
    {
        // 只读事务
    }
}
```

### EventListener
事件监听器。

```php
use app\attribute\EventListener;

class UserEventListener
{
    #[EventListener(events: 'user.created', priority: 10)]
    public function onUserCreated($event)
    {
        // 处理用户创建事件
    }
    
    #[EventListener(events: ['user.updated', 'user.deleted'])]
    public function onUserChanged($event)
    {
        // 处理用户变更事件
    }
}
```

### Middleware
中间件标记。

```php
use app\attribute\Middleware;

#[Middleware(middleware: 'auth', priority: 100)]
class AdminController
{
    #[Middleware(middleware: ['throttle:60,1', 'verified'])]
    public function sensitiveAction() {}
}
```

### Validated
验证标记。

```php
use app\attribute\Validated;

#[Validated]
class UserController
{
    #[Validated(groups: ['create'])]
    public function store($request) {}
}
```

### Conditional
条件化 Bean。

```php
use app\attribute\{Service, Conditional};

#[Service]
#[Conditional(condition: 'env.APP_ENV === "production"')]
class ProductionLogger
{
    // 仅在生产环境创建
}
```

---

## 完整示例

```php
<?php

namespace app\controller;

use app\attribute\{
    RestController,
    GetMapping,
    PostMapping,
    PutMapping,
    DeleteMapping,
    Autowired,
    Lazy,
    Cacheable,
    CacheEvict,
    Validated,
    Middleware
};
use app\service\UserService;
use app\service\CacheService;

#[RestController(prefix: '/api/users', middleware: ['auth'])]
class UserController
{
    #[Autowired]
    private UserService $userService;
    
    #[Lazy]
    private CacheService $cache;
    
    #[GetMapping(path: '', name: 'users.index')]
    #[Cacheable(key: 'users:list:{page}', ttl: 600)]
    public function index($page = 1)
    {
        return $this->userService->paginate($page);
    }
    
    #[GetMapping(path: '/{id}', name: 'users.show')]
    #[Cacheable(key: 'user:{id}', ttl: 3600)]
    public function show($id)
    {
        return $this->userService->findById($id);
    }
    
    #[PostMapping(path: '', name: 'users.store')]
    #[Validated(groups: ['create'])]
    #[Middleware(middleware: 'throttle:10,1')]
    #[CacheEvict(allEntries: true)]
    public function store($request)
    {
        return $this->userService->create($request->all());
    }
    
    #[PutMapping(path: '/{id}', name: 'users.update')]
    #[CacheEvict(key: 'user:{id}')]
    public function update($id, $request)
    {
        return $this->userService->update($id, $request->all());
    }
    
    #[DeleteMapping(path: '/{id}', name: 'users.destroy')]
    #[CacheEvict(key: 'user:{id}')]
    public function destroy($id)
    {
        return $this->userService->delete($id);
    }
}
```

```php
<?php

namespace app\service;

use app\attribute\{
    Service,
    Autowired,
    Lazy,
    Scheduled,
    Transactional,
    Async,
    EventListener
};
use app\repository\UserRepository;

#[Service(singleton: true)]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Lazy]
    private EmailService $emailService;
    
    #[Transactional]
    public function create($data)
    {
        $user = $this->repository->create($data);
        $this->sendWelcomeEmail($user);
        return $user;
    }
    
    #[Async]
    private function sendWelcomeEmail($user)
    {
        $this->emailService->send($user->email, 'Welcome!');
    }
    
    #[Scheduled(cron: '0 2 * * *')]
    public function cleanupInactiveUsers()
    {
        $this->repository->deleteInactive(30);
    }
    
    #[EventListener(events: 'user.login', priority: 10)]
    public function onUserLogin($event)
    {
        $this->repository->updateLastLogin($event->userId);
    }
}
```

---

## 注意事项

1. **命名空间**: 所有 Attributes 都在 `app\attribute` 命名空间下
2. **PHP 版本**: 需要 PHP 8.1 或更高版本
3. **自动加载**: 确保 composer autoload 配置正确
4. **处理器**: 这些 Attributes 需要相应的处理器来解析和执行，需要实现对应的加载器和处理逻辑
5. **性能**: 合理使用缓存和懒加载可以提升应用性能
6. **事务**: 使用 `#[Transactional]` 时注意数据库连接配置

---

## 下一步

要使这些 Attributes 真正工作，你需要实现：

1. **依赖注入容器** - 处理 `#[Autowired]`, `#[Lazy]`, `#[Inject]` 等
2. **路由加载器** - 扫描并注册带有路由 Attributes 的控制器
3. **缓存处理器** - 实现缓存的拦截和管理
4. **定时任务调度器** - 处理 `#[Scheduled]` 注解的方法
5. **事件分发器** - 处理 `#[EventListener]` 注解
6. **AOP 支持** - 实现方法拦截和增强

参考 `app/support/AttributeRouteLoader.php` 了解如何实现 Attribute 处理器。
