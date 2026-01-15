<?php

namespace app\model;

/**
 * 成交记录模型
 */
class Transaction extends BaseModel
{
    protected string $table = 're_transactions';
    
    protected array $fillable = [
        'property_id',
        'transaction_type',
        'client_id',
        'owner_id',
        'agent_id',
        'final_price',
        'closing_date',
        'commission_amount',
        'attributed_branch_id',
        'attribution_calculated_at',
        'remark'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'property_id' => 'int',
        'client_id' => 'int',
        'owner_id' => 'int',
        'agent_id' => 'int',
        'attributed_branch_id' => 'int',
        'final_price' => 'decimal',
        'commission_amount' => 'decimal',
        'closing_date' => 'date',
        'attribution_calculated_at' => 'datetime',
        'created_at' => 'datetime'
    ];
    
    /**
     * 获取成交详情（包含房源、客户、经纪人信息）
     */
    public function getTransactionDetails($transactionId)
    {
        return $this->query()
            ->leftJoin('re_properties', 're_transactions.property_id', '=', 're_properties.id')
            ->leftJoin('re_clients as clients', 're_transactions.client_id', '=', 'clients.id')
            ->leftJoin('re_clients as owners', 're_transactions.owner_id', '=', 'owners.id')
            ->leftJoin('re_agents', 're_transactions.agent_id', '=', 're_agents.id')
            ->leftJoin('re_branches', 're_transactions.attributed_branch_id', '=', 're_branches.id')
            ->where('re_transactions.id', $transactionId)
            ->select(
                're_transactions.*',
                're_properties.title as property_title',
                're_properties.address as property_address',
                're_properties.property_type',
                'clients.name as client_name',
                'clients.phone as client_phone',
                'owners.name as owner_name',
                'owners.phone as owner_phone',
                're_agents.name as agent_name',
                're_agents.agent_code',
                're_branches.branch_name'
            )
            ->first();
    }
    
    /**
     * 根据经纪人获取成交记录
     */
    public function getTransactionsByAgent($agentId, $limit = null)
    {
        $query = $this->where('agent_id', $agentId)->orderBy('closing_date', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * 根据分店获取成交记录
     */
    public function getTransactionsByBranch($branchId, $limit = null)
    {
        $query = $this->where('attributed_branch_id', $branchId)->orderBy('closing_date', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * 根据日期范围获取成交记录
     */
    public function getTransactionsByDateRange($startDate, $endDate)
    {
        return $this->whereBetween('closing_date', [$startDate, $endDate])
            ->orderBy('closing_date', 'desc')
            ->get();
    }
    
    /**
     * 获取买卖成交记录
     */
    public function getSalesTransactions()
    {
        return $this->where('transaction_type', '買賣')->orderBy('closing_date', 'desc')->get();
    }
    
    /**
     * 获取租赁成交记录
     */
    public function getRentalTransactions()
    {
        return $this->where('transaction_type', '出租')->orderBy('closing_date', 'desc')->get();
    }
    
    /**
     * 获取月度成交统计
     */
    public function getMonthlyStatistics($year, $month)
    {
        return $this->query()
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN transaction_type = "買賣" THEN 1 ELSE 0 END) as sales_count,
                SUM(CASE WHEN transaction_type = "出租" THEN 1 ELSE 0 END) as rental_count,
                SUM(final_price) as total_amount,
                SUM(commission_amount) as total_commission
            ')
            ->whereYear('closing_date', $year)
            ->whereMonth('closing_date', $month)
            ->first();
    }
    
    /**
     * 获取年度成交统计
     */
    public function getYearlyStatistics($year)
    {
        return $this->query()
            ->selectRaw('
                COUNT(*) as total_count,
                SUM(CASE WHEN transaction_type = "買賣" THEN 1 ELSE 0 END) as sales_count,
                SUM(CASE WHEN transaction_type = "出租" THEN 1 ELSE 0 END) as rental_count,
                SUM(final_price) as total_amount,
                SUM(commission_amount) as total_commission,
                AVG(final_price) as avg_price
            ')
            ->whereYear('closing_date', $year)
            ->first();
    }
    
    /**
     * 获取经纪人业绩排名
     */
    public function getAgentPerformanceRanking($year, $month, $limit = 10)
    {
        return $this->query()
            ->leftJoin('re_agents', 're_transactions.agent_id', '=', 're_agents.id')
            ->selectRaw('
                re_agents.id,
                re_agents.name,
                re_agents.agent_code,
                COUNT(*) as transaction_count,
                SUM(final_price) as total_amount,
                SUM(commission_amount) as total_commission,
                AVG(final_price) as avg_price
            ')
            ->whereYear('closing_date', $year)
            ->whereMonth('closing_date', $month)
            ->groupBy('re_agents.id', 're_agents.name', 're_agents.agent_code')
            ->orderBy('total_amount', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * 获取分店业绩排名
     */
    public function getBranchPerformanceRanking($year, $month, $limit = 10)
    {
        return $this->query()
            ->leftJoin('re_branches', 're_transactions.attributed_branch_id', '=', 're_branches.id')
            ->selectRaw('
                re_branches.id,
                re_branches.branch_name,
                re_branches.city,
                COUNT(*) as transaction_count,
                SUM(final_price) as total_amount,
                SUM(commission_amount) as total_commission,
                AVG(final_price) as avg_price
            ')
            ->whereYear('closing_date', $year)
            ->whereMonth('closing_date', $month)
            ->groupBy('re_branches.id', 're_branches.branch_name', 're_branches.city')
            ->orderBy('total_amount', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * 获取价格区间分析
     */
    public function getPriceRangeAnalysis($year, $month)
    {
        return $this->query()
            ->selectRaw('
                CASE 
                    WHEN final_price < 5000000 THEN "500万以下"
                    WHEN final_price < 10000000 THEN "500-1000万"
                    WHEN final_price < 20000000 THEN "1000-2000万"
                    WHEN final_price < 30000000 THEN "2000-3000万"
                    ELSE "3000万以上"
                END as price_range,
                COUNT(*) as count,
                SUM(final_price) as total_amount,
                AVG(final_price) as avg_price
            ')
            ->whereYear('closing_date', $year)
            ->whereMonth('closing_date', $month)
            ->groupBy('price_range')
            ->orderBy('total_amount', 'desc')
            ->get();
    }
    
    /**
     * 获取区域成交分析
     */
    public function getAreaAnalysis($year, $month, $limit = 20)
    {
        return $this->query()
            ->leftJoin('re_properties', 're_transactions.property_id', '=', 're_properties.id')
            ->selectRaw('
                re_properties.city,
                re_properties.district,
                COUNT(*) as transaction_count,
                SUM(final_price) as total_amount,
                AVG(final_price) as avg_price,
                AVG(final_price / re_properties.building_area) as avg_price_per_ping
            ')
            ->whereYear('closing_date', $year)
            ->whereMonth('closing_date', $month)
            ->groupBy('re_properties.city', 're_properties.district')
            ->orderBy('transaction_count', 'desc')
            ->limit($limit)
            ->get();
    }
}
