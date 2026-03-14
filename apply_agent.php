<?php
require_once 'config.php';
session_start();
if (!isset($_SESSION['customer_id'])) {
    header('Location: ' . BASE_PATH . 'login.php?from=apply_agent');
    exit;
}

$msg = '';
$stmt = $pdo->prepare("SELECT id, role, agent_status FROM customers WHERE id = ?");
$stmt->execute([$_SESSION['customer_id']]);
$me = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($me['role'] === 'agent') {
        if ($me['agent_status'] === 'pending') {
            $msg = '您已提交过批发申请，请等待审核。';
        } else {
            $msg = '您已是批发客户。';
        }
    } else {
        try {
            $pdo->prepare("UPDATE customers SET role = 'agent', agent_status = 'pending' WHERE id = ?")->execute([$_SESSION['customer_id']]);
            $_SESSION['customer_role'] = 'agent';
            $msg = '批发申请已提交，请等待审核通过。';
            $me = ['role' => 'agent', 'agent_status' => 'pending'];
        } catch (Exception $e) {
            $msg = '提交失败，请稍后再试。';
        }
    }
}

$pageTitle = '申请批发 - 烟花网购';
require_once 'includes/header.php';
?>

<main>
    <h2>申请成为批发客户 (Agent)</h2>
    <?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if ($me['role'] === 'customer'): ?>
        <p>提交后我们将审核您的批发资格，通过后您将享有批发客户权益。</p>
        <form method="post">
            <button type="submit" class="btn btn-primary">提交申请</button>
        </form>
    <?php elseif ($me['agent_status'] === 'pending'): ?>
        <p>您的批发申请审核中，请耐心等待。</p>
    <?php else: ?>
        <p>您已是批发客户。</p>
    <?php endif; ?>
    <p style="margin-top:1rem;"><a href="<?php echo BASE_PATH; ?>index.php" class="btn">返回首页</a></p>
</main>
<?php require_once 'includes/footer.php'; ?>
