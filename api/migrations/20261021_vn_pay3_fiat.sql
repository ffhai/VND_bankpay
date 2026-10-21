-- vn_pay3 fiat business migration. Existing accounts and balances are untouched.

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `dt_banks`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `bin` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `short_name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `swift_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `status` tinyint(1) NULL DEFAULT -1,
  `status_in` tinyint(1) NULL DEFAULT -1,
  `status_out` tinyint(1) NULL DEFAULT -1,
  `create_time` int(10) NULL DEFAULT 0,
  `update_time` int(10) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `bin`(`bin`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `dt_bank_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NULL DEFAULT 0,
  `name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `idcard` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `status` tinyint(1) NULL DEFAULT -1,
  `create_time` int(10) NULL DEFAULT 0,
  `update_time` int(10) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `idcard`(`idcard`, `uid`) USING BTREE,
  INDEX `getOptions`(`uid`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `dt_bank_cards`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NULL DEFAULT 0,
  `bank_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `bank_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `bank_user_id` int(10) NULL DEFAULT 0,
  `balance` int(10) NULL DEFAULT 0,
  `online_account` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '网银-账号',
  `online_password` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '网银-密码',
  `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '手机号码',
  `notify_type` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '回调方式 app/online',
  `notify_device` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '回调设备名称',
  `limit_in_max` int(10) NULL DEFAULT 0,
  `limit_out_max` int(10) NULL DEFAULT 0,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `status` tinyint(1) NULL DEFAULT -1,
  `last_time` int(10) NULL DEFAULT 0,
  `create_time` int(10) NULL DEFAULT 0,
  `update_time` int(10) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `bank_number`(`bank_number`) USING BTREE,
  INDEX `getOptions`(`uid`, `status`) USING BTREE,
  INDEX `createOrder`(`limit_in_max`, `limit_out_max`, `status`) USING BTREE,
  INDEX `createOrder1`(`bank_code`, `limit_in_max`, `limit_out_max`, `status`) USING BTREE,
  INDEX `task_getBank`(`uid`, `online_account`, `online_password`, `phone_number`, `notify_type`, `notify_device`, `status`) USING BTREE,
  INDEX `task_getBankIsSet`(`uid`, `notify_type`, `notify_device`, `status`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `dt_device_notify`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NULL DEFAULT 0,
  `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `card_number` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `balance` int(10) NULL DEFAULT 0,
  `sms_code` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `remark` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `order_id` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `time` int(10) NULL DEFAULT 0,
  `status` tinyint(1) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `task_online_smscode`(`uid`, `type`, `phone_number`, `time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `dt_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '系统订单号',
  `trade_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '商户订单号',
  `trade_uid` int(10) NULL DEFAULT 0,
  `pay_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `channel_uid` int(10) NULL DEFAULT 0,
  `bank_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '银行卡号码',
  `amount` int(10) NULL DEFAULT 0 COMMENT '金额 整数',
  `ps` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '附言',
  `status` tinyint(1) NULL DEFAULT 1,
  `notify_status` tinyint(1) NULL DEFAULT 1,
  `notify_time` int(10) NULL DEFAULT 0,
  `notify_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `return_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `create_time` int(10) NULL DEFAULT 0,
  `expire_time` int(10) NULL DEFAULT 0,
  `pay_time` int(10) NULL DEFAULT 0,
  `settlement_time` int(10) NULL DEFAULT 0 COMMENT '结算时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_id`(`order_id`) USING BTREE,
  UNIQUE INDEX `trade_no`(`trade_no`, `trade_uid`) USING BTREE,
  UNIQUE INDEX `ps`(`ps`) USING BTREE,
  INDEX `settlement`(`status`, `settlement_time`, `pay_time`) USING BTREE,
  INDEX `pendding1`(`bank_number`, `amount`) USING BTREE,
  INDEX `sumBankCards`(`bank_number`, `status`, `create_time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

CREATE TABLE IF NOT EXISTS `dt_pay_type`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NULL DEFAULT 1,
  `expire_time` int(10) NULL DEFAULT 0,
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `create_time` int(10) NULL DEFAULT 0,
  `update_time` int(10) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `code`(`code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '通道' ROW_FORMAT = Dynamic;

INSERT INTO dt_pay_type (code,name,status,expire_time,remark,create_time,update_time) SELECT 'bank2bank','卡转卡',1,600,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_pay_type WHERE code='bank2bank');

INSERT IGNORE INTO `dt_banks` VALUES (1, 'Ngân hàng TMCP Công thương Việt Nam', 'ICB', '970415', 'https://cdn.vietqr.io/img/ICB.png', 'VietinBank', 'ICBVVNVX', 1, 1, 1, 0, 1790992102);

INSERT IGNORE INTO `dt_banks` VALUES (2, 'Ngân hàng TMCP Ngoại Thương Việt Nam', 'VCB', '970436', 'https://cdn.vietqr.io/img/VCB.png', 'Vietcombank', 'BFTVVNVX', 1, 1, -1, 0, 1790992136);

INSERT IGNORE INTO `dt_banks` VALUES (3, 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam', 'BIDV', '970418', 'https://cdn.vietqr.io/img/BIDV.png', 'BIDV', 'BIDVVNVX', 1, -1, 1, 0, 1790992152);

INSERT IGNORE INTO `dt_banks` VALUES (4, 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam', 'VBA', '970405', 'https://cdn.vietqr.io/img/VBA.png', 'Agribank', 'VBAAVNVX', 1, 1, 1, 0, 1790992158);

INSERT IGNORE INTO `dt_banks` VALUES (5, 'Ngân hàng TMCP Phương Đông', 'OCB', '970448', 'https://cdn.vietqr.io/img/OCB.png', 'OCB', 'ORCOVNVX', -1, 1, 1, 0, 1790992168);

INSERT IGNORE INTO `dt_banks` VALUES (6, 'Ngân hàng TMCP Quân đội', 'MB', '970422', 'https://cdn.vietqr.io/img/MB.png', 'MBBank', 'MSCBVNVX', 1, 1, 1, 0, 1790992174);

INSERT IGNORE INTO `dt_banks` VALUES (7, 'Ngân hàng TMCP Kỹ thương Việt Nam', 'TCB', '970407', 'https://cdn.vietqr.io/img/TCB.png', 'Techcombank', 'VTCBVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (8, 'Ngân hàng TMCP Á Châu', 'ACB', '970416', 'https://cdn.vietqr.io/img/ACB.png', 'ACB', 'ASCBVNVX', 1, 1, 1, 0, 1790994645);

INSERT IGNORE INTO `dt_banks` VALUES (9, 'Ngân hàng TMCP Việt Nam Thịnh Vượng', 'VPB', '970432', 'https://cdn.vietqr.io/img/VPB.png', 'VPBank', 'VPBKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (10, 'Ngân hàng TMCP Tiên Phong', 'TPB', '970423', 'https://cdn.vietqr.io/img/TPB.png', 'TPBank', 'TPBVVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (11, 'Ngân hàng TMCP Sài Gòn Thương Tín', 'STB', '970403', 'https://cdn.vietqr.io/img/STB.png', 'Sacombank', 'SGTTVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (12, 'Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh', 'HDB', '970437', 'https://cdn.vietqr.io/img/HDB.png', 'HDBank', 'HDBCVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (13, 'Ngân hàng TMCP Bản Việt', 'VCCB', '970454', 'https://cdn.vietqr.io/img/VCCB.png', 'VietCapitalBank', 'VCBCVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (14, 'Ngân hàng TMCP Sài Gòn', 'SCB', '970429', 'https://cdn.vietqr.io/img/SCB.png', 'SCB', 'SACLVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (15, 'Ngân hàng TMCP Quốc tế Việt Nam', 'VIB', '970441', 'https://cdn.vietqr.io/img/VIB.png', 'VIB', 'VNIBVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (16, 'Ngân hàng TMCP Sài Gòn - Hà Nội', 'SHB', '970443', 'https://cdn.vietqr.io/img/SHB.png', 'SHB', 'SHBAVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (17, 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam', 'EIB', '970431', 'https://cdn.vietqr.io/img/EIB.png', 'Eximbank', 'EBVIVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (18, 'Ngân hàng TMCP Hàng Hải Việt Nam', 'MSB', '970426', 'https://cdn.vietqr.io/img/MSB.png', 'MSB', 'MCOBVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (19, 'Ngân hàng TMCP Sài Gòn Công Thương', 'SGICB', '970400', 'https://cdn.vietqr.io/img/SGICB.png', 'SaigonBank', 'SBITVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (20, 'Ngân hàng TMCP Bắc Á', 'BAB', '970409', 'https://cdn.vietqr.io/img/BAB.png', 'BacABank', 'NASCVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (21, 'Ngân hàng TMCP Đại Chúng Việt Nam Ngân hàng số', 'PVDB', '971133', 'https://cdn.vietqr.io/img/PVCB.png', 'PVcomBank Pay', 'WBVNVNVX', 1, -1, -1, 0, 1790991989);

INSERT IGNORE INTO `dt_banks` VALUES (22, 'Ngân hàng TMCP Đại Chúng Việt Nam', 'PVCB', '970412', 'https://cdn.vietqr.io/img/PVCB.png', 'PVcomBank', 'WBVNVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (23, 'Ngân hàng TNHH MTV Việt Nam Hiện Đại', 'MBV', '970414', 'https://cdn.vietqr.io/img/MBV.png', 'MBV', 'OCBKUS3M', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (24, 'Ngân hàng TMCP Quốc Dân', 'NCB', '970419', 'https://cdn.vietqr.io/img/NCB.png', 'NCB', 'NVBAVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (25, 'Ngân hàng TNHH MTV Shinhan Việt Nam', 'SHBVN', '970424', 'https://cdn.vietqr.io/img/SHBVN.png', 'ShinhanBank', 'SHBKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (26, 'Ngân hàng TMCP An Bình', 'ABB', '970425', 'https://cdn.vietqr.io/img/ABB.png', 'ABBANK', 'ABBKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (27, 'Ngân hàng TMCP Việt Á', 'VAB', '970427', 'https://cdn.vietqr.io/img/VAB.png', 'VietABank', 'VNACVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (28, 'Ngân hàng TMCP Nam Á', 'NAB', '970428', 'https://cdn.vietqr.io/img/NAB.png', 'NamABank', 'NAMAVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (29, 'Ngân hàng TMCP Thịnh vượng và Phát triển', 'PGB', '970430', 'https://cdn.vietqr.io/img/PGB.png', 'PGBank', 'PGBLVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (30, 'Ngân hàng TMCP Việt Nam Thương Tín', 'VIETBANK', '970433', 'https://cdn.vietqr.io/img/VIETBANK.png', 'VietBank', 'VNTTVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (31, 'Ngân hàng TMCP Bảo Việt', 'BVB', '970438', 'https://cdn.vietqr.io/img/BVB.png', 'BaoVietBank', 'BVBVVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (32, 'Ngân hàng TMCP Đông Nam Á', 'SEAB', '970440', 'https://cdn.vietqr.io/img/SEAB.png', 'SeABank', 'SEAVVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (33, 'Ngân hàng TMCP Lộc Phát Việt Nam', 'LPB', '970449', 'https://cdn.vietqr.io/img/LPB.png', 'LPBank', 'LVBKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (34, 'Ngân hàng TMCP Kiên Long', 'KLB', '970452', 'https://cdn.vietqr.io/img/KLB.png', 'KienLongBank', 'KLBKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (35, 'Ngân hàng Đại chúng TNHH Kasikornbank', 'KBank', '668888', 'https://cdn.vietqr.io/img/KBANK.png', 'KBank', 'KASIVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (36, 'Ngân hàng TNHH MTV Hong Leong Việt Nam', 'HLBVN', '970442', 'https://cdn.vietqr.io/img/HLBVN.png', 'HongLeong', 'HLBBVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (37, 'Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam', 'CBB', '970444', 'https://cdn.vietqr.io/img/CBB.png', 'CBBank', 'GTBAVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (38, 'Ngân hàng TNHH MTV CIMB Việt Nam', 'CIMB', '422589', 'https://cdn.vietqr.io/img/CIMB.png', 'CIMB', 'CIBBVNVN', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (39, 'DBS Bank Ltd - Chi nhánh Thành phố Hồ Chí Minh', 'DBS', '796500', 'https://cdn.vietqr.io/img/DBS.png', 'DBSBank', 'DBSSVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (40, 'Ngân hàng TNHH MTV Số Vikki', 'Vikki', '970406', 'https://cdn.vietqr.io/img/Vikki.png', 'Vikki', 'EACBVNVX', 1, 1, -1, 0, 1791444647);

INSERT IGNORE INTO `dt_banks` VALUES (41, 'Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu', 'GPB', '970408', 'https://cdn.vietqr.io/img/GPB.png', 'GPBank', 'GBNKVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (42, 'Ngân hàng TNHH MTV HSBC (Việt Nam)', 'HSBC', '458761', 'https://cdn.vietqr.io/img/HSBC.png', 'HSBC', 'HSBCVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (43, 'Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam', 'SCVN', '970410', 'https://cdn.vietqr.io/img/SCVN.png', 'StandardChartered', 'SCBLVNVX', -1, -1, -1, 0, 0);

INSERT IGNORE INTO `dt_banks` VALUES (44, 'Ngân hàng TNHH MTV Public Việt Nam', 'PBVN', '970439', 'https://cdn.vietqr.io/img/PBVN.png', 'PublicBank', 'VIDPVNVX', -1, -1, -1, 0, 0);

START TRANSACTION;

INSERT INTO dt_roles (type,name,status,remark,create_time,update_time) SELECT 'channel','渠道',1,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_roles WHERE type='channel');

INSERT INTO dt_roles (type,name,status,remark,create_time,update_time) SELECT 'merchant','商户',1,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_roles WHERE type='merchant');

INSERT INTO dt_roles (type,name,status,remark,create_time,update_time) SELECT 'task','任务服务',1,'',UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_roles WHERE type='task');

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'银行管理','group',NULL,'/admin/bank','admin','','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/bank');

SET @vn_node_47=(SELECT id FROM dt_perms WHERE route_path='/admin/bank' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_47,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_47 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_47,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_47 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_47,'银行列表','page',NULL,'/admin/bank/index','admin','Banks','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/bank/index');

SET @vn_node_48=(SELECT id FROM dt_perms WHERE route_path='/admin/bank/index' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_48,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_48 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_48,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_48 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_48,'列表','action','admin.banks.items',NULL,'admin','Banks','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.banks.items');

SET @vn_node_49=(SELECT id FROM dt_perms WHERE permission_key='admin.banks.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_49,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_49 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_49,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_49 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_48,'新增','action','admin.banks.add',NULL,'admin','Banks','add',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.banks.add');

SET @vn_node_50=(SELECT id FROM dt_perms WHERE permission_key='admin.banks.add' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_50,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_50 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_50,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_50 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_48,'编辑','action','admin.banks.edit',NULL,'admin','Banks','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.banks.edit');

SET @vn_node_51=(SELECT id FROM dt_perms WHERE permission_key='admin.banks.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_51,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_51 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_51,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_51 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_48,'删除','action','admin.banks.del',NULL,'admin','Banks','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.banks.del');

SET @vn_node_52=(SELECT id FROM dt_perms WHERE permission_key='admin.banks.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_52,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_52 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_52,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_52 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_47,'银行户名','page',NULL,'/admin/bank/user','admin','BankUser','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/bank/user');

SET @vn_node_53=(SELECT id FROM dt_perms WHERE route_path='/admin/bank/user' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_53,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_53 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_53,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_53 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_53,'列表','action','admin.bankuser.items',NULL,'admin','BankUser','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankuser.items');

SET @vn_node_58=(SELECT id FROM dt_perms WHERE permission_key='admin.bankuser.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_58,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_58 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_58,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_58 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_53,'编辑','action','admin.bankuser.edit',NULL,'admin','BankUser','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankuser.edit');

SET @vn_node_60=(SELECT id FROM dt_perms WHERE permission_key='admin.bankuser.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_60,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_60 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_60,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_60 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_53,'删除','action','admin.bankuser.del',NULL,'admin','BankUser','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankuser.del');

SET @vn_node_61=(SELECT id FROM dt_perms WHERE permission_key='admin.bankuser.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_61,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_61 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_61,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_61 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_48,'getOptions','api','admin.banks.getoptions',NULL,'admin','Banks','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.banks.getoptions');

SET @vn_node_62=(SELECT id FROM dt_perms WHERE permission_key='admin.banks.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_62,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_62 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_62,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_62 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_53,'getOptions','api','admin.bankuser.getoptions',NULL,'admin','BankUser','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankuser.getoptions');

SET @vn_node_63=(SELECT id FROM dt_perms WHERE permission_key='admin.bankuser.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_63,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_63 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_63,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_63 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_47,'银行卡号','page',NULL,'/admin/bank/cards','admin','BankCards','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/bank/cards');

SET @vn_node_64=(SELECT id FROM dt_perms WHERE route_path='/admin/bank/cards' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_64,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_64 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_64,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_64 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_64,'列表','action','admin.bankcards.items',NULL,'admin','BankCards','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankcards.items');

SET @vn_node_65=(SELECT id FROM dt_perms WHERE permission_key='admin.bankcards.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_65,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_65 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_65,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_65 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_64,'编辑','action','admin.bankcards.edit',NULL,'admin','BankCards','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankcards.edit');

SET @vn_node_67=(SELECT id FROM dt_perms WHERE permission_key='admin.bankcards.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_67,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_67 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_67,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_67 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_64,'删除','action','admin.bankcards.del',NULL,'admin','BankCards','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.bankcards.del');

SET @vn_node_68=(SELECT id FROM dt_perms WHERE permission_key='admin.bankcards.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_68,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_68 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_68,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_68 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'码商银行管理','group',NULL,'/channel/bank','channel','','',1000,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/bank');

SET @vn_node_69=(SELECT id FROM dt_perms WHERE route_path='/channel/bank' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_69,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_69 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_69,'银行户名','page',NULL,'/channel/bank/user','channel','BankUser','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/bank/user');

SET @vn_node_70=(SELECT id FROM dt_perms WHERE route_path='/channel/bank/user' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_70,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_70 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_70,'列表','action','channel.bankuser.items',NULL,'channel','BankUser','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankuser.items');

SET @vn_node_71=(SELECT id FROM dt_perms WHERE permission_key='channel.bankuser.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_71,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_71 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_70,'新增','action','channel.bankuser.add',NULL,'channel','BankUser','add',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankuser.add');

SET @vn_node_72=(SELECT id FROM dt_perms WHERE permission_key='channel.bankuser.add' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_72,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_72 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_70,'编辑','action','channel.bankuser.edit',NULL,'channel','BankUser','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankuser.edit');

SET @vn_node_73=(SELECT id FROM dt_perms WHERE permission_key='channel.bankuser.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_73,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_73 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_70,'删除','action','channel.bankuser.del',NULL,'channel','BankUser','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankuser.del');

SET @vn_node_74=(SELECT id FROM dt_perms WHERE permission_key='channel.bankuser.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_74,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_74 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_69,'银行卡号','page',NULL,'/channel/bank/cards','channel','BankCards','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/bank/cards');

SET @vn_node_75=(SELECT id FROM dt_perms WHERE route_path='/channel/bank/cards' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_75,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_75 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_75,'列表','action','channel.bankcards.items',NULL,'channel','BankCards','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankcards.items');

SET @vn_node_76=(SELECT id FROM dt_perms WHERE permission_key='channel.bankcards.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_76,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_76 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_75,'新增','action','channel.bankcards.add',NULL,'channel','BankCards','add',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankcards.add');

SET @vn_node_77=(SELECT id FROM dt_perms WHERE permission_key='channel.bankcards.add' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_77,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_77 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_75,'编辑','action','channel.bankcards.edit',NULL,'channel','BankCards','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankcards.edit');

SET @vn_node_78=(SELECT id FROM dt_perms WHERE permission_key='channel.bankcards.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_78,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_78 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_75,'删除','action','channel.bankcards.del',NULL,'channel','BankCards','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankcards.del');

SET @vn_node_79=(SELECT id FROM dt_perms WHERE permission_key='channel.bankcards.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_79,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_79 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_70,'getOptions','api','channel.bankuser.getoptions',NULL,'channel','BankUser','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankuser.getoptions');

SET @vn_node_80=(SELECT id FROM dt_perms WHERE permission_key='channel.bankuser.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_80,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_80 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_75,'getOptions','api','channel.bankcards.getoptions',NULL,'channel','BankCards','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.bankcards.getoptions');

SET @vn_node_81=(SELECT id FROM dt_perms WHERE permission_key='channel.bankcards.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_81,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_81 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_69,'银行列表','group',NULL,NULL,'channel','Banks','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='channel' AND controller='Banks' AND node_type='group' AND name='银行列表');

SET @vn_node_82=(SELECT id FROM dt_perms WHERE module='channel' AND controller='Banks' AND node_type='group' AND name='银行列表' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_82,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_82 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_82,'getOptions','api','channel.banks.getoptions',NULL,'channel','Banks','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.banks.getoptions');

SET @vn_node_83=(SELECT id FROM dt_perms WHERE permission_key='channel.banks.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_83,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_83 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'码商设备管理','group',NULL,'/channel/device','channel','','',1999,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/device');

SET @vn_node_84=(SELECT id FROM dt_perms WHERE route_path='/channel/device' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_84,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_84 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_84,'通知推送','page',NULL,'/channel/device/notifyQrcode','channel','Device','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/device/notifyQrcode');

SET @vn_node_85=(SELECT id FROM dt_perms WHERE route_path='/channel/device/notifyQrcode' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_85,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_85 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_85,'getNotifyQrcode','action','channel.device.getnotifyqrcode',NULL,'channel','Device','getNotifyQrcode',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.device.getnotifyqrcode');

SET @vn_node_86=(SELECT id FROM dt_perms WHERE permission_key='channel.device.getnotifyqrcode' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_86,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_86 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'订单管理','group',NULL,'/admin/order','admin','','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/order');

SET @vn_node_87=(SELECT id FROM dt_perms WHERE route_path='/admin/order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_87,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_87 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_87,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_87 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_87,'支付方式','page',NULL,'/admin/order/payType','admin','PayType','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/order/payType');

SET @vn_node_88=(SELECT id FROM dt_perms WHERE route_path='/admin/order/payType' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_88,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_88 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_88,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_88 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_88,'列表','action','admin.paytype.items',NULL,'admin','PayType','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.paytype.items');

SET @vn_node_89=(SELECT id FROM dt_perms WHERE permission_key='admin.paytype.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_89,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_89 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_89,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_89 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_88,'新增','action','admin.paytype.add',NULL,'admin','PayType','add',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.paytype.add');

SET @vn_node_90=(SELECT id FROM dt_perms WHERE permission_key='admin.paytype.add' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_90,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_90 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_90,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_90 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_88,'编辑','action','admin.paytype.edit',NULL,'admin','PayType','edit',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.paytype.edit');

SET @vn_node_91=(SELECT id FROM dt_perms WHERE permission_key='admin.paytype.edit' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_91,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_91 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_91,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_91 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_88,'删除','action','admin.paytype.del',NULL,'admin','PayType','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.paytype.del');

SET @vn_node_92=(SELECT id FROM dt_perms WHERE permission_key='admin.paytype.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_92,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_92 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_92,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_92 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_88,'getOptions','api','admin.paytype.getoptions',NULL,'admin','PayType','getOptions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.paytype.getoptions');

SET @vn_node_93=(SELECT id FROM dt_perms WHERE permission_key='admin.paytype.getoptions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_93,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_93 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_93,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_93 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'OpenApi','group',NULL,NULL,'openapi','','',9999,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='openapi' AND controller='' AND node_type='group' AND name='OpenApi');

SET @vn_node_100=(SELECT id FROM dt_perms WHERE module='openapi' AND controller='' AND node_type='group' AND name='OpenApi' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_100,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_100 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_100,'Order','group',NULL,NULL,'openapi','Order','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='openapi' AND controller='Order' AND node_type='group' AND name='Order');

SET @vn_node_101=(SELECT id FROM dt_perms WHERE module='openapi' AND controller='Order' AND node_type='group' AND name='Order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_101,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_101 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_101,'create_order','api','openapi.order.create_order',NULL,'openapi','Order','create_order',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='openapi.order.create_order');

SET @vn_node_102=(SELECT id FROM dt_perms WHERE permission_key='openapi.order.create_order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_102,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_102 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_101,'query_order','api','openapi.order.query_order',NULL,'openapi','Order','query_order',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='openapi.order.query_order');

SET @vn_node_103=(SELECT id FROM dt_perms WHERE permission_key='openapi.order.query_order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_103,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_103 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_87,'订单列表','page',NULL,'/admin/order/index','admin','Order','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/order/index');

SET @vn_node_106=(SELECT id FROM dt_perms WHERE route_path='/admin/order/index' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_106,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_106 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_106,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_106 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_106,'列表','action','admin.order.items',NULL,'admin','Order','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.order.items');

SET @vn_node_107=(SELECT id FROM dt_perms WHERE permission_key='admin.order.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_107,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_107 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_107,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_107 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_106,'确认订单','action','admin.order.confirmed',NULL,'admin','Order','confirmed',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.order.confirmed');

SET @vn_node_108=(SELECT id FROM dt_perms WHERE permission_key='admin.order.confirmed' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_108,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_108 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_108,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_108 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_106,'回调','action','admin.order.notify',NULL,'admin','Order','notify',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.order.notify');

SET @vn_node_109=(SELECT id FROM dt_perms WHERE permission_key='admin.order.notify' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_109,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_109 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_109,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_109 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_106,'删除','action','admin.order.del',NULL,'admin','Order','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.order.del');

SET @vn_node_110=(SELECT id FROM dt_perms WHERE permission_key='admin.order.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_110,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_110 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_110,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_110 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_101,'banks','api','openapi.order.banks',NULL,'openapi','Order','banks',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='openapi.order.banks');

SET @vn_node_111=(SELECT id FROM dt_perms WHERE permission_key='openapi.order.banks' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_111,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_111 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'码商订单管理','group',NULL,'/channel/order','channel','','',1001,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/order');

SET @vn_node_112=(SELECT id FROM dt_perms WHERE route_path='/channel/order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_112,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_112 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_112,'订单列表','page',NULL,'/channel/order/index','channel','Order','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/channel/order/index');

SET @vn_node_113=(SELECT id FROM dt_perms WHERE route_path='/channel/order/index' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_113,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_113 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_113,'列表','action','channel.order.items',NULL,'channel','Order','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.order.items');

SET @vn_node_114=(SELECT id FROM dt_perms WHERE permission_key='channel.order.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_114,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_114 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_113,'确认订单','action','channel.order.confirmed',NULL,'channel','Order','confirmed',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.order.confirmed');

SET @vn_node_115=(SELECT id FROM dt_perms WHERE permission_key='channel.order.confirmed' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_115,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_115 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_113,'回调','action','channel.order.notify',NULL,'channel','Order','notify',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='channel.order.notify');

SET @vn_node_116=(SELECT id FROM dt_perms WHERE permission_key='channel.order.notify' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_116,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_116 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'商户订单管理','group',NULL,'/merchant/order','merchant','','',2000,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/merchant/order');

SET @vn_node_117=(SELECT id FROM dt_perms WHERE route_path='/merchant/order' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_117,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_117 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_117,'订单列表','page',NULL,'/merchant/order/index','merchant','Order','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/merchant/order/index');

SET @vn_node_118=(SELECT id FROM dt_perms WHERE route_path='/merchant/order/index' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_118,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_118 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_118,'列表','action','merchant.order.items',NULL,'merchant','Order','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='merchant.order.items');

SET @vn_node_119=(SELECT id FROM dt_perms WHERE permission_key='merchant.order.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_119,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_119 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_118,'回调','action','merchant.order.notify',NULL,'merchant','Order','notify',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='merchant.order.notify');

SET @vn_node_120=(SELECT id FROM dt_perms WHERE permission_key='merchant.order.notify' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'merchant',@vn_node_120,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_120 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT 0,'Task','group',NULL,NULL,'task','','',9999,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='task' AND controller='' AND node_type='group' AND name='Task');

SET @vn_node_127=(SELECT id FROM dt_perms WHERE module='task' AND controller='' AND node_type='group' AND name='Task' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_127,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_127 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_127,'Notify','group',NULL,NULL,'task','Notify','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='task' AND controller='Notify' AND node_type='group' AND name='Notify');

SET @vn_node_128=(SELECT id FROM dt_perms WHERE module='task' AND controller='Notify' AND node_type='group' AND name='Notify' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_128,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_128 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_128,'add','api','task.notify.add',NULL,'task','Notify','add',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.notify.add');

SET @vn_node_129=(SELECT id FROM dt_perms WHERE permission_key='task.notify.add' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_129,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_129 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'channel',@vn_node_129,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_129 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_128,'getPending','api','task.notify.getpending',NULL,'task','Notify','getPending',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.notify.getpending');

SET @vn_node_130=(SELECT id FROM dt_perms WHERE permission_key='task.notify.getpending' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_130,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_130 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_127,'API','group',NULL,NULL,'task','Api','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='task' AND controller='Api' AND node_type='group' AND name='API');

SET @vn_node_131=(SELECT id FROM dt_perms WHERE module='task' AND controller='Api' AND node_type='group' AND name='API' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_131,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_131 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_131,'getNotifyTask','api','task.api.getnotifytask',NULL,'task','Api','getNotifyTask',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.api.getnotifytask');

SET @vn_node_132=(SELECT id FROM dt_perms WHERE permission_key='task.api.getnotifytask' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_132,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_132 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_131,'setNotifyStatus','api','task.api.setnotifystatus',NULL,'task','Api','setNotifyStatus',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.api.setnotifystatus');

SET @vn_node_133=(SELECT id FROM dt_perms WHERE permission_key='task.api.setnotifystatus' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_133,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_133 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_128,'setPending','api','task.notify.setpending',NULL,'task','Notify','setPending',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.notify.setpending');

SET @vn_node_134=(SELECT id FROM dt_perms WHERE permission_key='task.notify.setpending' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_134,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_134 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_87,'设备推送','page',NULL,'/admin/order/deviceNotify','admin','DeviceNotify','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE route_path='/admin/order/deviceNotify');

SET @vn_node_135=(SELECT id FROM dt_perms WHERE route_path='/admin/order/deviceNotify' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_135,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_135 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_135,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_135 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_135,'列表','action','admin.devicenotify.items',NULL,'admin','DeviceNotify','items',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.devicenotify.items');

SET @vn_node_136=(SELECT id FROM dt_perms WHERE permission_key='admin.devicenotify.items' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_136,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_136 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_136,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_136 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_135,'删除','action','admin.devicenotify.del',NULL,'admin','DeviceNotify','del',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.devicenotify.del');

SET @vn_node_137=(SELECT id FROM dt_perms WHERE permission_key='admin.devicenotify.del' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_137,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_137 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_137,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_137 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_135,'重置','action','admin.devicenotify.reset',NULL,'admin','DeviceNotify','reset',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='admin.devicenotify.reset');

SET @vn_node_138=(SELECT id FROM dt_perms WHERE permission_key='admin.devicenotify.reset' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'super',@vn_node_138,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_138 IS NOT NULL;

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'admin',@vn_node_138,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_138 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_127,'Online','group',NULL,NULL,'task','Online','',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE module='task' AND controller='Online' AND node_type='group' AND name='Online');

SET @vn_node_139=(SELECT id FROM dt_perms WHERE module='task' AND controller='Online' AND node_type='group' AND name='Online' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_139,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_139 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_139,'getBank','api','task.online.getbank',NULL,'task','Online','getBank',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.online.getbank');

SET @vn_node_140=(SELECT id FROM dt_perms WHERE permission_key='task.online.getbank' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_140,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_140 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_139,'getSmsCode','api','task.online.getsmscode',NULL,'task','Online','getSmsCode',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.online.getsmscode');

SET @vn_node_141=(SELECT id FROM dt_perms WHERE permission_key='task.online.getsmscode' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_141,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_141 IS NOT NULL;

INSERT INTO dt_perms (parent_id,name,node_type,permission_key,route_path,module,controller,action,sort,status,create_time,update_time) SELECT @vn_node_139,'upTransactions','api','task.online.uptransactions',NULL,'task','Online','upTransactions',0,1,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE NOT EXISTS (SELECT 1 FROM dt_perms WHERE permission_key='task.online.uptransactions');

SET @vn_node_142=(SELECT id FROM dt_perms WHERE permission_key='task.online.uptransactions' LIMIT 1);

INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time) SELECT 'task',@vn_node_142,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() WHERE @vn_node_142 IS NOT NULL;

-- Shared read-only payment method selector used by channel and merchant order pages.
INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time)
SELECT r.type,p.id,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM dt_roles r
JOIN dt_perms p ON p.permission_key='admin.paytype.getoptions'
WHERE r.type IN ('channel','merchant');

-- Reuse the framework personal balance page and its own-account endpoints.
INSERT IGNORE INTO dt_role_perms (type,perms_id,create_time,update_time)
SELECT r.type,p.id,UNIX_TIMESTAMP(),UNIX_TIMESTAMP() FROM dt_roles r
JOIN dt_perms p ON p.route_path IN ('/user','/user/balance')
    OR p.permission_key IN ('user.balance.info','user.balance.items')
WHERE r.type IN ('channel','merchant');

COMMIT;
