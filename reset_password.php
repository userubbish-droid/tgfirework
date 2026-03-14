<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'index.php');
    exit;
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $phone = preg_replace('/\D/', '', $phone);
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    if (!$phone || !$password || !$password2) {
        $error = '请填写手机号和新密码';
    } elseif (strlen($phone) < 10 || strlen($phone) > 15) {
        $error = '请填写正确的手机号（10～15 位数字）';
    } elseif (strlen($password) < 6) {
        $error = '新密码至少 6 位';
    } elseif ($password !== $password2) {
        $error = '两次输入的密码不一致';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        if (!$user) {
            $error = '该手机号未注册，请先注册';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE customers SET password = ? WHERE id = ?")->execute([$hash, $user['id']]);
            $success = '密码已重置，请使用新密码登录。';
        }
    }
}

$pageTitle = '重置密码 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>重置密码</h2>
    <p>忘记密码时，输入注册手机号并设置新密码。</p>
    <?php if ($success): ?>
        <div class="alert" style="background:#d4edda;color:#155724;"><?php echo htmlspecialchars($success); ?></div>
        <a href="<?php echo BASE_PATH; ?>login.php" class="btn btn-primary">去登录</a>
    <?php else: ?>
        <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
        <form method="post" style="max-width:400px;">
            <div class="form-group">
                <label>手机号 *</label>
                <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="注册时使用的手机号">
            </div>
            <div class="form-group">
                <label>新密码 *</label>
                <input type="password" name="password" required minlength="6" placeholder="至少 6 位">
            </div>
            <div class="form-group">
                <label>确认新密码 *</label>
                <input type="password" name="password2" required minlength="6" placeholder="再次输入新密码">
            </div>
            <button type="submit" class="btn btn-primary">重置密码</button>
            <a href="<?php echo BASE_PATH; ?>login.php" class="btn" style="margin-left:0.5rem;">返回登录</a>
        </form>
    <?php endif; ?>
</main>
<?php require_once 'includes/footer.php'; ?>
