<?php
/**
 * 本地/线上数据库配置示例
 * 复制此文件为 config.local.php 并填写真实信息，config.local.php 不会被提交到版本库
 */
return [
    'host'     => 'localhost',       // 数据库主机，虚拟主机可能是 localhost 或主机商提供的地址
    'dbname'   => 'fireworks_shop',  // 数据库名（主机商可能要求用指定前缀）
    'user'     => 'root',            // 数据库用户名（主机商一般会分配一个）
    'password' => '你的数据库密码',   // 必填：主机上 root 或分配用户的密码
    'site_url' => '/tg',             // 可选：站点路径，根目录请填 ''
];
