-- 仅用于已删除四个解析字段、且仍使用0未处理/1已处理/2失败的上一版表。
-- 原始旧表直接使用20261022_device_notify_content.sql升级，不重复执行本转换。
UPDATE `dt_device_notify`
SET `status` = CASE WHEN `status` = 0 THEN 1 WHEN `status` = 1 THEN 2 WHEN `status` = 2 THEN -1 ELSE 1 END;
ALTER TABLE `dt_device_notify`
    MODIFY COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1未处理 2已处理 -1处理失败';
