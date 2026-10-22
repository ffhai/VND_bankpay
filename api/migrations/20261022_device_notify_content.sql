-- 设备推送改为原文存储：从原始表升级：0未处理、1成功、2失败、-1其他，转为StatusEnum。
-- task旧解析及短信验证码接口尚未适配，本迁移仅调整存储结构及列表状态。
UPDATE `dt_device_notify`
SET `status` = CASE WHEN `status` IN (1, -1) THEN 2 WHEN `status` = 2 THEN -1 ELSE 1 END;

ALTER TABLE `dt_device_notify`
    DROP COLUMN `card_number`,
    DROP COLUMN `balance`,
    DROP COLUMN `sms_code`,
    DROP COLUMN `order_id`,
    MODIFY COLUMN `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1未处理 2已处理 -1处理失败';
