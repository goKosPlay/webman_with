# PHP 8 Attributes 属性列表

本目录包含所有可用的 PHP 8 Attributes，用于依赖注入、路由映射、缓存管理、定时任务等功能。

## 📋 属性分类

### 🌐 路由映射 (Routing)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Route` | Method | 基础路由定义，支持多种 HTTP 方法 |
| `RequestMapping` | Class/Method | 通用请求映射，支持类和方法级别 |
| `GetMapping` | Method | GET 请求快捷映射 |
| `PostMapping` | Method | POST 请求快捷映射 |
| `PutMapping` | Method | PUT 请求快捷映射 |
| `DeleteMapping` | Method | DELETE 请求快捷映射 |
| `PatchMapping` | Method | PATCH 请求快捷映射 |

### 💉 依赖注入 (Dependency Injection)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Autowired` | Property/Parameter | 自动装配依赖 |
| `Inject` | Property/Parameter | 通用注入标记 |
| `Lazy` | Property/Parameter | 懒加载注入 |
| `Value` | Property/Parameter | 配置值注入 |
| `Qualifier` | Property/Parameter | 指定具体实现 |

### 🏗️ 组件定义 (Component Definition)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Service` | Class | 服务层组件 |
| `Component` | Class | 通用组件 |
| `Repository` | Class | 数据仓库组件 |
| `Controller` | Class | 控制器组件 |
| `RestController` | Class | REST 控制器组件 |
| `Configuration` | Class | 配置类 |
| `Bean` | Method | Bean 工厂方法 |
| `Scope` | Class | 作用域定义 |
| `Primary` | Class/Method | 主要实现标记 |

### 💾 缓存管理 (Caching)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Cacheable` | Method | 缓存方法返回值 |
| `CachePut` | Method | 更新缓存 |
| `CacheEvict` | Method | 清除缓存 |

### ⏰ 定时任务 (Scheduling)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Scheduled` | Method | 定时任务配置 |

### 🔧 功能增强 (Enhancement)

| 属性 | 目标 | 说明 |
|------|------|------|
| `Async` | Method | 异步执行 |
| `Transactional` | Method | 事务管理 |
| `Validated` | Class/Method | 验证标记 |
| `EventListener` | Method | 事件监听器 |
| `Middleware` | Class/Method | 中间件 |
| `Conditional` | Class/Method | 条件化创建 |

## 📊 属性统计

- **总计**: 28 个属性
- **路由相关**: 7 个
- **依赖注入**: 5 个
- **组件定义**: 9 个
- **缓存管理**: 3 个
- **定时任务**: 1 个
- **功能增强**: 6 个

## 🚀 快速开始

### 1. 路由映射示例

```php
use app\attribute\{RestController, GetMapping, PostMapping};

#[RestController(prefix: '/api/users')]
class UserController
{
    #[GetMapping(path: '/{id}')]
    public function show($id) {}
    
    #[PostMapping(path: '')]
    public function store() {}
}
```

### 2. 依赖注入示例

```php
use app\attribute\{Service, Autowired, Lazy};

#[Service]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Lazy]
    private EmailService $emailService;
}
```

### 3. 缓存管理示例

```php
use app\attribute\{Cacheable, CacheEvict};

class UserService
{
    #[Cacheable(key: 'user:{id}', ttl: 3600)]
    public function findById($id) {}
    
    #[CacheEvict(key: 'user:{id}')]
    public function update($id, $data) {}
}
```

### 4. 定时任务示例

```php
use app\attribute\Scheduled;

class TaskService
{
    #[Scheduled(cron: '0 0 * * *')]
    public function dailyCleanup() {}
    
    #[Scheduled(fixedRate: 60000)]
    public function updateMetrics() {}
}
```

## 📖 详细文档

查看 [ATTRIBUTES_USAGE.md](../../ATTRIBUTES_USAGE.md) 获取完整的使用指南和示例。

## 📁 示例代码

查看 `app/example/` 目录下的示例代码：

- `ExampleUserController.php` - 控制器示例
- `ExampleUserService.php` - 服务层示例
- `ExampleUserRepository.php` - 仓库层示例
- `ExampleTaskService.php` - 定时任务示例
- `ExampleAppConfig.php` - 配置类示例

## ⚠️ 注意事项

1. 需要 PHP 8.1 或更高版本
2. 需要实现相应的处理器来解析和执行这些属性
3. 建议参考 Spring Framework 的设计理念
4. 合理使用可以大幅提升代码的可维护性和可读性

## 🔗 相关资源

- [PHP Attributes 官方文档](https://www.php.net/manual/en/language.attributes.php)
- [Webman 框架文档](https://www.workerman.net/doc/webman)
- [Spring Framework 注解参考](https://spring.io/projects/spring-framework)
