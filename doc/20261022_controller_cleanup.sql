-- 清理前端未使用、已从控制器移除的操作权限；保留页面及支付/任务入口。
-- 自定义表前缀时替换 dt_；执行前备份权限和角色授权表。
CREATE TEMPORARY TABLE tp8_unused_controller_nodes (id INT PRIMARY KEY);
INSERT INTO tp8_unused_controller_nodes (id)
SELECT p.id FROM dt_perms p
WHERE p.node_type IN ('action', 'api')
AND (BINARY p.module, BINARY p.controller, BINARY p.action) IN (
  ('admin', 'AppList', 'getOptions'),
  ('admin', 'Balance', 'add'),
  ('admin', 'Balance', 'del'),
  ('admin', 'Balance', 'edit'),
  ('admin', 'Balance', 'getOptions'),
  ('admin', 'BankCards', 'add'),
  ('admin', 'BankCards', 'getOptions'),
  ('admin', 'BankUser', 'add'),
  ('admin', 'Config', 'add'),
  ('admin', 'Config', 'del'),
  ('admin', 'Config', 'edit'),
  ('admin', 'Config', 'getOptions'),
  ('admin', 'ConfigList', 'getOptions'),
  ('admin', 'DeviceNotify', 'add'),
  ('admin', 'DeviceNotify', 'edit'),
  ('admin', 'DeviceNotify', 'getOptions'),
  ('admin', 'Editor', 'add'),
  ('admin', 'Editor', 'del'),
  ('admin', 'Editor', 'edit'),
  ('admin', 'Editor', 'getOptions'),
  ('admin', 'Order', 'add'),
  ('admin', 'Order', 'del'),
  ('admin', 'Order', 'edit'),
  ('admin', 'Order', 'getOptions'),
  ('admin', 'Perms', 'getOptions'),
  ('admin', 'RolePerms', 'add'),
  ('admin', 'RolePerms', 'del'),
  ('admin', 'RolePerms', 'edit'),
  ('admin', 'RolePerms', 'getOptions'),
  ('admin', 'RolePerms', 'items'),
  ('admin', 'SmsCode', 'add'),
  ('admin', 'SmsCode', 'edit'),
  ('admin', 'SmsCode', 'getOptions'),
  ('admin', 'SmsTrade', 'add'),
  ('admin', 'SmsTrade', 'edit'),
  ('admin', 'SmsTrade', 'getOptions'),
  ('channel', 'BankCards', 'getOptions'),
  ('channel', 'Banks', 'add'),
  ('channel', 'Banks', 'del'),
  ('channel', 'Banks', 'edit'),
  ('channel', 'Banks', 'items'),
  ('channel', 'Device', 'add'),
  ('channel', 'Device', 'del'),
  ('channel', 'Device', 'edit'),
  ('channel', 'Device', 'getOptions'),
  ('channel', 'Device', 'items'),
  ('channel', 'Order', 'add'),
  ('channel', 'Order', 'del'),
  ('channel', 'Order', 'edit'),
  ('channel', 'Order', 'getOptions'),
  ('common', 'Account', 'notice'),
  ('merchant', 'Order', 'add'),
  ('merchant', 'Order', 'del'),
  ('merchant', 'Order', 'edit'),
  ('merchant', 'Order', 'getOptions'),
  ('user', 'Balance', 'info')
);
START TRANSACTION;
DELETE rp FROM dt_role_perms rp INNER JOIN tp8_unused_controller_nodes n ON rp.perms_id = n.id;
DELETE p FROM dt_perms p INNER JOIN tp8_unused_controller_nodes n ON p.id = n.id;
COMMIT;
DROP TEMPORARY TABLE tp8_unused_controller_nodes;
