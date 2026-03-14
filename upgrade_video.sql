-- 商品表增加视频字段（已有数据库在 phpMyAdmin 中执行）
ALTER TABLE products ADD COLUMN video VARCHAR(255) DEFAULT NULL AFTER image;
