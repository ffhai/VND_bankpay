START TRANSACTION;
INSERT INTO dt_perms (parent_id,name,node_type,permission_key,module,controller,action,status,create_time,update_time)
SELECT p.id,'测试VietQR','action',CONCAT(p.module,'.bankcards.testvietqr'),p.module,'BankCards','testVietqr',1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
FROM dt_perms p WHERE p.route_path IN ('/admin/bank/cards','/channel/bank/cards')
AND NOT EXISTS (SELECT 1 FROM dt_perms n WHERE n.permission_key=CONCAT(p.module,'.bankcards.testvietqr'));

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time)
SELECT rp.type,n.id,UNIX_TIMESTAMP(),UNIX_TIMESTAMP()
FROM dt_perms n JOIN dt_perms listed ON listed.module=n.module AND listed.permission_key=CONCAT(n.module,'.bankcards.items')
JOIN dt_role_perms rp ON rp.perms_id=listed.id
WHERE n.permission_key IN ('admin.bankcards.testvietqr','channel.bankcards.testvietqr');
COMMIT;
