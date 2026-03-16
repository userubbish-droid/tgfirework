-- 首页多图轮播 Banner 表（已有数据库在 phpMyAdmin 中执行）
CREATE TABLE IF NOT EXISTS home_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image VARCHAR(255) NOT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

