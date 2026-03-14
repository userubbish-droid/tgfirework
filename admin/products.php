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
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $uploadDir = __DIR__ . '/../uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'p_' . time() . '_' . uniqid() . '.' . $ext;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                $image = $filename;
            }
        }
    }
    if ($action === 'edit' && $id) {
        $params = [$name, $category_id, $description, $price, $stock, $is_active];
        $sql = "UPDATE products SET name=?, category_id=?, description=?, price=?, stock=?, is_active=?";
        if ($image) { $sql .= ", image=?"; $params[] = $image; }
        $params[] = $id;
        $sql .= " WHERE id=?";
        $pdo->prepare($sql)->execute($params);
    } else {
        $pdo->prepare("INSERT INTO products (name, category_id, description, price, stock, is_active, image) VALUES (?,?,?,?,?,?,?)")
            ->execute([$name, $category_id, $description, $price, $stock, $is_active, $image]);
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
                    <label>图片</label>
                    <input type="file" name="image" accept="image/*">
                    <?php if ($product && $product['image']): ?>
                        <p>当前：<img src="<?php echo BASE_PATH; ?>uploads/<?php echo htmlspecialchars($product['image']); ?>" alt="" style="max-height:80px;"></p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label><input type="checkbox" name="is_active" value="1" <?php echo (!$product || $product['is_active']) ? 'checked' : ''; ?>> 上架</label>
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="products.php" class="btn" style="margin-left:0.5rem;">取消</a>
            </form>
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
