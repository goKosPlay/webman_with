# 依赖注入使用指南

## 🐛 常见问题

### ArgumentCountError: Too few arguments to function __construct()

**错误示例**:
```
ArgumentCountError: Too few arguments to function app\controller\EventTestController::__construct(), 
0 passed in /vendor/workerman/webman-framework/src/Container.php on line 70 and exactly 2 expected
```

**原因**: Webman 的默认容器不支持控制器的构造函数自动注入。

## ✅ 解决方案

### 方案 1: 使用 Getter 方法（推荐用于控制器）

```php
<?php

namespace app\controller;

use app\attribute\RestController;
use app\service\UserService;
use app\support\Container;

#[RestController(prefix: '/api/users')]
class UserController
{
    // 不使用构造函数注入
    
    protected function getUserService(): UserService
    {
        return Container::getInstance()->make(UserService::class);
    }
    
    public function index()
    {
        $users = $this->getUserService()->getAll();
        return json($users);
    }
}
```

### 方案 2: 在方法中直接获取（简单场景）

```php
<?php

namespace app\controller;

use app\support\Container;
use app\service\UserService;

class UserController
{
    public function index()
    {
        $userService = Container::getInstance()->make(UserService::class);
        $users = $userService->getAll();
        return json($users);
    }
}
```

### 方案 3: 使用全局辅助函数（可选）

创建辅助函数：
```php
// app/functions.php
function app(string $class)
{
    return \app\support\Container::getInstance()->make($class);
}
```

使用：
```php
public function index()
{
    $users = app(UserService::class)->getAll();
    return json($users);
}
```

## 🎯 在服务层使用依赖注入

服务层可以使用 `#[Autowired]` 和 `#[Lazy]` 属性：

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired, Lazy};
use app\repository\UserRepository;

#[Service(singleton: true)]
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

## 📋 依赖注入支持对比

| 位置 | 构造函数注入 | 属性注入 | 手动获取 |
|------|------------|---------|---------|
| **Controller** | ❌ 不支持 | ❌ 不支持 | ✅ 推荐 |
| **Service** | ❌ 不支持 | ✅ 支持 | ✅ 支持 |
| **Repository** | ❌ 不支持 | ✅ 支持 | ✅ 支持 |
| **Component** | ❌ 不支持 | ✅ 支持 | ✅ 支持 |

## 🔧 为什么控制器不支持构造函数注入？

Webman 的控制器实例化由框架的默认容器处理，该容器不支持自动解析构造函数参数。这是 Webman 的设计决策，与我们自定义的 `Container` 不同。

**Webman 的控制器实例化流程**:
```
请求到达 → Webman Router → Webman Container → new Controller()
```

**我们的服务实例化流程**:
```
Container::make() → 解析依赖 → 注入属性 → 返回实例
```

## 💡 最佳实践

### 1. 控制器保持轻量

```php
#[RestController(prefix: '/api/users')]
class UserController
{
    protected function getUserService(): UserService
    {
        return Container::getInstance()->make(UserService::class);
    }
    
    #[GetMapping(path: '')]
    public function index()
    {
        // 控制器只负责接收请求和返回响应
        return json($this->getUserService()->getAll());
    }
}
```

### 2. 业务逻辑放在服务层

```php
#[Service]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
    
    #[Autowired]
    private CacheService $cache;
    
    #[Lazy]
    private EmailService $emailService;
    
    public function getAll()
    {
        // 复杂的业务逻辑
        return $this->cache->remember('users', function() {
            return $this->repository->findAll();
        });
    }
}
```

### 3. 缓存服务实例（性能优化）

如果在同一个请求中多次使用服务：

```php
class UserController
{
    private ?UserService $userService = null;
    
    protected function getUserService(): UserService
    {
        if ($this->userService === null) {
            $this->userService = Container::getInstance()->make(UserService::class);
        }
        return $this->userService;
    }
}
```

### 4. 使用 Trait 简化代码（可选）

创建一个 Trait：
```php
<?php

namespace app\traits;

use app\support\Container;

trait InjectsServices
{
    protected function make(string $class)
    {
        return Container::getInstance()->make($class);
    }
}
```

在控制器中使用：
```php
class UserController
{
    use InjectsServices;
    
    public function index()
    {
        $users = $this->make(UserService::class)->getAll();
        return json($users);
    }
}
```

## 🎨 完整示例

### 控制器层

```php
<?php

namespace app\controller;

use app\attribute\{RestController, GetMapping, PostMapping};
use app\service\{UserService, OrderService};
use app\support\Container;
use support\Request;

#[RestController(prefix: '/api')]
class ApiController
{
    protected function getUserService(): UserService
    {
        return Container::getInstance()->make(UserService::class);
    }
    
    protected function getOrderService(): OrderService
    {
        return Container::getInstance()->make(OrderService::class);
    }
    
    #[GetMapping(path: '/users')]
    public function getUsers()
    {
        return json($this->getUserService()->getAll());
    }
    
    #[PostMapping(path: '/orders')]
    public function createOrder(Request $request)
    {
        $order = $this->getOrderService()->create($request->all());
        return json($order);
    }
}
```

### 服务层

```php
<?php

namespace app\service;

use app\attribute\{Service, Autowired, Lazy, Transactional};
use app\repository\{UserRepository, OrderRepository};

#[Service(singleton: true)]
class OrderService
{
    #[Autowired]
    private OrderRepository $orderRepository;
    
    #[Autowired]
    private UserRepository $userRepository;
    
    #[Lazy]
    private EmailService $emailService;
    
    #[Lazy]
    private PaymentService $paymentService;
    
    #[Transactional]
    public function create(array $data): array
    {
        $user = $this->userRepository->find($data['user_id']);
        $order = $this->orderRepository->create($data);
        
        $this->emailService->sendOrderConfirmation($order);
        
        return $order;
    }
}
```

### 仓库层

```php
<?php

namespace app\repository;

use app\attribute\{Repository, Value};

#[Repository(singleton: true)]
class UserRepository
{
    #[Value(key: 'database.default', default: 'mysql')]
    private string $connection;
    
    public function findAll(): array
    {
        return Db::connection($this->connection)
            ->table('users')
            ->get();
    }
}
```

## 🔍 调试依赖注入

### 检查服务是否已注册

```php
$container = Container::getInstance();

// 检查绑定
if (isset($container->bindings[UserService::class])) {
    echo "UserService is registered\n";
}

// 获取实例
try {
    $service = $container->make(UserService::class);
    echo "Service created successfully\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

### 查看已注册的服务

在 `AttributeBootstrap` 中添加调试输出：

```php
public static function start($worker): void
{
    // ... 扫描服务
    
    echo "Registered services:\n";
    foreach ($container->bindings as $abstract => $binding) {
        echo "  - {$abstract}\n";
    }
}
```

## ⚠️ 常见错误

### 1. 在控制器构造函数中注入

❌ **错误**:
```php
class UserController
{
    public function __construct(
        private UserService $userService  // 不支持！
    ) {}
}
```

✅ **正确**:
```php
class UserController
{
    protected function getUserService(): UserService
    {
        return Container::getInstance()->make(UserService::class);
    }
}
```

### 2. 忘记添加 #[Service] 属性

❌ **错误**:
```php
class UserService  // 没有 #[Service]
{
    #[Autowired]
    private UserRepository $repository;  // 不会被注入！
}
```

✅ **正确**:
```php
#[Service]
class UserService
{
    #[Autowired]
    private UserRepository $repository;
}
```

### 3. 循环依赖

❌ **错误**:
```php
#[Service]
class ServiceA
{
    #[Autowired]
    private ServiceB $serviceB;
}

#[Service]
class ServiceB
{
    #[Autowired]
    private ServiceA $serviceA;  // 循环依赖！
}
```

✅ **解决方案**: 使用 `#[Lazy]` 或重构代码

```php
#[Service]
class ServiceA
{
    #[Lazy]
    private ServiceB $serviceB;  // 懒加载打破循环
}
```

## 📚 相关文档

- [Attributes 使用指南](./ATTRIBUTES_USAGE.md)
- [Attributes 集成说明](./ATTRIBUTES_INTEGRATION.md)
- [事件监听器指南](./EVENT_LISTENER_GUIDE.md)

## 🎯 总结

- **控制器**: 使用 getter 方法手动获取服务
- **服务层**: 使用 `#[Autowired]` 和 `#[Lazy]` 属性注入
- **保持简单**: 控制器轻量，业务逻辑在服务层
- **性能优化**: 合理使用单例和懒加载
