-- Agent 默认回扣：每位 Agent 可设默认回扣，未设特别回扣的商品用此默认（已有库执行）
ALTER TABLE customers ADD COLUMN default_rebate DECIMAL(10,2) DEFAULT NULL COMMENT 'Agent默认回扣（元）' AFTER agent_status;
