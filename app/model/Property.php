<?php

namespace app\model;

/**
 * 房源/物件主檔模型
 */
class Property extends BaseModel
{
    protected string $table = 're_properties';
    
    protected array $fillable = [
        'property_code',
        'property_type',
        'transaction_type',
        'title',
        'address',
        'city',
        'district',
        'latitude',
        'longitude',
        'coord_source',
        'floor',
        'land_area',
        'building_area',
        'room',
        'living',
        'bath',
        'age',
        'current_price',
        'price_per_ping',
        'status',
        'owner_id',
        'agent_id',
        'branch_id',
        'remark',
        'tags'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'owner_id' => 'int',
        'agent_id' => 'int',
        'branch_id' => 'int',
        'latitude' => 'decimal',
        'longitude' => 'decimal',
        'land_area' => 'decimal',
        'building_area' => 'decimal',
        'room' => 'int',
        'living' => 'int',
        'bath' => 'int',
        'age' => 'decimal',
        'current_price' => 'decimal',
        'price_per_ping' => 'decimal',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据物件编号查找
     */
    public function findByCode(string $propertyCode)
    {
        return $this->where('property_code', $propertyCode)->first();
    }
    
    /**
     * 获取待售房源
     */
    public function getForSaleProperties()
    {
        return $this->where('status', '待售')->where('transaction_type', '出售')->get();
    }
    
    /**
     * 获取待租房源
     */
    public function getForRentProperties()
    {
        return $this->where('status', '待售')->where('transaction_type', '出租')->get();
    }
    
    /**
     * 根据城市和区域获取房源
     */
    public function getPropertiesByLocation(string $city, ?string $district = null)
    {
        $query = $this->where('city', $city);
        
        if ($district) {
            $query->where('district', $district);
        }
        
        return $query->where('status', '待售')->get();
    }
    
    /**
     * 根据价格范围获取房源
     */
    public function getPropertiesByPriceRange($minPrice, $maxPrice)
    {
        return $this->where('current_price', '>=', $minPrice)
            ->where('current_price', '<=', $maxPrice)
            ->where('status', '待售')
            ->get();
    }
    
    /**
     * 获取房源详细信息（包含经纪人、分店、房东信息）
     */
    public function getPropertyDetails($propertyId)
    {
        return $this->query()
            ->leftJoin('re_agents', 're_properties.agent_id', '=', 're_agents.id')
            ->leftJoin('re_branches', 're_properties.branch_id', '=', 're_branches.id')
            ->leftJoin('re_clients as owners', 're_properties.owner_id', '=', 'owners.id')
            ->where('re_properties.id', $propertyId)
            ->select(
                're_properties.*',
                're_agents.name as agent_name',
                're_agents.phone as agent_phone',
                're_branches.branch_name',
                're_branches.phone as branch_phone',
                'owners.name as owner_name',
                'owners.phone as owner_phone'
            )
            ->first();
    }
    
    /**
     * 搜索房源
     */
    public function searchProperties(array $filters)
    {
        $query = $this->query();
        
        if (isset($filters['property_type'])) {
            $query->where('property_type', $filters['property_type']);
        }
        
        if (isset($filters['transaction_type'])) {
            $query->where('transaction_type', $filters['transaction_type']);
        }
        
        if (isset($filters['city'])) {
            $query->where('city', $filters['city']);
        }
        
        if (isset($filters['district'])) {
            $query->where('district', $filters['district']);
        }
        
        if (isset($filters['min_price'])) {
            $query->where('current_price', '>=', $filters['min_price']);
        }
        
        if (isset($filters['max_price'])) {
            $query->where('current_price', '<=', $filters['max_price']);
        }
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        } else {
            $query->where('status', '待售');
        }
        
        return $query->get();
    }
}
