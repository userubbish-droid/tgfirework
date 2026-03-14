-- 按 Agent 按商品设置特别回扣表（已有库在 phpMyAdmin 执行）
CREATE TABLE IF NOT EXISTS agent_product_rebate (
    customer_id INT NOT NULL COMMENT 'Agent 客户 id',
    product_id INT NOT NULL,
    rebate_piece DECIMAL(10,2) DEFAULT NULL COMMENT '件价特别回扣',
    rebate_box DECIMAL(10,2) DEFAULT NULL COMMENT '箱价特别回扣',
    PRIMARY KEY (customer_id, product_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);
