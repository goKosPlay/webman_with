# PHP 8 Attributes 自动验证指南

## 概述

直接在控制器方法参数上使用 PHP 8 Attributes 声明验证规则，实现自动验证，无需手动创建验证器类或 DTO。

## 核心特性

✅ **参数级别验证** - 直接在方法参数上声明验证规则  
✅ **ThinkPHP 风格** - 支持 `#[Rule]` 属性使用字符串规则  
✅ **独立属性** - 支持 `#[Required]`、`#[Email]` 等独立验证属性  
✅ **自动执行** - 调用 `validate()` 方法自动验证  
✅ **友好错误** - 自定义错误消息  

## 两种验证方式

### 方式 1：使用 Rule 属性（ThinkPHP 风格）

```php
#[Route('POST', '/auto/login')]
public function login(
    Request $request,
    #[Rule(rule: 'require|email', message: '请输入正确的邮箱')]
    string $email = '',
    
    #[Rule(rule: 'require|length:6,32', message: '密码长度必须在 6-32 个字符之间')]
    string $password = ''
) {
    if (!$this->validate(func_get_args())) {
        return $this->validationError();
    }
    
    // 业务逻辑
    return json(['success' => true]);
}
```

### 方式 2：使用独立验证属性

```php
#[Route('POST', '/auto/register')]
public function register(
    Request $request,
    #[Required(message: '用户名不能为空')]
    #[Length(min: 3, max: 20, message: '用户名长度必须在 3-20 个字符之间')]
    string $username = '',
    
    #[Required(message: '邮箱不能为空')]
    #[Email(message: '邮箱格式不正确')]
    string $email = '',
    
    #[Required(message: '密码不能为空')]
    #[Length(min: 6, max: 32)]
    string $password = ''
) {
    if (!$this->validate(func_get_args())) {
        return $this->validationError();
    }
    
    // 业务逻辑
    return json(['success' => true, 'data' => compact('username', 'email')]);
}
```

## 可用的验证规则

### Rule 属性支持的规则

| 规则 | 说明 | 示例 |
|------|------|------|
| `require` | 必填 | `require` |
| `email` | 邮箱 | `email` |
| `mobile` | 手机号 | `mobile` |
| `url` | URL | `url` |
| `number` | 数字 | `number` |
| `integer` | 整数 | `integer` |
| `alpha` | 字母 | `alpha` |
| `alphaNum` | 字母和数字 | `alphaNum` |
| `length` | 长度 | `length:3,20` |
| `min` | 最小值/长度 | `min:6` |
| `max` | 最大值/长度 | `max:100` |
| `between` | 区间 | `between:1,120` |
| `in` | 枚举 | `in:male,female,other` |
| `regex` | 正则 | `regex:/^[A-Z]+$/` |

### 独立验证属性

```php
use app\attribute\validation\{
    Required, Email, Min, Max, Length, 
    Pattern, In, Url, Numeric
};

#[Required(message: '不能为空')]
#[Email(message: '邮箱格式不正确')]
#[Min(value: 18, message: '年龄必须大于等于18')]
#[Max(value: 100, message: '年龄不能超过100')]
#[Length(min: 3, max: 20, message: '长度必须在3-20之间')]
#[Pattern(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
#[In(values: ['male', 'female'], message: '性别值不正确')]
#[Url(message: 'URL格式不正确')]
#[Numeric(message: '必须是数字')]
```

## 完整示例

### 示例 1：用户注册

```php
<?php

namespace app\controller;

use app\attribute\Route;
use app\attribute\validation\{Required, Email, Length, Pattern};
use app\support\ValidationInterceptor;
use support\Request;
use ReflectionMethod;

class UserController
{
    #[Route('POST', '/users/register', 'users.register')]
    public function register(
        Request $request,
        #[Required(message: '用户名不能为空')]
        #[Length(min: 3, max: 20, message: '用户名长度必须在 3-20 个字符之间')]
        string $username = '',
        
        #[Required(message: '邮箱不能为空')]
        #[Email(message: '邮箱格式不正确')]
        string $email = '',
        
        #[Required(message: '密码不能为空')]
        #[Length(min: 6, max: 32, message: '密码长度必须在 6-32 个字符之间')]
        string $password = '',
        
        #[Pattern(pattern: '/^1[3-9]\d{9}$/', message: '手机号格式不正确')]
        ?string $phone = null
    ) {
        // 自动验证
        if (!$this->validate(func_get_args())) {
            return $this->validationError();
        }
        
        // 创建用户
        $user = [
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'phone' => $phone
        ];
        
        return json([
            'success' => true,
            'message' => '注册成功',
            'data' => $user
        ]);
    }
    
    protected function validate(array $arguments): bool
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerMethod = $backtrace[1]['function'] ?? null;
        
        if (!$callerMethod) return true;
        
        try {
            $reflection = new ReflectionMethod($this, $callerMethod);
            $interceptor = new ValidationInterceptor();
            return $interceptor->validateMethodParameters($reflection, $arguments);
        } catch (\Exception $e) {
            return true;
        }
    }
    
    protected function validationError()
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerMethod = $backtrace[1]['function'] ?? null;
        
        if (!$callerMethod) {
            return json(['success' => false, 'message' => 'Validation failed'], 422);
        }
        
        try {
            $reflection = new ReflectionMethod($this, $callerMethod);
            $interceptor = new ValidationInterceptor();
            $interceptor->validateMethodParameters($reflection, func_get_args());
            
            return json([
                'success' => false,
                'message' => $interceptor->getFirstError(),
                'errors' => $interceptor->getErrors()
            ], 422);
        } catch (\Exception $e) {
            return json(['success' => false, 'message' => 'Validation failed'], 422);
        }
    }
}
```

### 示例 2：使用 Rule 属性（ThinkPHP 风格）

```php
use app\attribute\validation\Rule;

#[Route('POST', '/orders', 'orders.create')]
public function createOrder(
    Request $request,
    #[Rule(rule: 'require|number', message: '用户ID必须是数字')]
    int $user_id = 0,
    
    #[Rule(rule: 'require|number|min:0.01', message: '订单金额必须大于0')]
    float $total_amount = 0,
    
    #[Rule(rule: 'require|in:pending,paid,shipped,completed', message: '订单状态不正确')]
    string $status = 'pending',
    
    #[Rule(rule: 'length:0,500', message: '备注不能超过500个字符')]
    ?string $note = null
) {
    if (!$this->validate(func_get_args())) {
        return $this->validationError();
    }
    
    // 创建订单逻辑
    return json([
        'success' => true,
        'data' => compact('user_id', 'total_amount', 'status', 'note')
    ]);
}
```

### 示例 3：混合使用

```php
#[Route('POST', '/articles', 'articles.create')]
public function createArticle(
    Request $request,
    #[Required]
    #[Length(min: 5, max: 100)]
    string $title = '',
    
    #[Rule(rule: 'require|length:10,5000', message: '内容长度必须在10-5000个字符之间')]
    string $content = '',
    
    #[In(values: ['draft', 'published', 'archived'])]
    string $status = 'draft',
    
    #[Url(message: '封面图URL格式不正确')]
    ?string $cover_image = null
) {
    if (!$this->validate(func_get_args())) {
        return $this->validationError();
    }
    
    return json(['success' => true]);
}
```

## 测试 API

### 测试注册接口

```bash
# 成功案例
curl -X POST http://localhost:8787/auto/register \
  -d "username=john&email=john@example.com&password=123456&phone=13800138000"

# 失败案例 - 用户名太短
curl -X POST http://localhost:8787/auto/register \
  -d "username=jo&email=john@example.com&password=123456"

# 失败案例 - 邮箱格式错误
curl -X POST http://localhost:8787/auto/register \
  -d "username=john&email=invalid-email&password=123456"
```

### 测试登录接口

```bash
curl -X POST http://localhost:8787/auto/login \
  -d "email=john@example.com&password=123456"
```

### 测试订单创建

```bash
curl -X POST http://localhost:8787/auto/order \
  -d "user_id=1&total_amount=99.99&status=pending&note=Test order"
```

### 测试用户更新

```bash
curl -X POST http://localhost:8787/auto/user/update \
  -d "username=john123&email=john@example.com&phone=13800138000&age=25&gender=male"
```

## 规则组合

### 多个规则

```php
#[Required]
#[Email]
#[Length(max: 100)]
string $email = ''
```

### 可选字段

```php
// 不添加 Required，其他验证只在有值时生效
#[Email]
#[Length(max: 100)]
?string $email = null
```

### ThinkPHP 风格规则链

```php
#[Rule(rule: 'require|email|length:5,100', message: '邮箱格式不正确或长度超限')]
string $email = ''
```

## 优势

1. **声明式** - 验证规则直接在参数上声明，一目了然
2. **无需 DTO** - 不需要创建额外的 DTO 类
3. **ThinkPHP 兼容** - 支持 ThinkPHP 的验证规则语法
4. **类型安全** - 利用 PHP 类型系统
5. **自动执行** - 调用 `validate()` 即可
6. **友好错误** - 自定义错误消息

## 与传统方式对比

### 传统方式（需要创建验证器类）

```php
// 需要创建 UserValidate.php
class UserValidate extends BaseValidate {
    protected $rule = [
        'username' => 'require|length:3,20',
        'email' => 'require|email',
    ];
}

// 控制器中使用
public function register(Request $request) {
    $validate = new UserValidate();
    if (!$validate->check($request->post())) {
        return json(['error' => $validate->getError()], 422);
    }
}
```

### PHP 8 Attributes 方式（无需额外类）

```php
public function register(
    Request $request,
    #[Rule(rule: 'require|length:3,20')]
    string $username = '',
    
    #[Rule(rule: 'require|email')]
    string $email = ''
) {
    if (!$this->validate(func_get_args())) {
        return $this->validationError();
    }
}
```

## 最佳实践

1. **使用类型提示** - 结合 PHP 类型系统
2. **提供默认值** - 避免未定义参数错误
3. **自定义消息** - 提供友好的中文提示
4. **可选参数使用 nullable** - 使用 `?string` 或 `?int`
5. **复杂验证使用 Rule** - 多个规则用 `|` 连接

## 总结

PHP 8 Attributes 自动验证系统提供了：

- ✅ **参数级别的声明式验证**
- ✅ **ThinkPHP 风格的规则语法**
- ✅ **无需创建额外的验证器类**
- ✅ **自动执行验证**
- ✅ **友好的错误提示**
- ✅ **类型安全**

这是最现代化、最简洁的验证方式，充分利用了 PHP 8 的 Attributes 特性。
