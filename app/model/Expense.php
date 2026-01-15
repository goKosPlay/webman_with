<?php

namespace app\model;

/**
 * Expense 模型
 */
class Expense extends BaseModel
{
    protected string $table = 're_expenses';
    
    protected array $fillable = ['category_id', 'branch_id', 'agent_id', 'amount', 'expense_date', 'status', 'remark'];
    
    protected array $casts = [
        'id' => 'int',
        'category_id' => 'int',
        'branch_id' => 'int',
        'agent_id' => 'int',
        'amount' => 'decimal',
        'expense_date' => 'date',
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
