<?php

namespace app\model;

/**
 * 新闻分类模型
 */
class NewsCategory extends BaseModel
{
    protected string $table = 're_news_categories';
    
    protected array $fillable = [
        'category_name',
        'category_code',
        'parent_id',
        'sort_order',
        'is_active',
        'description',
        'icon'
    ];
    
    protected array $casts = [
        'id' => 'int',
        'parent_id' => 'int',
        'sort_order' => 'int',
        'is_active' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * 根据分类代码查找
     */
    public function findByCode(string $categoryCode)
    {
        return $this->where('category_code', $categoryCode)->first();
    }
    
    /**
     * 获取活跃分类
     */
    public function getActiveCategories()
    {
        return $this->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->orderBy('category_name', 'asc')
            ->get();
    }
    
    /**
     * 获取顶级分类
     */
    public function getTopLevelCategories()
    {
        return $this->where('parent_id', 0)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
    
    /**
     * 获取子分类
     */
    public function getChildCategories($parentId)
    {
        return $this->where('parent_id', $parentId)
            ->where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();
    }
    
    /**
     * 获取分类树结构
     */
    public function getCategoryTree()
    {
        $categories = $this->getActiveCategories();
        $tree = [];
        
        foreach ($categories as $category) {
            if ($category->parent_id == 0) {
                $tree[$category->id] = [
                    'category' => $category,
                    'children' => []
                ];
            }
        }
        
        foreach ($categories as $category) {
            if ($category->parent_id > 0 && isset($tree[$category->parent_id])) {
                $tree[$category->parent_id]['children'][] = $category;
            }
        }
        
        return array_values($tree);
    }
    
    /**
     * 获取分类及其新闻数量
     */
    public function getCategoriesWithNewsCount()
    {
        return $this->query()
            ->leftJoin('re_news', 're_news_categories.id', '=', 're_news.category_id')
            ->where('re_news_categories.is_active', true)
            ->where('re_news.status', '已發布')
            ->where('re_news.is_published', true)
            ->select(
                're_news_categories.*',
                \DB::raw('COUNT(re_news.id) as news_count')
            )
            ->groupBy('re_news_categories.id')
            ->orderBy('re_news_categories.sort_order', 'asc')
            ->get();
    }
}
