<?php

namespace app\model;

/**
 * Commission 模型
 */
class Commission extends BaseModel
{
    protected string $table = 're_commissions';
    
    protected array $fillable = ['transaction_id', 'total_commission', 'buyer_commission', 'seller_commission', 'status'];
    
    protected array $casts = [
        'id' => 'int',
        'transaction_id' => 'int',
        'total_commission' => 'decimal',
        'buyer_commission' => 'decimal',
        'seller_commission' => 'decimal',
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
