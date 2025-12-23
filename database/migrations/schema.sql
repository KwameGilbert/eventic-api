-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 23, 2025 at 03:27 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `eventic`
--
CREATE DATABASE IF NOT EXISTS `eventic` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `eventic`;

-- --------------------------------------------------------

--
-- Table structure for table `attendees`
--

DROP TABLE IF EXISTS `attendees`;
CREATE TABLE IF NOT EXISTS `attendees` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `attendees`
--

INSERT INTO `attendees` (`id`, `user_id`, `first_name`, `last_name`, `email`, `phone`, `bio`, `profile_image`, `created_at`, `updated_at`) VALUES
(1, 5, 'Test', 'Attendee', 'attendee@test.com', '+233241234567', NULL, NULL, '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(2, 6, 'Micheal', 'Jackson', 'client@example.com', '0541436414', 'Hello World', '/uploads/profiles/attendee_6_1765046803.jpg', '2025-12-06 19:46:05', '2025-12-06 19:51:45'),
(3, 8, 'FirstCode.Inc', '', 'gkukah1@gmail.com', NULL, NULL, NULL, '2025-12-06 22:55:51', '2025-12-06 22:55:51'),
(4, 7, 'Gilbert', 'Elikplim Kukah', 'kwamegilbert1114@gmail.com', NULL, NULL, NULL, '2025-12-06 22:56:45', '2025-12-06 22:56:45');

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB AUTO_INCREMENT=103 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `ip_address`, `user_agent`, `metadata`, `created_at`) VALUES
(1, NULL, 'login_failed', '127.0.0.1', 'PostmanRuntime/7.49.0', '{\"reason\":\"user_not_found\",\"email\":\"kwamert@gmail.com\"}', '2025-11-30 09:00:23'),
(2, NULL, 'login_failed', '127.0.0.1', 'PostmanRuntime/7.49.0', '{\"reason\":\"user_not_found\",\"email\":\"kwamert@gmail.com\"}', '2025-11-30 09:00:30'),
(3, NULL, 'login_failed', '127.0.0.1', 'PostmanRuntime/7.49.0', '{\"reason\":\"user_not_found\",\"email\":\"kwamert@gmail.com\"}', '2025-11-30 09:00:33'),
(4, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:32:42'),
(5, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:23'),
(6, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:25'),
(7, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:27'),
(8, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:29'),
(9, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:31'),
(10, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:33'),
(11, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:35'),
(12, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:38'),
(13, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:40'),
(14, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:43'),
(15, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:45'),
(16, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:48'),
(17, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:50'),
(18, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.0', NULL, '2025-11-30 09:34:53'),
(19, NULL, 'login', '127.0.0.1', 'PostmanRuntime/7.49.1', NULL, '2025-11-30 22:22:53'),
(20, NULL, 'login_failed', '127.0.0.1', 'PostmanRuntime/7.49.1', '{\"reason\":\"user_not_found\",\"email\":\"kwamegilbert111@gmail.com\"}', '2025-11-30 22:24:17'),
(21, NULL, 'login_failed', '127.0.0.1', 'PostmanRuntime/7.49.1', '{\"reason\":\"user_not_found\",\"email\":\"kwamegilbert111@gmail.com\"}', '2025-11-30 22:26:23'),
(22, NULL, 'register', '127.0.0.1', 'PostmanRuntime/7.49.1', NULL, '2025-11-30 22:38:00'),
(23, 6, 'register', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:27:57'),
(24, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:37:33'),
(25, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:42:56'),
(26, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:45:59'),
(27, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:50:48'),
(28, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:52:15'),
(29, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:54:11'),
(30, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 17:57:52'),
(31, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 18:02:27'),
(32, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 18:19:01'),
(33, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 19:09:52'),
(34, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 19:16:45'),
(35, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-05 21:43:37'),
(36, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 00:20:39'),
(37, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 07:18:52'),
(38, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 07:40:09'),
(39, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:04:58'),
(40, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:08:40'),
(41, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:12:24'),
(42, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:22:38'),
(43, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:31:24'),
(44, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:39:48'),
(45, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:54:37'),
(46, 6, 'login', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', NULL, '2025-12-06 08:57:35'),
(47, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 11:20:56'),
(48, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 11:21:25'),
(49, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 12:23:34'),
(50, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 14:18:11'),
(51, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/17.5 Mobile/15A5370a Safari/602.1', NULL, '2025-12-06 15:49:58'),
(52, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 17:57:12'),
(53, 6, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 18:16:26'),
(54, 6, 'password_changed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 18:58:00'),
(55, 7, 'register', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 19:42:53'),
(56, 8, 'register', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 20:05:11'),
(57, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 21:28:14'),
(58, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 21:56:44'),
(59, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-06 21:56:59'),
(60, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 06:51:21'),
(61, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 08:03:33'),
(62, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 09:06:02'),
(63, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 12:40:56'),
(64, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 14:38:06'),
(65, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 14:40:18'),
(66, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 14:41:57'),
(67, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 15:46:15'),
(68, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 15:46:57'),
(69, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 15:48:49'),
(70, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 15:54:37'),
(71, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-07 15:55:51'),
(72, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 06:05:02'),
(73, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 13:25:56'),
(74, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 14:34:29'),
(75, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-08 16:17:16'),
(76, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-09 10:23:47'),
(77, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 22:37:10'),
(78, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', NULL, '2025-12-10 22:37:12'),
(79, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', NULL, '2025-12-15 09:07:41'),
(80, 7, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 16:26:31'),
(81, 8, 'login', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-19 16:40:02'),
(82, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 09:40:32'),
(83, 6, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 09:43:06'),
(84, 8, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 13:15:28'),
(85, 8, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 13:59:14'),
(86, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:01:50'),
(87, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:01:57'),
(88, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:03:00'),
(89, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:03:28'),
(90, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:04:55'),
(91, 8, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 14:05:29'),
(92, 6, 'login_failed', '::1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/17.5 Mobile/15A5370a Safari/602.1', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:06:35'),
(93, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:07:24'),
(94, 7, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:07:35'),
(95, 7, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:09:22'),
(96, 8, 'login', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', NULL, '2025-12-23 14:10:44'),
(97, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:15:28'),
(98, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:15:43'),
(99, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:16:25'),
(100, 7, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:18:14'),
(101, 6, 'login_failed', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:18:38'),
(102, 6, 'login_failed', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '{\"reason\":\"invalid_password\"}', '2025-12-23 14:23:02');

-- --------------------------------------------------------

--
-- Table structure for table `awards`
--

DROP TABLE IF EXISTS `awards`;
CREATE TABLE IF NOT EXISTS `awards` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `ceremony_date` datetime NOT NULL COMMENT 'Awards ceremony date',
  `voting_start` datetime NOT NULL COMMENT 'Global voting start',
  `voting_end` datetime NOT NULL COMMENT 'Global voting end',
  `status` enum('draft','completed','pending','published','cancelled') DEFAULT 'draft',
  `show_results` tinyint(1) NOT NULL DEFAULT 1 COMMENT 'Whether to show voting results publicly',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `admin_share_percent` decimal(5,2) NOT NULL DEFAULT 15.00 COMMENT 'Admin/platform share percentage (0-100). Organizer gets remainder.',
  `country` varchar(255) NOT NULL DEFAULT 'Ghana',
  `region` varchar(255) NOT NULL DEFAULT 'Greater Accra',
  `city` varchar(255) NOT NULL DEFAULT 'Accra',
  `phone` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `views` int(11) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `organizer_id` (`organizer_id`),
  KEY `status` (`status`),
  KEY `is_featured` (`is_featured`),
  KEY `voting_start` (`voting_start`),
  KEY `voting_end` (`voting_end`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `awards`
--

INSERT INTO `awards` (`id`, `organizer_id`, `title`, `slug`, `description`, `banner_image`, `venue_name`, `address`, `map_url`, `ceremony_date`, `voting_start`, `voting_end`, `status`, `show_results`, `is_featured`, `admin_share_percent`, `country`, `region`, `city`, `phone`, `website`, `facebook`, `twitter`, `instagram`, `video_url`, `views`, `created_at`, `updated_at`) VALUES
(1, 3, 'Global Tech Innovators 2025', 'global-tech-innovators-2025', 'Celebrating the best in technology and AI.', 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1000&auto=format&fit=crop', 'Grand Convention Center', '123 Innovation Dr', 'https://maps.google.com/?q=123', '2025-12-15 18:00:00', '2025-10-01 00:00:00', '2026-12-16 23:59:59', 'completed', 1, 1, 15.00, 'USA', 'California', 'San Francisco', '+15550199', 'https://techawards.com', 'techawards', 'techawards', 'techawards', 'https://youtube.com/tech', 1571, '2025-12-14 22:59:53', '2025-12-23 14:13:21'),
(2, 3, 'City Music Excellence Awards', 'city-music-excellence-awards', 'Honoring local musical talent and production.', 'https://images.unsplash.com/photo-1501281668745-f7f57925c3b4?q=80&w=1000&auto=format&fit=crop', 'City Hall Auditorium', '45 Melody Lane', 'https://maps.google.com/?q=45', '2025-11-20 19:30:00', '2025-09-01 00:00:00', '2025-11-15 23:59:59', 'completed', 1, 1, 15.00, 'UK', 'London', 'London', '+44207946', 'https://citymusic.co.uk', 'citymusic', 'citymusic', 'citymusic', NULL, 881, '2025-12-14 22:59:53', '2025-12-15 15:41:43'),
(9, 1, 'Ghana Music Awards 2025', 'ghana-music-awards-2025', 'The most prestigious music awards in Ghana celebrating excellence in music', 'https://cosororadio.co.uk/storage/2025/05/TGMA-26-1170x658.webp', 'Accra International Conference Centre', 'Liberation Road, Accra, Ghana', NULL, '2025-03-15 19:00:00', '2025-08-01 19:00:00', '2026-03-25 19:00:00', 'completed', 1, 1, 15.00, 'Ghana', 'Greater Accra', 'Accra', NULL, NULL, NULL, NULL, NULL, NULL, 3, '2025-12-13 20:16:50', '2025-12-15 16:12:04'),
(10, 1, 'Ghana Movie Awards 2025', 'ghana-movie-awards-2025', 'Celebrating excellence in Ghanaian cinema and filmmaking', NULL, 'National Theatre of Ghana', 'Liberia Road, Accra, Ghana', NULL, '2025-04-20 18:00:00', '2025-02-20 18:00:00', '2025-04-19 18:00:00', 'completed', 1, 1, 15.00, 'Ghana', 'Greater Accra', 'Accra', NULL, NULL, NULL, NULL, NULL, NULL, 4, '2025-12-13 20:16:50', '2025-12-15 16:12:04'),
(11, 1, 'Tech Innovation Awards Ghana 2025', 'tech-innovation-awards-2025', 'Recognizing outstanding innovations and achievements in Ghana\'s tech ecosystem', NULL, 'Kempinski Hotel Gold Coast City', 'Gamel Abdul Nasser Avenue, Accra, Ghana', NULL, '2025-05-10 17:00:00', '2025-03-10 17:00:00', '2025-05-09 17:00:00', 'completed', 1, 0, 15.00, 'Ghana', 'Greater Accra', 'Accra', NULL, NULL, NULL, NULL, NULL, NULL, 0, '2025-12-13 20:16:50', '2025-12-15 16:12:04'),
(12, 1, 'Ghana Sports Personality Awards 2025', 'ghana-sports-awards-2025', 'Honoring excellence in Ghanaian sports across all disciplines', NULL, 'Accra Sports Stadium', 'Osu, Accra, Ghana', NULL, '2025-06-05 19:00:00', '2025-04-05 19:00:00', '2025-06-04 19:00:00', 'completed', 1, 0, 15.00, 'Ghana', 'Greater Accra', 'Accra', NULL, NULL, NULL, NULL, NULL, NULL, 2, '2025-12-13 20:16:50', '2025-12-22 11:39:47'),
(13, 3, 'INFOTESS AWARDS', 'infotess-awards', 'HELLO WORLD, THIS IS A DESCRIPTION', NULL, 'nATINOAL tHEAORD', 'DAFSDFADFAFAS', 'ASDFF', '2025-12-19 06:48:00', '2025-12-29 18:45:00', '2026-01-31 18:45:00', 'completed', 1, 0, 15.00, 'China', 'ASDFA', 'DFASF', '', '', '', '', '', '', 3, '2025-12-15 16:12:05', '2025-12-19 17:25:09'),
(17, 3, 'hello', 'hello', 'kljadfklja', 'https://images.unsplash.com/photo-1518770660439-4636190af475?q=80&w=1000&auto=format&fit=crop', 'Nationa', 'CI-1785-2738, MZ/J 35. Gomoa Eshiem', '', '2025-12-02 00:00:00', '2025-12-25 12:42:00', '2025-12-25 12:42:00', 'completed', 1, 0, 15.00, 'Ghana', 'Greater Accra', 'Gomoa Eshiem', '+233249973054', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', 'https://youtu.be/zCsQ6ILLrrw?si=pQLwD1Gmr1-oRgUR', 11, '2025-12-15 16:59:04', '2025-12-23 14:12:10'),
(19, 3, 'AAMUSTED Most Strongest', 'aamusted-most-strongest', 'Stronrgest AAMUSTED student', 'http://app.eventic.com/uploads/banners/awards/banner_694a97e7d42d1_1766496231.webp', 'National Theatre', 'Tanoso', '', '2026-01-11 16:20:00', '2025-12-22 13:19:00', '2026-01-01 13:19:00', 'published', 1, 0, 15.00, 'Ghana', 'Ashanti', 'Kumasi', '+233541436414', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', 'https://youtu.be/zCsQ6ILLrrw?si=pQLwD1Gmr1-oRgUR', 5, '2025-12-23 14:23:51', '2025-12-23 14:56:12');

-- --------------------------------------------------------

--
-- Table structure for table `awards_images`
--

DROP TABLE IF EXISTS `awards_images`;
CREATE TABLE IF NOT EXISTS `awards_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `award_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `award_categories`
--

DROP TABLE IF EXISTS `award_categories`;
CREATE TABLE IF NOT EXISTS `award_categories` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `award_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `cost_per_vote` decimal(10,2) NOT NULL DEFAULT 1.00,
  `voting_start` datetime DEFAULT NULL,
  `voting_end` datetime DEFAULT NULL,
  `status` enum('active','deactivated') NOT NULL DEFAULT 'active',
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `award_categories`
--

INSERT INTO `award_categories` (`id`, `award_id`, `name`, `image`, `description`, `cost_per_vote`, `voting_start`, `voting_end`, `status`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 'Best AI Startup', 'https://images.unsplash.com/photo-1677442136019-21780ecad995?q=80&w=500&auto=format&fit=crop', 'Most innovative AI solution.', 0.50, '2025-10-01 00:00:00', '2025-12-01 23:59:59', 'active', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(2, 1, 'Developer of the Year', 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?q=80&w=500&auto=format&fit=crop', 'Top individual contributor.', 6.00, '2025-10-01 00:00:00', '2025-12-01 23:59:59', 'active', 2, '2025-12-14 23:00:21', '2025-12-15 07:06:15'),
(3, 2, 'Song of the Year', 'https://images.unsplash.com/photo-1470225620780-dba8ba36b745?q=80&w=500&auto=format&fit=crop', 'Best original composition.', 1.00, '2025-09-01 00:00:00', '2025-11-15 23:59:59', 'active', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(4, 2, 'Best New Artist', 'https://images.unsplash.com/photo-1516280440614-37939bbacd81?q=80&w=500&auto=format&fit=crop', 'Best debut act.', 1.00, '2025-09-01 00:00:00', '2025-11-15 23:59:59', 'active', 2, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(6, 17, 'Best Artist of The Year', NULL, '', 1.00, NULL, NULL, 'active', 1, '2025-12-16 13:52:32', '2025-12-16 13:52:32'),
(7, 17, 'Ghana\'s Most Beautiful', NULL, '', 1.00, NULL, NULL, 'active', 2, '2025-12-16 13:58:29', '2025-12-16 13:58:29'),
(9, 19, 'Best Wight Lifter', NULL, 'List the heaviest weight', 2.00, NULL, NULL, 'active', 1, '2025-12-23 14:28:52', '2025-12-23 14:28:52'),
(10, 19, 'Most Built Body', NULL, '', 1.00, NULL, NULL, 'active', 2, '2025-12-23 14:29:15', '2025-12-23 14:29:15');

-- --------------------------------------------------------

--
-- Table structure for table `award_nominees`
--

DROP TABLE IF EXISTS `award_nominees`;
CREATE TABLE IF NOT EXISTS `award_nominees` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` int(11) UNSIGNED NOT NULL,
  `award_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `award_nominees`
--

INSERT INTO `award_nominees` (`id`, `category_id`, `award_id`, `name`, `description`, `image`, `display_order`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 'NeuroCore Systems', 'Pioneering neural networks.', 'https://images.unsplash.com/photo-1614064641938-3bbee52942c7?q=80&w=200&auto=format&fit=crop', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(2, 1, 1, 'DeepMindz', 'AI for healthcare solutions.', 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485?q=80&w=200&auto=format&fit=crop', 2, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(3, 1, 1, 'AutoBotics', 'Autonomous robotics AI.', 'https://images.unsplash.com/photo-1535378620166-273708d44e4c?q=80&w=200&auto=format&fit=crop', 3, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(4, 2, 1, 'Sarah Jenkins', 'Lead dev at OpenSource.', 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?q=80&w=200&auto=format&fit=crop', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(5, 2, 1, 'Mike Ross', 'Fullstack expert.', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?q=80&w=200&auto=format&fit=crop', 2, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(6, 3, 2, 'Midnight Rain', 'A soulful jazz piece.', 'https://images.unsplash.com/photo-1514320291840-2e0a9bf2a9ae?q=80&w=200&auto=format&fit=crop', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(7, 3, 2, 'Electric Pulse', 'High energy EDM track.', 'https://images.unsplash.com/photo-1494232410401-ad00d5433cfa?q=80&w=200&auto=format&fit=crop', 2, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(8, 4, 2, 'The Echoes', 'Indie rock band.', 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?q=80&w=200&auto=format&fit=crop', 1, '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(11, 6, 17, 'Gilbert Elikplim', '', 'http://app.eventic.com/uploads/images/nominees/image_694157263dcec_1765889830.png', 1, '2025-12-16 13:57:10', '2025-12-16 13:57:10'),
(12, 9, 19, 'Shaka Zulu', '', 'http://app.eventic.com/uploads/images/nominees/image_694a9967f2a64_1766496615.jpg', 1, '2025-12-23 14:30:16', '2025-12-23 14:30:16'),
(13, 9, 19, 'Van Damme', '', 'http://app.eventic.com/uploads/images/nominees/image_694a99870e3eb_1766496647.webp', 2, '2025-12-23 14:30:47', '2025-12-23 14:30:47'),
(14, 10, 19, 'Victor Ampofoh', '', 'http://app.eventic.com/uploads/images/nominees/image_694a99adb0a07_1766496685.jpg', 1, '2025-12-23 14:31:25', '2025-12-23 14:31:25'),
(15, 10, 19, 'Prosper Dakora', '', 'http://app.eventic.com/uploads/images/nominees/image_694a99f082918_1766496752.jpg', 2, '2025-12-23 14:32:32', '2025-12-23 14:32:32');

-- --------------------------------------------------------

--
-- Table structure for table `award_votes`
--

DROP TABLE IF EXISTS `award_votes`;
CREATE TABLE IF NOT EXISTS `award_votes` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `nominee_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED NOT NULL,
  `award_id` int(11) UNSIGNED NOT NULL,
  `number_of_votes` int(11) UNSIGNED NOT NULL,
  `cost_per_vote` decimal(10,2) NOT NULL DEFAULT 0.00,
  `gross_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admin_share_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `admin_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `organizer_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','paid') NOT NULL DEFAULT 'pending',
  `reference` text NOT NULL,
  `voter_name` varchar(255) DEFAULT NULL,
  `voter_email` varchar(255) DEFAULT NULL,
  `voter_phone` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `nominee_id` (`nominee_id`),
  KEY `category_id` (`category_id`),
  KEY `award_id` (`award_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `award_votes`
--

INSERT INTO `award_votes` (`id`, `nominee_id`, `category_id`, `award_id`, `number_of_votes`, `cost_per_vote`, `gross_amount`, `admin_share_percent`, `admin_amount`, `organizer_amount`, `payment_fee`, `status`, `reference`, `voter_name`, `voter_email`, `voter_phone`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 2, 5, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF12345678', 'John Doe', 'john@example.com', '1234567890', '2025-12-14 23:00:21', '2025-12-15 08:56:01'),
(2, 2, 1, 1, 10, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF12345679', 'Jane Smith', 'jane@example.com', '0987654321', '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(3, 1, 1, 1, 2, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF12345680', 'Alice Brown', 'alice@example.com', '1122334455', '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(4, 4, 2, 1, 1, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF_FREE_01', 'Bob White', 'bob@example.com', NULL, '2025-12-14 23:00:21', '2025-12-14 23:03:50'),
(5, 6, 3, 2, 20, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF99887766', 'Charlie Green', 'charlie@example.com', '5566778899', '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(6, 7, 3, 2, 5, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, 'paid', 'REF99887700', 'David Black', 'david@example.com', '6677889900', '2025-12-14 23:00:21', '2025-12-14 23:00:21'),
(7, 14, 10, 19, 50, 1.00, 50.00, 15.00, 6.75, 42.50, 0.75, 'paid', 'VOTE-14-1766497466-694a9cbac0918', 'Gilbert Elikplim Kukah', 'kwamegilbert1114@gmail.com', '+233541436414', '2025-12-23 14:44:26', '2025-12-23 14:45:34'),
(8, 13, 9, 19, 1, 2.00, 2.00, 15.00, 0.27, 1.70, 0.03, 'paid', 'VOTE-13-1766498113-694a9f4163b4e', 'Gilbert Elikplim Kukah', 'kwamegilbert1114@gmail.com', '+233541436414', '2025-12-23 14:55:13', '2025-12-23 14:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `event_type_id` int(11) UNSIGNED DEFAULT NULL,
  `venue_name` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `map_url` varchar(255) DEFAULT NULL,
  `banner_image` varchar(255) DEFAULT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `status` enum('draft','completed','pending','published','cancelled') DEFAULT 'draft',
  `is_featured` tinyint(1) NOT NULL DEFAULT 0,
  `admin_share_percent` decimal(5,2) NOT NULL DEFAULT 10.00 COMMENT 'Admin/platform share percentage (0-100). Organizer gets remainder.',
  `audience` varchar(255) DEFAULT NULL,
  `language` varchar(255) DEFAULT NULL,
  `tags` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`tags`)),
  `website` varchar(255) DEFAULT NULL,
  `facebook` varchar(255) DEFAULT NULL,
  `twitter` varchar(255) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `country` varchar(255) NOT NULL DEFAULT 'Ghana',
  `region` varchar(255) NOT NULL DEFAULT 'Greater Accra',
  `city` varchar(255) NOT NULL DEFAULT 'Accra',
  `views` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `event_type_id` (`event_type_id`),
  KEY `organizer_id` (`organizer_id`),
  KEY `is_featured` (`is_featured`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `organizer_id`, `title`, `slug`, `description`, `event_type_id`, `venue_name`, `address`, `map_url`, `banner_image`, `start_time`, `end_time`, `status`, `is_featured`, `admin_share_percent`, `audience`, `language`, `tags`, `website`, `facebook`, `twitter`, `instagram`, `phone`, `video_url`, `created_at`, `updated_at`, `country`, `region`, `city`, `views`) VALUES
(1, 2, 'Afro Nation Ghana 2025', 'afro-nation-ghana-2025', 'Experience the biggest Afrobeats festival in West Africa! Afro Nation Ghana 2025 brings together the hottest African artists and international DJs for an unforgettable weekend of music, culture, and celebration on the beautiful beaches of Accra. Featuring performances by Burna Boy, Wizkid, Davido, Stonebwoy, and many more!', 1, 'Laboma Beach', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.773449735772!2d-0.186964!3d5.603717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80', '2025-12-27 16:00:00', '2025-12-29 23:59:00', 'published', 1, 10.00, 'Music Lovers, Festival Goers, 18+', 'English', '[\"Afrobeats\", \"Music Festival\", \"Beach Party\", \"Live Music\", \"Accra\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-23 14:58:08', 'Ghana', 'Greater Accra', 'Accra', 3),
(2, 1, 'Hearts of Oak vs Asante Kotoko', 'hearts-vs-kotoko-2025', 'The biggest rivalry in Ghanaian football! Watch Hearts of Oak take on Asante Kotoko in this thrilling Ghana Premier League showdown. Experience the electric atmosphere as the Phobians face the Porcupine Warriors in front of thousands of passionate fans.', 2, 'Accra Sports Stadium', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.8!2d-0.19!3d5.55!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1920&q=80', '2025-12-20 15:00:00', '2025-12-20 17:30:00', 'published', 1, 10.00, 'Sports Fans, Families, All Ages', 'English', '[\"Football\", \"Soccer\", \"Ghana Premier League\", \"Sports\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-06 19:47:46', 'Ghana', 'Greater Accra', 'Accra', 0),
(3, 3, 'Tech Summit Ghana 2025', 'tech-summit-ghana-2025', 'Join Ghanas largest technology conference bringing together innovators, entrepreneurs, investors, and industry leaders. Explore cutting-edge technologies including AI, blockchain, fintech, and sustainable tech. Network with professionals from across Africa and beyond. Featuring keynotes from Google, Microsoft, and leading African tech companies.', 5, 'Kempinski Hotel Gold Coast City', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.5!2d-0.17!3d5.58!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1920&q=80', '2026-01-15 08:00:00', '2026-01-17 17:00:00', 'published', 1, 10.00, 'Tech Professionals, Entrepreneurs, Students', 'English', '[\"Technology\", \"Conference\", \"Innovation\", \"Networking\", \"Startup\"]', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233249973054', NULL, '2025-12-06 19:47:46', '2025-12-11 10:25:27', 'Ghana', 'Greater Accra', 'Gomoa Eshiem', 74),
(4, 1, 'Jazz Night at +233 Bar', 'jazz-night-accra', 'Experience an unforgettable evening of smooth jazz with world-renowned artists. This intimate performance features a carefully curated selection of contemporary and classic jazz pieces that will transport you to a world of musical excellence. The +233 Bar provides the perfect ambiance for an evening of sophisticated entertainment with craft cocktails and fine dining.', 1, '+233 Jazz Bar & Grill', 'Osu, Accra, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.773449735772!2d-0.186964!3d5.603717!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&q=80', '2025-12-15 20:00:00', '2025-12-15 23:30:00', 'published', 1, 10.00, 'Adults, Jazz Enthusiasts, Music Lovers', 'English', '[\"Jazz\", \"Live Music\", \"Night Event\", \"Concert\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-10 15:15:05', 'Ghana', 'Greater Accra', 'Accra', 11),
(5, 2, 'Chale Wote Street Art Festival 2025', 'chale-wote-2025', 'Ghanas premiere street art festival returns to Jamestown! Experience a vibrant celebration of African art, music, and culture. Featuring murals, installations, performances, fashion shows, and interactive workshops. Join thousands of artists and art lovers for a weekend of creativity and cultural exchange.', 3, 'Jamestown, Accra', 'Jamestown, Accra, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.9!2d-0.21!3d5.52!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=800&q=80', '2025-08-15 10:00:00', '2025-08-17 22:00:00', 'published', 1, 10.00, 'Art Lovers, Families, All Ages', 'English', '[\"Art\", \"Street Art\", \"Festival\", \"Culture\", \"Ghana\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-06 19:47:46', 'Ghana', 'Greater Accra', 'Accra', 0),
(6, 1, 'Ghana Food & Wine Expo 2025', 'food-wine-expo-2025', 'Indulge in a culinary journey featuring the finest local and international cuisines. Meet renowned chefs, sample exclusive wines from around the world, and participate in live cooking demonstrations. This expo celebrates the rich flavors of Ghana and beyond, offering tastings, masterclasses, and networking opportunities for food enthusiasts.', 4, 'Accra International Conference Centre', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.6!2d-0.18!3d5.57!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80', '2025-12-22 12:00:00', '2025-12-22 20:00:00', 'published', 0, 10.00, 'Food Enthusiasts, Wine Lovers, Adults', 'English', '[\"Food\", \"Wine\", \"Tasting\", \"Culinary\", \"Expo\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-06 19:47:46', 'Ghana', 'Greater Accra', 'Accra', 0),
(7, 1, 'Comedy Fiesta Ghana', 'comedy-fiesta-ghana-2025', 'Get ready for a night of non-stop laughter with Ghanas top comedians! This hilarious show features stand-up performances from DKB, Clemento Suarez, OB Amponsah, and special guest comedians from Nigeria and South Africa. Perfect for a fun evening out with friends and family. Doors open at 7:30 PM for pre-show entertainment and refreshments.', 7, 'National Theatre of Ghana', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.7!2d-0.20!3d5.54!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=800&q=80', '2025-12-18 20:00:00', '2025-12-18 23:00:00', 'published', 0, 10.00, 'Adults, Families, Comedy Fans', 'English', '[\"Comedy\", \"Stand-up\", \"Entertainment\", \"Night Out\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-06 19:47:46', 'Ghana', 'Greater Accra', 'Accra', 0),
(8, 1, 'Accra International Marathon 2025', 'accra-marathon-2025', 'Lace up your running shoes for the annual Accra International Marathon! Choose from the full marathon (42km), half marathon (21km), or 10K fun run. The scenic route takes you through historic Accra neighborhoods with thousands of cheering spectators. All proceeds support local charities and youth sports development programs.', 2, 'Independence Square', 'Accra, Greater Accra Region, Ghana', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3970.8!2d-0.19!3d5.55!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1', 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1920&q=80', '2026-03-15 06:00:00', '2026-03-15 14:00:00', 'published', 0, 10.00, 'Runners, Fitness Enthusiasts, All Ages', 'English', '[\"Marathon\", \"Running\", \"Charity\", \"Fitness\", \"Sports\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-06 19:47:46', '2025-12-06 19:47:46', 'Ghana', 'Greater Accra', 'Accra', 0),
(9, 3, 'INFOTESS DINNER NIGHT', 'infotess-dinner-night', 'INFOTESS AWARDS AND DINNER NIGHT', 7, 'New Auditorium AAMUSTED', 'Tanoso, AAMUSTED, Ghana, Kumasi, Ghana', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/banners/events/banner_6942f072c36c1_1765994610.png', '2025-12-08 07:04:00', '2025-12-08 12:05:00', 'pending', 0, 10.00, 'Students', 'English', '[\"INFOTESS\",\"awards\",\"dinner\"]', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233246706020', NULL, '2025-12-07 09:09:36', '2025-12-17 19:03:31', 'Ghana', 'Greater Accra', 'ACCRA', 0),
(10, 3, 'TECH IN GHANA', 'tech-in-ghana', 'TECH', 5, 'Central Park Amphitheater', 'CI-1785-2738, MZ/J 35. Gomoa Eshiem MZ/J 35, Gomoa Eshiem, Ghana, Kumasi, Ghana', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/events/event_69354ef7ee9fb_1765101303.png', '2025-12-08 14:49:00', '2025-12-25 12:50:00', 'pending', 0, 10.00, 'Everyone', NULL, '[\"GHANA\",\"TECH\"]', 'https://summerfest2024.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233541436414', NULL, '2025-12-07 10:55:04', '2025-12-09 16:07:26', 'Ghana', 'Greater Accra', 'Accra', 0),
(11, 3, 'Gilbert Elikplim Kukah', 'gilbert-elikplim-kukah', 'dafsfafds', 2, 'New Auditorium AAMUSTED', 'CI-1785-2738, MZ/J 35. Gomoa Eshiem MZ/J 35, Gomoa Eshiem, Ghana', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/events/event_6935508c04616_1765101708.png', '2025-12-26 00:59:00', '2025-12-26 03:59:00', 'pending', 0, 10.00, 'Students', NULL, '[\"df\",\"dfda\",\"adf\",\"dfa\",\"e\",\"g\",\"efr\",\"grt\",\"4t5t\"]', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-07 11:01:48', '2025-12-09 16:07:16', 'Ghana', 'Greater Accra', 'Accra', 0),
(12, 3, 'Miss AAMUSTED', 'miss-aamusted', 'Hello World, this is miss aamusted', 7, 'New Auditorium AAMUSTED', 'Tanoso, Kumasi, Ghana', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/banners/events/banner_6942f043caf4a_1765994563.png', '2025-12-09 15:42:00', '2025-12-23 03:30:00', 'pending', 0, 10.00, 'Students', NULL, '[\"miss\",\"aamusted\"]', 'www.melekglobalconsult.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233541436414', 'https://youtube.com/watch?v=example', '2025-12-08 14:45:41', '2025-12-17 19:02:43', 'Ghana', 'Greater Accra', 'Accra', 0),
(14, 3, 'Ghana\'s Most Beautiful', 'ghana-s-most-beautiful', 'Ghana’s Most Beautiful is a nationally televised cultural pageant that showcases the heritage, values, and traditions of Ghana’s diverse regions. The competition focuses on intellect, eloquence, leadership, and cultural representation rather than traditional beauty standards. Contestants undergo weeks of themed performances, community projects, and public engagements, with the ultimate objective of promoting national unity and celebrating Ghanaian identity.', 3, 'Accra National Theater', '123 Main St', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/events/event_69380a8feab72_1765280399.png', '2026-01-12 08:00:00', '2026-01-12 20:00:00', 'published', 0, 10.00, 'Everyone', 'English', '[\"ghana\",\"beauty\",\"all_nations\"]', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233541436414', 'https://youtu.be/zCsQ6ILLrrw?si=pQLwD1Gmr1-oRgUR', '2025-12-09 12:40:00', '2025-12-23 14:12:42', 'Ghana', 'Greater Accra', 'Accra', 19),
(19, 3, 'Meeting', 'meeting', 'dasffaff', 5, 'Kempinski Hotel Gold Coast City', 'CI-1785-2738, MZ/J 35. Gomoa Eshiem', '', 'http://app.eventic.com/uploads/banners/events/banner_6942f01337f67_1765994515.png', '2025-12-18 05:42:00', '2025-12-25 20:42:00', 'published', 0, 10.00, 'Everyone', 'English', '[\"fdf\",\"adfas\",\"err\",\"gadf\"]', 'www.melekglobalconsult.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233541436414', 'https://youtu.be/zCsQ6ILLrrw?si=pQLwD1Gmr1-oRgUR', '2025-12-17 17:42:29', '2025-12-23 14:11:30', 'Ghana', 'California', 'Gomoa Eshiem', 0),
(20, 3, 'Updated Omnivest Educational Consult Updated ', 'updated-omnivest-educational-consult-updated-', 'qwertyuiopplkjhgfdsazxcvbnm', 1, 'New Auditorium AAMUSTED', 'CI-1785-2738, MZ/J 35. Gomoa Eshiem MZ/J 35', 'https://maps.app.goo.gl/dbMHMt3M38mxDFBn6', 'http://app.eventic.com/uploads/banners/events/banner_6942f159dc129_1765994841.png', '2025-12-15 18:05:00', '2025-12-20 20:05:00', 'published', 0, 10.00, 'Students', 'English', '[\"qwer\",\"tyu\",\"iop\",\"asdf\",\"ghj\",\"kl;\",\"zxcv\",\"bnm\"]', 'www.ghanasmostbeautiful.com', 'https://facebook.com/summerfest', 'https://twitter.com/summerfest', 'https://instagram.com/summerfest', '+233541436414', 'https://youtu.be/zCsQ6ILLrrw?si=pQLwD1Gmr1-oRgUR', '2025-12-17 19:07:21', '2025-12-23 14:11:24', 'Ghana', 'California', 'Gomoa Eshiem', 0);

-- --------------------------------------------------------

--
-- Table structure for table `event_images`
--

DROP TABLE IF EXISTS `event_images`;
CREATE TABLE IF NOT EXISTS `event_images` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_images`
--

INSERT INTO `event_images` (`id`, `event_id`, `image_path`, `created_at`, `updated_at`) VALUES
(1, 1, 'https://images.unsplash.com/photo-1470229722913-7c0e2dbbafd3?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(2, 1, 'https://images.unsplash.com/photo-1429962714451-bb934ecdc4ec?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(3, 2, 'https://images.unsplash.com/photo-1459865264687-595d652de67e?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(4, 3, 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(5, 3, 'https://images.unsplash.com/photo-1591115765373-5207764f72e7?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(6, 4, 'https://images.unsplash.com/photo-1511192336575-5a79af67a629?w=800&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(7, 5, 'https://images.unsplash.com/photo-1561214115-f2f134cc4912?w=800&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(8, 6, 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=800&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(9, 7, 'https://images.unsplash.com/photo-1585699324551-f6c309eedeca?w=800&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47'),
(10, 8, 'https://images.unsplash.com/photo-1452626038306-9aae5e071dd3?w=1920&q=80', '2025-12-06 19:47:47', '2025-12-06 19:47:47');

-- --------------------------------------------------------

--
-- Table structure for table `event_reviews`
--

DROP TABLE IF EXISTS `event_reviews`;
CREATE TABLE IF NOT EXISTS `event_reviews` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `reviewer_id` int(11) UNSIGNED NOT NULL,
  `rating` int(11) NOT NULL DEFAULT 0,
  `comment` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `reviewer_id` (`reviewer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `event_types`
--

DROP TABLE IF EXISTS `event_types`;
CREATE TABLE IF NOT EXISTS `event_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `event_types`
--

INSERT INTO `event_types` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Concert / Music', 'concert-music', 'Live music performances, concerts, and music festivals', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(2, 'Sport / Fitness', 'sport-fitness', 'Sports events, matches, fitness activities', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(3, 'Theater / Arts', 'theater-arts', 'Theater performances, art exhibitions, cultural events', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(4, 'Food & Drink', 'food-drink', 'Food festivals, wine tastings, culinary events', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(5, 'Conference', 'conference', 'Business conferences, seminars, professional events', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(6, 'Cinema', 'cinema', 'Film screenings, movie premieres, film festivals', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(7, 'Entertainment', 'entertainment', 'Comedy shows, variety performances, entertainment events', '2025-12-05 23:40:20', '2025-12-05 23:40:20'),
(8, 'Workshop', 'workshop', 'Hands-on workshops, training sessions, skill-building events', '2025-12-05 23:40:20', '2025-12-05 23:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
CREATE TABLE IF NOT EXISTS `orders` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','paid','failed','refunded','cancelled') DEFAULT 'pending',
  `payment_reference` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `subtotal` decimal(10,2) DEFAULT 0.00,
  `fees` decimal(10,2) DEFAULT 0.00,
  `customer_email` varchar(255) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_amount`, `status`, `payment_reference`, `created_at`, `updated_at`, `subtotal`, `fees`, `customer_email`, `customer_name`, `customer_phone`, `paid_at`) VALUES
(1, 6, 203.00, 'pending', 'EVT-1-1765011530', '2025-12-06 09:58:50', '2025-12-06 09:58:50', 200.00, 3.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, NULL),
(2, 6, 203.00, 'pending', 'EVT-2-1765012856', '2025-12-06 10:20:56', '2025-12-06 10:20:56', 200.00, 3.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, NULL),
(3, 6, 203.00, 'pending', 'EVT-3-1765013226', '2025-12-06 10:27:05', '2025-12-06 10:27:06', 200.00, 3.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, NULL),
(4, 6, 203.00, 'paid', 'EVT-4-1765020155', '2025-12-06 12:22:35', '2025-12-06 12:33:35', 200.00, 3.00, 'client@example.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-06 12:33:35'),
(5, 6, 324.80, 'paid', 'EVT-5-1765020998', '2025-12-06 12:36:38', '2025-12-06 12:36:51', 320.00, 4.80, 'client@example.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-06 12:36:51'),
(6, 6, 812.00, 'paid', 'EVT-6-1765023551', '2025-12-06 13:19:11', '2025-12-06 13:19:33', 800.00, 12.00, 'client@example.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-06 13:19:33'),
(7, 6, 812.00, 'paid', 'EVT-7-1765023887', '2025-12-06 13:24:47', '2025-12-06 13:25:18', 800.00, 12.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, '2025-12-06 13:25:18'),
(8, 6, 609.00, 'paid', 'EVT-8-1765024469', '2025-12-06 13:34:29', '2025-12-06 13:34:52', 600.00, 9.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, '2025-12-06 13:34:52'),
(9, 6, 812.00, 'cancelled', 'EVT-9-1765024606', '2025-12-06 13:36:46', '2025-12-06 13:43:25', 800.00, 12.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, NULL),
(10, 6, 812.00, 'paid', 'EVT-10-1765025148', '2025-12-06 13:45:47', '2025-12-06 13:46:27', 800.00, 12.00, 'client@example.com', 'Gilbert Elikplim Kukah', NULL, '2025-12-06 13:46:27'),
(11, 6, 5582.50, 'paid', 'EVT-11-1765037590', '2025-12-06 17:13:10', '2025-12-06 17:13:42', 5500.00, 82.50, 'client@example.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-06 17:13:42'),
(12, 6, 1816.85, 'paid', 'EVT-12-1765043993', '2025-12-06 18:59:53', '2025-12-06 19:01:58', 1790.00, 26.85, 'client@example.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-06 19:01:58'),
(13, 7, 101.50, 'paid', 'EVT-13-1765050748', '2025-12-06 20:52:28', '2025-12-06 20:52:43', 100.00, 1.50, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', NULL, '2025-12-06 20:52:43'),
(14, 7, 1268.75, 'paid', 'EVT-14-1765051255', '2025-12-06 21:00:55', '2025-12-06 21:01:10', 1250.00, 18.75, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', NULL, '2025-12-06 21:01:10'),
(15, 7, 456.75, 'pending', 'EVT-15-1765118452', '2025-12-07 15:40:51', '2025-12-07 15:40:52', 450.00, 6.75, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', '0541436414', NULL),
(16, 7, 1294.13, 'paid', 'EVT-16-1765122454', '2025-12-07 16:47:34', '2025-12-07 16:48:01', 1275.00, 19.13, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-07 16:48:01'),
(17, 7, 253.75, 'pending', 'EVT-17-1765122919', '2025-12-07 16:55:19', '2025-12-07 16:55:19', 250.00, 3.75, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', NULL, NULL),
(18, 7, 3045.00, 'paid', 'EVT-18-1765406833', '2025-12-10 23:47:13', '2025-12-11 00:00:23', 3000.00, 45.00, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', '0246706020', '2025-12-11 00:00:23'),
(19, 7, 1999.55, 'paid', 'EVT-19-1766161636', '2025-12-19 17:27:16', '2025-12-19 17:27:41', 1970.00, 29.55, 'kwamegilbert1114@gmail.com', 'Gilbert Elikplim Kukah', '0541436414', '2025-12-19 17:27:41');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
CREATE TABLE IF NOT EXISTS `order_items` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL,
  `ticket_type_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `total_price` decimal(10,2) DEFAULT NULL,
  `admin_share_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `admin_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `organizer_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `event_id` (`event_id`),
  KEY `ticket_type_id` (`ticket_type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `event_id`, `ticket_type_id`, `quantity`, `unit_price`, `total_price`, `admin_share_percent`, `admin_amount`, `organizer_amount`, `payment_fee`, `created_at`, `updated_at`) VALUES
(17, 13, 8, 24, 1, 100.00, 100.00, 0.00, 0.00, 0.00, 0.00, '2025-12-06 20:52:28', '2025-12-06 20:52:28'),
(18, 14, 3, 8, 5, 250.00, 1250.00, 0.00, 0.00, 0.00, 0.00, '2025-12-06 21:00:55', '2025-12-06 21:00:55'),
(19, 15, 4, 13, 1, 450.00, 450.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 15:40:51', '2025-12-07 15:40:51'),
(20, 16, 4, 13, 1, 450.00, 450.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 16:47:34', '2025-12-07 16:47:34'),
(21, 16, 3, 8, 1, 250.00, 250.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 16:47:34', '2025-12-07 16:47:34'),
(22, 16, 3, 9, 1, 500.00, 500.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 16:47:34', '2025-12-07 16:47:34'),
(23, 16, 3, 10, 1, 75.00, 75.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 16:47:34', '2025-12-07 16:47:34'),
(24, 17, 3, 8, 1, 250.00, 250.00, 0.00, 0.00, 0.00, 0.00, '2025-12-07 16:55:19', '2025-12-07 16:55:19'),
(25, 18, 14, 31, 6, 500.00, 3000.00, 0.00, 0.00, 0.00, 0.00, '2025-12-10 23:47:13', '2025-12-10 23:47:13'),
(26, 19, 3, 8, 1, 250.00, 250.00, 10.00, 21.25, 225.00, 3.75, '2025-12-19 17:27:16', '2025-12-19 17:27:16'),
(27, 19, 4, 13, 2, 450.00, 900.00, 10.00, 76.50, 810.00, 13.50, '2025-12-19 17:27:16', '2025-12-19 17:27:16'),
(28, 19, 4, 12, 2, 250.00, 500.00, 10.00, 42.50, 450.00, 7.50, '2025-12-19 17:27:16', '2025-12-19 17:27:16'),
(29, 19, 4, 11, 4, 80.00, 320.00, 10.00, 27.20, 288.00, 4.80, '2025-12-19 17:27:16', '2025-12-19 17:27:16');

-- --------------------------------------------------------

--
-- Table structure for table `organizers`
--

DROP TABLE IF EXISTS `organizers`;
CREATE TABLE IF NOT EXISTS `organizers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `organization_name` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `social_facebook` varchar(255) DEFAULT NULL,
  `social_instagram` varchar(255) DEFAULT NULL,
  `social_twitter` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizers`
--

INSERT INTO `organizers` (`id`, `user_id`, `organization_name`, `bio`, `profile_image`, `social_facebook`, `social_instagram`, `social_twitter`, `created_at`, `updated_at`) VALUES
(1, 2, 'EventPro Ghana', 'Premier event organizers in Ghana. We create unforgettable experiences through world-class entertainment, corporate events, and community gatherings.', 'https://ui-avatars.com/api/?name=EventPro+Ghana&background=FF6B35&color=fff&size=200', 'https://facebook.com/eventproghana', 'https://instagram.com/eventprogh', 'https://twitter.com/eventprogh', '2025-12-06 19:47:46', '2025-12-06 19:47:46'),
(2, 3, 'Live Nation Africa', 'Africas leading live entertainment company, producing music festivals and concerts across the continent. Bringing global artists to African stages since 2015.', 'https://ui-avatars.com/api/?name=Live+Nation&background=E91E63&color=fff&size=200', 'https://facebook.com/livenationafrica', 'https://instagram.com/livenationafrica', 'https://twitter.com/livenationafr', '2025-12-06 19:47:46', '2025-12-06 19:47:46'),
(3, 8, 'TechHub Accra', 'Ghanas premier technology community. Organizing tech conferences, workshops, and networking events to foster innovation and digital transformation.', 'https://ui-avatars.com/api/?name=TechHub+Accra&background=673AB7&color=fff&size=200', 'https://facebook.com/techhubaccra', 'https://instagram.com/techhubaccra', 'https://twitter.com/techhubaccra', '2025-12-06 19:47:46', '2025-12-06 21:51:53'),
(4, 7, 'Gilbert Elikplim Kukah\'s Organization', NULL, NULL, NULL, NULL, NULL, '2025-12-23 11:24:21', '2025-12-23 11:24:21');

-- --------------------------------------------------------

--
-- Table structure for table `organizer_balances`
--

DROP TABLE IF EXISTS `organizer_balances`;
CREATE TABLE IF NOT EXISTS `organizer_balances` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `available_balance` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Ready for withdrawal',
  `pending_balance` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Within hold period',
  `total_earned` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Lifetime earnings',
  `total_withdrawn` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'Total payouts completed',
  `last_payout_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `organizer_id` (`organizer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `organizer_balances`
--

INSERT INTO `organizer_balances` (`id`, `organizer_id`, `available_balance`, `pending_balance`, `total_earned`, `total_withdrawn`, `last_payout_at`, `created_at`, `updated_at`) VALUES
(1, 3, 0.00, 269.20, 269.20, 0.00, NULL, '2025-12-19 17:27:42', '2025-12-23 14:56:08'),
(2, 1, 0.00, 1548.00, 1548.00, 0.00, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `organizer_followers`
--

DROP TABLE IF EXISTS `organizer_followers`;
CREATE TABLE IF NOT EXISTS `organizer_followers` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `follower_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `organizer_id` (`organizer_id`),
  KEY `follower_id` (`follower_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `email` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  KEY `email` (`email`,`token`),
  KEY `created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payout_requests`
--

DROP TABLE IF EXISTS `payout_requests`;
CREATE TABLE IF NOT EXISTS `payout_requests` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(10) UNSIGNED DEFAULT NULL,
  `award_id` int(11) UNSIGNED DEFAULT NULL,
  `payout_type` enum('event','award') NOT NULL DEFAULT 'event',
  `amount` decimal(10,2) DEFAULT 0.00,
  `gross_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `admin_fee` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('bank_transfer','mobile_money') NOT NULL DEFAULT 'bank_transfer',
  `account_number` varchar(255) NOT NULL,
  `account_name` varchar(255) NOT NULL,
  `bank_name` varchar(255) DEFAULT NULL,
  `status` enum('pending','processing','completed','rejected') NOT NULL DEFAULT 'pending',
  `processed_by` int(11) UNSIGNED DEFAULT NULL,
  `processed_at` timestamp NULL DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `organizer_id` (`organizer_id`),
  KEY `event_id` (`event_id`),
  KEY `fk_payout_award` (`award_id`),
  KEY `fk_payout_processor` (`processed_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `phinxlog`
--

DROP TABLE IF EXISTS `phinxlog`;
CREATE TABLE IF NOT EXISTS `phinxlog` (
  `version` bigint(20) NOT NULL,
  `migration_name` varchar(100) DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20251128122908, 'InitialSchema', '2025-11-28 15:43:40', '2025-11-28 15:43:43', 0),
(20251206173345, 'CompleteSchema', '2025-12-06 19:17:04', '2025-12-06 19:17:04', 0),
(20251207101500, 'AddSocialsToEvents', '2025-12-07 12:25:55', '2025-12-07 12:25:55', 0),
(20251208151919, 'AddCompletedToEventStatus', '2025-12-08 16:44:56', '2025-12-08 16:44:57', 0),
(20251209113543, 'AddTicketDescription', '2025-12-09 12:37:02', '2025-12-09 12:37:02', 0),
(20251209114402, 'AddMissingEventColumns', '2025-12-09 13:00:32', '2025-12-09 13:00:32', 0),
(20251209134512, 'AddEventViews', '2025-12-09 14:48:46', '2025-12-09 14:48:46', 0),
(20251213160700, 'AddEventFormatColumn', '2025-12-13 17:13:23', '2025-12-13 17:13:24', 0),
(20251213160800, 'CreateAwardsTables', '2025-12-14 07:58:49', '2025-12-14 07:58:50', 0),
(20251214065054, 'SeparateAwardsFromEvents', '2025-12-14 08:04:15', '2025-12-14 08:04:16', 0),
(20251215013245, 'AddShowResultsColumnToAwards', '2025-12-15 06:51:08', '2025-12-15 06:51:09', 0),
(20251217183504, 'AddEventPenndingStatus', '2025-12-17 19:38:44', '2025-12-17 19:38:45', 0),
(20251217184625, 'AddAwardPenndingStatus', '2025-12-17 19:46:51', '2025-12-17 19:46:51', 0),
(20251219124232, 'AddFinanceTracking', '2025-12-19 13:51:16', '2025-12-19 13:51:19', 0);

-- --------------------------------------------------------

--
-- Table structure for table `platform_settings`
--

DROP TABLE IF EXISTS `platform_settings`;
CREATE TABLE IF NOT EXISTS `platform_settings` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  `setting_type` enum('string','number','boolean','json') NOT NULL DEFAULT 'string',
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `platform_settings`
--

INSERT INTO `platform_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_at`) VALUES
(1, 'default_event_admin_share', '10', 'number', 'Default admin share % for new events', '2025-12-19 12:51:18', '2025-12-19 12:51:18'),
(2, 'default_award_admin_share', '15', 'number', 'Default admin share % for new awards', '2025-12-19 12:51:18', '2025-12-19 12:51:18'),
(3, 'payout_hold_days', '7', 'number', 'Days after event/voting ends before payout eligibility', '2025-12-19 12:51:18', '2025-12-19 12:51:18'),
(4, 'min_payout_amount', '10', 'number', 'Minimum amount (GHS) required for payout request', '2025-12-19 12:51:18', '2025-12-19 17:16:30'),
(5, 'paystack_fee_percent', '1.5', 'number', 'Paystack transaction fee percentage', '2025-12-19 12:51:18', '2025-12-19 12:51:18');

-- --------------------------------------------------------

--
-- Table structure for table `pos_assignments`
--

DROP TABLE IF EXISTS `pos_assignments`;
CREATE TABLE IF NOT EXISTS `pos_assignments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `event_id` int(11) DEFAULT NULL,
  `organizer_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
CREATE TABLE IF NOT EXISTS `refresh_tokens` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `device_name` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `expires_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revoked` tinyint(1) DEFAULT 0,
  `revoked_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  KEY `revoked` (`revoked`)
) ENGINE=InnoDB AUTO_INCREMENT=83 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `refresh_tokens`
--

INSERT INTO `refresh_tokens` (`id`, `user_id`, `token_hash`, `device_name`, `ip_address`, `user_agent`, `expires_at`, `revoked`, `revoked_at`, `created_at`, `updated_at`) VALUES
(19, 6, 'a347a7d4da9031a5ee0f5fb718677d9a0479801051e3ff8034425e331615afc0', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:27:57', 0, NULL, '2025-12-05 18:27:57', '2025-12-05 18:27:57'),
(20, 6, 'b05f9ea30732dff56ee266fd092b0c01a7a7e4ba713942eed49ac4db99e8cd36', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:37:33', 0, NULL, '2025-12-05 18:37:33', '2025-12-05 18:37:33'),
(21, 6, 'b31fbbc1f28e67b550d01cc24123f3cf5a80fdff8dd0fa888b734eeb9ea5daca', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:42:56', 0, NULL, '2025-12-05 18:42:56', '2025-12-05 18:42:56'),
(22, 6, '6c1c31e64d24bae31b6e66bbfb22e2531ece6441793baf2e96c542fb8e4d5b6b', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:45:59', 0, NULL, '2025-12-05 18:45:59', '2025-12-05 18:45:59'),
(23, 6, '112c8edabe593b064878819f9cb93b424ddcedc0a450a1f021b5c160b79c4e42', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:50:48', 0, NULL, '2025-12-05 18:50:48', '2025-12-05 18:50:48'),
(24, 6, '5ebe4fdb48a9a6c6f5076bf3942f5e347962a426513e97849df7266b9dce1443', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:52:15', 0, NULL, '2025-12-05 18:52:15', '2025-12-05 18:52:15'),
(25, 6, 'e67926931308470dbfa788866f3714fd571301efdd78bad31e01f8a88628502f', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:54:11', 0, NULL, '2025-12-05 18:54:11', '2025-12-05 18:54:11'),
(26, 6, '2534d9de23621554883f5506a900cfdb80591afca822bc7cb20a42dc8a66961d', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 18:57:52', 0, NULL, '2025-12-05 18:57:52', '2025-12-05 18:57:52'),
(27, 6, '3a9f5aaa91d7b130e1ee94a6bd50ee4b74abd278e1748e85cfcac40014f206be', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 19:02:27', 0, NULL, '2025-12-05 19:02:27', '2025-12-05 19:02:27'),
(28, 6, 'a786d576da7c9e0d108775e180c26983161ff8010ca29d8469b13ed828a4170e', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 19:19:01', 0, NULL, '2025-12-05 19:19:01', '2025-12-05 19:19:01'),
(29, 6, '0b751f5911b1c3016ae29fe7715934a89e15feaf9f8e86796deece8b320f01f1', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 20:09:52', 0, NULL, '2025-12-05 20:09:52', '2025-12-05 20:09:52'),
(30, 6, '92fc02e00df8235133ae9f93e0be3dd201d62df71eebc46c1ed7ec007e3e6d7c', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 20:16:45', 0, NULL, '2025-12-05 20:16:45', '2025-12-05 20:16:45'),
(31, 6, '31ada19e43dbd50bab676a4604b2e2b77c2fd8fa56fd116109778b5a03e92bad', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-12 22:43:37', 0, NULL, '2025-12-05 22:43:37', '2025-12-05 22:43:37'),
(32, 6, 'd06b88bfe2fce4843bfe4add3fba026565316c2b06410e4a62e24ffc11d30504', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 01:20:39', 0, NULL, '2025-12-06 01:20:39', '2025-12-06 01:20:39'),
(33, 6, '21a563fcf018f0ea0c747d56452f1d0c97cc8240f31c23e82bb1bcbbb7823ddb', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 08:18:51', 0, NULL, '2025-12-06 08:18:51', '2025-12-06 08:18:51'),
(34, 6, 'c1cc0ce14eeaf21828b9f64e75b4a0bdd0ca36dba46f80d99e3bcadf4fcb467a', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 08:40:09', 0, NULL, '2025-12-06 08:40:09', '2025-12-06 08:40:09'),
(35, 6, '55adec1a32bf6a1f5b0d202f5438662999226afc88f7e40e3f77b0669cbc3953', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:04:58', 0, NULL, '2025-12-06 09:04:58', '2025-12-06 09:04:58'),
(36, 6, 'cc2c0852af893a64db98763592f4debe474e76442ec26cc3c6f7aecdb2831f4e', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:08:40', 0, NULL, '2025-12-06 09:08:40', '2025-12-06 09:08:40'),
(37, 6, 'e1da22111dc32479b32438cc36cc7ad7e7d6ac72248169f22c9350c5b9004e65', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:12:24', 0, NULL, '2025-12-06 09:12:24', '2025-12-06 09:12:24'),
(38, 6, '8a3c93ba75b9ba3dde593ae68f650eedfcc1a3b510e8d3cd6e5151ad6c3435d8', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:22:38', 0, NULL, '2025-12-06 09:22:38', '2025-12-06 09:22:38'),
(39, 6, '746ccec89878924ed3ac38e582ebf5067a96a7ba1b92a457db348e9820a476fa', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:31:24', 0, NULL, '2025-12-06 09:31:24', '2025-12-06 09:31:24'),
(40, 6, '42779ec1387cd22295481deee2c5d664f3d56d8cff38069528da8201dcd19146', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:39:48', 0, NULL, '2025-12-06 09:39:48', '2025-12-06 09:39:48'),
(41, 6, 'f9931db433e63151792d37e7f97ba16b4002e198ca285f68157b9ff860043958', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:54:37', 0, NULL, '2025-12-06 09:54:37', '2025-12-06 09:54:37'),
(42, 6, '547307386421ba0a4bd7d610aa891da63a54edaebf0bf28cc50f85da5a81d229', '', '::1', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Mobile Safari/537.36', '2025-12-13 09:57:35', 0, NULL, '2025-12-06 09:57:35', '2025-12-06 09:57:35'),
(43, 6, '9a6647f690dbda4db3ff4f1b8c4ed84f83a357c2b1870f889ad2673a3dbef7a4', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 12:20:56', 0, NULL, '2025-12-06 12:20:56', '2025-12-06 12:20:56'),
(44, 6, '67110cb63915074231443711180846d2ab31164220e430386b81fd8f3fa9d152', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 12:21:25', 0, NULL, '2025-12-06 12:21:25', '2025-12-06 12:21:25'),
(45, 6, '1f627fac877aa12059e30e2ff3b4b6776a062f2b2c38eb24439f6f4772082b11', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 13:23:34', 0, NULL, '2025-12-06 13:23:34', '2025-12-06 13:23:34'),
(46, 6, '02e9ee94fb3be885e13f3af3af02dbf63dcc5c6f7495795509bee9cc0ffb39bb', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 15:18:11', 0, NULL, '2025-12-06 15:18:11', '2025-12-06 15:18:11'),
(47, 6, '217434b5b711074661f1a9c9541ac6f117191f927fcad20bd4f46386526975b7', '', '127.0.0.1', 'Mozilla/5.0 (iPhone; CPU iPhone OS 16_0 like Mac OS X) AppleWebKit/603.1.30 (KHTML, like Gecko) Version/17.5 Mobile/15A5370a Safari/602.1', '2025-12-13 16:49:58', 0, NULL, '2025-12-06 16:49:58', '2025-12-06 16:49:58'),
(48, 6, '41d261e068b325be554bcdf0475d9449a654f2af65636380b49bccca2ea7c58b', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-06 18:15:48', 1, '2025-12-06 19:15:48', '2025-12-06 18:57:12', '2025-12-06 19:15:48'),
(49, 6, '2f58e8da99c2d759d0d2042cb89704693db65ee2c374d01d6a08e1f0b28f83f6', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 19:16:26', 0, NULL, '2025-12-06 19:16:26', '2025-12-06 19:16:26'),
(50, 7, '6a1ebae04275613135013fc73174e8533c705d67bb3d75bf97a68f3ea540e670', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-06 20:02:17', 1, '2025-12-06 21:02:17', '2025-12-06 20:42:53', '2025-12-06 21:02:17'),
(51, 8, '4aed225f9e9d3389cc3a1ef744598bc770afd10ba40e9ff0c367e7554f54af9e', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 21:05:11', 0, NULL, '2025-12-06 21:05:11', '2025-12-06 21:05:11'),
(52, 8, '52eda37b31520fa1e687a08327869a4531f68c57355b457fb21807481308fd8c', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-06 21:56:03', 1, '2025-12-06 22:56:03', '2025-12-06 22:28:14', '2025-12-06 22:56:03'),
(53, 7, 'd763641d5074956088d1ac33f476ff32e5f77163be63f184ece501f58e6eee25', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-06 21:56:53', 1, '2025-12-06 22:56:53', '2025-12-06 22:56:44', '2025-12-06 22:56:53'),
(54, 8, 'ad2fd124ab9431d1eb00565195439e2d0b8d87a157525fd97be8d544db68bbe9', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-13 22:56:59', 0, NULL, '2025-12-06 22:56:59', '2025-12-06 22:56:59'),
(55, 8, '83b96ef7b094f628ba2cecb97b92132f90bb982ebce1848f733f1a4677774333', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 07:51:21', 0, NULL, '2025-12-07 07:51:21', '2025-12-07 07:51:21'),
(56, 8, '3c4f7d43911c2662aa4ddb3a09402e499c5bb2164a85a925b4b65271ed84a0f7', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 09:03:33', 0, NULL, '2025-12-07 09:03:33', '2025-12-07 09:03:33'),
(57, 8, '1f6863b0632437cb3989c5928d86bfdeea80bdc508d9451112409f17aeaa6a02', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 10:06:02', 0, NULL, '2025-12-07 10:06:02', '2025-12-07 10:06:02'),
(58, 8, 'bd25dd532bb3e900c192ad7f556d2870e4be3910aaec2e3dcdfb7cd36c4dd5f9', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 13:40:56', 0, NULL, '2025-12-07 13:40:56', '2025-12-07 13:40:56'),
(59, 8, '6c6c602979c066435fbf05fef1e0761e72f41799a967f604a5a7ddf8a984d252', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 14:39:06', 1, '2025-12-07 15:39:06', '2025-12-07 15:38:06', '2025-12-07 15:39:06'),
(60, 7, '1f422efdf4d64947f1b30d6120a65f80db54897ee5516124ba41b23200e86968', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 14:41:30', 1, '2025-12-07 15:41:30', '2025-12-07 15:40:18', '2025-12-07 15:41:30'),
(61, 8, '4035176bc57fa95ed0e003d0e0cdbb1dcb10e548d8d8d199caa309aa5391cdc0', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 15:41:57', 0, NULL, '2025-12-07 15:41:57', '2025-12-07 15:41:57'),
(62, 8, 'fd695ca5cc488e6728851694aab66d10f2376de5ee1f1fccdb4317e7ca88b9de', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:46:51', 1, '2025-12-07 16:46:51', '2025-12-07 16:46:15', '2025-12-07 16:46:51'),
(63, 7, '508fd294e98bc15ae29b23d21cbe18999a9eaa9dbcb237d32c2299cb09336707', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:48:39', 1, '2025-12-07 16:48:39', '2025-12-07 16:46:57', '2025-12-07 16:48:39'),
(64, 8, 'a154ac0d8fe34eff03a367931addc51352ceeff1698149c9efb8828993b4c5ce', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:54:25', 1, '2025-12-07 16:54:25', '2025-12-07 16:48:49', '2025-12-07 16:54:25'),
(65, 7, 'fcc9a953ede5472a52abdaa07b4f99f6e3aef7fe0636e60a7406a98c164c0a7c', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-07 15:55:32', 1, '2025-12-07 16:55:32', '2025-12-07 16:54:37', '2025-12-07 16:55:32'),
(66, 8, '59efea57b94a9977dc57cc42dc0f87a661ae352e02dec57c054da1dd91b934df', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 16:55:51', 0, NULL, '2025-12-07 16:55:51', '2025-12-07 16:55:51'),
(67, 8, '4d08107d259c7da87b8b2673ae782dc00308f665228b50d10bfd86114c1c806d', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-15 07:05:02', 0, NULL, '2025-12-08 07:05:02', '2025-12-08 07:05:02'),
(68, 8, '94e375d9bc5579da5cb9f6afdd99530934eb5158e494a41f4bad19b46d80dfbb', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-15 14:25:56', 0, NULL, '2025-12-08 14:25:56', '2025-12-08 14:25:56'),
(69, 8, '96e52504f325c56b16f140ee397af1144775bc365413b301db54dd0a127fd221', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-15 15:34:29', 0, NULL, '2025-12-08 15:34:29', '2025-12-08 15:34:29'),
(70, 8, '92c6c92111e7e6f44368663b6af4eac3cbf8a30e4480149e66b6ab1d99a6acec', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-15 17:17:15', 0, NULL, '2025-12-08 17:17:15', '2025-12-08 17:17:15'),
(71, 8, '7153891c792e966d29e95f61231a88ae9335c004c0a4a6487b591a9b4bc4371f', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-10 22:36:40', 1, '2025-12-10 23:36:40', '2025-12-09 11:23:47', '2025-12-10 23:36:40'),
(72, 8, 'af39150e6ad0b455dcf1f0bbf620f70d3e57038da2e258d26872536515aae95a', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-14 22:03:29', 1, '2025-12-14 23:03:14', '2025-12-10 23:37:10', '2025-12-14 23:03:29'),
(73, 7, '7ecb8e4393233a5c04ddc8c9d06b90804d8f1484b5e8eda0a2e5107ed2846929', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-12-17 23:37:12', 0, NULL, '2025-12-10 23:37:12', '2025-12-10 23:37:12'),
(74, 8, '39e89ef1cde06520ebf331ce5b4086543dd4d61866ab324457379307fe4c4edc', '', '127.0.0.1', 'Mozilla/5.0 (Linux; Android 13; SM-G981B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Mobile Safari/537.36', '2025-12-19 16:25:19', 1, '2025-12-19 17:25:19', '2025-12-15 10:07:40', '2025-12-19 17:25:19'),
(75, 7, '09ffdaab2149e342224362d694cd05d295a6f43c76db2914b6748089310721da', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-19 16:33:13', 1, '2025-12-19 17:33:13', '2025-12-19 17:26:31', '2025-12-19 17:33:13'),
(76, 8, 'eba3935c1a6f0aa0c11df389e771fcef67b4cb35585c662265819f2289bcdff8', '', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-26 17:40:02', 0, NULL, '2025-12-19 17:40:02', '2025-12-19 17:40:02'),
(77, 6, '4adc0a8a722fbea13403a436e231f111b2dd4e0127d255b45ede3196d07c54dc', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 09:42:50', 1, '2025-12-23 10:42:50', '2025-12-23 10:40:31', '2025-12-23 10:42:50'),
(78, 6, 'a1ef30f37f079d93a9033d90fe8e70f50cb9daa4c4f9245a0ac22ec412b10f63', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 13:14:43', 1, '2025-12-23 14:14:42', '2025-12-23 10:43:06', '2025-12-23 14:14:43'),
(79, 8, 'b6028bab018e99b16f4cccf0d1cae680c43a8206a87e59150c1eb7f70c9f3c87', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 13:58:39', 1, '2025-12-23 14:58:39', '2025-12-23 14:15:28', '2025-12-23 14:58:39'),
(80, 8, 'e152b3c347de8e8d17f7d220cd90c289bbf1ec4f4d4ddb674b8b0afa63722595', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 14:01:32', 1, '2025-12-23 15:01:32', '2025-12-23 14:59:12', '2025-12-23 15:01:32'),
(81, 8, '19b83095e6a8f671168373e97ed24390af06dc0920f643c76b10b5966825d202', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 14:06:43', 1, '2025-12-23 15:06:43', '2025-12-23 15:05:29', '2025-12-23 15:06:43'),
(82, 8, '89117ba95ba275e81ec12be3114774072612bf230f0efdc1510d75a3b23f3bb0', '', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2025-12-23 14:15:14', 1, '2025-12-23 15:15:14', '2025-12-23 15:10:44', '2025-12-23 15:15:14');

-- --------------------------------------------------------

--
-- Table structure for table `scanner_assignments`
--

DROP TABLE IF EXISTS `scanner_assignments`;
CREATE TABLE IF NOT EXISTS `scanner_assignments` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `event_id` (`event_id`),
  KEY `organizer_id` (`organizer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` int(11) UNSIGNED NOT NULL,
  `event_id` int(11) UNSIGNED NOT NULL,
  `ticket_type_id` int(11) UNSIGNED NOT NULL,
  `ticket_code` varchar(255) NOT NULL,
  `status` enum('active','used','cancelled') DEFAULT 'active',
  `admitted_by` int(11) UNSIGNED DEFAULT NULL,
  `admitted_at` timestamp NULL DEFAULT NULL,
  `attendee_id` int(11) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `ticket_code` (`ticket_code`),
  KEY `order_id` (`order_id`),
  KEY `event_id` (`event_id`),
  KEY `ticket_type_id` (`ticket_type_id`),
  KEY `attendee_id` (`attendee_id`),
  KEY `admitted_by` (`admitted_by`)
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `order_id`, `event_id`, `ticket_type_id`, `ticket_code`, `status`, `admitted_by`, `admitted_at`, `attendee_id`, `created_at`, `updated_at`) VALUES
(44, 13, 8, 24, 'E21459744F', 'active', NULL, NULL, NULL, '2025-12-06 20:52:44', '2025-12-06 20:52:44'),
(45, 14, 3, 8, 'F66F36BE03', 'active', NULL, NULL, NULL, '2025-12-06 21:01:10', '2025-12-06 21:01:10'),
(46, 14, 3, 8, '3FB8DDA84C', 'active', NULL, NULL, NULL, '2025-12-06 21:01:10', '2025-12-06 21:01:10'),
(47, 14, 3, 8, 'AA48B1A25F', 'active', NULL, NULL, NULL, '2025-12-06 21:01:10', '2025-12-06 21:01:10'),
(48, 14, 3, 8, 'BB600FEAB7', 'active', NULL, NULL, NULL, '2025-12-06 21:01:10', '2025-12-06 21:01:10'),
(49, 14, 3, 8, '5EF4DA4489', 'active', NULL, NULL, NULL, '2025-12-06 21:01:10', '2025-12-06 21:01:10'),
(50, 16, 4, 13, 'BCE5CAC43E', 'active', NULL, NULL, NULL, '2025-12-07 16:48:01', '2025-12-07 16:48:01'),
(51, 16, 3, 8, 'DA368BB601', 'active', NULL, NULL, NULL, '2025-12-07 16:48:01', '2025-12-07 16:48:01'),
(52, 16, 3, 9, '98BE3709BC', 'active', NULL, NULL, NULL, '2025-12-07 16:48:01', '2025-12-07 16:48:01'),
(53, 16, 3, 10, '8AD5E0B6C9', 'active', NULL, NULL, NULL, '2025-12-07 16:48:01', '2025-12-07 16:48:01'),
(54, 18, 14, 31, '693AAFE126', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(55, 18, 14, 31, 'A0717C5AAF', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(56, 18, 14, 31, '3398A1B3C0', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(57, 18, 14, 31, '86BFFFA991', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(58, 18, 14, 31, 'F888A820D8', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(59, 18, 14, 31, 'BF06C534E2', 'active', NULL, NULL, NULL, '2025-12-11 00:00:23', '2025-12-11 00:00:23'),
(60, 19, 3, 8, '5DCFE2CF0A', 'active', NULL, NULL, NULL, '2025-12-19 17:27:41', '2025-12-19 17:27:41'),
(61, 19, 4, 13, '440D4B4320', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(62, 19, 4, 13, '8C46A16C20', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(63, 19, 4, 12, '0491B01D78', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(64, 19, 4, 12, 'D7AAE4D06F', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(65, 19, 4, 11, '096562723A', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(66, 19, 4, 11, 'C30C842739', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(67, 19, 4, 11, '93DFA05794', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(68, 19, 4, 11, '3013A00F2C', 'active', NULL, NULL, NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43');

-- --------------------------------------------------------

--
-- Table structure for table `ticket_types`
--

DROP TABLE IF EXISTS `ticket_types`;
CREATE TABLE IF NOT EXISTS `ticket_types` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `event_id` int(11) UNSIGNED NOT NULL,
  `organizer_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `remaining` int(11) NOT NULL DEFAULT 0,
  `dynamic_fee` decimal(5,2) DEFAULT 0.00,
  `sale_start` datetime DEFAULT NULL,
  `sale_end` datetime DEFAULT NULL,
  `max_per_user` int(11) DEFAULT 10,
  `ticket_image` varchar(255) DEFAULT NULL,
  `status` enum('active','deactivated') NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_price` decimal(10,2) DEFAULT 0.00,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `organizer_id` (`organizer_id`),
  KEY `sale_start` (`sale_start`),
  KEY `sale_end` (`sale_end`)
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ticket_types`
--

INSERT INTO `ticket_types` (`id`, `event_id`, `organizer_id`, `name`, `price`, `quantity`, `remaining`, `dynamic_fee`, `sale_start`, `sale_end`, `max_per_user`, `ticket_image`, `status`, `created_at`, `updated_at`, `sale_price`, `description`) VALUES
(1, 1, 1, 'General Admission', 250.00, 5000, 4850, 2.50, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 6, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(2, 1, 1, 'VIP Pass', 500.00, 1000, 950, 5.00, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(3, 1, 1, 'VVIP Experience', 1000.00, 200, 195, 10.00, '2025-06-01 00:00:00', '2025-12-27 12:00:00', 2, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(4, 2, 1, 'Regular Stand', 30.00, 10000, 8500, 0.50, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 10, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(5, 2, 1, 'VIP Stand', 80.00, 2000, 1800, 1.00, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 6, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(6, 2, 1, 'Presidential Box', 200.00, 100, 95, 2.00, '2025-11-01 00:00:00', '2025-12-20 13:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(7, 3, 3, 'Early Bird', 150.00, 300, 50, 2.00, '2025-08-31 22:00:00', '2025-11-30 22:59:00', 3, 'http://app.eventic.com/uploads/tickets/ticket_693a8e07f0631_1765445127.png', 'active', '2025-12-06 19:47:46', '2025-12-11 10:25:28', 100.00, ''),
(8, 3, 3, 'Standard Pass', 250.00, 800, 742, 3.00, '2025-08-31 22:00:00', '2026-01-14 22:59:00', 5, 'http://app.eventic.com/uploads/tickets/ticket_693a8e0852d99_1765445128.png', 'active', '2025-12-06 19:47:46', '2025-12-19 17:27:16', 200.00, ''),
(9, 3, 3, 'VIP Package', 500.00, 150, 139, 5.00, '2025-08-31 22:00:00', '2026-01-14 22:59:00', 2, 'http://app.eventic.com/uploads/tickets/ticket_693a8e08598e6_1765445128.png', 'active', '2025-12-06 19:47:46', '2025-12-11 10:25:28', 200.09, ''),
(10, 3, 3, 'Student Pass', 75.00, 200, 179, 1.00, '2025-08-31 22:00:00', '2026-01-14 22:59:00', 1, 'http://app.eventic.com/uploads/tickets/ticket_693a8e085e372_1765445128.png', 'active', '2025-12-06 19:47:46', '2025-12-11 10:25:28', 50.10, ''),
(11, 4, 1, 'Regular', 80.00, 150, 136, 1.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-19 17:27:16', 0.00, NULL),
(12, 4, 1, 'VIP Table (2 seats)', 250.00, 30, 26, 3.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 2, NULL, 'active', '2025-12-06 19:47:46', '2025-12-19 17:27:16', 0.00, NULL),
(13, 4, 1, 'Premium Table (4 seats)', 450.00, 15, 10, 5.00, '2025-11-01 00:00:00', '2025-12-15 18:00:00', 1, NULL, 'active', '2025-12-06 19:47:46', '2025-12-19 17:27:16', 0.00, NULL),
(14, 5, 1, 'Day Pass', 20.00, 5000, 4800, 0.50, '2025-05-01 00:00:00', '2025-08-17 10:00:00', 10, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(15, 5, 1, 'Weekend Pass', 50.00, 2000, 1850, 1.00, '2025-05-01 00:00:00', '2025-08-15 08:00:00', 6, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(16, 5, 1, 'VIP Weekend', 150.00, 300, 290, 2.00, '2025-05-01 00:00:00', '2025-08-15 08:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(17, 6, 1, 'Standard Entry', 60.00, 800, 750, 1.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 5, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(18, 6, 1, 'VIP Tasting Pass', 150.00, 200, 185, 2.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 3, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(19, 6, 1, 'Masterclass Bundle', 250.00, 50, 45, 3.00, '2025-10-01 00:00:00', '2025-12-22 10:00:00', 2, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(20, 7, 1, 'Standard Seating', 50.00, 500, 450, 1.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 6, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(21, 7, 1, 'Front Row', 100.00, 100, 90, 2.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(22, 7, 1, 'VIP Lounge', 200.00, 40, 38, 3.00, '2025-11-01 00:00:00', '2025-12-18 18:00:00', 4, NULL, 'active', '2025-12-06 19:47:46', '2025-12-06 19:47:46', 0.00, NULL),
(23, 8, 1, '10K Fun Run', 50.00, 3000, 2800, 1.00, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, NULL, 'active', '2025-12-06 19:47:47', '2025-12-06 19:47:47', 0.00, NULL),
(24, 8, 1, 'Half Marathon', 100.00, 2000, 1849, 2.00, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, NULL, 'active', '2025-12-06 19:47:47', '2025-12-06 20:52:28', 0.00, NULL),
(25, 8, 1, 'Full Marathon', 150.00, 1000, 920, 2.50, '2025-11-01 00:00:00', '2026-03-14 23:59:00', 1, NULL, 'active', '2025-12-06 19:47:47', '2025-12-06 19:47:47', 0.00, NULL),
(26, 9, 3, 'VIP', 120.00, 20, 20, 0.00, '2025-12-12 13:34:00', '2025-12-16 13:34:00', 10, 'http://app.eventic.com/uploads/tickets/ticket_69398536bd6f6_1765377334.jpeg', 'active', '2025-12-07 09:40:08', '2025-12-17 19:03:31', 0.00, 'VVVVVVIP TICKET'),
(27, 10, 3, 'Regular', 100.00, 200, 200, 0.00, NULL, NULL, 10, NULL, 'active', '2025-12-07 10:55:04', '2025-12-07 10:55:04', 0.00, NULL),
(28, 11, 3, 'VIP', 33432.00, 222, 222, 0.00, NULL, NULL, 10, NULL, 'active', '2025-12-07 11:01:53', '2025-12-07 11:01:53', 0.00, NULL),
(29, 11, 3, 'Regular', 400.00, 12, 12, 0.00, NULL, NULL, 10, NULL, 'active', '2025-12-07 11:01:55', '2025-12-07 11:01:55', 0.00, NULL),
(30, 12, 3, 'Regular', 1000.00, 100, 100, 0.00, NULL, NULL, 10, 'http://app.eventic.com/uploads/tickets/ticket_6942dc630fe33_1765989475.png', 'active', '2025-12-08 14:45:41', '2025-12-17 17:37:55', 0.00, ''),
(31, 14, 3, 'VIP', 500.00, 5000, 4994, 0.00, '2025-10-11 14:00:00', '2025-10-14 14:00:00', 10, 'http://app.eventic.com/uploads/tickets/ticket_693853bb83b3c_1765299131.jpg', 'active', '2025-12-09 12:40:00', '2025-12-10 23:47:13', 300.00, 'Best VIP Ticket'),
(32, 14, 3, 'Regular', 500.00, 100, 100, 0.00, '2025-12-10 06:33:00', '2025-12-13 06:33:00', 5, 'http://app.eventic.com/uploads/tickets/ticket_693853bb8c863_1765299131.png', 'active', '2025-12-09 12:40:00', '2025-12-10 14:47:38', 200.00, 'Regular'),
(33, 12, 3, 'Regular', 1000.00, 100, 100, 0.00, NULL, NULL, 10, NULL, 'active', '2025-12-17 19:02:44', '2025-12-17 19:02:44', 0.00, ''),
(34, 9, 3, 'VIP', 120.00, 20, 20, 0.00, '2025-12-12 13:34:00', '2025-12-16 13:34:00', 10, NULL, 'active', '2025-12-17 19:03:31', '2025-12-17 19:03:31', 0.00, 'VVVVVVIP TICKET'),
(35, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, 'http://app.eventic.com/uploads/tickets/ticket_6942f15a00ad1_1765994842.png', 'active', '2025-12-17 19:07:22', '2025-12-22 12:58:39', 2.00, ''),
(36, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-17 19:31:08', '2025-12-22 12:58:39', 2.00, ''),
(37, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:56:31', '2025-12-22 12:58:39', 2.00, ''),
(38, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:56:31', '2025-12-22 12:58:40', 2.00, ''),
(39, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:58:39', '2025-12-22 12:58:39', 2.00, ''),
(40, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:58:39', '2025-12-22 12:58:39', 2.00, ''),
(41, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:58:40', '2025-12-22 12:58:40', 2.00, ''),
(42, 20, 3, 'VIP', 12.00, 122, 122, 0.00, '2025-12-18 15:06:00', '2025-12-22 15:06:00', 1, NULL, 'active', '2025-12-22 12:58:40', '2025-12-22 12:58:40', 2.00, '');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `reference` varchar(100) NOT NULL,
  `transaction_type` enum('ticket_sale','vote_purchase','payout','refund') NOT NULL,
  `organizer_id` int(11) UNSIGNED DEFAULT NULL,
  `event_id` int(11) UNSIGNED DEFAULT NULL,
  `award_id` int(11) UNSIGNED DEFAULT NULL,
  `order_id` int(11) UNSIGNED DEFAULT NULL,
  `order_item_id` int(11) UNSIGNED DEFAULT NULL,
  `vote_id` int(11) UNSIGNED DEFAULT NULL,
  `payout_id` int(11) UNSIGNED DEFAULT NULL,
  `gross_amount` decimal(10,2) DEFAULT 0.00,
  `admin_amount` decimal(10,2) DEFAULT 0.00,
  `organizer_amount` decimal(10,2) DEFAULT 0.00,
  `payment_fee` decimal(10,2) DEFAULT 0.00,
  `status` enum('pending','completed','failed','refunded') NOT NULL DEFAULT 'pending',
  `description` varchar(255) DEFAULT NULL,
  `metadata` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`metadata`)),
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reference` (`reference`),
  KEY `transaction_type` (`transaction_type`),
  KEY `organizer_id` (`organizer_id`),
  KEY `event_id` (`event_id`),
  KEY `award_id` (`award_id`),
  KEY `status` (`status`),
  KEY `created_at` (`created_at`),
  KEY `order_id` (`order_id`),
  KEY `order_item_id` (`order_item_id`),
  KEY `vote_id` (`vote_id`),
  KEY `payout_id` (`payout_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `reference`, `transaction_type`, `organizer_id`, `event_id`, `award_id`, `order_id`, `order_item_id`, `vote_id`, `payout_id`, `gross_amount`, `admin_amount`, `organizer_amount`, `payment_fee`, `status`, `description`, `metadata`, `created_at`, `updated_at`) VALUES
(1, 'TKT_69457CFDDBA0D_1766161661', 'ticket_sale', 3, 3, NULL, 19, 26, NULL, NULL, 250.00, 21.25, 225.00, 3.75, 'completed', 'Ticket sale: Tech Summit Ghana 2025', NULL, '2025-12-19 17:27:41', '2025-12-19 17:27:41'),
(2, 'TKT_69457CFF42C2C_1766161663', 'ticket_sale', 1, 4, NULL, 19, 27, NULL, NULL, 900.00, 76.50, 810.00, 13.50, 'completed', 'Ticket sale: Jazz Night at +233 Bar', NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(3, 'TKT_69457CFF58C15_1766161663', 'ticket_sale', 1, 4, NULL, 19, 28, NULL, NULL, 500.00, 42.50, 450.00, 7.50, 'completed', 'Ticket sale: Jazz Night at +233 Bar', NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(4, 'TKT_69457CFF5BCF2_1766161663', 'ticket_sale', 1, 4, NULL, 19, 29, NULL, NULL, 320.00, 27.20, 288.00, 4.80, 'completed', 'Ticket sale: Jazz Night at +233 Bar', NULL, '2025-12-19 17:27:43', '2025-12-19 17:27:43'),
(5, 'VOT_694A9CFE7C224_1766497534', 'vote_purchase', 3, NULL, 19, NULL, NULL, 7, NULL, 50.00, 6.75, 42.50, 0.75, 'completed', 'Vote purchase: AAMUSTED Most Strongest', NULL, '2025-12-23 14:45:34', '2025-12-23 14:45:34'),
(6, 'VOT_694A9F7897392_1766498168', 'vote_purchase', 3, NULL, 19, NULL, NULL, 8, NULL, 2.00, 0.27, 1.70, 0.03, 'completed', 'Vote purchase: AAMUSTED Most Strongest', NULL, '2025-12-23 14:56:08', '2025-12-23 14:56:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `role` enum('admin','organizer','attendee','pos','scanner') NOT NULL DEFAULT 'attendee',
  `email_verified` tinyint(1) DEFAULT 0,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `status` enum('active','suspended') NOT NULL DEFAULT 'active',
  `first_login` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_login_at` timestamp NULL DEFAULT NULL,
  `last_login_ip` varchar(45) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`),
  KEY `phone` (`phone`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `remember_token`, `role`, `email_verified`, `email_verified_at`, `status`, `first_login`, `created_at`, `updated_at`, `last_login_at`, `last_login_ip`) VALUES
(1, 'Admin User', 'admin@eventic.com', '+233200000001', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', NULL, 'admin', 1, NULL, 'active', 0, '2025-12-06 19:47:46', '2025-12-06 19:47:46', NULL, NULL),
(2, 'EventPro Ghana', 'organizer@eventpro.gh', '+233201234567', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', NULL, 'organizer', 1, NULL, 'active', 0, '2025-12-06 19:47:46', '2025-12-06 19:47:46', NULL, NULL),
(3, 'Live Nation Africa', 'info@livenation.africa', '+233209876543', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', NULL, 'organizer', 1, NULL, 'active', 0, '2025-12-06 19:47:46', '2025-12-06 19:47:46', NULL, NULL),
(4, 'TechHub Accra', 'events@techhub.gh', '+233205551234', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', NULL, 'organizer', 1, NULL, 'active', 0, '2025-12-06 19:47:46', '2025-12-06 19:47:46', NULL, NULL),
(5, 'Test Attendee', 'attendee@test.com', '+233241234567', '$argon2id$v=19$m=65536,t=4,p=1$d2p6cERBaVlBcTlvWEpMYg$rjnw1tViE6n/bq+fKnHl4k6k5bTzPyJbNVxFqFk5Nxs', NULL, 'attendee', 1, NULL, 'active', 0, '2025-12-06 19:47:46', '2025-12-06 19:47:46', NULL, NULL),
(6, 'Gilbert Elikplim Kukah', 'admin@gmail.com', '+233541436414', '$argon2id$v=19$m=65536,t=4,p=2$WFQ3bGNrODJ1ZnlIZDZWMA$Pg+/4J27zVEm1WSCUCG97lC9VF9YpDYzLpZSnztfklU', NULL, 'admin', 0, NULL, 'active', 0, '2025-12-05 18:27:57', '2025-12-23 10:43:06', '2025-12-23 10:43:06', '::1'),
(7, 'Gilbert Elikplim Kukah', 'kwamegilbert1114@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=2$L2gwSElObFVlZVpEdWpGNA$5b7wha3MsESsCmIiBKO1x8xmWTUoki0deJTUrGU2Fgo', NULL, 'organizer', 0, NULL, 'active', 1, '2025-12-06 20:42:53', '2025-12-23 11:24:48', '2025-12-19 17:26:31', '127.0.0.1'),
(8, 'FirstCode.Inc', 'gkukah1@gmail.com', NULL, '$argon2id$v=19$m=65536,t=4,p=2$WFQ3bGNrODJ1ZnlIZDZWMA$Pg+/4J27zVEm1WSCUCG97lC9VF9YpDYzLpZSnztfklU', NULL, 'organizer', 0, NULL, 'active', 0, '2025-12-06 21:05:07', '2025-12-23 15:10:44', '2025-12-23 15:10:44', '::1');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendees`
--
ALTER TABLE `attendees`
  ADD CONSTRAINT `attendees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `awards`
--
ALTER TABLE `awards`
  ADD CONSTRAINT `awards_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `awards_images`
--
ALTER TABLE `awards_images`
  ADD CONSTRAINT `awards_images_ibfk_1` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `award_categories`
--
ALTER TABLE `award_categories`
  ADD CONSTRAINT `award_categories_ibfk_2` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `award_nominees`
--
ALTER TABLE `award_nominees`
  ADD CONSTRAINT `award_nominees_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `award_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `award_nominees_ibfk_3` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `award_votes`
--
ALTER TABLE `award_votes`
  ADD CONSTRAINT `award_votes_ibfk_1` FOREIGN KEY (`nominee_id`) REFERENCES `award_nominees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `award_votes_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `award_categories` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `award_votes_ibfk_4` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `events`
--
ALTER TABLE `events`
  ADD CONSTRAINT `events_ibfk_1` FOREIGN KEY (`event_type_id`) REFERENCES `event_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `events_ibfk_2` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_images`
--
ALTER TABLE `event_images`
  ADD CONSTRAINT `event_images_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `event_reviews`
--
ALTER TABLE `event_reviews`
  ADD CONSTRAINT `event_reviews_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `event_reviews_ibfk_2` FOREIGN KEY (`reviewer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_3` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organizers`
--
ALTER TABLE `organizers`
  ADD CONSTRAINT `organizers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organizer_balances`
--
ALTER TABLE `organizer_balances`
  ADD CONSTRAINT `organizer_balances_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organizer_followers`
--
ALTER TABLE `organizer_followers`
  ADD CONSTRAINT `organizer_followers_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `organizer_followers_ibfk_2` FOREIGN KEY (`follower_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `payout_requests`
--
ALTER TABLE `payout_requests`
  ADD CONSTRAINT `fk_payout_award` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_payout_processor` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `payout_requests_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `payout_requests_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `refresh_tokens`
--
ALTER TABLE `refresh_tokens`
  ADD CONSTRAINT `refresh_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `scanner_assignments`
--
ALTER TABLE `scanner_assignments`
  ADD CONSTRAINT `scanner_assignments_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scanner_assignments_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `scanner_assignments_ibfk_3` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_3` FOREIGN KEY (`ticket_type_id`) REFERENCES `ticket_types` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_4` FOREIGN KEY (`attendee_id`) REFERENCES `attendees` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_5` FOREIGN KEY (`admitted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `ticket_types`
--
ALTER TABLE `ticket_types`
  ADD CONSTRAINT `ticket_types_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `ticket_types_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`organizer_id`) REFERENCES `organizers` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_3` FOREIGN KEY (`award_id`) REFERENCES `awards` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_4` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_5` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_6` FOREIGN KEY (`vote_id`) REFERENCES `award_votes` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_7` FOREIGN KEY (`payout_id`) REFERENCES `payout_requests` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;
