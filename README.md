# 烟花网购站

带前台与后台的烟花在线销售网站。

## 功能

**前台（顾客）**
- **普通客户**：注册后即可登录、下单购物
- **Agent（批发）**：可前台「申请批发」或由后台在客户管理中「提升为 Agent」；申请后需后台审核通过
- 首页商品列表、按分类筛选；商品详情、加入购物车；购物车 → 去结算；我的订单

**后台（管理员）**
- 登录 / 退出
- 仪表盘：今日订单、今日销售额、在售商品数、待处理订单
- 商品管理：添加 / 编辑 / 删除、上传图片、上下架
- 订单管理：列表、详情、修改状态（待付款 / 已付款 / 已发货 / 已完成 / 已取消）

## 安装

1. 环境：PHP 7.4+（PDO MySQL）、MySQL 5.7+ 或 MariaDB、Web 服务器。

2. 创建数据库并导入：
   ```bash
   mysql -u root -p < install.sql
   ```
   或在 phpMyAdmin 中执行 `install.sql`。

3. **数据库连接（解决「数据库连接失败 / Access denied」）**  
   线上环境建议用本地配置，不把密码写进版本库：
   - 复制 `config.local.example.php` 为 `config.local.php`
   - 在 `config.local.php` 里填写主机、数据库名、用户名、**密码**（多数主机要求 root 或分配用户必须带密码）
   - 若站点在根目录，可在 `config.local.php` 里增加 `'site_url' => ''`（需在 config.php 里用该值覆盖 SITE_URL，当前示例未写可后续加）

4. 确保 `uploads/` 目录可写（商品图片上传）。

5. **已上线的站加“客户注册”**：在 phpMyAdmin 中导入 `upgrade_register.sql`，会新增 `customers` 表及订单表字段。  
6. **普通客户 / Agent(批发)**：在 phpMyAdmin 中导入 `upgrade_agent.sql`，为 customers 表增加 `role`、`agent_status`；普通客户注册即可购物，Agent 需申请或由管理员提升并可选审核。

## 访问

- 前台：`http://你的域名/tg/index.php`
- 后台：`http://你的域名/tg/admin/login.php`
- 默认管理员：用户名 `admin`，密码 `admin123`（上线后请修改）

## 目录结构

```
tg/
├── config.php
├── install.sql
├── index.php, product.php, cart.php, checkout.php, login.php, register.php, logout.php, my_orders.php, order_detail.php
├── includes/header.php, footer.php
├── admin/login.php, logout.php, index.php, products.php, orders.php, order_detail.php
├── assets/css/style.css, admin.css
├── assets/img/placeholder.svg
├── uploads/
└── README.md
```
