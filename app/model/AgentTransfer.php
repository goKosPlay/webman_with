<?php

namespace app\model;

/**
 * AgentTransfer 模型
 */
class AgentTransfer extends BaseModel
{
    protected string $table = 're_agent_transfers';
    
    protected array $fillable = ['agent_id', 'from_branch_id', 'to_branch_id', 'transfer_date', 'transfer_type', 'performance_rule', 'performance_cutoff_date', 'transfer_reason', 'approver_id', 'status', 'special_remark'];
    
    protected array $casts = [
        'id' => 'int',
        'agent_id' => 'int',
        'from_branch_id' => 'int',
        'to_branch_id' => 'int',
        'approver_id' => 'int',
        'transfer_date' => 'date',
        'performance_cutoff_date' => 'date',
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
