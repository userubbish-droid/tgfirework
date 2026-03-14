<?php
/**
 * 烟花网购站 - 数据库配置
 * 线上环境请复制 config.local.example.php 为 config.local.php 并填写真实数据库信息
 */
$dbConfig = [
    'host'     => 'localhost',
    'dbname'   => 'fireworks_shop',
    'user'     => 'root',
    'password' => '',
    'charset'  => 'utf8mb4',
];

if (file_exists(__DIR__ . '/config.local.php')) {
    $local = include __DIR__ . '/config.local.php';
    if (is_array($local)) {
        $dbConfig = array_merge($dbConfig, $local);
    }
}

$dsn = 'mysql:host=' . $dbConfig['host'] . ';dbname=' . $dbConfig['dbname'] . ';charset=' . $dbConfig['charset'];
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
} catch (PDOException $e) {
    die('数据库连接失败：' . $e->getMessage());
}

if (!defined('SITE_URL')) {
    define('SITE_URL', isset($dbConfig['site_url']) ? $dbConfig['site_url'] : '/tg');
}
define('ADMIN_URL', SITE_URL . '/admin');
