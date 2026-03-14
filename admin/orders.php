<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
if (isset($_GET['update_status']) && isset($_GET['id'])) {
    $oid = (int)$_GET['id'];
    $status = $_GET['update_status'];
    if (in_array($status, ['pending','paid','shipped','completed','cancelled'])) {
        $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?")->execute([$status, $oid]);
    }
    header('Location: orders.php');
    exit;
}
$orders = $pdo->query("SELECT * FROM orders ORDER BY id DESC")->fetchAll();
$statusLabels = ['pending'=>'待付款','paid'=>'已付款','shipped'=>'已发货','completed'=>'已完成','cancelled'=>'已取消'];
$deliveryLabels = ['self_pickup'=>'自取','lalamove'=>'Lalamove','mail'=>'邮寄'];
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>订单管理 - 烟花网购后台</title>
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
            <h2>订单管理</h2>
        </div>
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>订单号</th><th>客户</th><th>电话</th><th>配送</th><th>金额</th><th>状态</th><th>下单时间</th><th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($o['order_no']); ?></td>
                        <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                        <td><?php echo htmlspecialchars($o['customer_phone']); ?></td>
                        <td><?php echo isset($o['delivery_type']) && $o['delivery_type'] ? ($deliveryLabels[$o['delivery_type']] ?? $o['delivery_type']) : '—'; ?></td>
                        <td>¥ <?php echo number_format($o['total_amount'], 2); ?></td>
                        <td><span class="admin-badge <?php echo $o['status']; ?>"><?php echo $statusLabels[$o['status']] ?? $o['status']; ?></span></td>
                        <td><?php echo $o['created_at']; ?></td>
                        <td class="admin-actions">
                            <a href="order_detail.php?id=<?php echo $o['id']; ?>">详情</a>
                            <?php if ($o['status'] !== 'cancelled' && $o['status'] !== 'completed'): ?>
                                <select onchange="if(this.value) location.href='?update_status='+this.value+'&id=<?php echo $o['id']; ?>'">
                                    <option value="">改状态</option>
                                    <?php foreach ($statusLabels as $k => $v): if ($k !== $o['status']) echo '<option value="'.$k.'">'.$v.'</option>'; endforeach; ?>
                                </select>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($orders)): ?><p style="padding:2rem; color:#888;">暂无订单</p><?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
