<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$saved = false;
$error = '';

// 处理删除
if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        try {
            $pdo->prepare("DELETE FROM home_banners WHERE id = ?")->execute([$id]);
            header('Location: banner.php');
            exit;
        } catch (Exception $e) {
            $error = '删除失败：' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 新增 banner
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] === UPLOAD_ERR_OK) {
            $tmp = $_FILES['banner_image']['tmp_name'];
            $name = $_FILES['banner_image']['name'];
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (!in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $error = '图片格式仅支持 jpg/jpeg/png/gif/webp';
            } else {
                $targetDir = __DIR__ . '/../uploads/';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $newName = 'home_banner_' . date('YmdHis') . '.' . $ext;
                $targetPath = $targetDir . $newName;
                if (move_uploaded_file($tmp, $targetPath)) {
                    $relPath = 'uploads/' . $newName;
                    try {
                        $maxSort = (int)$pdo->query("SELECT IFNULL(MAX(sort_order),0) FROM home_banners")->fetchColumn();
                        $stmt = $pdo->prepare("INSERT INTO home_banners (image, sort_order, is_active) VALUES (?, ?, 1)");
                        $stmt->execute([$relPath, $maxSort + 10]);
                        $saved = true;
                    } catch (Exception $e) {
                        $error = '保存失败：' . $e->getMessage();
                    }
                } else {
                    $error = '图片上传失败，请重试';
                }
            }
        } else {
            $error = '请选择要上传的图片';
        }
    }
    // 保存列表（排序/启用）
    if (isset($_POST['action']) && $_POST['action'] === 'save_list' && isset($_POST['banners']) && is_array($_POST['banners'])) {
        try {
            $stmt = $pdo->prepare("UPDATE home_banners SET sort_order = ?, is_active = ? WHERE id = ?");
            foreach ($_POST['banners'] as $id => $data) {
                $id = (int)$id;
                if ($id <= 0) continue;
                $sort = isset($data['sort_order']) ? (int)$data['sort_order'] : 0;
                $active = isset($data['is_active']) ? 1 : 0;
                $stmt->execute([$sort, $active, $id]);
            }
            $saved = true;
        } catch (Exception $e) {
            $error = '保存失败：' . $e->getMessage();
        }
    }
}

// 读取所有 banner
$banners = [];
try {
    $banners = $pdo->query("SELECT id, image, sort_order, is_active FROM home_banners ORDER BY sort_order, id")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // 表不存在也没关系，只是不显示列表
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>首页横幅设置 - 烟花网购后台</title>
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
        <a href="banner.php" class="active">首页横幅 Banner</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>首页横幅 Banner</h2>
        </div>
        <?php if ($saved): ?>
            <div class="alert alert-success">已保存。</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="admin-card" style="max-width:900px;">
            <p style="margin-bottom:1rem;">可以上传多张首页横幅图（Banner1、Banner2…），前台会轮播显示。数值小的排在前面。</p>

            <?php if (!empty($banners)): ?>
                <form method="post" style="margin-bottom:1.5rem;">
                    <input type="hidden" name="action" value="save_list">
                    <table class="admin-table">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>预览</th>
                            <th>排序（越小越前）</th>
                            <th>启用</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($banners as $b): ?>
                            <tr>
                                <td><?php echo $b['id']; ?></td>
                                <td>
                                    <img src="<?php echo BASE_PATH . htmlspecialchars($b['image']); ?>" alt="" style="max-width:260px;border-radius:8px;">
                                </td>
                                <td>
                                    <input type="number" name="banners[<?php echo $b['id']; ?>][sort_order]" value="<?php echo (int)$b['sort_order']; ?>" style="width:80px;">
                                </td>
                                <td>
                                    <input type="checkbox" name="banners[<?php echo $b['id']; ?>][is_active]" value="1" <?php echo $b['is_active'] ? 'checked' : ''; ?>>
                                </td>
                                <td>
                                    <a href="banner.php?delete=<?php echo $b['id']; ?>" onclick="return confirm('确定删除这张横幅？');">删除</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <button type="submit" class="btn btn-primary">保存排序与启用</button>
                </form>
            <?php endif; ?>

            <hr style="margin:1.5rem 0;">

            <h3 style="margin-bottom:0.75rem;">新增横幅</h3>
            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label>选择横幅图片</label>
                    <input type="file" name="banner_image" accept="image/*">
                    <small style="color:#666;">建议宽图，比例接近 3:1，例如 1200×400 像素。</small>
                </div>
                <button type="submit" class="btn btn-primary">上传并添加</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

