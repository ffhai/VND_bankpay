-- Run after schema/data backup and integrity inspection. Existing account amounts are unchanged.
ALTER TABLE dt_bank_cards ENGINE=InnoDB;
ALTER TABLE dt_bank_user ENGINE=InnoDB;
ALTER TABLE dt_device_notify ENGINE=InnoDB;

ALTER TABLE dt_order
    ADD COLUMN bank_card_id int NOT NULL DEFAULT 0 COMMENT '收款银行卡原始ID',
    ADD COLUMN bank_user_id int NOT NULL DEFAULT 0 COMMENT '收款户名原始ID',
    ADD COLUMN bank_code varchar(50) NOT NULL DEFAULT '' COMMENT '收款银行代码快照',
    ADD COLUMN bank_bin varchar(16) NOT NULL DEFAULT '' COMMENT '收款银行BIN快照',
    ADD COLUMN bank_name varchar(255) NOT NULL DEFAULT '' COMMENT '收款银行名称快照',
    ADD COLUMN bank_short_name varchar(100) NOT NULL DEFAULT '' COMMENT '收款银行简称快照',
    ADD COLUMN account_name varchar(255) NOT NULL DEFAULT '' COMMENT '收款户名快照',
    ADD COLUMN snapshot_source varchar(20) NOT NULL DEFAULT 'missing' COMMENT 'created/legacy_backfill/missing',
    ADD INDEX idx_bank_card_id (bank_card_id),
    ADD INDEX idx_bank_user_id (bank_user_id),
    ADD INDEX idx_bank_code (bank_code);

-- Legacy backfill reflects CURRENT associated data, not guaranteed original historical values.
UPDATE dt_order o JOIN dt_bank_cards c ON c.bank_number=o.bank_number AND c.uid=o.channel_uid
JOIN dt_banks b ON b.code=c.bank_code
JOIN dt_bank_user u ON u.id=c.bank_user_id AND u.uid=c.uid
SET o.bank_card_id=c.id,o.bank_user_id=u.id,o.bank_code=b.code,o.bank_bin=b.bin,
    o.bank_name=b.name,o.bank_short_name=b.short_name,o.account_name=u.name,
    o.snapshot_source='legacy_backfill'
WHERE o.snapshot_source='missing' AND b.bin<>'' AND u.name<>'';

-- Retain existing global UNIQUE(bank_number): a card cannot be registered in multiple channels/banks.
