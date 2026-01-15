<?php

namespace app\validate;

/**
 * 订单验证器
 */
class OrderValidate extends BaseValidate
{
    protected array $rule = [
        'user_id'      => 'require|number',
        'items'        => 'require|array',
        'total_amount' => 'require|number|min:0.01',
        'status'       => 'require|in:pending,paid,shipped,completed,cancelled',
        'note'         => 'length:0,500',
        'payment_method' => 'in:alipay,wechat,card',
    ];
    
    protected array $message = [
        'user_id.require'      => '用户ID不能为空',
        'user_id.number'       => '用户ID必须是数字',
        'items.require'        => '订单商品不能为空',
        'items.array'          => '订单商品格式不正确',
        'total_amount.require' => '订单金额不能为空',
        'total_amount.number'  => '订单金额必须是数字',
        'total_amount.min'     => '订单金额必须大于 0',
        'status.require'       => '订单状态不能为空',
        'status.in'            => '订单状态值不正确',
        'note.length'          => '备注不能超过 500 个字符',
        'payment_method.in'    => '支付方式不正确',
    ];
    
    protected array $scene = [
        'create' => ['user_id', 'items', 'total_amount', 'payment_method'],
        'update' => ['status', 'note'],
    ];
}
