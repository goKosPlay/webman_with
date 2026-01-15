# 路由加载问题修复指南

## 🐛 问题描述

错误信息：
```
AttributeRouteLoader: Failed to register route for app\admin\controller\IndexController::index: 
Call to a member function addRoute() on null
```

## ✅ 已修复的问题

### 1. 路由注册逻辑优化

**文件**: `app/support/AttributeRouteLoader.php`

**修复内容**:
- 添加了 null 检查，防止 `Route::add()` 返回 null 时调用方法
- 为每个 HTTP 方法单独注册路由
- 改进错误日志记录

**修复前**:
```php
$route = Route::add($httpMethods, $path, $callback);
$route->name($routeAttr->name);  // 如果 $route 是 null 会报错
```

**修复后**:
```php
foreach ((array)$httpMethods as $httpMethod) {
    $route = Route::add($httpMethod, $path, $callback);
    
    if ($route === null) {
        error_log("Route::add returned null...");
        continue;
    }
    
    if (isset($routeAttr->name) && $routeAttr->name) {
        $route->name($routeAttr->name);
    }
}
```

### 2. 移除重复的路由加载

**文件**: `app/support/AttributeBootstrap.php`

**修复内容**:
- 从 `AttributeBootstrap::start()` 中移除了 `AttributeRouteLoader::load()` 调用
- 路由只在 `config/route.php` 中加载一次

**原因**: 
- Bootstrap 在 worker 启动时执行，此时路由系统可能还未完全初始化
- 路由应该在 `config/route.php` 中加载，这是 Webman 的标准做法

### 3. 优化路由配置

**文件**: `config/route.php`

**修复内容**:
- 移除了 `Route::disableDefaultRoute()`（如果不需要的话）
- 保持简洁的路由加载配置

**当前配置**:
```php
use Webman\Route;
use app\support\AttributeRouteLoader;

// Load routes from attributes
AttributeRouteLoader::load();
```

## 🔍 路由加载流程

### 正确的加载顺序

1. **Webman 启动** → 初始化路由系统
2. **加载 config/route.php** → 调用 `AttributeRouteLoader::load()`
3. **扫描控制器** → 注册所有带 Attribute 的路由
4. **Bootstrap 启动** → 注册服务、事件监听器等（不包括路由）

### AttributeRouteLoader 工作流程

```
AttributeRouteLoader::load()
  ↓
扫描控制器目录
  ↓
检查类的 Attribute (#[Controller], #[RestController])
  ↓
检查方法的 Attribute (#[GetMapping], #[PostMapping] 等)
  ↓
为每个 HTTP 方法注册路由
  ↓
应用中间件和路由名称
```

## 📝 支持的路由 Attributes

### 类级别

- `#[Controller(prefix: '/admin', middleware: ['auth'])]`
- `#[RestController(prefix: '/api')]`
- `#[RequestMapping(path: '/users')]`

### 方法级别

- `#[Route('GET', '/path', 'name')]`
- `#[RequestMapping(path: '/path', methods: 'GET')]`
- `#[GetMapping(path: '/path')]`
- `#[PostMapping(path: '/path')]`
- `#[PutMapping(path: '/path')]`
- `#[DeleteMapping(path: '/path')]`
- `#[PatchMapping(path: '/path')]`

## 🧪 测试路由是否正常工作

### 方法 1: 查看日志

启动应用时，应该看到类似输出：
```
=== Initializing Attribute System ===

1. Scanning and registering services...

2. Routes will be loaded from config/route.php

3. Registering event listeners...
...
```

**不应该看到**:
```
AttributeRouteLoader: Failed to register route...
AttributeRouteLoader: Route::add returned null...
```

### 方法 2: 访问路由

测试现有的路由：
```bash
# 测试 IndexController
curl http://localhost:8787/admin

# 测试 EventTestController
curl http://localhost:8787/api/event-test/trigger-all
```

### 方法 3: 检查路由列表

创建一个路由列表命令（可选）：
```php
// 在控制器中添加
#[GetMapping(path: '/debug/routes')]
public function listRoutes()
{
    // 获取所有注册的路由
    return json(['routes' => 'Route list here']);
}
```

## 🔧 常见问题排查

### 问题 1: 路由返回 404

**可能原因**:
- 路径不匹配
- HTTP 方法不匹配
- 路由未正确注册

**解决方法**:
```bash
# 检查日志
tail -f runtime/logs/webman.log

# 重启应用
php start.php restart
```

### 问题 2: Route::add() 返回 null

**可能原因**:
- 在错误的时机调用（如在 Bootstrap 中）
- 路由系统未初始化

**解决方法**:
- 确保只在 `config/route.php` 中调用 `AttributeRouteLoader::load()`
- 不要在 `AttributeBootstrap` 中加载路由

### 问题 3: 中间件不生效

**检查**:
```php
#[RestController(prefix: '/api', middleware: ['auth'])]
class UserController
{
    #[GetMapping(path: '/users', middleware: ['throttle:60,1'])]
    public function index() {}
}
```

**确保**:
- 中间件已在 `config/middleware.php` 中定义
- 中间件名称正确

## 📊 路由优先级

当同时使用类级别和方法级别的 Attribute 时：

1. **路径**: `类前缀 + 方法路径`
   ```php
   #[RestController(prefix: '/api')]
   class UserController {
       #[GetMapping(path: '/users')]  // 最终路径: /api/users
   }
   ```

2. **中间件**: `类中间件 + 方法中间件`
   ```php
   #[RestController(middleware: ['auth'])]
   class UserController {
       #[GetMapping(middleware: ['throttle'])]  // 应用: auth + throttle
   }
   ```

## 💡 最佳实践

### 1. 使用类级别前缀

```php
#[RestController(prefix: '/api/v1')]
class UserController
{
    #[GetMapping(path: '/users')]      // /api/v1/users
    #[GetMapping(path: '/users/{id}')] // /api/v1/users/{id}
}
```

### 2. 分离公共和私有路由

```php
// 公共 API
#[RestController(prefix: '/api/public')]
class PublicApiController {}

// 需要认证的 API
#[RestController(prefix: '/api', middleware: ['auth'])]
class UserApiController {}
```

### 3. 使用路由名称

```php
#[GetMapping(path: '/users/{id}', name: 'users.show')]
public function show($id) {}

// 在代码中使用
$url = route('users.show', ['id' => 1]);
```

### 4. 合理使用中间件

```php
#[RestController(prefix: '/api', middleware: ['auth', 'json'])]
class ApiController
{
    // 所有方法都应用 auth 和 json 中间件
    
    #[GetMapping(path: '/sensitive', middleware: ['admin'])]
    public function sensitive() {
        // 应用: auth + json + admin
    }
}
```

## 🚀 重启应用

修复后，重启应用使更改生效：

```bash
# 停止
php start.php stop

# 启动（开发模式）
php start.php start

# 或者重启
php start.php restart

# 守护进程模式
php start.php start -d
```

## 📚 相关文档

- [Webman 路由文档](https://www.workerman.net/doc/webman/route.html)
- [Attributes 使用指南](./ATTRIBUTES_USAGE.md)
- [Attributes 集成说明](./ATTRIBUTES_INTEGRATION.md)

## ✅ 验证修复

修复完成后，应该：

1. ✅ 没有 "Route::add returned null" 错误
2. ✅ 所有路由正常访问
3. ✅ 中间件正常工作
4. ✅ 路由名称可以使用

如果仍有问题，请检查：
- PHP 版本 >= 8.1
- Webman 版本是否最新
- 控制器命名空间是否正确
- Attribute 语法是否正确
