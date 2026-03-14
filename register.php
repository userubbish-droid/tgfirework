<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = preg_replace('/\s+/', '', $phone);
    $phoneNormalized = preg_replace('/\D/', '', $phone);
    if (!$phone || !$password || !$name) {
        $error = '请填写手机号、密码和姓名';
    } elseif (strlen($phoneNormalized) < 10 || strlen($phoneNormalized) > 15) {
        $error = '请填写正确的手机号（10～15 位数字，可带 + 或空格）';
    } elseif (strlen($password) < 6) {
        $error = '密码至少 6 位';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM customers WHERE phone = ?");
        $stmt->execute([$phoneNormalized]);
        if ($stmt->fetch()) {
            $error = '该手机号已注册，请直接登录';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $emailVal = $email !== '' ? $email : null;
            try {
                $pdo->prepare("INSERT INTO customers (phone, email, password, name, role, status) VALUES (?, ?, ?, ?, 'customer', 'approved')")
                    ->execute([$phoneNormalized, $emailVal, $hash, $name]);
            } catch (Exception $e) {
                $pdo->prepare("INSERT INTO customers (phone, email, password, name, status) VALUES (?, ?, ?, ?, 'approved')")
                    ->execute([$phoneNormalized, $emailVal, $hash, $name]);
            }
            $_SESSION['customer_id'] = $pdo->lastInsertId();
            $_SESSION['customer_role'] = 'customer';
            $_SESSION['customer_phone'] = $phoneNormalized;
            $_SESSION['customer_name'] = $name;
            $to = isset($_GET['from']) ? $_GET['from'] : 'index';
            if ($to === 'checkout') {
                header('Location: ' . BASE_PATH . 'checkout.php');
            } else {
                header('Location: ' . BASE_PATH . 'index.php');
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
    <p>使用手机号注册，注册后可下单购物。</p>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" style="max-width:400px;">
        <div class="form-group">
            <label>手机号 *</label>
            <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="支持 +60、+86 等，10～15 位数字">
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
            <label>邮箱</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" placeholder="选填，用于接收通知">
        </div>
        <button type="submit" class="btn btn-primary">注册</button>
        <a href="<?php echo BASE_PATH; ?>login.php?<?php echo isset($_GET['from']) ? 'from='.urlencode($_GET['from']) : ''; ?>" class="btn" style="margin-left:0.5rem;">已有账号？去登录</a>
    </form>
</main>
<?php require_once 'includes/footer.php'; ?>
