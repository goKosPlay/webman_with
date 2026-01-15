/*
 Navicat Premium Dump SQL

 Source Server         : 本機專案
 Source Server Type    : MySQL
 Source Server Version : 80300 (8.3.0)
 Source Host           : localhost:3306
 Source Schema         : rent_house

 Target Server Type    : MySQL
 Target Server Version : 80300 (8.3.0)
 File Encoding         : 65001

 Date: 15/01/2026 04:23:01
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for re_admin_users
-- ----------------------------
DROP TABLE IF EXISTS `re_admin_users`;
CREATE TABLE `re_admin_users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `real_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `status` enum('正常','禁用','鎖定') COLLATE utf8mb4_unicode_ci DEFAULT '正常',
  `last_login_time` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `phone` (`phone`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='後台管理員帳號表';

-- ----------------------------
-- Records of re_admin_users
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_agent_transfers
-- ----------------------------
DROP TABLE IF EXISTS `re_agent_transfers`;
CREATE TABLE `re_agent_transfers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `from_branch_id` bigint unsigned DEFAULT NULL COMMENT '原分店',
  `to_branch_id` bigint unsigned NOT NULL,
  `transfer_date` date NOT NULL COMMENT '調店生效日期（業績切割點）',
  `transfer_type` enum('正式調店','臨時支援','跨區協助','晉升調店','其他') COLLATE utf8mb4_unicode_ci DEFAULT '正式調店',
  `performance_rule` enum('成交日期切割','調店日期切割','全跟人走','自定義') COLLATE utf8mb4_unicode_ci DEFAULT '成交日期切割' COMMENT '業績歸屬規則',
  `performance_cutoff_date` date DEFAULT NULL COMMENT '業績切割日期（可手動調整尾單保護）',
  `transfer_reason` text COLLATE utf8mb4_unicode_ci COMMENT '調店原因',
  `approver_id` bigint unsigned DEFAULT NULL COMMENT '審批人',
  `status` enum('待審批','已生效','已取消','已撤銷') COLLATE utf8mb4_unicode_ci DEFAULT '已生效',
  `special_remark` text COLLATE utf8mb4_unicode_ci COMMENT '業績特殊約定（如尾單歸屬比例）',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='經紀人調店 / 轉店歷史記錄表（含業績歸屬規則）';

-- ----------------------------
-- Records of re_agent_transfers
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_agents
-- ----------------------------
DROP TABLE IF EXISTS `re_agents`;
CREATE TABLE `re_agents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '業務編號 e.g. A001',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `line_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_branch_id` bigint unsigned DEFAULT NULL COMMENT '當前所屬分店（冗余，快速查詢）',
  `position` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT '業務',
  `status` enum('在職','離職','休假') COLLATE utf8mb4_unicode_ci DEFAULT '在職',
  `performance_total` decimal(16,2) DEFAULT '0.00' COMMENT '個人累計產生佣金總額（不受調店影響）',
  `level_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '當前等級（如 A6、S級）',
  `join_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `agent_code` (`agent_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='經紀人員 / 業務人員表';

-- ----------------------------
-- Records of re_agents
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_branch_agents
-- ----------------------------
DROP TABLE IF EXISTS `re_branch_agents`;
CREATE TABLE `re_branch_agents` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_id` bigint unsigned NOT NULL,
  `agent_id` bigint unsigned NOT NULL,
  `role` enum('店長','副店長','業務','行政') COLLATE utf8mb4_unicode_ci DEFAULT '業務',
  `is_primary` tinyint(1) DEFAULT '1',
  `join_date` date DEFAULT NULL,
  `leave_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_branch_agent` (`branch_id`,`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分店與經紀人關聯表（一人可屬多店）';

-- ----------------------------
-- Records of re_branch_agents
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_branches
-- ----------------------------
DROP TABLE IF EXISTS `re_branches`;
CREATE TABLE `re_branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `branch_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `branch_type` enum('總公司','區域總部','直營','加盟','其他') COLLATE utf8mb4_unicode_ci DEFAULT '直營',
  `status` enum('營業中','籌備中','關閉') COLLATE utf8mb4_unicode_ci DEFAULT '營業中',
  `city` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `manager_id` bigint unsigned DEFAULT NULL COMMENT '店長（對應 re_agents.id）',
  `open_date` date DEFAULT NULL,
  `monthly_rent` decimal(10,0) DEFAULT '0',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `branch_code` (`branch_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='分店 / 門市表';

-- ----------------------------
-- Records of re_branches
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_clients
-- ----------------------------
DROP TABLE IF EXISTS `re_clients`;
CREATE TABLE `re_clients` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `client_type` enum('房東','買方','租方','買+租','其他') COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `identity_card` char(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '身分證',
  `birthday` date DEFAULT NULL,
  `source` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '客戶來源',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_phone` (`phone`),
  KEY `idx_type` (`client_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客戶表（房東、購屋者、租屋者等）';

-- ----------------------------
-- Records of re_clients
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_commission_rules
-- ----------------------------
DROP TABLE IF EXISTS `re_commission_rules`;
CREATE TABLE `re_commission_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `min_amount` decimal(14,2) DEFAULT '0.00',
  `max_amount` decimal(14,2) DEFAULT '9999999999.99',
  `total_rate` decimal(5,3) NOT NULL,
  `buyer_rate` decimal(5,3) DEFAULT '0.000',
  `seller_rate` decimal(5,3) DEFAULT '0.000',
  `company_rate` decimal(5,3) DEFAULT '0.400',
  `agent_rate` decimal(5,3) DEFAULT '0.500',
  `is_active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='佣金抽成規則表';

-- ----------------------------
-- Records of re_commission_rules
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_commission_splits
-- ----------------------------
DROP TABLE IF EXISTS `re_commission_splits`;
CREATE TABLE `re_commission_splits` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `commission_id` bigint unsigned NOT NULL,
  `recipient_type` enum('業務','店長','公司','協力仲介') COLLATE utf8mb4_unicode_ci NOT NULL,
  `recipient_id` bigint unsigned DEFAULT NULL COMMENT 'agent_id 或其他',
  `split_amount` decimal(12,2) NOT NULL,
  `payout_status` enum('待發','已發') COLLATE utf8mb4_unicode_ci DEFAULT '待發',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='佣金內部分成明細表';

-- ----------------------------
-- Records of re_commission_splits
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_commissions
-- ----------------------------
DROP TABLE IF EXISTS `re_commissions`;
CREATE TABLE `re_commissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `transaction_id` bigint unsigned NOT NULL,
  `total_commission` decimal(12,2) NOT NULL,
  `buyer_commission` decimal(12,2) DEFAULT '0.00',
  `seller_commission` decimal(12,2) DEFAULT '0.00',
  `status` enum('待確認','已確認','已發放') COLLATE utf8mb4_unicode_ci DEFAULT '待確認',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成交佣金總表';

-- ----------------------------
-- Records of re_commissions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_employee_leave_balances
-- ----------------------------
DROP TABLE IF EXISTS `re_employee_leave_balances`;
CREATE TABLE `re_employee_leave_balances` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `leave_type_id` int unsigned NOT NULL,
  `year` int NOT NULL,
  `entitled_days` decimal(5,1) DEFAULT '0.0',
  `used_days` decimal(5,1) DEFAULT '0.0',
  `remaining_days` decimal(5,1) GENERATED ALWAYS AS ((`entitled_days` - `used_days`)) STORED,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_agent_type_year` (`agent_id`,`leave_type_id`,`year`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='經紀人假期餘額表';

-- ----------------------------
-- Records of re_employee_leave_balances
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_expense_categories
-- ----------------------------
DROP TABLE IF EXISTS `re_expense_categories`;
CREATE TABLE `re_expense_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_cost` tinyint(1) DEFAULT '1',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='費用科目分類表';

-- ----------------------------
-- Records of re_expense_categories
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_expenses
-- ----------------------------
DROP TABLE IF EXISTS `re_expenses`;
CREATE TABLE `re_expenses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `expense_date` date NOT NULL,
  `status` enum('待審','已審','已付') COLLATE utf8mb4_unicode_ci DEFAULT '待審',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='費用 / 支出明細表';

-- ----------------------------
-- Records of re_expenses
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_lands
-- ----------------------------
DROP TABLE IF EXISTS `re_lands`;
CREATE TABLE `re_lands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '編號',
  `land_position` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '土地位置',
  `land_area_sqm` decimal(12,2) DEFAULT NULL COMMENT '土地移轉面積平方公尺',
  `land_use_zoning` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '使用分區或編定',
  `transfer_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '移轉情形',
  `parcel_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地號',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_serial` (`serial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='土地資訊表 (a_lvr_land_c.xls 土地部分)';

-- ----------------------------
-- Records of re_lands
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_leave_requests
-- ----------------------------
DROP TABLE IF EXISTS `re_leave_requests`;
CREATE TABLE `re_leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `agent_id` bigint unsigned NOT NULL,
  `leave_type_id` int unsigned NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `days` decimal(4,1) NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('待審批','已批准','已拒絕','已銷假') COLLATE utf8mb4_unicode_ci DEFAULT '待審批',
  `approver_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='請假申請表';

-- ----------------------------
-- Records of re_leave_requests
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_leave_types
-- ----------------------------
DROP TABLE IF EXISTS `re_leave_types`;
CREATE TABLE `re_leave_types` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_paid` tinyint(1) DEFAULT '1',
  `default_days` decimal(4,1) DEFAULT '0.0',
  `max_days_per_year` decimal(5,1) DEFAULT '0.0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_code` (`type_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='請假類型 / 假期種類表';

-- ----------------------------
-- Records of re_leave_types
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_news
-- ----------------------------
DROP TABLE IF EXISTS `re_news`;
CREATE TABLE `re_news` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subtitle` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `category_id` bigint unsigned NOT NULL,
  `author_id` bigint unsigned DEFAULT NULL,
  `author_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `cover_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `images` text COLLATE utf8mb4_unicode_ci,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `views_count` int unsigned DEFAULT '0',
  `is_top` tinyint unsigned DEFAULT '0',
  `is_hot` tinyint(1) DEFAULT '0',
  `is_published` tinyint(1) DEFAULT '0',
  `publish_time` datetime DEFAULT NULL,
  `status` enum('草稿','已發布','下架','刪除') COLLATE utf8mb4_unicode_ci DEFAULT '草稿',
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='新聞 / 資訊主表';

-- ----------------------------
-- Records of re_news
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_news_categories
-- ----------------------------
DROP TABLE IF EXISTS `re_news_categories`;
CREATE TABLE `re_news_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT '0',
  `sort_order` int DEFAULT '0',
  `is_active` tinyint(1) DEFAULT '1',
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_code` (`category_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='新聞類別 / 欄目表';

-- ----------------------------
-- Records of re_news_categories
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_parkings
-- ----------------------------
DROP TABLE IF EXISTS `re_parkings`;
CREATE TABLE `re_parkings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '編號 (關聯主表)',
  `parking_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '車位類別',
  `parking_price_ntd` decimal(12,0) DEFAULT NULL COMMENT '車位價格',
  `parking_area_sqm` decimal(10,2) DEFAULT NULL COMMENT '車位面積平方公尺',
  `parking_floor` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '車位所在樓層',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_serial` (`serial_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='車位資訊表 (三個檔案的車位欄位)';

-- ----------------------------
-- Records of re_parkings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_performance_summary
-- ----------------------------
DROP TABLE IF EXISTS `re_performance_summary`;
CREATE TABLE `re_performance_summary` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `period_year` int NOT NULL,
  `period_month` tinyint NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = 全公司',
  `agent_id` bigint unsigned DEFAULT NULL COMMENT 'NULL = 門店或公司',
  `sales_count` int unsigned DEFAULT '0',
  `rental_count` int unsigned DEFAULT '0',
  `total_transactions` int unsigned GENERATED ALWAYS AS ((`sales_count` + `rental_count`)) STORED,
  `sales_amount` decimal(16,2) DEFAULT '0.00',
  `rental_amount` decimal(14,2) DEFAULT '0.00',
  `commission_income` decimal(14,2) DEFAULT '0.00',
  `agent_split_amount` decimal(14,2) DEFAULT '0.00',
  `branch_income` decimal(14,2) DEFAULT '0.00',
  `commission_target` decimal(12,2) DEFAULT '0.00',
  `achievement_rate` decimal(6,3) GENERATED ALWAYS AS ((case when (`commission_target` > 0) then (`commission_income` / `commission_target`) else 0 end)) STORED,
  `attribution_type` enum('個人累計','門店當前','歷史歸屬') COLLATE utf8mb4_unicode_ci DEFAULT '門店當前' COMMENT '匯總類型',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_period` (`period_year`,`period_month`,`branch_id`,`agent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='業績匯總表（月度/人員/分店）';

-- ----------------------------
-- Records of re_performance_summary
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_permissions
-- ----------------------------
DROP TABLE IF EXISTS `re_permissions`;
CREATE TABLE `re_permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `perm_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perm_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `perm_type` enum('menu','button','api','data') COLLATE utf8mb4_unicode_ci NOT NULL,
  `parent_id` bigint unsigned DEFAULT '0',
  `path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `component` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `icon` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `status` enum('啟用','禁用') COLLATE utf8mb4_unicode_ci DEFAULT '啟用',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `perm_code` (`perm_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='權限資源表（RBAC）';

-- ----------------------------
-- Records of re_permissions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_presale_transactions
-- ----------------------------
DROP TABLE IF EXISTS `re_presale_transactions`;
CREATE TABLE `re_presale_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '編號',
  `district` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '鄉鎮市區',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '土地位置建物門牌',
  `land_area_sqm` decimal(12,2) DEFAULT '0.00',
  `total_price_ntd` decimal(15,0) DEFAULT NULL,
  `unit_price_sqm` decimal(12,0) DEFAULT NULL,
  `parking_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `parking_area_sqm` decimal(10,2) DEFAULT '0.00',
  `parking_price_ntd` decimal(12,0) DEFAULT NULL,
  `project_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '建案名稱',
  `building_unit` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '棟及號',
  `termination_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '解約情形',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `transaction_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_serial` (`serial_number`),
  KEY `idx_project_name` (`project_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='預售屋買賣實價登錄表 (a_lvr_land_b.xls)';

-- ----------------------------
-- Records of re_presale_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_properties
-- ----------------------------
DROP TABLE IF EXISTS `re_properties`;
CREATE TABLE `re_properties` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '物件編號 e.g. SO202601001',
  `property_type` enum('土地','透天','公寓','大樓','套房','店面','廠房','其他') COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_type` enum('出售','出租','售租皆可') COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `city` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `district` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `latitude` decimal(10,7) DEFAULT NULL COMMENT '緯度',
  `longitude` decimal(10,7) DEFAULT NULL COMMENT '經度',
  `coord_source` enum('手動','Google','政府','估計') COLLATE utf8mb4_unicode_ci DEFAULT '估計',
  `floor` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `land_area` decimal(10,2) DEFAULT NULL,
  `building_area` decimal(10,2) DEFAULT NULL,
  `room` tinyint unsigned DEFAULT '0',
  `living` tinyint unsigned DEFAULT '0',
  `bath` tinyint unsigned DEFAULT '0',
  `age` decimal(4,1) DEFAULT NULL,
  `current_price` decimal(14,0) DEFAULT NULL,
  `price_per_ping` decimal(12,0) DEFAULT NULL,
  `status` enum('待售','洽談中','成交','已租','下架') COLLATE utf8mb4_unicode_ci DEFAULT '待售',
  `owner_id` bigint unsigned DEFAULT NULL COMMENT '房東 client id',
  `agent_id` bigint unsigned DEFAULT NULL COMMENT '負責業務',
  `branch_id` bigint unsigned DEFAULT NULL COMMENT '所屬分店',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `tags` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `property_code` (`property_code`),
  KEY `idx_status` (`status`),
  KEY `idx_city_district` (`city`,`district`),
  KEY `idx_coord` (`latitude`,`longitude`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='房源 / 物件主檔表';

-- ----------------------------
-- Records of re_properties
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_property_keys
-- ----------------------------
DROP TABLE IF EXISTS `re_property_keys`;
CREATE TABLE `re_property_keys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `key_type` enum('原配鑰匙','備用鑰匙','智能鎖密碼','門禁卡','信箱鑰匙','其他') COLLATE utf8mb4_unicode_ci NOT NULL,
  `key_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '鑰匙編號/密碼',
  `key_count` tinyint unsigned DEFAULT '1',
  `key_location` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '存放位置',
  `status` enum('在店可用','已借出','丟失','作廢','維修中','其他') COLLATE utf8mb4_unicode_ci DEFAULT '在店可用',
  `borrow_agent_id` bigint unsigned DEFAULT NULL COMMENT '當前借出人',
  `borrow_time` datetime DEFAULT NULL COMMENT '借出時間',
  `return_time` datetime DEFAULT NULL COMMENT '歸還時間',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='房源鑰匙主信息表';

-- ----------------------------
-- Records of re_property_keys
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_property_transactions
-- ----------------------------
DROP TABLE IF EXISTS `re_property_transactions`;
CREATE TABLE `re_property_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `serial_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '編號 (e.g. RPROMLRKKIKGFAA77DA)',
  `district` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '鄉鎮市區',
  `transaction_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '交易標的 (房地(土地+建物)+車位、車位、土地等)',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '土地位置建物門牌',
  `land_area_sqm` decimal(12,2) DEFAULT '0.00' COMMENT '土地移轉總面積平方公尺',
  `zoning_use` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '都市土地使用分區',
  `non_urban_use` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '非都市土地使用分區',
  `non_urban_denomination` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '非都市土地使用編定',
  `transaction_date` date NOT NULL COMMENT '交易年月日 (格式如 1141206 → 2025-12-06)',
  `floors` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '移轉層次 / 總樓層數',
  `building_type` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '建物型態',
  `main_use` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '主要用途',
  `main_material` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '主要建材',
  `completion_date` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '建築完成年月',
  `building_area_sqm` decimal(12,2) DEFAULT '0.00' COMMENT '建物移轉總面積平方公尺',
  `rooms` tinyint unsigned DEFAULT '0' COMMENT '建物現況格局-房',
  `living_rooms` tinyint unsigned DEFAULT '0' COMMENT '建物現況格局-廳',
  `bathrooms` tinyint unsigned DEFAULT '0' COMMENT '建物現況格局-衛',
  `total_price_ntd` decimal(15,0) DEFAULT NULL COMMENT '總價元',
  `unit_price_sqm` decimal(12,0) DEFAULT NULL COMMENT '單價元平方公尺',
  `parking_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '車位類別',
  `parking_area_sqm` decimal(10,2) DEFAULT '0.00' COMMENT '車位移轉總面積平方公尺',
  `parking_price_ntd` decimal(12,0) DEFAULT NULL COMMENT '車位總價元',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '備註',
  `main_building_area` decimal(10,2) DEFAULT NULL COMMENT '主建物面積',
  `balcony_area` decimal(10,2) DEFAULT NULL COMMENT '陽台面積',
  `has_elevator` tinyint(1) DEFAULT '0' COMMENT '電梯',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_serial` (`serial_number`),
  KEY `idx_district` (`district`),
  KEY `idx_transaction_date` (`transaction_date`),
  KEY `idx_total_price` (`total_price_ntd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='不動產買賣實價登錄主表 (a_lvr_land_a.xls)';

-- ----------------------------
-- Records of re_property_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_rental_listings
-- ----------------------------
DROP TABLE IF EXISTS `re_rental_listings`;
CREATE TABLE `re_rental_listings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `monthly_rent` decimal(9,0) NOT NULL,
  `management_fee` decimal(7,0) DEFAULT '0',
  `deposit_months` tinyint DEFAULT '2',
  `status` enum('有效','成交','下架') COLLATE utf8mb4_unicode_ci DEFAULT '有效',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='出租委託表';

-- ----------------------------
-- Records of re_rental_listings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_roles
-- ----------------------------
DROP TABLE IF EXISTS `re_roles`;
CREATE TABLE `re_roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_system` tinyint(1) DEFAULT '0',
  `status` enum('啟用','禁用') COLLATE utf8mb4_unicode_ci DEFAULT '啟用',
  `sort_order` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_code` (`role_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='角色表（RBAC）';

-- ----------------------------
-- Records of re_roles
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_sales_listings
-- ----------------------------
DROP TABLE IF EXISTS `re_sales_listings`;
CREATE TABLE `re_sales_listings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `price` decimal(12,0) NOT NULL,
  `price_per_ping` decimal(10,0) DEFAULT NULL,
  `status` enum('有效','成交','下架') COLLATE utf8mb4_unicode_ci DEFAULT '有效',
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `agent_id` bigint unsigned DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='出售委託 / 售價調整歷史表';

-- ----------------------------
-- Records of re_sales_listings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_transaction_corrections
-- ----------------------------
DROP TABLE IF EXISTS `re_transaction_corrections`;
CREATE TABLE `re_transaction_corrections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `original_transaction_id` bigint unsigned NOT NULL COMMENT '原始成交 re_transactions.id',
  `correction_type` enum('金额更正','业务员更正','门店归属更正','日期更正','佣金比例更正','其他') COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '更正类型',
  `original_final_price` decimal(12,0) DEFAULT NULL COMMENT '原成交总价',
  `original_agent_id` bigint unsigned DEFAULT NULL COMMENT '原业务员',
  `original_branch_id` bigint unsigned DEFAULT NULL COMMENT '原归属门店',
  `original_closing_date` date DEFAULT NULL COMMENT '原成交日期',
  `corrected_final_price` decimal(12,0) DEFAULT NULL COMMENT '更正后总价',
  `corrected_agent_id` bigint unsigned DEFAULT NULL COMMENT '更正后业务员',
  `corrected_branch_id` bigint unsigned DEFAULT NULL COMMENT '更正后归属门店',
  `corrected_closing_date` date DEFAULT NULL COMMENT '更正后成交日期',
  `corrected_commission_rate` decimal(5,3) DEFAULT NULL COMMENT '更正后佣金比例',
  `correction_reason` text COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '更正原因（必填）',
  `attachment_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '证明附件（如合同修改、客户确认书）',
  `status` enum('待审批','已批准','已拒绝','已冲正') COLLATE utf8mb4_unicode_ci DEFAULT '待审批' COMMENT '更正状态',
  `approver_id` bigint unsigned DEFAULT NULL COMMENT '审批人 re_admin_users.id 或 re_agents.id',
  `approve_time` datetime DEFAULT NULL,
  `approver_remark` text COLLATE utf8mb4_unicode_ci COMMENT '审批意见',
  `created_by` bigint unsigned NOT NULL COMMENT '申请人',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_original_tx` (`original_transaction_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成交更正 / 冲正记录表（红冲蓝补）';

-- ----------------------------
-- Records of re_transaction_corrections
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_transactions
-- ----------------------------
DROP TABLE IF EXISTS `re_transactions`;
CREATE TABLE `re_transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `transaction_type` enum('買賣','出租') COLLATE utf8mb4_unicode_ci NOT NULL,
  `client_id` bigint unsigned DEFAULT NULL COMMENT '買方/承租方',
  `owner_id` bigint unsigned DEFAULT NULL COMMENT '賣方/房東',
  `agent_id` bigint unsigned DEFAULT NULL COMMENT '主責業務',
  `final_price` decimal(12,0) NOT NULL,
  `closing_date` date NOT NULL,
  `commission_amount` decimal(10,0) DEFAULT NULL,
  `attributed_branch_id` bigint unsigned DEFAULT NULL COMMENT '該筆成交最終歸屬門店ID（根據調店規則計算）',
  `attribution_calculated_at` datetime DEFAULT NULL COMMENT '歸屬計算時間',
  `remark` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='成交紀錄表（買賣與租賃）';

-- ----------------------------
-- Records of re_transactions
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- Table structure for re_viewings
-- ----------------------------
DROP TABLE IF EXISTS `re_viewings`;
CREATE TABLE `re_viewings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `property_id` bigint unsigned NOT NULL,
  `client_id` bigint unsigned NOT NULL,
  `agent_id` bigint unsigned NOT NULL,
  `viewing_date` datetime NOT NULL,
  `status` enum('已約','已看','取消') COLLATE utf8mb4_unicode_ci DEFAULT '已約',
  `feedback` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='帶看 / 看房紀錄表';

-- ----------------------------
-- Records of re_viewings
-- ----------------------------
BEGIN;
COMMIT;

-- ----------------------------
-- View structure for re_view_agent_monthly_rank
-- ----------------------------
DROP VIEW IF EXISTS `re_view_agent_monthly_rank`;
CREATE ALGORITHM = UNDEFINED SQL SECURITY DEFINER VIEW `re_view_agent_monthly_rank` AS select `a`.`name` AS `agent_name`,`b`.`branch_name` AS `branch_name`,`p`.`period_year` AS `period_year`,`p`.`period_month` AS `period_month`,`p`.`total_transactions` AS `total_transactions`,`p`.`commission_income` AS `commission_income`,`p`.`agent_split_amount` AS `agent_split_amount`,rank() OVER (PARTITION BY `p`.`period_year`,`p`.`period_month` ORDER BY `p`.`commission_income` desc )  AS `rank_no` from ((`re_performance_summary` `p` join `re_agents` `a` on((`p`.`agent_id` = `a`.`agent_id`))) join `re_branches` `b` on((`p`.`branch_id` = `b`.`branch_id`))) where (`p`.`agent_id` is not null);

SET FOREIGN_KEY_CHECKS = 1;
