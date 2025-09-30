/*
 Navicat Premium Data Transfer

 Source Server         : helpdeskfix
 Source Server Type    : MySQL
 Source Server Version : 50541 (5.5.41)
 Source Host           : localhost:3306
 Source Schema         : helpdeskjateng

 Target Server Type    : MySQL
 Target Server Version : 50541 (5.5.41)
 File Encoding         : 65001

 Date: 30/09/2025 13:09:50
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for failed_jobs
-- ----------------------------
DROP TABLE IF EXISTS `failed_jobs`;
CREATE TABLE `failed_jobs`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `uuid` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `failed_jobs_uuid_unique`(`uuid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of failed_jobs
-- ----------------------------

-- ----------------------------
-- Table structure for migrations
-- ----------------------------
DROP TABLE IF EXISTS `migrations`;
CREATE TABLE `migrations`  (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 12 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of migrations
-- ----------------------------
INSERT INTO `migrations` VALUES (1, '2014_10_12_000000_create_users_table', 1);
INSERT INTO `migrations` VALUES (2, '2014_10_12_100000_create_password_resets_table', 1);
INSERT INTO `migrations` VALUES (3, '2019_08_19_000000_create_failed_jobs_table', 1);
INSERT INTO `migrations` VALUES (4, '2019_12_14_000001_create_personal_access_tokens_table', 1);
INSERT INTO `migrations` VALUES (5, '2025_09_27_122017_add_role_to_users_table', 1);
INSERT INTO `migrations` VALUES (6, '2025_09_27_122912_create_tickets_table', 2);
INSERT INTO `migrations` VALUES (7, '2025_09_27_121720_add_role_to_users_table', 3);
INSERT INTO `migrations` VALUES (8, '2025_09_27_140919_create_ticket_comments_table', 3);
INSERT INTO `migrations` VALUES (9, '2025_09_27_142244_add_it_id_to_tickets_table', 4);
INSERT INTO `migrations` VALUES (10, '2025_09_27_155031_add_username_to_users_table', 5);
INSERT INTO `migrations` VALUES (11, '2025_09_30_013744_add_eskalasi_and_timestamps_to_tickets_table', 5);

-- ----------------------------
-- Table structure for password_resets
-- ----------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets`  (
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`) USING BTREE
) ENGINE = MyISAM CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of password_resets
-- ----------------------------

-- ----------------------------
-- Table structure for personal_access_tokens
-- ----------------------------
DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE `personal_access_tokens`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `personal_access_tokens_token_unique`(`token`) USING BTREE,
  INDEX `personal_access_tokens_tokenable_type_tokenable_id_index`(`tokenable_type`, `tokenable_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of personal_access_tokens
-- ----------------------------

-- ----------------------------
-- Table structure for ticket_comments
-- ----------------------------
DROP TABLE IF EXISTS `ticket_comments`;
CREATE TABLE `ticket_comments`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `body` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `ticket_comments_ticket_id_foreign`(`ticket_id`) USING BTREE,
  INDEX `ticket_comments_user_id_foreign`(`user_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 3 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of ticket_comments
-- ----------------------------
INSERT INTO `ticket_comments` VALUES (1, 1, 31, 'jika membutuhkan followup bisa ke no xxxxxxx', '2025-09-27 16:32:09', '2025-09-27 16:32:09');
INSERT INTO `ticket_comments` VALUES (2, 108, 15, 'asdas', '2025-09-29 09:53:42', '2025-09-29 09:53:42');

-- ----------------------------
-- Table structure for tickets
-- ----------------------------
DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nomor_tiket` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `kategori` enum('JARINGAN','LAYANAN','CBS','OTHER') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `deskripsi` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lampiran` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `status` enum('OPEN','ON_PROGRESS','CLOSED') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'OPEN',
  `taken_at` timestamp NULL DEFAULT NULL,
  `closed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `it_id` bigint(20) UNSIGNED NULL DEFAULT NULL,
  `eskalasi` enum('VENDOR','TIDAK') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `tickets_nomor_tiket_unique`(`nomor_tiket`) USING BTREE,
  INDEX `tickets_user_id_foreign`(`user_id`) USING BTREE,
  INDEX `tickets_it_id_foreign`(`it_id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 110 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tickets
-- ----------------------------
INSERT INTO `tickets` VALUES (1, 'TCK-202509-0001', 31, 'JARINGAN', 'jaringan kantor cabang putus', 'lampiran/k7JdHMy1pTUi3sWQl6J47KdL9Ub6lQFWRov8dLCu.png', 'OPEN', NULL, NULL, '2025-09-27 16:25:03', '2025-09-27 16:25:03', NULL, NULL);
INSERT INTO `tickets` VALUES (2, 'TCK-202509-0002', 20, 'JARINGAN', 'CBS Putus', 'lampiran/gOg9QiwbVNPYU5yslzIiEk7R2gtWnZOaDyxqTKNQ.png', 'OPEN', NULL, NULL, '2025-09-27 17:49:30', '2025-09-27 17:49:30', NULL, NULL);
INSERT INTO `tickets` VALUES (3, 'TCK-202509-0003', 17, 'CBS', 'Aperiam natus voluptatem rerum laborum dolores asperiores aperiam exercitationem distinctio illum quisquam odio.', NULL, 'CLOSED', NULL, NULL, '2025-08-30 22:58:01', '2025-09-27 17:53:41', 3, NULL);
INSERT INTO `tickets` VALUES (4, 'TCK-202509-0004', 14, 'OTHER', 'Est corporis mollitia repudiandae ut delectus deleniti maiores.', NULL, 'ON_PROGRESS', NULL, NULL, '2025-06-30 01:32:37', '2025-09-27 17:53:47', 3, NULL);
INSERT INTO `tickets` VALUES (5, 'TCK-202509-0005', 12, 'OTHER', 'Dummy ticket 0', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (6, 'TCK-202509-0006', 23, 'CBS', 'Dummy ticket 1', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (7, 'TCK-202509-0007', 12, 'OTHER', 'Dummy ticket 2', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (8, 'TCK-202509-0008', 11, 'CBS', 'Dummy ticket 3', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (9, 'TCK-202509-0009', 29, 'JARINGAN', 'Dummy ticket 4', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (10, 'TCK-202509-0010', 27, 'OTHER', 'Dummy ticket 5', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (11, 'TCK-202509-0011', 16, 'JARINGAN', 'Dummy ticket 6', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (12, 'TCK-202509-0012', 25, 'JARINGAN', 'Dummy ticket 7', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (13, 'TCK-202509-0013', 23, 'LAYANAN', 'Dummy ticket 8', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (14, 'TCK-202509-0014', 9, 'JARINGAN', 'Dummy ticket 9', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (15, 'TCK-202509-0015', 23, 'OTHER', 'Dummy ticket 10', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (16, 'TCK-202509-0016', 13, 'CBS', 'Dummy ticket 11', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (17, 'TCK-202509-0017', 26, 'JARINGAN', 'Dummy ticket 12', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (18, 'TCK-202509-0018', 27, 'CBS', 'Dummy ticket 13', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (19, 'TCK-202509-0019', 18, 'JARINGAN', 'Dummy ticket 14', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (20, 'TCK-202509-0020', 11, 'OTHER', 'Dummy ticket 15', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (21, 'TCK-202509-0021', 7, 'CBS', 'Dummy ticket 16', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (22, 'TCK-202509-0022', 21, 'CBS', 'Dummy ticket 17', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (23, 'TCK-202509-0023', 7, 'JARINGAN', 'Dummy ticket 18', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (24, 'TCK-202509-0024', 29, 'OTHER', 'Dummy ticket 19', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (25, 'TCK-202509-0025', 23, 'LAYANAN', 'Dummy ticket 20', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (26, 'TCK-202509-0026', 29, 'JARINGAN', 'Dummy ticket 21', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (27, 'TCK-202509-0027', 7, 'CBS', 'Dummy ticket 22', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (28, 'TCK-202509-0028', 22, 'LAYANAN', 'Dummy ticket 23', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (29, 'TCK-202509-0029', 19, 'JARINGAN', 'Dummy ticket 24', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (30, 'TCK-202509-0030', 8, 'CBS', 'Dummy ticket 25', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (31, 'TCK-202509-0031', 6, 'LAYANAN', 'Dummy ticket 26', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (32, 'TCK-202509-0032', 5, 'OTHER', 'Dummy ticket 27', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (33, 'TCK-202509-0033', 12, 'JARINGAN', 'Dummy ticket 28', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (34, 'TCK-202509-0034', 11, 'JARINGAN', 'Dummy ticket 29', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (35, 'TCK-202509-0035', 17, 'CBS', 'Dummy ticket 30', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (36, 'TCK-202509-0036', 4, 'CBS', 'Dummy ticket 31', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (37, 'TCK-202509-0037', 7, 'JARINGAN', 'Dummy ticket 32', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (38, 'TCK-202509-0038', 22, 'CBS', 'Dummy ticket 33', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (39, 'TCK-202509-0039', 24, 'JARINGAN', 'Dummy ticket 34', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (40, 'TCK-202509-0040', 9, 'JARINGAN', 'Dummy ticket 35', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (41, 'TCK-202509-0041', 18, 'OTHER', 'Dummy ticket 36', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (42, 'TCK-202509-0042', 27, 'JARINGAN', 'Dummy ticket 37', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (43, 'TCK-202509-0043', 15, 'LAYANAN', 'Dummy ticket 38', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (44, 'TCK-202509-0044', 21, 'CBS', 'Dummy ticket 39', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (45, 'TCK-202509-0045', 9, 'LAYANAN', 'Dummy ticket 40', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (46, 'TCK-202509-0046', 9, 'LAYANAN', 'Dummy ticket 41', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (47, 'TCK-202509-0047', 16, 'CBS', 'Dummy ticket 42', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (48, 'TCK-202509-0048', 25, 'OTHER', 'Dummy ticket 43', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (49, 'TCK-202509-0049', 19, 'LAYANAN', 'Dummy ticket 44', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (50, 'TCK-202509-0050', 21, 'CBS', 'Dummy ticket 45', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (51, 'TCK-202509-0051', 17, 'JARINGAN', 'Dummy ticket 46', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (52, 'TCK-202509-0052', 30, 'CBS', 'Dummy ticket 47', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (53, 'TCK-202509-0053', 22, 'CBS', 'Dummy ticket 48', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 2, NULL);
INSERT INTO `tickets` VALUES (54, 'TCK-202509-0054', 9, 'CBS', 'Dummy ticket 49', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (55, 'TCK-202509-0055', 15, 'LAYANAN', 'Dummy ticket 50', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (56, 'TCK-202509-0056', 26, 'LAYANAN', 'Dummy ticket 51', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (57, 'TCK-202509-0057', 4, 'JARINGAN', 'Dummy ticket 52', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (58, 'TCK-202509-0058', 24, 'JARINGAN', 'Dummy ticket 53', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (59, 'TCK-202509-0059', 10, 'CBS', 'Dummy ticket 54', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', NULL, NULL);
INSERT INTO `tickets` VALUES (60, 'TCK-202509-0060', 19, 'LAYANAN', 'Dummy ticket 55', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (61, 'TCK-202509-0061', 18, 'CBS', 'Dummy ticket 56', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (62, 'TCK-202509-0062', 14, 'CBS', 'Dummy ticket 57', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 3, NULL);
INSERT INTO `tickets` VALUES (63, 'TCK-202509-0063', 4, 'OTHER', 'Dummy ticket 58', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:53', '2025-09-27 17:54:53', 1, NULL);
INSERT INTO `tickets` VALUES (64, 'TCK-202509-0064', 24, 'OTHER', 'Dummy ticket 59', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (65, 'TCK-202509-0065', 20, 'OTHER', 'Dummy ticket 60', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (66, 'TCK-202509-0066', 24, 'OTHER', 'Dummy ticket 61', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (67, 'TCK-202509-0067', 31, 'CBS', 'Dummy ticket 62', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (68, 'TCK-202509-0068', 7, 'CBS', 'Dummy ticket 63', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (69, 'TCK-202509-0069', 16, 'LAYANAN', 'Dummy ticket 64', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (70, 'TCK-202509-0070', 28, 'LAYANAN', 'Dummy ticket 65', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (71, 'TCK-202509-0071', 26, 'OTHER', 'Dummy ticket 66', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (72, 'TCK-202509-0072', 12, 'LAYANAN', 'Dummy ticket 67', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (73, 'TCK-202509-0073', 22, 'OTHER', 'Dummy ticket 68', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (74, 'TCK-202509-0074', 11, 'LAYANAN', 'Dummy ticket 69', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-30 02:07:26', 1, 'VENDOR');
INSERT INTO `tickets` VALUES (75, 'TCK-202509-0075', 4, 'LAYANAN', 'Dummy ticket 70', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 1, NULL);
INSERT INTO `tickets` VALUES (76, 'TCK-202509-0076', 9, 'JARINGAN', 'Dummy ticket 71', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (77, 'TCK-202509-0077', 17, 'CBS', 'Dummy ticket 72', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (78, 'TCK-202509-0078', 15, 'LAYANAN', 'Dummy ticket 73', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (79, 'TCK-202509-0079', 11, 'LAYANAN', 'Dummy ticket 74', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (80, 'TCK-202509-0080', 10, 'CBS', 'Dummy ticket 75', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (81, 'TCK-202509-0081', 26, 'JARINGAN', 'Dummy ticket 76', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (82, 'TCK-202509-0082', 23, 'LAYANAN', 'Dummy ticket 77', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (83, 'TCK-202509-0083', 9, 'LAYANAN', 'Dummy ticket 78', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (84, 'TCK-202509-0084', 23, 'OTHER', 'Dummy ticket 79', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (85, 'TCK-202509-0085', 15, 'JARINGAN', 'Dummy ticket 80', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (86, 'TCK-202509-0086', 12, 'CBS', 'Dummy ticket 81', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (87, 'TCK-202509-0087', 20, 'LAYANAN', 'Dummy ticket 82', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 1, NULL);
INSERT INTO `tickets` VALUES (88, 'TCK-202509-0088', 30, 'LAYANAN', 'Dummy ticket 83', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (89, 'TCK-202509-0089', 19, 'CBS', 'Dummy ticket 84', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (90, 'TCK-202509-0090', 20, 'OTHER', 'Dummy ticket 85', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (91, 'TCK-202509-0091', 4, 'OTHER', 'Dummy ticket 86', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (92, 'TCK-202509-0092', 24, 'CBS', 'Dummy ticket 87', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (93, 'TCK-202509-0093', 29, 'JARINGAN', 'Dummy ticket 88', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (94, 'TCK-202509-0094', 31, 'OTHER', 'Dummy ticket 89', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 2, NULL);
INSERT INTO `tickets` VALUES (95, 'TCK-202509-0095', 15, 'CBS', 'Dummy ticket 90', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 1, NULL);
INSERT INTO `tickets` VALUES (96, 'TCK-202509-0096', 14, 'CBS', 'Dummy ticket 91', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (97, 'TCK-202509-0097', 15, 'OTHER', 'Dummy ticket 92', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (98, 'TCK-202509-0098', 8, 'OTHER', 'Dummy ticket 93', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (99, 'TCK-202509-0099', 26, 'OTHER', 'Dummy ticket 94', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (100, 'TCK-202509-0100', 25, 'CBS', 'Dummy ticket 95', NULL, 'ON_PROGRESS', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 1, NULL);
INSERT INTO `tickets` VALUES (101, 'TCK-202509-0101', 4, 'CBS', 'Dummy ticket 96', NULL, 'CLOSED', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', 3, NULL);
INSERT INTO `tickets` VALUES (102, 'TCK-202509-0102', 19, 'CBS', 'Dummy ticket 97', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (103, 'TCK-202509-0103', 15, 'CBS', 'Dummy ticket 98', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (104, 'TCK-202509-0104', 20, 'CBS', 'Dummy ticket 99', NULL, 'OPEN', NULL, NULL, '2025-09-27 17:54:54', '2025-09-27 17:54:54', NULL, NULL);
INSERT INTO `tickets` VALUES (105, 'TCK-202509-0105', 29, 'CBS', 'asdadsadas', 'lampiran/6PzGoRJ3W48Q8n9rs0W1MwLsGrNZlKjLXUKQu4Yf.png', 'OPEN', NULL, NULL, '2025-09-28 03:18:22', '2025-09-28 03:18:22', NULL, NULL);
INSERT INTO `tickets` VALUES (106, 'TCK-202509-0106', 15, 'JARINGAN', 'sdadadsa', 'lampiran/v1Jl7ZiTZzcdFQnuZJNVZqOZira4HMJZ6cvAptbW.png', 'OPEN', NULL, NULL, '2025-09-29 09:36:56', '2025-09-29 09:36:56', NULL, NULL);
INSERT INTO `tickets` VALUES (107, 'TCK-202509-0107', 15, 'CBS', 'adsda', 'lampiran/M8uNxBVdHtr705UupSnE8A8LKZA7VbQcSTWulBhN.png', 'OPEN', NULL, NULL, '2025-09-29 09:48:48', '2025-09-29 09:48:48', NULL, NULL);
INSERT INTO `tickets` VALUES (108, 'TCK-202509-0108', 15, 'OTHER', 'asddsadsa', 'lampiran/raLUjqlnSJvUYl7dICrNLUgA0GLX3XrbHFXWpmpX.png', 'OPEN', NULL, NULL, '2025-09-29 09:53:38', '2025-09-29 09:53:38', NULL, NULL);
INSERT INTO `tickets` VALUES (109, 'TCK-202509-0109', 4, 'JARINGAN', 'test123', NULL, 'CLOSED', '2025-09-30 02:27:41', '2025-09-30 02:29:41', '2025-09-30 02:27:01', '2025-09-30 02:29:41', 1, 'TIDAK');

-- ----------------------------
-- Table structure for users
-- ----------------------------
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('IT','CABANG') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'CABANG',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE INDEX `users_email_unique`(`email`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 32 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of users
-- ----------------------------
INSERT INTO `users` VALUES (1, 'Erik Pratama Yudha', 'erik', 'erik@example.test', NULL, '$2y$10$02lAPqw38WC1mlsph8hE3usM1jv/BKxkQS/SPz1LsQ6BqZXx0u.QK', NULL, '2025-09-27 15:58:34', '2025-09-27 15:58:34', 'IT');
INSERT INTO `users` VALUES (2, 'Yuda Hardiadi Putra', 'yuda', 'yuda@example.test', NULL, '$2y$10$EGpP2DgrENRLlzd3kQYj3eHFe/tTG5yPeN2Xq68rrSHFGNXb4537i', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'IT');
INSERT INTO `users` VALUES (3, 'Admin IT', 'admin', 'admin@example.test', NULL, '$2y$10$dOr.ZFkooEjIMnfHU4ULne0tsV05xY1dsLZOteJy90Mby9tn8Plq6', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'IT');
INSERT INTO `users` VALUES (4, 'Cabang 001', '001', '001@example.test', NULL, '$2y$10$DobQzKxxeYnbaNJS.Fgmne6bPo5AHV7g4qyJr.IDPoKuVnqQ4lKRS', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (5, 'Cabang 002', '002', '002@example.test', NULL, '$2y$10$SJ8EitTrOG7HElJApU.kq.9jLApkm64jVXUF67Q2QZRQbdkRJiR/K', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (6, 'Cabang 003', '003', '003@example.test', NULL, '$2y$10$9r75iUbw3cp/5MUvxELgOO2hqqK1epeU.1kDQarRLrxXlGKY7ll8S', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (7, 'Cabang 004', '004', '004@example.test', NULL, '$2y$10$WghS8PHUmHhRoiKyXxeY3OyDZOjAmyeowETGPnmXKyoBARcLinNsC', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (8, 'Cabang 005', '005', '005@example.test', NULL, '$2y$10$ykJOzkeUbz1yvqReQdY31Og/qLLq7.fq0tY51hsfrmt8g1txfPuYa', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (9, 'Cabang 006', '006', '006@example.test', NULL, '$2y$10$B1Wl7vePWVTPVzuiUMAUBuMqdnaPFXebvIIRmp762KhXjBhZmB9Qi', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (10, 'Cabang 007', '007', '007@example.test', NULL, '$2y$10$dWXPzvSHQ2zJ/k5L9GW6e.AxB68yva9Hxpinj7Du3W6nrEgD.x3sG', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (11, 'Cabang 008', '008', '008@example.test', NULL, '$2y$10$8dykS8cZEUiSWNf/h3KoOe4qleG8vC5/mF9RlLX4si.AeO6tV2hXm', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (12, 'Cabang 009', '009', '009@example.test', NULL, '$2y$10$n64x0dVr7obLihteH15vXeiROFQBLbn7ucjVlrvnrcNp1EobD64hW', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (13, 'Cabang 010', '010', '010@example.test', NULL, '$2y$10$m9HB/V3a.sOQBlcKqAQnn.LwYcD9BYd95VR5C3kmuEOwtwlqcVMpS', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (14, 'Cabang 011', '011', '011@example.test', NULL, '$2y$10$mZh7X4ja3nZCweeWjVNTpu/HS9UhvdEEDS1F/fTx.ygOQ9FaUAnuq', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (15, 'Cabang 012', '012', '012@example.test', NULL, '$2y$10$6P/UCAzbnRISI7k9r6xD9eMs5l4Oh3oKCp/Um1mP21C/BAK6gYBZ2', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (16, 'Cabang 013', '013', '013@example.test', NULL, '$2y$10$5frGCqF6tCzvF9c17NcWguUQG/9JwsqGeW2wRmIWyxup1uxGWwVPK', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (17, 'Cabang 014', '014', '014@example.test', NULL, '$2y$10$Nwb98mKWzs3albErfNo7o.WLPJ02JIlDuFRGCfLSeUie.LaE5BOya', NULL, '2025-09-27 15:58:35', '2025-09-27 15:58:35', 'CABANG');
INSERT INTO `users` VALUES (18, 'Cabang 015', '015', '015@example.test', NULL, '$2y$10$xbEywfuSFPxgcyh5NzmVZO7Ny9U.1.JhfF9gkYihBU6iuO1fss6d.', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (19, 'Cabang 016', '016', '016@example.test', NULL, '$2y$10$TwBcz5qwnIGVUCZ2GX5NUOpzaT//0P3FgUnV91FEFhv00zFKI7YmO', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (20, 'Cabang 017', '017', '017@example.test', NULL, '$2y$10$nclwLlCIFWnTjS5yTgprdOvTCrJ9CTWD.RMGFG/Wk8.jTsEKvd04m', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (21, 'Cabang 018', '018', '018@example.test', NULL, '$2y$10$9Dbh2wa3ejBRP3n5CEW8..lKcrSnw6ChkDrp4ZVlCo.oTI6CT1jOq', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (22, 'Cabang 019', '019', '019@example.test', NULL, '$2y$10$oruOpLvMjxkDiZ0m7DPvhugNNQKTu6EEX.Oei7TIcZnnjHd6SOlU6', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (23, 'Cabang 020', '020', '020@example.test', NULL, '$2y$10$5H5dqoeHME2sTKLSCo/Dzu6y817HsOLQJllQw.gcazUEsZpl0sbwK', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (24, 'Cabang 021', '021', '021@example.test', NULL, '$2y$10$EkDndwHhzBwMXXTcU5yf7uNlP2mEYXoS8qGEP/TvWh4E5RoOGY072', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (25, 'Cabang 022', '022', '022@example.test', NULL, '$2y$10$NyZqfcBwz9yGMPcRdr9dqemkH/0XgsMcMbndCqqyxp0ZSym7XMj8q', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (26, 'Cabang 023', '023', '023@example.test', NULL, '$2y$10$LRf7E/irtby1wHWas2D9meR/KHKbWeeULl/Qc.q.L5iVT1QQZu9hS', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (27, 'Cabang 024', '024', '024@example.test', NULL, '$2y$10$bYfXgwL0QZTwJ5qU7i2TCe1bKP5duO/K58ZTVsb/h7lvDbf6YU5s6', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (28, 'Cabang 025', '025', '025@example.test', NULL, '$2y$10$HLuA7ELa37FM//2MkYt3kO0xapUkz9Uf2XP.S4PIj6O2OpLthet3q', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (29, 'Cabang 026', '026', '026@example.test', NULL, '$2y$10$WopeSBrZdYJIwaKtClXWx.yTvZr7XI2TounOO04Kc3q/jPoFrRwRu', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (30, 'Cabang 027', '027', '027@example.test', NULL, '$2y$10$UXdO671HSFjl0/hWLD1RKeg.E0oujW4cMWHWnXdBDHcGSj0iR0bb2', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');
INSERT INTO `users` VALUES (31, 'Cabang 028', '028', '028@example.test', NULL, '$2y$10$Psl81MargXO9/wK5sECuzuHlMT1iCSr1gX6J8lVo8lGTLzeuTENgq', NULL, '2025-09-27 15:58:36', '2025-09-27 15:58:36', 'CABANG');

SET FOREIGN_KEY_CHECKS = 1;
