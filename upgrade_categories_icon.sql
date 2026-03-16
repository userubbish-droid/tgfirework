-- 分类图标与英文名（已有数据库在 phpMyAdmin 中执行）
ALTER TABLE categories ADD COLUMN image VARCHAR(255) DEFAULT NULL COMMENT '分类图标' AFTER name;
ALTER TABLE categories ADD COLUMN name_en VARCHAR(100) DEFAULT NULL COMMENT '英文/主标题，显示在图标下第一行' AFTER image;
