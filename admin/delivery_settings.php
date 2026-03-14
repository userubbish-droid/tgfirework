<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/delivery_helpers.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$labels = ['self_pickup' => '自取', 'lalamove' => 'Lalamove', 'mail' => '邮寄'];
$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keys = [
        'delivery_self_pickup_enabled', 'delivery_self_pickup_date_from', 'delivery_self_pickup_date_to',
        'delivery_lalamove_enabled', 'delivery_lalamove_date_from', 'delivery_lalamove_date_to',
        'delivery_mail_enabled', 'delivery_mail_date_from', 'delivery_mail_date_to',
    ];
    $stmt = $pdo->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($keys as $k) {
        $v = isset($_POST[$k]) ? trim($_POST[$k]) : '0';
        if ($k === 'delivery_self_pickup_enabled' || $k === 'delivery_lalamove_enabled' || $k === 'delivery_mail_enabled') {
            $v = isset($_POST[$k]) && $_POST[$k] ? '1' : '0';
        }
        $stmt->execute([$k, $v]);
    }
    $saved = true;
}

$settings = getDeliverySettings($pdo);
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>配送设置 - 烟花网购后台</title>
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
        <a href="delivery_settings.php" class="active">配送设置</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>配送设置</h2>
        </div>
        <?php if ($saved): ?><div class="alert alert-success">已保存。</div><?php endif; ?>
        <div class="admin-card">
            <p style="margin-bottom:1rem;">在此开启/关闭各配送方式，并可选填开放日期范围（不填表示一直开放）。商品是否支持该方式在「商品管理」中按商品设置。</p>
            <form method="post" style="max-width:600px;">
                <?php foreach (['self_pickup', 'lalamove', 'mail'] as $key): ?>
                <div class="form-group" style="border:1px solid #eee;padding:1rem;margin-bottom:1rem;border-radius:8px;">
                    <h4><?php echo htmlspecialchars($labels[$key]); ?></h4>
                    <label>
                        <input type="checkbox" name="delivery_<?php echo $key; ?>_enabled" value="1" <?php echo $settings[$key]['enabled'] ? 'checked' : ''; ?>>
                        开放此配送方式
                    </label>
                    <div style="display:flex;gap:1rem;margin-top:0.5rem;flex-wrap:wrap;">
                        <span>开放时间：</span>
                        <input type="date" name="delivery_<?php echo $key; ?>_date_from" value="<?php echo htmlspecialchars($settings[$key]['date_from']); ?>" placeholder="开始日期">
                        <span>至</span>
                        <input type="date" name="delivery_<?php echo $key; ?>_date_to" value="<?php echo htmlspecialchars($settings[$key]['date_to']); ?>" placeholder="结束日期">
                        <span style="color:#666;font-size:0.9em;">不填表示一直开放</span>
                    </div>
                </div>
                <?php endforeach; ?>
                <button type="submit" class="btn btn-primary">保存设置</button>
            </form>
        </div>
    </main>
</div>
</body>
</html>
