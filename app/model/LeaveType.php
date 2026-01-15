<?php

namespace app\model;

/**
 * LeaveType 模型
 */
class LeaveType extends BaseModel
{
    protected string $table = 're_leave_types';
    
    protected array $fillable = ['type_code', 'type_name', 'is_paid', 'default_days', 'max_days_per_year'];
    
    protected array $casts = [
        'id' => 'int',
        'is_paid' => 'bool',
        'default_days' => 'decimal',
        'max_days_per_year' => 'decimal'
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
