-- 登录改为手机号：已有数据库在 phpMyAdmin 中执行本文件
-- 执行前请确认：无重复手机号，且空手机号会被设为 migrate_用户ID（这些用户需在后台改为真实手机号后才能登录）

-- 将空手机号设为临时唯一值，以便加 UNIQUE
UPDATE customers SET phone = CONCAT('migrate_', id) WHERE phone = '' OR phone IS NULL;

-- 手机号改为必填且唯一；邮箱改为可空（不再用于登录）
ALTER TABLE customers MODIFY phone VARCHAR(20) NOT NULL;
ALTER TABLE customers MODIFY email VARCHAR(100) NULL;
ALTER TABLE customers ADD UNIQUE KEY uk_phone (phone);
-- 若报错 Duplicate entry，说明存在重复手机号，需在后台或数据库里先改成不同值后再执行
