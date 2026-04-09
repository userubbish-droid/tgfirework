<?php
// Lightweight i18n (zh-CN / en). Switch with ?lang=en or ?lang=zh

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function get_lang(): string {
    $lang = $_GET['lang'] ?? ($_SESSION['lang'] ?? 'zh');
    $lang = is_string($lang) ? strtolower(trim($lang)) : 'zh';
    $lang = ($lang === 'en' || $lang === 'zh') ? $lang : 'zh';
    $_SESSION['lang'] = $lang;
    return $lang;
}

function t(string $key, array $vars = []): string {
    static $dict = null;
    if ($dict === null) {
        $dict = [
            'zh' => [
                'site.name' => '烟花网购',
                'nav.home' => '首页',
                'nav.cart' => '购物车',
                'nav.my_orders' => '我的订单',
                'nav.change_password' => '修改密码',
                'nav.apply_agent' => '申请批发',
                'nav.agent_badge' => '批发客户',
                'nav.logout' => '退出',
                'nav.login' => '登录',
                'nav.register' => '注册',
                'nav.lang.zh' => '中文',
                'nav.lang.en' => 'English',

                'home.title' => '烟花网购站',
                'home.subtitle' => '安全合规 · 品质保证 · 送货上门',
                'home.empty' => '暂无商品',

                'product.stock' => '库存 {n} 件',
                'product.add_to_cart' => '加入购物车',

                'cart.title' => '购物车',
                'cart.empty' => '购物车为空，去选购',
                'cart.col.product' => '商品',
                'cart.col.price' => '单价',
                'cart.col.qty' => '数量',
                'cart.col.subtotal' => '小计',
                'cart.total' => '合计：',
                'cart.checkout' => '去结算',
                'cart.continue' => '继续购物',
                'cart.remove' => '删除',
                'cart.unit.box' => '箱',
                'cart.unit.piece' => '件',
                'cart.unit.box_hint' => '(按箱)',

                'common.na' => '暂无图片',
                'common.added_to_cart' => '已加入购物车',
            ],
            'en' => [
                'site.name' => 'Fireworks Shop',
                'nav.home' => 'Home',
                'nav.cart' => 'Cart',
                'nav.my_orders' => 'My Orders',
                'nav.change_password' => 'Change Password',
                'nav.apply_agent' => 'Wholesale Application',
                'nav.agent_badge' => 'Wholesale',
                'nav.logout' => 'Logout',
                'nav.login' => 'Login',
                'nav.register' => 'Register',
                'nav.lang.zh' => '中文',
                'nav.lang.en' => 'English',

                'home.title' => 'Fireworks Online Store',
                'home.subtitle' => 'Compliant · Quality assured · Delivery available',
                'home.empty' => 'No products available',

                'product.stock' => 'Stock {n} pcs',
                'product.add_to_cart' => 'Add to Cart',

                'cart.title' => 'Shopping Cart',
                'cart.empty' => 'Your cart is empty. Start shopping',
                'cart.col.product' => 'Product',
                'cart.col.price' => 'Price',
                'cart.col.qty' => 'Qty',
                'cart.col.subtotal' => 'Subtotal',
                'cart.total' => 'Total:',
                'cart.checkout' => 'Checkout',
                'cart.continue' => 'Continue Shopping',
                'cart.remove' => 'Remove',
                'cart.unit.box' => 'Box',
                'cart.unit.piece' => 'Pc',
                'cart.unit.box_hint' => '(per box)',

                'common.na' => 'No image',
                'common.added_to_cart' => 'Added to cart',
            ],
        ];
    }

    $lang = get_lang();
    $text = $dict[$lang][$key] ?? $dict['zh'][$key] ?? $key;
    foreach ($vars as $k => $v) {
        $text = str_replace('{' . $k . '}', (string)$v, $text);
    }
    return $text;
}

