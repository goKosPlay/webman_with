<?php

namespace app\model;

/**
 * 后台管理员用户模型
 */
class AdminUser extends BaseModel
{
    protected string $table = 're_admin_users';
    
    protected array $fillable = [
        'username',
        'password_hash',
        'real_name',
        'phone',
        'email',
        'branch_id',
        'status',
        'last_login_time'
    ];
    
    protected array $hidden = [
        'password_hash'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'branch_id' => 'int',
        'last_login_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据用户名查找用户
     */
    public function findByUsername(string $username)
    {
        return $this->where('username', $username)->first();
    }
    
    /**
     * 根据手机号查找用户
     */
    public function findByPhone(string $phone)
    {
        return $this->where('phone', $phone)->first();
    }
    
    /**
     * 根据邮箱查找用户
     */
    public function findByEmail(string $email)
    {
        return $this->where('email', $email)->first();
    }
    
    /**
     * 更新最后登录时间
     */
    public function updateLastLogin($id)
    {
        return $this->update($id, ['last_login_time' => date('Y-m-d H:i:s')]);
    }
    
    /**
     * 获取活跃用户
     */
    public function getActiveUsers()
    {
        return $this->where('status', '正常')->get();
    }
}
