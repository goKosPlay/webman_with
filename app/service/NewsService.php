<?php

namespace app\service;

use app\attribute\dependency\Service;
use app\model\{News, NewsCategory};
use support\Log;

#[Service]
class NewsService
{
    /**
     * 获取新闻列表
     */
    public function getNewsList(array $params = [])
    {
        try {
            $news = new News();
            
            if (isset($params['category_id'])) {
                return $news->getNewsByCategory($params['category_id']);
            }
            
            if (isset($params['is_hot']) && $params['is_hot']) {
                return $news->getHotNews($params['limit'] ?? 10);
            }
            
            if (isset($params['is_top']) && $params['is_top']) {
                return $news->getTopNews($params['limit'] ?? 5);
            }
            
            return $news->getPublishedNews();
            
        } catch (\Exception $e) {
            Log::error('获取新闻列表失败', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取新闻详情
     */
    public function getNewsDetail($newsId)
    {
        try {
            $news = new News();
            $detail = $news->getNewsDetails($newsId);
            
            if ($detail) {
                // 增加浏览次数
                $news->incrementViews($newsId);
            }
            
            return $detail;
            
        } catch (\Exception $e) {
            Log::error('获取新闻详情失败', ['news_id' => $newsId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 搜索新闻
     */
    public function searchNews(array $searchParams)
    {
        try {
            $news = new News();
            return $news->searchNews($searchParams)->get();
            
        } catch (\Exception $e) {
            Log::error('搜索新闻失败', ['params' => $searchParams, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取新闻分类
     */
    public function getNewsCategories()
    {
        try {
            $category = new NewsCategory();
            return $category->getActiveCategories();
            
        } catch (\Exception $e) {
            Log::error('获取新闻分类失败', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 获取分类树
     */
    public function getCategoryTree()
    {
        try {
            $category = new NewsCategory();
            return $category->getCategoryTree();
            
        } catch (\Exception $e) {
            Log::error('获取分类树失败', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 创建新闻
     */
    public function createNews(array $data)
    {
        try {
            $news = new News();
            
            // 设置默认值
            $data['views_count'] = 0;
            $data['is_published'] = $data['is_published'] ?? false;
            $data['is_top'] = $data['is_top'] ?? false;
            $data['is_hot'] = $data['is_hot'] ?? false;
            $data['status'] = $data['status'] ?? '草稿';
            
            if ($data['is_published'] && $data['status'] === '草稿') {
                $data['status'] = '已發布';
                $data['publish_time'] = date('Y-m-d H:i:s');
            }
            
            return $news->create($data);
            
        } catch (\Exception $e) {
            Log::error('创建新闻失败', ['data' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 更新新闻
     */
    public function updateNews($newsId, array $data)
    {
        try {
            $news = new News();
            return $news->update($newsId, $data);
            
        } catch (\Exception $e) {
            Log::error('更新新闻失败', ['news_id' => $newsId, 'data' => $data, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 发布新闻
     */
    public function publishNews($newsId)
    {
        try {
            $news = new News();
            $result = $news->publish($newsId);
            
            Log::info('新闻发布成功', ['news_id' => $newsId]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('发布新闻失败', ['news_id' => $newsId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
    
    /**
     * 下架新闻
     */
    public function unpublishNews($newsId)
    {
        try {
            $news = new News();
            $result = $news->unpublish($newsId);
            
            Log::info('新闻下架成功', ['news_id' => $newsId]);
            
            return $result;
            
        } catch (\Exception $e) {
            Log::error('下架新闻失败', ['news_id' => $newsId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}