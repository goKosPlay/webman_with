<?php

namespace app\model;

/**
 * PerformanceSummary 模型
 */
class PerformanceSummary extends BaseModel
{
    protected string $table = 're_performance_summary';
    
    protected array $fillable = ['period_year', 'period_month', 'branch_id', 'agent_id', 'sales_count', 'rental_count', 'sales_amount', 'rental_amount', 'commission_income', 'agent_split_amount', 'branch_income', 'commission_target', 'attribution_type'];
    
    protected array $casts = [
        'id' => 'int',
        'period_year' => 'int',
        'period_month' => 'int',
        'branch_id' => 'int',
        'agent_id' => 'int',
        'sales_count' => 'int',
        'rental_count' => 'int',
        'total_transactions' => 'int',
        'sales_amount' => 'decimal',
        'rental_amount' => 'decimal',
        'commission_income' => 'decimal',
        'agent_split_amount' => 'decimal',
        'branch_income' => 'decimal',
        'commission_target' => 'decimal',
        'achievement_rate' => 'decimal',
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
