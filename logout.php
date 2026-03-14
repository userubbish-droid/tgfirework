<?php
require_once __DIR__ . '/config.php';
session_start();
$_SESSION['customer_id'] = null;
$_SESSION['customer_phone'] = null;
$_SESSION['customer_email'] = null;
$_SESSION['customer_name'] = null;
$_SESSION['customer_role'] = null;
unset($_SESSION['customer_id'], $_SESSION['customer_phone'], $_SESSION['customer_email'], $_SESSION['customer_name'], $_SESSION['customer_role']);
$url = defined('BASE_PATH') ? BASE_PATH . 'index.php' : '/index.php';
header('Location: ' . $url);
exit;
