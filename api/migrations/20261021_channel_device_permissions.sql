START TRANSACTION;
INSERT INTO dt_perms (parent_id,name,node_type,route_path,module,status,sort,create_time,update_time)
SELECT 0,'设备管理','group','/channel/device','channel',1,1999,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/device');
SET @device_group=(SELECT id FROM dt_perms WHERE route_path='/channel/device' LIMIT 1);

INSERT INTO dt_perms (parent_id,name,node_type,route_path,module,controller,status,create_time,update_time)
SELECT @device_group,'通知推送','page','/channel/device/notifyQrcode','channel','Device',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/device/notifyQrcode');
SET @device_page=(SELECT id FROM dt_perms WHERE route_path='/channel/device/notifyQrcode' LIMIT 1);

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,module,controller,action,status,create_time,update_time)
SELECT @device_page,'获取设备通知二维码','action','channel.device.getnotifyqrcode','channel','Device','getNotifyQrcode',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.device.getnotifyqrcode');

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time)
SELECT r.type,p.id,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM dt_roles r
JOIN dt_perms p ON p.route_path IN ('/channel/device','/channel/device/notifyQrcode')
OR p.permission_key='channel.device.getnotifyqrcode'
WHERE r.type='channel';
COMMIT;
