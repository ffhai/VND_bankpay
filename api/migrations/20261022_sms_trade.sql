-- 使用现有dt_sms_trade表，仅注册菜单及查询、删除权限，不修改表结构。
START TRANSACTION;
SET @sms_device_group=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device' LIMIT 1);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`route_path`,`status`,`sort`,`create_time`,`update_time`)
SELECT 0,'设备推送','group','/admin/device',1,0,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @sms_device_group IS NULL;
SET @sms_device_group=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device' LIMIT 1);

SET @sms_trade_page=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device/smsTrade' LIMIT 1);
SET @sms_trade_page_new=(@sms_trade_page IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`route_path`,`module`,`controller`,`status`,`create_time`,`update_time`)
SELECT @sms_device_group,'短信交易','page','/admin/device/smsTrade','admin','SmsTrade',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @sms_trade_page_new;
SET @sms_trade_page=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device/smsTrade' LIMIT 1);

SET @sms_trade_items=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smstrade.items' LIMIT 1);
SET @sms_trade_items_new=(@sms_trade_items IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @sms_trade_page,'查询','action','admin.smstrade.items','admin','SmsTrade','items',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @sms_trade_items_new;
SET @sms_trade_del=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smstrade.del' LIMIT 1);
SET @sms_trade_del_new=(@sms_trade_del IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @sms_trade_page,'删除','action','admin.smstrade.del','admin','SmsTrade','del',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @sms_trade_del_new;

-- 首次创建时继承有效推送记录查询/删除授权；重跑不会恢复已撤销的短信交易权限。
CREATE TEMPORARY TABLE `sms_trade_permission_grants` AS
SELECT DISTINCT rp.`type`, source.`action` FROM `dt_role_perms` rp
JOIN `dt_perms` source ON source.`id`=rp.`perms_id`
JOIN `dt_perms` page ON page.`id`=source.`parent_id`
JOIN `dt_perms` root ON root.`id`=page.`parent_id`
WHERE source.`module`='admin' AND source.`controller`='DeviceNotify' AND source.`action` IN ('items','del')
AND source.`status`=1 AND page.`status`=1 AND root.`status`=1;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT source_grants.`type`, target.`id`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP() FROM `sms_trade_permission_grants` source_grants
JOIN `dt_perms` target ON
(target.`route_path`='/admin/device/smsTrade' AND @sms_trade_page_new)
OR (target.`permission_key`='admin.smstrade.items' AND @sms_trade_items_new AND source_grants.`action`='items')
OR (target.`permission_key`='admin.smstrade.del' AND @sms_trade_del_new AND source_grants.`action`='del');
DROP TEMPORARY TABLE `sms_trade_permission_grants`;
COMMIT;
