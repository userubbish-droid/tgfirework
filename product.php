<?php
require_once 'config.php';
session_start();
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header('Location: index.php'); exit; }

$stmt = $pdo->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ? AND p.is_active = 1");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) { header('Location: index.php'); exit; }

$sell_type = $product['sell_type'] ?? 'piece';
$can_box = ($sell_type === 'box' || $sell_type === 'both') && !empty($product['box_pieces']) && isset($product['price_box']) && $product['price_box'] !== '' && $product['price_box'] !== null;
$can_piece = ($sell_type === 'piece' || $sell_type === 'both');
$is_agent = isset($_SESSION['customer_role']) && $_SESSION['customer_role'] === 'agent';
$rebate = isset($product['agent_rebate']) && $product['agent_rebate'] !== null && $product['agent_rebate'] !== '' ? (float)$product['agent_rebate'] : 0;
$rebate_box = isset($product['agent_rebate_box']) && $product['agent_rebate_box'] !== null && $product['agent_rebate_box'] !== '' ? (float)$product['agent_rebate_box'] : 0;
$price_piece = (float)$product['price'];
$price_box_val = $can_box ? (float)$product['price_box'] : 0;
if ($is_agent) {
    $price_piece = $rebate > 0 ? max(0, $price_piece - $rebate) : $price_piece;
    $price_box_val = $rebate_box > 0 ? max(0, $price_box_val - $rebate_box) : $price_box_val;
}
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
            <?php if ($can_box && $can_piece): ?>
                <div class="price-row">
                    <span>散买：<strong class="price">¥ <?php echo number_format($price_piece, 2); ?></strong> / 件</span>
                    <span>按箱：<strong class="price">¥ <?php echo number_format($price_box_val, 2); ?></strong> / 箱（<?php echo (int)$product['box_pieces']; ?> 件）</span>
                </div>
            <?php elseif ($can_box): ?>
                <div class="price">¥ <?php echo number_format($price_box_val, 2); ?> / 箱（<?php echo (int)$product['box_pieces']; ?> 件）</div>
            <?php else: ?>
                <div class="price">¥ <?php echo number_format($price_piece, 2); ?> / 件</div>
            <?php endif; ?>
            <div class="description"><?php echo nl2br(htmlspecialchars($product['description'] ?: '暂无描述')); ?></div>
            <p>库存：<?php echo $product['stock']; ?> 件</p>
            <?php if ($can_box || $can_piece): ?>
            <div class="quantity-wrap">
                <label>购买方式：</label>
                <?php if ($can_box && $can_piece): ?>
                    <label><input type="radio" name="buy_unit" value="piece" checked> 散买（按件）</label>
                    <label><input type="radio" name="buy_unit" value="box"> 按箱买</label>
                <?php elseif ($can_box): ?>
                    <span>按箱买</span>
                <?php else: ?>
                    <span>散买（按件）</span>
                <?php endif; ?>
            </div>
            <div class="quantity-wrap">
                <label>数量：</label>
                <input type="number" id="qty" min="1" value="1" max="<?php echo $can_box && !$can_piece && !empty($product['box_pieces']) ? floor($product['stock']/max(1,(int)$product['box_pieces'])) : $product['stock']; ?>">
                <?php if ($can_box): ?><span class="unit-hint"><?php echo $can_piece ? '（件/箱）' : '箱'; ?></span><?php endif; ?>
            </div>
            <button class="btn-buy" id="btnAddCart" <?php echo $product['stock']<1 ? ' disabled' : ''; ?>>加入购物车</button>
            <?php else: ?>
            <div class="quantity-wrap">
                <label>数量：</label>
                <input type="number" id="qty" min="1" max="<?php echo $product['stock']; ?>" value="1">
            </div>
            <button class="btn-buy" onclick="addAndGo(<?php echo $product['id']; ?>,'<?php echo htmlspecialchars(addslashes($product['name'])); ?>',<?php echo $price_piece; ?>,'piece')" <?php echo $product['stock']<1 ? ' disabled' : ''; ?>>加入购物车</button>
            <?php endif; ?>
        </div>
    </div>
</main>
<script>
var productData = {
    id: <?php echo (int)$product['id']; ?>,
    name: <?php echo json_encode($product['name']); ?>,
    price: <?php echo (float)$product['price']; ?>,
    stock: <?php echo (int)$product['stock']; ?>,
    sell_type: <?php echo json_encode($sell_type); ?>,
    box_pieces: <?php echo $can_box ? (int)$product['box_pieces'] : 'null'; ?>,
    price_box: <?php echo $can_box && isset($product['price_box']) ? (float)$product['price_box'] : 'null'; ?>
};
function addAndGo(id,name,price,unit){
    var qty = parseInt(document.getElementById('qty').value)||1;
    var cart = JSON.parse(localStorage.getItem('cart')||'[]');
    var boxPieces = (unit==='box' && productData.box_pieces) ? productData.box_pieces : null;
    var priceBox = (unit==='box' && productData.price_box != null) ? productData.price_box : null;
    var item = { id:id, name:name, price: price, quantity:qty, unit: unit||'piece' };
    if(unit==='box' && boxPieces!=null){ item.price_box=priceBox; item.box_pieces=boxPieces; }
    var key = unit==='box' ? id+'_box' : id;
    var i = cart.findIndex(function(x){ return (x.unit==='box' ? x.id+'_box' : x.id) === key; });
    if(i>=0) cart[i].quantity += qty; else cart.push(item);
    localStorage.setItem('cart',JSON.stringify(cart));
    if(confirm('已加入购物车，是否前往购物车？')) location.href='<?php echo BASE_PATH; ?>cart.php';
}
(function(){
    var btn = document.getElementById('btnAddCart');
    if(!btn) return;
    btn.onclick = function(){
        var unitEl = document.querySelector('input[name="buy_unit"]:checked');
        var unit = unitEl ? unitEl.value : (productData.sell_type==='box' ? 'box' : 'piece');
        var qty = parseInt(document.getElementById('qty').value)||1;
        var maxStock = productData.stock;
        if(unit==='box' && productData.box_pieces) {
            var maxBox = Math.floor(maxStock / productData.box_pieces);
            if(qty > maxBox){ alert('库存不足，最多可买 '+maxBox+' 箱'); return; }
        } else if(unit==='piece' && qty > maxStock){ alert('库存不足'); return; }
        addAndGo(productData.id, productData.name, unit==='box' ? productData.price_box : productData.price, unit);
    };
})();
</script>
<?php require_once 'includes/footer.php'; ?>
