<?php

namespace app\model;

/**
 * Viewing 模型
 */
class Viewing extends BaseModel
{
    protected string $table = 're_viewings';
    
    protected array $fillable = ['property_id', 'client_id', 'agent_id', 'viewing_date', 'status', 'feedback'];
    
    protected array $casts = [
        'id' => 'int',
        'property_id' => 'int',
        'client_id' => 'int',
        'agent_id' => 'int',
        'viewing_date' => 'datetime',
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
