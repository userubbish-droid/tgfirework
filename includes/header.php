<!DOCTYPE html>
<?php require_once __DIR__ . '/i18n.php'; $lang = get_lang(); ?>
<html lang="<?php echo $lang === 'en' ? 'en' : 'zh-CN'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : htmlspecialchars(t('site.name')); ?></title>
    <?php $styleVer = @filemtime(__DIR__ . '/../assets/css/style.css') ?: time(); ?>
    <link rel="stylesheet" href="<?php echo BASE_PATH; ?>assets/css/style.css?v=<?php echo (int)$styleVer; ?>">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a href="<?php echo BASE_PATH; ?>index.php" class="site-logo"><?php echo htmlspecialchars(t('site.name')); ?></a>
        <nav class="site-nav">
            <a href="<?php echo BASE_PATH; ?>index.php"><?php echo htmlspecialchars(t('nav.home')); ?></a>
            <a href="<?php echo BASE_PATH; ?>cart.php" class="cart-link" id="cartLink"><?php echo htmlspecialchars(t('nav.cart')); ?></a>
            <?php if (!empty($_SESSION['customer_id'])): ?>
                <a href="<?php echo BASE_PATH; ?>my_orders.php"><?php echo htmlspecialchars(t('nav.my_orders')); ?></a>
                <a href="<?php echo BASE_PATH; ?>change_password.php"><?php echo htmlspecialchars(t('nav.change_password')); ?></a>
                <?php if (($_SESSION['customer_role'] ?? 'customer') === 'customer'): ?>
                    <a href="<?php echo BASE_PATH; ?>apply_agent.php"><?php echo htmlspecialchars(t('nav.apply_agent')); ?></a>
                <?php elseif (($_SESSION['customer_role'] ?? '') === 'agent'): ?>
                    <span class="nav-badge"><?php echo htmlspecialchars(t('nav.agent_badge')); ?></span>
                <?php endif; ?>
                <span class="nav-user"><?php echo htmlspecialchars($_SESSION['customer_name'] ?? $_SESSION['customer_phone'] ?? ''); ?></span>
                <a href="<?php echo BASE_PATH; ?>logout.php"><?php echo htmlspecialchars(t('nav.logout')); ?></a>
            <?php else: ?>
                <a href="<?php echo BASE_PATH; ?>login.php"><?php echo htmlspecialchars(t('nav.login')); ?></a>
                <a href="<?php echo BASE_PATH; ?>register.php"><?php echo htmlspecialchars(t('nav.register')); ?></a>
            <?php endif; ?>
            <?php
                $base = strtok($_SERVER['REQUEST_URI'] ?? '', '?') ?: '';
                $qs = $_GET;
                $qs['lang'] = ($lang === 'en') ? 'zh' : 'en';
                $toggleUrl = $base . '?' . http_build_query($qs);
            ?>
            <a href="<?php echo htmlspecialchars($toggleUrl); ?>" style="opacity:.9;">
                <?php echo $lang === 'en' ? htmlspecialchars(t('nav.lang.zh')) : htmlspecialchars(t('nav.lang.en')); ?>
            </a>
        </nav>
    </div>
</header>
<div class="container">
