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

 Date: 27/06/2022 16:13:47
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for tasks
-- ----------------------------
DROP TABLE IF EXISTS `tasks`;
CREATE TABLE `tasks`  (
  `taskId` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `description` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `assignedTo` int NULL DEFAULT 0,
  `taskStatusId` int NULL DEFAULT 0,
  `isDiscussionRequired` tinyint NULL DEFAULT 0,
  `discussionRequestedOn` datetime NULL DEFAULT NULL,
  `imageName` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT '',
  `priorityId` int NULL DEFAULT 0,
  `isApproved` tinyint NULL DEFAULT 0,
  `approvedOn` datetime NULL DEFAULT NULL,
  `createdOn` datetime NULL DEFAULT NULL,
  `startedOn` datetime NULL DEFAULT NULL,
  `finishedOn` datetime NULL DEFAULT NULL,
  PRIMARY KEY (`taskId`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

SET FOREIGN_KEY_CHECKS = 1;
