<?php
declare(strict_types=1);
namespace app\front\controller;

use app\attribute\routing\Route;
use app\attribute\validation\{Email, In, Length, Pattern, Required, Rule};
use app\support\ValidationInterceptor;
use ReflectionMethod;
use support\Request;

/**
 * 自动验证控制器
 * 直接在方法参数上使用 PHP 8 Attributes 进行验证
 */
class AutoValidateController
{
    /**
     * 用户注册 - 使用独立的验证属性
     */
    #[Route('POST', '/auto/register', 'auto.register')]
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
        
        // 验证通过，处理业务逻辑
        return json([
            'success' => true,
            'message' => '注册成功',
            'data' => [
                'username' => $username,
                'email' => $email
            ]
        ]);
    }
    
    /**
     * 用户登录 - 使用 Rule 属性（ThinkPHP 风格）
     */
    #[Route('POST', '/auto/login', 'auto.login')]
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
        
        // 登录逻辑
        return json([
            'success' => true,
            'message' => '登录成功',
            'token' => 'mock_token_' . time()
        ]);
    }
    
    /**
     * 创建订单
     */
    #[Route('POST', '/auto/order', 'auto.order')]
    public function createOrder(
        Request $request,
        #[Rule(rule: 'require|number', message: '用户ID必须是数字')]
        int $user_id = 0,
        
        #[Rule(rule: 'require|number|min:0.01', message: '订单金额必须大于0')]
        float $total_amount = 0,
        
        #[Required]
        #[In(values: ['pending', 'paid', 'shipped', 'completed'], message: '订单状态不正确')]
        string $status = 'pending',
        
        #[Length(max: 500, message: '备注不能超过500个字符')]
        ?string $note = null
    ) {
        if (!$this->validate(func_get_args())) {
            return $this->validationError();
        }
        
        return json([
            'success' => true,
            'message' => '订单创建成功',
            'data' => [
                'user_id' => $user_id,
                'total_amount' => $total_amount,
                'status' => $status,
                'note' => $note
            ]
        ]);
    }
    
    /**
     * 更新用户信息
     */
    #[Route('POST', '/auto/user/update', 'auto.user.update')]
    public function updateUser(
        Request $request,
        #[Rule(rule: 'require|length:3,20|alphaNum', message: '用户名格式不正确')]
        string $username = '',
        
        #[Rule(rule: 'email', message: '邮箱格式不正确')]
        ?string $email = null,
        
        #[Rule(rule: 'mobile', message: '手机号格式不正确')]
        ?string $phone = null,
        
        #[Rule(rule: 'number|between:1,120', message: '年龄必须在1-120之间')]
        ?int $age = null,
        
        #[Rule(rule: 'in:male,female,other', message: '性别值不正确')]
        ?string $gender = null
    ) {
        if (!$this->validate(func_get_args())) {
            return $this->validationError();
        }
        
        return json([
            'success' => true,
            'message' => '用户信息更新成功',
            'data' => compact('username', 'email', 'phone', 'age', 'gender')
        ]);
    }
    
    /**
     * 发布文章
     */
    #[Route('POST', '/auto/article', 'auto.article')]
    public function createArticle(
        Request $request,
        #[Rule(rule: 'require|length:5,100', message: '标题长度必须在5-100个字符之间')]
        string $title = '',
        
        #[Rule(rule: 'require|length:10,5000', message: '内容长度必须在10-5000个字符之间')]
        string $content = '',
        
        #[Rule(rule: 'require|in:draft,published,archived', message: '文章状态不正确')]
        string $status = 'draft',
        
        #[Rule(rule: 'url', message: '封面图URL格式不正确')]
        ?string $cover_image = null,
        
        #[Rule(rule: 'length:0,200', message: '摘要不能超过200个字符')]
        ?string $summary = null
    ) {
        if (!$this->validate(func_get_args())) {
            return $this->validationError();
        }
        
        return json([
            'success' => true,
            'message' => '文章创建成功',
            'data' => compact('title', 'content', 'status', 'cover_image', 'summary')
        ]);
    }
    
    /**
     * 自动验证方法
     */
    protected function validate(array $arguments): bool
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        $callerMethod = $backtrace[1]['function'] ?? null;
        
        if (!$callerMethod) {
            return true;
        }
        
        try {
            $reflection = new ReflectionMethod($this, $callerMethod);
            $interceptor = new ValidationInterceptor();
            
            return $interceptor->validateMethodParameters($reflection, $arguments);
        } catch (\Exception $e) {
            return true;
        }
    }
    
    /**
     * 返回验证错误
     */
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
