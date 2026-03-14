-- 普通客户 / Agent(批发) 身份功能
-- 在 phpMyAdmin 中选中数据库后执行。若报错“重复列”说明已执行过，可忽略。

ALTER TABLE customers ADD COLUMN role ENUM('customer','agent') NOT NULL DEFAULT 'customer' COMMENT 'customer=普通客户 agent=批发' AFTER address;
ALTER TABLE customers ADD COLUMN agent_status ENUM('pending','approved') DEFAULT NULL COMMENT '仅agent: 申请待审核/已通过' AFTER status;
-- 已有客户视为普通客户且可登录
UPDATE customers SET role = 'customer', status = 'approved' WHERE role IS NULL OR role = '';
