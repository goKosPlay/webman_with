<?php

namespace app\model;

/**
 * 经纪人/业务人员模型
 */
class Agent extends BaseModel
{
    protected string $table = 're_agents';
    
    protected array $fillable = [
        'agent_code',
        'name',
        'phone',
        'line_id',
        'email',
        'current_branch_id',
        'position',
        'status',
        'performance_total',
        'level_code',
        'join_date'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'current_branch_id' => 'int',
        'performance_total' => 'decimal',
        'join_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据业务编号查找
     */
    public function findByCode(string $agentCode)
    {
        return $this->where('agent_code', $agentCode)->first();
    }
    
    /**
     * 获取在职经纪人
     */
    public function getActiveAgents()
    {
        return $this->where('status', '在職')->get();
    }
    
    /**
     * 根据分店获取经纪人
     */
    public function getAgentsByBranch($branchId)
    {
        return $this->where('current_branch_id', $branchId)->where('status', '在職')->get();
    }
    
    /**
     * 获取经纪人及其分店信息
     */
    public function getAgentWithBranch($agentId)
    {
        return $this->query()
            ->leftJoin('re_branches', 're_agents.current_branch_id', '=', 're_branches.id')
            ->where('re_agents.id', $agentId)
            ->select('re_agents.*', 're_branches.branch_name', 're_branches.city', 're_branches.district')
            ->first();
    }
    
    /**
     * 根据手机号查找经纪人
     */
    public function findByPhone(string $phone)
    {
        return $this->where('phone', $phone)->first();
    }
    
    /**
     * 获取业绩排行榜
     */
    public function getPerformanceRanking($limit = 10)
    {
        return $this->query()
            ->where('status', '在職')
            ->orderBy('performance_total', 'desc')
            ->limit($limit)
            ->get();
    }
}
