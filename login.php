<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['customer_id'])) {
    $to = isset($_GET['from']) ? $_GET['from'] : 'index';
    if ($to === 'checkout') {
        header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'checkout.php');
    } else {
        header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (!$email || !$password) {
        $error = '请填写邮箱和密码';
    } else {
        $stmt = $pdo->prepare("SELECT id, email, password, name, role, status FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            $error = '邮箱或密码错误';
        } elseif (isset($user['status']) && $user['status'] !== 'approved') {
            $error = '您的账号尚未通过审核，请等待或联系客服。';
        } else {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_email'] = $user['email'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_role'] = $user['role'] ?? 'customer';
            $to = isset($_GET['from']) ? $_GET['from'] : 'index';
            if ($to === 'checkout') {
                header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'checkout.php');
            } else {
                header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'index.php');
            }
            exit;
        }
    }
}

$pageTitle = '登录 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>登录</h2>
    <p>登录后可下单、查看订单。</p>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" style="max-width:400px;">
        <div class="form-group">
            <label>邮箱</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label>密码</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-primary">登录</button>
        <a href="<?php echo SITE_URL ? SITE_URL.'/register.php' : 'register.php'; ?>?<?php echo isset($_GET['from']) ? 'from='.urlencode($_GET['from']) : ''; ?>" class="btn" style="margin-left:0.5rem;">没有账号？去注册</a>
    </form>
</main>
<?php require_once 'includes/footer.php'; ?>
