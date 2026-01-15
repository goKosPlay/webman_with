<?php

namespace app\model;

/**
 * EmployeeLeaveBalance 模型
 */
class EmployeeLeaveBalance extends BaseModel
{
    protected string $table = 're_employee_leave_balances';
    
    protected array $fillable = ['agent_id', 'leave_type_id', 'year', 'entitled_days', 'used_days'];
    
    protected array $casts = [
        'id' => 'int',
        'agent_id' => 'int',
        'leave_type_id' => 'int',
        'year' => 'int',
        'entitled_days' => 'decimal',
        'used_days' => 'decimal',
        'remaining_days' => 'decimal'
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
