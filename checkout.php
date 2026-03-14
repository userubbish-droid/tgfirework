<?php
require_once 'config.php';
require_once 'includes/delivery_helpers.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'login.php?from=checkout');
    exit;
}

$pageTitle = '确认订单 - 烟花网购';
$deliveryLabels = ['self_pickup' => '自取', 'lalamove' => 'Lalamove', 'mail' => '邮寄'];
require_once 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['customer_name'] ?? '');
    $phone = trim($_POST['customer_phone'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $remark = trim($_POST['remark'] ?? '');
    $deliveryType = trim($_POST['delivery_type'] ?? '');
    $cart = json_decode($_POST['cart_json'] ?? '[]', true);
    $productIds = array_map(function($i) { return (int)($i['id'] ?? 0); }, is_array($cart) ? $cart : []);
    $productIds = array_filter($productIds);
    $allowedDelivery = getAllowedDeliveryTypes($pdo, $productIds);
    if (!$name || !$phone || !is_array($cart) || empty($cart)) {
        echo '<div class="alert alert-error">请填写收货人、电话且购物车不能为空。</div>';
    } elseif (!in_array($deliveryType, $allowedDelivery, true)) {
        echo '<div class="alert alert-error">请选择有效的配送方式。</div>';
    } elseif ($deliveryType !== 'self_pickup' && $address === '') {
        echo '<div class="alert alert-error">选择 Lalamove 或邮寄时请填写收货地址。</div>';
    } else {
        $orderNo = 'FW' . date('YmdHis') . rand(100, 999);
        $total = 0;
        foreach ($cart as $item) $total += ($item['price']??0) * ($item['quantity']??1);
        $addressVal = $deliveryType === 'self_pickup' ? ($address !== '' ? $address : '自取') : $address;
        $pdo->beginTransaction();
        try {
            $hasDeliveryCol = false;
            $hasUnitCol = false;
            try {
                $cols = $pdo->query("SHOW COLUMNS FROM orders")->fetchAll(PDO::FETCH_COLUMN);
                $hasDeliveryCol = in_array('delivery_type', $cols);
                $oicols = $pdo->query("SHOW COLUMNS FROM order_items")->fetchAll(PDO::FETCH_COLUMN);
                $hasUnitCol = in_array('unit', $oicols);
            } catch (Exception $e) {}
            if ($hasDeliveryCol) {
                $pdo->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, customer_address, delivery_type, total_amount, remark) VALUES (?,?,?,?,?,?,?,?)")
                    ->execute([$orderNo, $_SESSION['customer_id'], $name, $phone, $addressVal, $deliveryType ?: null, $total, $remark]);
            } else {
                $pdo->prepare("INSERT INTO orders (order_no, customer_id, customer_name, customer_phone, customer_address, total_amount, remark) VALUES (?,?,?,?,?,?,?)")
                    ->execute([$orderNo, $_SESSION['customer_id'], $name, $phone, $addressVal, $total, $remark]);
            }
            $orderId = $pdo->lastInsertId();
            foreach ($cart as $item) {
                $qty = (int)($item['quantity']??1);
                $price = (float)($item['price']??0);
                $unit = isset($item['unit']) && $item['unit'] === 'box' ? 'box' : 'piece';
                $sub = $price * $qty;
                $deduct = $unit === 'box' && !empty($item['box_pieces']) ? (int)$item['box_pieces'] * $qty : $qty;
                if ($hasUnitCol) {
                    $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, unit, subtotal) VALUES (?,?,?,?,?,?,?)")
                        ->execute([$orderId, $item['id'], $item['name']??'', $price, $qty, $unit, $sub]);
                } else {
                    $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_name, price, quantity, subtotal) VALUES (?,?,?,?,?,?)")
                        ->execute([$orderId, $item['id'], $item['name']??'', $price, $qty, $sub]);
                }
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")->execute([$deduct, $item['id']]);
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
    <form method="post" action="" id="checkoutForm">
        <input type="hidden" name="cart_json" id="cartJson" value="">
        <div class="delivery-radio-hidden" aria-hidden="true">
            <input type="radio" name="delivery_type" id="delivery_self_pickup" value="self_pickup" tabindex="-1">
            <input type="radio" name="delivery_type" id="delivery_lalamove" value="lalamove" tabindex="-1">
            <input type="radio" name="delivery_type" id="delivery_mail" value="mail" tabindex="-1">
        </div>
        <div class="form-group delivery-method-wrap">
            <label class="form-label">配送方式 *</label>
            <div class="delivery-options-row" role="group" aria-label="配送方式">
                <button type="button" class="delivery-option-btn" data-delivery="self_pickup" aria-pressed="false">自取</button>
                <button type="button" class="delivery-option-btn" data-delivery="lalamove" aria-pressed="false">Lalamove</button>
                <button type="button" class="delivery-option-btn" data-delivery="mail" aria-pressed="false">邮寄</button>
            </div>
        </div>
        <div class="form-group">
            <label>收货人 *</label>
            <input type="text" name="customer_name" required placeholder="姓名" value="<?php echo htmlspecialchars($cust['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>联系电话 *</label>
            <input type="text" name="customer_phone" required placeholder="手机号" value="<?php echo htmlspecialchars($cust['phone'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>收货地址 <span id="addrLabel">（自取可不填）</span></label>
            <textarea name="customer_address" placeholder="详细地址；自取可填自取点或留空"><?php echo htmlspecialchars($cust['address'] ?? ''); ?></textarea>
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
(function(){
    var form = document.getElementById('checkoutForm');
    var row = form && form.querySelector('.delivery-options-row');
    if(!row) return;
    var radios = form.querySelectorAll('input[name="delivery_type"]');
    var btns = row.querySelectorAll('.delivery-option-btn');
    function syncFromRadio(){
        var checked = form.querySelector('input[name="delivery_type"]:checked');
        btns.forEach(function(btn){
            var v = btn.getAttribute('data-delivery');
            var isSelected = checked && checked.value === v;
            btn.classList.toggle('delivery-selected', isSelected);
            btn.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
        });
    }
    btns.forEach(function(btn){
        btn.addEventListener('click', function(){
            var v = this.getAttribute('data-delivery');
            var r = form.querySelector('input[name="delivery_type"][value="'+v+'"]');
            if(r){ r.checked = true; syncFromRadio(); }
        });
    });
    radios.forEach(function(r){ r.addEventListener('change', syncFromRadio); });
    syncFromRadio();
    form.addEventListener('submit', function(e){
        var checked = form.querySelector('input[name="delivery_type"]:checked');
        if(!checked){
            e.preventDefault();
            alert('请选择配送方式');
            return false;
        }
    });
})();
</script>
<?php require_once 'includes/footer.php'; ?>
