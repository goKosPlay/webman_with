<?php

namespace app\model;

/**
 * Permission 模型
 */
class Permission extends BaseModel
{
    protected string $table = 're_permissions';
    
    protected array $fillable = ['perm_code', 'perm_name', 'perm_type', 'parent_id', 'path', 'component', 'icon', 'sort_order', 'status'];
    
    protected array $casts = [
        'id' => 'int',
        'parent_id' => 'int',
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
