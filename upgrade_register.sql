-- 已有数据库请执行本文件，添加“客户注册”相关表与字段
-- 在 phpMyAdmin 中选中数据库后导入

CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(50) NOT NULL,
    phone VARCHAR(20) NOT NULL DEFAULT '',
    address TEXT,
    status ENUM('pending','approved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 若 orders 表已存在且无 customer_id，执行下面一行（若报错“重复列”可忽略）
ALTER TABLE orders ADD COLUMN customer_id INT DEFAULT NULL AFTER order_no;
