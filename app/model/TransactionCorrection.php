<?php

namespace app\model;

/**
 * TransactionCorrection 模型
 */
class TransactionCorrection extends BaseModel
{
    protected string $table = 're_transaction_corrections';
    
    protected array $fillable = ['original_transaction_id', 'correction_type', 'original_final_price', 'original_agent_id', 'original_branch_id', 'original_closing_date', 'corrected_final_price', 'corrected_agent_id', 'corrected_branch_id', 'corrected_closing_date', 'corrected_commission_rate', 'correction_reason', 'attachment_url', 'status', 'approver_id', 'approve_time', 'approver_remark', 'created_by'];
    
    protected array $casts = [
        'id' => 'int',
        'original_transaction_id' => 'int',
        'original_agent_id' => 'int',
        'original_branch_id' => 'int',
        'corrected_agent_id' => 'int',
        'corrected_branch_id' => 'int',
        'original_final_price' => 'decimal',
        'corrected_final_price' => 'decimal',
        'corrected_commission_rate' => 'decimal',
        'approver_id' => 'int',
        'created_by' => 'int',
        'approve_time' => 'datetime',
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
