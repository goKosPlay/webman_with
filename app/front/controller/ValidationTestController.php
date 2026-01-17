<?php
declare(strict_types=1);
namespace app\front\controller;

use app\attribute\routing\Route;
use app\dto\{CreateOrderDto, CreateUserDto};
use app\support\Validator;
use support\Request;

class ValidationTestController
{
    #[Route('POST', '/validate/user', 'validate.user')]
    public function validateUser(Request $request)
    {
        // 从请求创建 DTO
        $dto = CreateUserDto::fromArray($request->post());
        
        // 验证
        $validator = new Validator();
        
        if (!$validator->validate($dto)) {
            return json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        // 验证通过，处理业务逻辑
        return json([
            'success' => true,
            'message' => 'User data is valid',
            'data' => [
                'username' => $dto->username,
                'email' => $dto->email
            ]
        ]);
    }
    
    #[Route('POST', '/validate/order', 'validate.order')]
    public function validateOrder(Request $request)
    {
        $dto = CreateOrderDto::fromArray($request->post());
        
        $validator = new Validator();
        
        if (!$validator->validate($dto)) {
            return json([
                'success' => false,
                'message' => $validator->getFirstError(),
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => 'Order data is valid',
            'data' => [
                'user_id' => $dto->user_id,
                'total_amount' => $dto->total_amount,
                'status' => $dto->status
            ]
        ]);
    }
    
    #[Route('POST', '/validate/array', 'validate.array')]
    public function validateArray(Request $request)
    {
        $data = $request->post();
        
        // 使用数组规则验证
        $validator = new Validator();
        
        $rules = [
            'name' => ['required', 'min:3'],
            'email' => ['required', 'email'],
            'age' => ['required', 'numeric', 'min:18'],
        ];
        
        if (!$validator->validateArray($data, $rules)) {
            return json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        return json([
            'success' => true,
            'message' => 'Data is valid'
        ]);
    }
    
    #[Route('POST', '/validate/custom', 'validate.custom')]
    public function validateCustom(Request $request)
    {
        $data = $request->post();
        
        $validator = new Validator();
        
        // 使用对象规则
        $rules = [
            'username' => [
                new \app\attribute\validation\Required(),
                new \app\attribute\validation\Length(min: 3, max: 20)
            ],
            'email' => [
                new \app\attribute\validation\Required(),
                new \app\attribute\validation\Email()
            ],
            'role' => [
                new \app\attribute\validation\In(values: ['admin', 'user', 'guest'])
            ]
        ];
        
        if (!$validator->validateArray($data, $rules)) {
            return json([
                'success' => false,
                'errors' => $validator->getErrors()
            ], 422);
        }
        
        return json([
            'success' => true,
            'data' => $data
        ]);
    }
}
