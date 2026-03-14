<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'login.php?from=order_detail');
    exit;
}

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
$stmt->execute([$id, $_SESSION['customer_id']]);
$order = $stmt->fetch();
if (!$order) {
    header('Location: my_orders.php');
    exit;
}

$items = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll();
$statusText = ['pending' => '待付款', 'paid' => '已付款', 'shipped' => '已发货', 'completed' => '已完成', 'cancelled' => '已取消'];

$pageTitle = '订单详情 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>订单详情</h2>
    <p><strong>订单号：</strong><?php echo htmlspecialchars($order['order_no']); ?></p>
    <p><strong>状态：</strong><?php echo $statusText[$order['status']] ?? $order['status']; ?></p>
    <p><strong>收货人：</strong><?php echo htmlspecialchars($order['customer_name']); ?></p>
    <p><strong>电话：</strong><?php echo htmlspecialchars($order['customer_phone']); ?></p>
    <p><strong>地址：</strong><?php echo nl2br(htmlspecialchars($order['customer_address'])); ?></p>
    <?php if ($order['remark']): ?><p><strong>备注：</strong><?php echo nl2br(htmlspecialchars($order['remark'])); ?></p><?php endif; ?>
    <table class="cart-table" style="margin-top:1rem;">
        <thead>
            <tr><th>商品</th><th>单价</th><th>数量</th><th>小计</th></tr>
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
    <p><a href="<?php echo SITE_URL ? SITE_URL.'/' : ''; ?>my_orders.php" class="btn">返回订单列表</a></p>
</main>
<?php require_once 'includes/footer.php'; ?>
