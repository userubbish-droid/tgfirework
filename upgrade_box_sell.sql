-- 同品可箱可散：商品表 + 订单明细单位（已有库在 phpMyAdmin 执行）
ALTER TABLE products ADD COLUMN sell_type VARCHAR(20) DEFAULT 'piece' COMMENT 'piece=仅散卖 box=仅按箱 both=箱+散' AFTER allow_mail;
ALTER TABLE products ADD COLUMN box_pieces INT DEFAULT NULL COMMENT '每箱件数' AFTER sell_type;
ALTER TABLE products ADD COLUMN price_box DECIMAL(10,2) DEFAULT NULL COMMENT '每箱价格' AFTER box_pieces;

ALTER TABLE order_items ADD COLUMN unit VARCHAR(10) DEFAULT 'piece' COMMENT 'piece=件 box=箱' AFTER quantity;
