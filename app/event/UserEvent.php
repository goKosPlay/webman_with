<?php

namespace app\event;

class UserEvent
{
    public static function created(array $userData): array
    {
        return [
            'action' => 'created',
            'user_id' => $userData['id'] ?? null,
            'email' => $userData['email'] ?? null,
            'name' => $userData['name'] ?? null,
            'operator_id' => $userData['operator_id'] ?? null,
            'ip' => request()?->getRealIp() ?? null,
            'user_agent' => request()?->header('user-agent') ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function updated(int $userId, array $changes, ?int $operatorId = null): array
    {
        return [
            'action' => 'updated',
            'user_id' => $userId,
            'changes' => $changes,
            'operator_id' => $operatorId,
            'ip' => request()?->getRealIp() ?? null,
            'user_agent' => request()?->header('user-agent') ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function deleted(int $userId, ?int $operatorId = null): array
    {
        return [
            'action' => 'deleted',
            'user_id' => $userId,
            'operator_id' => $operatorId,
            'ip' => request()?->getRealIp() ?? null,
            'user_agent' => request()?->header('user-agent') ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function login(int $userId, bool $success = true, ?string $reason = null): array
    {
        return [
            'action' => 'login',
            'user_id' => $userId,
            'success' => $success,
            'reason' => $reason,
            'ip' => request()?->getRealIp() ?? null,
            'user_agent' => request()?->header('user-agent') ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function logout(int $userId): array
    {
        return [
            'action' => 'logout',
            'user_id' => $userId,
            'ip' => request()?->getRealIp() ?? null,
            'user_agent' => request()?->header('user-agent') ?? null,
            'timestamp' => time(),
        ];
    }
    
    public static function passwordReset(string $email, string $token): array
    {
        return [
            'action' => 'password.reset',
            'email' => $email,
            'token' => $token,
            'ip' => request()?->getRealIp() ?? null,
            'timestamp' => time(),
        ];
    }
}
