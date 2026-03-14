<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$today = date('Y-m-d');
$todayOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE DATE(created_at) = '$today'")->fetchColumn();
$todaySales = $pdo->query("SELECT COALESCE(SUM(total_amount),0) FROM orders WHERE DATE(created_at) = '$today' AND status != 'cancelled'")->fetchColumn();
$productCount = $pdo->query("SELECT COUNT(*) FROM products WHERE is_active = 1")->fetchColumn();
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>后台首页 - 烟花网购</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">后台管理</div>
        <a href="index.php" class="active">仪表盘</a>
        <a href="products.php">商品管理</a>
        <a href="orders.php">订单管理</a>
        <a href="<?php echo SITE_URL; ?>/index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>仪表盘</h2>
            <span>欢迎，<?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
        </div>
        <div class="stats-row">
            <div class="stat-card">
                <h3>今日订单</h3>
                <div class="num"><?php echo $todayOrders; ?></div>
            </div>
            <div class="stat-card gold">
                <h3>今日销售额</h3>
                <div class="num">¥ <?php echo number_format($todaySales, 2); ?></div>
            </div>
            <div class="stat-card">
                <h3>在售商品</h3>
                <div class="num"><?php echo $productCount; ?></div>
            </div>
            <div class="stat-card">
                <h3>待处理订单</h3>
                <div class="num"><?php echo $pendingOrders; ?></div>
            </div>
        </div>
        <div class="admin-card">
            <h3>快捷操作</h3>
            <p>
                <a href="products.php?action=add" class="btn btn-primary btn-sm">添加商品</a>
                <a href="orders.php" class="btn btn-sm" style="background:#eee;">查看订单</a>
            </p>
        </div>
    </main>
</div>
</body>
</html>
