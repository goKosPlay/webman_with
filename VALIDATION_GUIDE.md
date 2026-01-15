# 验证系统使用指南

## 概述

基于 PHP 8 Attributes 的声明式验证系统，支持 DTO 对象验证和数组数据验证。

## 可用的验证属性

### 1. `#[Required]` - 必填验证

```php
use app\attribute\validation\Required;

#[Required(message: '字段不能为空')]
public string $username;
```

### 2. `#[Email]` - 邮箱验证

```php
use app\attribute\validation\Email;

#[Email(message: '邮箱格式不正确')]
public string $email;
```

### 3. `#[Min]` - 最小值验证

```php
use app\attribute\validation\Min;

#[Min(value: 18, message: '年龄必须大于等于 18')]
public int $age;

#[Min(value: 6, message: '密码至少 6 个字符')]
public string $password;
```

### 4. `#[Max]` - 最大值验证

```php
use app\attribute\validation\Max;

#[Max(value: 100, message: '年龄不能超过 100')]
public int $age;

#[Max(value: 200, message: '简介不能超过 200 个字符')]
public string $bio;
```

### 5. `#[Length]` - 长度验证

```php
use app\attribute\validation\Length;

#[Length(min: 3, max: 20, message: '用户名长度必须在 3-20 个字符之间')]
public string $username;

#[Length(min: 6, max: 32)]
public string $password;
```

### 6. `#[Pattern]` - 正则表达式验证

```php
use app\attribute\validation\Pattern;

#[Pattern(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
public string $phone;

#[Pattern(pattern: '/^[A-Z0-9]+$/', message: '只能包含大写字母和数字')]
public string $code;
```

### 7. `#[In]` - 枚举值验证

```php
use app\attribute\validation\In;

#[In(values: ['male', 'female', 'other'], message: '性别值不正确')]
public string $gender;

#[In(values: ['pending', 'paid', 'shipped', 'completed'])]
public string $status;
```

### 8. `#[Url]` - URL 验证

```php
use app\attribute\validation\Url;

#[Url(message: '网址格式不正确')]
public string $website;
```

### 9. `#[Numeric]` - 数字验证

```php
use app\attribute\validation\Numeric;

#[Numeric(message: '必须是数字')]
public string $amount;
```

## 创建 DTO 类

### 示例 1：用户注册 DTO

```php
<?php

namespace app\dto;

use app\attribute\validation\{Required, Email, Length, Pattern};

class CreateUserDto
{
    #[Required(message: '用户名不能为空')]
    #[Length(min: 3, max: 20, message: '用户名长度必须在 3-20 个字符之间')]
    public string $username;
    
    #[Required(message: '邮箱不能为空')]
    #[Email(message: '邮箱格式不正确')]
    public string $email;
    
    #[Required(message: '密码不能为空')]
    #[Length(min: 6, max: 32, message: '密码长度必须在 6-32 个字符之间')]
    public string $password;
    
    #[Pattern(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
    public ?string $phone = null;
    
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->username = $data['username'] ?? '';
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->phone = $data['phone'] ?? null;
        
        return $dto;
    }
}
```

### 示例 2：订单创建 DTO

```php
<?php

namespace app\dto;

use app\attribute\validation\{Required, Numeric, Min, In};

class CreateOrderDto
{
    #[Required]
    #[Numeric]
    public int $user_id;
    
    #[Required]
    public array $items;
    
    #[Required]
    #[Numeric]
    #[Min(value: 0.01, message: '订单金额必须大于 0')]
    public float $total_amount;
    
    #[Required]
    #[In(values: ['pending', 'paid', 'shipped', 'completed', 'cancelled'])]
    public string $status = 'pending';
    
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->user_id = (int)($data['user_id'] ?? 0);
        $dto->items = $data['items'] ?? [];
        $dto->total_amount = (float)($data['total_amount'] ?? 0);
        $dto->status = $data['status'] ?? 'pending';
        
        return $dto;
    }
}
```

## 在控制器中使用

### 方式 1：DTO 对象验证

```php
<?php

namespace app\controller;

use app\attribute\Route;
use app\dto\CreateUserDto;
use app\support\Validator;
use support\Request;

class UserController
{
    #[Route('POST', '/users', 'users.create')]
    public function create(Request $request)
    {
        // 1. 从请求创建 DTO
        $dto = CreateUserDto::fromArray($request->post());
        
        // 2. 验证
        $validator = new Validator();
        
        if (!$validator->validate($dto)) {
            return json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        // 3. 验证通过，处理业务逻辑
        $user = $this->userService->create([
            'username' => $dto->username,
            'email' => $dto->email,
            'password' => $dto->password
        ]);
        
        return json([
            'success' => true,
            'data' => $user
        ]);
    }
}
```

### 方式 2：数组验证（字符串规则）

```php
#[Route('POST', '/login', 'auth.login')]
public function login(Request $request)
{
    $data = $request->post();
    
    $validator = new Validator();
    
    $rules = [
        'email' => ['required', 'email'],
        'password' => ['required', 'min:6']
    ];
    
    if (!$validator->validateArray($data, $rules)) {
        return json([
            'success' => false,
            'errors' => $validator->getErrors()
        ], 422);
    }
    
    // 登录逻辑
    $token = $this->authService->login($data['email'], $data['password']);
    
    return json([
        'success' => true,
        'token' => $token
    ]);
}
```

### 方式 3：数组验证（对象规则）

```php
use app\attribute\validation\{Required, Email, Length, In};

#[Route('POST', '/users/update', 'users.update')]
public function update(Request $request)
{
    $data = $request->post();
    
    $validator = new Validator();
    
    $rules = [
        'username' => [
            new Required(),
            new Length(min: 3, max: 20)
        ],
        'email' => [
            new Required(),
            new Email()
        ],
        'role' => [
            new In(values: ['admin', 'user', 'guest'])
        ]
    ];
    
    if (!$validator->validateArray($data, $rules)) {
        return json([
            'success' => false,
            'errors' => $validator->getErrors()
        ], 422);
    }
    
    // 更新逻辑
    return json(['success' => true]);
}
```

## 错误处理

### 获取所有错误

```php
$validator = new Validator();

if (!$validator->validate($dto)) {
    $errors = $validator->getErrors();
    
    // 返回格式：
    // [
    //     'username' => ['用户名不能为空', '用户名长度必须在 3-20 个字符之间'],
    //     'email' => ['邮箱格式不正确']
    // ]
}
```

### 获取第一个错误

```php
if (!$validator->validate($dto)) {
    $firstError = $validator->getFirstError();
    
    return json([
        'success' => false,
        'message' => $firstError
    ], 422);
}
```

### 检查是否有错误

```php
if ($validator->hasErrors()) {
    // 有错误
}
```

## 完整示例

### 用户注册完整流程

```php
<?php

namespace app\controller;

use app\attribute\{Route, Autowired};
use app\dto\CreateUserDto;
use app\service\UserService;
use app\support\{Validator, Queue};
use app\job\SendEmailJob;
use support\Request;

class UserController
{
    #[Autowired]
    private UserService $userService;
    
    #[Autowired]
    private Queue $queue;
    
    #[Route('POST', '/users/register', 'users.register')]
    public function register(Request $request)
    {
        // 1. 创建并验证 DTO
        $dto = CreateUserDto::fromArray($request->post());
        
        $validator = new Validator();
        
        if (!$validator->validate($dto)) {
            return json([
                'success' => false,
                'message' => '数据验证失败',
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        // 2. 检查用户是否已存在
        if ($this->userService->existsByEmail($dto->email)) {
            return json([
                'success' => false,
                'message' => '邮箱已被注册'
            ], 400);
        }
        
        // 3. 创建用户
        $user = $this->userService->create([
            'username' => $dto->username,
            'email' => $dto->email,
            'password' => password_hash($dto->password, PASSWORD_DEFAULT),
            'phone' => $dto->phone
        ]);
        
        // 4. 异步发送欢迎邮件
        $this->queue->push(SendEmailJob::class, [
            'to' => $user['email'],
            'subject' => 'Welcome!',
            'body' => "Welcome {$user['username']}!"
        ]);
        
        return json([
            'success' => true,
            'message' => '注册成功',
            'data' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'email' => $user['email']
            ]
        ]);
    }
}
```

## 自定义验证规则

### 创建自定义验证属性

```php
<?php

namespace app\attribute\validation;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class Unique
{
    public function __construct(
        public string $table,
        public string $column,
        public ?string $message = null
    ) {}
}
```

### 在 Validator 中添加验证逻辑

```php
// 在 Validator::validateProperty 方法中添加
match (true) {
    // ... 其他规则
    $instance instanceof Unique => $this->validateUnique($fieldName, $value, $instance),
    default => null
};

// 添加验证方法
protected function validateUnique(string $field, mixed $value, Unique $rule): void
{
    if ($value === null || $value === '') {
        return;
    }
    
    $exists = Db::table($rule->table)
        ->where($rule->column, $value)
        ->exists();
    
    if ($exists) {
        $this->addError($field, $rule->message ?? "{$field} already exists");
    }
}
```

## 测试 API

### 测试用户注册验证

```bash
# 成功案例
curl -X POST http://localhost:8787/validate/user \
  -d "username=john&email=john@example.com&password=123456&phone=13800138000"

# 失败案例 - 缺少必填字段
curl -X POST http://localhost:8787/validate/user \
  -d "username=jo&email=invalid-email"

# 失败案例 - 长度不符
curl -X POST http://localhost:8787/validate/user \
  -d "username=ab&email=test@example.com&password=123"
```

### 测试订单验证

```bash
curl -X POST http://localhost:8787/validate/order \
  -H "Content-Type: application/json" \
  -d '{
    "user_id": 1,
    "items": [{"id": 1, "qty": 2}],
    "total_amount": 99.99,
    "status": "pending"
  }'
```

### 测试数组验证

```bash
curl -X POST http://localhost:8787/validate/array \
  -d "name=John&email=john@example.com&age=25"
```

## 验证规则组合

### 多个验证规则

```php
#[Required]
#[Email]
#[Length(max: 100)]
public string $email;
```

### 可选字段验证

```php
// 不添加 Required，其他验证只在有值时生效
#[Email]
#[Length(max: 100)]
public ?string $email = null;
```

### 自定义错误消息

```php
#[Required(message: '请输入用户名')]
#[Length(min: 3, max: 20, message: '用户名必须是 3-20 个字符')]
#[Pattern(pattern: '/^[a-zA-Z0-9_]+$/', message: '用户名只能包含字母、数字和下划线')]
public string $username;
```

## 最佳实践

1. **使用 DTO 类** - 为每个业务场景创建专门的 DTO
2. **添加自定义消息** - 提供友好的中文错误提示
3. **验证在控制器层** - 在业务逻辑之前进行验证
4. **返回统一格式** - 错误响应使用统一的 JSON 格式
5. **可选字段使用 nullable** - 使用 `?string` 或 `?int` 类型

## 总结

验证系统提供了：

- ✅ **声明式验证** - 使用 PHP 8 Attributes
- ✅ **丰富的验证规则** - Required, Email, Length, Pattern 等
- ✅ **DTO 支持** - 类型安全的数据传输对象
- ✅ **数组验证** - 支持传统数组数据验证
- ✅ **自定义消息** - 可自定义错误提示
- ✅ **易于扩展** - 可添加自定义验证规则

通过 Attributes 验证系统，可以编写更清晰、更易维护的验证代码。
