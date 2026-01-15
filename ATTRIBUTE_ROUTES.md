# Webman PHP 8 Attributes 路由使用指南

## 功能说明

这个实现为 Webman 框架添加了 PHP 8 Attributes（注解）方式的路由定义，让你可以直接在控制器方法上使用 `#[Route]` 标注路由，无需在 `config/route.php` 手动注册。

## 核心文件

- `app/attribute/Route.php` - Route Attribute 定义
- `app/support/AttributeRouteLoader.php` - 自动扫描和注册路由的加载器
- `config/route.php` - 已启用自动加载：`AttributeRouteLoader::load()`

## 基本用法

### 1. 简单 GET 路由

```php
<?php
namespace app\admin\controller;

use app\attribute\Route;
use support\Request;

class UserController
{
    #[Route('GET', '/admin/users')]
    public function index(Request $request)
    {
        return json(['users' => []]);
    }
}
```

访问：`GET http://your-domain/admin/users`

### 2. 带路由参数

```php
#[Route('GET', '/admin/user/{id}')]
public function show(Request $request, $id)
{
    return json(['user_id' => $id]);
}
```

访问：`GET http://your-domain/admin/user/123`

### 3. 多个 HTTP 方法

```php
#[Route(['GET', 'POST'], '/admin/user/save')]
public function save(Request $request)
{
    return json(['saved' => true]);
}
```

### 4. 任意方法（ANY）

```php
#[Route('ANY', '/admin/webhook')]
public function webhook(Request $request)
{
    return json(['method' => $request->method()]);
}
```

### 5. 命名路由（用于 URL 生成）

```php
#[Route('GET', '/admin/user/{id}/edit', 'admin.user.edit')]
public function edit(Request $request, $id)
{
    return view('user/edit', ['id' => $id]);
}
```

生成 URL：
```php
$url = route('admin.user.edit', ['id' => 123]); // /admin/user/123/edit
```

### 6. 带中间件

```php
#[Route('POST', '/admin/user/delete', middleware: [\app\middleware\AdminAuth::class])]
public function delete(Request $request)
{
    return json(['deleted' => true]);
}
```

### 7. 一个方法多个路由

```php
#[Route('GET', '/api/v1/users')]
#[Route('GET', '/api/v2/users')]
public function users(Request $request)
{
    return json(['users' => []]);
}
```

## 完整示例

```php
<?php
namespace app\admin\controller;

use app\attribute\Route;
use support\Request;

class ArticleController
{
    #[Route('GET', '/admin/articles', 'admin.articles.index')]
    public function index(Request $request)
    {
        return json(['articles' => []]);
    }
    
    #[Route('GET', '/admin/article/{id}', 'admin.articles.show')]
    public function show(Request $request, $id)
    {
        return json(['article_id' => $id]);
    }
    
    #[Route('POST', '/admin/article', 'admin.articles.store', middleware: [\app\middleware\AdminAuth::class])]
    public function store(Request $request)
    {
        return json(['created' => true]);
    }
    
    #[Route('PUT', '/admin/article/{id}', 'admin.articles.update')]
    public function update(Request $request, $id)
    {
        return json(['updated' => $id]);
    }
    
    #[Route('DELETE', '/admin/article/{id}', 'admin.articles.destroy')]
    public function destroy(Request $request, $id)
    {
        return json(['deleted' => $id]);
    }
}
```

## Route Attribute 参数说明

```php
#[Route(
    methods: 'GET',              // string|array - HTTP 方法：GET, POST, PUT, DELETE, PATCH, OPTIONS, HEAD, ANY
    path: '/your/path',          // string - 路由路径，支持参数如 {id}
    name: 'route.name',          // string|null - 路由名称（可选）
    middleware: []               // array - 中间件列表（可选）
)]
```

## 自动扫描目录

默认扫描以下目录：
- `app/admin/controller`
- `app/front/controller`
- `app/api/controller`
- `app/controller`

### 自定义扫描目录

在 `config/route.php` 中：

```php
use app\support\AttributeRouteLoader;

// 扫描自定义目录
AttributeRouteLoader::load([
    base_path() . '/app/custom/controller',
    base_path() . '/app/other/controller',
]);
```

## 注意事项

1. **PHP 版本要求**：需要 PHP 8.0+
2. **类命名空间**：确保控制器类有正确的命名空间
3. **方法可见性**：只有 `public` 非静态方法会被扫描
4. **路由优先级**：Attribute 路由在 `config/route.php` 中的手动路由**之后**加载
5. **性能**：路由扫描只在启动时执行一次，不影响运行时性能

## 与传统路由共存

你可以同时使用 Attribute 路由和传统路由定义：

```php
// config/route.php
use Webman\Route;
use app\support\AttributeRouteLoader;

// 传统路由
Route::get('/legacy/path', [SomeController::class, 'method']);

// 自动加载 Attribute 路由
AttributeRouteLoader::load();
```

## 调试

如果路由未生效，检查：

1. 确认 `config/route.php` 中已调用 `AttributeRouteLoader::load()`
2. 检查控制器文件是否在扫描目录内
3. 确认 `use app\attribute\Route;` 导入正确
4. 查看日志文件 `runtime/logs/webman-*.log` 是否有错误信息
5. 重启 Webman：`php start.php restart`

## 示例项目结构

```
app/
├── attribute/
│   └── Route.php                    # Route Attribute 定义
├── support/
│   └── AttributeRouteLoader.php     # 路由加载器
├── admin/
│   └── controller/
│       ├── IndexController.php      # 使用 #[Route] 标注
│       └── UserController.php
└── front/
    └── controller/
        └── HomeController.php

config/
└── route.php                        # 启用 AttributeRouteLoader::load()
```

## 高级用法：路由分组

虽然当前实现专注于方法级别的 Attribute，你仍可以在 `config/route.php` 中结合使用：

```php
Route::group('/api', function () {
    AttributeRouteLoader::load([base_path() . '/app/api/controller']);
});
```

## 性能优化建议

在生产环境，考虑：
1. 使用 OPcache 缓存 PHP 文件
2. 确保 `workerman/webman-framework` 版本 >= 2.1
3. 路由扫描只在进程启动时执行，不会影响每次请求

---

**实现完成！** 现在你可以在控制器方法上直接使用 `#[Route]` 定义路由了。
