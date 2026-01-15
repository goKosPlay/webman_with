<?php

namespace app\model;

/**
 * Role 模型
 */
class Role extends BaseModel
{
    protected string $table = 're_roles';
    
    protected array $fillable = ['role_code', 'role_name', 'description', 'is_system', 'status', 'sort_order'];
    
    protected array $casts = [
        'id' => 'int',
        'is_system' => 'bool',
        'sort_order' => 'int',
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
