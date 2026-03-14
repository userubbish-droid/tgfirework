<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}
$categories = $pdo->query("SELECT id, name FROM categories ORDER BY sort_order")->fetchAll();
$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($action === 'delete' && $id) {
    $pdo->prepare("DELETE FROM products WHERE id = ?")->execute([$id]);
    header('Location: products.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'add' || $action === 'edit')) {
    $name = trim($_POST['name'] ?? '');
    $category_id = !empty($_POST['category_id']) ? (int)$_POST['category_id'] : null;
    $description = trim($_POST['description'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $stock = (int)($_POST['stock'] ?? 0);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $allow_self_pickup = isset($_POST['allow_self_pickup']) ? 1 : 0;
    $allow_lalamove = isset($_POST['allow_lalamove']) ? 1 : 0;
    $allow_mail = isset($_POST['allow_mail']) ? 1 : 0;
    $sell_type = in_array($_POST['sell_type'] ?? '', ['piece','box','both']) ? $_POST['sell_type'] : 'piece';
    $box_pieces = ($sell_type === 'box' || $sell_type === 'both') && isset($_POST['box_pieces']) ? max(1, (int)$_POST['box_pieces']) : null;
    $price_box = ($sell_type === 'box' || $sell_type === 'both') && isset($_POST['price_box']) ? (float)$_POST['price_box'] : null;
    $image = null;
    $video = null;
    $uploadDir = __DIR__ . '/../uploads/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $filename = 'p_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = $filename;
            }
        }
    }
    if (!empty($_FILES['video']['name'])) {
        $ext = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4','webm','mov','ogg'])) {
            $filename = 'v_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['video']['tmp_name'], $uploadDir . $filename)) {
                $video = $filename;
            }
        }
    }
    $hasDeliveryCols = false;
    $hasBoxCols = false;
    try {
        $cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
        $hasDeliveryCols = in_array('allow_self_pickup', $cols);
        $hasBoxCols = in_array('sell_type', $cols);
    } catch (Exception $e) {}
    if ($action === 'edit' && $id) {
        $params = [$name, $category_id, $description, $price, $stock, $is_active];
        $sql = "UPDATE products SET name=?, category_id=?, description=?, price=?, stock=?, is_active=?";
        if ($image) { $sql .= ", image=?"; $params[] = $image; }
        if ($video) { $sql .= ", video=?"; $params[] = $video; }
        if ($hasDeliveryCols) { $sql .= ", allow_self_pickup=?, allow_lalamove=?, allow_mail=?"; $params[] = $allow_self_pickup; $params[] = $allow_lalamove; $params[] = $allow_mail; }
        if ($hasBoxCols) { $sql .= ", sell_type=?, box_pieces=?, price_box=?"; $params[] = $sell_type; $params[] = $box_pieces; $params[] = $price_box; }
        $params[] = $id;
        $sql .= " WHERE id=?";
        $pdo->prepare($sql)->execute($params);
    } else {
        $fields = "name, category_id, description, price, stock, is_active, image, video";
        $placeholders = "?,?,?,?,?,?,?,?";
        $vals = [$name, $category_id, $description, $price, $stock, $is_active, $image, $video];
        if ($hasDeliveryCols) { $fields .= ", allow_self_pickup, allow_lalamove, allow_mail"; $placeholders .= ",?,?,?"; $vals[] = $allow_self_pickup; $vals[] = $allow_lalamove; $vals[] = $allow_mail; }
        if ($hasBoxCols) { $fields .= ", sell_type, box_pieces, price_box"; $placeholders .= ",?,?,?"; $vals[] = $sell_type; $vals[] = $box_pieces; $vals[] = $price_box; }
        $pdo->prepare("INSERT INTO products ($fields) VALUES ($placeholders)")->execute($vals);
    }
    header('Location: products.php');
    exit;
}

$product = null;
if ($action === 'edit' && $id) {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    if (!$product) { header('Location: products.php'); exit; }
}
$products = $pdo->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id ORDER BY p.id DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>商品管理 - 烟花网购后台</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">后台管理</div>
        <a href="index.php">仪表盘</a>
        <a href="products.php" class="active">商品管理</a>
        <a href="orders.php">订单管理</a>
        <a href="customers.php">客户管理</a>
        <a href="agent_rebate.php">Agent 回扣</a>
        <a href="delivery_settings.php">配送设置</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>商品管理</h2>
            <a href="?action=add" class="btn btn-primary btn-sm">添加商品</a>
        </div>
        <?php if ($action === 'add' || $action === 'edit'): ?>
        <div class="admin-card">
            <h3><?php echo $action === 'add' ? '添加商品' : '编辑商品'; ?></h3>
            <form method="post" enctype="multipart/form-data" style="max-width:500px;">
                <div class="form-group">
                    <label>商品名称 *</label>
                    <input type="text" name="name" required value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>">
                </div>
                <div class="form-group">
                    <label>分类</label>
                    <select name="category_id">
                        <option value="">-- 请选择 --</option>
                        <?php foreach ($categories as $c): ?>
                            <option value="<?php echo $c['id']; ?>" <?php echo ($product && $product['category_id'] == $c['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>描述</label>
                    <textarea name="description"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
                </div>
                <div class="form-group">
                    <label>价格 *</label>
                    <input type="number" name="price" step="0.01" required value="<?php echo $product ? $product['price'] : ''; ?>">
                </div>
                <div class="form-group">
                    <label>库存 *</label>
                    <input type="number" name="stock" min="0" required value="<?php echo $product ? $product['stock'] : '0'; ?>">
                </div>
                <div class="form-group">
                    <label>销售方式</label>
                    <select name="sell_type" id="sellType">
                        <option value="piece" <?php echo ($product['sell_type'] ?? 'piece') === 'piece' ? 'selected' : ''; ?>>仅散卖（按件）</option>
                        <option value="box" <?php echo ($product['sell_type'] ?? '') === 'box' ? 'selected' : ''; ?>>仅按箱</option>
                        <option value="both" <?php echo ($product['sell_type'] ?? '') === 'both' ? 'selected' : ''; ?>>箱 + 散都可</option>
                    </select>
                </div>
                <div class="form-group box-fields">
                    <label>每箱件数</label>
                    <input type="number" name="box_pieces" min="1" value="<?php echo isset($product['box_pieces']) && $product['box_pieces'] ? (int)$product['box_pieces'] : ''; ?>" placeholder="1箱=多少件">
                </div>
                <div class="form-group box-fields">
                    <label>每箱价格（元）</label>
                    <input type="number" name="price_box" step="0.01" min="0" value="<?php echo isset($product['price_box']) && $product['price_box'] !== null && $product['price_box'] !== '' ? $product['price_box'] : ''; ?>" placeholder="按箱售价">
                </div>
                <div class="form-group">
                    <label>图片</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if ($product && !empty($product['image'])): ?>
                        <p>当前：<img src="<?php echo BASE_PATH; ?>uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="" style="max-height:80px;"></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>视频</label>
                    <input type="file" name="video" accept="video/mp4,video/webm,video/quicktime,video/ogg">
                    <span style="color:#666;font-size:0.9em;">支持 mp4、webm、mov、ogg</span>
                    <?php if ($product && !empty($product['video'])): ?>
                        <p>当前：<a href="<?php echo BASE_PATH; ?>uploads/<?php echo htmlspecialchars($product['video']); ?>" target="_blank"><?php echo htmlspecialchars($product['video']); ?></a></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>配送方式（该商品支持的方式，勾选后前台结账可选）</label>
                    <label style="display:block;"><input type="checkbox" name="allow_self_pickup" value="1" <?php echo (!isset($product['allow_self_pickup']) || $product['allow_self_pickup']) ? 'checked' : ''; ?>> 自取</label>
                    <label style="display:block;"><input type="checkbox" name="allow_lalamove" value="1" <?php echo (!isset($product['allow_lalamove']) || $product['allow_lalamove']) ? 'checked' : ''; ?>> Lalamove</label>
                    <label style="display:block;"><input type="checkbox" name="allow_mail" value="1" <?php echo (!isset($product['allow_mail']) || $product['allow_mail']) ? 'checked' : ''; ?>> 邮寄</label>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?php echo (!$product || $product['is_active']) ? 'checked' : ''; ?>> 上架</label>
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="products.php" class="btn" style="margin-left:0.5rem;">取消</a>
            </form>
            <script>
            (function(){
                var sel = document.getElementById('sellType');
                var boxFields = document.querySelectorAll('.box-fields');
                function toggle(){ var v = sel ? sel.value : 'piece'; boxFields.forEach(function(el){ el.style.display = (v === 'box' || v === 'both') ? '' : 'none'; }); }
                if(sel){ sel.addEventListener('change', toggle); toggle(); }
            })();
            </script>
        </div>
        <?php endif; ?>
        <div class="admin-card">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th><th>名称</th><th>分类</th><th>价格</th><th>库存</th><th>状态</th><th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td><?php echo $p['id']; ?></td>
                        <td><?php echo htmlspecialchars($p['name']); ?></td>
                        <td><?php echo htmlspecialchars($p['category_name'] ?? '-'); ?></td>
                        <td>¥ <?php echo number_format($p['price'], 2); ?></td>
                        <td><?php echo $p['stock']; ?></td>
                        <td><?php echo $p['is_active'] ? '上架' : '下架'; ?></td>
                        <td class="admin-actions">
                            <a href="?action=edit&id=<?php echo $p['id']; ?>">编辑</a>
                            <a href="?action=delete&id=<?php echo $p['id']; ?>" onclick="return confirm('确定删除？');">删除</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
