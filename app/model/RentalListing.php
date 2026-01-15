<?php

namespace app\model;

/**
 * RentalListing 模型
 */
class RentalListing extends BaseModel
{
    protected string $table = 're_rental_listings';
    
    protected array $fillable = ['property_id', 'monthly_rent', 'management_fee', 'deposit_months', 'status', 'start_date', 'end_date', 'agent_id'];
    
    protected array $casts = [
        'id' => 'int',
        'property_id' => 'int',
        'agent_id' => 'int',
        'monthly_rent' => 'decimal',
        'management_fee' => 'decimal',
        'deposit_months' => 'int',
        'start_date' => 'date',
        'end_date' => 'date',
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
