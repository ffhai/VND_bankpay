-- 系统 > 上传文件管理。自定义表前缀时请替换 dt_。
INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`route_path`,`module`,`controller`,`action`,`sort`,`status`,`create_time`,`update_time`)
SELECT `id`, '上传文件管理', 'page', '/admin/system/upload', 'admin', 'Upload', '', 10, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `dt_perms` WHERE `route_path` = '/admin/system'
AND NOT EXISTS (SELECT 1 FROM (SELECT `route_path` FROM `dt_perms`) AS existing WHERE existing.`route_path` = '/admin/system/upload');

INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT p.`id`, '列表', 'action', 'admin.upload.items', 'admin', 'Upload', 'items', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `dt_perms` p WHERE p.`route_path` = '/admin/system/upload'
AND NOT EXISTS (SELECT 1 FROM (SELECT `permission_key` FROM `dt_perms`) AS existing WHERE existing.`permission_key` = 'admin.upload.items');

INSERT INTO `dt_perms` (`parent_id`,`name`,`node_type`,`permission_key`,`module`,`controller`,`action`,`status`,`create_time`,`update_time`)
SELECT p.`id`, '删除', 'action', 'admin.upload.del', 'admin', 'Upload', 'del', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `dt_perms` p WHERE p.`route_path` = '/admin/system/upload'
AND NOT EXISTS (SELECT 1 FROM (SELECT `permission_key` FROM `dt_perms`) AS existing WHERE existing.`permission_key` = 'admin.upload.del');

INSERT INTO `dt_role_perms` (`type`,`perms_id`,`create_time`,`update_time`)
SELECT r.`type`, p.`id`, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM `dt_roles` r CROSS JOIN `dt_perms` p
WHERE r.`type` IN ('super','admin')
AND (p.`route_path` = '/admin/system/upload' OR p.`permission_key` IN ('admin.upload.items','admin.upload.del'))
AND NOT EXISTS (SELECT 1 FROM `dt_role_perms` rp WHERE rp.`type` = r.`type` AND rp.`perms_id` = p.`id`);
