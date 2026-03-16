<?php 
require_once 'config.php';
session_start();

try {
    $catCols = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    $hasCatIcon = in_array('image', $catCols) && in_array('name_en', $catCols);
    $catSql = "SELECT id, name" . ($hasCatIcon ? ", image, name_en" : "") . " FROM categories ORDER BY sort_order, id";
    $categories = $pdo->query($catSql)->fetchAll(PDO::FETCH_ASSOC);

    $category_id = isset($_GET['category']) ? (int)$_GET['category'] : null;
    $sql = "SELECT p.*, c.name AS category_name 
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

$agent_rebate_map = [];
if (!empty($products) && isset($_SESSION['customer_id'], $_SESSION['customer_role']) && $_SESSION['customer_role'] === 'agent') {
    try {
        $pids = array_column($products, 'id');
        $placeholders = implode(',', array_fill(0, count($pids), '?'));
        $stmt = $pdo->prepare("SELECT product_id, rebate_piece FROM agent_product_rebate WHERE customer_id = ? AND product_id IN ($placeholders)");
        $stmt->execute(array_merge([$_SESSION['customer_id']], $pids));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (isset($row['rebate_piece']) && $row['rebate_piece'] !== null && $row['rebate_piece'] !== '') {
                $agent_rebate_map[(int)$row['product_id']] = (float)$row['rebate_piece'];
            }
        }
    } catch (Exception $e) {}
}
$pageTitle = '首页 - 烟花网购';
require_once 'includes/header.php';

// 读取首页轮播横幅
$homeBanners = [];
try {
    $stmt = $pdo->query("SELECT image FROM home_banners WHERE is_active = 1 ORDER BY sort_order, id");
    $homeBanners = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {
    $homeBanners = [];
}
?>

<main>
    <div class="hero">
        <?php if (!empty($homeBanners)): ?>
            <div class="hero-slider" id="heroSlider">
                <?php foreach ($homeBanners as $idx => $img): ?>
                    <div class="hero-slide<?php echo $idx === 0 ? ' active' : ''; ?>">
                        <img src="<?php echo BASE_PATH . htmlspecialchars($img); ?>" alt="首页横幅" class="hero-banner">
                    </div>
                <?php endforeach; ?>
                <?php if (count($homeBanners) > 1): ?>
                    <div class="hero-dots">
                        <?php foreach ($homeBanners as $idx => $img): ?>
                            <button type="button" class="hero-dot<?php echo $idx === 0 ? ' active' : ''; ?>" data-index="<?php echo $idx; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <h1>烟花网购站</h1>
            <p>安全合规 · 品质保证 · 送货上门</p>
        <?php endif; ?>
    </div>
    <?php
    $useCategoryCards = $hasCatIcon && array_reduce($categories, function($carry, $c) { return $carry || !empty($c['image']); }, false);
    ?>
    <?php if ($useCategoryCards): ?>
    <div class="category-cards-wrap">
        <div class="category-cards">
            <a href="index.php" class="category-card<?php echo !$category_id ? ' active' : ''; ?>">
                <span class="category-icon category-icon-all">全部</span>
                <span class="category-name-en">ALL</span>
                <span class="category-name">全部商品</span>
            </a>
            <?php foreach ($categories as $c):
                $imgSrc = !empty($c['image']) ? (BASE_PATH . 'uploads/' . $c['image']) : (BASE_PATH . 'assets/img/placeholder.svg');
            ?>
                <a href="index.php?category=<?php echo $c['id']; ?>" class="category-card<?php echo $category_id == $c['id'] ? ' active' : ''; ?>">
                    <span class="category-icon"><img src="<?php echo htmlspecialchars($imgSrc); ?>" alt="" onerror="this.style.display='none';var n=this.parentElement.nextElementSibling;if(n)n.classList.add('show');"></span>
                    <span class="category-icon category-icon-fallback"><?php echo htmlspecialchars(mb_substr($c['name'], 0, 1)); ?></span>
                    <span class="category-name-en"><?php echo htmlspecialchars($c['name_en'] ?? $c['name']); ?></span>
                    <span class="category-name"><?php echo htmlspecialchars($c['name']); ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <?php else: ?>
    <div class="categories">
        <a href="index.php" class="<?php echo !$category_id ? 'active' : ''; ?>">全部</a>
        <?php foreach ($categories as $c): ?>
            <a href="index.php?category=<?php echo $c['id']; ?>" class="<?php echo $category_id == $c['id'] ? 'active' : ''; ?>">
                <?php echo htmlspecialchars($c['name']); ?>
            </a>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="products-grid">
        <?php if (empty($products)): ?>
            <p style="grid-column:1/-1; text-align:center; color:#888;">暂无商品</p>
        <?php else: ?>
            <?php foreach ($products as $p):
                $isAgent = isset($_SESSION['customer_role']) && $_SESSION['customer_role'] === 'agent';
                $rebate = 0;
                if ($isAgent) {
                    if (isset($agent_rebate_map[$p['id']]) && $agent_rebate_map[$p['id']] > 0) {
                        $rebate = $agent_rebate_map[$p['id']];
                    } elseif (isset($_SESSION['agent_default_rebate']) && $_SESSION['agent_default_rebate'] !== null) {
                        $rebate = (float)$_SESSION['agent_default_rebate'];
                    }
                }
                $priceDisplay = $isAgent && $rebate > 0 ? max(0, (float)$p['price'] - $rebate) : (float)$p['price'];
            ?>
                <div class="product-card">
                    <a href="product.php?id=<?php echo $p['id']; ?>">
                        <img src="<?php echo $p['image'] ? (BASE_PATH.'uploads/'.htmlspecialchars($p['image'])) : (BASE_PATH.'assets/img/placeholder.svg'); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>" onerror="this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22400%22 height=%22300%22%3E%3Crect fill=%22%23eee%22 width=%22400%22 height=%22300%22/%3E%3Ctext x=%2250%25%22 y=%2250%25%22 dominant-baseline=%22middle%22 text-anchor=%22middle%22 fill=%22%23999%22 font-size=%2216%22%3E暂无图片%3C/text%3E%3C/svg%3E';this.onerror=null;">
                    </a>
                    <div class="info">
                        <div class="name"><?php echo htmlspecialchars($p['name']); ?></div>
                        <div class="price">¥ <?php echo number_format($priceDisplay, 2); ?></div>
                        <div class="stock">库存 <?php echo $p['stock']; ?> 件</div>
                        <button class="btn-add" onclick="addToCart(<?php echo $p['id']; ?>,'<?php echo htmlspecialchars(addslashes($p['name'])); ?>',<?php echo $priceDisplay; ?>)" <?php echo $p['stock']<1 ? ' disabled' : ''; ?>>加入购物车</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</main>
<script>
// 首页横幅轮播
(function(){
    var slider = document.getElementById('heroSlider');
    if (!slider) return;
    var slides = slider.querySelectorAll('.hero-slide');
    var dots = slider.querySelectorAll('.hero-dot');
    if (slides.length <= 1) return;
    var index = 0;
    var timer = null;

    function show(i) {
        index = i;
        slides.forEach(function(s, idx){
            s.classList.toggle('active', idx === index);
        });
        dots.forEach(function(d, idx){
            d.classList.toggle('active', idx === index);
        });
    }
    function start() {
        stop();
        timer = setInterval(function(){
            var next = (index + 1) % slides.length;
            show(next);
        }, 4000);
    }
    function stop() {
        if (timer) {
            clearInterval(timer);
            timer = null;
        }
    }
    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', start);
    dots.forEach(function(d){
        d.addEventListener('click', function(){
            var i = parseInt(this.getAttribute('data-index') || '0', 10);
            show(i);
        });
    });
    start();
})();

function addToCart(id,name,price){
    var cart=JSON.parse(localStorage.getItem('cart')||'[]');
    var i=cart.find(function(x){return x.id==id && (x.unit||'piece')==='piece';});
    if(i)i.quantity=(i.quantity||1)+1; else cart.push({id:id,name:name,price:price,quantity:1,unit:'piece'});
    localStorage.setItem('cart',JSON.stringify(cart));
    alert('已加入购物车');
    document.getElementById('cartLink').innerHTML='购物车 ('+cart.reduce(function(s,x){return s+(x.quantity||1);},0)+')';
}
</script>
<?php require_once 'includes/footer.php'; ?>
