-- 为“客户注册审核”功能升级：给 customers 表增加 status 字段
-- 已有 customers 表的站点在 phpMyAdmin 中执行本文件
-- 执行后：已有客户会设为已通过，新注册客户为待审核，需在后台“客户管理”中通过

ALTER TABLE customers ADD COLUMN status ENUM('pending','approved') NOT NULL DEFAULT 'approved' COMMENT 'pending=待审核 approved=已通过' AFTER address;
-- 上面 DEFAULT 'approved' 表示现有客户视为已通过；若希望现有客户也需审核，可改为 DEFAULT 'pending' 后单独执行一次：
-- UPDATE customers SET status = 'approved' WHERE 1=1;
