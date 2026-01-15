<?php

namespace app\validate;

use app\attribute\validation\{Required, Email, Length, Pattern};

/**
 * 用户验证器
 * 结合 ThinkPHP 风格和 PHP 8 Attributes
 */
class UserValidate extends BaseValidate
{
    /**
     * 验证规则（传统方式）
     */
    protected array $rule = [
        'username' => 'require|length:3,20|alphaNum',
        'email'    => 'require|email',
        'password' => 'require|length:6,32',
        'phone'    => 'mobile',
        'age'      => 'number|between:1,120',
    ];
    
    /**
     * 错误提示信息
     */
    protected array $message = [
        'username.require'   => '用户名不能为空',
        'username.length'    => '用户名长度必须在 3-20 个字符之间',
        'username.alphaNum'  => '用户名只能是字母和数字',
        'email.require'      => '邮箱不能为空',
        'email.email'        => '邮箱格式不正确',
        'password.require'   => '密码不能为空',
        'password.length'    => '密码长度必须在 6-32 个字符之间',
        'phone.mobile'       => '手机号格式不正确',
        'age.number'         => '年龄必须是数字',
        'age.between'        => '年龄必须在 1-120 之间',
    ];
    
    /**
     * 验证场景
     */
    protected array $scene = [
        'register' => ['username', 'email', 'password', 'phone'],
        'login'    => ['email', 'password'],
        'update'   => ['username', 'email', 'phone'],
    ];
    
    /**
     * 自定义验证规则：验证用户名是否唯一
     */
    protected function checkUnique($value, $rule, $data = [])
    {
        // 模拟数据库查询
        // $exists = Db::table('users')->where('username', $value)->find();
        // return $exists ? '用户名已存在' : true;
        
        return true;
    }
}
