<?php
/**
 * 一键重置管理员 admin 的密码为 admin123
 * 用浏览器访问一次后，请立即删除本文件！
 */
require_once __DIR__ . '/../config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm'])) {
    $newPassword = 'admin123';
    $hash = password_hash($newPassword, PASSWORD_DEFAULT);
    try {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE username = 'admin'");
        $stmt->execute();
        $row = $stmt->fetch();
        if ($row) {
            $pdo->prepare("UPDATE admins SET password = ? WHERE username = 'admin'")->execute([$hash]);
            $msg = '已把用户 admin 的密码重置为：admin123。请立即删除本文件 reset_admin.php，然后去登录。';
        } else {
            $pdo->prepare("INSERT INTO admins (username, password) VALUES ('admin', ?)")->execute([$hash]);
            $msg = '已创建用户 admin，密码：admin123。请立即删除本文件 reset_admin.php，然后去登录。';
        }
    } catch (Exception $e) {
        $msg = '失败：' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>重置管理员密码</title>
    <style>body{font-family:sans-serif;max-width:400px;margin:2rem auto;padding:1rem;} .btn{background:#c41e3a;color:#fff;padding:0.5rem 1rem;border:none;cursor:pointer;} .btn:hover{background:#9a1830;} .alert{background:#d4edda;padding:0.75rem;margin:1rem 0;border-radius:4px;}</style>
</head>
<body>
    <h2>重置管理员密码</h2>
    <?php if ($msg): ?>
        <p class="alert"><?php echo htmlspecialchars($msg); ?></p>
        <p><a href="login.php">去登录</a></p>
    <?php else: ?>
        <p>将把用户 <strong>admin</strong> 的密码设为 <strong>admin123</strong>。</p>
        <form method="post">
            <input type="hidden" name="confirm" value="1">
            <button type="submit" class="btn">确认重置</button>
        </form>
    <?php endif; ?>
</body>
</html>
