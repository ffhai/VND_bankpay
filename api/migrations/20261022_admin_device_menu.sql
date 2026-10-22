-- 保留推送记录页面ID、状态、操作/API子节点与原角色授权，仅移动菜单位置。
START TRANSACTION;
SET @admin_device_group=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device' LIMIT 1);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`route_path`,`module`,`controller`,`action`,`sort`,`status`,`create_time`,`update_time`)
SELECT 0,'设备推送','group','/admin/device','','','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @admin_device_group IS NULL;
SET @admin_device_group=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device' LIMIT 1);
UPDATE `dt_perms`
SET `parent_id`=@admin_device_group,`name`='推送记录',`route_path`='/admin/device/notifyRecords',`update_time`=UNIX_TIMESTAMP()
WHERE `route_path` IN ('/admin/order/deviceNotify','/admin/device/notifyRecords');
COMMIT;
