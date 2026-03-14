<?php
/**
 * 临时诊断：检查 config.local.php 是否被加载、密码是否已设置
 * 用后请删除此文件，避免泄露信息
 */
header('Content-Type: text/plain; charset=utf-8');
$configPath = __DIR__ . '/config.php';
$localPath = __DIR__ . '/config.local.php';

echo "1. config.php 所在目录: " . __DIR__ . "\n";
echo "2. config.local.php 是否存在: " . (file_exists($localPath) ? '是' : '否') . "\n";

if (!file_exists($configPath)) {
    echo "错误: config.php 不存在\n";
    exit;
}

$dbConfig = [
    'host'     => 'localhost',
    'dbname'   => 'u870568714_tgfirework',
    'user'     => 'u870568714_tgfirework',
    'password' => 'Tgfirework@996',
    'charset'  => 'utf8mb4',
];
if (file_exists($localPath)) {
    $local = @include $localPath;
    echo "3. config.local.php 返回类型: " . gettype($local) . "\n";
    if (is_array($local)) {
        $dbConfig = array_merge($dbConfig, $local);
        echo "4. 合并后 password 是否已设置(非空): " . (isset($dbConfig['password']) && $dbConfig['password'] !== '' ? '是' : '否') . "\n";
    } else {
        echo "4. 警告: config.local.php 未 return 数组，请检查语法\n";
    }
} else {
    echo "3. 请将 config.local.php 放在与 config.php 同一目录: " . __DIR__ . "\n";
}

$envPass = getenv('DB_PASSWORD');
echo "5. 环境变量 DB_PASSWORD 是否已设置: " . ($envPass !== false ? '是' : '否') . "\n";

echo "\n若 2=否，请把 config.local.php 上传到上述目录。\n";
echo "若 4=否，请检查 config.local.php 内容是否为: return ['password' => '你的密码'];";
