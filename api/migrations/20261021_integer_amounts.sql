-- Monetary values are already integer columns. Keep existing numeric values unchanged.
-- Only remove obsolete minor-unit descriptions; do not rescale historical balances.
ALTER TABLE dt_user MODIFY COLUMN balance bigint(20) NOT NULL DEFAULT 0 COMMENT '余额(整数)';
ALTER TABLE dt_balance_log MODIFY COLUMN change_amount bigint(20) NOT NULL DEFAULT 0 COMMENT '变动金额(整数，可为负)',
    MODIFY COLUMN balance_after bigint(20) NOT NULL DEFAULT 0 COMMENT '变动后余额(整数)';
ALTER TABLE dt_order MODIFY COLUMN amount int(10) NULL DEFAULT 0 COMMENT '金额 整数';
