-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 09, 2026 at 12:46 AM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u979833469_scholar`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_logs`
--

CREATE TABLE `action_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(128) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 2, 'batch_change_course', '{\"ids\":[8],\"course\":\"BSHM\"}', '::1', '2025-08-24 04:14:49'),
(2, 2, 'batch_change_course', '{\"ids\":[8],\"course\":\"BSED\"}', '::1', '2025-08-24 04:15:23'),
(3, 2, 'batch_change_type', '{\"ids\":[8],\"type\":\"TDP\"}', '::1', '2025-08-24 04:15:44'),
(4, 2, 'batch_change_year', '{\"ids\":[8],\"year\":\"1st Year\"}', '::1', '2025-08-24 04:16:32'),
(5, 2, 'batch_delete_scholars', '{\"ids\":[8]}', '::1', '2025-08-24 04:17:00'),
(6, 2, 'batch_change_year', '{\"ids\":[9],\"year\":\"3rd Year\"}', '::1', '2025-08-24 04:28:38'),
(7, 2, 'batch_reset_passwords', '{\"ids\":[9]}', '::1', '2025-08-24 04:29:19'),
(8, 2, 'batch_enroll', '{\"ids\":[9],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-08-26 14:48:26'),
(9, 2, 'batch_assign_batch', '{\"ids\":[10,11],\"batch\":\"batch 12\"}', '::1', '2025-08-27 22:57:26'),
(10, 2, 'batch_reset_passwords', '{\"ids\":[10,11],\"user_ids\":[15,16]}', '::1', '2025-08-27 22:58:14'),
(11, 2, 'batch_assign_batch', '{\"ids\":[11],\"batch\":\"batch 13\"}', '::1', '2025-08-28 03:20:48'),
(12, 2, 'batch_assign_batch', '{\"ids\":[12],\"batch\":\"batch 12\"}', '::1', '2025-08-28 03:27:15'),
(13, 2, 'batch_change_course', '{\"ids\":[10,11,12],\"course\":\"BSBA\"}', '::1', '2025-08-31 16:17:39'),
(14, 2, 'batch_change_year', '{\"ids\":[10,11,12],\"year\":\"4th Year\"}', '::1', '2025-08-31 16:25:17'),
(15, 2, 'batch_enroll', '{\"enrolled\":[10,11,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-08-31 16:31:09'),
(16, 2, 'batch_change_course', '{\"ids\":[10,11,12],\"course\":\"BSCS\"}', '::1', '2025-08-31 17:07:37'),
(17, 2, 'batch_change_course', '{\"ids\":[10,11,12],\"course\":\"BSBA\"}', '::1', '2025-09-01 18:55:08'),
(18, 2, 'batch_assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"Batch 13\"}', '::1', '2025-09-02 16:17:50'),
(19, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 02:08:41'),
(20, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 02:11:12'),
(21, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 02:11:46'),
(22, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 02:24:53'),
(23, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 02:25:26'),
(24, 2, 'batch_reset_passwords', '{\"ids\":[10,11,13,12],\"user_ids\":[15,16,17,18]}', '::1', '2025-09-03 03:05:56'),
(25, 2, 'change_course', '{\"ids\":[10,11,13,12],\"course\":\"BEED\"}', '::1', '2025-09-03 03:12:33'),
(26, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 03:26:39'),
(27, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 03:26:50'),
(28, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 03:33:00'),
(29, 2, 'batch_enroll', '{\"enrolled\":[10],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 03:33:51'),
(30, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 03:41:05'),
(31, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"1st\"}', '::1', '2025-09-03 03:42:22'),
(32, 2, 'batch_enroll', '{\"enrolled\":[10,11,13,12],\"skipped\":[],\"school_year_id\":1,\"semester\":\"2nd\"}', '::1', '2025-09-03 03:42:32'),
(33, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"Batch 11\"}', '::1', '2025-09-07 11:29:16'),
(34, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"Batch 13\"}', '::1', '2025-09-07 11:41:48'),
(35, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"Batch 11\"}', '::1', '2025-09-07 11:42:20'),
(36, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"Batch 13\"}', '::1', '2025-09-07 11:49:44'),
(37, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"12.00\"}', '::1', '2025-09-07 11:53:01'),
(38, 2, 'assign_batch', '{\"ids\":[10,11,13,12],\"batch\":\"12.10\"}', '::1', '2025-09-07 12:02:32'),
(39, 21, 'batch_enroll', '{\"enrolled\":[15],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '49.147.40.160', '2025-09-09 04:09:55'),
(40, 21, 'batch_reset_passwords', '{\"ids\":[15],\"user_ids\":[22]}', '49.147.40.160', '2025-09-09 04:10:46'),
(41, 21, 'batch_enroll', '{\"enrolled\":[16],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '49.147.40.160', '2025-09-09 04:22:26'),
(42, 21, 'assign_batch', '{\"ids\":[16],\"batch\":\"8.00\"}', '49.147.40.160', '2025-09-09 04:22:47'),
(43, 21, 'batch_reset_passwords', '{\"ids\":[16],\"user_ids\":[23]}', '49.147.40.160', '2025-09-09 04:23:35'),
(44, 20, 'assign_batch', '{\"ids\":[17],\"batch\":\"15.00\"}', '152.32.70.156', '2025-09-09 05:14:21'),
(45, 20, 'batch_enroll', '{\"enrolled\":[17],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-09 05:15:34'),
(46, 20, 'assign_batch', '{\"ids\":[17],\"batch\":\"15.00\"}', '152.32.70.156', '2025-09-09 05:16:39'),
(47, 20, 'assign_batch', '{\"ids\":[16],\"batch\":\"8.10\"}', '152.32.70.156', '2025-09-09 05:17:14'),
(48, 20, 'batch_enroll', '{\"enrolled\":[18],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-09 05:23:41'),
(49, 20, 'batch_enroll', '{\"enrolled\":[19],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-09 05:25:54'),
(50, 20, 'batch_enroll', '{\"enrolled\":[20],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-09 05:28:39'),
(51, 20, 'batch_enroll', '{\"enrolled\":[21],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-09 05:35:15'),
(52, 20, 'batch_reset_passwords', '{\"ids\":[22],\"user_ids\":[29]}', '180.195.195.163', '2025-09-09 11:34:56'),
(53, 20, 'assign_batch', '{\"ids\":[22],\"batch\":\"13.00\"}', '180.195.195.163', '2025-09-09 11:35:40'),
(54, 20, 'batch_enroll', '{\"enrolled\":[22],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '180.195.195.163', '2025-09-09 11:35:53'),
(55, 21, 'batch_enroll', '{\"enrolled\":[23,24],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '49.147.40.160', '2025-09-10 23:39:11'),
(56, 21, 'assign_batch', '{\"ids\":[23,24],\"batch\":\"13.00\"}', '49.147.40.160', '2025-09-10 23:39:37'),
(57, 21, 'batch_reset_passwords', '{\"ids\":[24],\"user_ids\":[31]}', '49.147.40.160', '2025-09-10 23:46:16'),
(58, 2, 'batch_reset_passwords', '{\"ids\":[17],\"user_ids\":[24]}', '49.147.40.160', '2025-09-11 07:17:52'),
(59, 2, 'assign_batch', '{\"ids\":[25],\"batch\":\"13.00\"}', '49.147.40.160', '2025-09-11 07:33:41'),
(60, 2, 'assign_batch', '{\"ids\":[25],\"batch\":\"13.00\"}', '49.147.40.160', '2025-09-11 07:34:14'),
(61, 2, 'batch_enroll', '{\"enrolled\":[22],\"skipped\":[],\"school_year_id\":5,\"semester\":\"2nd\"}', '180.191.225.232', '2025-09-14 12:17:33'),
(62, 2, 'assign_batch', '{\"ids\":[17,20,23,24,19,16,18,15,21],\"batch\":\"12.00\"}', '180.191.225.232', '2025-09-14 12:41:21'),
(63, 20, 'batch_reset_passwords', '{\"ids\":[21],\"user_ids\":[28]}', '2001:4453:608:9b00:2014:5000:d9cd:fe6b', '2025-09-15 10:22:30'),
(64, 21, 'batch_enroll', '{\"enrolled\":[26],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-15 11:22:52'),
(65, 21, 'batch_reset_passwords', '{\"ids\":[26],\"user_ids\":[33]}', '103.252.32.12', '2025-09-15 11:23:20'),
(66, 20, 'batch_delete_scholars', '{\"ids\":[17,20,23,26,24,19,16,18,15,21],\"user_ids\":[22,23,24,25,26,27,28,30,31,33]}', '152.32.70.156', '2025-09-17 04:41:00'),
(67, 2, 'batch_enroll', '{\"enrolled\":[27],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-17 04:55:57'),
(68, 2, 'batch_reset_passwords', '{\"ids\":[27],\"user_ids\":[34]}', '152.32.70.156', '2025-09-17 04:56:25'),
(69, 20, 'batch_enroll', '{\"enrolled\":[29],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '2001:4453:608:9b00:747f:3d03:c0d3:1aae', '2025-09-19 12:06:17'),
(70, 20, 'batch_reset_passwords', '{\"ids\":[29],\"user_ids\":[36]}', '2001:4453:608:9b00:747f:3d03:c0d3:1aae', '2025-09-19 12:06:27'),
(71, 21, 'assign_batch', '{\"ids\":[30],\"batch\":\"15.00\"}', '103.252.32.12', '2025-09-19 13:06:07'),
(72, 21, 'batch_enroll', '{\"enrolled\":[30],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-19 13:06:33'),
(73, 21, 'assign_batch', '{\"ids\":[30],\"batch\":\"15.00\"}', '103.252.32.12', '2025-09-19 13:07:07'),
(74, 21, 'batch_reset_passwords', '{\"ids\":[30],\"user_ids\":[37]}', '103.252.32.12', '2025-09-19 13:08:00'),
(75, 21, 'batch_delete_scholars', '{\"ids\":[30],\"user_ids\":[37]}', '103.252.32.12', '2025-09-19 13:53:43'),
(76, 2, 'batch_enroll', '{\"enrolled\":[32,33],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '2405:8d40:448e:d60c:3949:b7cd:16da:94f0', '2025-09-24 02:49:00'),
(77, 21, 'batch_enroll', '{\"enrolled\":[34],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-25 09:41:55'),
(78, 21, 'batch_enroll', '{\"enrolled\":[35],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-25 09:43:54'),
(79, 21, 'batch_enroll', '{\"enrolled\":[38,39,41,42],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 05:03:13'),
(80, 21, 'batch_enroll', '{\"enrolled\":[36,37],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 05:03:39'),
(81, 21, 'batch_enroll', '{\"enrolled\":[39],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '152.32.70.156', '2025-09-30 05:03:48'),
(82, 21, 'batch_enroll', '{\"enrolled\":[44],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 05:43:57'),
(83, 21, 'batch_enroll', '{\"enrolled\":[43],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 05:51:28'),
(84, 21, 'batch_enroll', '{\"enrolled\":[67,46,66,76,65,64,63,50,62,75,70,68,61,49,60,74,48,59,58,57,56,55,54,73,72,53,47,52,71,51,69],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 06:19:16'),
(85, 21, 'batch_enroll', '{\"enrolled\":[91,77,82,92,93,94,79,80,85,83,86,96,95,81,87,84,99,90,88,98,97,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 06:28:26'),
(86, 21, 'batch_enroll', '{\"enrolled\":[89,78],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 06:28:49'),
(87, 21, 'change_type', '{\"ids\":[38],\"scholarship_type\":\"Listahanan\"}', '110.54.191.137', '2025-09-30 06:36:12'),
(88, 21, 'batch_enroll', '{\"enrolled\":[101],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 06:39:56'),
(89, 21, 'batch_enroll', '{\"enrolled\":[103,104,105],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 07:04:31'),
(90, 21, 'batch_enroll', '{\"enrolled\":[106],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 07:10:17'),
(91, 21, 'batch_enroll', '{\"enrolled\":[107],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.191.137', '2025-09-30 07:11:51'),
(92, 21, 'batch_enroll', '{\"enrolled\":[108,111,110,109],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 10:32:27'),
(93, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,45,91,38,108,39,107,67,41,103,42,35,43,46,77,66,82,76,92,65,115,64,116,63,104,89,93,111,50,62,101,75,94,122,70,68,121,61,49,112,32,33,79,80,85,60,74,48,83,59,86,58,96,95,57,117,56,55,118,54,81,114,87,84,73,99,90,72,53,110,88,47,98,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 13:51:41'),
(94, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,123,45,91,38,108,39,107,67,41,103,42,35,130,128,43,127,136,46,77,131,66,82,76,92,65,115,64,124,135,116,63,104,89,93,111,50,62,101,75,94,122,125,70,129,68,121,61,49,112,133,32,33,79,80,126,85,132,60,74,48,134,83,59,86,58,96,95,57,117,56,55,118,54,81,114,87,84,73,99,90,137,72,53,110,88,47,98,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 13:55:28'),
(95, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,123,45,91,38,108,39,107,67,41,103,42,35,130,128,43,127,136,46,77,131,66,82,76,138,92,65,115,64,124,135,116,63,104,89,93,111,50,62,101,75,94,122,125,70,129,68,121,61,49,112,133,32,33,79,80,126,85,132,60,74,48,134,83,59,86,58,96,95,57,117,56,55,118,54,81,114,87,84,73,99,90,137,72,53,110,88,47,98,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 14:14:01'),
(96, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,123,45,91,38,108,39,107,67,41,103,42,35,130,128,43,127,136,46,77,131,66,82,76,138,92,65,140,115,64,124,135,116,63,104,89,93,111,50,62,101,75,94,122,125,70,129,68,121,61,49,112,133,32,33,79,80,126,85,132,60,74,48,134,83,59,86,58,96,95,57,117,56,55,118,54,81,139,114,87,84,73,99,90,137,141,72,53,110,88,47,98,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 14:19:08'),
(97, 21, 'batch_enroll', '{\"enrolled\":[142],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 14:28:59'),
(98, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,123,45,91,38,108,39,107,67,41,103,144,42,35,130,128,43,127,136,46,77,131,66,82,76,138,92,65,140,115,64,124,135,116,63,104,89,93,111,50,62,101,75,94,143,122,125,70,129,68,121,61,49,145,112,133,32,33,79,80,126,85,132,60,74,48,134,83,59,86,58,96,95,57,117,56,55,118,54,81,139,114,87,84,73,99,90,137,141,72,53,110,88,47,98,142,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-09-30 14:33:53'),
(99, 20, 'batch_enroll', '{\"enrolled\":[146],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.54', '2025-09-30 15:24:38'),
(100, 20, 'batch_enroll', '{\"enrolled\":[146],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.54', '2025-09-30 15:24:39'),
(101, 20, 'batch_reset_passwords', '{\"ids\":[146],\"user_ids\":[154]}', '136.239.180.54', '2025-09-30 15:25:04'),
(102, 21, 'batch_enroll', '{\"enrolled\":[36,44,37,34,123,45,91,38,108,39,107,67,41,103,144,42,35,130,128,43,127,136,46,77,131,66,82,76,138,92,65,140,115,64,124,135,116,63,104,89,93,150,111,50,62,101,75,94,143,122,125,70,129,68,121,61,49,145,112,133,32,33,79,80,126,85,132,60,74,48,134,83,59,86,58,96,95,57,148,117,56,55,118,54,81,139,114,87,146,84,73,147,99,90,137,141,149,72,53,110,88,47,98,142,52,105,71,119,106,120,51,109,97,78,113,69,100],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-10-01 03:58:43'),
(103, 21, 'batch_enroll', '{\"enrolled\":[153,143,152,115,140,141,142,139,116,108,38,119,136,135,33,77,118,109,121,148,120,138,117,137,151],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '103.252.32.12', '2025-10-03 11:09:54'),
(104, 20, 'assign_batch', '{\"ids\":[142],\"batch\":\"15.00\"}', '136.239.180.217', '2025-10-03 11:37:23'),
(105, 20, 'change_type', '{\"ids\":[130,129,149,101,58,57,52,104,113,107,49,48,106,44,128,127,65,61,59,50,47,34,67,41,42],\"scholarship_type\":\"Listahanan\"}', '136.239.180.217', '2025-10-03 11:42:13'),
(106, 20, 'change_type', '{\"ids\":[114,112,39,147,110,66,63,62,55,53,51,100,46,64,150,111,54,142,60,56],\"scholarship_type\":\"Listahanan\"}', '136.239.180.217', '2025-10-03 11:43:17'),
(107, 20, 'change_type', '{\"ids\":[68],\"scholarship_type\":\"Listahanan\"}', '136.239.180.217', '2025-10-03 11:44:42'),
(108, 20, 'change_type', '{\"ids\":[92,97,37,103,94,123,145,98,73,95,96,74,71,89,90,75,125,70,72,91,124,76,32,69,93],\"scholarship_type\":\"Listahanan\"}', '136.239.180.217', '2025-10-03 11:45:38'),
(109, 20, 'change_type', '{\"ids\":[43,126],\"scholarship_type\":\"Listahanan\"}', '136.239.180.217', '2025-10-03 11:46:05'),
(110, 20, 'batch_enroll', '{\"enrolled\":[158,156,155,154,157],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.217', '2025-10-03 13:43:47'),
(111, 20, 'batch_enroll', '{\"enrolled\":[159,160],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.21', '2025-10-05 14:24:22'),
(112, 20, 'batch_enroll', '{\"enrolled\":[161],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.21', '2025-10-05 14:30:23'),
(113, 20, 'assign_batch', '{\"ids\":[115],\"batch\":\"19.00\"}', '49.147.51.204', '2025-10-08 04:48:23'),
(114, 21, 'batch_enroll', '{\"enrolled\":[162],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '49.147.51.204', '2025-10-09 05:30:43'),
(115, 21, 'batch_enroll', '{\"enrolled\":[164],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '49.147.51.204', '2025-10-09 06:25:26'),
(116, 20, 'batch_enroll', '{\"enrolled\":[165],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '136.239.180.216', '2025-10-19 12:41:37'),
(117, 20, 'batch_reset_passwords', '{\"ids\":[165],\"user_ids\":[173]}', '136.239.180.216', '2025-10-19 12:42:23'),
(118, 21, 'batch_reset_passwords', '{\"ids\":[163],\"user_ids\":[171]}', '110.54.143.143', '2025-10-24 04:59:11'),
(119, 21, 'batch_enroll', '{\"enrolled\":[163],\"skipped\":[],\"school_year_id\":5,\"semester\":\"1st\"}', '110.54.143.143', '2025-10-24 05:01:12'),
(120, 2, 'batch_reset_passwords', '{\"ids\":[45],\"user_ids\":[53]}', '180.191.224.101', '2025-11-30 15:55:02'),
(121, 2, 'batch_reset_passwords', '{\"ids\":[45],\"user_ids\":[53]}', '180.191.224.101', '2025-11-30 16:05:36'),
(122, 2, 'change_type', '{\"ids\":[36],\"scholarship_type\":\"Listahanan\"}', '180.191.224.101', '2025-11-30 18:19:16'),
(123, 2, 'change_type', '{\"ids\":[92,97,37,103,94,123,145,99,98,73,95,96,74,71,89,90,75,125,70,72,91,124,76,32,132],\"scholarship_type\":\"Listahanan\"}', '180.191.224.101', '2025-11-30 18:20:22'),
(124, 2, 'assign_batch', '{\"ids\":[92,97,37,103,94,123,145,99,98,73,95,96,74,71,89,90,75,125,70,72,91,124,76,32,132],\"batch\":\"2.00\"}', '180.191.224.101', '2025-11-30 18:20:49'),
(125, 2, 'change_type', '{\"ids\":[69,93,43,126,159],\"scholarship_type\":\"Listahanan\"}', '180.191.224.101', '2025-11-30 18:21:26'),
(126, 2, 'assign_batch', '{\"ids\":[159,69,93,43,126],\"batch\":\"2.00\"}', '180.191.224.101', '2025-11-30 18:21:45'),
(127, 2, 'assign_batch', '{\"ids\":[158,143,152,141,139,134,79,78,105,154,122,85,87,86,88,165,162,164,160,161,115],\"batch\":\"1.00\"}', '180.191.224.101', '2025-11-30 18:25:55'),
(128, 2, 'change_type', '{\"ids\":[134,79,158,87,162,143,85,78,122,86,152,164,105,160,161,88,165,115,141,154,139],\"scholarship_type\":\"Listahanan\"}', '180.191.224.101', '2025-11-30 18:27:24'),
(129, 2, 'assign_batch', '{\"ids\":[101,58,57,52,104,113,107,48,106,44,128,127,65,61,59,50,47,34,67,41,42,114,112,39,147],\"batch\":\"1.00\"}', '180.191.224.101', '2025-11-30 18:28:09'),
(130, 2, 'assign_batch', '{\"ids\":[110,66,63,62,129,55,53,51,100,64,150,111,54,142,60,56,149,163],\"batch\":\"1.00\"}', '180.191.224.101', '2025-11-30 18:28:33'),
(131, 2, 'assign_batch', '{\"ids\":[131,144,68],\"batch\":\"1.00\"}', '180.191.224.101', '2025-11-30 18:29:22'),
(132, 20, 'batch_reset_passwords', '{\"ids\":[167],\"user_ids\":[176]}', '112.198.134.26', '2025-12-03 05:26:41'),
(133, 20, 'batch_enroll', '{\"enrolled\":[167],\"skipped\":[],\"school_year_id\":5,\"semester\":\"2nd\"}', '112.198.134.26', '2025-12-03 05:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `credentials`
--

CREATE TABLE `credentials` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `credential_type` varchar(150) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `exported_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `permanent` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `physical_copy_confirmed` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `documents`
--

INSERT INTO `documents` (`id`, `scholar_id`, `document_type`, `file_path`, `uploaded_at`, `updated_at`, `status`, `physical_copy_confirmed`) VALUES
(36, 34, 'Certificate of Grades', 'uploads/credentials/doc_42_1759207873_inbound7459727666259512117.pdf', '2025-09-30 04:51:13', NULL, 'Approved', 1),
(37, 34, 'Student ID', 'uploads/credentials/doc_42_1759207912_inbound5031826748516589224.pdf', '2025-09-30 04:51:52', NULL, 'Approved', 1),
(38, 34, 'Certificate of Registration', 'uploads/credentials/doc_42_1759207999_inbound2561451731956688623.pdf', '2025-09-30 04:53:19', NULL, 'Approved', 1),
(39, 47, 'Certificate of Grades', 'uploads/credentials/doc_55_1759309629_inbound1790875769123389321.pdf', '2025-09-30 06:20:07', NULL, 'Approved', 1),
(40, 47, 'Certificate of Registration', 'uploads/credentials/doc_55_1759215657_inbound6220934075101817295.pdf', '2025-09-30 06:20:56', NULL, 'Approved', 1),
(41, 47, 'Student ID', 'uploads/credentials/doc_55_1759215675_inbound2758085867116423217.pdf', '2025-09-30 06:21:16', NULL, 'Approved', 1),
(42, 65, 'Certificate of Registration', 'uploads/credentials/doc_73_1759213435_inbound7665354872548919962.pdf', '2025-09-30 06:23:55', NULL, 'Approved', 1),
(43, 65, 'Student ID', 'uploads/credentials/doc_73_1759213449_inbound5200239863029392328.pdf', '2025-09-30 06:24:09', NULL, 'Approved', 1),
(44, 78, 'Certificate of Grades', 'uploads/credentials/doc_86_1759213659_TRIVI__O_MICHAEL_RAMOS__COG.pdf', '2025-09-30 06:27:39', NULL, 'Approved', 1),
(45, 78, 'Certificate of Registration', 'uploads/credentials/doc_86_1759213681_Trivi__o_Michael_Ramos_COR.pdf', '2025-09-30 06:28:01', NULL, 'Approved', 1),
(46, 78, 'Student ID', 'uploads/credentials/doc_86_1759213705_Trivi__o_Michael_R._ID.pdf', '2025-09-30 06:28:25', NULL, 'Approved', 1),
(47, 86, 'Certificate of Registration', 'uploads/credentials/doc_94_1759213707_inbound2444099857373129077.jpg', '2025-09-30 06:28:26', NULL, 'Approved', 1),
(50, 65, 'Certificate of Grades', 'uploads/credentials/doc_73_1760962725_2025-10-20_20-08-36.pdf', '2025-09-30 06:29:23', NULL, 'Pending', 1),
(51, 86, 'Student ID', 'uploads/credentials/doc_94_1759213878_Document__TES_Id.pdf', '2025-09-30 06:31:18', NULL, 'Rejected', 0),
(52, 48, 'Certificate of Grades', 'uploads/credentials/doc_56_1759214560_inbound6944213934165978318.pdf', '2025-09-30 06:42:37', NULL, 'Rejected', 0),
(54, 62, 'Certificate of Grades', 'uploads/credentials/doc_70_1759981237_Nikka_20Decastro_COG.pdf', '2025-09-30 06:43:54', NULL, 'Approved', 1),
(55, 62, 'Certificate of Registration', 'uploads/credentials/doc_70_1759981107_Nikka_20Decastro__20COR.pdf', '2025-09-30 06:44:42', NULL, 'Approved', 1),
(56, 62, 'Student ID', 'uploads/credentials/doc_70_1759214766_De_Castro_Nikka_Mae_B_ID.pdf', '2025-09-30 06:45:09', NULL, 'Approved', 1),
(58, 48, 'Certificate of Registration', 'uploads/credentials/doc_56_1759215107_inbound4067238003045768579.pdf', '2025-09-30 06:51:47', NULL, 'Approved', 1),
(59, 55, 'Certificate of Grades', 'uploads/credentials/doc_63_1759215131_inbound4623322136961276349.pdf', '2025-09-30 06:52:11', NULL, 'Approved', 1),
(60, 55, 'Certificate of Registration', 'uploads/credentials/doc_63_1759215178_inbound3548475720067924871.pdf', '2025-09-30 06:52:58', NULL, 'Approved', 1),
(61, 55, 'Student ID', 'uploads/credentials/doc_63_1759215193_inbound1444188370709336999.pdf', '2025-09-30 06:53:13', NULL, 'Approved', 1),
(62, 48, 'Student ID', 'uploads/credentials/doc_56_1759215197_inbound845067127686224774.pdf', '2025-09-30 06:53:17', NULL, 'Approved', 1),
(66, 51, 'Certificate of Grades', 'uploads/credentials/doc_59_1759215970_Salgarino__Charmaine__B._COG.pdf', '2025-09-30 07:06:10', NULL, 'Approved', 1),
(67, 51, 'Certificate of Registration', 'uploads/credentials/doc_59_1759215998_Salgarino__Charmaine__B._COR.pdf', '2025-09-30 07:06:38', NULL, 'Approved', 1),
(68, 91, 'Certificate of Grades', 'uploads/credentials/doc_99_1759216075_CamScanner_09-17-2025_15.51.pdf', '2025-09-30 07:07:55', NULL, 'Approved', 1),
(69, 51, 'Student ID', 'uploads/credentials/doc_59_1759216116_Salgarino__Charmaine__B._ID.pdf', '2025-09-30 07:08:34', NULL, 'Approved', 1),
(71, 91, 'Certificate of Registration', 'uploads/credentials/doc_99_1759216141_CamScanner_09-22-2025_09.28.pdf', '2025-09-30 07:09:01', NULL, 'Approved', 1),
(72, 91, 'Student ID', 'uploads/credentials/doc_99_1759216171_CamScanner_09-24-2025_09.37.pdf', '2025-09-30 07:09:31', NULL, 'Approved', 1),
(73, 63, 'Certificate of Grades', 'uploads/credentials/doc_71_1759216455_inbound3137624281531761932.pdf', '2025-09-30 07:14:15', NULL, 'Approved', 1),
(74, 63, 'Certificate of Registration', 'uploads/credentials/doc_71_1759216485_inbound6106262558160122198.pdf', '2025-09-30 07:14:45', NULL, 'Approved', 1),
(75, 63, 'Student ID', 'uploads/credentials/doc_71_1759216517_inbound7267548721502949975.pdf', '2025-09-30 07:15:17', NULL, 'Approved', 1),
(76, 53, 'Certificate of Grades', 'uploads/credentials/doc_61_1759217046__COG_Ondo__Jamaica_P._BSED_2_FM_.pdf.pdf', '2025-09-30 07:24:06', NULL, 'Approved', 1),
(77, 53, 'Certificate of Registration', 'uploads/credentials/doc_61_1759217121_COR_BSED2_FM_ONDO_JAMAICA_P..pdf.pdf', '2025-09-30 07:25:21', NULL, 'Approved', 1),
(79, 53, 'Student ID', 'uploads/credentials/doc_61_1759218277_ID_BSED2_FM_ONDO__JAMAICA_P..pdf', '2025-09-30 07:26:00', NULL, 'Approved', 1),
(81, 41, 'Student ID', 'uploads/credentials/doc_49_1759217492_inbound9152810172036297240.pdf', '2025-09-30 07:31:32', NULL, 'Approved', 1),
(82, 41, 'Certificate of Registration', 'uploads/credentials/doc_49_1759217504_inbound7651843130003587554.pdf', '2025-09-30 07:31:44', NULL, 'Approved', 1),
(83, 41, 'Certificate of Grades', 'uploads/credentials/doc_49_1759498347_inbound1578560706377898636.pdf', '2025-09-30 07:32:08', NULL, 'Approved', 1),
(84, 57, 'Certificate of Registration', 'uploads/credentials/doc_65_1759217914_Labustro__Kaye_Airelle__B._COR.pdf', '2025-09-30 07:38:29', NULL, 'Approved', 1),
(89, 57, 'Student ID', 'uploads/credentials/doc_65_1759217929_Labustro__Kaye_Airelle__B._ID.pdf', '2025-09-30 07:38:49', NULL, 'Approved', 1),
(90, 89, 'Certificate of Grades', 'uploads/credentials/doc_97_1759218036_Catibog__Stephanie__M._COG.pdf', '2025-09-30 07:40:36', NULL, 'Approved', 1),
(92, 89, 'Certificate of Registration', 'uploads/credentials/doc_97_1759218087_Catibog__Stephanie_M._COR.pdf', '2025-09-30 07:41:13', NULL, 'Approved', 1),
(95, 89, 'Student ID', 'uploads/credentials/doc_97_1759218157_Catibog__Stephanie_M._ID.pdf', '2025-09-30 07:42:12', NULL, 'Approved', 1),
(99, 107, 'Certificate of Registration', '', '2025-09-30 07:49:14', NULL, 'Rejected', 0),
(101, 107, 'Student ID', 'uploads/credentials/doc_115_1759218645_Aytona__Pauline_Anne_A.__ID.pdf', '2025-09-30 07:50:45', NULL, 'Approved', 1),
(102, 105, 'Certificate of Registration', 'uploads/credentials/doc_113_1759218927_COR-BSED2-FM-KRISTINE-JOY-PUNZALAN.pdf', '2025-09-30 07:55:27', NULL, 'Approved', 1),
(103, 105, 'Student ID', 'uploads/credentials/doc_113_1759219148_Punzalan_Kristine_Joy_V._ID_pdf.pdf', '2025-09-30 07:59:08', NULL, 'Approved', 1),
(104, 57, 'Certificate of Grades', 'uploads/credentials/doc_65_1759219206_Labustro__Kaye_Airelle__B._COG.pdf', '2025-09-30 08:00:06', NULL, 'Approved', 1),
(105, 105, 'Certificate of Grades', 'uploads/credentials/doc_113_1759219216_Punzalan_Kristine_Joy_V._COG.pdf', '2025-09-30 08:00:16', NULL, 'Approved', 1),
(106, 69, 'Certificate of Grades', 'uploads/credentials/doc_77_1759219230_inbound4566718607485176202.pdf', '2025-09-30 08:00:30', NULL, 'Approved', 1),
(107, 69, 'Certificate of Registration', 'uploads/credentials/doc_77_1759219267_inbound3586826671647136577.pdf', '2025-09-30 08:01:07', NULL, 'Approved', 1),
(108, 69, 'Student ID', 'uploads/credentials/doc_77_1759219319_inbound2670702657938897960.pdf', '2025-09-30 08:01:59', NULL, 'Approved', 1),
(109, 75, 'Certificate of Grades', 'uploads/credentials/doc_83_1759229535_inbound819690353592123661.pdf', '2025-09-30 08:31:51', NULL, 'Approved', 1),
(110, 75, 'Certificate of Registration', 'uploads/credentials/doc_83_1759229594_inbound5699214420299537492.pdf', '2025-09-30 08:32:17', NULL, 'Approved', 1),
(111, 75, 'Student ID', 'uploads/credentials/doc_83_1759229611_inbound8026325317014195135.pdf', '2025-09-30 08:32:40', NULL, 'Approved', 1),
(112, 44, 'Certificate of Grades', 'uploads/credentials/doc_52_1759222647_COG.pdf', '2025-09-30 08:57:27', NULL, 'Approved', 1),
(113, 44, 'Certificate of Registration', 'uploads/credentials/doc_52_1759222708_COR.pdf', '2025-09-30 08:58:28', NULL, 'Approved', 1),
(114, 44, 'Student ID', 'uploads/credentials/doc_52_1759222773_Agquiz_Crisalyn_C._ID.pdf', '2025-09-30 08:59:33', NULL, 'Approved', 1),
(115, 52, 'Certificate of Registration', 'uploads/credentials/doc_60_1759224477_inbound231265076427923003.jpg', '2025-09-30 09:27:18', NULL, 'Approved', 1),
(116, 52, 'Certificate of Grades', 'uploads/credentials/doc_60_1759224458_inbound8375733325831911672.jpg', '2025-09-30 09:27:38', NULL, 'Approved', 1),
(118, 52, 'Student ID', 'uploads/credentials/doc_60_1759224573_inbound8539865450980389551.jpg', '2025-09-30 09:29:17', NULL, 'Approved', 1),
(121, 108, 'Certificate of Grades', 'uploads/credentials/doc_116_1759227449_inbound3036764753416931653.pdf', '2025-09-30 10:11:59', NULL, 'Approved', 1),
(122, 108, 'Certificate of Registration', 'uploads/credentials/doc_116_1759227470_inbound4818432510556071401.pdf', '2025-09-30 10:14:46', NULL, 'Approved', 1),
(124, 108, 'Student ID', 'uploads/credentials/doc_116_1759227314_inbound2974859905875803899.pdf', '2025-09-30 10:15:14', NULL, 'Approved', 1),
(130, 104, 'Certificate of Registration', 'uploads/credentials/doc_112_1759229750_Castillo__Antonette_A.__COR.pdf', '2025-09-30 10:55:50', NULL, 'Approved', 1),
(131, 104, 'Student ID', 'uploads/credentials/doc_112_1759229877_Castillo__Antonette_A.__ID.pdf', '2025-09-30 10:56:21', NULL, 'Approved', 1),
(133, 96, 'Certificate of Grades', 'uploads/credentials/doc_104_1759230851_Krause__Kevin_Jurgen_G_COG.pdf', '2025-09-30 11:14:10', NULL, 'Approved', 1),
(135, 96, 'Certificate of Registration', 'uploads/credentials/doc_104_1759230870_Krause__Kevin_Jurgen_G_COR.pdf', '2025-09-30 11:14:30', NULL, 'Approved', 1),
(136, 96, 'Student ID', 'uploads/credentials/doc_104_1759230889_Krause__Kevin_Jurgen_G._ID.pdf', '2025-09-30 11:14:49', NULL, 'Approved', 1),
(137, 109, 'Certificate of Grades', 'uploads/credentials/doc_117_1759231151_inbound484152566111279213.pdf', '2025-09-30 11:19:11', NULL, 'Approved', 1),
(138, 81, 'Student ID', 'uploads/credentials/doc_89_1759231221_Macalalad__Ma._Andrei_D.__ID.jpg', '2025-09-30 11:20:16', NULL, 'Approved', 1),
(140, 109, 'Certificate of Registration', 'uploads/credentials/doc_117_1759231931_inbound7038599762683011171.pdf', '2025-09-30 11:32:03', NULL, 'Approved', 1),
(143, 109, 'Student ID', 'uploads/credentials/doc_117_1759232053_inbound5519699538557498612.pdf', '2025-09-30 11:34:13', NULL, 'Approved', 1),
(145, 59, 'Student ID', 'uploads/credentials/doc_67_1759232063_inbound13148664580060524.pdf', '2025-09-30 11:34:23', NULL, 'Approved', 1),
(146, 59, 'Certificate of Registration', 'uploads/credentials/doc_67_1759232136_inbound4105594180595316294.pdf', '2025-09-30 11:35:36', NULL, 'Approved', 1),
(147, 68, 'Certificate of Registration', 'uploads/credentials/doc_76_1759232517_inbound6576378942842176399.pdf', '2025-09-30 11:41:57', NULL, 'Approved', 1),
(148, 81, 'Certificate of Registration', 'uploads/credentials/doc_89_1759232584_Macalalad__Ma._Andrei_D.__COR.pdf', '2025-09-30 11:43:04', NULL, 'Approved', 1),
(149, 81, 'Certificate of Grades', 'uploads/credentials/doc_89_1759232607_Macalalad__Ma._Andrei_D._COG.pdf', '2025-09-30 11:43:27', NULL, 'Approved', 1),
(150, 59, 'Certificate of Grades', 'uploads/credentials/doc_67_1759232872_inbound140533679751933778.jpg', '2025-09-30 11:47:52', NULL, 'Rejected', 0),
(151, 68, 'Certificate of Grades', 'uploads/credentials/doc_76_1759235287_inbound4689736069068814695.pdf', '2025-09-30 12:28:07', NULL, 'Approved', 1),
(152, 68, 'Student ID', 'uploads/credentials/doc_76_1759235957_inbound6053328934314053721.pdf', '2025-09-30 12:39:17', NULL, 'Approved', 1),
(153, 93, 'Certificate of Registration', 'uploads/credentials/doc_101_1759237128_inbound3759475167848882876.pdf', '2025-09-30 12:58:48', NULL, 'Approved', 1),
(154, 93, 'Certificate of Grades', 'uploads/credentials/doc_101_1759237144_inbound5911671713826362584.pdf', '2025-09-30 12:59:04', NULL, 'Approved', 1),
(155, 93, 'Student ID', 'uploads/credentials/doc_101_1759237154_inbound2641938055317015615.pdf', '2025-09-30 12:59:14', NULL, 'Approved', 1),
(156, 88, 'Certificate of Grades', 'uploads/credentials/doc_96_1759239349_Pacleb__Marie_Thony__S_COG.pdf', '2025-09-30 13:35:49', NULL, 'Approved', 1),
(157, 88, 'Student ID', 'uploads/credentials/doc_96_1759239540_Pacleb__Marie_Thony_S._ID.pdf', '2025-09-30 13:36:39', NULL, 'Approved', 1),
(158, 88, 'Certificate of Registration', 'uploads/credentials/doc_96_1759239430_Pacleb__Marie_Thony__S_COR.pdf', '2025-09-30 13:37:10', NULL, 'Approved', 1),
(160, 116, 'Certificate of Registration', 'uploads/credentials/doc_124_1759240301_inbound6326310122127290677.jpg', '2025-09-30 13:51:41', NULL, 'Rejected', 0),
(161, 116, 'Student ID', 'uploads/credentials/doc_124_1759240316_inbound2571289254308890535.jpg', '2025-09-30 13:51:56', NULL, 'Rejected', 0),
(162, 117, 'Certificate of Grades', 'uploads/credentials/doc_125_1759240429_inbound7275189247164367062.pdf', '2025-09-30 13:53:49', NULL, 'Approved', 1),
(163, 117, 'Certificate of Registration', 'uploads/credentials/doc_125_1759240447_inbound3673726211943884993.pdf', '2025-09-30 13:54:07', NULL, 'Approved', 1),
(164, 117, 'Student ID', 'uploads/credentials/doc_125_1759240524_inbound8741489785205543368.pdf', '2025-09-30 13:55:24', NULL, 'Approved', 1),
(165, 126, 'Certificate of Grades', 'uploads/credentials/doc_134_1759240803_inbound7269180535075224366.jpg', '2025-09-30 14:00:03', NULL, 'Approved', 1),
(166, 126, 'Certificate of Registration', 'uploads/credentials/doc_134_1759240836_inbound6711419128652901506.jpg', '2025-09-30 14:00:36', NULL, 'Approved', 1),
(167, 126, 'Student ID', 'uploads/credentials/doc_134_1759240866_inbound4060692335022120824.jpg', '2025-09-30 14:01:06', NULL, 'Approved', 1),
(168, 90, 'Certificate of Grades', 'uploads/credentials/doc_98_1759492518_COG_-_MEDINA__KYLA_MARIE_C._BSED_2_FM.pdf.pdf', '2025-09-30 14:01:13', NULL, 'Approved', 1),
(169, 90, 'Certificate of Registration', 'uploads/credentials/doc_98_1759492017_Medina__Kyla_Marie_C._BSED_2-_FM_COE_034638.pdf', '2025-09-30 14:01:37', NULL, 'Approved', 1),
(170, 90, 'Student ID', 'uploads/credentials/doc_98_1759491976_Medina_Kyla_Marie_C._BSED_2-_FM_ID_105902.pdf', '2025-09-30 14:02:18', NULL, 'Approved', 1),
(171, 127, 'Certificate of Registration', 'uploads/credentials/doc_135_1759241008_Basilio__Charlize_Joan__R._COR.pdf', '2025-09-30 14:03:28', NULL, 'Approved', 1),
(172, 127, 'Certificate of Grades', 'uploads/credentials/doc_135_1759241079_Basilio__Charlize_Joan__R._COG.pdf', '2025-09-30 14:04:31', NULL, 'Approved', 1),
(174, 127, 'Student ID', 'uploads/credentials/doc_135_1759241112_Basilio__Charlize_Joan__R._ID.pdf', '2025-09-30 14:05:12', NULL, 'Approved', 1),
(175, 131, 'Certificate of Grades', 'uploads/credentials/doc_139_1759241710_Bautista__Erich_C._COG.pdf', '2025-09-30 14:15:10', NULL, 'Approved', 1),
(176, 131, 'Certificate of Registration', 'uploads/credentials/doc_139_1759241792_Bautista__Erich_C._COR.pdf', '2025-09-30 14:16:32', NULL, 'Approved', 1),
(177, 131, 'Student ID', 'uploads/credentials/doc_139_1759241825_Bautista__Erich_ID.pdf', '2025-09-30 14:17:05', NULL, 'Approved', 1),
(178, 43, 'Certificate of Grades', 'uploads/credentials/doc_51_1759242213_inbound7510055146233943746.pdf', '2025-09-30 14:23:33', NULL, 'Approved', 1),
(179, 43, 'Certificate of Registration', 'uploads/credentials/doc_51_1759242260_inbound8341100281964583918.pdf', '2025-09-30 14:24:20', NULL, 'Approved', 1),
(180, 43, 'Student ID', 'uploads/credentials/doc_51_1759242274_inbound1457459877970384791.pdf', '2025-09-30 14:24:34', NULL, 'Approved', 1),
(185, 138, 'Certificate of Grades', 'uploads/credentials/doc_146_1759244798_inbound1302812051924630638.pdf', '2025-09-30 15:06:38', NULL, 'Approved', 1),
(187, 138, 'Certificate of Registration', 'uploads/credentials/doc_146_1759246193_inbound8632486170825676566.pdf', '2025-09-30 15:29:53', NULL, 'Approved', 1),
(188, 138, 'Student ID', 'uploads/credentials/doc_146_1759246231_inbound7527366852861572076.pdf', '2025-09-30 15:30:31', NULL, 'Approved', 1),
(189, 101, 'Certificate of Registration', 'uploads/credentials/doc_109_1759249558_De_Guzman__Angel_Ann__E._COR.pdf.pdf', '2025-09-30 16:21:39', NULL, 'Approved', 1),
(190, 101, 'Student ID', 'uploads/credentials/doc_109_1759249508_DE_GUZMAN__ANGEL_ANN__E._ID.pdf', '2025-09-30 16:25:08', NULL, 'Approved', 1),
(192, 101, 'Certificate of Grades', 'uploads/credentials/doc_109_1759249636_De_Guzman___Angel_Ann___Elmission.jpg', '2025-09-30 16:27:16', NULL, 'Approved', 1),
(193, 115, 'Certificate of Grades', 'uploads/credentials/doc_123_1759268482_Blanza__Sharina_M_COG.pdf', '2025-09-30 21:41:22', NULL, 'Approved', 1),
(195, 125, 'Certificate of Grades', 'uploads/credentials/doc_133_1759272482_inbound8661477796393237114.pdf', '2025-09-30 22:48:02', NULL, 'Approved', 1),
(196, 125, 'Certificate of Registration', 'uploads/credentials/doc_133_1759272523_inbound4717405412125266099.pdf', '2025-09-30 22:48:43', NULL, 'Approved', 1),
(197, 125, 'Student ID', 'uploads/credentials/doc_133_1759272542_inbound5631313441412124767.pdf', '2025-09-30 22:49:02', NULL, 'Approved', 1),
(198, 129, 'Certificate of Grades', 'uploads/credentials/doc_137_1759277757_Princes_Mary_S._Del_Pilar_cog_.pdf', '2025-10-01 00:09:05', NULL, 'Approved', 1),
(199, 104, 'Certificate of Grades', 'uploads/credentials/doc_112_1759277615_Castillo__Antonette_A._COG.pdf', '2025-10-01 00:13:35', NULL, 'Approved', 1),
(201, 129, 'Certificate of Registration', 'uploads/credentials/doc_137_1759277773_Princes_Mary_S._Del_Pilar_Cor_.pdf', '2025-10-01 00:16:13', NULL, 'Approved', 1),
(202, 129, 'Student ID', 'uploads/credentials/doc_137_1759277785_Del_Pilar__Princes_Mary_S._Id.pdf.pdf', '2025-10-01 00:16:25', NULL, 'Approved', 1),
(203, 119, 'Certificate of Registration', 'uploads/credentials/doc_127_1759279089_Robles__Precious_M._COR.pdf', '2025-10-01 00:38:09', NULL, 'Approved', 1),
(204, 119, 'Student ID', 'uploads/credentials/doc_127_1759279101_Robles__Precious_M._ID.pdf', '2025-10-01 00:38:21', NULL, 'Approved', 1),
(205, 132, 'Certificate of Grades', 'uploads/credentials/doc_140_1759279649_ZARA__JOEFER_G._-_COG.pdf', '2025-10-01 00:47:29', NULL, 'Approved', 1),
(206, 132, 'Certificate of Registration', 'uploads/credentials/doc_140_1759279676_Zara__Joefer_G_-_COR.pdf', '2025-10-01 00:47:55', NULL, 'Approved', 1),
(208, 132, 'Student ID', 'uploads/credentials/doc_140_1759279695_Zara__Joefer_G_-_ID.pdf', '2025-10-01 00:48:15', NULL, 'Approved', 1),
(210, 80, 'Certificate of Grades', 'uploads/credentials/doc_88_1759282303_GARCIA__SHANE_AIRA_C._COG.pdf', '2025-10-01 01:31:43', NULL, 'Approved', 1),
(211, 80, 'Certificate of Registration', 'uploads/credentials/doc_88_1759282332_GARCIA__SHANE_AIRA_C._COR.pdf', '2025-10-01 01:32:12', NULL, 'Approved', 1),
(212, 80, 'Student ID', 'uploads/credentials/doc_88_1759282344_GARCIA__SHANE_AIRA_C._ID.pdf', '2025-10-01 01:32:24', NULL, 'Approved', 1),
(213, 84, 'Certificate of Grades', 'uploads/credentials/doc_92_1759282815_inbound202366562742740901.pdf', '2025-10-01 01:40:15', NULL, 'Approved', 1),
(214, 84, 'Certificate of Registration', 'uploads/credentials/doc_92_1759282870_inbound8012237264730488603.pdf', '2025-10-01 01:41:10', NULL, 'Approved', 1),
(215, 84, 'Student ID', 'uploads/credentials/doc_92_1759282925_inbound6228234625574236325.pdf', '2025-10-01 01:42:05', NULL, 'Approved', 1),
(217, 121, 'Certificate of Grades', 'uploads/credentials/doc_129_1759317973_Dimafelix__Johann_C._-_COG.pdf', '2025-10-01 11:26:13', NULL, 'Approved', 1),
(219, 121, 'Certificate of Registration', 'uploads/credentials/doc_129_1759318018_Dimafelix__Johann_C_-_COR.pdf', '2025-10-01 11:26:58', NULL, 'Approved', 1),
(220, 121, 'Student ID', 'uploads/credentials/doc_129_1759318040_Dimafelix__Johann_C._-_ID.pdf', '2025-10-01 11:27:20', NULL, 'Approved', 1),
(222, 133, 'Certificate of Registration', 'uploads/credentials/doc_141_1759325190_Esteron__Joshua_L._COR.pdf', '2025-10-01 13:26:30', NULL, 'Approved', 1),
(223, 133, 'Student ID', 'uploads/credentials/doc_141_1759325206_Esteron_Joshua_L._ID.pdf', '2025-10-01 13:26:46', NULL, 'Approved', 1),
(224, 114, 'Certificate of Grades', 'uploads/credentials/doc_122_1759332870_inbound6288161108273234144.pdf', '2025-10-01 15:34:30', NULL, 'Approved', 1),
(225, 114, 'Certificate of Registration', 'uploads/credentials/doc_122_1759332886_inbound3609856447520690487.pdf', '2025-10-01 15:34:46', NULL, 'Approved', 1),
(226, 114, 'Student ID', 'uploads/credentials/doc_122_1759332900_inbound8397807959886516720.pdf', '2025-10-01 15:35:00', NULL, 'Approved', 1),
(227, 119, 'Certificate of Grades', 'uploads/credentials/doc_127_1759333968_Robles__Precious_M._COG.pdf', '2025-10-01 15:52:48', NULL, 'Approved', 1),
(228, 56, 'Certificate of Grades', 'uploads/credentials/doc_64_1759380131_Lauron_Mary_Jane_A_COG.pdf', '2025-10-02 04:42:11', NULL, 'Approved', 1),
(229, 56, 'Certificate of Registration', 'uploads/credentials/doc_64_1759380147_Lauron__Mary_Jane_A_COR.pdf', '2025-10-02 04:42:27', NULL, 'Approved', 1),
(230, 56, 'Student ID', 'uploads/credentials/doc_64_1759380162_Lauron__Mary_Jane_A_ID.pdf', '2025-10-02 04:42:42', NULL, 'Approved', 1),
(231, 128, 'Student ID', 'uploads/credentials/doc_136_1759452154_Barsaga__John_Mark_B._ID.pdf', '2025-10-03 00:40:00', NULL, 'Approved', 1),
(235, 42, 'Certificate of Grades', 'uploads/credentials/doc_50_1759467150_inbound3518656801759310194.pdf', '2025-10-03 04:52:30', NULL, 'Approved', 1),
(236, 42, 'Certificate of Registration', 'uploads/credentials/doc_50_1759467200_inbound7675115956653327632.pdf', '2025-10-03 04:53:20', NULL, 'Approved', 1),
(237, 42, 'Student ID', 'uploads/credentials/doc_50_1759467210_inbound5668520217587125530.pdf', '2025-10-03 04:53:30', NULL, 'Approved', 1),
(238, 103, 'Student ID', 'uploads/credentials/doc_111_1759467332_inbound7131037406379982705.pdf', '2025-10-03 04:55:32', NULL, 'Approved', 1),
(239, 103, 'Certificate of Registration', 'uploads/credentials/doc_111_1759467661_inbound95699653194690855.pdf', '2025-10-03 05:01:01', NULL, 'Approved', 1),
(240, 37, 'Student ID', 'uploads/credentials/doc_45_1759467712_Aguilar__Christine_S._ID.pdf', '2025-10-03 05:01:51', NULL, 'Approved', 1),
(242, 37, 'Certificate of Registration', 'uploads/credentials/doc_45_1759467934_Aguilar__Christine_S._COR.pdf', '2025-10-03 05:05:34', NULL, 'Approved', 1),
(243, 142, 'Certificate of Grades', 'uploads/credentials/doc_150_1759467951_IMG_4032.jpeg', '2025-10-03 05:05:51', NULL, 'Rejected', 0),
(244, 142, 'Certificate of Registration', 'uploads/credentials/doc_150_1759467969_IMG_4033.jpeg', '2025-10-03 05:06:09', NULL, 'Approved', 1),
(245, 142, 'Student ID', 'uploads/credentials/doc_150_1759467981_8f4bdce93dfee09fd9cfd1c10e3eeb75.jpeg', '2025-10-03 05:06:21', NULL, 'Approved', 1),
(246, 85, 'Certificate of Grades', 'uploads/credentials/doc_93_1759469117_Gonzales__Bianca_M._COG.pdf', '2025-10-03 05:11:31', NULL, 'Approved', 1),
(247, 85, 'Certificate of Registration', 'uploads/credentials/doc_93_1759468322_inbound4626370955475051365.pdf', '2025-10-03 05:12:02', NULL, 'Approved', 1),
(248, 103, 'Certificate of Grades', 'uploads/credentials/doc_111_1759468363_inbound1343429220423485683.pdf', '2025-10-03 05:12:43', NULL, 'Approved', 1),
(249, 37, 'Certificate of Grades', 'uploads/credentials/doc_45_1759468399_Aguilar__Christine_S._COG.pdf', '2025-10-03 05:13:19', NULL, 'Approved', 1),
(250, 85, 'Student ID', 'uploads/credentials/doc_93_1759468725_Gonzales__Bianca_M._ID.pdf', '2025-10-03 05:18:45', NULL, 'Approved', 1),
(251, 60, 'Certificate of Grades', 'uploads/credentials/doc_68_1759468924_Gumapac__Pauline_Alexa_Marie_A_COG.pdf', '2025-10-03 05:22:04', NULL, 'Approved', 1),
(252, 60, 'Certificate of Registration', 'uploads/credentials/doc_68_1759468962_Gumapac__Pauline_Alexa_Marie_COR.pdf', '2025-10-03 05:22:42', NULL, 'Approved', 1),
(253, 60, 'Student ID', 'uploads/credentials/doc_68_1759468985_Gumapac__Pauline_Alexa_Marie_A_ID.pdf', '2025-10-03 05:23:05', NULL, 'Approved', 1),
(255, 113, 'Certificate of Grades', 'uploads/credentials/doc_121_1759469895_COG.pdf', '2025-10-03 05:38:15', NULL, 'Approved', 1),
(256, 113, 'Certificate of Registration', 'uploads/credentials/doc_121_1759469922_COR.pdf', '2025-10-03 05:38:42', NULL, 'Approved', 1),
(257, 113, 'Student ID', 'uploads/credentials/doc_121_1759469934_ID.pdf', '2025-10-03 05:38:54', NULL, 'Approved', 1),
(258, 134, 'Certificate of Grades', 'uploads/credentials/doc_142_1759475736_Iba__ez-BEEd2-COG_20251003_150911_0000.pdf', '2025-10-03 07:15:36', NULL, 'Approved', 1),
(259, 134, 'Certificate of Registration', 'uploads/credentials/doc_142_1759475868_Iba__ez-BEEd2-COR_20251003_151003_0000.pdf', '2025-10-03 07:17:48', NULL, 'Approved', 1),
(260, 134, 'Student ID', 'uploads/credentials/doc_142_1759475955_Iba__ez-BEEd2-School_I.D_20251003_150942_0000.pdf', '2025-10-03 07:19:15', NULL, 'Approved', 1),
(261, 58, 'Student ID', 'uploads/credentials/doc_66_1759492868_inbound4480452626464101072.jpg', '2025-10-03 11:45:53', NULL, 'Approved', 1),
(265, 92, 'Certificate of Registration', '', '2025-10-03 11:48:53', NULL, 'Rejected', 0),
(266, 100, 'Certificate of Grades', 'uploads/credentials/doc_108_1759492226_Zara__Rosalyn__R._COG.pdf', '2025-10-03 11:50:26', NULL, 'Approved', 1),
(267, 100, 'Certificate of Registration', 'uploads/credentials/doc_108_1759492402_ZARA__ROSALYN__RIVA_COR.pdf', '2025-10-03 11:53:22', NULL, 'Approved', 1),
(269, 100, 'Student ID', 'uploads/credentials/doc_108_1759492589_ZARA__ROSALYN__RIVA_ID.pdf', '2025-10-03 11:56:29', NULL, 'Approved', 1),
(275, 92, 'Certificate of Grades', 'uploads/credentials/doc_100_1759494227_BINGCANG_LANCE_ANDREI_I.____COG.pdf', '2025-10-03 12:23:46', NULL, 'Approved', 1),
(304, 92, 'Student ID', '', '2025-10-03 12:25:09', NULL, 'Rejected', 0),
(305, 151, 'Certificate of Grades', 'uploads/credentials/doc_159_1759495056_inbound234633955468104447.pdf', '2025-10-03 12:29:58', NULL, 'Approved', 1),
(308, 151, 'Certificate of Registration', 'uploads/credentials/doc_159_1759495034_inbound6961494322068192386.pdf', '2025-10-03 12:32:09', NULL, 'Approved', 1),
(309, 151, 'Student ID', 'uploads/credentials/doc_159_1759495001_inbound7207392283055098438.pdf', '2025-10-03 12:36:41', NULL, 'Approved', 1),
(312, 122, 'Certificate of Registration', 'uploads/credentials/doc_130_1759497003_inbound4015658143179895433.jpg', '2025-10-03 13:10:03', NULL, 'Approved', 1),
(313, 122, 'Student ID', 'uploads/credentials/doc_130_1759497018_inbound8159735093596808578.jpg', '2025-10-03 13:10:18', NULL, 'Approved', 1),
(314, 122, 'Certificate of Grades', 'uploads/credentials/doc_130_1759497037_inbound3763584285872356741.jpg', '2025-10-03 13:10:37', NULL, 'Approved', 1),
(317, 141, 'Certificate of Grades', 'uploads/credentials/doc_149_1759499060_Messenger_creation_F2DF6F28-4D14-47A8-855A-709A1DBFC2E5.jpeg', '2025-10-03 13:44:20', NULL, 'Rejected', 1),
(318, 141, 'Certificate of Registration', 'uploads/credentials/doc_149_1759499108_Messenger_creation_3882DD96-090A-4A3D-BC7C-81C0414B67D8.jpeg', '2025-10-03 13:45:08', NULL, 'Rejected', 1),
(319, 141, 'Student ID', 'uploads/credentials/doc_149_1759549690_IMG_20251004_103702.jpg', '2025-10-03 13:45:18', NULL, 'Rejected', 1),
(320, 123, 'Certificate of Registration', 'uploads/credentials/doc_131_1759503300_ALDAY_CARL_EMMANUEL_COR.pdf', '2025-10-03 14:55:00', NULL, 'Approved', 1),
(321, 123, 'Student ID', 'uploads/credentials/doc_131_1759503309_ALDAY_CARL_EMMANUEL_ID.pdf', '2025-10-03 14:55:09', NULL, 'Approved', 1),
(322, 123, 'Certificate of Grades', 'uploads/credentials/doc_131_1759503334_ALDAY_20CARL_20EMMANUEL_20M._20COG.pdf', '2025-10-03 14:55:34', NULL, 'Approved', 1),
(324, 64, 'Certificate of Grades', 'uploads/credentials/doc_72_1759761182_inbound6720092855309475826.pdf', '2025-10-06 14:33:02', NULL, 'Approved', 1),
(325, 64, 'Certificate of Registration', 'uploads/credentials/doc_72_1759761232_inbound8743029607837342364.pdf', '2025-10-06 14:33:52', NULL, 'Approved', 1),
(326, 64, 'Student ID', 'uploads/credentials/doc_72_1759761285_inbound3184601750683294371.pdf', '2025-10-06 14:34:45', NULL, 'Approved', 1),
(327, 50, 'Student ID', 'uploads/credentials/doc_58_1759826264_inbound4662194593006276092.pdf', '2025-10-07 08:37:44', NULL, 'Approved', 1),
(328, 50, 'Certificate of Registration', 'uploads/credentials/doc_58_1759826297_inbound8579949891033723237.pdf', '2025-10-07 08:38:17', NULL, 'Approved', 1),
(329, 50, 'Certificate of Grades', 'uploads/credentials/doc_58_1759826431_inbound7609417302414173121.pdf', '2025-10-07 08:40:31', NULL, 'Approved', 1),
(330, 61, 'Certificate of Registration', 'uploads/credentials/doc_69_1759975601_inbound3366530430019182270.pdf', '2025-10-09 02:06:41', NULL, 'Approved', 1),
(331, 61, 'Student ID', 'uploads/credentials/doc_69_1759975624_inbound7434010595885102758.pdf', '2025-10-09 02:07:04', NULL, 'Approved', 1),
(334, 162, 'Certificate of Grades', 'uploads/credentials/doc_170_1759994665_PALMA__HAZEL_ANNE_L.__COG.pdf', '2025-10-09 07:22:36', NULL, 'Approved', 1),
(335, 162, 'Certificate of Registration', 'uploads/credentials/doc_170_1759994680_PALMA__HAZEL_ANNE_L._COR.pdf', '2025-10-09 07:23:08', NULL, 'Approved', 1),
(338, 162, 'Student ID', 'uploads/credentials/doc_170_1759994702_Palma__Hazel_Anne_L._ID.pdf', '2025-10-09 07:25:02', NULL, 'Approved', 1),
(339, 120, 'Certificate of Grades', 'uploads/credentials/doc_128_1760273427_inbound9097240848543440473.pdf', '2025-10-12 12:50:27', NULL, 'Approved', 1),
(340, 120, 'Certificate of Registration', 'uploads/credentials/doc_128_1760273686_inbound204753751843147469.pdf', '2025-10-12 12:54:46', NULL, 'Approved', 1),
(341, 120, 'Student ID', 'uploads/credentials/doc_128_1760273714_inbound5755497103344786614.pdf', '2025-10-12 12:55:14', NULL, 'Approved', 1),
(342, 118, 'Certificate of Registration', 'uploads/credentials/doc_126_1760499774_COE.pdf', '2025-10-15 03:42:52', NULL, 'Approved', 1),
(344, 118, 'Certificate of Grades', 'uploads/credentials/doc_126_1760500006_COG.pdf', '2025-10-15 03:46:44', NULL, 'Approved', 1),
(349, 118, 'Student ID', 'uploads/credentials/doc_126_1760500571_ID.pdf', '2025-10-15 03:56:11', NULL, 'Approved', 1),
(350, 58, 'Certificate of Grades', 'uploads/credentials/doc_66_1760612280_inbound8228996171227761481.pdf', '2025-10-16 10:57:17', NULL, 'Approved', 1),
(351, 58, 'Certificate of Registration', 'uploads/credentials/doc_66_1760612259_inbound9089114516938976997.pdf', '2025-10-16 10:57:39', NULL, 'Approved', 1),
(353, 165, 'Student ID', '', '2025-10-19 12:43:37', NULL, 'Pending', 0),
(356, 165, 'Certificate of Grades', 'uploads/credentials/doc_173_1764518048_Rebyewer.pdf', '2025-10-19 12:43:46', NULL, 'Approved', 1),
(358, 73, 'Certificate of Grades', '', '2025-10-24 11:32:23', NULL, 'Rejected', 0),
(363, 73, 'Certificate of Registration', '', '2025-10-24 11:33:12', NULL, 'Rejected', 0),
(364, 137, 'Certificate of Grades', 'uploads/credentials/doc_145_1764124927_Image_to_PDF_20251124_13.16.17.pdf', '2025-11-24 05:21:48', NULL, 'Pending', 0),
(365, 137, 'Certificate of Registration', '', '2025-11-24 05:25:10', NULL, 'Pending', 0),
(366, 137, 'Student ID', '', '2025-11-24 05:25:21', NULL, 'Pending', 0),
(369, 45, 'Certificate of Grades', 'uploads/credentials/doc_53_1769555269_ARMT_1207L___Activity_7.pdf', '2025-12-02 05:39:26', NULL, 'Approved', 1),
(371, 45, 'Certificate of Registration', '', '2026-01-25 18:08:04', NULL, 'Pending', 0),
(372, 45, 'Student ID', '', '2026-01-25 18:08:18', NULL, 'Pending', 0),
(373, 45, 'LOA', 'uploads/credentials/doc_53_1769380841_04.08_01_OCILLOS__MARILYN_D..PDF', '2026-01-25 22:40:41', NULL, 'Pending', 0);

-- --------------------------------------------------------

--
-- Table structure for table `exported_credentials`
--

CREATE TABLE `exported_credentials` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_plain` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `exported_credentials`
--

INSERT INTO `exported_credentials` (`id`, `scholar_id`, `username`, `password_plain`, `created_at`) VALUES
(29, 32, '', '1ea8a14e', '2025-09-24 02:38:53'),
(30, 33, '', 'e3a5d050', '2025-09-24 02:46:07'),
(31, 34, '', 'f11c0b2f', '2025-09-25 09:40:15'),
(32, 35, '', '36e53a64', '2025-09-25 09:43:32'),
(33, 36, 'MaAngelica22-', '8a0eb206', '2025-09-30 04:41:38'),
(34, 37, 'augtine05', 'b385bedc', '2025-09-30 04:57:20'),
(35, 38, 'Nadine_30', '3bfbce9e', '2025-09-30 04:57:34'),
(36, 39, 'angge', 'fa0bf53b', '2025-09-30 04:57:53'),
(38, 41, 'Kent_27', '0ee9c548', '2025-09-30 05:01:26'),
(39, 42, '', '4cff7cca', '2025-09-30 05:01:40'),
(40, 43, '', '1f571a6c', '2025-09-30 05:01:49'),
(41, 44, '', '4a2c7d36', '2025-09-30 05:08:09'),
(42, 45, '', '2e4929d3', '2025-09-30 05:49:50'),
(44, 47, '', '3371e568', '2025-09-30 06:11:17'),
(45, 48, '', '4fc3bc1f', '2025-09-30 06:11:27'),
(47, 50, '', '94cdfb01', '2025-09-30 06:11:46'),
(48, 51, '', '16019fa3', '2025-09-30 06:11:54'),
(49, 52, '', '6d0a836c', '2025-09-30 06:12:05'),
(50, 53, '', 'f8e02396', '2025-09-30 06:12:16'),
(51, 54, '', '06e5a884', '2025-09-30 06:12:46'),
(52, 55, '', 'd671e660', '2025-09-30 06:12:53'),
(53, 56, '', '41d4c53d', '2025-09-30 06:13:02'),
(54, 57, '', '34766bad', '2025-09-30 06:13:11'),
(55, 58, '', '9fe97a66', '2025-09-30 06:13:21'),
(56, 59, '', 'b58031f9', '2025-09-30 06:13:28'),
(57, 60, '', '325f1dab', '2025-09-30 06:13:35'),
(58, 61, '', '03c80927', '2025-09-30 06:13:42'),
(59, 62, '', '62a85e3d', '2025-09-30 06:13:49'),
(60, 63, '', '0e69fad3', '2025-09-30 06:13:57'),
(61, 64, '', '92f18065', '2025-09-30 06:14:04'),
(62, 65, '', '0c03050c', '2025-09-30 06:14:10'),
(63, 66, '', 'fc59f90c', '2025-09-30 06:14:18'),
(64, 67, '', '159f2c55', '2025-09-30 06:14:41'),
(65, 68, '', '905c8778', '2025-09-30 06:14:49'),
(66, 69, '', '8a6505dc', '2025-09-30 06:14:56'),
(67, 70, '', '7ef58a5e', '2025-09-30 06:15:17'),
(68, 71, '', 'e0b62d94', '2025-09-30 06:15:32'),
(69, 72, '', '0eef855f', '2025-09-30 06:15:41'),
(70, 73, '', '004114fe', '2025-09-30 06:16:13'),
(71, 74, '', '6af9d41a', '2025-09-30 06:16:23'),
(72, 75, '', '7e8c27da', '2025-09-30 06:16:32'),
(73, 76, '', 'fa76e79b', '2025-09-30 06:16:42'),
(74, 77, '', '8adf96ac', '2025-09-30 06:20:55'),
(75, 78, '', '12b07e07', '2025-09-30 06:21:02'),
(76, 79, '', '5c058346', '2025-09-30 06:21:09'),
(77, 80, '', 'aa337a67', '2025-09-30 06:21:15'),
(78, 81, '', '14abb912', '2025-09-30 06:21:33'),
(79, 82, '', '2518c2a9', '2025-09-30 06:21:40'),
(80, 83, '', '58d26a6b', '2025-09-30 06:21:47'),
(81, 84, '', 'ca2f4a6c', '2025-09-30 06:21:54'),
(82, 85, '', '79884f2b', '2025-09-30 06:22:00'),
(83, 86, '', 'c3a13d7f', '2025-09-30 06:22:07'),
(84, 87, '', 'c641dd49', '2025-09-30 06:22:13'),
(85, 88, '', '85221752', '2025-09-30 06:22:20'),
(86, 89, '', 'becc901c', '2025-09-30 06:22:33'),
(87, 90, '', 'e1cf0491', '2025-09-30 06:22:40'),
(88, 91, '', '270f30bf', '2025-09-30 06:22:47'),
(89, 92, '', '08f75018', '2025-09-30 06:22:54'),
(90, 93, '', '59ee7f05', '2025-09-30 06:23:03'),
(91, 94, '', '565051ce', '2025-09-30 06:23:09'),
(92, 95, '', 'a3713a77', '2025-09-30 06:23:14'),
(93, 96, '', 'c5dc1ca3', '2025-09-30 06:23:21'),
(94, 97, '', 'b8799a45', '2025-09-30 06:23:46'),
(95, 98, '', '36bc95b7', '2025-09-30 06:23:51'),
(96, 99, '', 'cb12d1d0', '2025-09-30 06:25:28'),
(97, 100, '', 'e3dfb886', '2025-09-30 06:26:22'),
(98, 101, '', 'd5b6c26a', '2025-09-30 06:38:42'),
(100, 103, '', '2b813428', '2025-09-30 07:03:26'),
(101, 104, '', '0f886b61', '2025-09-30 07:03:38'),
(102, 105, '', '71bc4c5d', '2025-09-30 07:03:48'),
(103, 106, '', 'd5599e1d', '2025-09-30 07:09:49'),
(104, 107, '', '0d48c18a', '2025-09-30 07:11:33'),
(105, 108, '', '860bfbf7', '2025-09-30 09:13:10'),
(106, 109, '', '2679f615', '2025-09-30 09:13:49'),
(107, 110, '', '64ee0283', '2025-09-30 10:29:43'),
(108, 111, '', '9d9f7735', '2025-09-30 10:30:01'),
(109, 112, '', 'a68d1fc1', '2025-09-30 13:48:15'),
(110, 113, '', '762874f3', '2025-09-30 13:48:23'),
(111, 114, '', '2bfe35ee', '2025-09-30 13:48:32'),
(112, 115, '', '1b6ef230', '2025-09-30 13:48:48'),
(113, 116, '', 'ec710619', '2025-09-30 13:49:22'),
(114, 117, '', 'e2dae47c', '2025-09-30 13:49:36'),
(115, 118, '', '5c5910ee', '2025-09-30 13:49:43'),
(116, 119, '', '9c6726d1', '2025-09-30 13:49:55'),
(117, 120, '', 'ba7919c9', '2025-09-30 13:51:08'),
(118, 121, '', 'fb56f079', '2025-09-30 13:51:18'),
(119, 122, '', '35aee0ad', '2025-09-30 13:51:26'),
(120, 123, '', '84c75c00', '2025-09-30 13:51:57'),
(121, 124, '', '4909dd6c', '2025-09-30 13:52:04'),
(122, 125, '', '030bcf38', '2025-09-30 13:52:15'),
(123, 126, '', '9209e5ec', '2025-09-30 13:52:24'),
(124, 127, '', 'de8f79c5', '2025-09-30 13:53:02'),
(125, 128, '', '1bfeaae2', '2025-09-30 13:53:10'),
(126, 129, '', '20fbaad3', '2025-09-30 13:53:26'),
(128, 131, '', '3a8fda3d', '2025-09-30 13:53:48'),
(129, 132, '', '8790c883', '2025-09-30 13:54:01'),
(130, 133, '', 'b4d8de09', '2025-09-30 13:54:08'),
(131, 134, '', 'c58ab289', '2025-09-30 13:54:27'),
(132, 135, '', '30680a8d', '2025-09-30 13:54:36'),
(133, 136, '', 'eb4e2d95', '2025-09-30 13:54:42'),
(134, 137, '', 'ddae0ba2', '2025-09-30 13:54:50'),
(135, 138, '', '4ff1548c', '2025-09-30 14:13:28'),
(136, 139, '', '403e3683', '2025-09-30 14:18:35'),
(138, 141, '', '3024a39c', '2025-09-30 14:18:56'),
(139, 142, '', '034c09e2', '2025-09-30 14:27:48'),
(140, 143, '', '1cc627b7', '2025-09-30 14:27:54'),
(141, 144, '', 'f5034cc6', '2025-09-30 14:32:51'),
(142, 145, '', '5ba79ae0', '2025-09-30 14:33:39'),
(144, 147, '', '5fc50ca7', '2025-10-01 00:19:05'),
(145, 148, '', '4e2e64f9', '2025-10-01 03:57:52'),
(146, 149, '', 'e884e3a1', '2025-10-01 03:58:09'),
(147, 150, '', '31776ea1', '2025-10-01 03:58:21'),
(148, 151, '', '56cab865', '2025-10-03 10:45:36'),
(149, 152, '', '7965af56', '2025-10-03 11:08:57'),
(150, 153, 'angela dellamas', 'fa5b01db', '2025-10-03 11:09:09'),
(151, 154, '', 'd6fab4d6', '2025-10-03 11:25:33'),
(152, 155, '', 'd58862f4', '2025-10-03 13:33:44'),
(153, 156, '', '426f6db1', '2025-10-03 13:35:46'),
(154, 157, '', '4148a35e', '2025-10-03 13:36:02'),
(155, 158, '', '7d220786', '2025-10-03 13:39:58'),
(156, 159, '', '617c0a1b', '2025-10-05 14:22:44'),
(157, 160, '', '4c178189', '2025-10-05 14:23:52'),
(158, 161, '', '58d94fcf', '2025-10-05 14:29:46'),
(159, 162, '', '5dd9347b', '2025-10-09 05:29:49'),
(160, 163, 'Marrie', '148f6f55', '2025-10-09 06:07:42'),
(161, 164, 'Rillan', '540fdf34', '2025-10-09 06:20:40'),
(162, 165, '', '98ae855e', '2025-10-19 12:30:06'),
(163, 166, '', 'd7dd71ea', '2025-12-02 02:20:03'),
(164, 167, '', '6042feaf', '2025-12-03 05:25:47');

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `requirements`
--

CREATE TABLE `requirements` (
  `id` int(11) NOT NULL,
  `tag` varchar(50) NOT NULL,
  `document_type` varchar(255) NOT NULL,
  `deadline` date DEFAULT NULL,
  `allowed_types` varchar(100) NOT NULL DEFAULT 'pdf',
  `is_permanent` tinyint(1) DEFAULT 0,
  `is_required` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `requirements`
--

INSERT INTO `requirements` (`id`, `tag`, `document_type`, `deadline`, `allowed_types`, `is_permanent`, `is_required`) VALUES
(5, 'COG', 'Certificate of Grades', '2025-10-11', 'pdf,jpg,png', 1, 1),
(6, 'COR', 'Certificate of Registration', '2025-10-11', 'pdf,jpg,png', 1, 1),
(7, 'ID', 'Student ID', '2025-10-11', 'pdf,jpg,png', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `scholars`
--

CREATE TABLE `scholars` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `first_name` varchar(60) NOT NULL DEFAULT '',
  `middle_name` varchar(60) DEFAULT NULL,
  `last_name` varchar(60) NOT NULL DEFAULT '',
  `extended_name` varchar(120) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `course` varchar(255) NOT NULL,
  `year_level` int(11) NOT NULL,
  `scholarship_type` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_pic` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `sex` varchar(10) DEFAULT NULL,
  `units` int(11) DEFAULT NULL,
  `tuition_fee` decimal(10,2) DEFAULT NULL,
  `batch` decimal(10,2) DEFAULT NULL,
  `status` enum('enrolled','not_enrolled','graduated') DEFAULT 'enrolled',
  `physical_copy_confirmed` tinyint(1) DEFAULT 0,
  `liquidated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholars`
--

INSERT INTO `scholars` (`id`, `user_id`, `first_name`, `middle_name`, `last_name`, `extended_name`, `email`, `course`, `year_level`, `scholarship_type`, `created_at`, `profile_pic`, `phone`, `sex`, `units`, `tuition_fee`, `batch`, `status`, `physical_copy_confirmed`, `liquidated`) VALUES
(32, 40, 'Ken Laurence', 'De Jesus', 'Estilo', NULL, 'laurenceestilo4@gmail.com', 'BSHM', 4, 'Listahanan', '2025-09-24 02:38:53', NULL, '09917745994', 'Male', 21, 19237.34, 2.00, 'enrolled', 0, 0),
(33, 41, 'Rod Christopher', 'Chavez', 'Estipona', NULL, 'estiponarod24@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-24 02:46:07', NULL, '09264578944', 'Male', 19, 19454.26, 1.00, 'enrolled', 0, 0),
(34, 42, 'Kurt Aces', 'Asaula', 'Alcañeses', NULL, 'kurtacealcaneses@gmail.com', 'BSCS', 2, 'Listahanan', '2025-09-25 09:40:15', 'Uploads/profile_pics/profile_68db6289491ff6.91700854.png', '09660855226', 'Male', 27, 25414.58, 1.00, 'enrolled', 0, 0),
(35, 43, 'Arvin James', 'Azucena', 'Barangas', NULL, 'arvinjamesbarangas07@gmail.com', 'BSHM', 3, 'TDP', '2025-09-25 09:43:32', NULL, '09984958689', 'Male', 22, 19795.00, 8.10, 'enrolled', 0, 0),
(36, 44, 'Ma Angelica', 'Alvarez', 'Abellera', NULL, 'maangelicaabellera10@gmail.com', 'BSA', 4, 'Listahanan', '2025-09-30 04:41:38', NULL, '09632085673', 'Female', 31, 24672.00, 2.00, 'enrolled', 0, 0),
(37, 45, 'Christine', 'Siscon', 'Aguilar', NULL, 'augtine05@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 04:57:20', NULL, '09685528104', 'Female', 31, 24672.74, 2.00, 'not_enrolled', 0, 0),
(38, 46, 'Nadine', 'Lagui', 'Alday', NULL, 'nadinealday04@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 04:57:34', NULL, '09263181380', 'Female', 22, 19645.88, 1.00, 'not_enrolled', 0, 0),
(39, 47, 'Panganiban', 'Baldrias', 'Angelyn', NULL, 'angelynpanganiban668@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-30 04:57:53', NULL, '09664408307', 'Female', 19, 19454.26, 1.00, 'not_enrolled', 0, 0),
(41, 49, 'Kent Daniel', 'Barit', 'Balaquiot', NULL, 'balaquiotkentdaniel@gmail.com', 'BSCS', 2, 'Listahanan', '2025-09-30 05:01:26', NULL, '09937014913', 'Male', 27, 25414.58, 1.00, 'not_enrolled', 0, 0),
(42, 50, 'Landher', 'Javier', 'Baon', NULL, 'landherbaon@gmail.com', 'BSCS', 2, 'Listahanan', '2025-09-30 05:01:40', NULL, '09977380859', 'Male', 27, 25.00, 1.00, 'enrolled', 0, 0),
(43, 51, 'Percimae', 'A.', 'Base', NULL, 'percimaeb@gmail.com', 'BSTM', 4, 'Listahanan', '2025-09-30 05:01:49', NULL, '09946061021', 'Female', 18, 17411.72, 2.00, 'enrolled', 0, 0),
(44, 52, 'Crisalyn', 'Cullo', 'Agquiz', NULL, 'agquizc04@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 05:08:09', NULL, '09926298530', 'Female', 20, 19278.80, 1.00, 'enrolled', 0, 0),
(45, 53, 'Errol', 'Lasheras', 'Alday', NULL, 'errolalday25@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-30 05:49:50', 'Uploads/profile_pics/profile_692c6c4759b031.06657136.jpg', '09556622460', 'Male', 19, 19454.26, 2.00, 'enrolled', 0, 0),
(47, 55, 'Maiky', 'L.', 'Padillo', NULL, 'maikypadillo3@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 06:11:17', NULL, '09365553023', 'Female', 22, 19645.88, 1.00, 'enrolled', 0, 0),
(48, 56, 'John Michael L', NULL, 'Hernandez', NULL, 'hjm822580@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 06:11:27', NULL, '09653985217', 'Male', 31, 24672.74, 1.00, 'enrolled', 0, 0),
(50, 58, 'Danica', 'Tolentino', 'Dacillo', NULL, 'danicadacillo97@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 06:11:46', NULL, '09613972634', 'Female', 22, 19645.88, 1.00, 'enrolled', 0, 0),
(51, 59, 'Charmaine', 'Briz', 'Salgarino', NULL, 'meynnn847@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:11:54', NULL, '09910612552', 'Female', 38, 24864.20, 1.00, 'enrolled', 0, 0),
(52, 60, 'Ericka', 'Duman', 'Prigo', NULL, 'prigoericka@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 06:12:05', NULL, '09067033958', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(53, 61, 'Jamaica', 'Punzalan', 'Ondo', NULL, 'jamaicapunzalanondo@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:12:16', NULL, '09554101140', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(54, 62, 'Justine', 'Manalo', 'Luceriano', NULL, 'justineluceriano107@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 06:12:46', NULL, '09757555537', 'Male', 25, 22000.00, 1.00, 'enrolled', 0, 0),
(55, 63, 'Jazmin', 'De Jesus', 'Limoico', NULL, 'jazminlimoico@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:12:53', NULL, '09912505291', 'Female', 30, 24864.20, 1.00, 'enrolled', 0, 0),
(56, 64, 'Mary Jane Lauron', 'Arenza', 'Lauron', NULL, 'lauronmaryjane92005@gmail.com', 'BSTM', 2, 'Listahanan', '2025-09-30 06:13:02', NULL, '09058606211', 'Female', 22, 22395.00, 1.00, 'enrolled', 0, 0),
(57, 65, 'Kaye Airelle', 'Bibas', 'Labustro', NULL, 'kayeairellel@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 06:13:11', NULL, '09811714455', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(58, 66, 'Dacillo', 'Esgusrra', 'Janelle', NULL, 'janelledacillo06@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 06:13:21', NULL, '09550592220', 'Female', 24, 13404.96, 1.00, 'enrolled', 0, 0),
(59, 67, 'Franz Mariella', 'Julongbayan', 'Ilao', NULL, 'franzmariellailao@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 06:13:28', NULL, '09473688676', 'Female', 20, 19278.00, 1.00, 'enrolled', 0, 0),
(60, 68, 'Pauline Alexa Marie', 'Alipustain', 'Gumapac', NULL, 'gumapac.paulinealexamarie03@gmail.com', 'BSTM', 2, 'Listahanan', '2025-09-30 06:13:35', NULL, '09266220638', 'Female', 22, 20395.00, 1.00, 'enrolled', 0, 0),
(61, 69, 'Verna', 'M.', 'Diongco', NULL, 'vernadiongcco@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 06:13:42', NULL, '09657534734', 'Female', 20, 19278.80, 1.00, 'enrolled', 0, 0),
(62, 70, 'Nikka Mae', 'B.', 'De Castro', NULL, 'nikkamaedecastro12@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:13:49', NULL, '09366713413', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(63, 71, 'Angelica', 'Intino', 'Castelo', NULL, 'angelicaintinocastelo@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:13:57', NULL, '09911262327', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(64, 72, 'Janice', 'Francisco', 'Butiong', NULL, 'janicebutiong278@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 06:14:04', NULL, '09632085678', 'Female', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(65, 73, 'Althea', 'Sacro', 'Bituin', NULL, 'bituinalthea768@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 06:14:10', NULL, '09457132158', 'Female', 20, 19278.80, 1.00, 'enrolled', 0, 0),
(66, 74, 'Renlyn', 'Isar', 'Bautista', 'N/A', 'renlyn597@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:14:18', NULL, '09270565243', 'Female', 30, 24864.00, 1.00, 'enrolled', 0, 0),
(67, 75, 'Ivan Carl', 'Butiong', 'Bagunas', 'N/A', 'bagunasivancarl13@gmail.com', 'BSCS', 2, 'Listahanan', '2025-09-30 06:14:41', NULL, '09511003874', 'Male', 27, 25000.00, 1.00, 'enrolled', 0, 0),
(68, 76, 'Angeline', 'Alimorong', 'Diaz', NULL, 'angelinediaz9@gmail.com', 'BSED', 3, 'Listahanan', '2025-09-30 06:14:49', NULL, '09980521873', 'Female', 28, 22997.12, 1.00, 'enrolled', 0, 0),
(69, 77, 'Ryan Joseph', 'De Lunas', 'Vinuya', NULL, 'vinuyaryanjoseph11@gmail.com', 'BSHM', 4, 'Listahanan', '2025-09-30 06:14:56', NULL, '09125792898', 'Male', 21, 19237.34, 2.00, 'enrolled', 0, 0),
(70, 78, 'Myla', 'Cabello', 'De Roxas', NULL, 'deroxasmylac02@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 06:15:17', NULL, '09542904696', 'Female', 21, 19087.34, 2.00, 'enrolled', 0, 0),
(71, 79, 'Arabela Grace', 'Bulan', 'Rivera', NULL, 'riveraarabelagrace2@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-30 06:15:32', NULL, '09161819960', 'Female', 19, 19454.00, 2.00, 'enrolled', 0, 0),
(72, 80, 'Ciryl Angelica', 'Barrameda', 'Oco', NULL, 'caoco387@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 06:15:41', NULL, '09551747966', 'Female', 21, 19087.34, 2.00, 'enrolled', 0, 0),
(73, 81, 'Lyka Vaness', 'Aganan', 'Manlapaz', NULL, 'vanessmanlapaz@gmail.com', 'BSBA', 4, 'Listahanan', '2025-09-30 06:16:13', NULL, '09458513143', 'Female', 21, 19087.34, 2.00, 'enrolled', 0, 0),
(74, 82, 'Jesca Collyn', NULL, 'Hernandez', NULL, 'hernandezjescacollyn@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-30 06:16:23', NULL, '09676806642', 'Female', 19, 19454.26, 2.00, 'enrolled', 0, 0),
(75, 83, 'Daniella Erica', 'Gonzalez', 'De Guzman', NULL, 'danielladeguzman92@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 06:16:32', 'Uploads/profile_pics/profile_68db98644c7bc9.78922333.jpg', '09360968183', 'Female', 18, 17411.72, 2.00, 'enrolled', 0, 0),
(76, 84, 'Mark Anthony', 'Manalo', 'Beadoy', NULL, 'Markbeadoy20@gmail.com', 'BSHM', 4, 'Listahanan', '2025-09-30 06:16:42', NULL, '09297713607', 'Male', 21, 19237.34, 2.00, 'enrolled', 0, 0),
(77, 85, 'Daniela Reine', 'Fronda', 'Bautista', NULL, 'dreinebautista@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:20:55', NULL, '09694852532', 'Female', 30, 16756.20, 1.00, 'enrolled', 0, 0),
(78, 86, 'Michael', 'Ramos', 'Triviño', NULL, 'mikelramos789@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 06:21:02', NULL, '09751386732', 'Male', 20, 18270.80, 1.00, 'enrolled', 0, 0),
(79, 87, 'Denver', NULL, 'Factuar', 'Ortega', 'denverfactuar77@gmail.com', 'BEED', 4, 'Listahanan', '2025-09-30 06:21:09', NULL, '09270425586', 'Male', 21, 11729.00, 1.00, 'enrolled', 0, 0),
(80, 88, 'Shane Aira', 'Catapang', 'Garcia', NULL, 'shaneairagarcia25@gmail.com', 'BSED', 3, 'Listahanan', '2025-09-30 06:21:15', NULL, '09168606374', 'Female', 28, 22997.00, 2.00, 'enrolled', 0, 0),
(81, 89, 'Ma. Andrei', 'De Roxas', 'Macalalad', NULL, 'andreimacalalad22@gmail.com', 'BEED', 4, 'TDP', '2025-09-30 06:21:33', NULL, '09553317785', 'Female', 21, 19087.00, 3.00, 'enrolled', 0, 0),
(82, 90, 'Ishi Nicole', 'De Roxas', 'Bauyon', '-', 'ishinicoleb17@gmail.com', 'BSTM', 3, 'TDP', '2025-09-30 06:21:40', NULL, '09452564577', 'Female', 25, 21321.50, 8.10, 'enrolled', 0, 0),
(83, 91, 'Ryzza Mae', 'Confiado', 'Ilagan', NULL, 'ryzzamaeconfiado@gmail.com', 'BSED', 3, 'TDP', '2025-09-30 06:21:47', NULL, '09774744055', 'Female', 28, 22997.12, 8.10, 'enrolled', 0, 0),
(84, 92, 'Ranquel', 'Barrientos', 'Maningat', NULL, 'maningatranquel55@gmail.com', 'BSED', 3, 'TDP', '2025-09-30 06:21:54', NULL, '09677404763', 'Female', 28, 22997.12, 8.10, 'enrolled', 0, 0),
(85, 93, 'Bianca', 'Mendoza', 'Gonzales', NULL, 'gonzales.mbianca@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 06:22:00', NULL, '09936681710', 'Female', 33, 25789.82, 1.00, 'enrolled', 0, 0),
(86, 94, 'Kim Eduard', 'Garcia', 'Iratay', NULL, 'iratayeduard@gmail.com', 'BSBA', 4, 'Listahanan', '2025-09-30 06:22:07', NULL, '09265655986', 'Male', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(87, 95, 'Alexandra', 'Lopez', 'Manalo', 'N/A', 'manalopezalexandra@gmail.com', 'BEED', 4, 'Listahanan', '2025-09-30 06:22:13', NULL, '09757529530', 'Female', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(88, 96, 'Marie Thony', 'Samatra', 'Pacleb', NULL, 'mariethony29@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 06:22:20', NULL, '09977339644', 'Female', 24, 20762.96, 1.00, 'enrolled', 0, 0),
(89, 97, 'Stephanie', 'Mayuga', 'Catibog', NULL, 'stephaniecatibog03@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:22:33', NULL, '09205602571', 'Female', 30, 16756.20, 2.00, 'enrolled', 0, 0),
(90, 98, 'Kyla Marie', 'Concepcion', 'Medina', NULL, 'kylamariemedina96@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:22:40', NULL, '09919199126', 'Female', 24, 21.51, 2.00, 'enrolled', 0, 0),
(91, 99, 'Kyla', 'Piliin', 'Alday', NULL, 'kaylapiliin@gmail.com', 'BSHM', 3, 'Listahanan', '2025-09-30 06:22:47', NULL, '09350040414', 'Female', 22, 19725.88, 2.00, 'enrolled', 0, 0),
(92, 100, 'Lance Andrei', 'Ilao', 'Bingcang', NULL, 'lancebingcang546@gmail.com', 'BEED', 3, 'Listahanan', '2025-09-30 06:22:54', NULL, '09915445828', 'Male', 25, 21321.50, 2.00, 'enrolled', 0, 0),
(93, 101, 'Angela', 'Manalo', 'Chica', NULL, 'angelamanalochica@gmail.com', 'BSTM', 3, 'Listahanan', '2025-09-30 06:23:03', NULL, '09261704911', 'Female', 25, 21321.50, 2.00, 'enrolled', 0, 0),
(94, 102, 'Pamela', 'Catapang', 'De Jesus', NULL, 'pamcatdj@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 06:23:09', NULL, '09533843571', 'Female', 31, 24672.74, 2.00, 'enrolled', 0, 0),
(95, 103, 'Kevin Jurgen', 'Gerolao', 'Krause', NULL, 'kevgerolao@gmail.com', 'BSCS', 3, 'Listahanan', '2025-09-30 06:23:14', NULL, '09531120500', 'Male', 19, 20938.26, 2.00, 'enrolled', 0, 0),
(96, 104, 'Kevin Jurgen', 'Gerolao', 'Krause', NULL, 'kebingerolao@gmail.com', 'BSCS', 3, 'Listahanan', '2025-09-30 06:23:21', NULL, '09397183859', 'Male', 19, 20938.25, 2.00, 'enrolled', 0, 0),
(97, 105, 'Cherry Mae', NULL, 'Torres', NULL, 'torrescherrymae97@gmail.com', 'BEED', 3, 'Listahanan', '2025-09-30 06:23:46', NULL, '09163320606', 'Female', 25, 21321.50, 2.00, 'enrolled', 0, 0),
(98, 106, 'Jhon Paulo', 'Rivera', 'Palanas', NULL, 'palanasp99@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 06:23:51', NULL, '09067028701', 'Male', 22, 19645.00, 2.00, 'enrolled', 0, 0),
(99, 107, 'Candelaria', 'Loganio', 'Mary Joy', NULL, 'cccandelariamaryjoy12@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 06:25:28', NULL, '09356905024', 'Female', 22, 19645.44, 2.00, 'enrolled', 0, 0),
(100, 108, 'Rosalyn', 'Riva', 'Zara', NULL, 'zararosalyn12@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 06:26:22', NULL, '09754062285', 'Female', 30, 24864.20, 1.00, 'enrolled', 0, 0),
(101, 109, 'Angel Ann', 'Elmission', 'De Guzman', NULL, 'angelannde84@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 06:38:42', NULL, '09653347139', 'Female', 24, 13404.96, 1.00, 'enrolled', 0, 0),
(103, 111, 'Maria Isabel', 'Valencia', 'Baldoz', NULL, 'mariaisabelbaldoz@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 07:03:26', NULL, '09941907288', 'Female', 31, 24672.74, 2.00, 'enrolled', 0, 0),
(104, 112, 'Antonette', 'Acosta', 'Castillo', NULL, 'castilloantonette13@gmail.com', 'BSA', 2, 'Listahanan', '2025-09-30 07:03:38', NULL, '09197716426', 'Female', 29, 24305.66, 1.00, 'enrolled', 0, 0),
(105, 113, 'Kristine Joy', 'Villamin', 'Punzalan', 'N/A', 'kristinejoypunzalan1@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 07:03:48', NULL, '09067019152', 'Female', 24, 21512.96, 1.00, 'enrolled', 0, 0),
(106, 114, 'Elaiza Mae', 'Mendoza', 'Roxas', NULL, 'elaizamaeroxas05@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 07:09:49', NULL, '09955155485', 'Female', 31, 24672.74, 1.00, 'enrolled', 0, 0),
(107, 115, 'Pauline Anne', 'Aninao', 'Aytona', NULL, 'paulineanneaytona78@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 07:11:33', NULL, '09369540157', 'Female', 31, 24672.74, 1.00, 'enrolled', 0, 0),
(108, 116, 'Marilou', 'De La Cruz', 'Andino', NULL, 'camillemisal789@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 09:13:10', NULL, '09931809042', 'Female', 20, 18278.80, 1.00, 'enrolled', 0, 0),
(109, 117, 'Angela', 'Tadaya', 'Tolentino', NULL, 'tolentinoangela191@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 09:13:49', NULL, '09657732512', 'Female', 30, 24864.00, 1.00, 'enrolled', 0, 0),
(110, 118, 'Joan', NULL, 'Ongy', NULL, 'wifeymemejoan@gmail.com', 'BSCS', 4, 'Listahanan', '2025-09-30 10:29:43', NULL, '09954600734', 'Female', 19, 10612.26, 1.00, 'enrolled', 0, 0),
(111, 119, 'Joel', NULL, 'Contreras', NULL, 'contrerasjoel485@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 10:30:01', NULL, '09701633541', 'Male', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(112, 120, 'Vincent Jay', 'Acebuche', 'Entico', 'N/A', 'vincentjayentico8@gmail.com', 'BSCS', 3, 'Listahanan', '2025-09-30 13:48:15', NULL, '09098231011', 'Male', 19, 20938.26, 1.00, 'enrolled', 0, 0),
(113, 121, 'Kim', 'Señorez', 'Valle', NULL, 'vkim98935@gmail.com', 'BSA', 2, 'Listahanan', '2025-09-30 13:48:23', NULL, '09514353567', 'Female', 29, 24305.66, 1.00, 'enrolled', 0, 0),
(114, 122, 'Roldan', 'Montealegre', 'Malaluan', NULL, 'roldanmalaluan44@gmail.com', 'BSCS', 2, 'Listahanan', '2025-09-30 13:48:32', NULL, '09974650290', 'Male', 27, 25414.58, 1.00, 'enrolled', 0, 0),
(115, 123, 'Sharina', 'Mapald', 'Blanza', NULL, 'sharinablanza@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 13:48:48', NULL, '09481473488', 'Female', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(116, 124, 'Vianah', 'Mayven Salazar', 'Casais', NULL, 'vianahmayvenscasais31@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 13:49:22', NULL, '09263486187', 'Female', 24, 13404.96, 1.00, 'enrolled', 0, 0),
(117, 125, 'Lexy Ann', 'De Castro', 'Lauresta', NULL, 'lexyannlauresta3@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 13:49:36', NULL, '09050491760', 'Female', 26, 22221.00, 1.00, 'enrolled', 0, 0),
(118, 126, 'Mikyla Rose', 'Bernal', 'Lopez', NULL, 'lopezmikyla79@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 13:49:43', NULL, '09975274956', 'Female', 30, 24864.00, 1.00, 'enrolled', 0, 0),
(119, 127, 'Precious', NULL, 'Robles', NULL, 'roblespreciousm335@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 13:49:55', NULL, '09977957160', 'Female', 22, 19645.88, 1.00, 'enrolled', 0, 0),
(120, 128, 'Angelica', 'Gayanes', 'Sacdalan', 'N/A', 'sacdalanangelica25@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 13:51:08', NULL, '09658916708', 'Female', 24, 2746.96, 1.00, 'enrolled', 0, 0),
(121, 129, 'Johann', 'Competente', 'Dimafelix', NULL, 'jhnndimf@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 13:51:18', NULL, '09619568272', 'Male', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(122, 130, 'Shiela', 'Dimailig', 'De Leon', NULL, 'deleon.shiela31@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 13:51:26', NULL, '09066359785', 'Female', 22, 19645.88, 1.00, 'enrolled', 0, 0),
(123, 131, 'Carl Emmanuel', 'Macalalad', 'Alday', NULL, 'aldaycarlemmanuel0405@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 13:51:57', NULL, '09539201315', 'Male', 22, 19645.88, 2.00, 'enrolled', 0, 0),
(124, 132, 'Kyla Janelle', 'Piñano', 'Caldo', NULL, 'kylajanelle794@gmail.com', 'BSHM', 3, 'Listahanan', '2025-09-30 13:52:04', NULL, '09519298314', 'Female', 22, 19.00, 2.00, 'enrolled', 0, 0),
(125, 133, 'Bernadette', 'Bulan', 'De Lunas', NULL, 'bernadettedelunas18@gmail.com', 'BSED', 4, 'Listahanan', '2025-09-30 13:52:15', NULL, '09657583903', 'Female', 18, 17411.72, 2.00, 'enrolled', 0, 0),
(126, 134, 'Yahnzel', 'Maminta', 'Garcia', 'None', 'yahnzelgarcia1@gmail.com', 'BSTM', 4, 'Listahanan', '2025-09-30 13:52:24', NULL, '09392600767', 'Female', 18, 17411.72, 2.00, 'enrolled', 0, 0),
(127, 135, 'Charlize Joan', 'Ramos', 'Basilio', NULL, 'basiliocharlizejoan@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 13:53:02', NULL, '09167969513', 'Female', 23, 12846.42, 1.00, 'enrolled', 0, 0),
(128, 136, 'John Mark', 'Bugtong', 'Barsaga', NULL, 'barsagajm400@gmail.com', 'BSBA', 2, 'Listahanan', '2025-09-30 13:53:10', NULL, '09687697415', 'Male', 20, 19278.00, 1.00, 'enrolled', 0, 0),
(129, 137, 'Princes Mary', 'Sarne', 'Del Pilar', NULL, 'princesmarysarne@gmail.com', 'BSED', 2, 'Listahanan', '2025-09-30 13:53:26', NULL, '09629877148', 'Female', 30, 24864.20, 1.00, 'enrolled', 0, 0),
(131, 139, 'Erich', 'Cabungcal', 'Bautista', NULL, 'bautistaerich364@gmail.com', 'BEED', 3, 'Listahanan', '2025-09-30 13:53:48', NULL, '09939957684', 'Female', 25, 21321.50, 1.00, 'enrolled', 0, 0),
(132, 140, 'Zara', 'Joefer', 'Gubi', NULL, 'jgzara29@gmail.com', 'BSHM', 4, 'Listahanan', '2025-09-30 13:54:01', 'Uploads/profile_pics/profile_68dc7a94b07bc8.32057632.jpg', '09919491178', 'Male', 21, 19237.34, 2.00, 'enrolled', 0, 0),
(133, 141, 'Joshua', 'Landicho', 'Esteron', NULL, 'joshuaesteron07@gmail.com', 'BSHM', 4, 'TDP', '2025-09-30 13:54:08', NULL, '09636796102', 'Male', 21, 19237.00, 3.00, 'enrolled', 0, 0),
(134, 142, 'Ma. Princess Iyah', 'Aldover', 'Ibañez', NULL, 'princessiyah021@gmail.com', 'BEED', 2, 'Listahanan', '2025-09-30 13:54:27', NULL, '09657530879', 'Female', 24, 13404.96, 1.00, 'enrolled', 0, 0),
(135, 143, 'Maria Cristine', 'Gayoso', 'Camacho', NULL, 'mcristine143@gmail.com', 'BSBA', 4, 'Listahanan', '2025-09-30 13:54:36', NULL, '09365980949', 'Female', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(136, 144, 'Rica Mae', 'Bajar', 'Bathan', NULL, 'ricamaebathan18@gmail.com', 'BSBA', 4, 'Listahanan', '2025-09-30 13:54:42', NULL, '09501708410', 'Female', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(137, 145, 'Tricia', 'Mendoza', 'Mendoza', NULL, 'triciamendoza34347@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 13:54:50', NULL, '09510479915', 'Female', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(138, 146, 'Abby Rose', 'Pineda', 'Billen', 'N/A', 'billenabbyrose@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 14:13:28', NULL, '09121233591', 'Female', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(139, 147, 'Mark Joros', 'Atajar', 'Magnaye', NULL, 'jorosmagnaye21@gmail.com', 'BSTM', 4, 'Listahanan', '2025-09-30 14:18:35', NULL, '09919414924', 'Male', 18, 17411.42, 1.00, 'enrolled', 0, 0),
(141, 149, 'Jerzi Mielle', 'Legaspi', 'Mirano', NULL, 'milanimirano26@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 14:18:56', NULL, '09187406644', 'Female', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(142, 150, 'John Hendrick', 'Banawa', 'Pastoral', NULL, 'johnhendrickpastoral@gmail.com', 'BSHM', 2, 'Listahanan', '2025-09-30 14:27:48', NULL, '09974873698', 'Male', 25, 22221.00, 1.00, 'enrolled', 0, 0),
(143, 151, 'John Kristoffe', NULL, 'De Leon', NULL, 'kristoffedeleon@gmail.com', 'BSA', 3, 'Listahanan', '2025-09-30 14:27:54', NULL, '09910807508', 'Male', 31, 24672.00, 1.00, 'enrolled', 0, 0),
(144, 152, 'Junior', 'M', 'Banaguas', NULL, 'juniorbanaguas@gmail.com', 'BSED', 3, 'Listahanan', '2025-09-30 14:32:51', NULL, '09095599570', 'Male', 28, 23000.00, 1.00, 'enrolled', 0, 0),
(145, 153, 'Dulongbinte', 'Ajero', 'Emerson', NULL, 'dulongbinteemerson@gmail.com', 'BSBA', 3, 'Listahanan', '2025-09-30 14:33:39', NULL, '09618263434', 'Male', 22, 19645.00, 2.00, 'enrolled', 0, 0),
(147, 155, 'John Rafael', NULL, 'Marata', NULL, 'johnrafaelmarata56@gmail.com', 'BSCS', 4, 'Listahanan', '2025-10-01 00:19:05', NULL, '09304927858', 'Male', 19, 19454.19, 1.00, 'enrolled', 0, 0),
(148, 156, 'Irish Joy', 'Tadas', 'Lara', NULL, 'larairishjoy@gmail.com', 'BSED', 4, 'Listahanan', '2025-10-01 03:57:52', NULL, '09055909083', 'Female', 24, 20762.96, 1.00, 'enrolled', 0, 0),
(149, 157, 'Kathleen Joy', NULL, 'Noche', NULL, 'kathleenjoynoche18@gmail.com', 'BSTM', 4, 'Listahanan', '2025-10-01 03:58:09', NULL, '09215939774', 'Female', 18, 17411.72, 1.00, 'enrolled', 0, 0),
(150, 158, 'Mark Jeffre', 'Deguzman', 'Concepcion', 'None', 'markjeffreyconcepcion6@gmail.com', 'BSHM', 2, 'Listahanan', '2025-10-01 03:58:21', NULL, '09089443497', 'Male', 25, 22221.50, 1.00, 'enrolled', 0, 0),
(151, 159, 'Christy', 'Benosa', 'Albis', 'None', 'albischristy44@gmail.com', 'BSTM', 2, 'Listahanan', '2025-10-03 10:45:36', NULL, '09939954924', 'Female', 22, 20395.00, 1.00, 'enrolled', 0, 0),
(152, 160, 'Jefferson', 'Ornum', 'Panganiban', NULL, 'jayjaypanganiban67@gmail.com', 'BSCS', 4, 'Listahanan', '2025-10-03 11:08:57', NULL, '09850401850', 'Male', 20, 19000.00, 1.00, 'enrolled', 0, 0),
(153, 161, 'Angela', 'De Llamas', 'Paking', NULL, 'angeladellamaspaking104@gmail.com', 'BSTM', 2, 'Listahanan', '2025-10-03 11:09:09', NULL, '09622989717', 'Female', 22, 20395.00, 1.00, 'enrolled', 0, 0),
(154, 162, 'Racquel Anne', 'Peña', 'Viarino', NULL, 'racquelvlog49@gmail.com', 'BSHM', 2, 'Listahanan', '2025-10-03 11:25:33', NULL, '09928242893', 'Female', 25, 22221.00, 1.00, 'enrolled', 0, 0),
(155, 163, 'Mark Gilbert', 'Marcuap', 'Bautista', NULL, 'bautistamarkgilbert@gmail.com', 'BSBA', 3, 'Listahanan', '2025-10-03 13:33:44', NULL, '09530529586', 'Male', 22, 19645.88, 1.00, 'enrolled', 0, 0),
(156, 164, 'Maria Maricel', 'Casal', 'Dumas', NULL, 'mariceldumas1999@gmail.com', 'BSA', 3, 'Listahanan', '2025-10-03 13:35:46', NULL, '09108030598', 'Female', 31, 24674.72, 1.00, 'enrolled', 0, 0),
(157, 165, 'Marvin', NULL, 'Ilao', NULL, 'marvin.ilao09@gmail.com', 'BSED', 4, 'TDP', '2025-10-03 13:36:02', NULL, '09677956648', 'Male', 24, 20762.00, 3.00, 'enrolled', 0, 0),
(158, 166, 'Jayna Mae', 'Carenan', 'Juson', NULL, 'juson.jayna@gmail.com', 'BEED', 4, 'Listahanan', '2025-10-03 13:39:58', NULL, '09977941259', 'Female', 21, 19087.34, 1.00, 'enrolled', 0, 0),
(159, 167, 'Erika Mae', 'D.', 'Fronda', NULL, 'erikamaefronda541@gmail.com', 'BSCS', 4, 'Listahanan', '2025-10-05 14:22:44', NULL, '09086353248', 'Female', 19, 19454.26, 2.00, 'enrolled', 0, 0),
(160, 168, 'Cristine May', 'Ynares', 'Bautista', 'None', 'bakerycristinemay@gmail.com', 'BSED', 4, 'Listahanan', '2025-10-05 14:23:52', NULL, '09462523319', 'Female', 24, 13404.00, 1.00, 'enrolled', 0, 0),
(161, 169, 'Jean', 'Dela Cruz', 'Orolfo', NULL, 'orolfojean212@gmail.com', 'BSED', 4, 'Listahanan', '2025-10-05 14:29:46', NULL, '09653284044', 'Female', 24, 20762.96, 1.00, 'enrolled', 0, 0),
(162, 170, 'Hazel Anne', 'Lauzon', 'Palma', NULL, 'zelannelzn@gmail.com', 'BSA', 2, 'Listahanan', '2025-10-09 05:29:49', NULL, '09553902610', 'Female', 29, 24305.66, 1.00, 'enrolled', 0, 0),
(163, 171, 'Mariecar', 'Corrado', 'Macalindong', NULL, 'sajjadmarie07@gmail.com', 'BSCS', 4, 'TDP', '2025-10-09 06:07:42', NULL, '09560190736', 'Female', 19, 19454.26, 1.00, 'not_enrolled', 0, 0),
(164, 172, 'Princess Rillan', 'Landicho', 'Rillorta', NULL, 'rillortaosu@gmail.com', 'BSCS', 4, 'Listahanan', '2025-10-09 06:20:40', NULL, '09668865638', 'Female', 23, 19424.26, 1.00, 'enrolled', 0, 0),
(165, 173, 'Karlov', NULL, 'Kamarov', NULL, 'bodaceia2403@gmail.com', 'BSHM', 1, 'Listahanan', '2025-10-19 12:30:06', NULL, '09091234567', 'Male', 14, 18000.15, 1.00, 'enrolled', 0, 0),
(166, 175, 'Micko', 'Ternida', 'Soriano', NULL, 'sorianomicko97@gmail.com', 'BSCS', 1, 'TES', '2025-12-02 02:20:03', NULL, '09951334425', 'Male', 23, 21455.00, 1.00, 'enrolled', 0, 0),
(167, 176, 'Jair Cyruz', 'Alday', 'Sacdalan', NULL, 'jaircyruz123@gmail.com', 'BSHM', 1, 'TDP', '2025-12-03 05:25:47', NULL, '09950226413', 'Male', 11, 10000000.00, 15.00, 'enrolled', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `scholar_applications`
--

CREATE TABLE `scholar_applications` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) NOT NULL,
  `extended_name` varchar(120) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `sex` enum('Male','Female','Other') NOT NULL,
  `units` int(11) NOT NULL,
  `tuition_fee` decimal(10,2) NOT NULL,
  `course` varchar(100) NOT NULL,
  `year_level` varchar(50) NOT NULL,
  `scholarship_type` varchar(100) NOT NULL,
  `batch` decimal(5,2) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholar_applications`
--

INSERT INTO `scholar_applications` (`id`, `username`, `first_name`, `middle_name`, `last_name`, `extended_name`, `email`, `phone`, `sex`, `units`, `tuition_fee`, `course`, `year_level`, `scholarship_type`, `batch`, `status`, `created_at`) VALUES
(186, 'pervo', 'Pervo', NULL, 'Abad', NULL, 'pervo8765@gmail.com', '09335525855', 'Male', 24, 10000.00, 'BSCS', '4th Year', 'TES', 12.50, 'pending', '2026-03-24 01:16:07'),
(187, 'bell', 'Bell', NULL, 'Roche', NULL, 'bellroche55@gmail.com', '09552228552', 'Female', 24, 100000.00, 'BSBA', '4th Year', 'TDP', 13.00, 'pending', '2026-03-24 01:18:26');

-- --------------------------------------------------------

--
-- Table structure for table `scholar_credentials`
--

CREATE TABLE `scholar_credentials` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `document_type` enum('COG','COR','ID') NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','approved','rejected','reupload_requested') NOT NULL DEFAULT 'pending',
  `expires_at` date DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewer_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `physical_copy_confirmed` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `scholar_enrollments`
--

CREATE TABLE `scholar_enrollments` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `school_year_id` int(11) NOT NULL,
  `enrolled_1st` tinyint(1) DEFAULT 0,
  `enrolled_2nd` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scholar_enrollments`
--

INSERT INTO `scholar_enrollments` (`id`, `scholar_id`, `school_year_id`, `enrolled_1st`, `enrolled_2nd`, `notes`, `updated_at`) VALUES
(25, 32, 5, 1, 0, NULL, '2025-09-24 02:49:00'),
(26, 33, 5, 1, 0, NULL, '2025-09-24 02:49:00'),
(27, 34, 5, 1, 0, NULL, '2025-09-25 09:41:55'),
(28, 35, 5, 1, 0, NULL, '2025-09-25 09:43:54'),
(29, 36, 5, 1, 0, 'Manual edit enrollment', '2025-09-30 05:03:39'),
(30, 37, 5, 1, 0, 'Manual edit enrollment', '2025-09-30 05:03:39'),
(31, 38, 5, 1, 0, 'Manual edit enrollment', '2025-09-30 05:03:13'),
(32, 39, 5, 1, 0, 'Manual edit enrollment', '2025-09-30 05:03:13'),
(33, 41, 5, 1, 0, 'Manual edit enrollment', '2025-09-30 05:03:13'),
(34, 42, 5, 1, 0, NULL, '2025-09-30 05:03:13'),
(35, 44, 5, 1, 0, NULL, '2025-09-30 05:43:57'),
(36, 43, 5, 1, 0, NULL, '2025-09-30 05:51:28'),
(37, 67, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(39, 66, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(40, 76, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(41, 65, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(42, 64, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(43, 63, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(44, 50, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(45, 62, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(46, 75, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(47, 70, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(48, 68, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(49, 61, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(51, 60, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(52, 74, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(53, 48, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(54, 59, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(55, 58, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(56, 57, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(57, 56, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(58, 55, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(59, 54, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(60, 73, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(61, 72, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(62, 53, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(63, 47, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(64, 52, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(65, 71, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(66, 51, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(67, 69, 5, 1, 0, NULL, '2025-09-30 06:19:16'),
(68, 91, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(69, 77, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(70, 82, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(71, 92, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(72, 93, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(73, 94, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(74, 79, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(75, 80, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(76, 85, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(77, 83, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(78, 86, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(79, 96, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(80, 95, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(81, 81, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(82, 87, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(83, 84, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(84, 99, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(85, 90, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(86, 88, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(87, 98, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(88, 97, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(89, 100, 5, 1, 0, NULL, '2025-09-30 06:28:26'),
(90, 89, 5, 1, 0, NULL, '2025-09-30 06:28:49'),
(91, 78, 5, 1, 0, NULL, '2025-09-30 06:28:49'),
(92, 101, 5, 1, 0, NULL, '2025-09-30 06:39:56'),
(93, 103, 5, 1, 0, NULL, '2025-09-30 07:04:31'),
(94, 104, 5, 1, 0, NULL, '2025-09-30 07:04:31'),
(95, 105, 5, 1, 0, NULL, '2025-09-30 07:04:31'),
(96, 106, 5, 1, 0, NULL, '2025-09-30 07:10:17'),
(97, 107, 5, 1, 0, NULL, '2025-09-30 07:11:51'),
(98, 108, 5, 1, 0, NULL, '2025-09-30 10:32:27'),
(99, 111, 5, 1, 0, NULL, '2025-09-30 10:32:27'),
(100, 110, 5, 1, 0, NULL, '2025-09-30 10:32:27'),
(101, 109, 5, 1, 0, NULL, '2025-09-30 10:32:27'),
(102, 45, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(103, 115, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(104, 116, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(105, 122, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(106, 121, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(107, 112, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(108, 117, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(109, 118, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(110, 114, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(111, 119, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(112, 120, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(113, 113, 5, 1, 0, NULL, '2025-09-30 13:51:41'),
(114, 123, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(116, 128, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(117, 127, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(118, 136, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(119, 131, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(120, 124, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(121, 135, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(122, 125, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(123, 129, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(124, 133, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(125, 126, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(126, 132, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(127, 134, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(128, 137, 5, 1, 0, NULL, '2025-09-30 13:55:28'),
(129, 138, 5, 1, 0, NULL, '2025-09-30 14:14:01'),
(131, 139, 5, 1, 0, NULL, '2025-09-30 14:19:08'),
(132, 141, 5, 1, 0, NULL, '2025-09-30 14:19:08'),
(133, 142, 5, 1, 0, NULL, '2025-09-30 14:28:59'),
(134, 144, 5, 1, 0, NULL, '2025-09-30 14:33:53'),
(135, 143, 5, 1, 0, NULL, '2025-09-30 14:33:53'),
(136, 145, 5, 1, 0, NULL, '2025-09-30 14:33:53'),
(138, 150, 5, 1, 0, NULL, '2025-10-01 03:58:43'),
(139, 148, 5, 1, 0, NULL, '2025-10-01 03:58:43'),
(140, 147, 5, 1, 0, NULL, '2025-10-01 03:58:43'),
(141, 149, 5, 1, 0, NULL, '2025-10-01 03:58:43'),
(142, 153, 5, 1, 0, NULL, '2025-10-03 11:09:54'),
(143, 152, 5, 1, 0, NULL, '2025-10-03 11:09:54'),
(144, 151, 5, 1, 0, NULL, '2025-10-03 11:09:54'),
(145, 158, 5, 1, 0, NULL, '2025-10-03 13:43:47'),
(146, 156, 5, 1, 0, NULL, '2025-10-03 13:43:47'),
(147, 155, 5, 1, 0, NULL, '2025-10-03 13:43:47'),
(148, 154, 5, 1, 0, NULL, '2025-10-03 13:43:47'),
(149, 157, 5, 1, 0, NULL, '2025-10-03 13:43:47'),
(150, 159, 5, 1, 0, NULL, '2025-10-05 14:24:22'),
(151, 160, 5, 1, 0, NULL, '2025-10-05 14:24:22'),
(152, 161, 5, 1, 0, NULL, '2025-10-05 14:30:23'),
(153, 162, 5, 1, 0, NULL, '2025-10-09 05:30:43'),
(154, 164, 5, 1, 0, NULL, '2025-10-09 06:25:26'),
(155, 165, 5, 1, 0, NULL, '2025-10-19 12:41:37'),
(156, 163, 5, 1, 0, 'Manual edit enrollment', '2025-10-24 05:01:12'),
(157, 167, 5, 0, 1, NULL, '2025-12-03 05:27:13');

-- --------------------------------------------------------

--
-- Table structure for table `school_years`
--

CREATE TABLE `school_years` (
  `id` int(11) NOT NULL,
  `label` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `is_current` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school_years`
--

INSERT INTO `school_years` (`id`, `label`, `start_date`, `end_date`, `is_current`, `created_at`) VALUES
(5, '2025-2026', '2025-09-09', '2026-09-09', 1, '2025-09-09 04:04:35');

-- --------------------------------------------------------

--
-- Table structure for table `special_cases`
--

CREATE TABLE `special_cases` (
  `id` int(11) NOT NULL,
  `scholar_id` int(11) NOT NULL,
  `case_type` varchar(50) NOT NULL,
  `required` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `special_cases`
--

INSERT INTO `special_cases` (`id`, `scholar_id`, `case_type`, `required`, `created_at`) VALUES
(7, 101, 'LOA', 0, '2025-12-01 07:03:13');

-- --------------------------------------------------------

--
-- Table structure for table `tes_applicants`
--

CREATE TABLE `tes_applicants` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `given_name` varchar(100) NOT NULL,
  `extension_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(100) NOT NULL,
  `sex` enum('Male','Female') NOT NULL,
  `birthdate` date NOT NULL,
  `complete_program_name` varchar(200) NOT NULL,
  `year_level` int(11) NOT NULL,
  `father_last_name` varchar(100) NOT NULL,
  `father_given_name` varchar(100) NOT NULL,
  `father_middle_name` varchar(100) NOT NULL,
  `mother_last_name` varchar(100) NOT NULL,
  `mother_given_name` varchar(100) NOT NULL,
  `mother_middle_name` varchar(100) NOT NULL,
  `street_barangay` varchar(200) NOT NULL,
  `zip_code` varchar(6) NOT NULL,
  `disability` varchar(100) DEFAULT NULL,
  `contact_number` varchar(11) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `indigenous_people_group` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','scholar') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `main_admin` tinyint(1) DEFAULT 0,
  `profile_pic` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `created_at`, `main_admin`, `profile_pic`) VALUES
(2, 'errol', '$2y$10$uwK0DFBkHRjVplWjR1zIU.mHWVzvV6xbeLJTLc9aqtW/ojnbmvRt2', 'admin', '2025-05-10 13:17:33', 0, 'uploads/profile_pics/profile_68c27d4e1ce0d6.43785079.jpg'),
(4, 'errolman', '$2y$10$TYwvcQyJt3MQ2W0L5jeY/OsbLdeyTQRLbmDh6974.WxNW3nIhhwsG', 'admin', '2025-05-18 16:08:24', 1, NULL),
(15, 'wensel', '$2y$10$r7iYsdIA7uZZYFacrF4GRO2we2NSPBgkvvlDewsvnm3F2F/3ySfMm', 'scholar', '2025-08-26 15:07:57', 0, NULL),
(16, 'waow', '$2y$10$r7iYsdIA7uZZYFacrF4GRO2we2NSPBgkvvlDewsvnm3F2F/3ySfMm', 'scholar', '2025-08-26 15:08:47', 0, NULL),
(17, 'wep', '$2y$10$r7iYsdIA7uZZYFacrF4GRO2we2NSPBgkvvlDewsvnm3F2F/3ySfMm', 'scholar', '2025-08-28 03:26:47', 0, NULL),
(18, 'wok', '$2y$10$r7iYsdIA7uZZYFacrF4GRO2we2NSPBgkvvlDewsvnm3F2F/3ySfMm', 'scholar', '2025-09-02 13:27:50', 0, NULL),
(19, 'bano', '$2y$10$tEpuS8R4AK13UZya4bYF6uVW6jZTG8H3aEC7Rvaa7QzEaKMmB1sbq', 'scholar', '2025-09-09 00:25:32', 0, NULL),
(20, 'carlos', '$2y$10$EGC8p6.YS.V/Vg43uwbdl.N3WIifyLnJSYY0h/uTSjsfG5ooRzKKm', 'admin', '2025-09-09 03:21:20', 0, NULL),
(21, 'Marie', '$2y$10$TaiI1TrO16Ub65jqqFQj0e6fURI8MMeU7QxywsVDaIKEGJBdoa3Wm', 'admin', '2025-09-09 03:21:42', 0, NULL),
(29, 'nikolai', '$2y$10$X4Fr.O4VJDf2QsAZze93OeqwNHrcpWDAi4SEoZxSaEoacpAZXlqqy', 'scholar', '2025-09-09 11:34:28', 0, NULL),
(39, 'Erlie', '$2y$10$NcZpPkr35OUzFSa2AKTf2OELNVj7/961NFshznQ8e1hZw61NQNPIC', 'admin', '2025-09-21 13:18:51', 0, NULL),
(40, 'Ken Laurence Estilo', '$2y$10$yrIPEo/I9baSbn9yeKd4g.nQsY5qx86TUSQ8v9XXxXrv/9qbvF.0G', 'scholar', '2025-09-24 02:38:53', 0, NULL),
(41, 'Rod_21', '$2y$10$CZOnhzCFScNI4D6G4cIHG.pYvXRRS9MHAB7ZUBmOHw3fF1AfUYHnK', 'scholar', '2025-09-24 02:46:07', 0, NULL),
(42, 'uokurt_13', '$2y$10$8T1ag/WXBnOPIAgJ8JAKIu6h1gU9CThHsihVBKExUYJmxJO94pzPa', 'scholar', '2025-09-25 09:40:15', 0, NULL),
(43, 'Arvin', '$2y$10$jS7cKFQZ9FiN84dNKYF3JONqAacUW00zZNdtwkE8A8R7wRAQH88O2', 'scholar', '2025-09-25 09:43:32', 0, NULL),
(44, 'MaAngelica22-', '$2y$10$PYhAiYX0GnfIkDDqxtIl0e9DS21ps3e/o/ZVY5MSnV2Xhj5hAiKNm', 'scholar', '2025-09-30 04:41:38', 0, NULL),
(45, 'augtine05', '$2y$10$zvhA1yiO7pK83RI0pOFz/eCpwut5ZmqSWGw6DM4ecqE1WaG3PIHwO', 'scholar', '2025-09-30 04:57:20', 0, NULL),
(46, 'Nadine_30', '$2y$10$Yh7Vg6awR3emz.085lemteaSy1hmbBzxP9jNIqMWoWNKUdgHOCVEy', 'scholar', '2025-09-30 04:57:34', 0, NULL),
(47, 'angge', '$2y$10$nRHqWi0WgYBZBLtow368HOMbcmVXJ0a8EwJdtGQC/7u1reToSdZIG', 'scholar', '2025-09-30 04:57:53', 0, NULL),
(49, 'Kent_27', '$2y$10$CKvvV0Pwc4RpjiybO/QgheTgByKWsRugxz6LbYpkCcNtQXPvAXyzu', 'scholar', '2025-09-30 05:01:26', 0, NULL),
(50, 'landherbaon@gmail.com', '$2y$10$IINaX5CQk//T3rsK6r3wmukJDD21O6MskHbXpClehZe.zcmhX4Ciu', 'scholar', '2025-09-30 05:01:40', 0, NULL),
(51, 'perci@27', '$2y$10$3ARVseKQFzfhcM9.HgzYxupT8DRm30vC2mInkHTOquA4ASuIfZ83q', 'scholar', '2025-09-30 05:01:49', 0, NULL),
(52, 'Crisalyn_04', '$2y$10$7DlvIxpR6xZTc8/6ovaO6e.fl17X4FsTTNL5nBcEfsnZt9D7V9S7u', 'scholar', '2025-09-30 05:08:09', 0, NULL),
(53, 'Errol_025', '$2y$10$Dnek0uRvS1SE2uv9HD7Z3OHJSPL/u.oNiHYd0C.o6WljImmp1WiAC', 'scholar', '2025-09-30 05:49:50', 0, NULL),
(55, 'MaikyPadillo', '$2y$10$rSbsdzKdH2QlJrk0VD.D8e9QgzTHXRueRmrfrCLyrhDDsn3kIf3Xu', 'scholar', '2025-09-30 06:11:17', 0, NULL),
(56, 'JM', '$2y$10$ggaToXouEOrEl8GA2M4Nd.UJLrKQ1tWa5glwmAw9SNd4ubbEUwwPG', 'scholar', '2025-09-30 06:11:27', 0, NULL),
(58, 'msdnctd', '$2y$10$kH6w2kioCkX6P.51CdhIReS5J/9.vGyQ/S1zewbk1xVdfRY3HmaX2', 'scholar', '2025-09-30 06:11:46', 0, NULL),
(59, 'meynnn847@gmail.com', '$2y$10$xyT..aM9v.1VzSGKHcqgqOEcUOfe1rzyLUoCFyzTZC6qQFHZAvyFO', 'scholar', '2025-09-30 06:11:54', 0, NULL),
(60, 'Prigoericka', '$2y$10$j0de3OwoSB1SigTMs/Xt6uCXEydVBKyDw4T7e6pKKaklE/SX0Onry', 'scholar', '2025-09-30 06:12:05', 0, NULL),
(61, 'Jamaica Ondo', '$2y$10$B.2i3LWDEgRfhpfA.p1YCuJVdXix5YlTkwCqFm15EqOPSRT3E18m2', 'scholar', '2025-09-30 06:12:16', 0, NULL),
(62, 'Justine luceriano', '$2y$10$2PhFR0zgf2h2ihjcJU.j.u05KN7CuTt6dImK4bm3aLmNLiLiOzly2', 'scholar', '2025-09-30 06:12:46', 0, NULL),
(63, 'Jazmin Limoico', '$2y$10$PQtKOe3H1RXObjtodXz55u7i7Im9Bef7A3R8ME2nDhNpqTgxCTqky', 'scholar', '2025-09-30 06:12:53', 0, NULL),
(64, 'mary jane lauron', '$2y$10$bz5l9uusa.dJt/Cy8I0rFuschE05DnKRgRhp3.K4AzlAs5fVZkyLK', 'scholar', '2025-09-30 06:13:02', 0, NULL),
(65, 'kayeairellel@gmail.com', '$2y$10$n8YLp0yrhTKalMEB3eI51OJIpZ4mEtVoX6q6w59DjYa2KR5LDPKeO', 'scholar', '2025-09-30 06:13:11', 0, NULL),
(66, 'Janelle_dacillo', '$2y$10$y22qQhZF6GUAK2g6CLWG.umUxGu93lpUfSqZhksdHbRelns/sJcS6', 'scholar', '2025-09-30 06:13:21', 0, NULL),
(67, 'Mariella', '$2y$10$sfbpw8gHtGeabBtbMIBIMuHHoxXCu7QJVpyqJDEX5Uqt.voCilbq2', 'scholar', '2025-09-30 06:13:28', 0, NULL),
(68, 'pauline', '$2y$10$uhYKb5ips8N6/wG6eqHnVOxitJllQTyyt5K4OPjIJ9uiF9lrWk4Ee', 'scholar', '2025-09-30 06:13:35', 0, NULL),
(69, 'Verna', '$2y$10$DDvG6cPn7nqF3wLtbkm8QuDF3P4ZuUvFxmW1K3w5w7TjgkvC2k7A6', 'scholar', '2025-09-30 06:13:42', 0, NULL),
(70, 'Nikka Mae De Castro', '$2y$10$O9XCbz3BfJGJqD1stq1jVOViFoJc5bMHaU2iA22jfpA8L/H4dHfYS', 'scholar', '2025-09-30 06:13:49', 0, NULL),
(71, 'angelica', '$2y$10$GWtP/P10fLNmSUd0200WuuhJN8nHPyJ3VtwWrBFgnykMyQ4WzhYfG', 'scholar', '2025-09-30 06:13:57', 0, NULL),
(72, 'janice_06', '$2y$10$KOr/BPG.sYIP0A/DCMSCKuaRrvTBZ5zy0y6/H6FTKsMfajpU7G6bS', 'scholar', '2025-09-30 06:14:04', 0, NULL),
(73, 'Althea_123', '$2y$10$DX0pCY.TsY2sQNvzVBTGkuu3YPxT2Hdq/Jfgm3t4xoOsX91bpQCdS', 'scholar', '2025-09-30 06:14:10', 0, NULL),
(74, 'renlyn', '$2y$10$t9vIhTqustwoxqk5pgBoseWxvOFZz8Zb5BVH9CtPxs6LQ6DqHup.G', 'scholar', '2025-09-30 06:14:18', 0, NULL),
(75, 'Ivan Carl', '$2y$10$.Anj8reXKGyRGEn25syLtOcXFkOHHxm56kp/YVow9gNodmf6B.Uw.', 'scholar', '2025-09-30 06:14:41', 0, NULL),
(76, 'ANGELINEDIAZ', '$2y$10$a.20rNmdzLOyIPKo50/veuLxlo.k77Px9p.LEMl11yudTpRILp/Eu', 'scholar', '2025-09-30 06:14:49', 0, NULL),
(77, 'Ryan_14', '$2y$10$t7gbumZ2JBAF4wZMpyjngOtp9hG6/SdKZwjFQ6cV./i3w4M5FmCcG', 'scholar', '2025-09-30 06:14:56', 0, NULL),
(78, 'Myla De Roxas', '$2y$10$jrUmTE/tIWFpaa92s1037eFD6YjtXQvmsAj2ZGY.4RlHQQAZaexxO', 'scholar', '2025-09-30 06:15:17', 0, NULL),
(79, 'Rivera_29', '$2y$10$ULoTgMvLrH.zJEttLi.9gOpGMThw2ivvIGqKnUSlqP2B5uMZ/dPjm', 'scholar', '2025-09-30 06:15:32', 0, NULL),
(80, 'caoco387', '$2y$10$5dfSOZveIyKAp73Cd9wrZekJANmMLCq1b5L5/v.hJi/3ObjmyggKW', 'scholar', '2025-09-30 06:15:41', 0, NULL),
(81, 'Lyka Vaness Manlapaz', '$2y$10$IBbbrVXiD8Dizm90hwhn6O2.dU.wh3sIn9rlbnsncOoofVAj5iR9W', 'scholar', '2025-09-30 06:16:13', 0, NULL),
(82, 'Jesca Collyn', '$2y$10$cb5OmyH4YqhVIkgDn8y0AuW0lCCJQ9Cw1zDWKDDLHneftQEcfIyF6', 'scholar', '2025-09-30 06:16:23', 0, NULL),
(83, 'Daniella De Guzman', '$2y$10$kZ67PbSjfwgp2dDPCspz3.Az.OHYIEsB2OuNCxB08NhSpljxl0h3i', 'scholar', '2025-09-30 06:16:32', 0, NULL),
(84, 'Mark', '$2y$10$slLSTW.yCaI.DUKyDx88RuxscbdauuLlcDttv1dl5ZdCqdh8EK4SS', 'scholar', '2025-09-30 06:16:42', 0, NULL),
(85, 'dreinebautista', '$2y$10$TRdZH5gSpZ3OZxP5NbBA9e7vKvvHs.HwnO6oyeJGzxAH70l5Kb0tO', 'scholar', '2025-09-30 06:20:55', 0, NULL),
(86, 'Mikel_123', '$2y$10$x5sRNdUPP0RMoUx0ZHENlu7uATQgGSUfsFulC.kNNpqd/SONTOp62', 'scholar', '2025-09-30 06:21:02', 0, NULL),
(87, 'denver', '$2y$10$Rb9AFvsRQzdun/LhHQxOLOahERVv7/dT0zzJ2JxUpT5bLHiV1rMYa', 'scholar', '2025-09-30 06:21:09', 0, NULL),
(88, 'ShaneAira', '$2y$10$Ko/scpiJ0MksbcuW6Z7UNeoonlhhkGKmFjA6EzRXGF2dSP0rDO.6m', 'scholar', '2025-09-30 06:21:15', 0, NULL),
(89, 'Andrei_922', '$2y$10$Y/26eBnw353l0zjr4KRwo.nBjbB7MONGuXSSgwhHPxzTvKlYdHtGC', 'scholar', '2025-09-30 06:21:33', 0, NULL),
(90, 'ishinicoleee', '$2y$10$fGI5AyMW/oeslxGr7cTG2.PciuVzOgbBIXBLI5mg4MnDm6T8CVWXm', 'scholar', '2025-09-30 06:21:40', 0, NULL),
(91, 'Ryzza Mae C. Ilagan', '$2y$10$PyicuTghMW.47ETmUljeUencc4dsatKqQMFdCbe8fkS3NWk7Dy8LW', 'scholar', '2025-09-30 06:21:47', 0, NULL),
(92, 'Ranquel Maningat', '$2y$10$N8HLJuDg470j3g7ZrIag2.ZUFiPLuyLKLyR9hSpTFddr/tL4xHcr.', 'scholar', '2025-09-30 06:21:54', 0, NULL),
(93, 'arbianch01', '$2y$10$qZEZ9rmTM8r.CMAWif.RsulCZ7C/otv7O93rydcnB60/1vpse7VG2', 'scholar', '2025-09-30 06:22:00', 0, NULL),
(94, 'Eduard kim', '$2y$10$899Ys04qOI8rvhYa0n7qIe1QVXb.2MKKBgF8RoQ742hpfRB7HsbuC', 'scholar', '2025-09-30 06:22:07', 0, NULL),
(95, 'Alexandra Manalo', '$2y$10$vOJ26cV5v0mSmDTzSIxpPewtkLglk6IO1Meo6NCbCzJOcRBEuNlre', 'scholar', '2025-09-30 06:22:13', 0, NULL),
(96, 'PaclebMarieThony_', '$2y$10$0ZOgjG.2eNOnpffwBEqjKu2zV8AGEOC8CAfxouzo.GwqLU8Tafs32', 'scholar', '2025-09-30 06:22:20', 0, NULL),
(97, 'stephaniecatibog03', '$2y$10$MWHOwjVfVLTOMI2UlyTk4OJYs7dX7K7VdNR7iK1PGlqMwu/MV5qc.', 'scholar', '2025-09-30 06:22:33', 0, NULL),
(98, 'Kyla Marie Medina', '$2y$10$g1865JUwD22iyErPvLILneqnynOqvbgWXWCJpidmVafJWMOiLGhAm', 'scholar', '2025-09-30 06:22:40', 0, NULL),
(99, 'kaylapiliin', '$2y$10$Vjy5JDJztESl6B0y0FZ.EO60r6bNV/59QcGTGsaikh0yN030g021W', 'scholar', '2025-09-30 06:22:47', 0, NULL),
(100, 'Bingcang23', '$2y$10$rvN.OxsVYuP.ubnyk2QHx.IN9ZkSDkdIKQh5pAu18s4i45IL3klo6', 'scholar', '2025-09-30 06:22:54', 0, NULL),
(101, 'Angela Chica', '$2y$10$4CgVh9EQKcBQVCwbH6yZOellf2Liw7UGviZQDGvVGTdBs/FAsjfju', 'scholar', '2025-09-30 06:23:03', 0, NULL),
(102, 'Pam De Jesus', '$2y$10$dRApLOtRzm7RpqUgFlwine.cV9coQIG3GJerddYTcKLmqXNTbpyjq', 'scholar', '2025-09-30 06:23:09', 0, NULL),
(103, 'Q', '$2y$10$vqR31d4A/xolFNhXocYQme2wAVPf6V439.emE1dQg7d1OlbwgVaZK', 'scholar', '2025-09-30 06:23:14', 0, NULL),
(104, 'Kevin_14', '$2y$10$13ODPLR5u8FEn8CfdECUOu.OeaPOvAJYb2ma/l.G6ysH.ugD.h0Eq', 'scholar', '2025-09-30 06:23:21', 0, NULL),
(105, 'Cherry Mae Torres', '$2y$10$lo8C7po3Mz2KMw.knnuV2uFk6UrBz.9v/5gXEzibkHvWGV2mIfG/6', 'scholar', '2025-09-30 06:23:46', 0, NULL),
(106, 'Paulo Palanas', '$2y$10$MH4Wo6hysMUmF3fB.RIF5.YhFU8RQBgIOvqkyTMbFPc1zcqq58uBe', 'scholar', '2025-09-30 06:23:51', 0, NULL),
(107, 'MaryjoyC_18', '$2y$10$aDAEzqBPrjVpzu706qGvgu9Lf2jN/hObn9lgV57o/dkg.IqnfV0S2', 'scholar', '2025-09-30 06:25:28', 0, NULL),
(108, 'zararosalyn12', '$2y$10$nUdLGV1pg.YlUfI0rW5JSeG69E7JoNUJQCKa/8W.loBLndGQinaz6', 'scholar', '2025-09-30 06:26:22', 0, NULL),
(109, 'Angel_DeGuzman', '$2y$10$YH2TTBevH8cUPyrPLjnJxeBHXO2ZTUkwsPCFwSQ1sAZsdRQawX8Fu', 'scholar', '2025-09-30 06:38:42', 0, NULL),
(111, 'Izzy_bela', '$2y$10$0VuB/J.Gr6sO5W8645XouefNm994Om7IAAjhM6yo9vaTloPPlNDNK', 'scholar', '2025-09-30 07:03:26', 0, NULL),
(112, 'Antonette_12', '$2y$10$cDaBRARSiLMhpRj9.IhR7uWk52JVr1hmOiRGy3YUomexngHehTBBu', 'scholar', '2025-09-30 07:03:38', 0, NULL),
(113, 'Kristine Joy Punzalan', '$2y$10$oaOMN56lzMomDUjDEwNV4OzlTJXbnUvwH.yyPJoe8brUbuOHu./xe', 'scholar', '2025-09-30 07:03:48', 0, NULL),
(114, 'Lai_26', '$2y$10$A3ng/3Th799x6iIU4NIxm.IHaTM3kdBJv3S.ua/mUBbmm6AiV2fo.', 'scholar', '2025-09-30 07:09:49', 0, NULL),
(115, 'PaulineAnneA.', '$2y$10$kY1SUWhDW1wtzeraSNoh6.jioX9.NoPMLqi6M4v1XWq31kqAi6ulu', 'scholar', '2025-09-30 07:11:33', 0, NULL),
(116, 'Marilou_123', '$2y$10$410kSDCE5jF33Z8n8qvfxOm5seGeA59LbUnGyvu05APClzeIrMwfG', 'scholar', '2025-09-30 09:13:10', 0, NULL),
(117, 'tolentinoangela191', '$2y$10$ZtLRoHV2pXIdXrEr4.cq/.jb27LZAlPJBKPFrzJatMyeYF4w3Vk1q', 'scholar', '2025-09-30 09:13:49', 0, NULL),
(118, 'Ongyjoan', '$2y$10$QZGz1TIBcYbqZrCmN1UxF.2TZv/mJfQG.Qp4j4JNjp2PhWYL8scL.', 'scholar', '2025-09-30 10:29:43', 0, NULL),
(119, 'Joel@07', '$2y$10$2ZHEHyKj7ufeEF.DIQk7WeOnJdoXSaPXGo/nvAvIvP3w6Wguud51u', 'scholar', '2025-09-30 10:30:01', 0, NULL),
(120, 'Vincent Jay Entico', '$2y$10$zHHp6gDiKFwMAB.9lk0sxur82SJ8vV5UC5vraceHp1Q.lCIMHX4b2', 'scholar', '2025-09-30 13:48:15', 0, NULL),
(121, 'Kim_0305', '$2y$10$GZ/UyHLZCDsU5oq5iLwYYOR2Mj21EzKJ5f3.828DPEuF9gogabB7W', 'scholar', '2025-09-30 13:48:23', 0, NULL),
(122, 'Roldan_09', '$2y$10$0VGlfD1OC8Vr3SVCJC0.leugH1XwexmbOFxIw9db5yVOngB59zaWi', 'scholar', '2025-09-30 13:48:32', 0, NULL),
(123, 'Blanza', '$2y$10$6u8qh0WxwpmUKbEsza4Kr..ojT2671XS4kY2EyuDTLVL0Fk8N6r.W', 'scholar', '2025-09-30 13:48:48', 0, NULL),
(124, 'vianah casais', '$2y$10$FvFoFoTWtT87J59/5fitKOztZSK9vruiwIGYvep8OJnm0q/adIBmC', 'scholar', '2025-09-30 13:49:22', 0, NULL),
(125, 'Lexy_028', '$2y$10$KcuAo16zBc3HKcA8uOrWuOINrbkVpqtf9arL3CZQLA5sAoknMW40W', 'scholar', '2025-09-30 13:49:36', 0, NULL),
(126, 'Mikyla_04', '$2y$10$p9a8dX338S6qD0DqB4sRHOwuCshR6YKWLqvERENr/DFyqAVIAjq8a', 'scholar', '2025-09-30 13:49:43', 0, NULL),
(127, 'PreciousRobles', '$2y$10$bjwkr/dYeIk16w9OPlLThOR/sF4TfnN3ug0k63G2J7F4Pvj/3dykG', 'scholar', '2025-09-30 13:49:55', 0, NULL),
(128, 'sacdalanangelica25@gmail.com', '$2y$10$B.WmOM7NU0GflbJ8wdm9weJc9VBTZ8X7J8weDF9nlTKzV3Wl4/0CW', 'scholar', '2025-09-30 13:51:08', 0, NULL),
(129, 'Johann05', '$2y$10$gncE893e/rDoEZsxbcelr.WgIAujpvKwjJeHqzpvfyROhFVDsfRSe', 'scholar', '2025-09-30 13:51:18', 0, NULL),
(130, 'Deleon_13', '$2y$10$Mqq4C0H3yecbOXAznfiI6.EW9aCsQFl7tQ9VaGohu1zoR2P5Z6QTe', 'scholar', '2025-09-30 13:51:26', 0, NULL),
(131, 'Carl_2005', '$2y$10$QuE5D9YyDwNypF.iPwuE4elajwb1pSnMlJHcG8PAs6njTxYbBcuaS', 'scholar', '2025-09-30 13:51:57', 0, NULL),
(132, 'Kyla_13', '$2y$10$rOlNAXUPM8Gff3ZUALBmAudlMt7hPGK/4LnK811FWQIsC8nhL1lIO', 'scholar', '2025-09-30 13:52:04', 0, NULL),
(133, 'Bernadette_01', '$2y$10$rypMLH.XL6DcQ.9oCkCb7ew2SLGB.yemqcOTWSzZNDDVe125Ntzb.', 'scholar', '2025-09-30 13:52:15', 0, NULL),
(134, 'Yahnzel02', '$2y$10$MQez5nvd1h1O1SWGk2h.1O08XRv7Q0qTvUkBj3NM58a044X0aZ1KO', 'scholar', '2025-09-30 13:52:24', 0, NULL),
(135, 'CJBasilio', '$2y$10$CyTg7N9jhVSKfcRa2qQ/aeO/WriwAvB110CXgFGlNxLToWNvRKfEq', 'scholar', '2025-09-30 13:53:02', 0, NULL),
(136, 'Jeem_0123', '$2y$10$xF4BKzXRx9tTuKHaKZlhMuvD0z56hLAljdLkWMYS2ffHRX8QSD2EK', 'scholar', '2025-09-30 13:53:10', 0, NULL),
(137, 'Princes_0908', '$2y$10$6Hg7PEnpNv0z8Qf0EqSRRekRYLjner5PvONTCLW0SAKa7BFebor5i', 'scholar', '2025-09-30 13:53:26', 0, NULL),
(139, 'Erich', '$2y$10$whDvfuOAVmBRqfPgJByruOCU1lnXtEUER21mJqwdiBMcX7zeEonbG', 'scholar', '2025-09-30 13:53:48', 0, NULL),
(140, 'ZaraJ_29', '$2y$10$aIzZl2x/PPyzTUktpYq.x.uAEpJPW6whr2EtLdBDp0DUEuO2KZ/4u', 'scholar', '2025-09-30 13:54:01', 0, NULL),
(141, 'Joshua', '$2y$10$Hs4mNiyHVa0S3LfP9RIjo.m9bPuvKgjQxWje2GEK4bEJYS56.WKCC', 'scholar', '2025-09-30 13:54:08', 0, NULL),
(142, 'Iyah_021', '$2y$10$LMarNWTWDZsT./sLQrgbHeMRs3g7pgZQYlJlZBN0I8G7QVglwMNYu', 'scholar', '2025-09-30 13:54:27', 0, NULL),
(143, 'Mcristine_13', '$2y$10$1l4je/mWUz8PVFMJ7QhIK../mawqXRGIlrJt3EQhVPSH0QliwTX/a', 'scholar', '2025-09-30 13:54:36', 0, NULL),
(144, 'Rica_12', '$2y$10$LbZ/.bpGaLIjJuNOV0mjMuPeUM1Ce01iePe15hw8ai5K9ZmAQs5E2', 'scholar', '2025-09-30 13:54:42', 0, NULL),
(145, 'Tricia Mendoza', '$2y$10$VpI4cpanGP4C2bT40NmE2.LncYvXyHp4KNJdv1.FM/NI0Kmx8rFzu', 'scholar', '2025-09-30 13:54:50', 0, NULL),
(146, 'Abby_022', '$2y$10$FZPUdo3yKGPQz1At6NAB4.xmGFmNGdhOjqeYYcdz0nsCxOL.gafwy', 'scholar', '2025-09-30 14:13:28', 0, NULL),
(147, 'jorosmagnaye21', '$2y$10$kY6UbUBYAz6ulsE8lBCO8.KxXTCtAuGbWbtovKLHTQMajVQqdPhly', 'scholar', '2025-09-30 14:18:35', 0, NULL),
(149, 'Miel_04', '$2y$10$GUVGQiUNk0hBi982R8EHBew25fxnQrYAtDleDYK4yw.yDcEIlYzLe', 'scholar', '2025-09-30 14:18:56', 0, NULL),
(150, 'John Hendrick Pastoral', '$2y$10$GoeX7MHMSa0M5LnpVUB1VO7eUxxlqPzG//c3F3udgVY2b5n2gi.yq', 'scholar', '2025-09-30 14:27:48', 0, NULL),
(151, 'kristoffe_Deleon', '$2y$10$8nt.Eqtqyz3NK2H18ziG7O7B8uGvqnxX4sD3EEPkJ9tZDXj.V1CA.', 'scholar', '2025-09-30 14:27:54', 0, NULL),
(152, 'Junior Banaguas', '$2y$10$OBSs/EdkDx5YAClNBN7erOldHGpkW8r.sZMVKf2KVfGVeKdesuu.S', 'scholar', '2025-09-30 14:32:51', 0, NULL),
(153, 'Emerson Dulongbinte', '$2y$10$rE/jxDSsS1xmf/H632U3WOf86lanGmf3yDA/XOpo4Y1FS1pDwEx92', 'scholar', '2025-09-30 14:33:39', 0, NULL),
(155, 'marata_2003', '$2y$10$hi05XqjLFiR.VcwBziPBm.cyEdXMcdpLGYMsv2D/Sy0OD05CX9sGu', 'scholar', '2025-10-01 00:19:05', 0, NULL),
(156, 'larairish_', '$2y$10$xnr/TXoQqPsbSLhk4fFHqusguNL/UPBal5s1xmTFFCL74JZApMKvC', 'scholar', '2025-10-01 03:57:52', 0, NULL),
(157, 'kathleenjoynoche', '$2y$10$pjyq6U0vYPUKJFRNVju4MeAI.g7QtxK/OIlhbrPln2cmVUCrrSmni', 'scholar', '2025-10-01 03:58:09', 0, NULL),
(158, 'markjeffreyconcepcion6@gmail.com', '$2y$10$5wsbnzGuzKOySSGOnuflVuDZmcQzjrwqwYIU.CJkCse/iUNnRfn2W', 'scholar', '2025-10-01 03:58:21', 0, NULL),
(159, 'Chirsty Albis', '$2y$10$iSTD./NEjrvSBqNbvJweweDGuk9GhUNbfMVBiWuydct6Q4mFOl8X2', 'scholar', '2025-10-03 10:45:36', 0, NULL),
(160, '09850401859', '$2y$10$cKKtnQVaMlbafINALzyoYO3KRKd6IPudEvlqHS6WSaqwD1LBhOcdG', 'scholar', '2025-10-03 11:08:57', 0, NULL),
(161, 'angela dellamas', '$2y$10$hDJlNPYpqrXr2xSpfG3GSetfceuM9qC5/5S67qLYLTvcQSxF3HGq.', 'scholar', '2025-10-03 11:09:09', 0, NULL),
(162, 'racquel viarino', '$2y$10$MKN/6mrrc516L8K2kWka9uCY3/scJXZnDSoWbU.XLbxAeHi8Nhd5.', 'scholar', '2025-10-03 11:25:33', 0, NULL),
(163, 'MGB', '$2y$10$MR14wofW0YFGKPPccgb5ReAAAS5xcD57tjWLBQl4Z1QSOe81AT4dO', 'scholar', '2025-10-03 13:33:44', 0, NULL),
(164, 'mmdumas', '$2y$10$/9W8xOb5GyENJT9UH72xxujP1vKMfcPtchbf4NdYdjFDdHCzmkVQS', 'scholar', '2025-10-03 13:35:46', 0, NULL),
(165, 'djxjscb', '$2y$10$7cEN/pm7JeIgXrWg5Y5/4uwSqkX.GtDCApI78BUfq3Y.mbl6MdAHe', 'scholar', '2025-10-03 13:36:02', 0, NULL),
(166, 'Yna', '$2y$10$L46nJT5ngDzc2kBUzWIby.qdoMg4R/I.PwjHEP90RsiX4m7inXU1u', 'scholar', '2025-10-03 13:39:58', 0, NULL),
(167, 'Erika_0126', '$2y$10$4VGEUoaLTW/cYIcJpwMtB.eFBAB6hgixP5CF4rWtJFoUZEpG46eue', 'scholar', '2025-10-05 14:22:44', 0, NULL),
(168, 'Cristine may Bautista', '$2y$10$uub.eS.Fs4F4QNj.g/hiUupRCiHvkcPrbWAVAXX5YJl4DiXZ.DPi.', 'scholar', '2025-10-05 14:23:52', 0, NULL),
(169, 'jean_03', '$2y$10$aq6wNDgwhpog6G6okziLGexiKpPK7MOh1E55b3N6IhvhoiBF6uwvi', 'scholar', '2025-10-05 14:29:46', 0, NULL),
(170, 'Hazel_1713', '$2y$10$UeeUnYVKSFiBi8vZMrHG1.8N7yn15UAj/YpPyb2M7G6HPDS2HDM6m', 'scholar', '2025-10-09 05:29:49', 0, NULL),
(171, 'Marrie', '$2y$10$H29xHNAxVYGxC4hcPA.3suWF52ezkcIdrHxAJSAtQm.UrwwV2orDq', 'scholar', '2025-10-09 06:07:42', 0, NULL),
(172, 'Rillan', '$2y$10$KiAUjqBYzU0m3GpmRVQohOHQCA7J3mHCOLtvFQ923AhGtwxTIVLjO', 'scholar', '2025-10-09 06:20:40', 0, NULL),
(173, 'Karlov1', '$2y$10$RwJfGkeIsfv9UxgudcidTOp4lIfR2yzvuHseoPtYx2rMhwenVm98m', 'scholar', '2025-10-19 12:30:06', 0, NULL),
(174, 'Johans23', '$2y$10$sgiFQQHC7xqhKiaLR.Cfregz8ObZnaAiA0zIVV2OiaRH9W.Pvs/3G', 'admin', '2025-12-01 06:37:14', 0, NULL),
(175, 'Micko', '$2y$10$DobOxfb6DOEWJrq/52RrFOT5785fv.lmGU9R0rFJKqtAs6GP6m0g2', 'scholar', '2025-12-02 02:20:03', 0, NULL),
(176, 'Jair Cyruz Sacdalan', '$2y$10$7sqWauRV66xLWiNoFeIwRuJDW5HBlbB01JHGSkC8YVvPHG6SJ1U3C', 'scholar', '2025-12-03 05:25:47', 0, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_logs`
--
ALTER TABLE `action_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `credentials`
--
ALTER TABLE `credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scholar_id` (`scholar_id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `scholar_id` (`scholar_id`,`document_type`);

--
-- Indexes for table `exported_credentials`
--
ALTER TABLE `exported_credentials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `scholar_id` (`scholar_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `requirements`
--
ALTER TABLE `requirements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scholars`
--
ALTER TABLE `scholars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD KEY `idx_batch` (`batch`),
  ADD KEY `idx_scholarship_type` (`scholarship_type`),
  ADD KEY `idx_scholars_batch` (`batch`);

--
-- Indexes for table `scholar_applications`
--
ALTER TABLE `scholar_applications`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `scholar_credentials`
--
ALTER TABLE `scholar_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_scholar_type` (`scholar_id`,`document_type`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `scholar_enrollments`
--
ALTER TABLE `scholar_enrollments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_scholar_year` (`scholar_id`,`school_year_id`),
  ADD KEY `school_year_id` (`school_year_id`);

--
-- Indexes for table `school_years`
--
ALTER TABLE `school_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `special_cases`
--
ALTER TABLE `special_cases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_scholar` (`scholar_id`);

--
-- Indexes for table `tes_applicants`
--
ALTER TABLE `tes_applicants`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`),
  ADD UNIQUE KEY `email_address` (`email_address`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_logs`
--
ALTER TABLE `action_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=134;

--
-- AUTO_INCREMENT for table `credentials`
--
ALTER TABLE `credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=375;

--
-- AUTO_INCREMENT for table `exported_credentials`
--
ALTER TABLE `exported_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=165;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `requirements`
--
ALTER TABLE `requirements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `scholars`
--
ALTER TABLE `scholars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=168;

--
-- AUTO_INCREMENT for table `scholar_applications`
--
ALTER TABLE `scholar_applications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=188;

--
-- AUTO_INCREMENT for table `scholar_credentials`
--
ALTER TABLE `scholar_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `scholar_enrollments`
--
ALTER TABLE `scholar_enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=158;

--
-- AUTO_INCREMENT for table `school_years`
--
ALTER TABLE `school_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `special_cases`
--
ALTER TABLE `special_cases`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tes_applicants`
--
ALTER TABLE `tes_applicants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `action_logs`
--
ALTER TABLE `action_logs`
  ADD CONSTRAINT `action_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `credentials`
--
ALTER TABLE `credentials`
  ADD CONSTRAINT `credentials_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `documents_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exported_credentials`
--
ALTER TABLE `exported_credentials`
  ADD CONSTRAINT `exported_credentials_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholar_credentials`
--
ALTER TABLE `scholar_credentials`
  ADD CONSTRAINT `scholar_credentials_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `scholar_enrollments`
--
ALTER TABLE `scholar_enrollments`
  ADD CONSTRAINT `scholar_enrollments_ibfk_1` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scholar_enrollments_ibfk_2` FOREIGN KEY (`school_year_id`) REFERENCES `school_years` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `special_cases`
--
ALTER TABLE `special_cases`
  ADD CONSTRAINT `fk_scholar` FOREIGN KEY (`scholar_id`) REFERENCES `scholars` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
