<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . (SITE_URL ? SITE_URL . '/' : '') . 'index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    if (!$email || !$password || !$name) {
        $error = '请填写邮箱、密码和姓名';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '邮箱格式不正确';
    } elseif (strlen($password) < 6) {
        $error = '密码至少 6 位';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = '该邮箱已注册，请直接登录';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            try {
                $pdo->prepare("INSERT INTO customers (email, password, name, phone, role, status) VALUES (?, ?, ?, ?, 'customer', 'approved')")
                    ->execute([$email, $hash, $name, $phone]);
            } catch (Exception $e) {
                $pdo->prepare("INSERT INTO customers (email, password, name, phone, status) VALUES (?, ?, ?, ?, 'approved')")
                    ->execute([$email, $hash, $name, $phone]);
            }
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['customer_role'] = 'customer';
            $_SESSION['customer_email'] = $email;
            $_SESSION['customer_name'] = $name;
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

$pageTitle = '注册 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>注册账号</h2>
    <p>注册后可下单购物。</p>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" style="max-width:400px;">
        <div class="form-group">
            <label>邮箱 *</label>
            <input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="用于登录">
        </div>
        <div class="form-group">
            <label>密码 *</label>
            <input type="password" name="password" required minlength="6" placeholder="至少 6 位">
        </div>
        <div class="form-group">
            <label>姓名 *</label>
            <input type="text" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" placeholder="收货人姓名">
        </div>
        <div class="form-group">
            <label>手机号</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="选填">
        </div>
        <button type="submit" class="btn btn-primary">注册</button>
        <a href="<?php echo SITE_URL ? SITE_URL.'/login.php' : 'login.php'; ?>?<?php echo isset($_GET['from']) ? 'from='.urlencode($_GET['from']) : ''; ?>" class="btn" style="margin-left:0.5rem;">已有账号？去登录</a>
    </form>
</main>
<?php require_once 'includes/footer.php'; ?>
