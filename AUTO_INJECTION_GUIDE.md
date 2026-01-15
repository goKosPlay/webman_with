# 自动注入完整解决方案

## 🎉 现在支持控制器构造函数自动注入！

我已经为您实现了完整的自动依赖注入系统，现在控制器可以像服务层一样使用构造函数注入了。

## ✅ 已实现的功能

### 1. ControllerInjector（控制器注入器）

**位置**: `app/support/ControllerInjector.php`

**功能**:
- 自动解析控制器构造函数参数
- 从容器中获取依赖实例
- 支持可选参数和默认值
- 智能检测是否需要注入

### 2. 自动路由注入

**修改**: `app/support/AttributeRouteLoader.php`

路由注册时自动使用 `ControllerInjector` 创建控制器实例。

## 🚀 使用方法

### 控制器中使用构造函数注入

现在可以直接在控制器中使用构造函数注入了！

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping, PostMapping};
use app\service\{UserService, OrderService};
use support\Request;

#[RestController(prefix: '/api/users')]
class UserController
{
    // ✅ 现在支持构造函数注入！
    public function __construct(
        private UserService $userService,
        private OrderService $orderService
    ) {}
    
    #[GetMapping(path: '')]
    public function index()
    {
        // 直接使用注入的服务
        return json($this->userService->getAll());
    }
    
    #[PostMapping(path: '')]
    public function store(Request $request)
    {
        $user = $this->userService->create($request->all());
        return json($user);
    }
}
```

### 支持可选依赖

```php
public function __construct(
    private UserService $userService,
    private ?CacheService $cache = null  // 可选依赖
) {}
```

### 支持默认值

```php
public function __construct(
    private UserService $userService,
    private int $pageSize = 20  // 默认值
) {}
```

## 📊 完整的依赖注入支持

| 位置 | 构造函数注入 | 属性注入 | 手动获取 |
|------|------------|---------|---------|
| **Controller** | ✅ **支持** | ❌ 不支持 | ✅ 支持 |
| **Service** | ✅ **支持** | ✅ 支持 | ✅ 支持 |
| **Repository** | ✅ **支持** | ✅ 支持 | ✅ 支持 |
| **Component** | ✅ **支持** | ✅ 支持 | ✅ 支持 |

## 🎯 工作原理

### 1. 路由注册时的处理

```
用户请求 → 路由匹配 → 闭包回调
  ↓
ControllerInjector::make()
  ↓
解析构造函数参数
  ↓
从 Container 获取依赖
  ↓
创建控制器实例
  ↓
调用方法并返回响应
```

### 2. 依赖解析流程

```php
// AttributeRouteLoader 中的处理
$callback = function(...$args) use ($className, $methodName) {
    $injector = ControllerInjector::getInstance();
    
    // 检查是否需要注入
    if ($injector->needsInjection($className)) {
        // 自动注入依赖
        $instance = $injector->make($className);
    } else {
        // 无参数构造函数
        $instance = new $className();
    }
    
    // 调用方法
    return $instance->$methodName(...$args);
};
```

## 💡 最佳实践

### 1. 推荐：使用构造函数注入

```php
#[RestController(prefix: '/api')]
class ApiController
{
    public function __construct(
        private UserService $userService,
        private OrderService $orderService,
        private CacheService $cache
    ) {}
    
    #[GetMapping(path: '/users')]
    public function getUsers()
    {
        return json($this->userService->getAll());
    }
}
```

**优点**:
- ✅ 代码简洁清晰
- ✅ 依赖明确可见
- ✅ 便于测试（可以传入 mock 对象）
- ✅ IDE 自动补全支持好

### 2. 服务层使用属性注入

```php
#[Service]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Lazy]
    private EmailService $emailService;
    
    public function createUser(array $data)
    {
        $user = $this->repository->create($data);
        $this->emailService->sendWelcome($user);
        return $user;
    }
}
```

**优点**:
- ✅ 支持懒加载（`#[Lazy]`）
- ✅ 支持配置注入（`#[Value]`）
- ✅ 更灵活的依赖管理

### 3. 混合使用

```php
#[RestController(prefix: '/api')]
class ApiController
{
    // 构造函数注入核心依赖
    public function __construct(
        private UserService $userService
    ) {}
    
    // 方法中按需获取其他服务
    #[GetMapping(path: '/stats')]
    public function stats()
    {
        $statsService = Container::getInstance()->make(StatsService::class);
        return json($statsService->calculate());
    }
}
```

## 🔍 调试和验证

### 检查注入是否工作

```php
// 在控制器构造函数中添加日志
public function __construct(
    private UserService $userService
) {
    error_log("UserService injected: " . get_class($this->userService));
}
```

### 查看注入的依赖

```php
#[GetMapping(path: '/debug/dependencies')]
public function debugDependencies()
{
    return json([
        'userService' => get_class($this->userService),
        'orderService' => get_class($this->orderService),
    ]);
}
```

## 🎨 完整示例

### 示例 1: RESTful API 控制器

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping, PostMapping, PutMapping, DeleteMapping};
use app\service\UserService;
use support\Request;

#[RestController(prefix: '/api/users', middleware: ['auth'])]
class UserController
{
    public function __construct(
        private UserService $userService
    ) {}
    
    #[GetMapping(path: '')]
    public function index()
    {
        return json($this->userService->paginate());
    }
    
    #[GetMapping(path: '/{id}')]
    public function show(int $id)
    {
        return json($this->userService->findById($id));
    }
    
    #[PostMapping(path: '')]
    public function store(Request $request)
    {
        $user = $this->userService->create($request->all());
        return json($user, 201);
    }
    
    #[PutMapping(path: '/{id}')]
    public function update(Request $request, int $id)
    {
        $user = $this->userService->update($id, $request->all());
        return json($user);
    }
    
    #[DeleteMapping(path: '/{id}')]
    public function destroy(int $id)
    {
        $this->userService->delete($id);
        return json(['message' => 'User deleted']);
    }
}
```

### 示例 2: 多服务注入

```php
<?php

namespace app\controller;

use app\attribute\{RestController, PostMapping};
use app\service\{UserService, OrderService, PaymentService, EmailService};
use support\Request;

#[RestController(prefix: '/api/checkout')]
class CheckoutController
{
    public function __construct(
        private UserService $userService,
        private OrderService $orderService,
        private PaymentService $paymentService,
        private EmailService $emailService
    ) {}
    
    #[PostMapping(path: '/process')]
    public function process(Request $request)
    {
        $userId = $request->input('user_id');
        $user = $this->userService->findById($userId);
        
        $order = $this->orderService->create([
            'user_id' => $userId,
            'items' => $request->input('items'),
            'total' => $request->input('total'),
        ]);
        
        $payment = $this->paymentService->charge(
            $user,
            $order->total,
            $request->input('payment_method')
        );
        
        if ($payment->success) {
            $this->orderService->markAsPaid($order->id);
            $this->emailService->sendOrderConfirmation($user, $order);
        }
        
        return json([
            'order' => $order,
            'payment' => $payment,
        ]);
    }
}
```

### 示例 3: 可选依赖

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping};
use app\service\{UserService, CacheService};

#[RestController(prefix: '/api/users')]
class UserController
{
    public function __construct(
        private UserService $userService,
        private ?CacheService $cache = null  // 可选依赖
    ) {}
    
    #[GetMapping(path: '')]
    public function index()
    {
        // 如果有缓存服务，使用缓存
        if ($this->cache) {
            return json($this->cache->remember('users', function() {
                return $this->userService->getAll();
            }));
        }
        
        // 否则直接查询
        return json($this->userService->getAll());
    }
}
```

## ⚠️ 注意事项

### 1. 确保服务已注册

依赖注入的服务必须已经在容器中注册（通过 `#[Service]`、`#[Component]` 等）。

```php
// ✅ 正确：服务已注册
#[Service]
class UserService {}

// ❌ 错误：服务未注册
class UserService {}  // 没有 #[Service] 属性
```

### 2. 避免循环依赖

```php
// ❌ 错误：循环依赖
class ControllerA {
    public function __construct(private ControllerB $b) {}
}

class ControllerB {
    public function __construct(private ControllerA $a) {}
}
```

**解决方案**: 重构代码或使用懒加载

### 3. 性能考虑

每次请求都会创建新的控制器实例，这是正常的。如果需要优化性能：

- 使用单例服务（`#[Service(singleton: true)]`）
- 使用懒加载（`#[Lazy]`）
- 合理使用缓存

## 🔧 故障排查

### 问题 1: 依赖未注入

**症状**: 属性为 null

**检查**:
1. 服务是否有 `#[Service]` 属性
2. 服务目录是否被扫描（`app/service`）
3. 查看启动日志确认服务已注册

### 问题 2: 类型错误

**症状**: TypeError: must be of type X, null given

**原因**: 依赖解析失败

**解决**:
1. 确保类型提示正确
2. 检查服务是否存在
3. 使用可选参数 `?Type $param = null`

### 问题 3: 性能问题

**症状**: 请求响应慢

**优化**:
1. 使用单例模式
2. 减少不必要的依赖
3. 使用懒加载

## 📚 相关文档

- [Attributes 使用指南](./ATTRIBUTES_USAGE.md)
- [依赖注入指南](./DEPENDENCY_INJECTION_GUIDE.md)
- [事件监听器指南](./EVENT_LISTENER_GUIDE.md)

## 🎯 总结

现在您可以在整个应用中使用统一的依赖注入方式：

✅ **控制器**: 构造函数注入  
✅ **服务层**: 属性注入（`#[Autowired]`、`#[Lazy]`）  
✅ **仓库层**: 属性注入  
✅ **组件**: 属性注入  

享受更简洁、更优雅的代码吧！🚀
