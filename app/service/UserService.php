<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\attribute\transaction\Transactional;
use app\event\UserEvent;
use Webman\Event\Event;
use support\Db;
use support\Log;

#[Service(singleton: true)]
class UserService
{
    #[Transactional]
    public function createUser(array $data): array
    {
        $user = [
            'id' => rand(1000, 9999),
            'name' => $data['name'] ?? 'Unknown',
            'email' => $data['email'] ?? 'user@example.com',
            'created_at' => date('Y-m-d H:i:s'),
        ];
        
        Log::info('User created in database', $user);
        
        Event::emit('user.created', UserEvent::created($user));
        
        return $user;
    }
    
    #[Transactional]
    public function updateUser(int $userId, array $data, ?int $operatorId = null): bool
    {
        $changes = $data;
        
        Log::info('User updated in database', [
            'user_id' => $userId,
            'changes' => $changes
        ]);
        
        Event::emit('user.updated', UserEvent::updated($userId, $changes, $operatorId));
        
        return true;
    }
    
    #[Transactional]
    public function deleteUser(int $userId, ?int $operatorId = null): bool
    {
        Log::info('User deleted from database', ['user_id' => $userId]);
        
        Event::emit('user.deleted', UserEvent::deleted($userId, $operatorId));
        
        return true;
    }
    
    public function login(string $email, string $password): ?array
    {
        $user = [
            'id' => 1001,
            'email' => $email,
            'name' => 'Test User',
        ];
        
        if ($password === 'wrong') {
            Event::emit('user.login', UserEvent::login($user['id'], false, 'Invalid password'));
            return null;
        }
        
        Event::emit('user.login', UserEvent::login($user['id'], true));
        
        return $user;
    }
    
    public function logout(int $userId): void
    {
        Event::emit('user.logout', UserEvent::logout($userId));
    }
    
    public function requestPasswordReset(string $email): string
    {
        $token = bin2hex(random_bytes(32));
        
        Event::emit('user.password.reset', UserEvent::passwordReset($email, $token));
        
        return $token;
    }
}
