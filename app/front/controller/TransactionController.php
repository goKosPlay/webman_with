<?php

namespace app\front\controller;

use app\attribute\dependency\RestController;
use app\attribute\routing\{GetMapping, PostMapping};
use app\attribute\dependency\Autowired;
use app\service\TransactionService;
use support\Request;

#[RestController(prefix: '/api/v1/front/transactions')]
class TransactionController
{
    #[Autowired]
    private TransactionService $transactionService;
    
    #[GetMapping('/{id}')]
    public function getTransactionDetail(Request $request, $id)
    {
        try {
            $transaction = $this->transactionService->getTransactionDetail($id);
            
            if (!$transaction) {
                return json([
                    'code' => 404,
                    'msg' => '成交记录不存在'
                ], 404);
            }
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $transaction
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取成交详情失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/agent/{agentId}')]
    public function getAgentTransactions(Request $request, $agentId)
    {
        try {
            $limit = $request->get('limit', 20);
            $transactions = $this->transactionService->getAgentTransactions($agentId, $limit);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取经纪人成交记录失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/branch/{branchId}')]
    public function getBranchTransactions(Request $request, $branchId)
    {
        try {
            $limit = $request->get('limit', 20);
            $transactions = $this->transactionService->getBranchTransactions($branchId, $limit);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $transactions
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取分店成交记录失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/statistics/monthly')]
    public function getMonthlyStatistics(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            
            $stats = $this->transactionService->getMonthlyStatistics($year, $month);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取月度统计失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/statistics/yearly')]
    public function getYearlyStatistics(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            
            $stats = $this->transactionService->getYearlyStatistics($year);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $stats
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取年度统计失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/ranking/agents')]
    public function getAgentPerformanceRanking(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            $limit = $request->get('limit', 10);
            
            $ranking = $this->transactionService->getAgentPerformanceRanking($year, $month, $limit);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $ranking
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取经纪人业绩排名失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/ranking/branches')]
    public function getBranchPerformanceRanking(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            $limit = $request->get('limit', 10);
            
            $ranking = $this->transactionService->getBranchPerformanceRanking($year, $month, $limit);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $ranking
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取分店业绩排名失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/analysis/price-range')]
    public function getPriceRangeAnalysis(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            
            $analysis = $this->transactionService->getPriceRangeAnalysis($year, $month);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $analysis
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取价格区间分析失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/analysis/area')]
    public function getAreaAnalysis(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            $limit = $request->get('limit', 20);
            
            $analysis = $this->transactionService->getAreaAnalysis($year, $month, $limit);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $analysis
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取区域成交分析失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/report/performance')]
    public function getPerformanceReport(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $month = $request->get('month', date('m'));
            
            $report = $this->transactionService->getPerformanceReport($year, $month);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $report
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取综合业绩报表失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[PostMapping('/create')]
    public function createTransaction(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要字段
            if (!isset($data['property_id']) || !isset($data['agent_id']) || !isset($data['final_price'])) {
                return json([
                    'code' => 400,
                    'msg' => '缺少必要字段：property_id, agent_id, final_price'
                ], 400);
            }
            
            $transaction = $this->transactionService->createTransaction($data);
            
            return json([
                'code' => 0,
                'msg' => '成交记录创建成功',
                'data' => $transaction
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '创建成交记录失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
