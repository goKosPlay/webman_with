<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\model\{Transaction, Agent, Branch, Property};
use support\Log;

#[Service]
class TransactionService
{
    /**
     * 获取成交详情
     */
    public function getTransactionDetail($transactionId)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getTransactionDetails($transactionId);
        } catch (\Exception $e) {
            Log::error('获取成交详情失败', ['transaction_id' => $transactionId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取经纪人成交记录
     */
    public function getAgentTransactions($agentId, $limit = null)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getTransactionsByAgent($agentId, $limit);
        } catch (\Exception $e) {
            Log::error('获取经纪人成交记录失败', ['agent_id' => $agentId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取分店成交记录
     */
    public function getBranchTransactions($branchId, $limit = null)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getTransactionsByBranch($branchId, $limit);
        } catch (\Exception $e) {
            Log::error('获取分店成交记录失败', ['branch_id' => $branchId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取月度统计
     */
    public function getMonthlyStatistics($year, $month)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getMonthlyStatistics($year, $month);
        } catch (\Exception $e) {
            Log::error('获取月度统计失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取年度统计
     */
    public function getYearlyStatistics($year)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getYearlyStatistics($year);
        } catch (\Exception $e) {
            Log::error('获取年度统计失败', ['year' => $year, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取经纪人业绩排名
     */
    public function getAgentPerformanceRanking($year, $month, $limit = 10)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getAgentPerformanceRanking($year, $month, $limit);
        } catch (\Exception $e) {
            Log::error('获取经纪人业绩排名失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取分店业绩排名
     */
    public function getBranchPerformanceRanking($year, $month, $limit = 10)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getBranchPerformanceRanking($year, $month, $limit);
        } catch (\Exception $e) {
            Log::error('获取分店业绩排名失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取价格区间分析
     */
    public function getPriceRangeAnalysis($year, $month)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getPriceRangeAnalysis($year, $month);
        } catch (\Exception $e) {
            Log::error('获取价格区间分析失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取区域成交分析
     */
    public function getAreaAnalysis($year, $month, $limit = 20)
    {
        try {
            $transaction = new Transaction();
            return $transaction->getAreaAnalysis($year, $month, $limit);
        } catch (\Exception $e) {
            Log::error('获取区域成交分析失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 创建成交记录
     */
    public function createTransaction(array $data)
    {
        try {
            $transaction = new Transaction();
            
            // 验证必要字段
            if (!isset($data['property_id']) || !isset($data['agent_id']) || !isset($data['final_price'])) {
                throw new \InvalidArgumentException('缺少必要字段：property_id, agent_id, final_price');
            }
            
            // 设置默认值
            $data['created_at'] = date('Y-m-d H:i:s');
            
            return $transaction->create($data);
        } catch (\Exception $e) {
            Log::error('创建成交记录失败', ['data' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取综合业绩报表
     */
    public function getPerformanceReport($year, $month)
    {
        try {
            $transaction = new Transaction();
            
            return [
                'monthly_stats' => $transaction->getMonthlyStatistics($year, $month),
                'yearly_stats' => $transaction->getYearlyStatistics($year),
                'agent_ranking' => $transaction->getAgentPerformanceRanking($year, $month, 10),
                'branch_ranking' => $transaction->getBranchPerformanceRanking($year, $month, 10),
                'price_analysis' => $transaction->getPriceRangeAnalysis($year, $month),
                'area_analysis' => $transaction->getAreaAnalysis($year, $month, 15)
            ];
        } catch (\Exception $e) {
            Log::error('获取综合业绩报表失败', ['year' => $year, 'month' => $month, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
