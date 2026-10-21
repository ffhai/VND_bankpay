-- Reuse existing framework balance page. Never delete financial ledger rows.
START TRANSACTION;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time)
SELECT r.type,p.id,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM dt_roles r
JOIN dt_perms p ON p.route_path IN ('/user','/user/balance')
    OR p.permission_key IN ('user.balance.info','user.balance.items')
WHERE r.type IN ('channel','merchant');

DELETE rp FROM dt_role_perms rp JOIN dt_perms p ON p.id=rp.perms_id
WHERE p.route_path IN ('/channel/finance/balanceLog','/merchant/finance/balanceLog')
    OR p.permission_key IN ('channel.balancelog.items','merchant.balancelog.items');
DELETE FROM dt_perms
WHERE route_path IN ('/channel/finance/balanceLog','/merchant/finance/balanceLog')
    OR permission_key IN ('channel.balancelog.items','merchant.balancelog.items');

-- Remove the migrated finance groups only when they have no remaining children.
CREATE TEMPORARY TABLE vn_empty_finance_groups (id int PRIMARY KEY);
INSERT INTO vn_empty_finance_groups (id)
SELECT p.id FROM dt_perms p LEFT JOIN dt_perms child ON child.parent_id=p.id
WHERE p.route_path IN ('/channel/finance','/merchant/finance') AND child.id IS NULL;
DELETE rp FROM dt_role_perms rp JOIN vn_empty_finance_groups g ON g.id=rp.perms_id;
DELETE p FROM dt_perms p JOIN vn_empty_finance_groups g ON g.id=p.id;
DROP TEMPORARY TABLE vn_empty_finance_groups;

COMMIT;
