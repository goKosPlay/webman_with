<?php
declare(strict_types=1);
namespace app\front\controller;

use app\attribute\dependency\Autowired;
use app\attribute\routing\Route;
use app\service\LogDemoService;
use support\Request;

class LogTestController
{

    #[Autowired]
    private LogDemoService $logDemoService;
    
    #[Route('GET', '/log-demo/register', 'log.demo.register')]
    public function registerDemo(Request $request)
    {
        try {
            $email = $request->get('email', 'user' . time() . '@example.com');
            $password = $request->get('password', 'password123');
            $profile = $request->get('profile', ['name' => 'Test User']);
            
            $user = $this->logDemoService->registerUser($email, $password, $profile);
            
            return json([
                'success' => true,
                'message' => 'User registered successfully',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
    
    #[Route('POST', '/log-demo/process', 'log.demo.process')]
    public function processDemo(Request $request)
    {
        $data = $request->post('data', ['item1', 'item2', 'item3', 'item4', 'item5']);
        
        $result = $this->logDemoService->processData($data);
        
        return json([
            'success' => true,
            'message' => 'Data processed successfully',
            'result' => $result
        ]);
    }
    
    #[Route('GET', '/log-demo/simple', 'log.demo.simple')]
    public function simpleDemo(Request $request)
    {
        $taskName = $request->get('task', 'default_task');
        
        $result = $this->logDemoService->simpleTask($taskName);
        
        return json([
            'success' => true,
            'message' => 'Simple task completed',
            'result' => $result
        ]);
    }
    
    #[Route('POST', '/log-demo/complex', 'log.demo.complex')]
    public function complexDemo(Request $request)
    {
        $type = $request->post('type', 'payment');
        $amount = (int) $request->post('amount', 100);
        $options = $request->post('options', ['priority' => 'high']);
        
        $result = $this->logDemoService->complexBusinessLogic($type, $amount, $options);
        
        return json([
            'success' => true,
            'message' => 'Complex business logic completed',
            'result' => $result
        ]);
    }
    
    #[Route('GET', '/log-demo/silent', 'log.demo.silent')]
    public function silentDemo(Request $request)
    {
        $data = $request->get('data', 'test_data');
        
        $result = $this->logDemoService->silentMethod($data);
        
        return json([
            'success' => true,
            'message' => 'Silent method completed',
            'result' => $result
        ]);
    }
    
    #[Route('GET', '/log-demo/error', 'log.demo.error')]
    public function errorDemo(Request $request)
    {
        try {
            // 故意触发错误
            $this->logDemoService->registerUser('test@example.com', 'password', []);
            
            return json([
                'success' => true,
                'message' => 'This should not be reached'
            ]);
        } catch (\Exception $e) {
            return json([
                'success' => false,
                'message' => 'Error triggered as expected: ' . $e->getMessage()
            ], 400);
        }
    }
}
