-- 接收解析后的短信交易；仅注册接口权限，不修改 dt_sms_trade 字段。
START TRANSACTION;
SET @notify_trade_source=(SELECT `id` FROM `dt_perms` WHERE `module`='task' AND `controller`='Notify' AND `action`='setPending' LIMIT 1);
SET @notify_trade_parent=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@notify_trade_source);
SET @notify_trade_module=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@notify_trade_parent);
SET @notify_trade_source_active=(SELECT `status`=1 AND `node_type`='api' FROM `dt_perms` WHERE `id`=@notify_trade_source);
SET @notify_trade_parent_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@notify_trade_parent);
SET @notify_trade_module_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@notify_trade_module);
SET @notify_trade_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='task.notify.addsmstrade' LIMIT 1);
SET @notify_trade_permission_new=(@notify_trade_permission IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @notify_trade_parent,'接收短信交易','api','task.notify.addsmstrade','task','Notify','addSmsTrade',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @notify_trade_permission_new AND @notify_trade_parent IS NOT NULL;
SET @notify_trade_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='task.notify.addsmstrade' LIMIT 1);

-- 首次创建时继承现有有效 setPending 授权；重跑不恢复已撤销的权限。
CREATE TEMPORARY TABLE `notify_trade_permission_grants` AS
SELECT DISTINCT `type` FROM `dt_role_perms` WHERE `perms_id`=@notify_trade_source
AND @notify_trade_source_active AND @notify_trade_parent_active AND @notify_trade_module_active;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT grants.`type`, @notify_trade_permission, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `notify_trade_permission_grants` grants WHERE @notify_trade_permission_new AND @notify_trade_permission IS NOT NULL;
DROP TEMPORARY TABLE `notify_trade_permission_grants`;
COMMIT;
