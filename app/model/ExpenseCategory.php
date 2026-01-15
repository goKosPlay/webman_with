<?php

namespace app\model;

/**
 * ExpenseCategory 模型
 */
class ExpenseCategory extends BaseModel
{
    protected string $table = 're_expense_categories';
    
    protected array $fillable = ['category_name', 'is_cost', 'remark'];
    
    protected array $casts = [
        'id' => 'int',
        'is_cost' => 'bool',
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
