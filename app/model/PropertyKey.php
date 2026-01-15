<?php

namespace app\model;

/**
 * PropertyKey 模型
 */
class PropertyKey extends BaseModel
{
    protected string $table = 're_property_keys';
    
    protected array $fillable = ['property_id', 'key_type', 'key_code', 'key_count', 'key_location', 'status', 'borrow_agent_id', 'borrow_time', 'return_time', 'remark'];
    
    protected array $casts = [
        'id' => 'int',
        'property_id' => 'int',
        'borrow_agent_id' => 'int',
        'key_count' => 'int',
        'borrow_time' => 'datetime',
        'return_time' => 'datetime',
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
