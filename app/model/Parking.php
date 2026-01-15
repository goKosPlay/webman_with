<?php

namespace app\model;

/**
 * Parking 模型
 */
class Parking extends BaseModel
{
    protected string $table = 're_parkings';
    
    protected array $fillable = ['serial_number', 'parking_type', 'parking_price_ntd', 'parking_area_sqm', 'parking_floor'];
    
    protected array $casts = [
        'id' => 'int',
        'parking_price_ntd' => 'decimal',
        'parking_area_sqm' => 'decimal',
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
