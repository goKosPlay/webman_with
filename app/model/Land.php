<?php

namespace app\model;

/**
 * Land 模型
 */
class Land extends BaseModel
{
    protected string $table = 're_lands';
    
    protected array $fillable = ['serial_number', 'land_position', 'land_area_sqm', 'land_use_zoning', 'transfer_status', 'parcel_number'];
    
    protected array $casts = [
        'id' => 'int',
        'land_area_sqm' => 'decimal',
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
