<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'login.php?from=my_orders');
    exit;
}

$stmt = $pdo->prepare("SELECT id, order_no, total_amount, status, created_at FROM orders WHERE customer_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['customer_id']]);
$orders = $stmt->fetchAll();
$statusText = ['pending' => '待付款', 'paid' => '已付款', 'shipped' => '已发货', 'completed' => '已完成', 'cancelled' => '已取消'];

$pageTitle = '我的订单 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>我的订单</h2>
    <?php if (empty($orders)): ?>
        <p class="cart-empty">暂无订单，<a href="<?php echo SITE_URL ? SITE_URL.'/' : ''; ?>index.php">去选购</a></p>
    <?php else: ?>
        <table class="cart-table">
            <thead>
                <tr>
                    <th>订单号</th>
                    <th>金额</th>
                    <th>状态</th>
                    <th>下单时间</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $o): ?>
                <tr>
                    <td><?php echo htmlspecialchars($o['order_no']); ?></td>
                    <td>¥ <?php echo number_format($o['total_amount'], 2); ?></td>
                    <td><?php echo $statusText[$o['status']] ?? $o['status']; ?></td>
                    <td><?php echo $o['created_at']; ?></td>
                    <td><a href="<?php echo SITE_URL ? SITE_URL.'/' : ''; ?>order_detail.php?id=<?php echo $o['id']; ?>">查看详情</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</main>
<?php require_once 'includes/footer.php'; ?>
