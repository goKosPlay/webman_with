<?php

namespace app\model;

use support\Db;

/**
 * 基础模型类
 */
abstract class BaseModel
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    protected array $casts = [];
    
    /**
     * 获取表名
     */
    public function getTable(): string
    {
        return $this->table;
    }
    
    /**
     * 获取主键
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * 查找单条记录
     */
    public function find($id)
    {
        return Db::table($this->table)->where($this->primaryKey, $id)->first();
    }
    
    /**
     * 查找多条记录
     */
    public function findMany(array $ids)
    {
        return Db::table($this->table)->whereIn($this->primaryKey, $ids)->get();
    }
    
    /**
     * 获取所有记录
     */
    public function all()
    {
        return Db::table($this->table)->get();
    }
    
    /**
     * 创建记录
     */
    public function create(array $data)
    {
        $fillableData = $this->filterFillable($data);
        $fillableData['created_at'] = date('Y-m-d H:i:s');
        
        $id = Db::table($this->table)->insertGetId($fillableData);
        
        return $this->find($id);
    }
    
    /**
     * 更新记录
     */
    public function update($id, array $data)
    {
        $fillableData = $this->filterFillable($data);
        $fillableData['updated_at'] = date('Y-m-d H:i:s');
        
        return Db::table($this->table)->where($this->primaryKey, $id)->update($fillableData);
    }
    
    /**
     * 删除记录
     */
    public function delete($id)
    {
        return Db::table($this->table)->where($this->primaryKey, $id)->delete();
    }
    
    /**
     * 查询构建器
     */
    public function query()
    {
        return Db::table($this->table);
    }
    
    /**
     * 根据条件查找
     */
    public function where($column, $operator = null, $value = null)
    {
        return Db::table($this->table)->where($column, $operator, $value);
    }
    
    /**
     * 分页查询
     */
    public function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return Db::table($this->table)->paginate($perPage, $columns, $pageName, $page);
    }
    
    /**
     * 过滤可填充字段
     */
    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
    
    /**
     * 类型转换
     */
    protected function castAttributes(array $attributes): array
    {
        foreach ($this->casts as $key => $type) {
            if (isset($attributes[$key])) {
                switch ($type) {
                    case 'int':
                    case 'integer':
                        $attributes[$key] = (int) $attributes[$key];
                        break;
                    case 'float':
                    case 'double':
                    case 'decimal':
                        $attributes[$key] = (float) $attributes[$key];
                        break;
                    case 'bool':
                    case 'boolean':
                        $attributes[$key] = (bool) $attributes[$key];
                        break;
                    case 'array':
                    case 'json':
                        if (is_string($attributes[$key])) {
                            $attributes[$key] = json_decode($attributes[$key], true);
                        }
                        break;
                    case 'datetime':
                        if (is_string($attributes[$key])) {
                            $attributes[$key] = new \DateTime($attributes[$key]);
                        }
                        break;
                }
            }
        }
        
        return $attributes;
    }
    
    /**
     * 隐藏敏感字段
     */
    protected function hideAttributes(array $attributes): array
    {
        foreach ($this->hidden as $field) {
            unset($attributes[$field]);
        }
        
        return $attributes;
    }
}
