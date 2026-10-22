-- 新增推送时间，并按原始推送回填历史短信交易。可重复执行。
SET @sms_trade_has_time=(SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='dt_sms_trade' AND COLUMN_NAME='time');
SET @sms_trade_time_ddl=IF(@sms_trade_has_time=0, 'ALTER TABLE `dt_sms_trade` ADD COLUMN `time` int(10) NOT NULL DEFAULT 0 COMMENT ''推送时间''', 'SET @sms_trade_time_ready=1');
PREPARE sms_trade_time_statement FROM @sms_trade_time_ddl;
EXECUTE sms_trade_time_statement;
DEALLOCATE PREPARE sms_trade_time_statement;

UPDATE `dt_sms_trade` trade
INNER JOIN `dt_device_notify` notify ON notify.`id`=trade.`notify_id`
SET trade.`time`=COALESCE(notify.`time`,0)
WHERE trade.`time`=0;
