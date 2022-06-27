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

 Date: 27/06/2022 13:28:36
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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

SET FOREIGN_KEY_CHECKS = 1;
