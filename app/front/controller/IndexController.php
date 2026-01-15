<?php

namespace app\front\controller;

use app\attribute\dependency\RestController;
use app\attribute\routing\{GetMapping, PostMapping};
use app\attribute\dependency\Autowired;
use app\service\NewsService;
use support\Request;

#[RestController(prefix: '/api/v1/front')]
class IndexController
{
    #[Autowired]
    private NewsService $newsService;
    
    #[GetMapping('/news/categories')]
    public function getNewsCategories(Request $request)
    {
        try {
            $categories = $this->newsService->getNewsCategories();
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $categories
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取新闻分类失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/news/categories/tree')]
    public function getCategoryTree(Request $request)
    {
        try {
            $tree = $this->newsService->getCategoryTree();
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $tree
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取分类树失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/news')]
    public function getNewsList(Request $request)
    {
        try {
            $params = [
                'category_id' => $request->get('category_id'),
                'is_hot' => $request->get('is_hot'),
                'is_top' => $request->get('is_top'),
                'limit' => $request->get('limit', 20)
            ];
            
            // 过滤空值
            $params = array_filter($params, function($value) {
                return $value !== null && $value !== '';
            });
            
            $news = $this->newsService->getNewsList($params);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $news
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取新闻列表失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[GetMapping('/news/{id}')]
    public function getNewsDetail(Request $request, $id)
    {
        try {
            $news = $this->newsService->getNewsDetail($id);
            
            if (!$news) {
                return json([
                    'code' => 404,
                    'msg' => '新闻不存在'
                ], 404);
            }
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $news
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '获取新闻详情失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    #[PostMapping('/news/search')]
    public function searchNews(Request $request)
    {
        try {
            $searchParams = [
                'keyword' => $request->post('keyword'),
                'category_id' => $request->post('category_id'),
                'tags' => $request->post('tags'),
                'is_hot' => $request->post('is_hot'),
                'is_top' => $request->post('is_top')
            ];
            
            // 过滤空值
            $searchParams = array_filter($searchParams, function($value) {
                return $value !== null && $value !== '';
            });
            
            $news = $this->newsService->searchNews($searchParams);
            
            return json([
                'code' => 0,
                'msg' => 'success',
                'data' => $news
            ]);
            
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'msg' => '搜索新闻失败',
                'error' => $e->getMessage()
            ], 500);
        }
    }

}
