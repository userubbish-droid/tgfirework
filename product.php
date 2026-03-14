<?php
require_once 'config.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

$pageTitle = $product['name'] . ' - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <div class="product-detail">
        <div>
            <img src="<?php echo $product['image'] ? SITE_URL.'/uploads/'.htmlspecialchars($product['image']) : SITE_URL.'/assets/img/placeholder.svg'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>
        <div class="meta">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <div class="price">¥ <?php echo number_format($product['price'], 2); ?></div>
            <div class="description"><?php echo nl2br(htmlspecialchars($product['description'] ?: '暂无描述')); ?></div>
            <p>库存：<?php echo $product['stock']; ?> 件</p>
            <div class="quantity-wrap">
                <label>数量：</label>
                <input type="number" id="qty" min="1" max="<?php echo $product['stock']; ?>" value="1">
            </div>
            <button class="btn-buy" onclick="addAndGo(<?php echo $product['id']; ?>,'<?php echo htmlspecialchars(addslashes($product['name'])); ?>',<?php echo $product['price']; ?>)" <?php echo $product['stock']<1 ? ' disabled' : ''; ?>>加入购物车</button>
        </div>
    </div>
</main>
<script>
function addAndGo(id,name,price){
    var qty=parseInt(document.getElementById('qty').value)||1;
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    var i=cart.find(function(x){return x.id==id;});
    if(i)i.quantity+=qty; else cart.push({id:id,name:name,price:price,quantity:qty});
    localStorage.setItem('cart',JSON.stringify(cart));
    if(confirm('已加入购物车，是否前往购物车？')) location.href='<?php echo SITE_URL; ?>/cart.php';
}
</script>
<?php require_once 'includes/footer.php'; ?>
