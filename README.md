# 烟花网购站

前台 + 后台的烟花在线销售网站。

## 功能

### 前台（顾客）
- 首页商品列表，按分类筛选
- 商品详情、加入购物车
- 购物车管理（本地存储）
- 填写收货信息提交订单

### 后台（管理员）
- 登录 / 退出
- 仪表盘：今日订单、今日销售额、在售商品数、待处理订单
- 商品管理：添加 / 编辑 / 删除商品，上传图片，上下架
- 订单管理：列表、详情、修改订单状态（待付款 / 已付款 / 已发货 / 已完成 / 已取消）

## 安装

1. **环境**：PHP 7.4+（开启 PDO MySQL）、MySQL 5.7+ 或 MariaDB、Web 服务器（Apache/Nginx）。

2. **创建数据库并导入表结构**：
   ```bash
   mysql -u root -p < install.sql
   ```
   或在 phpMyAdmin 中执行 `install.sql`。

3. **修改配置**：编辑 `config.php`，填写数据库账号密码和（如需要）站点 URL：
   ```php
   define('DB_USER', '你的数据库用户');
   define('DB_PASS', '你的密码');
   define('SITE_URL', '/tg');  // 若站点在根目录改为 ''
   ```

4. **目录权限**：确保 `uploads/` 可写，用于商品图片上传。

## 访问地址

- **前台首页**：`http://你的域名/tg/index.php` 或 `http://你的域名/tg/`
- **后台登录**：`http://你的域名/tg/admin/login.php`  
- **默认管理员**：用户名 `admin`，密码 `admin123`（上线后请在后台或数据库修改密码）

## 目录结构

```
tg/
├── config.php          # 数据库配置
├── install.sql         # 数据库初始化脚本
├── index.php           # 前台首页
├── product.php         # 商品详情
├── cart.php            # 购物车
├── checkout.php        # 提交订单
├── includes/
│   ├── header.php
│   └── footer.php
├── admin/
│   ├── login.php       # 后台登录
│   ├── logout.php
│   ├── index.php       # 仪表盘
│   ├── products.php    # 商品管理
│   ├── orders.php      # 订单列表
│   └── order_detail.php
├── assets/
│   ├── css/
│   │   ├── style.css   # 前台样式
│   │   └── admin.css   # 后台样式
│   └── img/
│       └── placeholder.svg
└── uploads/            # 商品图片上传目录
```
