<?php
/**
 * 烟花网购站 - 数据库配置
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'fireworks_shop');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

$dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die('数据库连接失败：' . $e->getMessage());
}

// 站点基础 URL（按需修改）
define('SITE_URL', '/tg');
define('ADMIN_URL', SITE_URL . '/admin');
