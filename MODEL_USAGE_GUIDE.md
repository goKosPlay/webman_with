# 租房系统 Model 使用指南

## 概述

基于 `rent_house.sql` 数据库结构，已生成完整的 Model 类体系，包含 28 个模型类，涵盖租房系统的所有核心业务模块。

## Model 类列表

### 核心业务模块

#### 1. 用户管理
- **AdminUser** - 后台管理员用户模型
- **Agent** - 经纪人/业务人员模型  
- **Client** - 客户模型（房东、买方、租方等）

#### 2. 组织架构
- **Branch** - 分店/门市模型
- **BranchAgent** - 分店与经纪人关联表
- **Role** - 角色表（RBAC）
- **Permission** - 权限资源表（RBAC）

#### 3. 房源管理
- **Property** - 房源/物件主檔模型
- **PropertyKey** - 房源钥匙主信息表
- **Parking** - 车位信息表
- **Land** - 土地信息表

#### 4. 交易管理
- **Transaction** - 成交记录表（买卖与租赁）
- **PropertyTransaction** - 不动产买卖实价登录主表
- **PresaleTransaction** - 预售屋买卖实价登录表
- **TransactionCorrection** - 成交更正/冲正记录表

#### 5. 委托管理
- **SalesListing** - 出售委託/售價調整歷史表
- **RentalListing** - 出租委託表
- **Viewing** - 带看/看房记录表

#### 6. 佣金管理
- **Commission** - 成交佣金总表
- **CommissionRule** - 佣金抽成规则表
- **CommissionSplit** - 佣金内部分成明细表

#### 7. 业绩管理
- **PerformanceSummary** - 业绩汇总表（月度/人员/分店）

#### 8. 费用管理
- **Expense** - 费用/支出明细表
- **ExpenseCategory** - 费用科目分类表

#### 9. 人事管理
- **LeaveRequest** - 请假申请表
- **LeaveType** - 请假类型/假期种类表
- **EmployeeLeaveBalance** - 经纪人假期余额表
- **AgentTransfer** - 经纪人调店/转店历史记录表

#### 10. 新闻资讯
- **News** - 新闻/资讯主表
- **NewsCategory** - 新闻类别/栏目表

## 基础使用方法

### 1. 基本 CRUD 操作

```php
use app\model\Agent;

// 创建实例
$agent = new Agent();

// 查找记录
$agent = $agent->find(1);
$agents = $agent->findMany([1, 2, 3]);
$allAgents = $agent->all();

// 创建记录
$data = [
    'agent_code' => 'A001',
    'name' => '张三',
    'phone' => '0912345678',
    'status' => '在職'
];
$newAgent = $agent->create($data);

// 更新记录
$agent->update(1, ['name' => '李四']);

// 删除记录
$agent->delete(1);
```

### 2. 查询构建器

```php
// 基础查询
$agents = $agent->where('status', '在職')->get();

// 复杂查询
$agents = $agent->query()
    ->where('status', '在職')
    ->where('performance_total', '>', 10000)
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// 分页查询
$agents = $agent->paginate(15);
```

### 3. 关联查询示例

```php
// 获取经纪人及其分店信息
$agent = $agent->query()
    ->leftJoin('re_branches', 're_agents.current_branch_id', '=', 're_branches.id')
    ->select('re_agents.*', 're_branches.branch_name')
    ->first();
```

## 具体模型使用示例

### Agent（经纪人）模型

```php
use app\model\Agent;

$agent = new Agent();

// 根据业务编号查找
$agent = $agent->findByCode('A001');

// 获取在职经纪人
$activeAgents = $agent->getActiveAgents();

// 根据分店获取经纪人
$branchAgents = $agent->getAgentsByBranch(1);

// 获取业绩排行榜
$topAgents = $agent->getPerformanceRanking(10);
```

### Property（房源）模型

```php
use app\model\Property;

$property = new Property();

// 根据物件编号查找
$property = $property->findByCode('SO202601001');

// 获取待售房源
$saleProperties = $property->getForSaleProperties();

// 根据城市获取房源
$properties = $property->getPropertiesByLocation('台北市', '大安區');

// 搜索房源
$filters = [
    'property_type' => '大樓',
    'city' => '台北市',
    'min_price' => 10000000,
    'max_price' => 20000000
];
$results = $property->searchProperties($filters);
```

### Transaction（成交）模型

```php
use app\model\Transaction;

$transaction = new Transaction();

// 获取成交详情
$detail = $transaction->getTransactionDetails(1);

// 根据经纪人获取成交记录
$transactions = $transaction->getTransactionsByAgent(1);

// 获取月度统计
$stats = $transaction->getMonthlyStatistics(2024, 1);
```

### News（新闻）模型

```php
use app\model\News;

$news = new News();

// 获取已发布新闻
$publishedNews = $news->getPublishedNews();

// 获取热门新闻
$hotNews = $news->getHotNews(10);

// 搜索新闻
$searchParams = [
    'keyword' => '房市',
    'category_id' => 1,
    'is_hot' => true
];
$results = $news->searchNews($searchParams);
```

## 类型转换

所有模型都支持自动类型转换：

```php
// decimal 类型会自动转换为浮点数
$agent->performance_total; // float(15000.50)

// datetime 类型会转换为 DateTime 对象
$agent->created_at; // DateTime 对象

// bool 类型会转换为布尔值
$news->is_hot; // bool(true)
```

## 数据验证

```php
// 过滤可填充字段，防止批量赋值
$safeData = $agent->filterFillable($requestData);

// 类型转换
$convertedData = $agent->castAttributes($data);

// 隐藏敏感字段
$publicData = $agent->hideAttributes($attributes);
```

## 最佳实践

### 1. 使用 Service 层

```php
use app\service\AgentService;

class AgentController
{
    #[Autowired]
    private AgentService $agentService;
    
    public function getAgent($id)
    {
        return $this->agentService->getAgentById($id);
    }
}
```

### 2. 错误处理

```php
try {
    $agent = new Agent();
    $result = $agent->create($data);
    return json(['code' => 0, 'data' => $result]);
} catch (\Exception $e) {
    Log::error('创建经纪人失败', ['error' => $e->getMessage()]);
    return json(['code' => 500, 'msg' => '创建失败'], 500);
}
```

### 3. 事务处理

```php
use support\Db;

Db::beginTransaction();
try {
    $agent = new Agent();
    $agent->create($agentData);
    
    $branchAgent = new BranchAgent();
    $branchAgent->create($branchAgentData);
    
    Db::commit();
} catch (\Exception $e) {
    Db::rollBack();
    throw $e;
}
```

## 数据库表对应关系

| 表名 | 模型类 | 说明 |
|------|--------|------|
| re_admin_users | AdminUser | 后台管理员 |
| re_agents | Agent | 经纪人 |
| re_branches | Branch | 分店 |
| re_clients | Client | 客户 |
| re_properties | Property | 房源 |
| re_transactions | Transaction | 成交记录 |
| re_news | News | 新闻 |
| ... | ... | ... |

## 注意事项

1. **表名前缀**：所有表名都使用 `re_` 前缀
2. **主键**：大部分表使用 `id` 作为主键
3. **时间戳**：大部分表包含 `created_at` 和 `updated_at` 字段
4. **软删除**：当前模型未实现软删除，如需要可自行扩展
5. **关联关系**：当前使用手动 JOIN 查询，后续可扩展为 ORM 关联

## 扩展建议

1. **添加关联关系**：定义模型间的 belongsTo、hasMany 等关系
2. **添加验证规则**：在模型中定义验证规则
3. **添加事件监听**：在模型操作时触发事件
4. **添加缓存支持**：对频繁查询的数据添加缓存
5. **添加软删除**：实现软删除功能

这套 Model 体系为租房系统提供了完整的数据访问层，支持所有核心业务功能的开发。
