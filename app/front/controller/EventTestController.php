<?php
declare(strict_types=1);
namespace app\front\controller;

use app\attribute\dependency\RestController;
use app\attribute\routing\{GetMapping, PostMapping};
use app\service\{OrderService, UserService};
use support\Request;
use support\Response;

#[RestController(prefix: '/api/event-test')]
class EventTestController
{
    public function __construct(
        private UserService $userService,
        private OrderService $orderService
    ) {}
    
    #[PostMapping(path: '/user/create')]
    public function createUser(Request $request): Response
    {
        $user = $this->userService->createUser([
            'name' => $request->input('name', 'Test User'),
            'email' => $request->input('email', 'test@example.com'),
        ]);
        
        return json([
            'code' => 0,
            'msg' => 'User created successfully',
            'data' => $user
        ]);
    }
    
    #[PostMapping(path: '/user/{id}/update')]
    public function updateUser(Request $request, int $id): Response
    {
        $this->userService->updateUser($id, [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
        ]);
        
        return json([
            'code' => 0,
            'msg' => 'User updated successfully'
        ]);
    }
    
    #[PostMapping(path: '/user/{id}/delete')]
    public function deleteUser(int $id): Response
    {
        $this->userService->deleteUser($id);
        
        return json([
            'code' => 0,
            'msg' => 'User deleted successfully'
        ]);
    }
    
    #[PostMapping(path: '/user/login')]
    public function login(Request $request): Response
    {
        $user = $this->userService->login(
            $request->input('email', 'test@example.com'),
            $request->input('password', 'password')
        );
        
        if (!$user) {
            return json([
                'code' => 401,
                'msg' => 'Login failed'
            ]);
        }
        
        return json([
            'code' => 0,
            'msg' => 'Login successful',
            'data' => $user
        ]);
    }
    
    #[PostMapping(path: '/user/{id}/logout')]
    public function logout(int $id): Response
    {
        $this->userService->logout($id);
        
        return json([
            'code' => 0,
            'msg' => 'Logout successful'
        ]);
    }
    
    #[PostMapping(path: '/user/password-reset')]
    public function passwordReset(Request $request): Response
    {
        $token = $this->userService->requestPasswordReset(
            $request->input('email', 'test@example.com')
        );
        
        return json([
            'code' => 0,
            'msg' => 'Password reset email sent',
            'data' => ['token' => $token]
        ]);
    }
    
    #[PostMapping(path: '/order/create')]
    public function createOrder(Request $request): Response
    {
        $order = $this->orderService->createOrder([
            'user_id' => $request->input('user_id', 1001),
            'email' => $request->input('email', 'customer@example.com'),
            'total' => $request->input('total', 99.99),
            'items' => $request->input('items', []),
        ]);
        
        return json([
            'code' => 0,
            'msg' => 'Order created successfully',
            'data' => $order
        ]);
    }
    
    #[PostMapping(path: '/order/{id}/pay')]
    public function payOrder(Request $request, int $id): Response
    {
        $this->orderService->markAsPaid(
            $id,
            $request->input('payment_method', 'credit_card'),
            $request->input('email', 'customer@example.com')
        );
        
        return json([
            'code' => 0,
            'msg' => 'Order paid successfully'
        ]);
    }
    
    #[PostMapping(path: '/order/{id}/ship')]
    public function shipOrder(Request $request, int $id): Response
    {
        $this->orderService->shipOrder(
            $id,
            $request->input('tracking_number', 'TRACK123456'),
            $request->input('email', 'customer@example.com')
        );
        
        return json([
            'code' => 0,
            'msg' => 'Order shipped successfully'
        ]);
    }
    
    #[PostMapping(path: '/order/{id}/cancel')]
    public function cancelOrder(Request $request, int $id): Response
    {
        $this->orderService->cancelOrder(
            $id,
            $request->input('reason', 'Customer request')
        );
        
        return json([
            'code' => 0,
            'msg' => 'Order cancelled successfully'
        ]);
    }
    
    #[GetMapping(path: '/trigger-all')]
    public function triggerAllEvents(): Response
    {
        $userService = $this->userService;
        $orderService = $this->orderService;
        
        $user = $userService->createUser([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
        
        sleep(1);
        
        $userService->updateUser($user['id'], [
            'name' => 'John Smith',
        ]);
        
        sleep(1);
        
        $order = $orderService->createOrder([
            'user_id' => $user['id'],
            'email' => 'john@example.com',
            'total' => 199.99,
            'items' => ['Product A', 'Product B'],
        ]);
        
        sleep(1);
        
        $orderService->markAsPaid($order['id'], 'stripe', 'john@example.com');
        
        sleep(1);
        
        $orderService->shipOrder($order['id'], 'TRACK789', 'john@example.com');
        
        sleep(1);
        
        $userService->logout($user['id']);
        
        return json([
            'code' => 0,
            'msg' => 'All events triggered successfully',
            'data' => [
                'user' => $user,
                'order' => $order,
            ]
        ]);
    }
}
