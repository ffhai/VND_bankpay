/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : 127.0.0.1:3306
 Source Schema         : vnd_bankpay

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 22/10/2026 13:41:33
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for dt_app_list
-- ----------------------------
DROP TABLE IF EXISTS `dt_app_list`;
CREATE TABLE `dt_app_list`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0,
  `appid` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `secret` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `remark` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `create_time` int(10) NOT NULL DEFAULT 0,
  `update_time` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `appid`(`appid`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE COMMENT '创建时间区间筛选'
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '应用接口' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_app_list
-- ----------------------------
INSERT INTO `dt_app_list` VALUES (1, 1, 'admin', '1d3gweoqpnqyjponpzovna', 1, '', 1782270416, 1792075267);
INSERT INTO `dt_app_list` VALUES (2, 3, 'test_merchant', 'm3vokxdps98e34jwwzhy5', 1, '', 1792586694, 1792586694);
INSERT INTO `dt_app_list` VALUES (3, 31, 'test_channel', 'pg1e9bgysbiexgd69kvgn', 1, '', 1792591285, 1792591285);

-- ----------------------------
-- Table structure for dt_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `dt_balance_log`;
CREATE TABLE `dt_balance_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `change_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数变动，绝对值最多13位',
  `balance_after` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '交易类型',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作员',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE COMMENT '时间索引',
  INDEX `idx_user_time`(`user_id`, `create_time`) USING BTREE COMMENT '用户+时间组合索引(按用户查流水并排序)'
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '余额变动记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_balance_log
-- ----------------------------
INSERT INTO `dt_balance_log` VALUES (1, 31, 10000, 10000, 'adjust', '', 'admin', 1792630205);
INSERT INTO `dt_balance_log` VALUES (2, 31, -100, 9900, 'CreateOrder', '7O2208500949995298', 'sys', 1792630209);
INSERT INTO `dt_balance_log` VALUES (3, 3, 100, 100, 'Settlement', '截止1792630240，合计100，费率10.0000%，本金', 'sys', 1792630240);
INSERT INTO `dt_balance_log` VALUES (4, 3, -10, 90, 'Settlement', '截止1792630240，合计100，费率10.0000%，商户手续费', 'sys', 1792630240);
INSERT INTO `dt_balance_log` VALUES (5, 31, 1, 9901, 'Settlement', '截止1792630240，合计100，费率1.0000%，码商佣金', 'sys', 1792630240);
INSERT INTO `dt_balance_log` VALUES (6, 31, -100, 9801, 'CreateOrder', 'AS2208522052549749', 'sys', 1792630340);
INSERT INTO `dt_balance_log` VALUES (7, 3, 100, 190, 'Settlement', '截止2026-10-22 08:52:30，合计100，费率10.0000%，本金', 'sys', 1792630350);
INSERT INTO `dt_balance_log` VALUES (8, 3, -10, 180, 'Settlement', '截止2026-10-22 08:52:30，合计100，费率10.0000%，商户手续费', 'sys', 1792630350);
INSERT INTO `dt_balance_log` VALUES (9, 31, 1, 9802, 'Settlement', '截止2026-10-22 08:52:30，合计100，费率1.0000%，码商佣金', 'sys', 1792630350);
INSERT INTO `dt_balance_log` VALUES (10, 31, -100, 9702, 'CreateOrder', 'DF2209054557539799', 'sys', 1792631145);
INSERT INTO `dt_balance_log` VALUES (11, 31, -100, 9602, 'CreateOrder', '3Y2209201097971029', 'sys', 1792632010);

-- ----------------------------
-- Table structure for dt_bank_cards
-- ----------------------------
DROP TABLE IF EXISTS `dt_bank_cards`;
CREATE TABLE `dt_bank_cards`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NULL DEFAULT 0,
  `bank_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `bank_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `bank_user_id` int(10) NULL DEFAULT 0,
  `balance` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位',
  `online_account` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '网银-账号',
  `online_password` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '网银-密码',
  `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '手机号码',
  `notify_type` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '回调方式 app/online',
  `notify_device` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '回调设备名称',
  `limit_in_max` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数入款限额，最多13位',
  `limit_out_max` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数出款限额，最多13位',
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
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_bank_cards
-- ----------------------------
INSERT INTO `dt_bank_cards` VALUES (10, 31, '80000186204', 'VCB', 10, 70200, '', '', '13000000000', 'online', '', 0, 0, '', 1, 0, 1792587971, 1792641405);

-- ----------------------------
-- Table structure for dt_bank_user
-- ----------------------------
DROP TABLE IF EXISTS `dt_bank_user`;
CREATE TABLE `dt_bank_user`  (
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
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_bank_user
-- ----------------------------
INSERT INTO `dt_bank_user` VALUES (10, 31, 'VO THI TUYET HONG', '11223344', '', 1, 1792587590, 1792587865);

-- ----------------------------
-- Table structure for dt_banks
-- ----------------------------
DROP TABLE IF EXISTS `dt_banks`;
CREATE TABLE `dt_banks`  (
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
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_banks
-- ----------------------------
INSERT INTO `dt_banks` VALUES (1, 'Ngân hàng TMCP Công thương Việt Nam', 'ICB', '970415', 'https://cdn.vietqr.io/img/ICB.png', 'VietinBank', 'ICBVVNVX', 1, 1, 1, 0, 1790992102);
INSERT INTO `dt_banks` VALUES (2, 'Ngân hàng TMCP Ngoại Thương Việt Nam', 'VCB', '970436', 'https://cdn.vietqr.io/img/VCB.png', 'Vietcombank', 'BFTVVNVX', 1, 1, -1, 0, 1790992136);
INSERT INTO `dt_banks` VALUES (3, 'Ngân hàng TMCP Đầu tư và Phát triển Việt Nam', 'BIDV', '970418', 'https://cdn.vietqr.io/img/BIDV.png', 'BIDV', 'BIDVVNVX', 1, -1, 1, 0, 1790992152);
INSERT INTO `dt_banks` VALUES (4, 'Ngân hàng Nông nghiệp và Phát triển Nông thôn Việt Nam', 'VBA', '970405', 'https://cdn.vietqr.io/img/VBA.png', 'Agribank', 'VBAAVNVX', 1, 1, 1, 0, 1790992158);
INSERT INTO `dt_banks` VALUES (5, 'Ngân hàng TMCP Phương Đông', 'OCB', '970448', 'https://cdn.vietqr.io/img/OCB.png', 'OCB', 'ORCOVNVX', -1, 1, 1, 0, 1790992168);
INSERT INTO `dt_banks` VALUES (6, 'Ngân hàng TMCP Quân đội', 'MB', '970422', 'https://cdn.vietqr.io/img/MB.png', 'MBBank', 'MSCBVNVX', 1, 1, 1, 0, 1790992174);
INSERT INTO `dt_banks` VALUES (7, 'Ngân hàng TMCP Kỹ thương Việt Nam', 'TCB', '970407', 'https://cdn.vietqr.io/img/TCB.png', 'Techcombank', 'VTCBVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (8, 'Ngân hàng TMCP Á Châu', 'ACB', '970416', 'https://cdn.vietqr.io/img/ACB.png', 'ACB', 'ASCBVNVX', 1, 1, 1, 0, 1790994645);
INSERT INTO `dt_banks` VALUES (9, 'Ngân hàng TMCP Việt Nam Thịnh Vượng', 'VPB', '970432', 'https://cdn.vietqr.io/img/VPB.png', 'VPBank', 'VPBKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (10, 'Ngân hàng TMCP Tiên Phong', 'TPB', '970423', 'https://cdn.vietqr.io/img/TPB.png', 'TPBank', 'TPBVVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (11, 'Ngân hàng TMCP Sài Gòn Thương Tín', 'STB', '970403', 'https://cdn.vietqr.io/img/STB.png', 'Sacombank', 'SGTTVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (12, 'Ngân hàng TMCP Phát triển Thành phố Hồ Chí Minh', 'HDB', '970437', 'https://cdn.vietqr.io/img/HDB.png', 'HDBank', 'HDBCVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (13, 'Ngân hàng TMCP Bản Việt', 'VCCB', '970454', 'https://cdn.vietqr.io/img/VCCB.png', 'VietCapitalBank', 'VCBCVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (14, 'Ngân hàng TMCP Sài Gòn', 'SCB', '970429', 'https://cdn.vietqr.io/img/SCB.png', 'SCB', 'SACLVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (15, 'Ngân hàng TMCP Quốc tế Việt Nam', 'VIB', '970441', 'https://cdn.vietqr.io/img/VIB.png', 'VIB', 'VNIBVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (16, 'Ngân hàng TMCP Sài Gòn - Hà Nội', 'SHB', '970443', 'https://cdn.vietqr.io/img/SHB.png', 'SHB', 'SHBAVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (17, 'Ngân hàng TMCP Xuất Nhập khẩu Việt Nam', 'EIB', '970431', 'https://cdn.vietqr.io/img/EIB.png', 'Eximbank', 'EBVIVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (18, 'Ngân hàng TMCP Hàng Hải Việt Nam', 'MSB', '970426', 'https://cdn.vietqr.io/img/MSB.png', 'MSB', 'MCOBVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (19, 'Ngân hàng TMCP Sài Gòn Công Thương', 'SGICB', '970400', 'https://cdn.vietqr.io/img/SGICB.png', 'SaigonBank', 'SBITVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (20, 'Ngân hàng TMCP Bắc Á', 'BAB', '970409', 'https://cdn.vietqr.io/img/BAB.png', 'BacABank', 'NASCVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (21, 'Ngân hàng TMCP Đại Chúng Việt Nam Ngân hàng số', 'PVDB', '971133', 'https://cdn.vietqr.io/img/PVCB.png', 'PVcomBank Pay', 'WBVNVNVX', 1, -1, -1, 0, 1790991989);
INSERT INTO `dt_banks` VALUES (22, 'Ngân hàng TMCP Đại Chúng Việt Nam', 'PVCB', '970412', 'https://cdn.vietqr.io/img/PVCB.png', 'PVcomBank', 'WBVNVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (23, 'Ngân hàng TNHH MTV Việt Nam Hiện Đại', 'MBV', '970414', 'https://cdn.vietqr.io/img/MBV.png', 'MBV', 'OCBKUS3M', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (24, 'Ngân hàng TMCP Quốc Dân', 'NCB', '970419', 'https://cdn.vietqr.io/img/NCB.png', 'NCB', 'NVBAVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (25, 'Ngân hàng TNHH MTV Shinhan Việt Nam', 'SHBVN', '970424', 'https://cdn.vietqr.io/img/SHBVN.png', 'ShinhanBank', 'SHBKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (26, 'Ngân hàng TMCP An Bình', 'ABB', '970425', 'https://cdn.vietqr.io/img/ABB.png', 'ABBANK', 'ABBKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (27, 'Ngân hàng TMCP Việt Á', 'VAB', '970427', 'https://cdn.vietqr.io/img/VAB.png', 'VietABank', 'VNACVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (28, 'Ngân hàng TMCP Nam Á', 'NAB', '970428', 'https://cdn.vietqr.io/img/NAB.png', 'NamABank', 'NAMAVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (29, 'Ngân hàng TMCP Thịnh vượng và Phát triển', 'PGB', '970430', 'https://cdn.vietqr.io/img/PGB.png', 'PGBank', 'PGBLVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (30, 'Ngân hàng TMCP Việt Nam Thương Tín', 'VIETBANK', '970433', 'https://cdn.vietqr.io/img/VIETBANK.png', 'VietBank', 'VNTTVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (31, 'Ngân hàng TMCP Bảo Việt', 'BVB', '970438', 'https://cdn.vietqr.io/img/BVB.png', 'BaoVietBank', 'BVBVVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (32, 'Ngân hàng TMCP Đông Nam Á', 'SEAB', '970440', 'https://cdn.vietqr.io/img/SEAB.png', 'SeABank', 'SEAVVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (33, 'Ngân hàng TMCP Lộc Phát Việt Nam', 'LPB', '970449', 'https://cdn.vietqr.io/img/LPB.png', 'LPBank', 'LVBKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (34, 'Ngân hàng TMCP Kiên Long', 'KLB', '970452', 'https://cdn.vietqr.io/img/KLB.png', 'KienLongBank', 'KLBKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (35, 'Ngân hàng Đại chúng TNHH Kasikornbank', 'KBank', '668888', 'https://cdn.vietqr.io/img/KBANK.png', 'KBank', 'KASIVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (36, 'Ngân hàng TNHH MTV Hong Leong Việt Nam', 'HLBVN', '970442', 'https://cdn.vietqr.io/img/HLBVN.png', 'HongLeong', 'HLBBVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (37, 'Ngân hàng Thương mại TNHH MTV Xây dựng Việt Nam', 'CBB', '970444', 'https://cdn.vietqr.io/img/CBB.png', 'CBBank', 'GTBAVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (38, 'Ngân hàng TNHH MTV CIMB Việt Nam', 'CIMB', '422589', 'https://cdn.vietqr.io/img/CIMB.png', 'CIMB', 'CIBBVNVN', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (39, 'DBS Bank Ltd - Chi nhánh Thành phố Hồ Chí Minh', 'DBS', '796500', 'https://cdn.vietqr.io/img/DBS.png', 'DBSBank', 'DBSSVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (40, 'Ngân hàng TNHH MTV Số Vikki', 'Vikki', '970406', 'https://cdn.vietqr.io/img/Vikki.png', 'Vikki', 'EACBVNVX', 1, 1, -1, 0, 1791444647);
INSERT INTO `dt_banks` VALUES (41, 'Ngân hàng Thương mại TNHH MTV Dầu Khí Toàn Cầu', 'GPB', '970408', 'https://cdn.vietqr.io/img/GPB.png', 'GPBank', 'GBNKVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (42, 'Ngân hàng TNHH MTV HSBC (Việt Nam)', 'HSBC', '458761', 'https://cdn.vietqr.io/img/HSBC.png', 'HSBC', 'HSBCVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (43, 'Ngân hàng TNHH MTV Standard Chartered Bank Việt Nam', 'SCVN', '970410', 'https://cdn.vietqr.io/img/SCVN.png', 'StandardChartered', 'SCBLVNVX', -1, -1, -1, 0, 0);
INSERT INTO `dt_banks` VALUES (44, 'Ngân hàng TNHH MTV Public Việt Nam', 'PBVN', '970439', 'https://cdn.vietqr.io/img/PBVN.png', 'PublicBank', 'VIDPVNVX', -1, -1, -1, 0, 0);

-- ----------------------------
-- Table structure for dt_config
-- ----------------------------
DROP TABLE IF EXISTS `dt_config`;
CREATE TABLE `dt_config`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) NOT NULL DEFAULT 0 COMMENT '分组ID',
  `key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '英文名称(唯一标识)',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '类型 text image select',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '中文名称',
  `content` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '内容',
  `placeholder` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '提示内容',
  `params` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '附加内容',
  `sort` int(11) NOT NULL DEFAULT 50,
  `show` tinyint(1) NOT NULL DEFAULT 1 COMMENT '是否显示 1是 -1否',
  `create_time` int(10) NOT NULL DEFAULT 0,
  `update_time` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_key`(`key`) USING BTREE COMMENT '唯一标识(供 getContentByKey 精确查找)',
  INDEX `idx_group_show_sort`(`group_id`, `show`, `sort`) USING BTREE COMMENT '设置页按分组取显示项并排序'
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_config
-- ----------------------------
INSERT INTO `dt_config` VALUES (1, 1, 'site_name', 'text', '站点名称', '管理面板', '', '', 10, 1, 0, 1789525468);
INSERT INTO `dt_config` VALUES (2, 1, 'site_status', 'select', '是否闭站', '1', '站点关闭后，用户无法登录', '[{\"id\":\"1\",\"name\":\"否\"},{\"id\":\"2\",\"name\":\"是\"}]', 10, 1, 0, 1789525471);
INSERT INTO `dt_config` VALUES (3, 1, 'logo', 'image', 'LOGO', 'static/logo.png', '', '', 50, 1, 0, 1791939916);
INSERT INTO `dt_config` VALUES (4, 1, 'site_stop_msg', 'text', '闭站提示', '系统维护中...', '', '', 10, 1, 0, 1789525475);
INSERT INTO `dt_config` VALUES (5, 1, 'demo_textarea', 'textarea', '多行文本', '', '', '', 1, -1, 0, 1789525480);
INSERT INTO `dt_config` VALUES (6, 1, 'captcha_status', 'select', '登录验证码', '2', '', '[{\"id\":\"1\",\"name\":\"启用\"},{\"id\":\"2\",\"name\":\"关闭\"}]', 50, 1, 0, 1791098578);
INSERT INTO `dt_config` VALUES (7, 1, 'openapi_language', 'select', 'OpenAPI语言', 'zh-cn', '统一控制开放接口提示及收银台页面语言，默认中文', '[{\"id\":\"zh-cn\",\"name\":\"中文\"},{\"id\":\"en\",\"name\":\"English\"},{\"id\":\"vi\",\"name\":\"Tiếng Việt\"}]', 10, 1, 1792631077, 1792632444);

-- ----------------------------
-- Table structure for dt_config_group
-- ----------------------------
DROP TABLE IF EXISTS `dt_config_group`;
CREATE TABLE `dt_config_group`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `sort` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `create_time` int(10) NOT NULL DEFAULT 0,
  `update_time` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_status_sort`(`status`, `sort`) USING BTREE COMMENT '按状态筛选并排序'
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置分组' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_config_group
-- ----------------------------
INSERT INTO `dt_config_group` VALUES (1, '系统', 9999, 1, 1789522790, 1791098566);
INSERT INTO `dt_config_group` VALUES (2, '用户', 0, -1, 1789525592, 1792115153);

-- ----------------------------
-- Table structure for dt_device_notify
-- ----------------------------
DROP TABLE IF EXISTS `dt_device_notify`;
CREATE TABLE `dt_device_notify`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(10) NULL DEFAULT 0,
  `type` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `remark` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `time` int(10) NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1未处理 2已处理 -1处理失败',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `task_online_smscode`(`uid`, `type`, `phone_number`, `time`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 9 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_device_notify
-- ----------------------------
INSERT INTO `dt_device_notify` VALUES (7, 1, '', '13000000000', '{\"package_name\":\"com.msb.digibank.retail\",\"text\":\"\",\"tickerText\":\"\",\"extrasText\":\"T\\u00e0i kho\\u1ea3n: 800xx6204 S\\u1ed1 d\\u01b0: 70,300 VND ND: 80000186204-6282IBT1i1WJWDGM-DCZ8P1HF\"}', '', 1792639277, 1);
INSERT INTO `dt_device_notify` VALUES (8, 1, '', '13000000000', '{\"package_name\":\"com.google.android.apps.messaging\",\"text\":\"\",\"tickerText\":\"\",\"extrasText\":\"Tai khoan VCB Digibank cua Quy khach dang dang nhap tren trinh duyet Web MOI. Ma OTP xac thuc la 535928 hieu luc 1 phut. TUYET DOI KHONG DE LO MA OTP\"}', '', 1792639277, 1);

-- ----------------------------
-- Table structure for dt_editor
-- ----------------------------
DROP TABLE IF EXISTS `dt_editor`;
CREATE TABLE `dt_editor`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '英文名称(唯一标识)',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '中文名称',
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '内容',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `key`(`key`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '富文本内容' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_editor
-- ----------------------------
INSERT INTO `dt_editor` VALUES (1, 'notice', '系统通知内容', '<p>欢迎使用！</p>');
INSERT INTO `dt_editor` VALUES (2, 'test', '测试内容', '<p>666</p>');

-- ----------------------------
-- Table structure for dt_login_log
-- ----------------------------
DROP TABLE IF EXISTS `dt_login_log`;
CREATE TABLE `dt_login_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT 0,
  `device` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '登录设备',
  `time` int(10) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1成功 -1失败',
  `desc` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'IPv4/IPv6',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_uid_time`(`uid`, `time`) USING BTREE COMMENT '按用户+时间查登录记录'
) ENGINE = InnoDB AUTO_INCREMENT = 164 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '登录记录' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_login_log
-- ----------------------------
INSERT INTO `dt_login_log` VALUES (1, 1, '', 1782270368, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (2, 1, '', 1783329753, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (3, 1, '', 1783329799, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (4, 1, '', 1783329857, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (5, 1, '', 1783330232, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (6, 1, '', 1783330249, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (7, 1, '', 1783345808, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (8, 1, '', 1783346779, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (9, 1, '', 1783346797, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (10, 1, '', 1783346944, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (11, 1, '', 1783346955, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (12, 1, '', 1783346981, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (13, 1, '', 1783563287, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (14, 2, '', 1783578780, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (15, 2, '', 1783579393, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (16, 1, '', 1783584222, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (17, 1, '', 1783584232, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (18, 1, '', 1783584356, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (19, 1, '', 1783584548, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (20, 2, '', 1783584873, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (21, 1, '', 1783586391, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (22, 1, '', 1783604687, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (23, 1, '', 1783663239, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (24, 1, '', 1783663268, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (25, 2, '', 1783663716, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (26, 2, '', 1783663820, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (27, 1, '', 1783840267, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (28, 1, '', 1783841057, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (29, 1, '', 1783841513, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (30, 1, '', 1784517848, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (31, 1, '', 1786972382, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (32, 1, '', 1786973568, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (33, 1, '', 1787473675, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (34, 2, '', 1787474020, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (35, 2, '', 1787474021, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (36, 2, '', 1787474023, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (37, 2, '', 1787474024, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (38, 2, '', 1787474025, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (39, 2, '', 1787474041, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (40, 2, '', 1787474055, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (41, 2, '', 1787474057, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (42, 2, '', 1787474059, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (43, 2, '', 1787474061, 2, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (44, 1, '', 1787659816, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (45, 1, '', 1787660095, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (46, 1, '', 1787660801, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (47, 1, '', 1789483305, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (48, 1, '', 1789520922, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (49, 2, '', 1789527264, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (50, 1, '', 1789996309, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (51, 1, '', 1790041625, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (52, 1, '', 1791902647, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (53, 1, '', 1791902663, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (54, 1, '', 1791902984, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (55, 1, '', 1791905810, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (56, 1, '', 1791905829, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (57, 1, '', 1791905833, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (58, 3, '', 1791908670, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (59, 1, '', 1791910768, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (60, 3, '', 1791938387, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (61, 2, '', 1791938601, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (62, 2, '', 1791938616, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (63, 1, '', 1791981052, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (64, 1, '', 1792046481, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (65, 1, '', 1792046516, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (66, 1, '', 1792067221, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (67, 1, '', 1792067958, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (68, 1, '', 1792074057, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (69, 1, '', 1792074057, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (70, 1, '', 1792074057, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (71, 1, '', 1792074058, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (72, 2, '', 1792074060, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (73, 2, '', 1792074060, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (74, 2, '', 1792074060, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (75, 2, '', 1792074060, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (76, 2, '', 1792074060, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (77, 1, '', 1792074060, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (78, 1, '', 1792074061, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (79, 1, '', 1792074136, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (80, 1, '', 1792074136, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (81, 1, '', 1792074137, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (82, 1, '', 1792074138, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (83, 2, '', 1792074139, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (84, 2, '', 1792074139, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (85, 2, '', 1792074139, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (86, 2, '', 1792074139, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (87, 2, '', 1792074139, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (88, 1, '', 1792074139, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (89, 1, '', 1792074140, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (90, 1, '', 1792074174, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (91, 1, '', 1792074175, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (92, 1, '', 1792074175, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (93, 1, '', 1792074175, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (94, 2, '', 1792074177, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (95, 2, '', 1792074177, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (96, 2, '', 1792074177, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (97, 2, '', 1792074177, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (98, 2, '', 1792074177, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (99, 1, '', 1792074177, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (100, 1, '', 1792074177, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (101, 1, '', 1792074200, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (102, 1, '', 1792074207, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (103, 1, '', 1792074208, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (104, 1, '', 1792074208, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (105, 1, '', 1792074208, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (106, 2, '', 1792074209, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (107, 2, '', 1792074210, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (108, 2, '', 1792074210, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (109, 2, '', 1792074210, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (110, 2, '', 1792074210, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (111, 1, '', 1792074210, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (112, 1, '', 1792074210, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (113, 1, '', 1792074255, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (114, 1, '', 1792074271, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (115, 1, '', 1792074277, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (116, 1, '', 1792074382, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (117, 1, '', 1792074726, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (118, 1, '', 1792075267, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (119, 1, '', 1792075305, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (121, 1, '', 1792075410, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (123, 1, '', 1792075412, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (124, 1, '', 1792075412, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (125, 2, '', 1792075412, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (126, 2, '', 1792075412, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (127, 2, '', 1792075412, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (128, 2, '', 1792075413, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (129, 2, '', 1792075413, -1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (130, 1, '', 1792075413, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (131, 1, '', 1792075455, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (132, 1, '', 1792075499, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (133, 1, '', 1792075505, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (134, 1, '', 1792075571, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (135, 1, '', 1792075603, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (137, 1, '', 1792075624, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (138, 1, '', 1792075634, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (139, 1, '', 1792076114, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (143, 1, '', 1792076852, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (144, 1, '', 1792076899, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (145, 1, '', 1792076977, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (146, 1, '', 1792077780, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (147, 1, '', 1792078791, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (149, 1, '', 1792078968, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (150, 1, '', 1792079428, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (152, 1, '', 1792079433, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (154, 1, '', 1792079931, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (155, 1, '', 1792079967, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (156, 1, '', 1792080445, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (157, 1, '', 1792583310, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (158, 31, '', 1792586738, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (159, 3, '', 1792587263, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (160, 31, '', 1792632965, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (161, 3, '', 1792634036, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (162, 1, '', 1792638579, 1, '', '127.0.0.1');
INSERT INTO `dt_login_log` VALUES (163, 31, '', 1792639355, 1, '', '127.0.0.1');

-- ----------------------------
-- Table structure for dt_order
-- ----------------------------
DROP TABLE IF EXISTS `dt_order`;
CREATE TABLE `dt_order`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '系统订单号',
  `trade_no` varchar(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '商户订单号',
  `trade_uid` int(10) NULL DEFAULT 0,
  `pay_type` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '',
  `channel_uid` int(10) NULL DEFAULT 0,
  `bank_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '0' COMMENT '银行卡号码',
  `amount` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数金额，最多13位',
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
  `bank_card_id` int(11) NOT NULL DEFAULT 0 COMMENT '收款银行卡原始ID',
  `bank_user_id` int(11) NOT NULL DEFAULT 0 COMMENT '收款户名原始ID',
  `bank_code` varchar(50) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收款银行代码快照',
  `bank_bin` varchar(16) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收款银行BIN快照',
  `bank_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收款银行名称快照',
  `bank_short_name` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收款银行简称快照',
  `account_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '' COMMENT '收款户名快照',
  `snapshot_source` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT 'missing' COMMENT 'created/legacy_backfill/missing',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `order_id`(`order_id`) USING BTREE,
  UNIQUE INDEX `trade_no`(`trade_no`, `trade_uid`) USING BTREE,
  UNIQUE INDEX `ps`(`ps`) USING BTREE,
  INDEX `settlement`(`status`, `settlement_time`, `pay_time`) USING BTREE,
  INDEX `pendding1`(`bank_number`, `amount`) USING BTREE,
  INDEX `sumBankCards`(`bank_number`, `status`, `create_time`) USING BTREE,
  INDEX `idx_bank_card_id`(`bank_card_id`) USING BTREE,
  INDEX `idx_bank_user_id`(`bank_user_id`) USING BTREE,
  INDEX `idx_bank_code`(`bank_code`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_order
-- ----------------------------
INSERT INTO `dt_order` VALUES (4, '3Y2209201097971029', '1792632010', 3, 'bank2bank', 31, '80000186204', 100, 'DCZ8P1HF', 1, 1, 0, 'http://4.test.com/test/notify.php', 'http://4.test.com/test/return.php', 1792632010, 1792632610, 0, 0, 10, 10, 'VCB', '970436', 'Ngân hàng TMCP Ngoại Thương Việt Nam', 'Vietcombank', 'VO THI TUYET HONG', 'created');

-- ----------------------------
-- Table structure for dt_pay_type
-- ----------------------------
DROP TABLE IF EXISTS `dt_pay_type`;
CREATE TABLE `dt_pay_type`  (
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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci COMMENT = '通道' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_pay_type
-- ----------------------------
INSERT INTO `dt_pay_type` VALUES (1, 'bank2bank', '卡转卡', 1, 600, '', 1792585801, 1792585801);

-- ----------------------------
-- Table structure for dt_perms
-- ----------------------------
DROP TABLE IF EXISTS `dt_perms`;
CREATE TABLE `dt_perms`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `parent_id` int(11) NOT NULL DEFAULT 0 COMMENT '父级ID',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '权限名称',
  `node_type` varchar(16) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'group' COMMENT 'group/page/action/api',
  `permission_key` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT 'Stable backend permission key',
  `route_path` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NULL DEFAULT NULL COMMENT 'Registered frontend route path',
  `module` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '所属模块',
  `controller` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '控制器',
  `action` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL DEFAULT '' COMMENT '操作方法',
  `sort` int(11) NOT NULL DEFAULT 0 COMMENT '排序(升序)',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_permission_key`(`permission_key`) USING BTREE,
  UNIQUE INDEX `uk_route_path`(`route_path`) USING BTREE,
  INDEX `idx_module_controller_action`(`module`, `controller`, `action`) USING BTREE COMMENT '模块控制器方法组合索引',
  INDEX `idx_parent_sort`(`parent_id`, `sort`) USING BTREE COMMENT '父级+排序(菜单树/级联删除)'
) ENGINE = InnoDB AUTO_INCREMENT = 183 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限节点表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_perms
-- ----------------------------
INSERT INTO `dt_perms` VALUES (1, 0, '设置', 'group', NULL, '/admin/system', 'admin', '', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (2, 1, '系统设置', 'page', NULL, '/admin/system/config', 'admin', 'Config', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (3, 1, '富文本', 'page', NULL, '/admin/system/editor', 'admin', 'Editor', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (5, 3, '保存', 'action', 'admin.editor.save', NULL, 'admin', 'Editor', 'save', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (6, 3, '列表', 'action', 'admin.editor.items', NULL, 'admin', 'Editor', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (7, 2, '列表', 'action', 'admin.config.items', NULL, 'admin', 'Config', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (8, 2, '保存', 'action', 'admin.config.save', NULL, 'admin', 'Config', 'save', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (9, 0, '账号管理', 'group', NULL, '/admin/account', 'admin', '', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (10, 9, 'API管理', 'page', NULL, '/admin/account/appList', 'admin', 'AppList', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (11, 10, '列表', 'action', 'admin.applist.items', NULL, 'admin', 'AppList', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (12, 10, '新增', 'action', 'admin.applist.add', NULL, 'admin', 'AppList', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (13, 10, '编辑', 'action', 'admin.applist.edit', NULL, 'admin', 'AppList', 'edit', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (14, 10, '删除', 'action', 'admin.applist.del', NULL, 'admin', 'AppList', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (15, 9, '角色管理', 'page', NULL, '/admin/account/roles', 'admin', 'Roles', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (16, 15, '列表', 'action', 'admin.roles.items', NULL, 'admin', 'Roles', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (17, 15, 'getOptions', 'api', 'admin.roles.getoptions', NULL, 'admin', 'Roles', 'getOptions', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (18, 15, '新增', 'action', 'admin.roles.add', NULL, 'admin', 'Roles', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (19, 15, '编辑', 'action', 'admin.roles.edit', NULL, 'admin', 'Roles', 'edit', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (20, 15, '删除', 'action', 'admin.roles.del', NULL, 'admin', 'Roles', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (21, 9, '权限关联', 'group', NULL, NULL, 'admin', 'RolePerms', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (22, 21, '保存', 'action', 'admin.roleperms.save', NULL, 'admin', 'RolePerms', 'save', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (23, 9, '权限节点', 'page', NULL, '/admin/account/perms', 'admin', 'Perms', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (24, 23, '列表', 'action', 'admin.perms.items', NULL, 'admin', 'Perms', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (26, 23, '新增', 'action', 'admin.perms.add', NULL, 'admin', 'Perms', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (27, 23, '编辑', 'action', 'admin.perms.edit', NULL, 'admin', 'Perms', 'edit', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (28, 23, '删除', 'action', 'admin.perms.del', NULL, 'admin', 'Perms', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (29, 9, '账号列表', 'page', NULL, '/admin/account/member', 'admin', 'Member', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (30, 29, '列表', 'action', 'admin.member.items', NULL, 'admin', 'Member', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (31, 29, '新增', 'action', 'admin.member.add', NULL, 'admin', 'Member', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (32, 29, '编辑', 'action', 'admin.member.edit', NULL, 'admin', 'Member', 'edit', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (33, 29, '删除', 'action', 'admin.member.del', NULL, 'admin', 'Member', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (34, 1, '设置分组', 'page', NULL, '/admin/system/configGroup', 'admin', 'ConfigGroup', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (35, 34, '列表', 'action', 'admin.configgroup.items', NULL, 'admin', 'ConfigGroup', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (36, 34, '新增', 'action', 'admin.configgroup.add', NULL, 'admin', 'ConfigGroup', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (37, 34, '编辑', 'action', 'admin.configgroup.edit', NULL, 'admin', 'ConfigGroup', 'edit', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (38, 34, '删除', 'action', 'admin.configgroup.del', NULL, 'admin', 'ConfigGroup', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (39, 1, '设置列表', 'page', NULL, '/admin/system/configList', 'admin', 'ConfigList', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (40, 39, '列表', 'action', 'admin.configlist.items', NULL, 'admin', 'ConfigList', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (41, 39, '新增', 'action', 'admin.configlist.add', NULL, 'admin', 'ConfigList', 'add', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (44, 34, 'getOptions', 'api', 'admin.configgroup.getoptions', NULL, 'admin', 'ConfigGroup', 'getOptions', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (45, 39, '编辑', 'action', 'admin.configlist.edit', NULL, 'admin', 'ConfigList', 'edit', 0, 1, 1789617122, 1789617122);
INSERT INTO `dt_perms` VALUES (46, 39, '删除', 'action', 'admin.configlist.del', NULL, 'admin', 'ConfigList', 'del', 0, 1, 1789617122, 1789617122);
INSERT INTO `dt_perms` VALUES (47, 0, '财务管理', 'group', NULL, '/admin/finance', 'admin', '', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (48, 47, '余额记录', 'page', NULL, '/admin/finance/balance', 'admin', 'Balance', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (49, 48, '列表', 'action', 'admin.balance.items', NULL, 'admin', 'Balance', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (51, 48, '删除', 'action', 'admin.balance.del', NULL, 'admin', 'Balance', 'del', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (52, 29, 'getOptions', 'api', 'admin.member.getoptions', NULL, 'admin', 'Member', 'getOptions', 0, 1, 0, 1792123469);
INSERT INTO `dt_perms` VALUES (53, 0, '个人中心', 'group', NULL, '/user', 'user', '', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (54, 53, '我的余额', 'page', NULL, '/user/balance', 'user', 'Balance', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (55, 54, '余额信息', 'action', 'user.balance.info', NULL, 'user', 'Balance', 'info', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (56, 54, '交易记录', 'action', 'user.balance.items', NULL, 'user', 'Balance', 'items', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (57, 29, '调整余额', 'action', 'admin.member.adjust', NULL, 'admin', 'Member', 'adjust', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (58, 2, '清理缓存', 'action', 'admin.config.clearcache', NULL, 'admin', 'Config', 'clearCache', 0, 1, 1791938366, 1791938366);
INSERT INTO `dt_perms` VALUES (75, 1, '文件管理', 'page', NULL, '/admin/system/upload', 'admin', 'Upload', '', 10, 1, 1792464189, 1792464265);
INSERT INTO `dt_perms` VALUES (76, 75, '列表', 'action', 'admin.upload.items', NULL, 'admin', 'Upload', 'items', 0, 1, 1792464189, 1792464189);
INSERT INTO `dt_perms` VALUES (77, 75, '删除', 'action', 'admin.upload.del', NULL, 'admin', 'Upload', 'del', 0, 1, 1792464189, 1792464189);
INSERT INTO `dt_perms` VALUES (78, 0, '银行管理', 'group', NULL, '/admin/bank', 'admin', '', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (79, 78, '银行列表', 'page', NULL, '/admin/bank/index', 'admin', 'Banks', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (80, 79, '列表', 'action', 'admin.banks.items', NULL, 'admin', 'Banks', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (81, 79, '新增', 'action', 'admin.banks.add', NULL, 'admin', 'Banks', 'add', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (82, 79, '编辑', 'action', 'admin.banks.edit', NULL, 'admin', 'Banks', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (83, 79, '删除', 'action', 'admin.banks.del', NULL, 'admin', 'Banks', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (84, 78, '银行户名', 'page', NULL, '/admin/bank/user', 'admin', 'BankUser', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (85, 84, '列表', 'action', 'admin.bankuser.items', NULL, 'admin', 'BankUser', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (87, 84, '编辑', 'action', 'admin.bankuser.edit', NULL, 'admin', 'BankUser', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (88, 84, '删除', 'action', 'admin.bankuser.del', NULL, 'admin', 'BankUser', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (89, 79, 'getOptions', 'api', 'admin.banks.getoptions', NULL, 'admin', 'Banks', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (90, 84, 'getOptions', 'api', 'admin.bankuser.getoptions', NULL, 'admin', 'BankUser', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (91, 78, '银行卡号', 'page', NULL, '/admin/bank/cards', 'admin', 'BankCards', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (92, 91, '列表', 'action', 'admin.bankcards.items', NULL, 'admin', 'BankCards', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (94, 91, '编辑', 'action', 'admin.bankcards.edit', NULL, 'admin', 'BankCards', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (95, 91, '删除', 'action', 'admin.bankcards.del', NULL, 'admin', 'BankCards', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (96, 0, '码商银行管理', 'group', NULL, '/channel/bank', 'channel', '', '', 1000, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (97, 96, '银行户名', 'page', NULL, '/channel/bank/user', 'channel', 'BankUser', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (98, 97, '列表', 'action', 'channel.bankuser.items', NULL, 'channel', 'BankUser', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (99, 97, '新增', 'action', 'channel.bankuser.add', NULL, 'channel', 'BankUser', 'add', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (100, 97, '编辑', 'action', 'channel.bankuser.edit', NULL, 'channel', 'BankUser', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (101, 97, '删除', 'action', 'channel.bankuser.del', NULL, 'channel', 'BankUser', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (102, 96, '银行卡号', 'page', NULL, '/channel/bank/cards', 'channel', 'BankCards', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (103, 102, '列表', 'action', 'channel.bankcards.items', NULL, 'channel', 'BankCards', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (104, 102, '新增', 'action', 'channel.bankcards.add', NULL, 'channel', 'BankCards', 'add', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (105, 102, '编辑', 'action', 'channel.bankcards.edit', NULL, 'channel', 'BankCards', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (106, 102, '删除', 'action', 'channel.bankcards.del', NULL, 'channel', 'BankCards', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (107, 97, 'getOptions', 'api', 'channel.bankuser.getoptions', NULL, 'channel', 'BankUser', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (108, 102, 'getOptions', 'api', 'channel.bankcards.getoptions', NULL, 'channel', 'BankCards', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (109, 96, '银行列表', 'group', NULL, NULL, 'channel', 'Banks', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (110, 109, 'getOptions', 'api', 'channel.banks.getoptions', NULL, 'channel', 'Banks', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (111, 0, '码商设备管理', 'group', NULL, '/channel/device', 'channel', '', '', 1999, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (112, 111, '通知推送', 'page', NULL, '/channel/device/notifyQrcode', 'channel', 'Device', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (113, 112, 'getNotifyQrcode', 'action', 'channel.device.getnotifyqrcode', NULL, 'channel', 'Device', 'getNotifyQrcode', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (114, 0, '订单管理', 'group', NULL, '/admin/order', 'admin', '', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (115, 114, '支付方式', 'page', NULL, '/admin/order/payType', 'admin', 'PayType', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (116, 115, '列表', 'action', 'admin.paytype.items', NULL, 'admin', 'PayType', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (117, 115, '新增', 'action', 'admin.paytype.add', NULL, 'admin', 'PayType', 'add', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (118, 115, '编辑', 'action', 'admin.paytype.edit', NULL, 'admin', 'PayType', 'edit', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (119, 115, '删除', 'action', 'admin.paytype.del', NULL, 'admin', 'PayType', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (120, 115, 'getOptions', 'api', 'admin.paytype.getoptions', NULL, 'admin', 'PayType', 'getOptions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (121, 0, 'OpenApi', 'group', NULL, NULL, 'openapi', '', '', 9999, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (122, 121, 'Order', 'group', NULL, NULL, 'openapi', 'Order', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (123, 122, 'create', 'api', 'openapi.Order.create_order', NULL, 'openapi', 'Order', 'create', 0, 1, 1792585801, 1792588297);
INSERT INTO `dt_perms` VALUES (124, 122, 'query', 'api', 'openapi.Order.query', NULL, 'openapi', 'Order', 'query', 0, 1, 1792585801, 1792588289);
INSERT INTO `dt_perms` VALUES (125, 114, '订单列表', 'page', NULL, '/admin/order/index', 'admin', 'Order', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (126, 125, '列表', 'action', 'admin.order.items', NULL, 'admin', 'Order', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (127, 125, '确认订单', 'action', 'admin.order.confirmed', NULL, 'admin', 'Order', 'confirmed', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (128, 125, '回调', 'action', 'admin.order.notify', NULL, 'admin', 'Order', 'notify', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (130, 122, 'banks', 'api', 'openapi.order.banks', NULL, 'openapi', 'Order', 'banks', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (131, 0, '码商订单管理', 'group', NULL, '/channel/order', 'channel', '', '', 1001, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (132, 131, '订单列表', 'page', NULL, '/channel/order/index', 'channel', 'Order', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (133, 132, '列表', 'action', 'channel.order.items', NULL, 'channel', 'Order', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (134, 132, '确认订单', 'action', 'channel.order.confirmed', NULL, 'channel', 'Order', 'confirmed', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (135, 132, '回调', 'action', 'channel.order.notify', NULL, 'channel', 'Order', 'notify', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (136, 0, '商户订单管理', 'group', NULL, '/merchant/order', 'merchant', '', '', 2000, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (137, 136, '订单列表', 'page', NULL, '/merchant/order/index', 'merchant', 'Order', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (138, 137, '列表', 'action', 'merchant.order.items', NULL, 'merchant', 'Order', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (139, 137, '回调', 'action', 'merchant.order.notify', NULL, 'merchant', 'Order', 'notify', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (146, 0, 'Task', 'group', NULL, NULL, 'task', '', '', 9999, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (147, 146, 'Notify', 'group', NULL, NULL, 'task', 'Notify', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (148, 147, 'add', 'api', 'task.notify.add', NULL, 'task', 'Notify', 'add', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (149, 147, 'getPending', 'api', 'task.notify.getpending', NULL, 'task', 'Notify', 'getPending', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (150, 146, 'API', 'group', NULL, NULL, 'task', 'Api', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (151, 150, 'getNotifyTask', 'api', 'task.api.getnotifytask', NULL, 'task', 'Api', 'getNotifyTask', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (152, 150, 'setNotifyStatus', 'api', 'task.api.setnotifystatus', NULL, 'task', 'Api', 'setNotifyStatus', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (153, 147, 'setPending', 'api', 'task.notify.setpending', NULL, 'task', 'Notify', 'setPending', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (154, 174, '推送记录', 'page', NULL, '/admin/device/notifyRecords', 'admin', 'DeviceNotify', '', 0, 1, 1792585801, 1792639666);
INSERT INTO `dt_perms` VALUES (155, 154, '列表', 'action', 'admin.devicenotify.items', NULL, 'admin', 'DeviceNotify', 'items', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (156, 154, '删除', 'action', 'admin.devicenotify.del', NULL, 'admin', 'DeviceNotify', 'del', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (157, 154, '重置', 'action', 'admin.devicenotify.reset', NULL, 'admin', 'DeviceNotify', 'reset', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (158, 146, 'Online', 'group', NULL, NULL, 'task', 'Online', '', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (159, 158, 'getBank', 'api', 'task.online.getbank', NULL, 'task', 'Online', 'getBank', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (160, 158, 'getSmsCode', 'api', 'task.online.getsmscode', NULL, 'task', 'Online', 'getSmsCode', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (161, 158, 'upTransactions', 'api', 'task.online.uptransactions', NULL, 'task', 'Online', 'upTransactions', 0, 1, 1792585801, 1792585801);
INSERT INTO `dt_perms` VALUES (162, 91, '测试VietQR', 'action', 'admin.bankcards.testvietqr', NULL, 'admin', 'BankCards', 'testVietqr', 0, 1, 1792588923, 1792588923);
INSERT INTO `dt_perms` VALUES (163, 102, '测试VietQR', 'action', 'channel.bankcards.testvietqr', NULL, 'channel', 'BankCards', 'testVietqr', 0, 1, 1792588923, 1792588923);
INSERT INTO `dt_perms` VALUES (165, 29, 'getChannelUserList', 'api', 'admin.Member.getChannelUserList', NULL, 'admin', 'Member', 'getChannelUserList', 0, 1, 1792592875, 1792592875);
INSERT INTO `dt_perms` VALUES (166, 29, 'getMerchantUserList', 'api', 'admin.Member.getMerchantUserList', NULL, 'admin', 'Member', 'getMerchantUserList', 0, 1, 1792592900, 1792592900);
INSERT INTO `dt_perms` VALUES (167, 0, '管理端统计', 'group', 'admin.statistics', NULL, 'admin', '', '', 10, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (168, 167, '订单统计', 'api', 'admin.statistics.orders', NULL, 'admin', 'Statistics', 'orders', 0, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (169, 0, '商户统计', 'group', 'merchant.statistics', NULL, 'merchant', '', '', 10, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (170, 169, '订单统计', 'api', 'merchant.statistics.orders', NULL, 'merchant', 'Statistics', 'orders', 0, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (171, 0, '码商统计', 'group', 'channel.statistics', NULL, 'channel', '', '', 10, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (172, 171, '订单统计', 'api', 'channel.statistics.orders', NULL, 'channel', 'Statistics', 'orders', 0, 1, 1792634461, 1792634461);
INSERT INTO `dt_perms` VALUES (173, 167, '账号统计', 'api', 'admin.statistics.accounts', NULL, 'admin', 'Statistics', 'accounts', 0, 1, 1792635431, 1792635431);
INSERT INTO `dt_perms` VALUES (174, 0, '设备推送', 'group', NULL, '/admin/device', '', '', '', 0, 1, 1792639666, 1792639666);
INSERT INTO `dt_perms` VALUES (175, 174, '短信交易', 'page', NULL, '/admin/device/smsTrade', 'admin', 'SmsTrade', '', 0, 1, 1792640931, 1792640931);
INSERT INTO `dt_perms` VALUES (176, 175, '查询', 'action', 'admin.smstrade.items', NULL, 'admin', 'SmsTrade', 'items', 0, 1, 1792640931, 1792640931);
INSERT INTO `dt_perms` VALUES (177, 175, '删除', 'action', 'admin.smstrade.del', NULL, 'admin', 'SmsTrade', 'del', 0, 1, 1792640931, 1792640931);
INSERT INTO `dt_perms` VALUES (178, 147, '接收短信交易', 'api', 'task.notify.addsmstrade', NULL, 'task', 'Notify', 'addSmsTrade', 0, 1, 1792644481, 1792644481);
INSERT INTO `dt_perms` VALUES (179, 174, '短信验证', 'page', NULL, '/admin/device/smsCode', 'admin', 'SmsCode', '', 0, 1, 1792645149, 1792645149);
INSERT INTO `dt_perms` VALUES (180, 179, '查询', 'action', 'admin.smscode.items', NULL, 'admin', 'SmsCode', 'items', 0, 1, 1792645149, 1792645149);
INSERT INTO `dt_perms` VALUES (181, 179, '删除', 'action', 'admin.smscode.del', NULL, 'admin', 'SmsCode', 'del', 0, 1, 1792645149, 1792645149);
INSERT INTO `dt_perms` VALUES (182, 147, '接收短信验证码', 'api', 'task.notify.addsmscode', NULL, 'task', 'Notify', 'addSmsCode', 0, 1, 1792645149, 1792645149);

-- ----------------------------
-- Table structure for dt_role_perms
-- ----------------------------
DROP TABLE IF EXISTS `dt_role_perms`;
CREATE TABLE `dt_role_perms`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色类型(关联dt_roles.type)',
  `perms_id` int(10) NOT NULL DEFAULT 0 COMMENT '权限ID(关联dt_perms.id)',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_type_perms_id`(`type`, `perms_id`) USING BTREE COMMENT '角色类型+权限ID唯一索引',
  INDEX `idx_perms_id`(`perms_id`) USING BTREE COMMENT '权限ID索引'
) ENGINE = InnoDB AUTO_INCREMENT = 1372 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色-权限关联表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_role_perms
-- ----------------------------
INSERT INTO `dt_role_perms` VALUES (777, 'user', 55, 1791098594, 1791098594);
INSERT INTO `dt_role_perms` VALUES (778, 'user', 56, 1791098594, 1791098594);
INSERT INTO `dt_role_perms` VALUES (781, 'super', 5, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (782, 'super', 6, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (783, 'super', 7, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (784, 'super', 8, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (785, 'super', 11, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (786, 'super', 12, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (787, 'super', 13, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (788, 'super', 14, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (789, 'super', 16, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (790, 'super', 17, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (791, 'super', 18, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (792, 'super', 19, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (793, 'super', 20, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (794, 'super', 21, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (795, 'super', 22, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (796, 'super', 24, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (797, 'super', 26, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (798, 'super', 27, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (799, 'super', 28, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (800, 'super', 30, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (801, 'super', 31, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (802, 'super', 32, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (803, 'super', 33, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (804, 'super', 35, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (805, 'super', 36, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (806, 'super', 37, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (807, 'super', 38, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (808, 'super', 40, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (809, 'super', 41, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (810, 'super', 49, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (811, 'super', 51, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (812, 'super', 52, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (813, 'super', 57, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (814, 'super', 58, 1791938525, 1791938525);
INSERT INTO `dt_role_perms` VALUES (815, 'admin', 5, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (816, 'admin', 6, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (817, 'admin', 7, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (818, 'admin', 8, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (819, 'admin', 11, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (820, 'admin', 12, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (821, 'admin', 13, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (822, 'admin', 14, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (823, 'admin', 17, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (824, 'admin', 30, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (825, 'admin', 31, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (826, 'admin', 32, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (827, 'admin', 33, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (828, 'admin', 57, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (829, 'admin', 58, 1791938680, 1791938680);
INSERT INTO `dt_role_perms` VALUES (832, 'admin', 75, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (833, 'super', 75, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (834, 'admin', 76, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (835, 'super', 76, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (836, 'admin', 77, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (837, 'super', 77, 1792464189, 1792464189);
INSERT INTO `dt_role_perms` VALUES (839, 'super', 78, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (840, 'admin', 78, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (841, 'super', 79, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (842, 'admin', 79, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (843, 'super', 80, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (844, 'admin', 80, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (845, 'super', 81, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (846, 'admin', 81, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (847, 'super', 82, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (848, 'admin', 82, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (849, 'super', 83, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (850, 'admin', 83, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (851, 'super', 84, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (852, 'admin', 84, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (853, 'super', 85, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (854, 'admin', 85, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (857, 'super', 87, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (858, 'admin', 87, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (859, 'super', 88, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (860, 'admin', 88, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (861, 'super', 89, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (862, 'admin', 89, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (863, 'super', 90, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (864, 'admin', 90, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (865, 'super', 91, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (866, 'admin', 91, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (867, 'super', 92, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (868, 'admin', 92, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (871, 'super', 94, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (872, 'admin', 94, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (873, 'super', 95, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (874, 'admin', 95, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (893, 'super', 114, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (894, 'admin', 114, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (895, 'super', 115, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (896, 'admin', 115, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (897, 'super', 116, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (898, 'admin', 116, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (899, 'super', 117, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (900, 'admin', 117, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (901, 'super', 118, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (902, 'admin', 118, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (903, 'super', 119, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (904, 'admin', 119, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (905, 'super', 120, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (906, 'admin', 120, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (911, 'super', 125, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (912, 'admin', 125, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (913, 'super', 126, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (914, 'admin', 126, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (915, 'super', 127, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (916, 'admin', 127, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (917, 'super', 128, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (918, 'admin', 128, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (946, 'super', 154, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (947, 'admin', 154, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (948, 'super', 155, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (949, 'admin', 155, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (950, 'super', 156, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (951, 'admin', 156, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (952, 'super', 157, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (953, 'admin', 157, 1792585801, 1792585801);
INSERT INTO `dt_role_perms` VALUES (1199, 'task', 146, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1200, 'task', 147, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1201, 'task', 148, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1202, 'task', 149, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1203, 'task', 153, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1204, 'task', 150, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1205, 'task', 151, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1206, 'task', 152, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1207, 'task', 158, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1208, 'task', 159, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1209, 'task', 160, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1210, 'task', 161, 1792586159, 1792586159);
INSERT INTO `dt_role_perms` VALUES (1284, 'super', 162, 1792588923, 1792588923);
INSERT INTO `dt_role_perms` VALUES (1285, 'admin', 162, 1792588923, 1792588923);
INSERT INTO `dt_role_perms` VALUES (1303, 'channel', 53, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1304, 'channel', 54, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1305, 'channel', 55, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1306, 'channel', 56, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1307, 'channel', 120, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1308, 'channel', 96, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1309, 'channel', 97, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1310, 'channel', 98, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1311, 'channel', 99, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1312, 'channel', 100, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1313, 'channel', 101, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1314, 'channel', 107, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1315, 'channel', 102, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1316, 'channel', 103, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1317, 'channel', 104, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1318, 'channel', 105, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1319, 'channel', 106, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1320, 'channel', 108, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1321, 'channel', 163, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1322, 'channel', 109, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1323, 'channel', 110, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1324, 'channel', 131, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1325, 'channel', 132, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1326, 'channel', 133, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1327, 'channel', 134, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1328, 'channel', 135, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1329, 'channel', 111, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1330, 'channel', 112, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1331, 'channel', 113, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1332, 'channel', 148, 1792591269, 1792591269);
INSERT INTO `dt_role_perms` VALUES (1333, 'merchant', 53, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1334, 'merchant', 54, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1335, 'merchant', 55, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1336, 'merchant', 56, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1337, 'merchant', 120, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1338, 'merchant', 136, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1339, 'merchant', 137, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1340, 'merchant', 138, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1341, 'merchant', 139, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1342, 'merchant', 121, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1343, 'merchant', 122, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1344, 'merchant', 123, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1345, 'merchant', 124, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1346, 'merchant', 130, 1792591367, 1792591367);
INSERT INTO `dt_role_perms` VALUES (1347, 'admin', 168, 1792634461, 1792634461);
INSERT INTO `dt_role_perms` VALUES (1348, 'super', 168, 1792634461, 1792634461);
INSERT INTO `dt_role_perms` VALUES (1349, 'merchant', 170, 1792634461, 1792634461);
INSERT INTO `dt_role_perms` VALUES (1350, 'channel', 172, 1792634461, 1792634461);
INSERT INTO `dt_role_perms` VALUES (1351, 'admin', 173, 1792635431, 1792635431);
INSERT INTO `dt_role_perms` VALUES (1352, 'super', 173, 1792635431, 1792635431);
INSERT INTO `dt_role_perms` VALUES (1353, 'super', 175, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1354, 'super', 177, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1355, 'admin', 175, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1356, 'admin', 177, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1357, 'super', 176, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1358, 'admin', 176, 1792640931, 1792640931);
INSERT INTO `dt_role_perms` VALUES (1360, 'task', 178, 1792644481, 1792644481);
INSERT INTO `dt_role_perms` VALUES (1361, 'super', 180, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1362, 'admin', 180, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1364, 'super', 179, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1365, 'admin', 179, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1367, 'super', 181, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1368, 'admin', 181, 1792645149, 1792645149);
INSERT INTO `dt_role_perms` VALUES (1371, 'task', 182, 1792645149, 1792645149);

-- ----------------------------
-- Table structure for dt_roles
-- ----------------------------
DROP TABLE IF EXISTS `dt_roles`;
CREATE TABLE `dt_roles`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色类型唯一标识(对应用户表角色字段)',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT '角色名称',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态：0禁用 1启用',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '创建时间',
  `update_time` int(10) NOT NULL DEFAULT 0 COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `uk_type`(`type`) USING BTREE COMMENT '角色类型唯一索引'
) ENGINE = InnoDB AUTO_INCREMENT = 15 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_roles
-- ----------------------------
INSERT INTO `dt_roles` VALUES (1, 'super', '超管', 1, '', 1783564275, 1783579631);
INSERT INTO `dt_roles` VALUES (2, 'admin', '管理员', 1, '', 1783564283, 1783576767);
INSERT INTO `dt_roles` VALUES (3, 'user', '用户', 1, '', 1783578101, 1792075410);
INSERT INTO `dt_roles` VALUES (12, 'channel', '码商', 1, '', 1792585801, 1792586768);
INSERT INTO `dt_roles` VALUES (13, 'merchant', '商户', 1, '', 1792585801, 1792585801);
INSERT INTO `dt_roles` VALUES (14, 'task', '任务服务', 1, '', 1792585801, 1792585801);

-- ----------------------------
-- Table structure for dt_sms_code
-- ----------------------------
DROP TABLE IF EXISTS `dt_sms_code`;
CREATE TABLE `dt_sms_code`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notify_id` int(10) NULL DEFAULT 0 COMMENT '设备推送ID',
  `phone_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '手机号码',
  `verify_code` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '验证码',
  `time` int(10) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `notify_id`(`notify_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dt_sms_trade
-- ----------------------------
DROP TABLE IF EXISTS `dt_sms_trade`;
CREATE TABLE `dt_sms_trade`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `notify_id` int(10) NULL DEFAULT 0 COMMENT '设备推送ID',
  `bank_number` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '银行卡号',
  `balance` int(10) NULL DEFAULT 0 COMMENT '余额',
  `change` int(10) NULL DEFAULT 0 COMMENT '变动',
  `remark` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '附言',
  `order_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT '' COMMENT '关联订单',
  `status` tinyint(1) NULL DEFAULT 1 COMMENT '状态',
  `time` int(10) NOT NULL DEFAULT 0 COMMENT '推送时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `notify_id`(`notify_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dt_upload
-- ----------------------------
DROP TABLE IF EXISTS `dt_upload`;
CREATE TABLE `dt_upload`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `size` int(11) NOT NULL DEFAULT 0,
  `mime` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'image/video/file',
  `path` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `time` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_type_time`(`type`, `time`) USING BTREE COMMENT '按类型+时间分页查附件'
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for dt_user
-- ----------------------------
DROP TABLE IF EXISTS `dt_user`;
CREATE TABLE `dt_user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '账号类型 super/admin/user(关联 dt_roles.type)',
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '账号',
  `nickname` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '昵称',
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT '头像',
  `balance` bigint(20) NOT NULL DEFAULT 0 COMMENT '整数余额，最多13位',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1正常 -1停用',
  `create_time` int(10) NOT NULL DEFAULT 0,
  `update_time` int(10) NOT NULL DEFAULT 0,
  `rate` decimal(7, 4) NOT NULL DEFAULT 0.0000 COMMENT '百分比费率，四位小数',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE COMMENT '注册时间区间筛选'
) ENGINE = InnoDB AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '账号表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_user
-- ----------------------------
INSERT INTO `dt_user` VALUES (1, 'super', 'admin', '', '$2y$10$x0lw5xHph1Y/7t735FE1Se4O.0ZTR5ayKkJDmzryf8bL243yLCqqm', '', 0, 1, 1652500366, 1791905409, 0.0000);
INSERT INTO `dt_user` VALUES (2, 'admin', 'super', '', '$2y$10$qaVPRbXHo3/OXxLKgXviiuHwEJny28/nlvJpEl3Iegpt07YNzM3Tm', '', 0, 1, 1652506679, 1791905395, 0.0000);
INSERT INTO `dt_user` VALUES (3, 'merchant', '测试商户', '', '$2y$10$.ypfEnribqVwP41oRiddiO2qIHaAAjOQDjHxg5RROWika0/TuJT6a', '', 180, 1, 1791908658, 1792630116, 10.0000);
INSERT INTO `dt_user` VALUES (31, 'channel', '测试码商', '', '$2y$10$UH9Anz0rttdxpPM50V5UnezixQyhKluBmUuK7ij8lR/W3u3zLq23.', '', 9602, 1, 1792586732, 1792630105, 1.0000);

SET FOREIGN_KEY_CHECKS = 1;
