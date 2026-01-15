<?php

namespace app\model;

/**
 * 分店/门市模型
 */
class Branch extends BaseModel
{
    protected string $table = 're_branches';
    
    protected array $fillable = [
        'branch_code',
        'branch_name',
        'branch_type',
        'status',
        'city',
        'district',
        'address',
        'phone',
        'manager_id',
        'open_date',
        'monthly_rent',
        'remark'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'manager_id' => 'int',
        'open_date' => 'date',
        'monthly_rent' => 'decimal',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据分店代码查找
     */
    public function findByCode(string $branchCode)
    {
        return $this->where('branch_code', $branchCode)->first();
    }
    
    /**
     * 获取营业中的分店
     */
    public function getActiveBranches()
    {
        return $this->where('status', '營業中')->get();
    }
    
    /**
     * 根据城市获取分店
     */
    public function getBranchesByCity(string $city)
    {
        return $this->where('city', $city)->where('status', '營業中')->get();
    }
    
    /**
     * 获取分店及其经理信息
     */
    public function getBranchWithManager($branchId)
    {
        return $this->query()
            ->leftJoin('re_agents', 're_branches.manager_id', '=', 're_agents.id')
            ->where('re_branches.id', $branchId)
            ->select('re_branches.*', 're_agents.name as manager_name', 're_agents.phone as manager_phone')
            ->first();
    }
    
    /**
     * 获取所有分店及其经理信息
     */
    public function getAllBranchesWithManagers()
    {
        return $this->query()
            ->leftJoin('re_agents', 're_branches.manager_id', '=', 're_agents.id')
            ->select('re_branches.*', 're_agents.name as manager_name', 're_agents.phone as manager_phone')
            ->orderBy('re_branches.branch_name')
            ->get();
    }
}
