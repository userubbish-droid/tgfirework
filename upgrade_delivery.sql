-- 配送方式功能升级（已有数据库在 phpMyAdmin 中执行）
-- 1. 设置表与默认值
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(64) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
INSERT IGNORE INTO settings (setting_key, setting_value) VALUES
('delivery_self_pickup_enabled', '1'),
('delivery_lalamove_enabled', '1'),
('delivery_mail_enabled', '1'),
('delivery_self_pickup_date_from', ''),
('delivery_self_pickup_date_to', ''),
('delivery_lalamove_date_from', ''),
('delivery_lalamove_date_to', ''),
('delivery_mail_date_from', ''),
('delivery_mail_date_to', '');

-- 2. 订单表增加配送类型，地址改为可空（自取时可无地址）
ALTER TABLE orders ADD COLUMN delivery_type VARCHAR(20) DEFAULT NULL COMMENT 'self_pickup|lalamove|mail' AFTER customer_address;
ALTER TABLE orders MODIFY customer_address TEXT NULL;

-- 3. 商品表增加配送方式勾选
ALTER TABLE products ADD COLUMN allow_self_pickup TINYINT(1) DEFAULT 1 AFTER is_active;
ALTER TABLE products ADD COLUMN allow_lalamove TINYINT(1) DEFAULT 1 AFTER allow_self_pickup;
ALTER TABLE products ADD COLUMN allow_mail TINYINT(1) DEFAULT 1 AFTER allow_lalamove;
