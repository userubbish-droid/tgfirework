<?php
/**
 * 配送方式：从 settings 表读取全局开关与开放日期，从商品表读取每商品支持的配送方式
 */

function getDeliverySettings(PDO $pdo) {
    $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings WHERE setting_key LIKE 'delivery_%'");
    $rows = $stmt ? $stmt->fetchAll(PDO::FETCH_KEY_PAIR) : [];
    $get = function($key) use ($rows) { return $rows[$key] ?? ''; };
    return [
        'self_pickup' => [
            'enabled' => (int)$get('delivery_self_pickup_enabled') === 1,
            'date_from' => $get('delivery_self_pickup_date_from'),
            'date_to' => $get('delivery_self_pickup_date_to'),
        ],
        'lalamove' => [
            'enabled' => (int)$get('delivery_lalamove_enabled') === 1,
            'date_from' => $get('delivery_lalamove_date_from'),
            'date_to' => $get('delivery_lalamove_date_to'),
        ],
        'mail' => [
            'enabled' => (int)$get('delivery_mail_enabled') === 1,
            'date_from' => $get('delivery_mail_date_from'),
            'date_to' => $get('delivery_mail_date_to'),
        ],
    ];
}

function isDeliveryAvailable(array $setting, $today = null) {
    if (!$setting['enabled']) return false;
    $today = $today ?? date('Y-m-d');
    if ($setting['date_from'] !== '' && $today < $setting['date_from']) return false;
    if ($setting['date_to'] !== '' && $today > $setting['date_to']) return false;
    return true;
}

/** 根据购物车商品 ID 列表，返回当前可选的配送方式（全局开放 + 所有商品都支持） */
function getAllowedDeliveryTypes(PDO $pdo, array $productIds) {
    if (empty($productIds)) return [];
    $settings = getDeliverySettings($pdo);
    $today = date('Y-m-d');
    $placeholders = implode(',', array_fill(0, count($productIds), '?'));
    $cols = ['allow_self_pickup', 'allow_lalamove', 'allow_mail'];
    try {
        $stmt = $pdo->prepare("SELECT MIN(COALESCE(allow_self_pickup,1)) AS s, MIN(COALESCE(allow_lalamove,1)) AS l, MIN(COALESCE(allow_mail,1)) AS m FROM products WHERE id IN ($placeholders)");
        $stmt->execute($productIds);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $row = ['s' => 1, 'l' => 1, 'm' => 1];
    }
    $allowed = [];
    if (isDeliveryAvailable($settings['self_pickup'], $today) && ($row['s'] ?? 1)) $allowed[] = 'self_pickup';
    if (isDeliveryAvailable($settings['lalamove'], $today) && ($row['l'] ?? 1)) $allowed[] = 'lalamove';
    if (isDeliveryAvailable($settings['mail'], $today) && ($row['m'] ?? 1)) $allowed[] = 'mail';
    return $allowed;
}
