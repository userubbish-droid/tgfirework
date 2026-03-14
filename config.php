<?php
/**
 * 烟花网购站 - 数据库配置
 * 线上（tgfirework.online）：在服务器上创建 config.local.php，内容只写 <?php return ['password' => '你的数据库密码']; ?>
 */
$dbConfig = [
    'host'     => 'localhost',
    'dbname'   => 'u870568714_tgfirework',
    'user'     => 'u870568714_tgfirework',
    'password' => 'Tgfirework@996',  // 线上必填：在 config.local.php 里写 password
    'charset'  => 'utf8mb4',
];

$configLocalPath = __DIR__ . '/config.local.php';
if (file_exists($configLocalPath)) {
    $local = include $configLocalPath;
    if (is_array($local)) {
        $dbConfig = array_merge($dbConfig, $local);
    }
}
// 环境变量 DB_PASSWORD 仅当有值时才覆盖，避免空值覆盖 config.local.php
$envPass = getenv('DB_PASSWORD');
if ($envPass !== false && $envPass !== '') {
    $dbConfig['password'] = $envPass;
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
    define('SITE_URL', isset($dbConfig['site_url']) ? $dbConfig['site_url'] : '');
}
// 根目录时 BASE_PATH='/'; 子目录时 BASE_PATH='/tg/'，所有链接用 BASE_PATH.'xxx.php'
define('BASE_PATH', SITE_URL !== '' ? rtrim(SITE_URL, '/') . '/' : '/');
define('ADMIN_URL', SITE_URL . '/admin');
