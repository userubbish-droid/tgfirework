<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'login.php?from=checkout');
    exit;
}

$pageTitle = '确认订单 - 烟花网购';
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $remark = trim($_POST['remark'] ?? '');
    $cart = json_decode($_POST['cart_json'] ?? '[]', true);
    if (!$name || !$phone || !$address || !is_array($cart) || empty($cart)) {
        echo '<div class="alert alert-error">请填写完整信息且购物车不能为空。</div>';
    } else {
        $orderNo = 'FW' . date('YmdHis') . rand(100, 999);
        $total = 0;
        foreach ($cart as $item) $total += ($item['price']??0) * ($item['quantity']??1);
        $pdo->beginTransaction();
        try {
            $pdo->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, customer_address, total_amount, remark) VALUES (?,?,?,?,?,?,?)")
                ->execute([$orderNo, $_SESSION['customer_id'], $name, $phone, $address, $total, $remark]);
            $orderId = $pdo->lastInsertId();
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?,?,?,?,?,?)");
            foreach ($cart as $item) {
                $sub = ($item['price']??0) * ($item['quantity']??1);
                $stmt->execute([$orderId, $item['id'], $item['name']??'', $item['price']??0, $item['quantity']??1, $sub]);
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$item['quantity']??1, $item['id']]);
            }
            $pdo->commit();
            echo '<div class="alert alert-success">下单成功！订单号：' . htmlspecialchars($orderNo) . '</div>';
            echo '<p><a href="' . BASE_PATH . 'index.php" class="btn btn-primary">返回首页</a></p>';
            echo '<script>localStorage.removeItem("cart");</script>';
            require_once 'includes/footer.php';
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            echo '<div class="alert alert-error">下单失败：' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>
<?php
$cust = null;
if (!empty($_SESSION['customer_id'])) {
    $c = $pdo->prepare("SELECT name, phone, address FROM customers WHERE id = ?");
    $c->execute([$_SESSION['customer_id']]);
    $cust = $c->fetch();
}
?>
<main>
    <h2>确认订单</h2>
    <form method="post" action="">
        <input type="hidden" name="cart_json" id="cartJson" value="">
        <div class="form-group">
            <label>收货人 *</label>
            <input type="text" name="customer_name" required placeholder="姓名" value="<?php echo htmlspecialchars($cust['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>联系电话 *</label>
            <input type="text" name="customer_phone" required placeholder="手机号" value="<?php echo htmlspecialchars($cust['phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>收货地址 *</label>
            <textarea name="customer_address" required placeholder="详细地址"><?php echo htmlspecialchars($cust['address'] ?? ''); ?></textarea>
        </div>
        <div class="form-group">
            <label>备注</label>
            <textarea name="remark" placeholder="选填"></textarea>
        </div>
        <button type="submit" class="btn btn-primary">提交订单</button>
        <a href="<?php echo BASE_PATH; ?>cart.php" class="btn" style="margin-left:0.5rem;">返回购物车</a>
    </form>
</main>
<script>
(function(){
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    document.getElementById('cartJson').value=JSON.stringify(cart);
    if(cart.length===0){ alert('购物车为空'); location.href='<?php echo BASE_PATH; ?>cart.php'; }
})();
</script>
<?php require_once 'includes/footer.php'; ?>
