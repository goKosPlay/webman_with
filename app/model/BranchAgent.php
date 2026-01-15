<?php

namespace app\model;

/**
 * BranchAgent 模型
 */
class BranchAgent extends BaseModel
{
    protected string $table = 're_branch_agents';
    
    protected array $fillable = ['branch_id', 'agent_id', 'role', 'is_primary', 'join_date', 'leave_date'];
    
    protected array $casts = [
        'id' => 'int',
        'branch_id' => 'int',
        'agent_id' => 'int',
        'is_primary' => 'bool',
        'join_date' => 'date',
        'leave_date' => 'date',
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
