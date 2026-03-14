-- Agent 回扣：商品表增加回扣字段（已有库在 phpMyAdmin 执行）
ALTER TABLE products ADD COLUMN agent_rebate DECIMAL(10,2) DEFAULT NULL COMMENT 'Agent回扣金额，折后价=原价-回扣' AFTER price_box;
ALTER TABLE products ADD COLUMN agent_rebate_box DECIMAL(10,2) DEFAULT NULL COMMENT 'Agent按箱回扣' AFTER agent_rebate;
