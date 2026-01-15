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
    
    #[Length(min: 0, max: 200)]
    public ?string $bio = null;
    
    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->username = $data['username'] ?? '';
        $dto->email = $data['email'] ?? '';
        $dto->password = $data['password'] ?? '';
        $dto->phone = $data['phone'] ?? null;
        $dto->bio = $data['bio'] ?? null;
        
        return $dto;
    }
}
