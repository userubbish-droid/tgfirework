<?php
require_once __DIR__ . '/../config.php';
session_start();
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$hasRole = false;
$hasAgentStatus = false;
$hasStatus = false;
try {
    $cols = $pdo->query("SHOW COLUMNS FROM customers")->fetchAll(PDO::FETCH_COLUMN);
    $hasRole = in_array('role', $cols);
    $hasAgentStatus = in_array('agent_status', $cols);
    $hasStatus = in_array('status', $cols);
} catch (Exception $e) {}

if (isset($_GET['approve']) && isset($_GET['id'])) {
    $cid = (int)$_GET['id'];
    if ($hasAgentStatus) {
        $pdo->prepare("UPDATE customers SET agent_status = 'approved' WHERE id = ?")->execute([$cid]);
    } else {
        $pdo->prepare("UPDATE customers SET status = 'approved' WHERE id = ?")->execute([$cid]);
    }
    header('Location: customers.php');
    exit;
}
if (isset($_GET['set_role']) && isset($_GET['id'])) {
    $cid = (int)$_GET['id'];
    $role = $_GET['set_role'] === 'agent' ? 'agent' : 'customer';
    if ($hasRole && $hasAgentStatus) {
        $pdo->prepare("UPDATE customers SET role = ?, agent_status = ? WHERE id = ?")
            ->execute([$role, $role === 'agent' ? 'approved' : null, $cid]);
    }
    header('Location: customers.php');
    exit;
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : '';
$sql = "SELECT id, email, name, phone, created_at";
if ($hasRole) $sql .= ", role";
if ($hasAgentStatus) $sql .= ", agent_status";
if ($hasStatus) $sql .= ", status";
$sql .= " FROM customers ORDER BY created_at DESC";
$params = [];
if ($hasRole && $filter === 'customer') {
    $sql = "SELECT id, email, name, phone, created_at";
    if ($hasRole) $sql .= ", role";
    if ($hasAgentStatus) $sql .= ", agent_status";
    if ($hasStatus) $sql .= ", status";
    $sql .= " FROM customers WHERE role = 'customer' ORDER BY created_at DESC";
}
if ($hasRole && $filter === 'agent') {
    $sql = "SELECT id, email, name, phone, created_at";
    if ($hasRole) $sql .= ", role";
    if ($hasAgentStatus) $sql .= ", agent_status";
    if ($hasStatus) $sql .= ", status";
    $sql .= " FROM customers WHERE role = 'agent' ORDER BY created_at DESC";
}
if ($hasRole && $hasAgentStatus && $filter === 'agent_pending') {
    $sql = "SELECT id, email, name, phone, created_at, role, agent_status";
    if ($hasStatus) $sql .= ", status";
    $sql .= " FROM customers WHERE role = 'agent' AND agent_status = 'pending' ORDER BY created_at DESC";
}
$customers = $pdo->query($sql)->fetchAll();

$pendingCount = 0;
if ($hasAgentStatus) {
    $r = $pdo->query("SELECT COUNT(*) FROM customers WHERE role = 'agent' AND agent_status = 'pending'");
    if ($r) $pendingCount = (int)$r->fetchColumn();
} elseif ($hasStatus) {
    $r = $pdo->query("SELECT COUNT(*) FROM customers WHERE status = 'pending'");
    if ($r) $pendingCount = (int)$r->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>客户管理 - 烟花网购后台</title>
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
        <a href="customers.php" class="active">客户管理</a>
        <a href="<?php echo BASE_PATH; ?>index.php" target="_blank">访问前台</a>
        <a href="logout.php">退出登录</a>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h2>客户管理</h2>
            <span>普通客户注册即可购物；Agent(批发) 需申请或由我方提升</span>
        </div>
        <div class="admin-card">
            <?php if ($hasRole): ?>
            <p style="margin-bottom:1rem;">
                <a href="customers.php" class="btn btn-sm <?php echo $filter === '' ? 'btn-primary' : ''; ?>" style="background:<?php echo $filter === '' ? '' : '#eee'; ?>">全部</a>
                <a href="customers.php?filter=customer" class="btn btn-sm <?php echo $filter === 'customer' ? 'btn-primary' : ''; ?>" style="background:<?php echo $filter === 'customer' ? '' : '#eee'; ?>">普通客户</a>
                <a href="customers.php?filter=agent" class="btn btn-sm <?php echo $filter === 'agent' ? 'btn-primary' : ''; ?>" style="background:<?php echo $filter === 'agent' ? '' : '#eee'; ?>">Agent(批发)</a>
                <a href="customers.php?filter=agent_pending" class="btn btn-sm <?php echo $filter === 'agent_pending' ? 'btn-primary' : ''; ?>" style="background:<?php echo $filter === 'agent_pending' ? '' : '#eee'; ?>">Agent 待审核 <?php if ($pendingCount > 0): ?>(<?php echo $pendingCount; ?>)<?php endif; ?></a>
            </p>
            <?php elseif ($hasStatus): ?>
            <p style="margin-bottom:1rem;">
                <a href="customers.php" class="btn btn-sm <?php echo $filter === '' ? 'btn-primary' : ''; ?>">全部</a>
                <a href="customers.php?status=pending" class="btn btn-sm">待审核 (<?php echo $pendingCount; ?>)</a>
                <a href="customers.php?status=approved" class="btn btn-sm">已通过</a>
            </p>
            <?php endif; ?>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>手机号</th>
                        <th>姓名</th>
                        <th>邮箱</th>
                        <?php if ($hasRole): ?><th>身份</th><?php endif; ?>
                        <?php if ($hasAgentStatus): ?><th>批发状态</th><?php endif; ?>
                        <?php if ($hasStatus && !$hasRole): ?><th>状态</th><?php endif; ?>
                        <th>注册时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($customers as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['phone'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($c['name']); ?></td>
                        <td><?php echo htmlspecialchars($c['email'] ?? ''); ?></td>
                        <?php if ($hasRole): ?>
                        <td>
                            <?php echo ($c['role'] ?? 'customer') === 'agent' ? 'Agent(批发)' : '普通客户'; ?>
                        </td>
                        <?php endif; ?>
                        <?php if ($hasAgentStatus): ?>
                        <td>
                            <?php if (($c['role'] ?? '') === 'agent'): ?>
                                <?php if (($c['agent_status'] ?? '') === 'pending'): ?>
                                    <span class="admin-badge pending">待审核</span>
                                    <a href="?approve=1&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary" style="margin-left:0.3rem;">通过</a>
                                <?php else: ?>
                                    <span class="admin-badge completed">已通过</span>
                                <?php endif; ?>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <?php if ($hasStatus && !$hasRole): ?>
                        <td>
                            <?php if (($c['status'] ?? '') === 'pending'): ?>
                                <span class="admin-badge pending">待审核</span>
                                <a href="?approve=1&id=<?php echo $c['id']; ?>" class="btn btn-sm btn-primary">通过</a>
                            <?php else: ?>
                                <span class="admin-badge completed">已通过</span>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                        <td><?php echo $c['created_at']; ?></td>
                        <td class="admin-actions">
                            <?php if ($hasRole): ?>
                                <?php if (($c['role'] ?? '') === 'customer'): ?>
                                    <a href="?set_role=agent&id=<?php echo $c['id']; ?>" onclick="return confirm('确认为该客户提升为 Agent(批发)？');">提升为 Agent</a>
                                <?php else: ?>
                                    <a href="?set_role=customer&id=<?php echo $c['id']; ?>" onclick="return confirm('确认为该客户改为普通客户？');">改为普通客户</a>
                                <?php endif; ?>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php if (empty($customers)): ?>
                <p style="padding:2rem; color:#888;">暂无客户</p>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html>
