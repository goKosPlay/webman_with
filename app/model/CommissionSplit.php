<?php

namespace app\model;

/**
 * CommissionSplit 模型
 */
class CommissionSplit extends BaseModel
{
    protected string $table = 're_commission_splits';
    
    protected array $fillable = ['commission_id', 'recipient_type', 'recipient_id', 'split_amount', 'payout_status'];
    
    protected array $casts = [
        'id' => 'int',
        'commission_id' => 'int',
        'recipient_id' => 'int',
        'split_amount' => 'decimal',
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
