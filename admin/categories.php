<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$hasIconCols = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM categories")->fetchAll(PDO::FETCH_COLUMN);
    $hasIconCols = in_array('image', $cols) && in_array('name_en', $cols);
} catch (Exception $e) {}

$saved = false;
$error = '';
$uploadDir = __DIR__ . '/../uploads/';
if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

// 删除分类（无商品时）
if (isset($_GET['delete']) && ctype_digit($_GET['delete']) && $hasIconCols) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $cnt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
        $cnt->execute([$id]);
        if ((int)$cnt->fetchColumn() === 0) {
            $pdo->prepare("DELETE FROM categories WHERE id = ?")->execute([$id]);
            header('Location: categories.php');
            exit;
        }
        $error = '该分类下还有商品，无法删除';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasIconCols) {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $name_en = trim($_POST['name_en'] ?? '');
        if ($name === '') {
            $error = '请填写分类名称（中文）';
        } else {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    $filename = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $image = $filename;
                    }
                }
            }
            $maxSort = (int)$pdo->query("SELECT IFNULL(MAX(sort_order),0) FROM categories")->fetchColumn();
            $pdo->prepare("INSERT INTO categories (name, image, name_en, sort_order) VALUES (?, ?, ?, ?)")
                ->execute([$name, $image, $name_en ?: null, $maxSort + 10]);
            $saved = true;
        }
    }
    if ($action === 'edit' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $name = trim($_POST['name'] ?? '');
        $name_en = trim($_POST['name_en'] ?? '');
        if ($id > 0 && $name !== '') {
            $image = null;
            if (!empty($_FILES['image']['name'])) {
                $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                    $filename = 'cat_' . time() . '_' . uniqid() . '.' . $ext;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $uploadDir . $filename)) {
                        $image = $filename;
                    }
                }
            }
            if ($image) {
                $pdo->prepare("UPDATE categories SET name=?, name_en=?, image=? WHERE id=?")->execute([$name, $name_en ?: null, $image, $id]);
            } else {
                $pdo->prepare("UPDATE categories SET name=?, name_en=? WHERE id=?")->execute([$name, $name_en ?: null, $id]);
            }
            $saved = true;
        }
    }
}

$categories = [];
if ($hasIconCols) {
    $categories = $pdo->query("SELECT id, name, image, name_en, sort_order FROM categories ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
} else {
    $categories = $pdo->query("SELECT id, name, sort_order FROM categories ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>分类管理 - 烟花网购后台</title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/admin.css">
</head>
<body>
<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="brand">后台管理</div>
        <a href="index.php">仪表盘</a>
        <a href="products.php">商品管理</a>
        <a href="orders.php">订单管理</a>
        <a href="customers.php">客户管理</a>
        <a href="agent_rebate.php">Agent 回扣</a>
        <a href="delivery_settings.php">配送设置</a>
        <a href="banner.php">首页横幅 Banner</a>
        <a href="categories.php" class="active">分类管理</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>分类管理</h2>
        </div>
        <?php if ($saved): ?><div class="alert alert-success">已保存。</div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <?php if (!$hasIconCols): ?>
            <div class="admin-card">
                <p class="alert alert-error">请先在 phpMyAdmin 中执行 <strong>upgrade_categories_icon.sql</strong>，为分类表增加图标与英文名字段。</p>
            </div>
        <?php else: ?>
            <div class="admin-card">
                <p style="margin-bottom:1rem;">在此为每个分类设置<strong>图标图片</strong>和<strong>名称</strong>，前台会以卡片形式展示（图1 风格：图标 + 第一行英文名 + 第二行中文名）。</p>

                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>图标预览</th>
                            <th>中文名（第二行）</th>
                            <th>英文名（第一行）</th>
                            <th>排序</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $c): ?>
                        <tr>
                            <td><?php echo $c['id']; ?></td>
                            <td>
                                <?php
                                $imgPath = !empty($c['image']) ? (BASE_PATH . 'uploads/' . $c['image']) : (BASE_PATH . 'assets/img/placeholder.svg');
                                ?>
                                <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="" style="width:64px;height:64px;object-fit:cover;border-radius:8px;">
                            </td>
                            <td><?php echo htmlspecialchars($c['name']); ?></td>
                            <td><?php echo htmlspecialchars($c['name_en'] ?? ''); ?></td>
                            <td><?php echo (int)($c['sort_order'] ?? 0); ?></td>
                            <td>
                                <a href="#edit-<?php echo $c['id']; ?>">编辑</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <hr style="margin:1.5rem 0;">

                <h3 style="margin-bottom:0.75rem;">编辑分类（上传图标 + 名称）</h3>
                <?php foreach ($categories as $c): ?>
                <form method="post" enctype="multipart/form-data" style="border:1px solid #eee;padding:1rem;margin-bottom:1rem;border-radius:8px;" id="edit-<?php echo $c['id']; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                    <div style="display:grid;grid-template-columns:auto 1fr;gap:1rem;align-items:start;">
                        <div>
                            <?php $imgPath = !empty($c['image']) ? (BASE_PATH . 'uploads/' . $c['image']) : (BASE_PATH . 'assets/img/placeholder.svg'); ?>
                            <img src="<?php echo htmlspecialchars($imgPath); ?>" alt="" style="width:80px;height:80px;object-fit:cover;border-radius:10px;display:block;margin-bottom:0.5rem;">
                            <label class="form-group"><input type="file" name="image" accept="image/*"> 更换图标</label>
                        </div>
                        <div>
                            <div class="form-group">
                                <label>中文名（第二行，如：鞭炮系列）</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($c['name']); ?>" required style="max-width:240px;">
                            </div>
                            <div class="form-group">
                                <label>英文名（第一行，如：FIRECRACKER）</label>
                                <input type="text" name="name_en" value="<?php echo htmlspecialchars($c['name_en'] ?? ''); ?>" placeholder="选填" style="max-width:240px;">
                            </div>
                            <button type="submit" class="btn btn-primary">保存</button>
                        </div>
                    </div>
                </form>
                <?php endforeach; ?>

                <hr style="margin:1.5rem 0;">
                <h3 style="margin-bottom:0.75rem;">新增分类</h3>
                <form method="post" enctype="multipart/form-data" style="max-width:500px;">
                    <input type="hidden" name="action" value="add">
                    <div class="form-group">
                        <label>中文名（第二行） *</label>
                        <input type="text" name="name" required placeholder="如：鞭炮系列">
                    </div>
                    <div class="form-group">
                        <label>英文名（第一行，选填）</label>
                        <input type="text" name="name_en" placeholder="如：FIRECRACKER">
                    </div>
                    <div class="form-group">
                        <label>图标图片（选填，建议方图）</label>
                        <input type="file" name="image" accept="image/*">
                    </div>
                    <button type="submit" class="btn btn-primary">添加分类</button>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
