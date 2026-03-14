<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new = $_POST['new_password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    if (!$current || !$new || !$confirm) {
        $error = '请填写全部三项';
    } elseif (strlen($new) < 6) {
        $error = '新密码至少 6 位';
    } elseif ($new !== $confirm) {
        $error = '两次输入的新密码不一致';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $row = $stmt->fetch();
        if (!$row || !password_verify($current, $row['password'])) {
            $error = '当前密码错误';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE admins SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['admin_id']]);
            $msg = '密码已修改，请使用新密码登录。';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>修改密码 - 烟花网购后台</title>
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
        <a href="change_password.php" class="active">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>修改密码</h2>
            <span><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
        </div>
        <div class="admin-card" style="max-width:400px;">
            <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
            <form method="post">
                <div class="form-group">
                    <label>当前密码</label>
                    <input type="password" name="current_password" required autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label>新密码</label>
                    <input type="password" name="new_password" required minlength="6" autocomplete="new-password" placeholder="至少 6 位">
                </div>
                <div class="form-group">
                    <label>确认新密码</label>
                    <input type="password" name="confirm_password" required minlength="6" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn-primary">保存</button>
                <a href="index.php" class="btn" style="margin-left:0.5rem;">返回</a>
            </form>
        </div>
    </main>
</div>
</body>
</html>
