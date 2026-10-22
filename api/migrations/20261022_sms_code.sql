-- 使用现有 dt_sms_code 表，仅注册短信验证菜单、查询/删除及任务上传权限。
START TRANSACTION;
SET @sms_code_group=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device' LIMIT 1);
SET @sms_code_page=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device/smsCode' LIMIT 1);
SET @sms_code_page_new=(@sms_code_page IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`route_path`,`module`,`controller`,`status`,`create_time`,`update_time`)
SELECT @sms_code_group,'短信验证','page','/admin/device/smsCode','admin','SmsCode',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @sms_code_page_new AND @sms_code_group IS NOT NULL;
SET @sms_code_page=(SELECT `id` FROM `dt_perms` WHERE `route_path`='/admin/device/smsCode' LIMIT 1);

-- 首次创建时继承对应有效权限；重跑不恢复已撤销授权。
SET @sms_code_source=(SELECT `id` FROM `dt_perms` WHERE `module`='admin' AND `controller`='SmsTrade' AND `action`='items' LIMIT 1);
SET @sms_code_source_parent=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_source_root=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_source_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_parent_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_root_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_root);
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smscode.items' LIMIT 1);
SET @sms_code_permission_new=(@sms_code_permission IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @sms_code_page,'查询','action','admin.smscode.items','admin','SmsCode','items',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @sms_code_permission_new AND @sms_code_page IS NOT NULL;
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smscode.items' LIMIT 1);
CREATE TEMPORARY TABLE `sms_code_permission_grants` AS
SELECT DISTINCT `type` FROM `dt_role_perms` WHERE `perms_id`=@sms_code_source
AND @sms_code_source_active AND @sms_code_parent_active AND @sms_code_root_active;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT `type`,@sms_code_permission,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM `sms_code_permission_grants`
WHERE @sms_code_permission_new AND @sms_code_permission IS NOT NULL;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT `type`,@sms_code_page,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM `sms_code_permission_grants`
WHERE @sms_code_page_new AND @sms_code_page IS NOT NULL;
DROP TEMPORARY TABLE `sms_code_permission_grants`;

-- 首次创建时继承对应有效权限；重跑不恢复已撤销授权。
SET @sms_code_source=(SELECT `id` FROM `dt_perms` WHERE `module`='admin' AND `controller`='SmsTrade' AND `action`='del' LIMIT 1);
SET @sms_code_source_parent=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_source_root=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_source_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_parent_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_root_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_root);
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smscode.del' LIMIT 1);
SET @sms_code_permission_new=(@sms_code_permission IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @sms_code_page,'删除','action','admin.smscode.del','admin','SmsCode','del',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @sms_code_permission_new AND @sms_code_page IS NOT NULL;
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='admin.smscode.del' LIMIT 1);
CREATE TEMPORARY TABLE `sms_code_permission_grants` AS
SELECT DISTINCT `type` FROM `dt_role_perms` WHERE `perms_id`=@sms_code_source
AND @sms_code_source_active AND @sms_code_parent_active AND @sms_code_root_active;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT `type`,@sms_code_permission,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM `sms_code_permission_grants`
WHERE @sms_code_permission_new AND @sms_code_permission IS NOT NULL;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT `type`,@sms_code_page,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM `sms_code_permission_grants`
WHERE @sms_code_page_new AND @sms_code_page IS NOT NULL;
DROP TEMPORARY TABLE `sms_code_permission_grants`;

-- 首次创建时继承对应有效权限；重跑不恢复已撤销授权。
SET @sms_code_source=(SELECT `id` FROM `dt_perms` WHERE `module`='task' AND `controller`='Notify' AND `action`='setPending' LIMIT 1);
SET @sms_code_source_parent=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_source_root=(SELECT `parent_id` FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_source_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source);
SET @sms_code_parent_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_parent);
SET @sms_code_root_active=(SELECT `status`=1 FROM `dt_perms` WHERE `id`=@sms_code_source_root);
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='task.notify.addsmscode' LIMIT 1);
SET @sms_code_permission_new=(@sms_code_permission IS NULL);
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT @sms_code_source_parent,'接收短信验证码','api','task.notify.addsmscode','task','Notify','addSmsCode',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE @sms_code_permission_new AND @sms_code_source_parent IS NOT NULL;
SET @sms_code_permission=(SELECT `id` FROM `dt_perms` WHERE `permission_key`='task.notify.addsmscode' LIMIT 1);
CREATE TEMPORARY TABLE `sms_code_permission_grants` AS
SELECT DISTINCT `type` FROM `dt_role_perms` WHERE `perms_id`=@sms_code_source
AND @sms_code_source_active AND @sms_code_parent_active AND @sms_code_root_active;
INSERT IGNORE INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT `type`,@sms_code_permission,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM `sms_code_permission_grants`
WHERE @sms_code_permission_new AND @sms_code_permission IS NOT NULL;
DROP TEMPORARY TABLE `sms_code_permission_grants`;
COMMIT;
