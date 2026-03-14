-- 烟花网购站 - 表结构及初始数据
-- 在 phpMyAdmin 中：先左侧选中你的数据库（如 u870568714_tgfirework），再点“导入”选择本文件执行
-- 管理员
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 客户：登录使用手机号；普通客户=注册即可购物；Agent(批发)=需申请或由管理员提升
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL UNIQUE COMMENT '登录账号',
    email VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(50) NOT NULL,
    address TEXT,
    role ENUM('customer','agent') NOT NULL DEFAULT 'customer' COMMENT 'customer=普通客户 agent=批发',
    status ENUM('pending','approved') NOT NULL DEFAULT 'approved' COMMENT '账号是否可登录',
    agent_status ENUM('pending','approved') DEFAULT NULL COMMENT '仅agent: 申请待审核/已通过',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 分类
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    sort_order INT DEFAULT 0
);

-- 商品
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT DEFAULT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    stock INT NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT NULL,
    video VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    allow_self_pickup TINYINT(1) DEFAULT 1,
    allow_lalamove TINYINT(1) DEFAULT 1,
    allow_mail TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 配送方式全局开关与开放时间（可选）
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(64) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 订单（customer_id 为登录用户 id，可为空兼容旧订单）
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_no VARCHAR(32) NOT NULL UNIQUE,
    customer_id INT DEFAULT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_address TEXT,
    delivery_type VARCHAR(20) DEFAULT NULL COMMENT 'self_pickup|lalamove|mail',
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
    remark TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- 订单明细
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(200) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT
);

INSERT INTO admins (username, password) VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- 配送方式默认全开，开放时间不填表示一直开放
INSERT INTO settings (setting_key, setting_value) VALUES
('delivery_self_pickup_enabled', '1'),
('delivery_lalamove_enabled', '1'),
('delivery_mail_enabled', '1'),
('delivery_self_pickup_date_from', ''),
('delivery_self_pickup_date_to', ''),
('delivery_lalamove_date_from', ''),
('delivery_lalamove_date_to', ''),
('delivery_mail_date_from', ''),
('delivery_mail_date_to', '')
ON DUPLICATE KEY UPDATE setting_key=setting_key;

INSERT INTO categories (name, sort_order) VALUES
('礼花弹', 1),
('组合烟花', 2),
('喷花类', 3),
('鞭炮', 4);

INSERT INTO products (category_id, name, description, price, stock) VALUES
(1, '高空礼花弹 A型', '适合庆典，色彩绚丽', 188.00, 50),
(2, '开门红组合烟花', '36发组合，安全环保', 68.00, 100),
(3, '儿童喷花 小星星', '儿童安全型喷花', 15.00, 200);
