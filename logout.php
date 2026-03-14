<?php
session_start();
$_SESSION['customer_id'] = null;
$_SESSION['customer_email'] = null;
$_SESSION['customer_name'] = null;
unset($_SESSION['customer_id'], $_SESSION['customer_email'], $_SESSION['customer_name']);
$url = defined('SITE_URL') && SITE_URL ? SITE_URL . '/index.php' : 'index.php';
header('Location: ' . $url);
exit;
