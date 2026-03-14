<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$hasRebateCols = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM products")->fetchAll(PDO::FETCH_COLUMN);
    $hasRebateCols = in_array('agent_rebate', $cols);
} catch (Exception $e) {}

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasRebateCols && !empty($_POST['rebates'])) {
    $rebates = $_POST['rebates'];
    $stmt = $pdo->prepare("UPDATE products SET agent_rebate = ?, agent_rebate_box = ? WHERE id = ?");
    foreach ($rebates as $pid => $r) {
        $pid = (int)$pid;
        if ($pid <= 0) continue;
        $rebate = isset($r['piece']) && $r['piece'] !== '' ? max(0, (float)$r['piece']) : null;
        $rebateBox = isset($r['box']) && $r['box'] !== '' ? max(0, (float)$r['box']) : null;
        $stmt->execute([$rebate, $rebateBox, $pid]);
    }
    $saved = true;
}

$products = $pdo->query("SELECT id, name, price, price_box, sell_type, box_pieces, agent_rebate, agent_rebate_box FROM products ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent 回扣 - 烟花网购后台</title>
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
        <a href="agent_rebate.php" class="active">Agent 回扣</a>
        <a href="delivery_settings.php">配送设置</a>
        <a href="change_password.php">修改密码</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>Agent 回扣</h2>
        </div>
        <?php if ($saved): ?><div class="alert alert-success">已保存。</div><?php endif; ?>
        <?php if (!$hasRebateCols): ?>
            <p class="alert alert-error">数据库尚未支持回扣字段，请先执行 upgrade_agent_rebate.sql。</p>
        <?php else: ?>
            <div class="admin-card">
                <p style="margin-bottom:0.5rem;"><strong>规则：</strong>每位 Agent 的<strong>默认回扣</strong>在「<a href="customers.php?filter=agent">客户管理 → Agent(批发)</a>」里设置（如 220 元，全部商品适用）。</p>
                <p style="margin-bottom:1rem;">下面为<strong>指定商品</strong>设置<strong>特别回扣</strong>（如 180）：被选中的商品用特别回扣，其余商品用该 Agent 的默认回扣。不填特别回扣则用默认回扣。</p>
                <form method="post">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>商品名称</th>
                                <th>前台件价</th>
                                <th>前台箱价</th>
                                <th>件价特别回扣（元）</th>
                                <th>箱价特别回扣（元）</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($products as $p): ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td>¥ <?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo (isset($p['price_box']) && $p['price_box'] !== null && $p['price_box'] !== '') ? '¥ ' . number_format($p['price_box'], 2) : '—'; ?></td>
                                <td><input type="number" name="rebates[<?php echo $p['id']; ?>][piece]" step="0.01" min="0" value="<?php echo isset($p['agent_rebate']) && $p['agent_rebate'] !== null && $p['agent_rebate'] !== '' ? $p['agent_rebate'] : ''; ?>" placeholder="不填=用默认" style="width:90px;"></td>
                                <td><input type="number" name="rebates[<?php echo $p['id']; ?>][box]" step="0.01" min="0" value="<?php echo isset($p['agent_rebate_box']) && $p['agent_rebate_box'] !== null && $p['agent_rebate_box'] !== '' ? $p['agent_rebate_box'] : ''; ?>" placeholder="不填=用默认" style="width:90px;"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="margin-top:1rem;"><button type="submit" class="btn btn-primary">保存回扣设置</button></p>
                </form>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>
