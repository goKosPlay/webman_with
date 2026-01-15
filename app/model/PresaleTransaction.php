<?php

namespace app\model;

/**
 * PresaleTransaction 模型
 */
class PresaleTransaction extends BaseModel
{
    protected string $table = 're_presale_transactions';
    
    protected array $fillable = ['serial_number', 'district', 'address', 'land_area_sqm', 'total_price_ntd', 'unit_price_sqm', 'parking_type', 'parking_area_sqm', 'parking_price_ntd', 'project_name', 'building_unit', 'termination_type', 'remark', 'transaction_date'];
    
    protected array $casts = [
        'id' => 'int',
        'land_area_sqm' => 'decimal',
        'total_price_ntd' => 'decimal',
        'unit_price_sqm' => 'decimal',
        'parking_area_sqm' => 'decimal',
        'parking_price_ntd' => 'decimal',
        'transaction_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
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
