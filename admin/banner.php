<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$saved = false;
$error = '';
$banner_image = '';

// 读取当前设置
try {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'home_banner_image' LIMIT 1");
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        $banner_image = $row['setting_value'];
    }
} catch (Exception $e) {
    // settings 表不存在时忽略，页面仍可打开
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
                $banner_image = 'uploads/' . $newName; // 存相对路径
            } else {
                $error = '图片上传失败，请重试';
            }
        }
    }

    if ($error === '') {
        try {
            $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('home_banner_image', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute([$banner_image]);
            $saved = true;
        } catch (Exception $e) {
            $error = '保存失败：' . $e->getMessage();
        }
    }
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
            <div class="alert alert-success">已保存首页横幅。</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <div class="admin-card" style="max-width:720px;">
            <p style="margin-bottom:1rem;">在这里上传首页顶部的大图（类似你发的 Bear 爆竹 banner）。建议尺寸比例接近宽:高 = 3:1。</p>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>当前横幅</label>
                    <?php if ($banner_image): ?>
                        <div style="margin-bottom:0.5rem;">
                            <img src="<?php echo BASE_PATH . htmlspecialchars($banner_image); ?>" alt="首页横幅" style="max-width:100%;border-radius:12px;">
                        </div>
                    <?php else: ?>
                        <p style="color:#666;">尚未上传横幅图片。</p>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>上传新横幅图片</label>
                    <input type="file" name="banner_image" accept="image/*">
                    <small style="color:#666;">选择新图片后保存即可替换旧横幅，不选则保持不变。</small>
                </div>
                <button type="submit" class="btn btn-primary">保存横幅</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>

