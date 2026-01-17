<?php
declare(strict_types=1);
namespace app\front\controller;

use app\attribute\routing\Route;
use app\validate\{OrderValidate, UserValidate};
use support\Request;

class ThinkValidateController
{
    #[Route('POST', '/think/validate/user/register', 'think.validate.user.register')]
    public function userRegister(Request $request)
    {
        $data = $request->post();
        
        $validate = new UserValidate();
        
        // 使用场景验证
        if (!$validate->scene('register')->check($data)) {
            return json([
                'success' => false,
                'message' => $validate->getFirstError(),
                'errors' => $validate->getError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '注册数据验证通过',
            'data' => [
                'username' => $data['username'],
                'email' => $data['email']
            ]
        ]);
    }
    
    #[Route('POST', '/think/validate/user/login', 'think.validate.user.login')]
    public function userLogin(Request $request)
    {
        $data = $request->post();
        
        $validate = new UserValidate();
        
        // 使用登录场景
        if (!$validate->scene('login')->check($data)) {
            return json([
                'success' => false,
                'message' => $validate->getFirstError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '登录数据验证通过'
        ]);
    }
    
    #[Route('POST', '/think/validate/user/update', 'think.validate.user.update')]
    public function userUpdate(Request $request)
    {
        $data = $request->post();
        
        $validate = new UserValidate();
        
        // 使用更新场景
        if (!$validate->scene('update')->check($data)) {
            return json([
                'success' => false,
                'errors' => $validate->getError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '更新数据验证通过'
        ]);
    }
    
    #[Route('POST', '/think/validate/order/create', 'think.validate.order.create')]
    public function orderCreate(Request $request)
    {
        $data = $request->post();
        
        $validate = new OrderValidate();
        
        if (!$validate->scene('create')->check($data)) {
            return json([
                'success' => false,
                'message' => $validate->getFirstError(),
                'errors' => $validate->getError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '订单创建数据验证通过',
            'data' => $data
        ]);
    }
    
    #[Route('POST', '/think/validate/order/update', 'think.validate.order.update')]
    public function orderUpdate(Request $request)
    {
        $data = $request->post();
        
        $validate = new OrderValidate();
        
        if (!$validate->scene('update')->check($data)) {
            return json([
                'success' => false,
                'errors' => $validate->getError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '订单更新数据验证通过'
        ]);
    }
    
    #[Route('POST', '/think/validate/quick', 'think.validate.quick')]
    public function quickValidate(Request $request)
    {
        $data = $request->post();
        
        // 快速验证（不创建验证器类）
        $validate = new class extends \app\validate\BaseValidate {
            protected array $rule = [
                'name'  => 'require|length:2,20',
                'email' => 'require|email',
                'age'   => 'number|between:1,120',
            ];
            
            protected array $message = [
                'name.require'  => '姓名不能为空',
                'name.length'   => '姓名长度必须在 2-20 个字符之间',
                'email.require' => '邮箱不能为空',
                'email.email'   => '邮箱格式不正确',
                'age.number'    => '年龄必须是数字',
                'age.between'   => '年龄必须在 1-120 之间',
            ];
        };
        
        if (!$validate->check($data)) {
            return json([
                'success' => false,
                'errors' => $validate->getError()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => '快速验证通过'
        ]);
    }
}
