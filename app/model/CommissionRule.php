<?php

namespace app\model;

/**
 * CommissionRule 模型
 */
class CommissionRule extends BaseModel
{
    protected string $table = 're_commission_rules';
    
    protected array $fillable = ['rule_name', 'min_amount', 'max_amount', 'total_rate', 'buyer_rate', 'seller_rate', 'company_rate', 'agent_rate', 'is_active'];
    
    protected array $casts = [
        'id' => 'int',
        'min_amount' => 'decimal',
        'max_amount' => 'decimal',
        'total_rate' => 'decimal',
        'buyer_rate' => 'decimal',
        'seller_rate' => 'decimal',
        'company_rate' => 'decimal',
        'agent_rate' => 'decimal',
        'is_active' => 'bool',
        'created_at' => 'datetime'
    ];
    
    /**
     * 根据ID查找记录
     */
    public function findById($id)
    {
        return $this->find($id);
    }
    
    /**
     * 获取所有记录
     */
    public function getAll()
    {
        return $this->all();
    }
    
    /**
     * 创建记录
     */
    public function createRecord(array $data)
    {
        return $this->create($data);
    }
    
    /**
     * 更新记录
     */
    public function updateRecord($id, array $data)
    {
        return $this->update($id, $data);
    }
    
    /**
     * 删除记录
     */
    public function deleteRecord($id)
    {
        return $this->delete($id);
    }
}
