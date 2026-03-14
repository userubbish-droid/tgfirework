<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : '烟花网购'; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="<?php echo SITE_URL; ?>/index.php" class="site-logo">烟花网购</a>
        <nav class="site-nav">
            <a href="<?php echo SITE_URL; ?>/index.php">首页</a>
            <a href="<?php echo SITE_URL; ?>/cart.php" class="cart-link" id="cartLink">购物车</a>
        </nav>
    </div>
</header>
<div class="container">
