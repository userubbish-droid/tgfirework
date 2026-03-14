<?php
require_once 'config.php';
session_start();

try {
    $stmt = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order, id");
    $categories = $stmt->fetchAll();

    $category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $sql = "SELECT p.id, p.name, p.price, p.stock, p.image, c.name AS category_name 
            FROM products p 
            LEFT JOIN categories c ON p.category_id = c.id 
            WHERE p.is_active = 1 AND p.stock > 0";
    $params = [];
    if ($category_id) {
        $sql .= " AND p.category_id = ?";
        $params[] = $category_id;
    }
    $sql .= " ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();
} catch (PDOException $e) {
    $msg = $e->getMessage();
    if (strpos($msg, 'doesn\'t exist') !== false || strpos($msg, '1146') !== false) {
        die('数据库表未创建。请在 phpMyAdmin 中导入 install.sql 创建表结构。错误参考：' . htmlspecialchars($msg));
    }
    throw $e;
}

$pageTitle = '首页 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <div class="hero">
        <h1>烟花网购站</h1>
        <p>安全合规 · 品质保证 · 送货上门</p>
    </div>
    <div class="categories">
        <a href="index.php" class="<?php echo !$category_id ? 'active' : ''; ?>">全部</a>
        <?php foreach ($categories as $c): ?>
            <a href="index.php?category=<?php echo $c['id']; ?>" class="<?php echo $category_id == $c['id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($c['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <p style="grid-column:1/-1; text-align:center; color:#888;">暂无商品</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo $p['id']; ?>">
                        <img src="<?php echo $p['image'] ? SITE_URL.'/uploads/'.htmlspecialchars($p['image']) : SITE_URL.'/assets/img/placeholder.svg'; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                    </a>
                    <div class="info">
                        <div class="name"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="price">¥ <?php echo number_format($p['price'], 2); ?></div>
                        <div class="stock">库存 <?php echo $p['stock']; ?> 件</div>
                        <button class="btn-add" onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo htmlspecialchars(addslashes($p['name'])); ?>',<?php echo $p['price']; ?>)" <?php echo $p['stock']<1 ? ' disabled' : ''; ?>>加入购物车</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<script>
function addToCart(id,name,price){
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    var i=cart.find(function(x){return x.id==id;});
    if(i)i.quantity=(i.quantity||1)+1; else cart.push({id:id,name:name,price:price,quantity:1});
    localStorage.setItem('cart',JSON.stringify(cart));
    alert('已加入购物车');
    document.getElementById('cartLink').innerHTML='购物车 ('+cart.reduce(function(s,x){return s+(x.quantity||1);},0)+')';
}
</script>
<?php require_once 'includes/footer.php'; ?>
