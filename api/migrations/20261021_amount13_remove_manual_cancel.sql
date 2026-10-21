-- Values are not rescaled. Preflight rejects existing out-of-range data before this script runs.
ALTER TABLE dt_order MODIFY amount bigint(20) NOT NULL DEFAULT 0 COMMENT '整数金额，最多13位';
ALTER TABLE dt_bank_cards MODIFY balance bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位',
    MODIFY limit_in_max bigint(20) NOT NULL DEFAULT 0 COMMENT '整数入款限额，最多13位',
    MODIFY limit_out_max bigint(20) NOT NULL DEFAULT 0 COMMENT '整数出款限额，最多13位';
ALTER TABLE dt_device_notify MODIFY balance bigint(20) NOT NULL DEFAULT 0 COMMENT '整数金额，最多13位';
ALTER TABLE dt_user MODIFY balance bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位';
ALTER TABLE dt_balance_log MODIFY change_amount bigint(20) NOT NULL DEFAULT 0 COMMENT '整数变动，绝对值最多13位',
    MODIFY balance_after bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位';

START TRANSACTION;
DELETE rp FROM dt_role_perms rp JOIN dt_perms p ON p.id=rp.perms_id WHERE p.permission_key='admin.order.cancel';
DELETE FROM dt_perms WHERE permission_key='admin.order.cancel';
COMMIT;
