<?php

namespace app\model;

/**
 * PropertyTransaction 模型
 */
class PropertyTransaction extends BaseModel
{
    protected string $table = 're_property_transactions';
    
    protected array $fillable = ['serial_number', 'district', 'transaction_type', 'address', 'land_area_sqm', 'zoning_use', 'non_urban_use', 'non_urban_denomination', 'transaction_date', 'floors', 'building_type', 'main_use', 'main_material', 'completion_date', 'building_area_sqm', 'rooms', 'living_rooms', 'bathrooms', 'total_price_ntd', 'unit_price_sqm', 'parking_type', 'parking_area_sqm', 'parking_price_ntd', 'remark', 'main_building_area', 'balcony_area', 'has_elevator'];
    
    protected array $casts = [
        'id' => 'int',
        'land_area_sqm' => 'decimal',
        'building_area_sqm' => 'decimal',
        'rooms' => 'int',
        'living_rooms' => 'int',
        'bathrooms' => 'int',
        'total_price_ntd' => 'decimal',
        'unit_price_sqm' => 'decimal',
        'parking_area_sqm' => 'decimal',
        'parking_price_ntd' => 'decimal',
        'main_building_area' => 'decimal',
        'balcony_area' => 'decimal',
        'has_elevator' => 'bool',
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
