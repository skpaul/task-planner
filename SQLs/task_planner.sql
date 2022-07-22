/*
 Navicat Premium Data Transfer

 Source Server         : XAMPP
 Source Server Type    : MySQL
 Source Server Version : 100417
 Source Host           : localhost:3306
 Source Schema         : task_planner

 Target Server Type    : MySQL
 Target Server Version : 100417
 File Encoding         : 65001

 Date: 23/07/2022 01:59:11
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for developers
-- ----------------------------
DROP TABLE IF EXISTS `developers`;
CREATE TABLE `developers`  (
  `developerId` int NOT NULL AUTO_INCREMENT,
  `fullName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `loginName` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `loginPassword` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `role` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  PRIMARY KEY (`developerId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of developers
-- ----------------------------
INSERT INTO `developers` VALUES (1, 'Saumitra', 'skpaul', '1', 'admin');
INSERT INTO `developers` VALUES (2, 'Sarmin', 'sarmin', 'Lima2304', '');
INSERT INTO `developers` VALUES (3, 'Asib', 'asib', 'winbip@2022', '');

-- ----------------------------
-- Table structure for priorities
-- ----------------------------
DROP TABLE IF EXISTS `priorities`;
CREATE TABLE `priorities`  (
  `priorityId` int NOT NULL,
  `name` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  PRIMARY KEY (`priorityId`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of priorities
-- ----------------------------
INSERT INTO `priorities` VALUES (0, '');
INSERT INTO `priorities` VALUES (1, '*');
INSERT INTO `priorities` VALUES (2, '**');
INSERT INTO `priorities` VALUES (3, '***');
INSERT INTO `priorities` VALUES (4, '****');
INSERT INTO `priorities` VALUES (5, '*****');

-- ----------------------------
-- Table structure for sessions
-- ----------------------------
DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `owner` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NULL DEFAULT '' COMMENT 'owner of this session. currently owner is EIIN number.',
  `data` longtext CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `datetime` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 41 CHARACTER SET = latin1 COLLATE = latin1_swedish_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of sessions
-- ----------------------------
INSERT INTO `sessions` VALUES (37, '1', '{\"devId\":1}', '2022-07-22 16:06:47');
INSERT INTO `sessions` VALUES (39, '2', '{\"devId\":2}', '2022-07-22 16:14:01');
INSERT INTO `sessions` VALUES (40, '1', '{\"devId\":1}', '2022-07-23 01:53:14');

-- ----------------------------
-- Table structure for task_statuses
-- ----------------------------
DROP TABLE IF EXISTS `task_statuses`;
CREATE TABLE `task_statuses`  (
  `statusId` int NOT NULL AUTO_INCREMENT,
  `statusName` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  PRIMARY KEY (`statusId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 4 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of task_statuses
-- ----------------------------
INSERT INTO `task_statuses` VALUES (1, 'Pending');
INSERT INTO `task_statuses` VALUES (2, 'Working');
INSERT INTO `task_statuses` VALUES (3, 'Finished');

-- ----------------------------
-- Table structure for tasks
-- ----------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks`  (
  `taskId` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `assignedTo` int NULL DEFAULT 0,
  `taskStatusId` int NULL DEFAULT 0,
  `isDiscussionRequired` tinyint NULL DEFAULT 0,
  `discussionRequestedOn` datetime NULL DEFAULT NULL,
  `attachments` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'multiple photos separated by comma',
  `attachmentsType` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '' COMMENT 'Link or jpg/png/pdf/etc',
  `priorityId` int NULL DEFAULT 0,
  `isApproved` tinyint NULL DEFAULT 0,
  `approvedOn` datetime NULL DEFAULT NULL,
  `createdOn` datetime NULL DEFAULT NULL,
  `startedOn` datetime NULL DEFAULT NULL,
  `finishedOn` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`taskId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 18 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tasks
-- ----------------------------
INSERT INTO `tasks` VALUES (15, 'multiline 15', 'lkasdj\r\nasjdlfsj\r\nasdflk\r\n', 1, 1, 1, '2022-07-23 01:35:28', 'asdsdf', 'link', 4, 0, NULL, '2022-07-22 19:49:19', NULL, NULL);
INSERT INTO `tasks` VALUES (16, 'multiline 16', '1. lkasdj\r\n2. asjdlfsj\r\n3. asdflk\r\n', 1, 1, 0, NULL, '', '', 4, 0, '2022-07-23 00:21:32', '2022-07-21 21:41:07', NULL, NULL);
INSERT INTO `tasks` VALUES (17, 'Attachment test', 'lsakdj alsdflsdlsdkfjl.\r\nalsdjlsdfj lsdf lsdlsfj. alsdjdfsda lasdfjsdl lasdjf lasdkf asldkfjsadl sldafsadlf asdf jlasdflsadf aslfjsdaf asdf.', 1, 1, 0, NULL, '', '', 0, 0, NULL, '2022-07-23 00:40:28', NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
