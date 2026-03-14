<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : '烟花网购'; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="<?php echo BASE_PATH; ?>index.php" class="site-logo">烟花网购</a>
        <nav class="site-nav">
            <a href="<?php echo BASE_PATH; ?>index.php">首页</a>
            <a href="<?php echo BASE_PATH; ?>cart.php" class="cart-link" id="cartLink">购物车</a>
            <?php if (!empty($_SESSION['customer_id'])): ?>
                <a href="<?php echo BASE_PATH; ?>my_orders.php">我的订单</a>
                <?php if (($_SESSION['customer_role'] ?? 'customer') === 'customer'): ?>
                    <a href="<?php echo BASE_PATH; ?>apply_agent.php">申请批发</a>
                <?php elseif (($_SESSION['customer_role'] ?? '') === 'agent'): ?>
                    <span style="color:#d4af37;">批发客户</span>
                <?php endif; ?>
                <span style="color:rgba(255,255,255,0.9);"><?php echo htmlspecialchars($_SESSION['customer_name'] ?? $_SESSION['customer_email']); ?></span>
                <a href="<?php echo BASE_PATH; ?>logout.php">退出</a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>login.php">登录</a>
                <a href="<?php echo BASE_PATH; ?>register.php">注册</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<div class="container">
