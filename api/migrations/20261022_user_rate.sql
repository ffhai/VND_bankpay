-- 费率单位为百分数，1.2500 表示 1.25%；存量账号默认0，按实际约定在账号编辑中设置。
ALTER TABLE `dt_user` ADD COLUMN `rate` decimal(7,4) NOT NULL DEFAULT 0.0000 COMMENT '百分比费率，四位小数';
