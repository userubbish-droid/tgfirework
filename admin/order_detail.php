<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch();
if (!$order) { header('Location: orders.php'); exit; }
$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();
$statusLabels = ['pending'=>'待付款','paid'=>'已付款','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'];
$deliveryLabels = ['self_pickup'=>'自取','lalamove'=>'Lalamove','mail'=>'邮寄'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单详情 - 烟花网购后台</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">后台管理</div>
        <a href="index.php">仪表盘</a>
        <a href="products.php">商品管理</a>
        <a href="orders.php" class="active">订单管理</a>
        <a href="customers.php">客户管理</a>
        <a href="delivery_settings.php">配送设置</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>订单详情</h2>
            <a href="orders.php" class="btn btn-sm">返回列表</a>
        </div>
        <div class="admin-card">
            <p><strong>订单号：</strong><?php echo htmlspecialchars($order['order_no']); ?></p>
            <p><strong>状态：</strong><span class="admin-badge <?php echo $order['status']; ?>"><?php echo $statusLabels[$order['status']]; ?></span></p>
            <p><strong>收货人：</strong><?php echo htmlspecialchars($order['customer_name']); ?></p>
            <p><strong>电话：</strong><?php echo htmlspecialchars($order['customer_phone']); ?></p>
            <p><strong>配送方式：</strong><?php echo isset($order['delivery_type']) && $order['delivery_type'] ? ($deliveryLabels[$order['delivery_type']] ?? $order['delivery_type']) : '—'; ?></p>
            <p><strong>地址：</strong><?php echo $order['customer_address'] ? nl2br(htmlspecialchars($order['customer_address'])) : '—'; ?></p>
            <?php if ($order['remark']): ?><p><strong>备注：</strong><?php echo nl2br(htmlspecialchars($order['remark'])); ?></p><?php endif; ?>
        </div>
        <div class="admin-card">
            <h3>商品明细</h3>
            <table class="admin-table">
                <thead>
                    <tr><th>商品名</th><th>单价</th><th>数量</th><th>小计</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $i): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($i['product_name']); ?></td>
                        <td>¥ <?php echo number_format($i['price'], 2); ?></td>
                        <td><?php echo $i['quantity']; ?></td>
                        <td>¥ <?php echo number_format($i['subtotal'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="cart-total">订单总额：<strong>¥ <?php echo number_format($order['total_amount'], 2); ?></strong></p>
        </div>
    </main>
</div>
</body>
</html>
