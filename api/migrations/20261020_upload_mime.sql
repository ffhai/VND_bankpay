-- Office 文件的 MIME 超过 50 字符；自定义表前缀时请替换 dt_upload。
ALTER TABLE `dt_upload` MODIFY COLUMN `mime` varchar(127) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '';
