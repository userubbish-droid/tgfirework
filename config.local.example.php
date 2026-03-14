<?php
/**
 * 本地/线上数据库配置示例
 * 复制此文件为 config.local.php 并填写真实信息（config.local.php 不要提交到版本库）
 *
 * 若出现：Access denied for user 'xxx'@'localhost' (using password: YES)
 * 请检查：
 * 1. 主机面板里是否已把「数据库用户」添加到「该数据库」并授权（如 cPanel：MySQL 数据库 → 将用户添加到数据库）
 * 2. 数据库名、用户名、密码是否与面板显示完全一致（多/少空格、大小写）
 * 3. 数据库名在虚拟主机上通常带前缀，如 u870568714_xxx，请填面板里显示的完整名
 */
return [
    'host'     => 'localhost',
    'dbname'   => 'u870568714_tgfirework',
    'user'     => 'u870568714_tgfirework',
    'password' => 'Tgfirework@996',                // 请在此填写你的数据库密码
    'site_url' => 'tgfirework.online',                // 站点在根目录（如 tgfirework.online）填 ''；在子目录填 '/tg'
];
