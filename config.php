<?php
/**
 * 烟花网购站 - 数据库配置
 * 线上（tgfirework.online）：在服务器上创建 config.local.php，内容只写 <?php return ['password' => '你的数据库密码']; ?>
 */
$dbConfig = [
    'host'     => 'localhost',
    'dbname'   => 'u870568714_tgfirework',
    'user'     => 'u870568714_tgfirework',
    'password' => '',  // 线上必填：在 config.local.php 里写 password
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
