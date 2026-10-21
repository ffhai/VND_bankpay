START TRANSACTION;
DELETE rp FROM dt_role_perms rp JOIN dt_perms p ON p.id=rp.perms_id
WHERE p.module='admin' AND p.controller IN ('BankUser','BankCards') AND p.action='add';
DELETE FROM dt_perms
WHERE module='admin' AND controller IN ('BankUser','BankCards') AND action='add';
COMMIT;
