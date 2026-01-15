<?php

namespace app\model;

/**
 * 新闻/资讯模型
 */
class News extends BaseModel
{
    protected string $table = 're_news';
    
    protected array $fillable = [
        'title',
        'subtitle',
        'category_id',
        'author_id',
        'author_name',
        'content',
        'summary',
        'cover_image',
        'images',
        'tags',
        'views_count',
        'is_top',
        'is_hot',
        'is_published',
        'publish_time',
        'status',
        'source',
        'source_url',
        'remark'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'category_id' => 'int',
        'author_id' => 'int',
        'views_count' => 'int',
        'is_top' => 'bool',
        'is_hot' => 'bool',
        'is_published' => 'bool',
        'publish_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'images' => 'json'
    ];
    
    /**
     * 获取已发布的新闻
     */
    public function getPublishedNews()
    {
        return $this->where('status', '已發布')
            ->where('is_published', true)
            ->orderBy('is_top', 'desc')
            ->orderBy('publish_time', 'desc')
            ->get();
    }
    
    /**
     * 获取热门新闻
     */
    public function getHotNews($limit = 10)
    {
        return $this->where('status', '已發布')
            ->where('is_published', true)
            ->where('is_hot', true)
            ->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * 获取置顶新闻
     */
    public function getTopNews($limit = 5)
    {
        return $this->where('status', '已發布')
            ->where('is_published', true)
            ->where('is_top', true)
            ->orderBy('publish_time', 'desc')
            ->limit($limit)
            ->get();
    }
    
    /**
     * 根据分类获取新闻
     */
    public function getNewsByCategory($categoryId, $limit = null)
    {
        $query = $this->where('category_id', $categoryId)
            ->where('status', '已發布')
            ->where('is_published', true)
            ->orderBy('publish_time', 'desc');
        
        if ($limit) {
            $query->limit($limit);
        }
        
        return $query->get();
    }
    
    /**
     * 获取新闻详情（包含分类信息）
     */
    public function getNewsDetails($newsId)
    {
        return $this->query()
            ->leftJoin('re_news_categories', 're_news.category_id', '=', 're_news_categories.id')
            ->where('re_news.id', $newsId)
            ->select(
                're_news.*',
                're_news_categories.category_name',
                're_news_categories.category_code'
            )
            ->first();
    }
    
    /**
     * 搜索新闻
     */
    public function searchNews(array $filters)
    {
        $query = $this->query();
        
        // 只搜索已发布的新闻
        $query->where('status', '已發布')->where('is_published', true);
        
        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }
        
        if (isset($filters['keyword'])) {
            $keyword = $filters['keyword'];
            $query->where(function($q) use ($keyword) {
                $q->where('title', 'like', '%' . $keyword . '%')
                  ->orWhere('content', 'like', '%' . $keyword . '%')
                  ->orWhere('summary', 'like', '%' . $keyword . '%');
            });
        }
        
        if (isset($filters['tags'])) {
            $tags = is_array($filters['tags']) ? $filters['tags'] : [$filters['tags']];
            foreach ($tags as $tag) {
                $query->where('tags', 'like', '%' . $tag . '%');
            }
        }
        
        if (isset($filters['is_hot'])) {
            $query->where('is_hot', $filters['is_hot']);
        }
        
        if (isset($filters['is_top'])) {
            $query->where('is_top', $filters['is_top']);
        }
        
        $query->orderBy('is_top', 'desc')->orderBy('publish_time', 'desc');
        
        return $query;
    }
    
    /**
     * 增加浏览次数
     */
    public function incrementViews($newsId)
    {
        return $this->query()->where('id', $newsId)->increment('views_count');
    }
    
    /**
     * 发布新闻
     */
    public function publish($newsId)
    {
        return $this->update($newsId, [
            'status' => '已發布',
            'is_published' => true,
            'publish_time' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * 下架新闻
     */
    public function unpublish($newsId)
    {
        return $this->update($newsId, [
            'status' => '下架',
            'is_published' => false
        ]);
    }
}
