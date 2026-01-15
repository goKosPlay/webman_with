<?php

namespace app\model;

/**
 * LeaveRequest 模型
 */
class LeaveRequest extends BaseModel
{
    protected string $table = 're_leave_requests';
    
    protected array $fillable = ['agent_id', 'leave_type_id', 'start_date', 'end_date', 'days', 'reason', 'status', 'approver_id', 'branch_id'];
    
    protected array $casts = [
        'id' => 'int',
        'agent_id' => 'int',
        'leave_type_id' => 'int',
        'days' => 'decimal',
        'approver_id' => 'int',
        'branch_id' => 'int',
        'start_date' => 'date',
        'end_date' => 'date',
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
