<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$hasRole = false;
$hasDefaultRebate = false;
$hasRebateTable = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    $hasRole = in_array('role', $cols);
    $hasDefaultRebate = in_array('default_rebate', $cols);
    $pdo->query("SELECT 1 FROM agent_product_rebate LIMIT 1");
    $hasRebateTable = true;
} catch (Exception $e) {}

$agents = [];
if ($hasRole) {
    $agents = $pdo->query("SELECT id, name, phone, default_rebate FROM customers WHERE role = 'agent' ORDER BY id")->fetchAll();
}
$products = $pdo->query("SELECT id, name, price, price_box, sell_type, box_pieces FROM products ORDER BY id")->fetchAll();

$selected_agent_id = isset($_GET['agent_id']) ? (int)$_GET['agent_id'] : (isset($_POST['agent_id']) ? (int)$_POST['agent_id'] : 0);
$selected_agent = null;
foreach ($agents as $a) {
    if ((int)$a['id'] === $selected_agent_id) { $selected_agent = $a; break; }
}

$saved = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $hasRebateTable && isset($_POST['save_special_rebate']) && $selected_agent_id) {
    $pdo->prepare("DELETE FROM agent_product_rebate WHERE customer_id = ?")->execute([$selected_agent_id]);
    $ins = $pdo->prepare("INSERT INTO agent_product_rebate (customer_id, product_id, rebate_piece, rebate_box) VALUES (?, ?, ?, ?)");
    $rebates = $_POST['rebates'] ?? [];
    foreach ($rebates as $pid => $r) {
        $pid = (int)$pid;
        if ($pid <= 0) continue;
        $piece = isset($r['piece']) && $r['piece'] !== '' ? max(0, (float)$r['piece']) : null;
        $box = isset($r['box']) && $r['box'] !== '' ? max(0, (float)$r['box']) : null;
        if ($piece !== null || $box !== null) {
            $ins->execute([$selected_agent_id, $pid, $piece, $box]);
        }
    }
    $saved = true;
}

$special_map = [];
if ($hasRebateTable && $selected_agent_id) {
    $rows = $pdo->prepare("SELECT product_id, rebate_piece, rebate_box FROM agent_product_rebate WHERE customer_id = ?");
    $rows->execute([$selected_agent_id]);
    while ($r = $rows->fetch(PDO::FETCH_ASSOC)) {
        $special_map[(int)$r['product_id']] = ['piece' => $r['rebate_piece'], 'box' => $r['rebate_box']];
    }
}
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
        <?php if ($saved): ?><div class="alert alert-success">已保存该 Agent 的特别回扣。</div><?php endif; ?>
        <div class="admin-card">
            <p style="margin-bottom:0.5rem;"><strong>规则：</strong>每位 Agent 的<strong>默认回扣</strong>在「<a href="customers.php?filter=agent">客户管理 → Agent(批发)</a>」设置（如 agent1 默认 250，全部商品适用）。</p>
            <p style="margin-bottom:1rem;">下面<strong>先选 Agent</strong>，再为该 Agent 的<strong>指定商品</strong>设<strong>特别回扣</strong>（如 agent1 的「红炮」扣 180，其他用默认 250；agent2 的某些手持扣 150，其他未设则无回扣）。</p>
            <form method="get" style="margin-bottom:1rem;">
                <label>选择 Agent：</label>
                <select name="agent_id" onchange="this.form.submit()">
                    <option value="">-- 请选择 --</option>
                    <?php foreach ($agents as $a): ?>
                    <option value="<?php echo $a['id']; ?>" <?php echo $selected_agent_id == $a['id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($a['name'] ?: $a['phone']); ?> (ID:<?php echo $a['id']; ?>)
                        <?php if ($hasDefaultRebate && isset($a['default_rebate']) && $a['default_rebate'] !== null && $a['default_rebate'] !== ''): ?> · 默认回扣 ¥<?php echo $a['default_rebate']; ?><?php endif; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm">确定</button>
            </form>
            <?php if (!$hasRebateTable): ?>
                <p class="alert alert-error">请先执行 upgrade_agent_product_rebate.sql 创建「按 Agent 按商品」回扣表。</p>
            <?php elseif ($selected_agent): ?>
                <p style="margin-bottom:0.5rem;"><strong>当前：<?php echo htmlspecialchars($selected_agent['name'] ?: $selected_agent['phone']); ?></strong>
                    <?php if ($hasDefaultRebate && isset($selected_agent['default_rebate']) && $selected_agent['default_rebate'] !== null && $selected_agent['default_rebate'] !== ''): ?>
                        · 默认回扣 <strong>¥<?php echo $selected_agent['default_rebate']; ?></strong>（未填特别回扣的商品用此）
                    <?php else: ?>
                        · 未设默认回扣（未填特别回扣的商品无回扣）
                    <?php endif; ?>
                </p>
                <form method="post">
                    <input type="hidden" name="agent_id" value="<?php echo $selected_agent_id; ?>">
                    <input type="hidden" name="save_special_rebate" value="1">
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
                            <?php foreach ($products as $p):
                                $sp = $special_map[$p['id']] ?? [];
                                $val_piece = isset($sp['piece']) && $sp['piece'] !== null && $sp['piece'] !== '' ? $sp['piece'] : '';
                                $val_box = isset($sp['box']) && $sp['box'] !== null && $sp['box'] !== '' ? $sp['box'] : '';
                            ?>
                            <tr>
                                <td><?php echo $p['id']; ?></td>
                                <td><?php echo htmlspecialchars($p['name']); ?></td>
                                <td>¥ <?php echo number_format($p['price'], 2); ?></td>
                                <td><?php echo (isset($p['price_box']) && $p['price_box'] !== null && $p['price_box'] !== '') ? '¥ ' . number_format($p['price_box'], 2) : '—'; ?></td>
                                <td><input type="number" name="rebates[<?php echo $p['id']; ?>][piece]" step="0.01" min="0" value="<?php echo htmlspecialchars($val_piece); ?>" placeholder="不填=用默认" style="width:90px;"></td>
                                <td><input type="number" name="rebates[<?php echo $p['id']; ?>][box]" step="0.01" min="0" value="<?php echo htmlspecialchars($val_box); ?>" placeholder="不填=用默认" style="width:90px;"></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p style="margin-top:1rem;"><button type="submit" class="btn btn-primary">保存该 Agent 的特别回扣</button></p>
                </form>
            <?php elseif ($selected_agent_id && !$selected_agent): ?>
                <p class="alert alert-error">未找到该 Agent。</p>
            <?php else: ?>
                <p style="color:#666;">请先在上方选择要设置特别回扣的 Agent。</p>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
