/*
 Navicat Premium Data Transfer

 Source Server         : 127.0.0.1
 Source Server Type    : MySQL
 Source Server Version : 50726
 Source Host           : 127.0.0.1:3306
 Source Schema         : dbdata

 Target Server Type    : MySQL
 Target Server Version : 50726
 File Encoding         : 65001

 Date: 20/10/2026 10:46:38
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
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '应用接口' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_app_list
-- ----------------------------
INSERT INTO `dt_app_list` VALUES (1, 1, 'admin', '1d3gweoqpnqyjponpzovna', 1, '', 1782270416, 1792075267);

-- ----------------------------
-- Table structure for dt_balance_log
-- ----------------------------
DROP TABLE IF EXISTS `dt_balance_log`;
CREATE TABLE `dt_balance_log`  (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `user_id` int(11) NOT NULL DEFAULT 0 COMMENT '用户ID',
  `change_amount` bigint(20) NOT NULL DEFAULT 0 COMMENT '变动金额(分，可为负)',
  `balance_after` bigint(20) NOT NULL DEFAULT 0 COMMENT '变动后余额(分)',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '交易类型',
  `remark` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '备注',
  `operator` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT '操作员',
  `create_time` int(10) NOT NULL DEFAULT 0 COMMENT '时间',
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE COMMENT '时间索引',
  INDEX `idx_user_time`(`user_id`, `create_time`) USING BTREE COMMENT '用户+时间组合索引(按用户查流水并排序)'
) ENGINE = InnoDB AUTO_INCREMENT = 11 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '余额变动记录表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_balance_log
-- ----------------------------
INSERT INTO `dt_balance_log` VALUES (1, 1, 10000, 10000, 'adjust', 'test', 'admin', 1791908719);
INSERT INTO `dt_balance_log` VALUES (4, 3, 10000, 10000, 'recharge', '', 'admin', 1791909561);
INSERT INTO `dt_balance_log` VALUES (5, 3, -2000, 8000, 'adjust', '', 'admin', 1791981103);

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
) ENGINE = InnoDB AUTO_INCREMENT = 7 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_config
-- ----------------------------
INSERT INTO `dt_config` VALUES (1, 1, 'site_name', 'text', '站点名称', '管理面板', '', '', 10, 1, 0, 1789525468);
INSERT INTO `dt_config` VALUES (2, 1, 'site_status', 'select', '是否闭站', '1', '站点关闭后，用户无法登录', '[{\"id\":\"1\",\"name\":\"否\"},{\"id\":\"2\",\"name\":\"是\"}]', 10, 1, 0, 1789525471);
INSERT INTO `dt_config` VALUES (3, 1, 'logo', 'image', 'LOGO', '/static/logo.png', '', '', 50, 1, 0, 1791939916);
INSERT INTO `dt_config` VALUES (4, 1, 'site_stop_msg', 'text', '闭站提示', '系统维护中...', '', '', 10, 1, 0, 1789525475);
INSERT INTO `dt_config` VALUES (5, 1, 'demo_textarea', 'textarea', '多行文本', '', '', '', 1, -1, 0, 1789525480);
INSERT INTO `dt_config` VALUES (6, 1, 'captcha_status', 'select', '登录验证码', '2', '', '[{\"id\":\"1\",\"name\":\"启用\"},{\"id\":\"2\",\"name\":\"关闭\"}]', 50, 1, 0, 1791098578);

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
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '配置分组' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_config_group
-- ----------------------------
INSERT INTO `dt_config_group` VALUES (1, '系统', 9999, 1, 1789522790, 1791098566);
INSERT INTO `dt_config_group` VALUES (2, '用户', 0, -1, 1789525592, 1792115153);

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
) ENGINE = InnoDB AUTO_INCREMENT = 157 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '登录记录' ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 78 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '权限节点表' ROW_FORMAT = Dynamic;

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
INSERT INTO `dt_perms` VALUES (71, 0, '开放接口', 'group', NULL, NULL, '', '', '', 0, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (72, 71, '接口连通性检查', 'api', 'openapi.index.ping', NULL, 'openapi', 'Index', 'ping', 10, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (73, 71, '获取任务', 'api', 'task.api.get_task', NULL, 'task', 'Api', 'getTask', 20, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (74, 71, '提交任务报告', 'api', 'task.api.set_report', NULL, 'task', 'Api', 'setReport', 30, 1, 0, 0);
INSERT INTO `dt_perms` VALUES (75, 1, '文件管理', 'page', NULL, '/admin/system/upload', 'admin', 'Upload', '', 10, 1, 1792464189, 1792464265);
INSERT INTO `dt_perms` VALUES (76, 75, '列表', 'action', 'admin.upload.items', NULL, 'admin', 'Upload', 'items', 0, 1, 1792464189, 1792464189);
INSERT INTO `dt_perms` VALUES (77, 75, '删除', 'action', 'admin.upload.del', NULL, 'admin', 'Upload', 'del', 0, 1, 1792464189, 1792464189);

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
) ENGINE = InnoDB AUTO_INCREMENT = 839 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色-权限关联表' ROW_FORMAT = Dynamic;

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
) ENGINE = InnoDB AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '角色表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_roles
-- ----------------------------
INSERT INTO `dt_roles` VALUES (1, 'super', '超管', 1, '', 1783564275, 1783579631);
INSERT INTO `dt_roles` VALUES (2, 'admin', '管理员', 1, '', 1783564283, 1783576767);
INSERT INTO `dt_roles` VALUES (3, 'user', '用户', 1, '', 1783578101, 1792075410);

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
) ENGINE = InnoDB AUTO_INCREMENT = 45 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

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
  `balance` bigint(20) NOT NULL DEFAULT 0 COMMENT '余额(分)',
  `status` tinyint(1) NOT NULL DEFAULT 1 COMMENT '状态 1正常 -1停用',
  `create_time` int(10) NOT NULL DEFAULT 0,
  `update_time` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `username`(`username`) USING BTREE,
  INDEX `idx_create_time`(`create_time`) USING BTREE COMMENT '注册时间区间筛选'
) ENGINE = InnoDB AUTO_INCREMENT = 17 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci COMMENT = '账号表' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of dt_user
-- ----------------------------
INSERT INTO `dt_user` VALUES (1, 'super', 'admin', '', '$2y$10$x0lw5xHph1Y/7t735FE1Se4O.0ZTR5ayKkJDmzryf8bL243yLCqqm', '', 10000, 1, 1652500366, 1791905409);
INSERT INTO `dt_user` VALUES (2, 'admin', 'super', '', '$2y$10$qaVPRbXHo3/OXxLKgXviiuHwEJny28/nlvJpEl3Iegpt07YNzM3Tm', '', 0, 1, 1652506679, 1791905395);
INSERT INTO `dt_user` VALUES (3, 'user', '张三', '', '$2y$10$.ypfEnribqVwP41oRiddiO2qIHaAAjOQDjHxg5RROWika0/TuJT6a', '', 8000, 1, 1791908658, 1792075267);

SET FOREIGN_KEY_CHECKS = 1;
