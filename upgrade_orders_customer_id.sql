-- 为 orders 表增加 customer_id 列（已有数据库在 phpMyAdmin 中执行）
-- 用于关联登录用户，方便「我的订单」等功能；执行后新订单会记录客户 id
ALTER TABLE orders ADD COLUMN customer_id INT DEFAULT NULL COMMENT '登录客户 id' AFTER order_no;
