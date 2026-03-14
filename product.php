<?php
require_once 'config.php';
session_start();
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
            <?php if (!empty($product['video'])): ?>
                <video src="<?php echo BASE_PATH; ?>uploads/<?php echo htmlspecialchars($product['video']); ?>" controls style="max-width:100%;max-height:400px;display:block;" poster="<?php echo $product['image'] ? (BASE_PATH.'uploads/'.htmlspecialchars($product['image'])) : ''; ?>">您的浏览器不支持视频播放。</video>
            <?php endif; ?>
            <img src="<?php echo !empty($product['image']) ? (BASE_PATH.'uploads/'.htmlspecialchars($product['image'])) : (BASE_PATH.'assets/img/placeholder.svg'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23eee%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2216%22%3E暂无图片%3C/text%3E%3C/svg%3E';this.onerror=null;"<?php if (!empty($product['video'])): ?> style="display:none;"<?php endif; ?>>
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
    if(confirm('已加入购物车，是否前往购物车？')) location.href='<?php echo BASE_PATH; ?>cart.php';
}
</script>
<?php require_once 'includes/footer.php'; ?>
