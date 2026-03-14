<?php
require_once 'config.php';
session_start();
if (empty($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'login.php?from=change_password');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (!$current || !$password || !$password2) {
        $error = '请填写当前密码和新密码';
    } elseif (strlen($password) < 6) {
        $error = '新密码至少 6 位';
    } elseif ($password !== $password2) {
        $error = '两次输入的新密码不一致';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM customers WHERE id = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($current, $user['password'])) {
            $error = '当前密码错误';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?")->execute([$hash, $_SESSION['customer_id']]);
            $success = '密码已修改，请使用新密码登录。';
        }
    }
}

$pageTitle = '修改密码 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>修改密码</h2>
    <?php if ($success): ?>
        <div class="alert" style="background:#d4edda;color:#155724;"><?php echo htmlspecialchars($success); ?></div>
        <a href="<?php echo BASE_PATH; ?>index.php" class="btn btn-primary">返回首页</a>
    <?php else: ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" style="max-width:400px;">
            <div class="form-group">
                <label>当前密码 *</label>
                <input type="password" name="current_password" required placeholder="请输入当前登录密码">
            </div>
            <div class="form-group">
                <label>新密码 *</label>
                <input type="password" name="password" required minlength="6" placeholder="至少 6 位">
            </div>
            <div class="form-group">
                <label>确认新密码 *</label>
                <input type="password" name="password2" required minlength="6" placeholder="再次输入新密码">
            </div>
            <button type="submit" class="btn btn-primary">保存新密码</button>
            <a href="<?php echo BASE_PATH; ?>my_orders.php" class="btn" style="margin-left:0.5rem;">取消</a>
        </form>
    <?php endif; ?>
</main>
<?php require_once 'includes/footer.php'; ?>
