<?php

namespace app\model;

/**
 * 客户模型
 */
class Client extends BaseModel
{
    protected string $table = 're_clients';
    
    protected array $fillable = [
        'client_type',
        'name',
        'phone',
        'line_id',
        'identity_card',
        'birthday',
        'source',
        'remark'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'birthday' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据手机号查找客户
     */
    public function findByPhone(string $phone)
    {
        return $this->where('phone', $phone)->first();
    }
    
    /**
     * 根据身份证号查找客户
     */
    public function findByIdentityCard(string $identityCard)
    {
        return $this->where('identity_card', $identityCard)->first();
    }
    
    /**
     * 获取房东客户
     */
    public function getLandlords()
    {
        return $this->where('client_type', '房東')->get();
    }
    
    /**
     * 获取买方客户
     */
    public function getBuyers()
    {
        return $this->where('client_type', '買方')->get();
    }
    
    /**
     * 获取租方客户
     */
    public function getTenants()
    {
        return $this->where('client_type', '租方')->get();
    }
    
    /**
     * 根据客户类型获取客户
     */
    public function getClientsByType(string $clientType)
    {
        return $this->where('client_type', $clientType)->get();
    }
    
    /**
     * 根据来源获取客户
     */
    public function getClientsBySource(string $source)
    {
        return $this->where('source', $source)->get();
    }
    
    /**
     * 搜索客户
     */
    public function searchClients(array $filters)
    {
        $query = $this->query();
        
        if (isset($filters['client_type'])) {
            $query->where('client_type', $filters['client_type']);
        }
        
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }
        
        if (isset($filters['phone'])) {
            $query->where('phone', 'like', '%' . $filters['phone'] . '%');
        }
        
        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }
        
        return $query->get();
    }
}
