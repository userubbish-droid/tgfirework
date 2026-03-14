<?php
require_once 'config.php';
session_start();
if (isset($_SESSION['customer_id'])) {
    $to = isset($_GET['from']) ? $_GET['from'] : 'index';
    if ($to === 'checkout') {
        header('Location: ' . BASE_PATH . 'checkout.php');
    } else {
        header('Location: ' . BASE_PATH . 'index.php');
    }
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = trim($_POST['phone'] ?? '');
    $phone = preg_replace('/\D/', '', $phone);
    $password = $_POST['password'] ?? '';
    if (!$phone || !$password) {
        $error = '请填写手机号和密码';
    } else {
        $stmt = $pdo->prepare("SELECT id, phone, password, name, role, status FROM customers WHERE phone = ?");
        $stmt->execute([$phone]);
        $user = $stmt->fetch();
        if (!$user || !password_verify($password, $user['password'])) {
            $error = '手机号或密码错误';
        } elseif (isset($user['status']) && $user['status'] !== 'approved') {
            $error = '您的账号尚未通过审核，请等待或联系客服。';
        } else {
            $_SESSION['customer_id'] = $user['id'];
            $_SESSION['customer_phone'] = $user['phone'];
            $_SESSION['customer_name'] = $user['name'];
            $_SESSION['customer_role'] = $user['role'] ?? 'customer';
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

$pageTitle = '登录 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>登录</h2>
    <p>使用手机号和密码登录，可下单、查看订单。</p>
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" style="max-width:400px;">
        <div class="form-group">
            <label>手机号</label>
            <input type="tel" name="phone" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" placeholder="用于登录的手机号">
        </div>
        <div class="form-group">
            <label>密码</label>
            <input type="password" name="password" required placeholder="登录密码">
        </div>
        <button type="submit" class="btn btn-primary">登录</button>
        <a href="<?php echo BASE_PATH; ?>register.php?<?php echo isset($_GET['from']) ? 'from='.urlencode($_GET['from']) : ''; ?>" class="btn" style="margin-left:0.5rem;">没有账号？去注册</a>
    </form>
</main>
<?php require_once 'includes/footer.php'; ?>
