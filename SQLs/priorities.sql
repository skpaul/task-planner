/*
 Navicat Premium Data Transfer

 Source Server         : Bar Council
 Source Server Type    : MySQL
 Source Server Version : 100505
 Source Host           : 192.168.61.178:3306
 Source Schema         : xdev_task_planner

 Target Server Type    : MySQL
 Target Server Version : 100505
 File Encoding         : 65001

 Date: 27/06/2022 16:13:14
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

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
INSERT INTO `priorities` VALUES (1, '*****');
INSERT INTO `priorities` VALUES (2, '****');
INSERT INTO `priorities` VALUES (3, '***');
INSERT INTO `priorities` VALUES (4, '**');
INSERT INTO `priorities` VALUES (5, '*');

SET FOREIGN_KEY_CHECKS = 1;
