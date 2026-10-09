-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 09, 2026 at 02:32 PM
-- Server version: 10.11.18-MariaDB-log
-- PHP Version: 8.4.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mrbeat_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `body` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `body`, `is_active`, `sort_order`, `created_at`) VALUES
(5, '#Disclaimer', 'For every $10 deposit you make, you get $2 cashback for one month.', 0, 0, '2026-03-08 16:45:29'),
(6, '#Disclaimer', 'For every $10 deposit you make, you get $2 cashback every time😉.', 1, 0, '2026-03-17 17:21:09');

-- --------------------------------------------------------

--
-- Table structure for table `banners`
--

CREATE TABLE `banners` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(190) NOT NULL,
  `body` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `banners`
--

INSERT INTO `banners` (`id`, `title`, `body`, `is_active`, `sort_order`, `created_at`) VALUES
(3, '#Disclaimer', '[Important Disclaimer and Usage Guidelines]\r\n\r\nAll accounts and services provided on this website (including but not limited to cloud service accounts, API access, etc.) are intended for legitimate purposes only, such as learning and testing, gaming and entertainment, international trade, and marketing. Users must ensure that their intended use complies with these regulations.\r\n\r\nUsers must fully comply with the laws and regulations of their country when using these services. Any use of this website\'s account or services for illegal purposes is the user\'s sole responsibility. Upon discovering such use, please immediately cease use and close the page.\r\n\r\nThe user is solely responsible for all legal and economic losses and consequences arising from illegal use, violation of laws and regulations, or breach of the platform\'s terms of service. This website bears no direct, indirect, or joint liability in such cases.\r\n\r\nBy purchasing or using the related services, the user acknowledges, understands, and voluntarily accepts all risks and responsibilities outlined in this statement.\r\n\r\nBy purchasing or using the related services, the user acknowledges and voluntarily accepts all risks and responsibilities outlined in this statement.\r\n\r\nBy purchasing or using the related services, you acknowledge and voluntarily accept all risks and responsibilities outlined in this statement.\r\n\r\nBy purchasing or using the related services, you acknowledge and voluntarily accept all risks and responsibilities outlined in this statement.', 0, 1, '2026-03-07 12:29:59');

-- --------------------------------------------------------

--
-- Table structure for table `contact_methods`
--

CREATE TABLE `contact_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `label` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `type` varchar(50) DEFAULT 'text',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_required` tinyint(1) NOT NULL DEFAULT 0,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_methods`
--

INSERT INTO `contact_methods` (`id`, `label`, `value`, `type`, `is_active`, `is_required`, `sort_order`, `created_at`) VALUES
(1, 'تليجرام', 'https://t.me/az100_market', 'link', 1, 1, 10, '2026-03-06 12:42:19');

-- --------------------------------------------------------

--
-- Table structure for table `deposit_requests`
--

CREATE TABLE `deposit_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` enum('mb','usdt') NOT NULL DEFAULT 'usdt',
  `network` varchar(50) DEFAULT NULL,
  `amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `txid` varchar(190) DEFAULT NULL,
  `proof_image` varchar(500) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deposit_requests`
--

INSERT INTO `deposit_requests` (`id`, `user_id`, `currency`, `network`, `amount`, `txid`, `proof_image`, `status`, `admin_note`, `created_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 3, 'usdt', NULL, 100.00, NULL, 'uploads/deposits/dep_3_1772739329_9536.png', 'approved', NULL, '2026-03-05 19:35:29', '2026-03-05 20:01:08', 1),
(2, 3, 'usdt', 'TRC20 | DD6352ASD8AS974DA89SD74A9S8D798ASD', 2000.00, NULL, 'uploads/deposits/dep_3_1772751642_1561.jpg', 'approved', NULL, '2026-03-05 23:00:42', '2026-03-05 23:00:57', 1),
(3, 3, 'usdt', 'TRC20 | DD6352ASD8AS974DA89SD74A9S8D798ASD', 2000.00, NULL, 'uploads/deposits/dep_3_1772794333_4074.jpg', 'approved', NULL, '2026-03-06 10:52:13', '2026-03-06 11:39:40', 1),
(4, 3, 'usdt', 'Binance id | 1217447525', 100.00, NULL, 'uploads/deposits/dep_3_1772824709_1729.jpeg', 'approved', NULL, '2026-03-06 19:18:29', '2026-03-06 19:18:45', 1),
(5, 3, 'usdt', 'Binance ID | 1217447525', 10.00, NULL, 'uploads/deposits/dep_3_1772851220_3459.jpg', 'approved', NULL, '2026-03-07 02:40:20', '2026-03-07 02:40:29', 1),
(6, 3, 'usdt', 'Binance ID | 1217447525', 45234.00, '27485678SDAKSDH', 'uploads/deposits/dep_3_1772854774_2703.jpg', 'approved', NULL, '2026-03-07 03:39:34', '2026-03-07 03:39:46', 1),
(7, 3, 'usdt', 'Binance ID | 1217447525', 500.00, NULL, 'uploads/deposits/dep_3_1772909967_1102.jpg', 'approved', NULL, '2026-03-07 18:59:27', '2026-03-07 20:34:33', 1),
(10, 3, 'usdt', 'Binance ID | 1217447525', 100.00, NULL, 'uploads/deposits/dep_3_1772989122_9214.jpg', 'rejected', NULL, '2026-03-08 16:58:42', '2026-03-08 16:58:51', 1),
(11, 10, 'usdt', 'BNB Smart Chain (BEP20)', 2000.00, NULL, 'uploads/deposits/dep_10_1772989276_9009.jpg', 'approved', NULL, '2026-03-08 17:01:16', '2026-03-08 17:01:25', 1),
(12, 3, 'usdt', 'Binance ID | 1217447525', 100.00, NULL, 'uploads/deposits/dep_3_1772993879_1755.jpg', 'approved', NULL, '2026-03-08 18:17:59', '2026-03-08 18:18:20', 1),
(13, 3, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 10.00, NULL, 'uploads/deposits/dep_3_1772994013_3858.jpg', 'rejected', NULL, '2026-03-08 18:20:13', '2026-03-08 23:04:30', 1),
(14, 3, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 25.00, NULL, 'uploads/deposits/dep_3_1772994128_2560.jpg', 'rejected', NULL, '2026-03-08 18:22:08', '2026-03-08 23:04:29', 1),
(15, 11, 'usdt', 'Binance ID | 1217447525', 10.00, '27485678SDAKSDH', 'uploads/deposits/dep_11_00c912a5970218806102.jpg', 'rejected', 'We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-12 14:19:33', '2026-03-12 14:20:10', 1),
(16, 11, 'usdt', 'Binance ID | 1217447525', 100.00, '27485678SDAKSDH', 'uploads/deposits/dep_11_f057fe2d20b3f1633102.jpeg', 'rejected', 'تم رفض الايداع الخاص بك لان صورة فيك', '2026-03-15 21:05:22', '2026-03-15 21:06:48', 1),
(17, 71, 'usdt', 'Binance ID | 1217447525', 4.50, NULL, 'uploads/deposits/dep_71_5d2e60ebcf11ab05436e.jpg', 'approved', NULL, '2026-03-17 05:24:46', '2026-03-17 05:42:43', 1),
(18, 72, 'usdt', 'Binance ID | 1217447525', 5.00, '420696176341557248', 'uploads/deposits/dep_72_44987ff0a24449eda32c.png', 'approved', NULL, '2026-03-17 09:13:15', '2026-03-17 09:14:27', 1),
(19, 71, 'usdt', 'Binance ID | 1217447525', 3.00, NULL, 'uploads/deposits/dep_71_abb9943fbac5f9422298.jpg', 'approved', 'Done', '2026-03-18 06:23:28', '2026-03-18 06:26:02', 1),
(20, 74, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 1000.00, NULL, 'uploads/deposits/dep_74_7acc158fdedfd156ed8f.jpg', 'rejected', 'رجاء تاكد من عملية الايداع بشكل صحيح ورفق اثبات الدفع الخاص بك Please ensure that the deposit process is correct and attach your proof of payment.', '2026-03-22 10:56:44', '2026-03-22 12:33:51', 1),
(21, 72, 'usdt', 'Binance ID | 1217447525', 7.00, '422547934353973248', 'uploads/deposits/dep_72_1e5f14affaf4017bbc61.png', 'approved', NULL, '2026-03-27 08:44:08', '2026-03-27 09:01:15', 1),
(22, 11, 'usdt', 'Binance ID | 1217447525', 13.00, NULL, 'uploads/deposits/dep_11_cef9dea4e91dfc6f3dca.jpg', 'rejected', NULL, '2026-03-28 03:41:27', '2026-03-29 23:41:33', 1),
(23, 72, 'usdt', 'Binance ID | 1217447525', 5.00, '424242878789509120', 'uploads/deposits/dep_72_be272134a8118bfaf72c.png', 'approved', 'DONE PRO', '2026-04-05 11:58:35', '2026-04-05 12:01:19', 1),
(24, 72, 'usdt', 'Binance ID | 1217447525', 6.00, '424409416238374912', 'uploads/deposits/dep_72_fac88bbb91640e0a0592.png', 'rejected', NULL, '2026-04-06 09:30:59', '2026-04-06 10:20:16', 1),
(25, 98, 'usdt', 'Binance ID | 1217447525', 7.00, NULL, 'uploads/deposits/dep_98_f56301cf88e164113012.jpg', 'approved', NULL, '2026-04-19 08:17:11', '2026-04-19 09:57:23', 1),
(26, 72, 'usdt', 'Binance ID | 1217447525', 10.00, '427193875632807936', 'uploads/deposits/dep_72_a045b294fbff64e845c6.png', 'approved', 'Done an come tle to gwt pay az', '2026-04-21 09:41:58', '2026-04-21 11:05:16', 1),
(27, 100, 'usdt', 'Binance ID | 1217447525', 4.50, NULL, 'uploads/deposits/dep_100_a08d9a093231c94cc052.jpg', 'approved', NULL, '2026-04-26 07:29:14', '2026-04-26 07:30:57', 1),
(28, 120, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 10.00, NULL, 'uploads/deposits/dep_120_27b112cfb77ca29a949c.png', 'approved', NULL, '2026-07-22 09:59:03', '2026-07-22 10:17:51', 1),
(29, 121, 'usdt', 'Binance ID | 1217447525', 0.50, NULL, 'uploads/deposits/dep_121_70cf94416dfc8a5ccd8e.png', 'approved', NULL, '2026-07-27 13:15:06', '2026-07-27 13:34:38', 1),
(30, 121, 'usdt', 'Binance ID | 1217447525', 0.50, NULL, 'uploads/deposits/dep_121_9f0d37b59719354b21c6.png', 'rejected', NULL, '2026-07-27 13:34:47', '2026-07-27 22:05:24', 1),
(31, 121, 'usdt', 'Binance ID | 1217447525', 0.34, NULL, 'uploads/deposits/dep_121_1a979dda127b531f15b3.png', 'approved', NULL, '2026-07-27 14:38:14', '2026-07-27 22:05:31', 1),
(32, 127, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 12.00, '9d415d48c30190c60d03e3ed78ca06672b735ad9e820d525973d3bc892f788ea', 'uploads/deposits/dep_127_96380b6ccd0de6a6e092.png', 'rejected', NULL, '2026-08-07 03:36:50', '2026-09-30 15:14:56', 1),
(33, 130, 'usdt', 'USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 10.00, NULL, 'uploads/deposits/dep_130_6e511f030d61642cb694.jpeg', 'rejected', NULL, '2026-08-21 07:24:34', '2026-09-30 15:14:51', 1),
(34, 121, 'usdt', 'Binance ID | 1217447525', 1.00, NULL, 'uploads/deposits/dep_121_c1290d363eba709d5f7b.png', 'rejected', NULL, '2026-09-09 23:38:48', '2026-09-30 15:14:53', 1),
(35, 138, 'usdt', 'Binance ID | 1217447525', 15.00, '455622283645313024', 'uploads/deposits/dep_138_e6e0f886109b60aa897e.png', 'rejected', NULL, '2026-09-21 14:55:03', '2026-09-30 15:14:52', 1),
(36, 143, 'usdt', 'Binance ID | 1217447525', 4.70, '457351338501169152', 'uploads/deposits/dep_143_f173b37c63e77d057e11.jpg', 'pending', NULL, '2026-09-30 22:34:13', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `total_mb` decimal(18,2) NOT NULL DEFAULT 0.00,
  `total_usdt` decimal(18,2) NOT NULL DEFAULT 0.00,
  `currency` enum('mb','usdt') NOT NULL DEFAULT 'mb',
  `status` enum('paid','refunded') NOT NULL DEFAULT 'paid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `total_mb`, `total_usdt`, `currency`, `status`, `created_at`) VALUES
(1, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-05 22:36:31'),
(2, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 01:00:39'),
(3, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 01:04:42'),
(4, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 01:57:55'),
(5, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 01:57:59'),
(6, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 01:58:02'),
(7, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 01:58:03'),
(8, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 02:01:18'),
(9, 3, 0.00, 0.00, 'mb', 'paid', '2026-03-06 02:04:04'),
(10, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-06 02:06:42'),
(11, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 10:13:59'),
(12, 3, 0.00, 5.00, 'usdt', 'paid', '2026-03-06 10:39:38'),
(13, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 10:39:56'),
(14, 3, 0.00, 15.00, 'usdt', 'paid', '2026-03-06 10:40:15'),
(15, 3, 0.00, 15.00, 'usdt', 'paid', '2026-03-06 10:40:29'),
(16, 3, 0.00, 15.00, 'usdt', 'paid', '2026-03-06 10:40:31'),
(17, 3, 0.00, 5.00, 'usdt', 'paid', '2026-03-06 11:02:55'),
(18, 3, 0.00, 12.00, 'usdt', 'paid', '2026-03-06 12:49:41'),
(19, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 16:13:09'),
(20, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 17:58:54'),
(21, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 18:02:37'),
(22, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:08:46'),
(23, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:08:51'),
(24, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:08:56'),
(25, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:10:40'),
(26, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:13:02'),
(27, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:13:07'),
(28, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:13:13'),
(29, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:17:38'),
(30, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:17:45'),
(31, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:22:06'),
(32, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:36:13'),
(33, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:36:15'),
(34, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:36:24'),
(35, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:36:28'),
(36, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:45:21'),
(37, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:45:24'),
(38, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:47:24'),
(39, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:47:33'),
(40, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:51:29'),
(41, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 19:59:44'),
(42, 3, 0.00, 3.50, 'usdt', 'paid', '2026-03-06 20:01:59'),
(43, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 20:02:54'),
(44, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-06 20:03:26'),
(45, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-06 20:13:17'),
(46, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-06 20:13:24'),
(47, 3, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 20:14:37'),
(48, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-06 21:55:08'),
(49, 10, 0.00, 4.50, 'usdt', 'paid', '2026-03-06 22:33:27'),
(50, 3, 0.00, 10.00, 'usdt', 'paid', '2026-03-07 02:14:48'),
(51, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 11:41:44'),
(52, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 11:42:04'),
(53, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 11:56:14'),
(63, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 14:03:57'),
(64, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 14:06:34'),
(65, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 14:13:02'),
(66, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 14:15:37'),
(67, 11, 0.00, 1.00, 'usdt', 'paid', '2026-03-07 14:15:46'),
(68, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 14:29:39'),
(69, 11, 0.00, 2.00, 'usdt', 'paid', '2026-03-07 14:35:24'),
(70, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 15:40:14'),
(71, 11, 0.00, 2.50, 'usdt', 'paid', '2026-03-07 15:43:00'),
(72, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 15:44:12'),
(74, 3, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 16:43:09'),
(75, 3, 0.00, 0.20, 'usdt', 'paid', '2026-03-07 18:55:50'),
(76, 3, 0.00, 9.00, 'usdt', 'paid', '2026-03-07 18:57:53'),
(77, 15, 0.00, 3.00, 'usdt', 'paid', '2026-03-07 19:08:45'),
(80, 3, 0.00, 0.50, 'usdt', 'paid', '2026-03-07 23:49:41'),
(81, 1, 0.00, 0.50, 'usdt', 'paid', '2026-03-08 01:42:37'),
(82, 1, 0.00, 1.50, 'usdt', 'paid', '2026-03-08 01:42:54'),
(83, 10, 0.00, 3.00, 'usdt', 'paid', '2026-03-08 01:44:13'),
(87, 10, 0.00, 4.50, 'usdt', 'paid', '2026-03-08 02:32:50'),
(91, 3, 0.00, 1.00, 'usdt', 'paid', '2026-03-08 14:28:14'),
(92, 11, 0.00, 0.50, 'usdt', 'paid', '2026-03-08 15:32:51'),
(94, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-08 22:03:04'),
(95, 11, 0.00, 3.50, 'usdt', 'paid', '2026-03-09 02:59:10'),
(96, 11, 0.00, 0.20, 'usdt', 'paid', '2026-03-09 21:20:41'),
(97, 29, 0.00, 0.20, 'usdt', 'paid', '2026-03-10 06:46:43'),
(98, 1, 0.00, 0.20, 'usdt', 'paid', '2026-03-10 15:48:31'),
(99, 33, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 09:53:28'),
(100, 33, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 09:55:49'),
(101, 33, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 09:57:02'),
(102, 33, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 10:00:27'),
(103, 33, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 10:00:43'),
(104, 34, 0.00, 0.32, 'usdt', 'paid', '2026-03-12 14:52:40'),
(105, 34, 0.00, 0.20, 'usdt', 'paid', '2026-03-12 14:55:05'),
(106, 34, 0.00, 0.40, 'usdt', 'paid', '2026-03-12 14:55:31'),
(107, 3, 0.00, 0.32, 'usdt', 'paid', '2026-03-12 15:11:57'),
(108, 11, 0.00, 3.60, 'usdt', 'paid', '2026-03-14 05:04:30'),
(109, 11, 0.00, 4.50, 'usdt', 'paid', '2026-03-14 05:05:21'),
(110, 35, 0.00, 0.41, 'usdt', 'paid', '2026-03-14 07:35:06'),
(111, 35, 0.00, 0.48, 'usdt', 'paid', '2026-03-14 12:55:18'),
(112, 3, 0.00, 5.50, 'usdt', 'paid', '2026-03-14 13:25:30'),
(113, 1, 0.00, 9.50, 'usdt', 'paid', '2026-03-14 13:37:52'),
(114, 36, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 08:57:05'),
(115, 36, 0.00, 0.41, 'usdt', 'paid', '2026-03-15 09:02:42'),
(116, 37, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:03:28'),
(117, 38, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:04:56'),
(118, 38, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:05:06'),
(119, 39, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:05:26'),
(120, 39, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:05:35'),
(121, 40, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:05:54'),
(122, 40, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:06:03'),
(123, 41, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:06:21'),
(124, 41, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:06:30'),
(125, 42, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:06:49'),
(126, 42, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:06:58'),
(127, 45, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:07:35'),
(128, 45, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:07:43'),
(129, 46, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:08:11'),
(130, 46, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:08:29'),
(131, 47, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:08:54'),
(132, 47, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:09:05'),
(133, 48, 0.00, 0.48, 'usdt', 'paid', '2026-03-15 09:09:31'),
(134, 48, 0.00, 0.41, 'usdt', 'paid', '2026-03-15 09:09:43'),
(135, 49, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:10:14'),
(136, 50, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:11:55'),
(137, 51, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:13:52'),
(138, 52, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:16:03'),
(139, 53, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:20:11'),
(140, 54, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:20:58'),
(141, 55, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:21:19'),
(142, 56, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:21:38'),
(143, 57, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:21:57'),
(144, 58, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:22:27'),
(145, 59, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:22:53'),
(146, 60, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:23:11'),
(147, 61, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:23:30'),
(148, 62, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:25:06'),
(149, 63, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:25:37'),
(150, 64, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:26:07'),
(151, 65, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:26:36'),
(152, 68, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:30:53'),
(153, 69, 0.00, 0.82, 'usdt', 'paid', '2026-03-15 09:51:30'),
(154, 70, 0.00, 0.96, 'usdt', 'paid', '2026-03-15 09:55:52'),
(155, 3, 0.00, 5.50, 'usdt', 'paid', '2026-03-15 19:42:00'),
(156, 3, 0.00, 9.50, 'usdt', 'paid', '2026-03-15 19:42:05'),
(157, 3, 0.00, 19.00, 'usdt', 'paid', '2026-03-15 19:42:13'),
(158, 1, 0.00, 0.22, 'usdt', 'paid', '2026-03-15 21:03:54'),
(159, 71, 0.00, 4.50, 'usdt', 'paid', '2026-03-17 05:43:11'),
(160, 71, 0.00, 0.27, 'usdt', 'paid', '2026-03-17 06:59:40'),
(161, 71, 0.00, 0.22, 'usdt', 'paid', '2026-03-17 06:59:56'),
(162, 72, 0.00, 4.50, 'usdt', 'paid', '2026-03-17 09:15:18'),
(163, 72, 0.00, 0.22, 'usdt', 'paid', '2026-03-18 05:53:50'),
(164, 71, 0.00, 3.60, 'usdt', 'paid', '2026-03-18 06:27:44'),
(165, 11, 0.00, 0.22, 'usdt', 'paid', '2026-03-18 18:28:00'),
(166, 72, 0.00, 7.50, 'usdt', 'paid', '2026-03-27 09:03:43'),
(167, 78, 0.00, 13.80, 'usdt', 'paid', '2026-03-28 03:47:05'),
(168, 72, 0.00, 4.60, 'usdt', 'paid', '2026-04-05 12:01:35'),
(169, 72, 0.00, 4.60, 'usdt', 'paid', '2026-04-06 10:22:01'),
(170, 1, 0.00, 0.22, 'usdt', 'paid', '2026-04-10 20:54:22'),
(171, 1, 0.00, 5.50, 'usdt', 'paid', '2026-04-13 01:31:46'),
(172, 98, 0.00, 6.50, 'usdt', 'paid', '2026-04-19 12:29:13'),
(173, 100, 0.00, 4.60, 'usdt', 'paid', '2026-04-26 07:31:13'),
(174, 102, 0.00, 0.22, 'usdt', 'paid', '2026-04-26 11:52:22'),
(175, 1, 0.00, 10.00, 'usdt', 'paid', '2026-04-27 12:52:31'),
(176, 1, 0.00, 9.50, 'usdt', 'paid', '2026-04-27 12:52:37'),
(177, 72, 0.00, 0.22, 'usdt', 'paid', '2026-05-14 03:55:20'),
(178, 105, 0.00, 25.00, 'usdt', 'paid', '2026-05-24 07:55:13'),
(179, 105, 0.00, 0.22, 'usdt', 'paid', '2026-05-24 08:51:05'),
(180, 121, 0.00, 0.44, 'usdt', 'paid', '2026-07-27 13:35:48'),
(181, 121, 0.00, 0.22, 'usdt', 'paid', '2026-07-27 22:21:49'),
(182, 121, 0.00, 0.27, 'usdt', 'paid', '2026-07-27 22:26:42'),
(183, 120, 0.00, 2.20, 'usdt', 'paid', '2026-08-02 09:15:03'),
(184, 122, 0.00, 4.80, 'usdt', 'paid', '2026-08-06 09:07:00'),
(185, 28, 0.00, 0.22, 'usdt', 'paid', '2026-09-14 14:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `order_id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `qty` int(11) NOT NULL DEFAULT 1,
  `unit_price_mb` decimal(18,2) NOT NULL DEFAULT 0.00,
  `unit_price_usdt` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `qty`, `unit_price_mb`, `unit_price_usdt`, `created_at`) VALUES
(94, 94, 15, 1, 0.00, 0.20, '2026-03-08 22:03:04'),
(95, 95, 17, 1, 0.00, 3.50, '2026-03-09 02:59:10'),
(96, 96, 15, 1, 0.00, 0.20, '2026-03-09 21:20:41'),
(97, 97, 15, 1, 0.00, 0.20, '2026-03-10 06:46:43'),
(98, 98, 15, 1, 0.00, 0.20, '2026-03-10 15:48:31'),
(99, 99, 15, 1, 0.00, 0.20, '2026-03-12 09:53:28'),
(100, 100, 15, 1, 0.00, 0.20, '2026-03-12 09:55:49'),
(101, 101, 15, 1, 0.00, 0.20, '2026-03-12 09:57:02'),
(102, 102, 15, 1, 0.00, 0.20, '2026-03-12 10:00:27'),
(103, 103, 15, 1, 0.00, 0.20, '2026-03-12 10:00:43'),
(104, 104, 20, 1, 0.00, 0.32, '2026-03-12 14:52:40'),
(105, 105, 15, 1, 0.00, 0.20, '2026-03-12 14:55:05'),
(106, 106, 15, 2, 0.00, 0.20, '2026-03-12 14:55:31'),
(107, 107, 20, 1, 0.00, 0.32, '2026-03-12 15:11:57'),
(109, 109, 14, 1, 0.00, 4.50, '2026-03-14 05:05:21'),
(110, 110, 15, 1, 0.00, 0.41, '2026-03-14 07:35:06'),
(111, 111, 20, 1, 0.00, 0.48, '2026-03-14 12:55:18'),
(112, 112, 22, 1, 0.00, 5.50, '2026-03-14 13:25:30'),
(113, 113, 21, 1, 0.00, 9.50, '2026-03-14 13:37:52'),
(114, 114, 20, 1, 0.00, 0.48, '2026-03-15 08:57:05'),
(115, 115, 15, 1, 0.00, 0.41, '2026-03-15 09:02:42'),
(116, 116, 20, 2, 0.00, 0.48, '2026-03-15 09:03:28'),
(117, 117, 20, 1, 0.00, 0.48, '2026-03-15 09:04:56'),
(118, 118, 20, 1, 0.00, 0.48, '2026-03-15 09:05:06'),
(119, 119, 20, 1, 0.00, 0.48, '2026-03-15 09:05:26'),
(120, 120, 20, 1, 0.00, 0.48, '2026-03-15 09:05:35'),
(121, 121, 20, 1, 0.00, 0.48, '2026-03-15 09:05:54'),
(122, 122, 20, 1, 0.00, 0.48, '2026-03-15 09:06:03'),
(123, 123, 20, 1, 0.00, 0.48, '2026-03-15 09:06:21'),
(124, 124, 20, 1, 0.00, 0.48, '2026-03-15 09:06:30'),
(125, 125, 20, 1, 0.00, 0.48, '2026-03-15 09:06:49'),
(126, 126, 20, 1, 0.00, 0.48, '2026-03-15 09:06:58'),
(127, 127, 20, 1, 0.00, 0.48, '2026-03-15 09:07:35'),
(128, 128, 20, 1, 0.00, 0.48, '2026-03-15 09:07:43'),
(129, 129, 20, 1, 0.00, 0.48, '2026-03-15 09:08:11'),
(130, 130, 20, 1, 0.00, 0.48, '2026-03-15 09:08:29'),
(131, 131, 20, 1, 0.00, 0.48, '2026-03-15 09:08:54'),
(132, 132, 20, 1, 0.00, 0.48, '2026-03-15 09:09:05'),
(133, 133, 20, 1, 0.00, 0.48, '2026-03-15 09:09:31'),
(134, 134, 15, 1, 0.00, 0.41, '2026-03-15 09:09:43'),
(135, 135, 20, 2, 0.00, 0.48, '2026-03-15 09:10:14'),
(136, 136, 20, 2, 0.00, 0.48, '2026-03-15 09:11:55'),
(137, 137, 20, 2, 0.00, 0.48, '2026-03-15 09:13:52'),
(138, 138, 20, 2, 0.00, 0.48, '2026-03-15 09:16:03'),
(139, 139, 20, 2, 0.00, 0.48, '2026-03-15 09:20:11'),
(140, 140, 20, 2, 0.00, 0.48, '2026-03-15 09:20:58'),
(141, 141, 20, 2, 0.00, 0.48, '2026-03-15 09:21:19'),
(142, 142, 20, 2, 0.00, 0.48, '2026-03-15 09:21:38'),
(143, 143, 20, 2, 0.00, 0.48, '2026-03-15 09:21:57'),
(144, 144, 15, 2, 0.00, 0.41, '2026-03-15 09:22:27'),
(145, 145, 15, 2, 0.00, 0.41, '2026-03-15 09:22:53'),
(146, 146, 20, 2, 0.00, 0.48, '2026-03-15 09:23:11'),
(147, 147, 20, 2, 0.00, 0.48, '2026-03-15 09:23:30'),
(148, 148, 15, 2, 0.00, 0.41, '2026-03-15 09:25:06'),
(149, 149, 15, 2, 0.00, 0.41, '2026-03-15 09:25:37'),
(150, 150, 15, 2, 0.00, 0.41, '2026-03-15 09:26:07'),
(151, 151, 15, 2, 0.00, 0.41, '2026-03-15 09:26:36'),
(152, 152, 20, 2, 0.00, 0.48, '2026-03-15 09:30:53'),
(153, 153, 15, 2, 0.00, 0.41, '2026-03-15 09:51:30'),
(154, 154, 20, 2, 0.00, 0.48, '2026-03-15 09:55:52'),
(155, 155, 22, 1, 0.00, 5.50, '2026-03-15 19:42:00'),
(156, 156, 21, 1, 0.00, 9.50, '2026-03-15 19:42:05'),
(157, 157, 21, 2, 0.00, 9.50, '2026-03-15 19:42:13'),
(158, 158, 20, 1, 0.00, 0.22, '2026-03-15 21:03:54'),
(159, 159, 14, 1, 0.00, 4.50, '2026-03-17 05:43:11'),
(160, 160, 15, 1, 0.00, 0.27, '2026-03-17 06:59:40'),
(161, 161, 20, 1, 0.00, 0.22, '2026-03-17 06:59:56'),
(162, 162, 14, 1, 0.00, 4.50, '2026-03-17 09:15:18'),
(163, 163, 20, 1, 0.00, 0.22, '2026-03-18 05:53:50'),
(165, 165, 20, 1, 0.00, 0.22, '2026-03-18 18:28:00'),
(167, 167, 23, 3, 0.00, 4.60, '2026-03-28 03:47:05'),
(168, 168, 23, 1, 0.00, 4.60, '2026-04-05 12:01:35'),
(169, 169, 23, 1, 0.00, 4.60, '2026-04-06 10:22:01'),
(170, 170, 20, 1, 0.00, 0.22, '2026-04-10 20:54:22'),
(171, 171, 28, 1, 0.00, 5.50, '2026-04-13 01:31:46'),
(172, 172, 28, 1, 0.00, 6.50, '2026-04-19 12:29:13'),
(174, 174, 20, 1, 0.00, 0.22, '2026-04-26 11:52:22'),
(175, 175, 25, 1, 0.00, 10.00, '2026-04-27 12:52:31'),
(177, 177, 20, 1, 0.00, 0.22, '2026-05-14 03:55:20'),
(179, 179, 20, 1, 0.00, 0.22, '2026-05-24 08:51:05'),
(180, 180, 20, 2, 0.00, 0.22, '2026-07-27 13:35:48'),
(181, 181, 20, 1, 0.00, 0.22, '2026-07-27 22:21:49'),
(182, 182, 15, 1, 0.00, 0.27, '2026-07-27 22:26:42'),
(183, 183, 27, 1, 0.00, 2.20, '2026-08-02 09:15:03'),
(184, 184, 14, 1, 0.00, 4.80, '2026-08-06 09:07:00'),
(185, 185, 20, 1, 0.00, 0.22, '2026-09-14 14:27:25');

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `currency` enum('mb','usdt') NOT NULL DEFAULT 'usdt',
  `network` varchar(80) NOT NULL,
  `address` varchar(255) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `method_type` varchar(50) NOT NULL DEFAULT 'crypto',
  `title` varchar(150) DEFAULT NULL,
  `account_name` varchar(150) DEFAULT NULL,
  `extra_fields` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_methods`
--

INSERT INTO `payment_methods` (`id`, `currency`, `network`, `address`, `is_active`, `sort_order`, `created_at`, `method_type`, `title`, `account_name`, `extra_fields`) VALUES
(11, 'usdt', 'BINANCE', '1217447525', 1, 0, '2026-03-08 18:17:00', 'binance_id', 'Binance ID', 'Mr - Beat88', '{\"note\":\"\",\"label1\":\"\",\"value1\":\"\",\"label2\":\"\",\"value2\":\"\"}'),
(12, 'usdt', 'TRC20', 'TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', 1, 0, '2026-03-08 18:19:59', 'crypto', 'USDT TRC20', '', '{\"note\":\"The minimum deposit is 10$.\",\"label1\":\"\",\"value1\":\"\",\"label2\":\"\",\"value2\":\"\"}'),
(13, 'usdt', 'BEP20', '0xcd7f8687599b6dad21f970837d72f116e4f61bef', 1, 0, '2026-03-08 18:21:30', 'crypto', 'USDT  BNB Smart Chain (BEP20)', '', '{\"note\":\"The minimum deposit is 10$.\",\"label1\":\"\",\"value1\":\"\",\"label2\":\"\",\"value2\":\"\"}');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `name` varchar(190) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price_mb` decimal(18,2) NOT NULL DEFAULT 0.00,
  `price_usdt` decimal(18,2) NOT NULL DEFAULT 0.00,
  `currency_mode` enum('mb','usdt','both') NOT NULL DEFAULT 'mb',
  `stock_type` enum('email','email_2fa','email_access_mail','email_2fa_access_mail','rdp') NOT NULL DEFAULT 'email',
  `image_url` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `featured` tinyint(1) NOT NULL DEFAULT 0,
  `featured_order` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `seller_id`, `name`, `category`, `price_mb`, `price_usdt`, `currency_mode`, `stock_type`, `image_url`, `description`, `is_active`, `created_at`, `featured`, `featured_order`) VALUES
(14, 1, 'Azure student 100$ Edu mail', 'AZURE STUDENT', 0.00, 4.80, 'usdt', 'email', 'uploads/prod_1772984791_3472.jpg', 'حساب ازور طلاب 100 دولار معاد تجديد مخزون قديم ضمان فحص\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable check warranty', 0, '2026-03-08 15:46:31', 0, 0),
(15, 1, '✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.', 'Fresh Edu mail', 0.00, 0.27, 'usdt', 'email', 'uploads/prod_1772994332_8111.jpg', '✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.', 1, '2026-03-08 18:25:32', 0, 0),
(16, 1, 'RDP 16 RAM 2 CORE  US', 'RDP US', 0.00, 10.00, 'usdt', 'rdp', 'uploads/prod_edit_16_1772994583_2260.jpg', 'RDP 16 RAM 2 CORE  US ONE MONTH WARRANTY', 0, '2026-03-08 18:29:09', 0, 0),
(17, 10, 'azure student with api 100$', 'AZURE STUDENT', 0.00, 4.50, 'usdt', 'email_access_mail', 'uploads/prod_1773025143_4404.jpg', 'azure student with api 100$  check warranty', 0, '2026-03-09 02:59:03', 0, 0),
(20, 1, '✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.', 'Fresh Edu mail', 0.00, 0.22, 'usdt', 'email', 'uploads/prod_1773325308_2816.jpg', '✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.', 0, '2026-03-12 14:21:48', 0, 0),
(21, 1, 'RDP 16 RAM 4 CORE italy', 'RDP italy', 0.00, 9.50, 'usdt', 'rdp', 'uploads/prod_1773491544_6015.webp', 'RDP 16 RAM 4 CORE ITALY ONE MONTH WARRANTY', 0, '2026-03-14 12:32:24', 0, 0),
(22, 1, 'RDP 8 RAM 2 CORE  (ubuntu)', 'linux serve (ubuntu)', 0.00, 6.00, 'usdt', 'rdp', 'uploads/prod_edit_22_1775019604_1970.png', 'RDP 8 RAM 2 CORE  (ubuntu)', 0, '2026-03-14 12:40:45', 0, 0),
(23, 1, 'Azure student 100$ Edu mail ( Renew )', 'Azure student 100$ Edu mail ( Renew )', 0.00, 4.60, 'usdt', 'email', 'uploads/prod_1773787156_5305.png', 'حساب ازور طلاب 100 دولار معاد تجديد مخزون قديم ضمان فحص\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable check warranty', 0, '2026-03-17 22:39:16', 0, 0),
(25, 1, 'RDP 16 RAM 2 CORE linux serve (ubuntu)', 'linux serve (ubuntu)', 0.00, 10.00, 'usdt', 'rdp', 'uploads/prod_1775019986_9195.jpg', 'RDP 16 RAM 4 CORE  linux serve (ubuntu) MONTH WARRANTY', 0, '2026-04-01 05:06:26', 0, 0),
(27, 1, 'card vcc azure Azure virtual credit card, $2 balance, Mercury Bank', 'card vcc azure', 0.00, 2.20, 'usdt', 'rdp', 'uploads/prod_edit_27_1775045292_9194.png', 'card vcc azure Azure virtual credit card, $2 balance, Mercury Bank\r\nFOR AZURE PAY AZ YOU GO', 0, '2026-04-01 11:28:06', 0, 0),
(28, 1, 'Azure student 100$ Edu mail ( Renew ) OLD STOCK 2026 Asia location support✅😉', 'AZ 100 ( Renew ) OLD Stock Asia location suppor😉', 0.00, 6.50, 'usdt', 'email', 'uploads/prod_edit_28_1776351178_5496.png', 'حساب ازور طلاب 100 دولار معاد تجديد مخزون قديم ضمان فحص\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable check warranty\r\nAllowed locations\r\n[\"japanwest\",\"southeastasia\",\"centralindia\",\"koreacentral\",\"eastasia\"]', 0, '2026-04-08 11:12:07', 0, 0),
(30, 1, 'Azure student 100$  ( Renew ) OLD STOCK UPDATE 6/9/2026✅😉', 'Azure student 100$ UPDATE 6/9/2026✅😉', 0.00, 5.50, 'usdt', 'email_access_mail', 'uploads/prod_edit_30_1781035666_9225.png', 'حساب ازور طلاب 100 دولار معاد تجديد مخزون قديم ضمان فحص\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable check warranty\r\nAllowed locations\r\n[\"spaincentral\",\"uaenorth\",\"centralindia\",\"italynorth\",\"swedencentral\",\"polandcentral\",\"francecentral\"]', 0, '2026-06-09 19:21:14', 0, 0),
(31, 1, '✅ AZURE STUDENT (Tier 1)  Available (old stock)', 'AZURE AI', 0.00, 60.00, 'usdt', 'email_2fa', 'uploads/prod_1791544813_2634.png', '✅ AZURE STUDENT (Tier 1)  Available (old stock)\r\n需要gpt-6-astra/gpt-6-sol/gpt-6-luna/gpt-5.6-sol/gpt-5.5 /gpt‑6.1‑sol', 1, '2026-10-09 11:20:13', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `product_stock`
--

CREATE TABLE `product_stock` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `product_id` bigint(20) UNSIGNED NOT NULL,
  `type` enum('email','email_2fa','email_access_mail','email_2fa_access_mail','rdp') NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `pass` varchar(190) DEFAULT NULL,
  `twofa_code` text DEFAULT NULL,
  `access_email` varchar(255) DEFAULT NULL,
  `access_pass` varchar(255) DEFAULT NULL,
  `host` varchar(190) DEFAULT NULL,
  `user` varchar(190) DEFAULT NULL,
  `status` enum('available','sold') NOT NULL DEFAULT 'available',
  `sold_to_user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sold_order_id` bigint(20) UNSIGNED DEFAULT NULL,
  `sold_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_stock`
--

INSERT INTO `product_stock` (`id`, `product_id`, `type`, `email`, `pass`, `twofa_code`, `access_email`, `access_pass`, `host`, `user`, `status`, `sold_to_user_id`, `sold_order_id`, `sold_at`, `created_at`) VALUES
(317, 14, 'email', '21212017200014@foe.zu.edu.eg', 'edu#20225vQ', NULL, NULL, NULL, NULL, NULL, 'sold', 11, 109, '2026-03-14 05:05:21', '2026-03-08 15:46:31'),
(318, 14, 'email', '21212017202319@foe.zu.edu.eg', 's#1a5x4d7a81g', NULL, NULL, NULL, NULL, NULL, 'sold', 71, 159, '2026-03-17 05:43:11', '2026-03-08 15:46:31'),
(319, 14, 'email', '21212017200015@foe.zu.edu.eg', 'edu#48cAx12a', NULL, NULL, NULL, NULL, NULL, 'sold', 72, 162, '2026-03-17 09:15:18', '2026-03-08 15:46:31'),
(320, 14, 'email', 'mohamedabdallah5@com.svu.edu.eg', 'ecxn#@65sd11', NULL, NULL, NULL, NULL, NULL, 'sold', 122, 184, '2026-08-06 09:07:00', '2026-03-08 15:46:31'),
(321, 14, 'email', 'aya.alip2@com.svu.edu.eg', 'A#521dcas78', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-08 15:46:31'),
(322, 15, 'email', 'social201510469@social.helwan.edu.eg', 'va68vc#47asa', NULL, NULL, NULL, NULL, NULL, 'sold', NULL, 93, '2026-03-08 18:26:01', '2026-03-08 18:25:32'),
(323, 15, 'email', 'social201510470@social.helwan.edu.eg', 'chapaass#@4sac1', NULL, NULL, NULL, NULL, NULL, 'sold', 11, 94, '2026-03-08 22:03:04', '2026-03-08 18:25:32'),
(324, 15, 'email', 'social201510472@social.helwan.edu.eg', 'sac12x587ca#8', NULL, NULL, NULL, NULL, NULL, 'sold', 11, 96, '2026-03-09 21:20:41', '2026-03-08 18:25:32'),
(325, 15, 'email', 'social201510473@social.helwan.edu.eg', 'cebeX5356ds', NULL, NULL, NULL, NULL, NULL, 'sold', 29, 97, '2026-03-10 06:46:43', '2026-03-08 18:25:32'),
(326, 15, 'email', 'social201510474@social.helwan.edu.eg', 'sca#847ascxa2s', NULL, NULL, NULL, NULL, NULL, 'sold', 1, 98, '2026-03-10 15:48:31', '2026-03-08 18:25:32'),
(327, 15, 'email', 'social201510475@social.helwan.edu.eg', 'v5555c#sa5c4', NULL, NULL, NULL, NULL, NULL, 'sold', 33, 99, '2026-03-12 09:53:28', '2026-03-08 18:25:32'),
(328, 15, 'email', 'social201510477@social.helwan.edu.eg', 'sea#5ascx000', NULL, NULL, NULL, NULL, NULL, 'sold', 33, 100, '2026-03-12 09:55:49', '2026-03-08 18:25:32'),
(329, 15, 'email', 'social201510478@social.helwan.edu.eg', 'ca5#8scx12a', NULL, NULL, NULL, NULL, NULL, 'sold', 33, 101, '2026-03-12 09:57:02', '2026-03-08 18:25:32'),
(330, 15, 'email', 'social201510479@social.helwan.edu.eg', 'ca57ca12xc0#4', NULL, NULL, NULL, NULL, NULL, 'sold', 33, 102, '2026-03-12 10:00:27', '2026-03-08 18:25:32'),
(331, 15, 'email', 'social201510480@social.helwan.edu.eg', 'c#75as7caxc2a', NULL, NULL, NULL, NULL, NULL, 'sold', 33, 103, '2026-03-12 10:00:43', '2026-03-08 18:25:32'),
(332, 15, 'email', 'social201510481@social.helwan.edu.eg', 'v6#8asca2s', NULL, NULL, NULL, NULL, NULL, 'sold', 34, 105, '2026-03-12 14:55:05', '2026-03-08 18:25:32'),
(333, 16, 'rdp', NULL, 'WARDA21SD0AS78DAS', NULL, NULL, NULL, '20.24.11', 'WARDA', 'available', NULL, NULL, NULL, '2026-03-08 18:29:09'),
(334, 17, 'email_2fa', 'walaa.khalil.ss@hotmail.com', 'W#32d110cas4', 'askhdasjdhasjd', NULL, NULL, NULL, NULL, 'sold', 11, 95, '2026-03-09 02:59:10', '2026-03-09 02:59:03'),
(335, 17, 'email_access_mail', 'walaa.khalil.ss@hotmail.com', 'W#32d110cas4', NULL, 'social201510363@social.helwan.edu.eg', 'hoda#154488c3', NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-09 03:00:27'),
(341, 15, 'email', '21212017200539@foe.zu.edu.eg', 'mena#89d47as87gh', NULL, NULL, NULL, NULL, NULL, 'sold', 34, 106, '2026-03-12 14:55:31', '2026-03-10 12:26:16'),
(342, 15, 'email', '21212017200540@foe.zu.edu.eg', 'edu#514cdghbfh', NULL, NULL, NULL, NULL, NULL, 'sold', 34, 106, '2026-03-12 14:55:31', '2026-03-10 12:26:16'),
(343, 15, 'email', '21212017200541@foe.zu.edu.eg', 'memo#78das', NULL, NULL, NULL, NULL, NULL, 'sold', 35, 110, '2026-03-14 07:35:06', '2026-03-10 12:26:16'),
(344, 15, 'email', 'doaa.abouelkader5.h@heco.helwan.edu.eg', 'ihgdiggutruy@54', NULL, NULL, NULL, NULL, NULL, 'sold', 36, 115, '2026-03-15 09:02:42', '2026-03-10 12:26:57'),
(345, 15, 'email', 'doaa.ahmed6.h@heco.helwan.edu.eg', 'tgihrtruuy@55', NULL, NULL, NULL, NULL, NULL, 'sold', 48, 134, '2026-03-15 09:09:43', '2026-03-10 12:26:57'),
(346, 15, 'email', 'doaa.ali1.h@heco.helwan.edu.eg', 'heurghruhgeu@44', NULL, NULL, NULL, NULL, NULL, 'sold', 58, 144, '2026-03-15 09:22:27', '2026-03-10 12:26:57'),
(347, 15, 'email', 'doaa.abouelkader.h@heco.helwan.edu.eg', 'geiguehugrh@6', NULL, NULL, NULL, NULL, NULL, 'sold', 58, 144, '2026-03-15 09:22:27', '2026-03-10 12:26:57'),
(348, 15, 'email', 'doaa.abouelkader.he@heco.helwan.edu.eg', 'jgijirtjgir@55', NULL, NULL, NULL, NULL, NULL, 'sold', 59, 145, '2026-03-15 09:22:53', '2026-03-10 12:26:57'),
(349, 15, 'email', 'doaa.ahmed11.h@heco.helwan.edu.eg', 'htgitrhgtrh@54', NULL, NULL, NULL, NULL, NULL, 'sold', 59, 145, '2026-03-15 09:22:53', '2026-03-10 12:26:57'),
(350, 15, 'email', 'doaa.ahmed11.he@heco.helwan.edu.eg', 'gyjytjyj@74', NULL, NULL, NULL, NULL, NULL, 'sold', 62, 148, '2026-03-15 09:25:06', '2026-03-10 12:26:57'),
(351, 15, 'email', 'doaa.ahmed8.he@heco.helwan.edu.eg', 'hugheruguer@588', NULL, NULL, NULL, NULL, NULL, 'sold', 62, 148, '2026-03-15 09:25:06', '2026-03-10 12:26:57'),
(352, 15, 'email', 'doaa.abouelkader1.he@heco.helwan.edu.eg', 'tjgitrit@55', NULL, NULL, NULL, NULL, NULL, 'sold', 63, 149, '2026-03-15 09:25:37', '2026-03-10 12:26:57'),
(353, 15, 'email', 'doaa.ahmed2.he@heco.helwan.edu.eg', 'digjiigtru@55', NULL, NULL, NULL, NULL, NULL, 'sold', 63, 149, '2026-03-15 09:25:37', '2026-03-10 12:26:57'),
(354, 15, 'email', 'doaa.ahmed6.he@heco.helwan.edu.eg', 'yjytdjtjytd@75', NULL, NULL, NULL, NULL, NULL, 'sold', 64, 150, '2026-03-15 09:26:07', '2026-03-10 12:26:57'),
(355, 15, 'email', 'doaa.abouelkader4.he@heco.helwan.edu.eg', 'yjhryrj@44', NULL, NULL, NULL, NULL, NULL, 'sold', 64, 150, '2026-03-15 09:26:07', '2026-03-10 12:26:57'),
(356, 15, 'email', 'doaa.ahmed8.h@heco.helwan.edu.eg', 'gjghjghjgh@757', NULL, NULL, NULL, NULL, NULL, 'sold', 65, 151, '2026-03-15 09:26:36', '2026-03-10 12:26:57'),
(357, 15, 'email', 'doaa.abouelkader2.h@heco.helwan.edu.eg', 'ugfryugrg2@555', NULL, NULL, NULL, NULL, NULL, 'sold', 65, 151, '2026-03-15 09:26:36', '2026-03-10 12:26:57'),
(358, 15, 'email', 'doaa.ali.he@heco.helwan.edu.eg', 'jrehhguerhughreh@655', NULL, NULL, NULL, NULL, NULL, 'sold', 69, 153, '2026-03-15 09:51:30', '2026-03-10 12:26:57'),
(359, 15, 'email', 'doaa.abouelkader5.he@heco.helwan.edu.eg', 'reigeru8euy@55', NULL, NULL, NULL, NULL, NULL, 'sold', 69, 153, '2026-03-15 09:51:30', '2026-03-10 12:26:57'),
(360, 15, 'email', 'doaa.abouelkader3.h@heco.helwan.edu.eg', 'ikltuutgktj@74', NULL, NULL, NULL, NULL, NULL, 'sold', 71, 160, '2026-03-17 06:59:40', '2026-03-10 12:26:57'),
(361, 20, 'email', 'SARAH.SHEDID@fth.helwan.edu.eg', 'A#ghf65474', NULL, NULL, NULL, NULL, NULL, 'sold', 34, 104, '2026-03-12 14:52:40', '2026-03-12 14:21:48'),
(362, 20, 'email', 'OMNIA.HASSAN@fth.helwan.edu.eg', 'A#hg747klklk', NULL, NULL, NULL, NULL, NULL, 'sold', 3, 107, '2026-03-12 15:11:57', '2026-03-12 14:21:48'),
(363, 20, 'email', 'MOMEN.ROSHY@fth.helwan.edu.eg', 'A3hgf458578', NULL, NULL, NULL, NULL, NULL, 'sold', 35, 111, '2026-03-14 12:55:18', '2026-03-12 14:21:48'),
(364, 20, 'email', 'CHRISTINE.BARSOUM@fth.helwan.edu.eg', 'A#hgh45478', NULL, NULL, NULL, NULL, NULL, 'sold', 36, 114, '2026-03-15 08:57:05', '2026-03-12 14:21:48'),
(365, 20, 'email', 'ALHA.HUSSEIN@fth.helwan.edu.eg', 'A#gfh5747487', NULL, NULL, NULL, NULL, NULL, 'sold', 37, 116, '2026-03-15 09:03:28', '2026-03-12 14:21:48'),
(366, 20, 'email', 'ALHA.HUSSEIN@fth.helwan.edu.eg', 'A3gf45h745778', NULL, NULL, NULL, NULL, NULL, 'sold', 37, 116, '2026-03-15 09:03:28', '2026-03-12 14:21:48'),
(367, 20, 'email', 'AMANI.MOHAMED@fth.helwan.edu.eg', 'A3nb45478', NULL, NULL, NULL, NULL, NULL, 'sold', 38, 117, '2026-03-15 09:04:56', '2026-03-12 14:21:48'),
(368, 20, 'email', 'RADWA.ABDELALIM@fth.helwan.edu.eg', 'A3hg445878', NULL, NULL, NULL, NULL, NULL, 'sold', 38, 118, '2026-03-15 09:05:06', '2026-03-12 14:21:48'),
(369, 20, 'email', 'MAREHAN.MANSOUR@fth.helwan.edu.eg', '\"a#HG4J5787', NULL, NULL, NULL, NULL, NULL, 'sold', 39, 119, '2026-03-15 09:05:26', '2026-03-12 14:21:48'),
(370, 20, 'email', 'MINA.AZIZ@fth.helwan.edu.eg', 'a3HG5477/7578', NULL, NULL, NULL, NULL, NULL, 'sold', 39, 120, '2026-03-15 09:05:35', '2026-03-12 14:21:48'),
(371, 20, 'email', 'SAMEH.HANNA@fth.helwan.edu.eg', 'a23GFH454857487', NULL, NULL, NULL, NULL, NULL, 'sold', 40, 121, '2026-03-15 09:05:54', '2026-03-12 14:21:48'),
(372, 20, 'email', 'MARWA.SHAIEN@fth.helwan.edu.eg', 'a3HG4575787', NULL, NULL, NULL, NULL, NULL, 'sold', 40, 122, '2026-03-15 09:06:03', '2026-03-12 14:21:48'),
(373, 20, 'email', 'NASHWA.MOHSAN@fth.helwan.edu.eg', 'a#HGH457485748', NULL, NULL, NULL, NULL, NULL, 'sold', 41, 123, '2026-03-15 09:06:21', '2026-03-12 14:21:48'),
(374, 20, 'email', 'ZEINAB.ABDELAZIZ@fth.helwan.edu.eg', 'a3GF4457478', NULL, NULL, NULL, NULL, NULL, 'sold', 41, 124, '2026-03-15 09:06:30', '2026-03-12 14:21:48'),
(375, 20, 'email', 'MOHAMED.HAMED04@fth.helwan.edu.eg', 'awFGHG45748', NULL, NULL, NULL, NULL, NULL, 'sold', 42, 125, '2026-03-15 09:06:49', '2026-03-12 14:21:48'),
(376, 20, 'email', 'NASHWA_IBRAHIM@fth.helwan.edu.eg', 'a3GHFH457/8', NULL, NULL, NULL, NULL, NULL, 'sold', 42, 126, '2026-03-15 09:06:58', '2026-03-12 14:21:48'),
(377, 20, 'email', 'RANA.ABDO@fth.helwan.edu.eg', 'a3HJ45744877', NULL, NULL, NULL, NULL, NULL, 'sold', 45, 127, '2026-03-15 09:07:35', '2026-03-12 14:21:48'),
(378, 20, 'email', 'MYRNA.SHAKER@fth.helwan.edu.eg', 'a3HG547787', NULL, NULL, NULL, NULL, NULL, 'sold', 45, 128, '2026-03-15 09:07:43', '2026-03-12 14:21:48'),
(379, 20, 'email', 'NANCY.GHATTAS@fth.helwan.edu.eg', 'a3HG52457', NULL, NULL, NULL, NULL, NULL, 'sold', 46, 129, '2026-03-15 09:08:11', '2026-03-12 14:21:48'),
(380, 20, 'email', 'BISCHOI.ABDELMASIH@fth.helwan.edu.eg', 'a#HG7877', NULL, NULL, NULL, NULL, NULL, 'sold', 46, 130, '2026-03-15 09:08:29', '2026-03-12 14:21:48'),
(381, 20, 'email', 'SAMEH.MOFTAH@fth.helwan.edu.eg', 'a#HG4785747/8', NULL, NULL, NULL, NULL, NULL, 'sold', 47, 131, '2026-03-15 09:08:54', '2026-03-12 14:21:48'),
(382, 20, 'email', 'FOUAD.ALFAKHARANY@fth.helwan.edu.eg', 'a3HG457878', NULL, NULL, NULL, NULL, NULL, 'sold', 47, 132, '2026-03-15 09:09:05', '2026-03-12 14:21:48'),
(383, 20, 'email', 'MAREHAN.ABDELSHAHID@fth.helwan.edu.eg', '\"a3GFH878787/8', NULL, NULL, NULL, NULL, NULL, 'sold', 48, 133, '2026-03-15 09:09:31', '2026-03-12 14:21:48'),
(384, 20, 'email', 'ISLAM.AHMED01@fth.helwan.edu.eg', 'a3GH74857877', NULL, NULL, NULL, NULL, NULL, 'sold', 49, 135, '2026-03-15 09:10:14', '2026-03-12 14:21:48'),
(385, 20, 'email', 'AHMED.MOHAMED07@fth.helwan.edu.eg', 'a3HG4574587', NULL, NULL, NULL, NULL, NULL, 'sold', 49, 135, '2026-03-15 09:10:14', '2026-03-12 14:21:48'),
(386, 20, 'email', 'AMR.YOUSSEF@fth.helwan.edu.eg', 'a3HG45787', NULL, NULL, NULL, NULL, NULL, 'sold', 50, 136, '2026-03-15 09:11:55', '2026-03-12 14:21:48'),
(387, 20, 'email', 'AMR.YOUSSEF@fth.helwan.edu.eg', 'a3HG457877', NULL, NULL, NULL, NULL, NULL, 'sold', 50, 136, '2026-03-15 09:11:55', '2026-03-12 14:21:48'),
(388, 20, 'email', 'MARIAN.RAWWAS@fth.helwan.edu.eg', 'a#HG545787', NULL, NULL, NULL, NULL, NULL, 'sold', 51, 137, '2026-03-15 09:13:52', '2026-03-12 14:21:48'),
(389, 20, 'email', 'ANGIE.ABDELMALEK@fth.helwan.edu.eg', 'a3GFH47487/8', NULL, NULL, NULL, NULL, NULL, 'sold', 51, 137, '2026-03-15 09:13:52', '2026-03-12 14:21:48'),
(390, 20, 'email', 'MARIA.AZIZ@fth.helwan.edu.eg', 'a3GHKJHJ47474877', NULL, NULL, NULL, NULL, NULL, 'sold', 52, 138, '2026-03-15 09:16:03', '2026-03-12 14:21:48'),
(391, 20, 'email', 'SARAH.HANNA@fth.helwan.edu.eg', 'a#HGJ4H454578', NULL, NULL, NULL, NULL, NULL, 'sold', 52, 138, '2026-03-15 09:16:03', '2026-03-12 15:16:28'),
(392, 20, 'email', 'SARAH.HANNA@fth.helwan.edu.eg', 'a3HG5JH648487', NULL, NULL, NULL, NULL, NULL, 'sold', 53, 139, '2026-03-15 09:20:11', '2026-03-12 15:16:28'),
(393, 20, 'email', 'RABA.TORK@fth.helwan.edu.eg', 'a#JH4J5458', NULL, NULL, NULL, NULL, NULL, 'sold', 53, 139, '2026-03-15 09:20:11', '2026-03-12 15:16:28'),
(394, 20, 'email', 'RANDA.MOHAMED@fth.helwan.edu.eg', 'a3GH4G547487', NULL, NULL, NULL, NULL, NULL, 'sold', 54, 140, '2026-03-15 09:20:58', '2026-03-12 15:16:28'),
(395, 20, 'email', 'SHAIMAA.ABDELHADY@fth.helwan.edu.eg', 'a3HGJ4548577', NULL, NULL, NULL, NULL, NULL, 'sold', 54, 140, '2026-03-15 09:20:58', '2026-03-12 15:16:28'),
(396, 20, 'email', 'KARIM.ALSAYED@fth.helwan.edu.eg', 'a3HGJ424587', NULL, NULL, NULL, NULL, NULL, 'sold', 55, 141, '2026-03-15 09:21:19', '2026-03-12 15:16:28'),
(397, 20, 'email', 'IBRAHIM_HAMUDA@fth.helwan.edu.eg', 'a#JH45488', NULL, NULL, NULL, NULL, NULL, 'sold', 55, 141, '2026-03-15 09:21:19', '2026-03-12 15:16:28'),
(398, 20, 'email', 'DMIANH.SALIB@fth.helwan.edu.eg', 'a3HGH4547487', NULL, NULL, NULL, NULL, NULL, 'sold', 56, 142, '2026-03-15 09:21:38', '2026-03-12 15:16:28'),
(399, 20, 'email', 'ISLAM.ALSAYED02@fth.helwan.edu.eg', 'as#HGH54577787', NULL, NULL, NULL, NULL, NULL, 'sold', 56, 142, '2026-03-15 09:21:38', '2026-03-12 15:16:28'),
(400, 20, 'email', 'NERMINE.OBAYED@fth.helwan.edu.eg', 'a3HJ45644787', NULL, NULL, NULL, NULL, NULL, 'sold', 57, 143, '2026-03-15 09:21:57', '2026-03-12 15:16:28'),
(401, 20, 'email', 'NERMINE.OBAYED@fth.helwan.edu.eg', 'a#HGH54474877', NULL, NULL, NULL, NULL, NULL, 'sold', 57, 143, '2026-03-15 09:21:57', '2026-03-12 15:16:28'),
(402, 20, 'email', 'ASMAA.MOHAMED03@fth.helwan.edu.eg', 'a#JH4545857/8', NULL, NULL, NULL, NULL, NULL, 'sold', 60, 146, '2026-03-15 09:23:11', '2026-03-12 15:16:28'),
(403, 20, 'email', 'MANAL.HADAD@fth.helwan.edu.eg', 'a3HG454585778', NULL, NULL, NULL, NULL, NULL, 'sold', 60, 146, '2026-03-15 09:23:11', '2026-03-12 15:16:28'),
(404, 20, 'email', 'NOHA.MOHAMED01@fth.helwan.edu.eg', 'a3JH47874878', NULL, NULL, NULL, NULL, NULL, 'sold', 61, 147, '2026-03-15 09:23:30', '2026-03-12 15:16:28'),
(405, 20, 'email', 'MOHAMED.SULTAN@fth.helwan.edu.eg', 'a#JHJ4574787', NULL, NULL, NULL, NULL, NULL, 'sold', 61, 147, '2026-03-15 09:23:30', '2026-03-12 15:16:28'),
(406, 20, 'email', 'GAMAL.FARHAN@fth.helwan.edu.eg', 'a3HG74574877', NULL, NULL, NULL, NULL, NULL, 'sold', 68, 152, '2026-03-15 09:30:53', '2026-03-12 15:16:28'),
(407, 20, 'email', 'ALHA.KHAFAGA@fth.helwan.edu.eg', 'a3HG64547487', NULL, NULL, NULL, NULL, NULL, 'sold', 68, 152, '2026-03-15 09:30:53', '2026-03-12 15:16:28'),
(408, 20, 'email', 'OSAMA.HAGAG@fth.helwan.edu.eg', 'a#HGG4574878', NULL, NULL, NULL, NULL, NULL, 'sold', 70, 154, '2026-03-15 09:55:52', '2026-03-12 15:16:28'),
(409, 20, 'email', 'NESAMA.AHMED@fth.helwan.edu.eg', 'a32GH4574748747', NULL, NULL, NULL, NULL, NULL, 'sold', 70, 154, '2026-03-15 09:55:52', '2026-03-12 15:16:28'),
(410, 20, 'email', 'MARINA.BARBARY@fth.helwan.edu.eg', 'a3GGH4547487', NULL, NULL, NULL, NULL, NULL, 'sold', 1, 158, '2026-03-15 21:03:54', '2026-03-12 15:16:28'),
(411, 20, 'email', 'MUSTAFA.AHMED@fth.helwan.edu.eg', 'a#HG454578787', NULL, NULL, NULL, NULL, NULL, 'sold', 71, 161, '2026-03-17 06:59:56', '2026-03-12 15:16:28'),
(412, 20, 'email', 'MAHMOUD.MATR@fth.helwan.edu.eg', 'a3HG345878', NULL, NULL, NULL, NULL, NULL, 'sold', 72, 163, '2026-03-18 05:53:50', '2026-03-12 15:16:28'),
(413, 20, 'email', 'TAMER_GAD@fth.helwan.edu.eg', 'a3HBG54445787', NULL, NULL, NULL, NULL, NULL, 'sold', 11, 165, '2026-03-18 18:28:00', '2026-03-12 15:16:28'),
(414, 20, 'email', 'TAMER_GAD@fth.helwan.edu.eg', 'a#JK45454748', NULL, NULL, NULL, NULL, NULL, 'sold', 1, 170, '2026-04-10 20:54:22', '2026-03-12 15:16:28'),
(415, 20, 'email', 'MUSTAFA.ATTIA01@fth.helwan.edu.eg', 'a3HGJ4574857', NULL, NULL, NULL, NULL, NULL, 'sold', 102, 174, '2026-04-26 11:52:22', '2026-03-12 15:16:28'),
(416, 20, 'email', 'SARAH.GERGES@fth.helwan.edu.eg', 'a3HJJ47474877', NULL, NULL, NULL, NULL, NULL, 'sold', 72, 177, '2026-05-14 03:55:20', '2026-03-12 15:16:28'),
(417, 20, 'email', 'MOHAMED.RAMADAN02@fth.helwan.edu.eg', 'a#HGH48577', NULL, NULL, NULL, NULL, NULL, 'sold', 105, 179, '2026-05-24 08:51:05', '2026-03-12 15:16:28'),
(418, 20, 'email', 'AHMED.MOHAMED13@fth.helwan.edu.eg', 'a3HGJ45457', NULL, NULL, NULL, NULL, NULL, 'sold', 121, 180, '2026-07-27 13:35:48', '2026-03-12 15:16:28'),
(419, 20, 'email', 'MADONA.SHENOUDA@fth.helwan.edu.eg', 'A#hg245457478', NULL, NULL, NULL, NULL, NULL, 'sold', 121, 180, '2026-07-27 13:35:48', '2026-03-12 15:16:28'),
(420, 20, 'email', 'YARA.YOUSSEF@fth.helwan.edu.eg', 'A3gh4545778', NULL, NULL, NULL, NULL, NULL, 'sold', 121, 181, '2026-07-27 22:21:49', '2026-03-12 15:16:28'),
(421, 20, 'email', 'YARA.YOUSSEF@fth.helwan.edu.eg', 'A#hg4j57878', NULL, NULL, NULL, NULL, NULL, 'sold', 28, 185, '2026-09-14 14:27:25', '2026-03-12 15:16:28'),
(422, 20, 'email', 'EMAN.KHAFAGY@fth.helwan.edu.eg', 'SAz3hg45jh4778', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(423, 20, 'email', 'EMAN.KHAFAGY@fth.helwan.edu.eg', 'A#jh4j5487', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(424, 20, 'email', 'SAMUEL.RIYADH@fth.helwan.edu.eg', 'A3hgf457487', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(425, 20, 'email', 'BASSEM.ABOELSAUD@fth.helwan.edu.eg', 'A3hg457487/8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(426, 20, 'email', 'NESMA.MOHAMED@fth.helwan.edu.eg', 'A3hg56746577', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(427, 20, 'email', 'MUSTAFA.SEODI@fth.helwan.edu.eg', 'A#hgf4h5747478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(428, 20, 'email', 'DIANA.GHALY@fth.helwan.edu.eg', 'A3ghh454857', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(429, 20, 'email', 'HAYDI.EZER@fth.helwan.edu.eg', 'A3hg45778', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(430, 20, 'email', 'MOHAMED.HASSAN07@fth.helwan.edu.eg', 'A3hg457478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(431, 20, 'email', 'AHMED.SALIM@fth.helwan.edu.eg', 'A3hg56777/8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:28'),
(432, 20, 'email', 'FAROUK.ABDO@fth.helwan.edu.eg\"', 'A#hghj45485', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(433, 20, 'email', 'AHMED.OQAB@fth.helwan.edu.eg', 'A3hg4j574857', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(434, 20, 'email', 'YASMINE.ELAWA@fth.helwan.edu.eg', 'A3gfh4478778', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(435, 20, 'email', 'NOURAN.ABBAS@fth.helwan.edu.eg', 'A3jhj45487', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(436, 20, 'email', 'BISCHOI.BBAWI@fth.helwan.edu.eg', 'A34gfg44577/8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(437, 20, 'email', 'MOHAMED.ALNAGAR01@fth.helwan.edu.eg', 'A#hj45748578', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(438, 20, 'email', 'AHMED.KHALIL@fth.helwan.edu.eg', 'A#hgh45747478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(439, 20, 'email', 'AHMED.KHALIL@fth.helwan.edu.eg', 'A#gf4545457787', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(440, 20, 'email', 'FADY.RAFLA@fth.helwan.edu.eg', 'A3jh45457787', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(441, 20, 'email', 'AMEL.AHMED@fth.helwan.edu.eg', 'A3hg45457487', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(442, 20, 'email', 'NOURA.ALSHAHAT@fth.helwan.edu.eg', 'A3hfg4h5857487', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(443, 20, 'email', 'MAHMOUD.MAHMOUD04@fth.helwan.edu.eg', 'A#hjj457478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(444, 20, 'email', 'YASSER_IBRAHIM02@fth.helwan.edu.eg', 'A3hg4h57478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(445, 20, 'email', 'AHMED.ALI14@fth.helwan.edu.eg', 'A#hg44857478', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(446, 20, 'email', 'OMAR.KHALIL@fth.helwan.edu.eg', 'A3hgh46574877/', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(447, 20, 'email', 'SAMIRA.IBRAHIM@fth.helwan.edu.eg', 'A3hgh5788778', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(448, 20, 'email', 'SHAHENDA.BAGHA@fth.helwan.edu.eg', 'A#hg457887', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(449, 20, 'email', 'MAHMOUD.ALI01@fth.helwan.edu.eg', 'A3hgh45748778', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-12 15:16:29'),
(451, 21, 'rdp', NULL, 'WALED@#63D521ASDA87', NULL, NULL, NULL, '20.85.244.3', 'WALED', 'sold', 1, 113, '2026-03-14 13:37:52', '2026-03-14 12:32:24'),
(452, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.244.3', 'WALED', 'sold', 3, 156, '2026-03-15 19:42:05', '2026-03-14 12:32:24'),
(453, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.214.3', 'WALED', 'sold', 3, 157, '2026-03-15 19:42:13', '2026-03-14 12:32:24'),
(454, 21, 'rdp', NULL, 'WALED@#24SSSSASDA87', NULL, NULL, NULL, '20.87.214.3', 'WALED', 'sold', 3, 157, '2026-03-15 19:42:13', '2026-03-14 12:32:24'),
(455, 21, 'rdp', NULL, 'WALED@#63D521ASDA87', NULL, NULL, NULL, '20.85.244.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(456, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.244.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(457, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.214.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(458, 21, 'rdp', NULL, 'WALED@#24SSSSASDA87', NULL, NULL, NULL, '20.87.214.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(459, 21, 'rdp', NULL, 'WALED@#63D521ASDA87', NULL, NULL, NULL, '20.85.244.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(460, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.244.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(461, 21, 'rdp', NULL, 'WALED@#24521ASDA87', NULL, NULL, NULL, '20.81.214.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(462, 21, 'rdp', NULL, 'WALED@#24SSSSASDA87', NULL, NULL, NULL, '20.87.214.3', 'WALED', 'available', NULL, NULL, NULL, '2026-03-14 12:32:24'),
(463, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'sold', 3, 112, '2026-03-14 13:25:30', '2026-03-14 13:23:21'),
(464, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'sold', 3, 155, '2026-03-15 19:42:00', '2026-03-14 13:23:23'),
(465, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:23'),
(466, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:24'),
(467, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.52.87.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:24'),
(468, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.53.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:24'),
(469, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:24'),
(470, 22, 'rdp', NULL, 'mrbeat#521asd8asd7as', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-03-14 13:23:24'),
(471, 15, 'email', 'social201510207@social.helwan.edu.eg', 'b#54acGf8', NULL, NULL, NULL, NULL, NULL, 'sold', 121, 182, '2026-07-27 22:26:42', '2026-03-15 18:27:26'),
(472, 15, 'email', 'social201510208@social.helwan.edu.eg', 'vc5v5d#7vc6', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(473, 15, 'email', 'social201510209@social.helwan.edu.eg', 'v#6dGdf68', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(474, 15, 'email', 'social201510210@social.helwan.edu.eg', 'bv6n#5vcGd57', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(475, 15, 'email', 'social201510211@social.helwan.edu.eg', 'v5vb6#Ngf6', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(476, 15, 'email', 'social201510212@social.helwan.edu.eg', 'vc25vb7#8b', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(477, 15, 'email', 'social201510214@social.helwan.edu.eg', 'v5bmgt31293#', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(478, 15, 'email', 'social201510215@social.helwan.edu.eg', 'bv65n87#Hf4', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:27:26'),
(479, 20, 'email', 'MARWA.ALNAGAR@med.suez.edu.eg', 'N#52f10dca8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:29:53'),
(480, 20, 'email', 'MANAL.ALBADAWY@med.suez.edu.eg', 'B#641d8sa', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:29:53'),
(481, 20, 'email', 'SALAHELDIN.KSA@med.suez.edu.eg', 'R#21d0ca8s', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:29:53'),
(482, 20, 'email', 'AHMED.ALZO@med.suez.edu.eg', 'B#d54as1d8as', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:29:53'),
(483, 20, 'email', 'AMR.DESOUKY@med.suez.edu.eg', 'A#5210dcs', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:29:53'),
(484, 20, 'email', 'REHAB.ABDELKARIM@med.suez.edu.eg', 'A#GFHJ85614', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:30:25'),
(485, 20, 'email', 'OLA.LHETAH@med.suez.edu.eg', 'A#BGYH5545', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:30:25'),
(486, 20, 'email', 'GIHAD.MOHAMMAD@med.suez.edu.eg', 'H#541dcas8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:30:40'),
(487, 20, 'email', 'SALLY.ALATLAH@med.suez.edu.eg', 'S#d85a41s', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:30:40'),
(488, 20, 'email', 'AHMED.MOHAMED02@med.suez.edu.eg', 'B#4d1xa0s8', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:30:40'),
(489, 20, 'email', 'alaa.ibrahim.std1@pharm.asu.edu.eg', 'hyukyukyku@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(490, 20, 'email', 'alaa.ismail.std2@pharm.asu.edu.eg', 'grehguerhuger@5', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(491, 20, 'email', 'alaa.salim.std1@pharm.asu.edu.eg', 'fthtrhrthrt@54', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(492, 20, 'email', 'alaa.desouky.std@pharm.asu.edu.eg', 'rehgyeyge@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(493, 20, 'email', 'alaa.ismail.std@pharm.asu.edu.eg', 'nenjejfnej@5', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(494, 20, 'email', 'islam.abdelaziz.std@pharm.asu.edu.eg', 'gygygygyg@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(495, 20, 'email', 'randa.youssif.std@pharm.asu.edu.eg', 'hgygygygygy@555', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(496, 20, 'email', 'mai.elshemi.std@pharm.asu.edu.eg', 'thththtr@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(497, 20, 'email', 'mai.seam.std@pharm.asu.edu.eg', 'rhrthrthtrr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(498, 20, 'email', 'mai.nazeer.std@pharm.asu.edu.eg', 'fnfgngfng@66', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(499, 20, 'email', 'mai.ghetas.std@pharm.asu.edu.eg', 'hgygygygyg@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(500, 20, 'email', 'mai.nada.std@pharm.asu.edu.eg', 'ugugugugu@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(501, 20, 'email', 'maisan.nabhan.std@pharm.asu.edu.eg', 'hgghghghgh@55', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:25'),
(502, 20, 'email', 'manal.hassanain.std@pharm.asu.edu.eg', 'edu#fcvasdopj5614', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(503, 20, 'email', 'mayar.hassanain.std@pharm.asu.edu.eg', 'edu#2536c10asg897', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(504, 20, 'email', 'mohamed.ali.std8@pharm.asu.edu.eg', 'edu#23610c4as89g7', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(505, 20, 'email', 'mohamed.ali.std7@pharm.asu.edu.eg', 'edu#25c10asg897', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(506, 20, 'email', 'mohamed.marea.std@pharm.asu.edu.eg', 'edu#52c10as4g897', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(507, 20, 'email', 'merna.yakout.std@pharm.asu.edu.eg', 'edu#20ca10sg987', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(508, 20, 'email', 'merna.aziz.std@pharm.asu.edu.eg', 'edu#6320c1as4g897', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(509, 20, 'email', 'merna.abdelsayed.std@pharm.asu.edu.eg', 'edu#32.0c1asg987', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(510, 20, 'email', 'merna.maseha.std@pharm.asu.edu.eg', 'edui#5261c4asg987', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(511, 20, 'email', 'merna.ishak.std@pharm.asu.edu.eg', 'edu#5210cas4g987', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:33:33'),
(512, 20, 'email', 'enji.aziz1.he@heco.helwan.edu.eg', 'rhtrhtrhtrhrthtr@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(513, 20, 'email', 'entsar.abouelmokhtar.h@heco.helwan.edu.eg', 'hrthrthrthvbc@7', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(514, 20, 'email', 'esraa.mohamed2.h@heco.helwan.edu.eg', 'htrhthtrhrth@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(515, 20, 'email', 'esraa.mohamed2.he@heco.helwan.edu.eg', 'tjtyjytjtyjtyj@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(516, 20, 'email', 'fareda.hashem.h@heco.helwan.edu.eg', 'ytjytjytjtyjty@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(517, 20, 'email', 'fareda.hashem.he@heco.helwan.edu.eg', 'yukyukuykyuky@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(518, 20, 'email', 'fatma.mohamed6.he@heco.helwan.edu.eg', 'tyjtyjytjtyjtyj@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(519, 20, 'email', 'fatma.mohamed10.he@heco.helwan.edu.eg', 'kyukyukghnghn@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(520, 20, 'email', 'fatma.mohamed10.h@heco.helwan.edu.eg', 'rhtrhtrhbbfbdf@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(521, 20, 'email', 'fatma.mohamed4.he@heco.helwan.edu.eg', 'yryjtyjytjytjty@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(522, 20, 'email', 'fatma.mohamed.h@heco.helwan.edu.eg', 'ehgergergergre@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(523, 20, 'email', 'fatma.mohamed6.h@heco.helwan.edu.eg', 'trhrthtrhthr@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(524, 20, 'email', 'fatma.mohamed2.he@heco.helwan.edu.eg', 'yjtyjyjtyjtyjyt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(525, 20, 'email', 'fatma.mohamed9.h@heco.helwan.edu.eg', 'jyrjryjrjrtjfnfbvnc@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(526, 20, 'email', 'fatma.mohamed7.h@heco.helwan.edu.eg', 'yjtyjytjytjyt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(527, 20, 'email', 'fatma.mohamed5.he@heco.helwan.edu.eg', 'rthtrhtrhtrhrt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(528, 20, 'email', 'fatma.mohamed9.he@heco.helwan.edu.eg', 'regregerger@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(529, 20, 'email', 'fatma.mohamed11.he@heco.helwan.edu.eg', 'yukyukyukkuyuk@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(530, 20, 'email', 'fatma.mohamed1.h@heco.helwan.edu.eg', 'ytjytjytjytjty@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(531, 20, 'email', 'fatma.mohamed1.he@heco.helwan.edu.eg', 'tyjytjtyjtyjt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(532, 20, 'email', 'fatma.mohamed11.h@heco.helwan.edu.eg', 'tyjtyjtyjytjyt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(533, 20, 'email', 'fatma.mohamed3.h@heco.helwan.edu.eg', 'rhtrhtrhrt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(534, 20, 'email', 'fatma.mohamed3.he@heco.helwan.edu.eg', 'tjytjytjtyjty@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(535, 20, 'email', 'fatma.mohamed5.h@heco.helwan.edu.eg', 'rhtrhrthrthrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(536, 20, 'email', 'fatma.mohamed7.he@heco.helwan.edu.eg', 'ytjtyjtyjytjty@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(537, 20, 'email', 'fatma.hassan3.h@heco.helwan.edu.eg', 'thrthtrhtrhrthtrh@789', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(538, 20, 'email', 'fatma.hassan4.he@heco.helwan.edu.eg', 'ykuykyukuykuykuy@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(539, 20, 'email', 'fatma.hassan.h@heco.helwan.edu.eg', 'tyjtyjytjytjty@444', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(540, 20, 'email', 'fatma.hassan2.he@heco.helwan.edu.eg', 'ykuykyukuykyukyu@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(541, 20, 'email', 'fatma.hassan3.he@heco.helwan.edu.eg', 'ukuykykuykyuky@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(542, 20, 'email', 'fatma.hassan1.he@heco.helwan.edu.eg', 'tkytkjytkyktykt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(543, 20, 'email', 'fatma.hassan1.h@heco.helwan.edu.eg', 'yjytjtrjtrjjrjyt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(544, 20, 'email', 'fatma.hassan5.he@heco.helwan.edu.eg', 'tjtyjtyjtyjty@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(545, 20, 'email', 'fatma.hassan5.h@heco.helwan.edu.eg', 'rjyjyrjrtjrjrt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(546, 20, 'email', 'fatma.hassan2.h@heco.helwan.edu.eg', 'tyjytjtyjtyjty@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(547, 20, 'email', 'fatma.hassan4.h@heco.helwan.edu.eg', 'ryjtjytjtyjt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(548, 20, 'email', 'fatma.hassan.he@heco.helwan.edu.eg', 'eheheherherhe@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(549, 20, 'email', 'fatma.abouelaleem4.h@heco.helwan.edu.eg', '4y54y554y45y54@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(550, 20, 'email', 'fatma.abouelaleem1.he@heco.helwan.edu.eg', 'jtjrtjhrtjrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(551, 20, 'email', 'fatma.abouelaleem3.h@heco.helwan.edu.eg', 'yjytj67u67u65u@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(552, 20, 'email', 'fatma.abouelaleem5.h@heco.helwan.edu.eg', 'tjtyjtyjtyjytjt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(553, 20, 'email', 'fatma.abouelaleem3.he@heco.helwan.edu.eg', 'regergegegeg@7521', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:34:04'),
(554, 20, 'email', 'eman.hassan4.he@heco.helwan.edu.eg', 'htrhtrhtrhtr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(555, 20, 'email', 'eman.hassan2.h@heco.helwan.edu.eg', 'rghtrhrthrth@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(556, 20, 'email', 'eman.hassan1.he@heco.helwan.edu.eg', 'rthtrhrthrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(557, 20, 'email', 'eman.hassan2.he@heco.helwan.edu.eg', 'regregregrge@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(558, 20, 'email', 'eman.hassan3.he@heco.helwan.edu.eg', 'rhrthrthrthtr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(559, 20, 'email', 'eman.hassan1.h@heco.helwan.edu.eg', 'rthrthrthrth@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(560, 20, 'email', 'eman.hassan4.h@heco.helwan.edu.eg', 'rthtrhhtrthhtrh@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(561, 20, 'email', 'eman.hassan3.h@heco.helwan.edu.eg', 'trhrthtrhrt@456', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(562, 20, 'email', 'eman.hassan6.he@heco.helwan.edu.eg', 'hrthtrhrthtrh@4521', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(563, 20, 'email', 'eman.saleh4.h@heco.helwan.edu.eg', 'rththtrhtrhrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(564, 20, 'email', 'eman.saleh1.h@heco.helwan.edu.eg', 'rthtrhtrhrttr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(565, 20, 'email', 'eman.saleh.he@heco.helwan.edu.eg', 'rthtrhtrhtr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(566, 20, 'email', 'eman.saleh4.he@heco.helwan.edu.eg', 'trhrhrthrthrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(567, 20, 'email', 'eman.saleh3.he@heco.helwan.edu.eg', 'ergergregre@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(568, 20, 'email', 'eman.saleh2.he@heco.helwan.edu.eg', 'eherhrehreheh@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(569, 20, 'email', 'eman.saleh1.he@heco.helwan.edu.eg', 'regergergergreg@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(570, 20, 'email', 'eman.hussien3.he@heco.helwan.edu.eg', 'thrthtrhtrhx@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(571, 20, 'email', 'eman.saleh5.he@heco.helwan.edu.eg', 'jtrhtrhtrhtrhrt@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(572, 20, 'email', 'eman.saleh2.h@heco.helwan.edu.eg', 'ergergregergre@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(573, 20, 'email', 'eman.faragallah.h@heco.helwan.edu.eg', 'trhtrhtrhtr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(574, 20, 'email', 'eman.faragallah3.he@heco.helwan.edu.eg', 'ergergregre@444', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(575, 20, 'email', 'eman.faragallah1.he@heco.helwan.edu.eg', 'rgergerggrgrrg@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(576, 20, 'email', 'eman.faragallah5.h@heco.helwan.edu.eg', 'tyjytjytjtyjytj@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(577, 20, 'email', 'eman.faragallah4.h@heco.helwan.edu.eg', 'trhtrhtrhtrhtr@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(578, 20, 'email', 'eman.faragallah5.he@heco.helwan.edu.eg', 'rthtrhtrhtrh@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(579, 20, 'email', 'eman.faragallah2.he@heco.helwan.edu.eg', 'trhtrhrthtrh@77', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(580, 20, 'email', 'eman.faragallah.he@heco.helwan.edu.eg', 'rthtrhrthtrh@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(581, 20, 'email', 'eman.faragallah2.h@heco.helwan.edu.eg', 'rthtrhtrhtrhrt@44', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-15 18:38:05'),
(582, 23, 'email', '21212017200204@foe.zu.edu.eg', 'edu#5c1x7nv11', NULL, NULL, NULL, NULL, NULL, 'sold', 78, 167, '2026-03-28 03:47:05', '2026-03-17 22:39:16'),
(583, 23, 'email', '21212017200014@foe.zu.edu.eg', 'edu#20225vQ', NULL, NULL, NULL, NULL, NULL, 'sold', 78, 167, '2026-03-28 03:47:05', '2026-03-17 22:39:16'),
(584, 23, 'email', '21212017202319@foe.zu.edu.eg', 's#1a5x4d7a81g', NULL, NULL, NULL, NULL, NULL, 'sold', 78, 167, '2026-03-28 03:47:05', '2026-03-17 22:39:16'),
(585, 23, 'email', '21212017200015@foe.zu.edu.eg', 'edu#48cAx12a', NULL, NULL, NULL, NULL, NULL, 'sold', 72, 168, '2026-04-05 12:01:35', '2026-03-17 22:39:16'),
(586, 23, 'email', 'mohamedabdallah5@com.svu.edu.eg', 'ecxn#@65sd11', NULL, NULL, NULL, NULL, NULL, 'sold', 72, 169, '2026-04-06 10:22:01', '2026-03-17 22:39:16'),
(592, 23, 'email', '21212017201609@foe.zu.edu.eg', '2x1csa5d74ca#', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-17 22:39:16'),
(593, 23, 'email', 'mona.ali2@com.svu.edu.eg', 'A#5210dxas87', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-17 22:39:16'),
(595, 23, 'email', 'mohamed.ahmedp27@com.svu.edu.eg', 'A#210cxasd87', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-17 22:39:16'),
(597, 23, 'email', 'ismail.abdellatiefp2@com.svu.edu.eg', 'V#52d1xasdas87d', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-17 22:39:16'),
(598, 23, 'email', '21212017201625@foe.zu.edu.eg', 'Awaisasdf123@', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-03-17 22:39:16'),
(603, 22, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(604, 22, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(605, 22, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(606, 22, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(607, 22, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(608, 22, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(609, 22, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(610, 22, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(611, 22, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(612, 22, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(613, 22, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(614, 22, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(615, 22, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(616, 22, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(617, 22, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(618, 22, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(619, 22, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(620, 22, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(621, 22, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(622, 22, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(623, 22, 'rdp', NULL, 'WALED@#24SSSSASDA87', NULL, NULL, NULL, '20.64.34.66', 'WALED', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(624, 22, 'rdp', NULL, 'SDACS54SSSSASDA87', NULL, NULL, NULL, '20.64.34.66', 'SCADDA', 'available', NULL, NULL, NULL, '2026-04-01 05:04:00'),
(625, 25, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'sold', 1, 175, '2026-04-27 12:52:31', '2026-04-01 05:06:26'),
(626, 25, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:26'),
(627, 25, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:26'),
(628, 25, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:26'),
(629, 25, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(630, 25, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(631, 25, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(632, 25, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(633, 25, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(634, 25, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:27'),
(635, 25, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(636, 25, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(637, 25, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(638, 25, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(639, 25, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(640, 25, 'rdp', NULL, 'mrbeat#521asd8asd7a', NULL, NULL, NULL, '20.54.85.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(641, 25, 'rdp', NULL, 'mrbeatsdasd8asd7a', NULL, NULL, NULL, '20.54.84.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(642, 25, 'rdp', NULL, 'mrb11at1fsddsfd7a', NULL, NULL, NULL, '20.51.54.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(643, 25, 'rdp', NULL, '#6sdasasd8asd7a', NULL, NULL, NULL, '20.54.34.16', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(644, 25, 'rdp', NULL, 'mrbeatsdaSDX5A', NULL, NULL, NULL, '20.64.34.66', 'mrbeat', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(645, 25, 'rdp', NULL, 'WALED@#24SSSSASDA87', NULL, NULL, NULL, '20.64.34.66', 'WALED', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(646, 25, 'rdp', NULL, 'SDACS54SSSSASDA87', NULL, NULL, NULL, '20.64.34.66', 'SCADDA', 'available', NULL, NULL, NULL, '2026-04-01 05:06:28'),
(681, 28, 'email', '21212017200044@foe.zu.edu.eg', 'edu@#1x5ca7a', NULL, NULL, NULL, NULL, NULL, 'sold', 1, 171, '2026-04-13 01:31:46', '2026-04-08 11:12:07'),
(682, 28, 'email', '21212017200327@foe.zu.edu.eg', 'edu#36666vcxsadg7', NULL, NULL, NULL, NULL, NULL, 'sold', 98, 172, '2026-04-19 12:29:13', '2026-04-08 11:12:07'),
(684, 28, 'email', 'yasmeen.mohamed.std10@law.asu.edu.eg', 'cxa#45n2asd8g', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-04-08 11:12:07'),
(685, 28, 'email', 'yehia.hassan.std@cis.asu.edu.eg', 'eduvbs6#2ca97v', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-04-08 11:12:07'),
(687, 28, 'email', 'UGS.190305ins@nursing.suez.edu.eg', 'asd14214a!4s@', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-04-08 11:12:07');
INSERT INTO `product_stock` (`id`, `product_id`, `type`, `email`, `pass`, `twofa_code`, `access_email`, `access_pass`, `host`, `user`, `status`, `sold_to_user_id`, `sold_order_id`, `sold_at`, `created_at`) VALUES
(688, 28, 'email', 'eman.bakry@com.svu.edu.eg', 'ecnx#5ac41as', NULL, NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-04-08 11:12:07'),
(696, 27, 'rdp', NULL, '07/31', NULL, NULL, NULL, '5236860114265657', '469', 'sold', 120, 183, '2026-08-02 09:15:03', '2026-07-22 10:19:55'),
(697, 27, 'rdp', NULL, '07/31', NULL, NULL, NULL, '5236 8601 7398 9734', '101', 'available', NULL, NULL, NULL, '2026-07-22 10:23:24'),
(698, 27, 'rdp', NULL, '06/31', NULL, NULL, NULL, '5342711012181119', '695', 'available', NULL, NULL, NULL, '2026-07-22 10:24:19'),
(699, 27, 'rdp', NULL, '06/31', NULL, NULL, NULL, '5342711032692582', '838', 'available', NULL, NULL, NULL, '2026-07-22 10:24:59'),
(700, 31, 'email_2fa', 'KARIM.MOHAMMAD@techedu.helwan.edu.eg', '222UjITx7mX', 'gdnkzvgxhcw6gbl7', NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-10-09 11:20:13'),
(701, 31, 'email_2fa', 'MAHMOUD.ABOYAZID@techedu.helwan.edu.eg', 'x223YBQ8JX', 'qhbybmwyxptqfzxt', NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-10-09 11:28:46'),
(702, 31, 'email_2fa', 'MOHAMED.GABAL@techedu.helwan.edu.eg', '9221JspxYb', 'gp26bydwqd6mhb7r', NULL, NULL, NULL, NULL, 'available', NULL, NULL, NULL, '2026-10-09 11:28:46');

-- --------------------------------------------------------

--
-- Table structure for table `seller_reviews`
--

CREATE TABLE `seller_reviews` (
  `id` int(11) NOT NULL,
  `seller_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seller_reviews`
--

INSERT INTO `seller_reviews` (`id`, `seller_id`, `user_id`, `rating`, `created_at`, `updated_at`) VALUES
(1, 10, 3, 5, '2026-03-07 05:32:48', '2026-03-07 05:32:48'),
(2, 10, 12, 5, '2026-03-07 14:51:32', '2026-03-07 14:56:01'),
(4, 0, 12, 5, '2026-03-07 14:56:04', '2026-03-07 15:21:35'),
(19, 0, 13, 5, '2026-03-07 15:23:00', '2026-03-07 15:23:02'),
(21, 10, 13, 5, '2026-03-07 15:23:22', '2026-03-07 15:23:22'),
(22, 1, 11, 5, '2026-03-07 17:27:56', '2026-03-09 03:02:00'),
(25, 3, 12, 5, '2026-03-07 23:06:46', '2026-03-07 23:06:46'),
(29, 1, 3, 5, '2026-03-12 15:12:00', '2026-03-14 13:25:43'),
(33, 1, 71, 5, '2026-03-18 00:56:22', '2026-03-18 00:56:27');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `k` varchar(100) NOT NULL,
  `v` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`k`, `v`, `updated_at`) VALUES
('announce_enabled', '1', '2026-10-09 11:21:39'),
('announce_text', 'Register now and receive a gift worth $1', '2026-10-09 11:21:39'),
('font_family', 'Cairo', '2026-03-05 23:09:58'),
('font_size', '15', '2026-03-06 01:04:05'),
('font_weight', '401', '2026-03-06 01:03:58'),
('gift_amount', '0', '2026-03-06 12:07:33'),
('gift_currency', 'usdt', '2026-03-06 12:07:33'),
('gift_enabled', '0', '2026-03-06 12:07:33'),
('home_banner_enabled', '0', '2026-03-05 13:30:06'),
('home_banner_text', 'Welcome to Mr Beat Pay', '2026-03-05 13:30:06'),
('payment_enabled', '1', '2026-10-09 11:21:39'),
('seller_commission_balance', '1.2', '2026-03-08 15:32:51'),
('seller_commission_enabled', '0', '2026-10-09 11:21:39'),
('seller_commission_percent', '20', '2026-10-09 11:21:39'),
('site_icon', 'uploads/site/site_icon_1772800800_6389.ico', '2026-03-06 12:40:00'),
('site_lang_default', 'en', '2026-10-09 11:21:39'),
('site_name', 'Mr Beat Store', '2026-10-09 11:21:39'),
('theme_bg', '#ffffff', '2026-03-07 21:11:01'),
('theme_bg_light', '#ffffff', '2026-03-07 21:11:01'),
('theme_border', '1', '2026-03-07 23:02:36'),
('theme_card', '#ffffff', '2026-03-07 21:11:01'),
('theme_card_light', '#ffffff', '2026-03-07 21:11:01'),
('theme_glass', '0', '2026-03-07 23:02:36'),
('theme_gradient_from', '#ffffff', '2026-03-07 21:11:01'),
('theme_gradient_from_light', '#ffffff', '2026-03-07 21:11:01'),
('theme_gradient_to', '#ffffff', '2026-03-07 21:11:01'),
('theme_gradient_to_light', '#ffffff', '2026-03-07 21:11:01'),
('theme_mode', 'light', '2026-10-09 11:21:39'),
('theme_preset', 'glass_light', '2026-10-09 11:21:39'),
('theme_primary', '#d08080', '2026-03-07 21:11:01'),
('theme_primary_light', '#ff0000', '2026-03-07 21:11:01'),
('theme_radius', '22', '2026-03-07 23:02:36'),
('theme_shadow', '24', '2026-03-07 23:02:36'),
('theme_text', '#f50000', '2026-03-07 21:11:01'),
('theme_text_light', '#ff0000', '2026-03-07 21:11:01'),
('ui_bg', '#ff0000', '2026-03-07 23:02:36'),
('ui_card', '#000000', '2026-03-07 23:02:36'),
('ui_g1', '#000000', '2026-03-07 23:02:36'),
('ui_g2', '#000000', '2026-03-07 23:02:36'),
('ui_primary', '#000000', '2026-03-07 23:02:36'),
('ui_text', '#000000', '2026-03-07 23:02:36'),
('welcome_bonus_amount', '0.1', '2026-10-09 11:21:39'),
('welcome_bonus_enabled', '1', '2026-10-09 11:21:39');

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `subject` varchar(190) NOT NULL,
  `status` enum('open','pending','closed') NOT NULL DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tickets`
--

INSERT INTO `tickets` (`id`, `user_id`, `subject`, `status`, `created_at`, `closed_at`) VALUES
(1, 3, 'saad', 'closed', '2026-03-05 20:49:07', '2026-03-06 11:40:36'),
(2, 3, 'warda', 'closed', '2026-03-06 11:09:43', '2026-03-07 00:32:24'),
(3, 10, 'saad', 'closed', '2026-03-06 21:16:05', '2026-03-07 00:32:32'),
(4, 1, 'saad', 'closed', '2026-03-06 22:20:44', '2026-03-07 00:54:30'),
(5, 10, 'Unblock Request', 'closed', '2026-03-06 22:29:23', '2026-03-07 00:54:34'),
(6, 1, 'سيشسي', 'closed', '2026-03-06 23:39:56', '2026-03-07 00:54:42'),
(7, 10, 'شسيش', 'closed', '2026-03-06 23:55:16', '2026-03-07 00:54:27'),
(8, 1, 'سشيش', 'closed', '2026-03-07 00:18:09', '2026-03-07 00:54:24'),
(9, 10, 'شسيشسيشس', 'closed', '2026-03-07 00:31:52', '2026-03-07 00:54:21'),
(10, 1, 'سيشسي', 'closed', '2026-03-07 00:59:40', '2026-03-07 02:59:46'),
(11, 1, 'saad', 'closed', '2026-03-07 01:57:21', '2026-03-07 02:59:44'),
(12, 3, 'saad', 'closed', '2026-03-07 02:59:14', '2026-03-07 02:59:41'),
(13, 3, 'سيشس', 'closed', '2026-03-07 03:03:20', '2026-03-07 23:50:39'),
(14, 11, 'saad', 'closed', '2026-03-07 11:42:32', '2026-03-07 11:44:50'),
(15, 10, 'يشس', 'closed', '2026-03-07 14:54:41', '2026-03-07 14:55:03'),
(16, 10, 'سيشسي', 'closed', '2026-03-07 16:50:36', '2026-03-07 16:51:28'),
(17, 3, 'sdasd', 'closed', '2026-03-07 19:02:25', '2026-03-07 23:51:04'),
(18, 10, 'saad', 'closed', '2026-03-07 20:55:44', '2026-03-07 20:56:15'),
(20, 10, 'سيشس', 'closed', '2026-03-07 22:53:13', '2026-03-07 23:50:41'),
(21, 3, 'warda', 'closed', '2026-03-07 23:50:05', '2026-03-07 23:50:45'),
(26, 3, 'سيشس', 'closed', '2026-03-08 19:37:18', '2026-03-10 12:32:13'),
(27, 11, 'Uhh', 'closed', '2026-03-08 21:50:25', '2026-03-08 22:27:05'),
(28, 11, 'ثصقبث', 'closed', '2026-03-15 21:07:15', '2026-03-15 21:07:42'),
(29, 11, 'Bbb', 'closed', '2026-03-18 18:29:20', '2026-03-18 18:36:46'),
(30, 93, 'مكافئة التسجيل', 'closed', '2026-04-11 05:14:08', '2026-04-15 10:55:15'),
(31, 93, 'مكافئة التسجيل', 'closed', '2026-04-19 13:13:29', '2026-04-20 14:10:35'),
(32, 121, 'deposit', 'closed', '2026-07-27 13:18:08', '2026-07-28 07:26:08'),
(33, 121, 'deposit', 'closed', '2026-07-27 16:05:31', '2026-07-28 07:26:12'),
(34, 122, 'Deposit fail', 'closed', '2026-07-28 07:21:22', '2026-08-06 09:09:02'),
(35, 127, 'Recharge query', 'closed', '2026-08-07 08:27:43', '2026-10-09 11:20:55'),
(36, 127, 'Can you check the payment?', 'closed', '2026-08-10 08:34:52', '2026-10-09 11:21:00'),
(37, 127, '?No customer service reply? Why not?', 'closed', '2026-08-13 02:38:17', '2026-10-09 11:20:52'),
(38, 138, 'how long deposit', 'closed', '2026-09-21 14:58:34', '2026-10-09 11:20:47'),
(39, 139, 'Lost my account', 'pending', '2026-09-21 15:49:52', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `ticket_messages`
--

CREATE TABLE `ticket_messages` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `ticket_id` bigint(20) UNSIGNED NOT NULL,
  `sender` enum('user','admin') NOT NULL,
  `sender_id` bigint(20) UNSIGNED DEFAULT NULL,
  `message` text NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_type` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ticket_messages`
--

INSERT INTO `ticket_messages` (`id`, `ticket_id`, `sender`, `sender_id`, `message`, `image_url`, `attachment_path`, `attachment_name`, `attachment_type`, `created_at`) VALUES
(1, 1, 'user', 3, 'عاوز حساب جامهععي', NULL, NULL, NULL, NULL, '2026-03-05 20:49:07'),
(2, 1, 'user', 3, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-05 20:49:11'),
(3, 1, 'user', 3, 'كف الحال', NULL, NULL, NULL, NULL, '2026-03-05 20:49:43'),
(4, 1, 'admin', 1, 'معاكي', NULL, NULL, NULL, NULL, '2026-03-05 20:56:42'),
(5, 1, 'user', 3, 'طيب مطلوب', NULL, NULL, NULL, NULL, '2026-03-05 23:01:24'),
(6, 1, 'user', 3, 'طب\r\nايه', NULL, NULL, NULL, NULL, '2026-03-05 23:54:16'),
(7, 1, 'user', 3, 'ليه كده', NULL, NULL, NULL, NULL, '2026-03-05 23:54:19'),
(8, 2, 'user', 3, 'need support', NULL, NULL, NULL, NULL, '2026-03-06 11:09:43'),
(9, 2, 'user', 3, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 14:05:29'),
(10, 2, 'admin', 1, 'hi', NULL, NULL, NULL, NULL, '2026-03-06 21:15:33'),
(11, 3, 'user', 10, 'dasdasds', NULL, NULL, NULL, NULL, '2026-03-06 21:16:05'),
(12, 3, 'user', 10, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 21:34:08'),
(13, 3, 'admin', 1, 'اه', NULL, NULL, NULL, NULL, '2026-03-06 21:34:34'),
(14, 3, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 21:36:31'),
(15, 3, 'user', 10, 'اساعدك ازاي', NULL, NULL, NULL, NULL, '2026-03-06 21:36:37'),
(16, 3, 'user', 10, 'igh', NULL, NULL, NULL, NULL, '2026-03-06 21:55:45'),
(17, 3, 'admin', 1, 'طيب', NULL, NULL, NULL, NULL, '2026-03-06 21:56:00'),
(18, 3, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 22:09:42'),
(19, 3, 'user', 10, 'طيب', NULL, NULL, NULL, NULL, '2026-03-06 22:09:54'),
(20, 4, 'user', 1, 'dasdasdas', NULL, 'uploads/tickets/ticket_1772835644_7959.png', '2026.png', 'image/png', '2026-03-06 22:20:44'),
(21, 4, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 22:21:17'),
(22, 5, 'user', 10, 'fasdas', NULL, NULL, NULL, NULL, '2026-03-06 22:29:23'),
(23, 5, 'admin', 1, 'id', NULL, NULL, NULL, NULL, '2026-03-06 22:29:34'),
(24, 5, 'user', 10, 'سش', NULL, NULL, NULL, NULL, '2026-03-06 23:03:41'),
(25, 5, 'user', 10, 'سيشس', NULL, NULL, NULL, NULL, '2026-03-06 23:03:43'),
(26, 5, 'user', 10, 'ايه', NULL, NULL, NULL, NULL, '2026-03-06 23:03:57'),
(27, 5, 'admin', 1, 'ماشي', NULL, NULL, NULL, NULL, '2026-03-06 23:04:19'),
(28, 5, 'admin', 1, 'طيب', NULL, NULL, NULL, NULL, '2026-03-06 23:04:31'),
(29, 5, 'user', 10, 'ماشي', NULL, NULL, NULL, NULL, '2026-03-06 23:04:37'),
(30, 5, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 23:36:22'),
(31, 6, 'user', 1, 'شسيسش', NULL, 'uploads/tickets/ticket_1772840396_9342.jpg', '2718ded3-7ca8-4b55-a553-86ef9015862b.jpg', 'image/jpeg', '2026-03-06 23:39:56'),
(32, 6, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 23:40:09'),
(33, 5, 'user', 10, 'ايه', NULL, NULL, NULL, NULL, '2026-03-06 23:40:40'),
(34, 5, 'admin', 1, 'ايه', NULL, NULL, NULL, NULL, '2026-03-06 23:40:46'),
(35, 5, 'user', 10, 'فينك', NULL, NULL, NULL, NULL, '2026-03-06 23:40:51'),
(36, 5, 'admin', 1, 'كويس ما هي مشكلتك يا خي', NULL, NULL, NULL, NULL, '2026-03-06 23:41:00'),
(37, 7, 'user', 10, 'شسيشسيشس', NULL, NULL, NULL, NULL, '2026-03-06 23:55:16'),
(38, 7, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-06 23:55:26'),
(39, 8, 'user', 1, 'شسيشسيشس', NULL, NULL, NULL, NULL, '2026-03-07 00:18:09'),
(40, 7, 'user', 10, 'ابعت', NULL, NULL, NULL, NULL, '2026-03-07 00:31:21'),
(41, 9, 'user', 10, 'يشسي', 'uploads/tickets/ticket_new_user_10_1772843512_1886.png', NULL, NULL, NULL, '2026-03-07 00:31:52'),
(42, 9, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-07 00:32:01'),
(43, 9, 'user', 10, 'ما هي مشكلتك', NULL, NULL, NULL, NULL, '2026-03-07 00:32:10'),
(44, 9, 'admin', 1, 'سيشسيشس', 'uploads/tickets/ticket_9_admin_1_1772843571_2516.png', NULL, NULL, NULL, '2026-03-07 00:32:51'),
(45, 9, 'user', 10, 'يشسيشسيشسي', 'uploads/tickets/ticket_9_user_10_1772844678_6860.png', NULL, NULL, NULL, '2026-03-07 00:51:18'),
(46, 9, 'user', 10, 'يشسيشسي', NULL, NULL, NULL, NULL, '2026-03-07 00:51:23'),
(47, 9, 'user', 10, 'يشسيشسيشسيشسيشسي', 'uploads/tickets/ticket_9_user_10_1772844692_6927.png', NULL, NULL, NULL, '2026-03-07 00:51:32'),
(48, 9, 'user', 10, 'اهو الاثبات', 'uploads/tickets/ticket_9_user_10_1772844828_3814.jpg', NULL, NULL, NULL, '2026-03-07 00:53:48'),
(49, 9, 'admin', 1, 'اه', 'uploads/tickets/ticket_9_admin_1_1772844853_5737.jpg', NULL, NULL, NULL, '2026-03-07 00:54:13'),
(50, 10, 'user', 1, 'يشسيشسي', NULL, 'uploads/tickets/ticket_1772845180_9811.jpg', 'photo_2025-12-01_12-55-34.jpg', 'image/jpeg', '2026-03-07 00:59:40'),
(51, 11, 'user', 1, 'يشسي', NULL, 'uploads/tickets/ticket_1772848641_3071.jpg', 'IMG_7378 (2).JPG', 'image/jpeg', '2026-03-07 01:57:21'),
(52, 11, 'admin', 1, 'يشسيشسي', NULL, NULL, NULL, NULL, '2026-03-07 01:57:48'),
(53, 12, 'user', 3, 'سشيشس', 'uploads/tickets/ticket_user_3_1772852354_5659.png', NULL, NULL, NULL, '2026-03-07 02:59:14'),
(54, 12, 'user', 3, 'اهلا', 'uploads/tickets/ticket_user_3_1772852362_2758.png', NULL, NULL, NULL, '2026-03-07 02:59:22'),
(55, 12, 'admin', 1, 'اهلا', 'uploads/tickets/ticket_12_admin_1_1772852379_2662.png', NULL, NULL, NULL, '2026-03-07 02:59:39'),
(56, 13, 'user', 3, 'سيشسي', NULL, NULL, NULL, NULL, '2026-03-07 03:03:20'),
(57, 13, 'user', 3, 'شسيسشيش', NULL, NULL, NULL, NULL, '2026-03-07 03:03:24'),
(58, 13, 'admin', 1, 'سيشسي', NULL, NULL, NULL, NULL, '2026-03-07 03:03:35'),
(59, 13, 'admin', 1, 'يشس', NULL, NULL, NULL, NULL, '2026-03-07 03:03:44'),
(60, 13, 'user', 3, '', 'uploads/tickets/ticket_user_3_1772853380_3244.png', NULL, NULL, NULL, '2026-03-07 03:16:20'),
(61, 14, 'user', 11, 'يشسي', 'uploads/tickets/ticket_user_11_1772883752_9219.jpg', NULL, NULL, NULL, '2026-03-07 11:42:32'),
(62, 14, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-07 11:42:52'),
(63, 14, 'user', 11, 'طيب', 'uploads/tickets/ticket_user_11_1772883884_6689.png', NULL, NULL, NULL, '2026-03-07 11:44:44'),
(64, 15, 'user', 10, 'يشسي', 'uploads/tickets/ticket_new_user_10_1772895281_6367.jpg', NULL, NULL, NULL, '2026-03-07 14:54:41'),
(65, 16, 'user', 10, 'يشسيشسي', 'uploads/tickets/ticket_new_user_10_1772902236_9590.png', NULL, NULL, NULL, '2026-03-07 16:50:36'),
(66, 16, 'user', 10, 'اخلا', NULL, NULL, NULL, NULL, '2026-03-07 16:50:41'),
(67, 16, 'admin', 1, 'اهلا', NULL, NULL, NULL, NULL, '2026-03-07 16:51:10'),
(68, 16, 'user', 10, 'فينك', NULL, NULL, NULL, NULL, '2026-03-07 16:51:16'),
(69, 16, 'admin', 1, 'الحمدلله', NULL, NULL, NULL, NULL, '2026-03-07 16:51:22'),
(70, 17, 'user', 3, 'asdasdasdas', 'uploads/tickets/ticket_user_3_1772910145_7966.png', NULL, NULL, NULL, '2026-03-07 19:02:25'),
(71, 17, 'user', 3, 'عايز مثلا سيلر', NULL, NULL, NULL, NULL, '2026-03-07 19:02:34'),
(72, 18, 'user', 10, 'sdasdas', 'uploads/tickets/ticket_new_user_10_1772916944_1991.png', NULL, NULL, NULL, '2026-03-07 20:55:44'),
(73, 18, 'user', 10, 'hi', NULL, NULL, NULL, NULL, '2026-03-07 20:55:48'),
(74, 18, 'admin', 1, 'hi', NULL, NULL, NULL, NULL, '2026-03-07 20:55:59'),
(77, 20, 'user', 10, 'يشسيسش', NULL, NULL, NULL, NULL, '2026-03-07 22:53:13'),
(78, 21, 'user', 3, 'dasdas', 'uploads/tickets/ticket_user_3_1772927405_4887.jpg', NULL, NULL, NULL, '2026-03-07 23:50:05'),
(79, 17, 'user', 3, 'يشسي', NULL, NULL, NULL, NULL, '2026-03-07 23:50:58'),
(88, 26, 'user', 3, 'يشسيشس', 'uploads/tickets/ticket_user_3_1772998638_9238.png', NULL, NULL, NULL, '2026-03-08 19:37:18'),
(89, 26, 'admin', 1, 'شسيشسيشسيشس', NULL, NULL, NULL, NULL, '2026-03-08 19:37:25'),
(90, 27, 'user', 11, 'Bbb', NULL, NULL, NULL, NULL, '2026-03-08 21:50:25'),
(91, 28, 'user', 11, 'بيسبسيب', 'uploads/tickets/ticket_11_adac958c4bf45702962c.jpeg', NULL, NULL, NULL, '2026-03-15 21:07:15'),
(92, 28, 'admin', 1, 'بمشسيب4', NULL, NULL, NULL, NULL, '2026-03-15 21:07:31'),
(93, 28, 'admin', 1, 'بمشسيب4', NULL, NULL, NULL, NULL, '2026-03-15 21:07:33'),
(94, 29, 'user', 11, 'Gnvcc', NULL, NULL, NULL, NULL, '2026-03-18 18:29:20'),
(95, 30, 'user', 93, 'احتاج الي RDP مقابل التسجيل', NULL, NULL, NULL, NULL, '2026-04-11 05:14:08'),
(96, 30, 'admin', 1, 'صباح الخير', NULL, NULL, NULL, NULL, '2026-04-11 06:03:57'),
(97, 30, 'admin', 1, 'تم تسجيل طلبك بنجاح رجاء الاشتراك بالقناه سيتم رد عليك خلال تلات ايام', NULL, NULL, NULL, NULL, '2026-04-11 06:04:30'),
(98, 30, 'admin', 1, 'https://t.me/az100_market', NULL, NULL, NULL, NULL, '2026-04-11 06:04:38'),
(99, 30, 'user', 93, 'مشترك في القناة اعمل ايه تاني عشان اخد السيرفر ويندوز 10 او. 11 وبيدعم المحاكي لو ممكن', NULL, NULL, NULL, NULL, '2026-04-12 15:38:12'),
(100, 30, 'admin', 1, 'سحب بكره ساعه 12 هتلاقي نتيجه تلاقئ هنا', NULL, NULL, NULL, NULL, '2026-04-12 17:29:44'),
(101, 30, 'user', 93, 'ان شاء الله اكسب ❤️', NULL, NULL, NULL, NULL, '2026-04-13 17:38:40'),
(102, 30, 'admin', 1, 'صباح الخير تم مراجعه حاله طلب ولاكن للاسف لم تكن من ضمن فائزين حظا موفق عزيزي 🌧️', NULL, NULL, NULL, NULL, '2026-04-15 02:27:48'),
(103, 31, 'user', 93, 'عزيزي ايه انا عايز سيرفر مقابل التسجيل', NULL, NULL, NULL, NULL, '2026-04-19 13:13:29'),
(104, 31, 'admin', 1, 'مساء الخير يوم سعيد \r\nتم عمل مسابقه بالفعل وتم الانتهاء لنا شكرا لك', NULL, NULL, NULL, NULL, '2026-04-20 14:10:32'),
(105, 32, 'user', 121, 'not received', 'uploads/tickets/ticket_121_430cbd5a5c6a91108aba.png', NULL, NULL, NULL, '2026-07-27 13:18:08'),
(106, 33, 'user', 121, 'not received', 'uploads/tickets/ticket_121_08b31b84f09446d12832.png', NULL, NULL, NULL, '2026-07-27 16:05:31'),
(107, 34, 'user', 122, 'I deposit 10 USDT with BNB, tx is 0x925da4f6f62997b6a50a237e18a586fabb2c3d593bace92ecd71aa236c32f317.\r\n\r\nwhen submit the server response 500.', 'uploads/tickets/ticket_122_764c85bbff0fd35000b7.png', NULL, NULL, NULL, '2026-07-28 07:21:22'),
(108, 34, 'user', 122, 'This is the transacation', 'uploads/tickets/ticket_122_98575d161a2a44cb308e.png', NULL, NULL, NULL, '2026-07-28 07:21:41'),
(109, 34, 'admin', 1, 'hi pro', NULL, NULL, NULL, NULL, '2026-07-28 07:25:49'),
(110, 34, 'admin', 1, 'ok no problem', NULL, NULL, NULL, NULL, '2026-07-28 07:25:56'),
(111, 35, 'user', 127, 'Hello, I have submitted the certificate of the rechargeable transaction. I have not yet arrived. Could you check it for me', NULL, NULL, NULL, NULL, '2026-08-07 08:27:43'),
(112, 36, 'user', 127, 'Hello, I have submitted the certificate of the rechargeable transaction. I have not yet arrived. Could you check it for me', NULL, NULL, NULL, NULL, '2026-08-10 08:34:52'),
(113, 37, 'user', 127, 'No customer service reply? Why not?', NULL, NULL, NULL, NULL, '2026-08-13 02:38:17'),
(114, 38, 'user', 138, 'hey brother, i deposited 15 usd on my account. how long will it take to buy', NULL, NULL, NULL, NULL, '2026-09-21 14:58:34'),
(115, 39, 'user', 139, 'Yo sir, is this live? i had a prev account with similar username but i fucking forgot the login. Had to create a new one to send a ticket. Just want to check if i can recover my old username, i made funny characters on it. I defintely still remember my pw', NULL, NULL, NULL, NULL, '2026-09-21 15:49:52'),
(116, 39, 'user', 139, 'i did have a pending deposit of 15 usd there. hope u can help me. i want to buy an account', NULL, NULL, NULL, NULL, '2026-09-21 15:50:14'),
(117, 39, 'user', 139, 'hi bro, can it just be added to this account? I totally forgot the username i entered in there. I gave a deposit of 15 usd. or if u could help me recover my old usename pls', 'uploads/tickets/ticket_139_45dde5e1b0a0d0ba63f6.png', NULL, NULL, NULL, '2026-09-21 16:30:00'),
(118, 39, 'user', 139, 'can u message me on telegram? @watermelonshake\r\ni could not send you message there', NULL, NULL, NULL, NULL, '2026-09-22 00:04:32'),
(119, 39, 'user', 139, 'i cannot message u on telegram too sir. can u reply? @watermelonshake', 'uploads/tickets/ticket_139_cbd1bf235c5826351d6f.png', NULL, NULL, NULL, '2026-09-22 01:59:03'),
(120, 39, 'user', 139, 'i cannot message u on telegram too sir. can u reply? @watermelonshake', 'uploads/tickets/ticket_139_4dae6ee4d3507a466fc4.png', NULL, NULL, NULL, '2026-09-22 01:59:05'),
(121, 39, 'admin', 1, 'hi', NULL, NULL, NULL, NULL, '2026-10-09 11:20:35'),
(122, 39, 'admin', 1, 'send your mail', NULL, NULL, NULL, NULL, '2026-10-09 11:20:40');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `admin_id` bigint(20) UNSIGNED DEFAULT NULL,
  `kind` enum('credit','debit','note') NOT NULL,
  `currency` enum('mb','usdt') NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `admin_id`, `kind`, `currency`, `amount`, `reason`, `created_at`) VALUES
(1, 3, 1, 'credit', 'usdt', 100.00, 'Deposit approved', '2026-03-05 20:01:08'),
(2, 1, 1, 'credit', 'usdt', 1000000.00, '', '2026-03-05 20:14:00'),
(3, 1, 1, 'credit', 'mb', 100000.00, '', '2026-03-05 20:14:06'),
(4, 3, 1, 'credit', 'usdt', 2000.00, 'Deposit approved', '2026-03-05 23:00:57'),
(5, 3, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #2', '2026-03-06 01:00:39'),
(6, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #3', '2026-03-06 01:04:42'),
(7, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #4', '2026-03-06 01:57:55'),
(8, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #5', '2026-03-06 01:57:59'),
(9, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #6', '2026-03-06 01:58:02'),
(10, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #7', '2026-03-06 01:58:03'),
(11, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: azure student • Qty: 1 • Order #8', '2026-03-06 02:01:18'),
(12, 3, NULL, 'debit', 'mb', 0.00, 'Purchase: ازور استودنت فريش • Qty: 1 • Order #9', '2026-03-06 02:04:04'),
(13, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: ازور استودنت فريش • Qty: 2 • Order #10', '2026-03-06 02:06:42'),
(14, 3, 1, 'debit', 'usdt', 100.00, '', '2026-03-06 02:08:07'),
(17, 3, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #11', '2026-03-06 10:13:59'),
(18, 3, NULL, 'debit', 'usdt', 5.00, 'Purchase: ازور استودنت فريش • Qty: 1 • Order #12', '2026-03-06 10:39:38'),
(19, 3, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #13', '2026-03-06 10:39:56'),
(20, 3, NULL, 'debit', 'usdt', 15.00, 'Purchase: ازور استودنت فريش • Qty: 3 • Order #14', '2026-03-06 10:40:15'),
(21, 3, NULL, 'debit', 'usdt', 15.00, 'Purchase: ازور استودنت فريش • Qty: 3 • Order #15', '2026-03-06 10:40:29'),
(22, 3, NULL, 'debit', 'usdt', 15.00, 'Purchase: ازور استودنت فريش • Qty: 3 • Order #16', '2026-03-06 10:40:31'),
(23, 3, NULL, 'debit', 'usdt', 5.00, 'Purchase: cybermeme • Qty: 1 • Order #17', '2026-03-06 11:02:55'),
(24, 3, 1, 'credit', 'usdt', 2000.00, 'Top-up approved • TRC20 | DD6352ASD8AS974DA89SD74A9S8D798ASD', '2026-03-06 11:39:40'),
(27, 3, NULL, 'debit', 'usdt', 10.00, 'Withdraw request • DXASDAS52D41CASD87AS', '2026-03-06 12:05:24'),
(28, 3, NULL, 'debit', 'usdt', 2000.00, 'Withdraw request pending • TRC20 • FASD5AS41D78ASD4AS8D', '2026-03-06 12:22:40'),
(29, 3, 1, 'credit', 'usdt', 2000.00, 'Withdraw rejected • TRC20 • FASD5AS41D78ASD4AS8D', '2026-03-06 12:23:01'),
(30, 3, NULL, 'debit', 'usdt', 430.00, 'Withdraw request pending • TRC20 • DXASDAS52D41CASD87AS', '2026-03-06 12:28:55'),
(31, 3, 1, 'credit', 'usdt', 430.00, 'Withdraw rejected • TRC20 • DXASDAS52D41CASD87AS', '2026-03-06 12:29:03'),
(32, 3, NULL, 'debit', 'usdt', 2000.00, 'Withdraw request pending • TRC20 • DXASDAS52D41CASD87AS', '2026-03-06 12:42:59'),
(33, 3, 1, 'credit', 'usdt', 2000.00, 'Withdraw rejected refund • TRC20 • DXASDAS52D41CASD87AS', '2026-03-06 12:43:05'),
(34, 3, NULL, 'debit', 'usdt', 398.00, 'Withdraw request pending • TRC20 • sd5124asd7as8dasd', '2026-03-06 12:43:23'),
(35, 3, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • sd5124asd7as8dasd', '2026-03-06 12:43:28'),
(36, 1, 1, 'credit', 'usdt', 100000.00, '', '2026-03-06 12:45:14'),
(37, 3, NULL, 'debit', 'usdt', 12.00, 'Purchase: RDP 16 RAM 2 CORE • Qty: 1 • Order #18', '2026-03-06 12:49:41'),
(38, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #19', '2026-03-06 16:13:09'),
(39, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #20', '2026-03-06 17:58:54'),
(40, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #21', '2026-03-06 18:02:37'),
(41, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #22', '2026-03-06 19:08:46'),
(42, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #23', '2026-03-06 19:08:51'),
(43, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #24', '2026-03-06 19:08:56'),
(44, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #25', '2026-03-06 19:10:40'),
(45, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #26', '2026-03-06 19:13:02'),
(46, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #27', '2026-03-06 19:13:07'),
(47, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #28', '2026-03-06 19:13:13'),
(48, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #29', '2026-03-06 19:17:38'),
(49, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #30', '2026-03-06 19:17:45'),
(50, 3, 1, 'credit', 'usdt', 100.00, 'Deposit approved • Binance id | 1217447525', '2026-03-06 19:18:45'),
(51, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #31', '2026-03-06 19:22:06'),
(52, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #32', '2026-03-06 19:36:13'),
(53, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #33', '2026-03-06 19:36:15'),
(54, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #34', '2026-03-06 19:36:24'),
(55, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #35', '2026-03-06 19:36:28'),
(56, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #36', '2026-03-06 19:45:21'),
(57, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #37', '2026-03-06 19:45:24'),
(58, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #38', '2026-03-06 19:47:24'),
(59, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #39', '2026-03-06 19:47:33'),
(60, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #40', '2026-03-06 19:51:29'),
(61, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #41', '2026-03-06 19:59:44'),
(62, 10, NULL, 'credit', 'usdt', 3.50, 'Sale income: azure student 100$ • Qty: 1 • Order #41', '2026-03-06 19:59:44'),
(63, 3, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student 100$ • Qty: 1 • Order #42', '2026-03-06 20:01:59'),
(64, 10, NULL, 'credit', 'usdt', 3.50, 'Sale income: azure student 100$ • Qty: 1 • Order #42', '2026-03-06 20:01:59'),
(65, 3, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #43', '2026-03-06 20:02:54'),
(66, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: azure student 100$ • Qty: 1 • Order #44', '2026-03-06 20:03:26'),
(67, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #44', '2026-03-06 20:03:26'),
(68, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: azure student 100$ • Qty: 1 • Order #45', '2026-03-06 20:13:17'),
(69, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #45', '2026-03-06 20:13:17'),
(70, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: azure student 100$ • Qty: 1 • Order #46', '2026-03-06 20:13:24'),
(71, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #46', '2026-03-06 20:13:24'),
(72, 3, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #47', '2026-03-06 20:14:37'),
(73, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: azure student 100$ • Qty: 1 • Order #48', '2026-03-06 21:55:08'),
(74, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #48', '2026-03-06 21:55:08'),
(75, 10, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student • Qty: 1 • Order #49', '2026-03-06 22:33:27'),
(76, 10, 1, 'credit', 'usdt', 1000.00, 'دعم', '2026-03-06 23:33:41'),
(77, 1, 1, 'credit', 'usdt', 10000000000.00, 'Admin balance added', '2026-03-07 01:40:59'),
(78, 1, 1, 'debit', 'usdt', 1000000.00, 'Admin balance deducted', '2026-03-07 01:41:21'),
(79, 1, 1, 'debit', 'usdt', 100000000.00, 'Admin balance deducted', '2026-03-07 01:41:30'),
(80, 1, 1, 'debit', 'usdt', 999999999.00, 'Admin balance deducted', '2026-03-07 01:41:40'),
(81, 1, 1, 'debit', 'usdt', 800000000.00, 'Admin balance deducted', '2026-03-07 01:41:55'),
(82, 1, 1, 'debit', 'usdt', 8100000000.00, 'Admin balance deducted', '2026-03-07 01:42:05'),
(83, 1, 1, 'credit', 'usdt', 104507.50, 'Admin balance added', '2026-03-07 01:42:14'),
(84, 1, 1, 'debit', 'usdt', 100000.00, 'Admin balance deducted', '2026-03-07 01:42:32'),
(85, 1, 1, 'debit', 'usdt', 100000.00, 'Admin balance deducted', '2026-03-07 01:42:38'),
(86, 3, NULL, 'debit', 'usdt', 10.00, 'Purchase: azure student 100$ • Qty: 1 • Order #50', '2026-03-07 02:14:48'),
(87, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #50', '2026-03-07 02:14:48'),
(88, 3, 1, 'credit', 'usdt', 10.00, 'Deposit approved • Binance ID | 1217447525', '2026-03-07 02:40:29'),
(89, 1, 1, 'debit', 'usdt', 1000.00, 'Admin balance deducted', '2026-03-07 03:21:59'),
(90, 1, 1, 'debit', 'usdt', 1000.00, 'Admin balance deducted', '2026-03-07 03:22:06'),
(91, 1, 1, 'debit', 'usdt', 1000.00, 'Admin balance deducted', '2026-03-07 03:22:17'),
(92, 1, 1, 'debit', 'usdt', 1000.00, 'Admin balance deducted', '2026-03-07 03:22:31'),
(93, 1, 1, 'debit', 'usdt', 500.00, 'Admin balance deducted', '2026-03-07 03:22:41'),
(94, 1, 1, 'credit', 'usdt', 5000.00, 'Admin balance added', '2026-03-07 03:22:52'),
(95, 1, 1, 'debit', 'usdt', 5000.00, 'Admin balance deducted', '2026-03-07 03:22:56'),
(96, 3, NULL, 'debit', 'usdt', 400.00, 'Withdraw request pending • usdt • Binance ID • 1537878 • رجاء التاكد من عنوان النسخ', '2026-03-07 03:37:51'),
(97, 3, 1, 'note', 'usdt', 0.00, 'Withdraw approved • Binance ID • 1537878 • ماشي يا حبيبي', '2026-03-07 03:38:57'),
(98, 3, 1, 'credit', 'usdt', 45234.00, 'Deposit approved • Binance ID | 1217447525 • TXID: 27485678SDAKSDH', '2026-03-07 03:39:46'),
(99, 3, 1, 'debit', 'usdt', 40029.00, 'Admin balance deducted', '2026-03-07 03:40:07'),
(100, 3, 1, 'debit', 'usdt', 8000.00, 'Admin balance deducted', '2026-03-07 03:40:15'),
(101, 11, 1, 'credit', 'usdt', 100.00, 'Admin balance added', '2026-03-07 11:41:26'),
(102, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #51', '2026-03-07 11:41:44'),
(103, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #52', '2026-03-07 11:42:04'),
(104, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #53', '2026-03-07 11:56:14'),
(111, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #58', '2026-03-07 12:51:30'),
(118, 10, NULL, 'credit', 'usdt', 10.00, 'Sale income: azure student 100$ • Qty: 1 • Order #62', '2026-03-07 13:23:17'),
(119, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #63', '2026-03-07 14:03:57'),
(120, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #64', '2026-03-07 14:06:34'),
(121, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #64', '2026-03-07 14:06:34'),
(122, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #65', '2026-03-07 14:13:02'),
(123, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #65', '2026-03-07 14:13:02'),
(124, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #66', '2026-03-07 14:15:37'),
(125, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #66', '2026-03-07 14:15:37'),
(126, 11, NULL, 'debit', 'usdt', 1.00, 'Purchase: حساب جامعي كلير • Qty: 2 • Order #67', '2026-03-07 14:15:46'),
(127, 10, NULL, 'credit', 'usdt', 1.00, 'Sale income: حساب جامعي كلير • Qty: 2 • Order #67', '2026-03-07 14:15:46'),
(128, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #68', '2026-03-07 14:29:39'),
(129, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #68', '2026-03-07 14:29:39'),
(130, 11, NULL, 'debit', 'usdt', 2.00, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 10 • Order #69', '2026-03-07 14:35:24'),
(131, 1, NULL, 'credit', 'usdt', 2.00, 'Sale income: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 10 • Order #69', '2026-03-07 14:35:24'),
(132, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #70', '2026-03-07 15:40:14'),
(133, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #70', '2026-03-07 15:40:14'),
(134, 11, NULL, 'debit', 'usdt', 2.50, 'Purchase: حساب جامعي كلير • Qty: 5 • Order #71', '2026-03-07 15:43:00'),
(135, 10, NULL, 'credit', 'usdt', 2.50, 'Sale income: حساب جامعي كلير • Qty: 5 • Order #71', '2026-03-07 15:43:00'),
(136, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #72', '2026-03-07 15:44:12'),
(137, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #72', '2026-03-07 15:44:12'),
(139, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #73', '2026-03-07 15:58:56'),
(140, 3, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #74', '2026-03-07 16:43:09'),
(141, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #74', '2026-03-07 16:43:09'),
(142, 10, NULL, 'debit', 'usdt', 5.00, 'Seller withdraw request pending', '2026-03-07 16:51:48'),
(143, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • AE48nZtWMo9RtTmCPUBsFtiXbNfDcRx2YVyrPbCN9nxi', '2026-03-07 16:51:59'),
(144, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:05'),
(145, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:09'),
(146, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:14'),
(147, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:17'),
(148, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:24'),
(149, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:32'),
(150, 10, NULL, 'debit', 'usdt', 1.00, 'Seller withdraw request pending', '2026-03-07 17:44:45'),
(151, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • Binance id • 12378562785', '2026-03-07 18:17:18'),
(152, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • sd5124asd7as8dasd', '2026-03-07 18:17:20'),
(153, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • sd5124asd7as8dasd', '2026-03-07 18:17:21'),
(154, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • sd5124asd7as8dasd • sd5124asd7as8dasd • خلي بالك من نفس', '2026-03-07 18:17:25'),
(155, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • sd5124asd7as8dasd • 1', '2026-03-07 18:17:26'),
(156, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • sd5124asd7as8dasd', '2026-03-07 18:17:27'),
(157, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • TRC20 • sd5124asd7as8dasd', '2026-03-07 18:17:30'),
(158, 3, NULL, 'debit', 'usdt', 0.20, 'Purchase: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #75', '2026-03-07 18:55:50'),
(159, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #75', '2026-03-07 18:55:50'),
(160, 3, NULL, 'debit', 'usdt', 9.00, 'Purchase: azure student renew • Qty: 2 • Order #76', '2026-03-07 18:57:53'),
(161, 10, NULL, 'credit', 'usdt', 9.00, 'Sale income: azure student renew • Qty: 2 • Order #76', '2026-03-07 18:57:53'),
(162, 3, NULL, 'debit', 'usdt', 50.00, 'Withdraw request pending • usdt • Binance ID • 1563278635278 • محتاجها عاجل', '2026-03-07 19:04:22'),
(163, 3, 1, 'note', 'usdt', 0.00, 'Withdraw approved • Binance ID • 1563278635278', '2026-03-07 19:04:42'),
(164, 15, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-07 19:07:47'),
(165, 15, 1, 'credit', 'usdt', 100.00, 'Admin balance added', '2026-03-07 19:08:33'),
(166, 15, NULL, 'debit', 'usdt', 3.00, 'Purchase: azure student renew • Qty: 1 • Order #77', '2026-03-07 19:08:45'),
(167, 3, NULL, 'credit', 'usdt', 3.00, 'Sale income: azure student renew • Qty: 1 • Order #77', '2026-03-07 19:08:45'),
(172, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: Clean University Email Account (Contains Personal Information), Office 365 Account • Qty: 1 • Order #78', '2026-03-07 20:02:32'),
(173, 3, 1, 'credit', 'usdt', 500.00, 'Deposit approved • Binance ID | 1217447525', '2026-03-07 20:34:33'),
(175, 3, NULL, 'credit', 'usdt', 3.00, 'Sale income: azure student renew • Qty: 1 • Order #79', '2026-03-07 21:00:17'),
(177, 3, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #80', '2026-03-07 23:49:41'),
(178, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #80', '2026-03-07 23:49:41'),
(179, 1, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #81', '2026-03-08 01:42:37'),
(180, 10, NULL, 'credit', 'usdt', 0.50, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #81', '2026-03-08 01:42:37'),
(181, 1, NULL, 'debit', 'usdt', 1.50, 'Purchase: حساب جامعي كلير • Qty: 3 • Order #82', '2026-03-08 01:42:54'),
(182, 10, NULL, 'credit', 'usdt', 1.50, 'Sale income: حساب جامعي كلير • Qty: 3 • Order #82', '2026-03-08 01:42:54'),
(183, 10, NULL, 'debit', 'usdt', 3.00, 'Purchase: azure student renew • Qty: 1 • Order #83', '2026-03-08 01:44:13'),
(184, 3, NULL, 'credit', 'usdt', 3.00, 'Sale income: azure student renew • Qty: 1 • Order #83', '2026-03-08 01:44:13'),
(186, 10, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student renew • Qty: 1 • Order #84 • Commission 20%', '2026-03-08 02:25:23'),
(187, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.9 USDT • Order #84', '2026-03-08 02:25:23'),
(189, 10, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student renew • Qty: 1 • Order #85 • Commission 20%', '2026-03-08 02:27:03'),
(190, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.9 USDT • Order #85', '2026-03-08 02:27:03'),
(192, 10, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student renew • Qty: 1 • Order #86 • Commission 20%', '2026-03-08 02:27:47'),
(193, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.9 USDT • Order #86', '2026-03-08 02:27:47'),
(194, 10, NULL, 'debit', 'usdt', 4.50, 'Purchase: azure student renew • Qty: 1 • Order #87', '2026-03-08 02:32:50'),
(196, 10, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student renew • Qty: 1 • Order #88 • Commission 20%', '2026-03-08 02:33:03'),
(197, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.9 USDT • Order #88', '2026-03-08 02:33:03'),
(199, 3, NULL, 'credit', 'usdt', 2.40, 'Sale income: azure student renew • Qty: 1 • Order #89 • Commission 20%', '2026-03-08 02:34:25'),
(200, 3, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.6 USDT • Order #89', '2026-03-08 02:34:25'),
(202, 10, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student renew • Qty: 1 • Order #90 • Commission 20%', '2026-03-08 02:53:16'),
(203, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.9 USDT • Order #90', '2026-03-08 02:53:16'),
(205, 3, NULL, 'debit', 'usdt', 1.00, 'Purchase: حساب جامعي كلير • Qty: 2 • Order #91', '2026-03-08 14:28:14'),
(206, 10, NULL, 'credit', 'usdt', 0.80, 'Sale income: حساب جامعي كلير • Qty: 2 • Order #91 • Commission 20%', '2026-03-08 14:28:14'),
(207, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.2 USDT • Order #91', '2026-03-08 14:28:14'),
(208, 21, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-08 15:25:02'),
(209, 11, NULL, 'debit', 'usdt', 0.50, 'Purchase: حساب جامعي كلير • Qty: 1 • Order #92', '2026-03-08 15:32:51'),
(210, 10, NULL, 'credit', 'usdt', 0.40, 'Sale income: حساب جامعي كلير • Qty: 1 • Order #92 • Commission 20%', '2026-03-08 15:32:51'),
(211, 10, NULL, 'note', 'usdt', 0.00, 'Commission deducted 20% = 0.1 USDT • Order #92', '2026-03-08 15:32:51'),
(213, 3, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525', '2026-03-08 16:58:51'),
(214, 10, NULL, 'debit', 'usdt', 10.00, 'Seller withdraw request pending', '2026-03-08 17:00:27'),
(215, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • BNB Smart Chain (BEP20) • 0xcd7f8687599b6dad21f970837d72f116e4f61bef', '2026-03-08 17:00:36'),
(216, 10, NULL, 'debit', 'usdt', 50.00, 'Seller withdraw request pending', '2026-03-08 17:00:56'),
(217, 10, 1, 'note', 'usdt', 0.00, 'Withdraw approved • BNB Smart Chain (BEP20) • 0xcd7f8687599b6dad21f970837d72f116e4f61bef', '2026-03-08 17:00:59'),
(218, 10, 1, 'credit', 'usdt', 2000.00, 'Deposit approved • BNB Smart Chain (BEP20)', '2026-03-08 17:01:25'),
(219, 3, NULL, 'debit', 'usdt', 100.00, 'Withdraw request pending • usdt • Binance ID • 23874563243', '2026-03-08 18:17:33'),
(220, 3, 1, 'credit', 'usdt', 100.00, 'Deposit approved • Binance ID | 1217447525', '2026-03-08 18:18:20'),
(221, 3, 1, 'credit', 'usdt', 100.00, 'Withdraw rejected refund • Binance ID • 23874563243', '2026-03-08 18:18:26'),
(224, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #93', '2026-03-08 18:26:01'),
(226, 23, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-08 19:51:08'),
(227, 24, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-08 21:22:41'),
(228, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #94', '2026-03-08 22:03:04'),
(229, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #94', '2026-03-08 22:03:04'),
(230, 3, 1, 'note', 'usdt', 0.00, 'Deposit rejected • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', '2026-03-08 23:04:29'),
(231, 3, 1, 'note', 'usdt', 0.00, 'Deposit rejected • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', '2026-03-08 23:04:30'),
(232, 11, NULL, 'debit', 'usdt', 3.50, 'Purchase: azure student with api 100$ • Qty: 1 • Order #95', '2026-03-09 02:59:10'),
(233, 10, NULL, 'credit', 'usdt', 3.50, 'Sale income: azure student with api 100$ • Qty: 1 • Order #95', '2026-03-09 02:59:10'),
(234, 25, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-09 16:43:44'),
(235, 26, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-09 21:14:27'),
(236, 11, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #96', '2026-03-09 21:20:41'),
(237, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #96', '2026-03-09 21:20:41'),
(238, 28, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-10 03:56:04'),
(239, 29, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-10 06:46:21'),
(240, 29, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #97', '2026-03-10 06:46:43'),
(241, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #97', '2026-03-10 06:46:43'),
(242, 30, 1, 'credit', 'usdt', 1.00, 'احلي مسا عليك يا جابر', '2026-03-10 12:32:54'),
(243, 31, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-10 14:05:18'),
(244, 31, NULL, 'debit', 'usdt', 1.00, 'Withdraw request pending • usdt • Binance ID • 584552658', '2026-03-10 14:06:34'),
(245, 31, 1, 'credit', 'usdt', 1.00, 'Withdraw rejected refund • Binance ID • 584552658 • We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-10 14:23:31'),
(246, 1, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #98', '2026-03-10 15:48:31'),
(247, 32, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-12 01:04:53'),
(248, 32, NULL, 'debit', 'usdt', 1.00, 'Withdraw request pending • usdt • USDT  BNB Smart Chain (BEP20) • 0xcd7f8687599b6dad21f970837d72f116e4f61bef', '2026-03-12 01:06:51'),
(249, 32, 1, 'credit', 'usdt', 1.00, 'Withdraw rejected refund • USDT  BNB Smart Chain (BEP20) • 0xcd7f8687599b6dad21f970837d72f116e4f61bef', '2026-03-12 06:21:12'),
(250, 33, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-12 09:52:57'),
(251, 33, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #99', '2026-03-12 09:53:28'),
(252, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #99', '2026-03-12 09:53:28'),
(253, 33, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #100', '2026-03-12 09:55:49'),
(254, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #100', '2026-03-12 09:55:49'),
(255, 33, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #101', '2026-03-12 09:57:02'),
(256, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #101', '2026-03-12 09:57:02'),
(257, 33, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #102', '2026-03-12 10:00:27'),
(258, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #102', '2026-03-12 10:00:27'),
(259, 33, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #103', '2026-03-12 10:00:43'),
(260, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #103', '2026-03-12 10:00:43'),
(261, 11, NULL, 'debit', 'usdt', 10.00, 'Withdraw request pending • usdt • USDT  BNB Smart Chain (BEP20) • 27485edawsdasdsa', '2026-03-12 14:19:52'),
(262, 11, 1, 'credit', 'usdt', 10.00, 'Withdraw rejected refund • USDT  BNB Smart Chain (BEP20) • 27485edawsdasdsa • We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-12 14:20:07'),
(263, 11, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525 • TXID: 27485678SDAKSDH • We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-12 14:20:10'),
(264, 34, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-12 14:52:34'),
(265, 34, NULL, 'debit', 'usdt', 0.32, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #104', '2026-03-12 14:52:40'),
(266, 1, NULL, 'credit', 'usdt', 0.32, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #104', '2026-03-12 14:52:40'),
(267, 34, NULL, 'debit', 'usdt', 0.20, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #105', '2026-03-12 14:55:05'),
(268, 1, NULL, 'credit', 'usdt', 0.20, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #105', '2026-03-12 14:55:05'),
(269, 34, NULL, 'debit', 'usdt', 0.40, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #106', '2026-03-12 14:55:31'),
(270, 1, NULL, 'credit', 'usdt', 0.40, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #106', '2026-03-12 14:55:31'),
(271, 3, NULL, 'debit', 'usdt', 0.32, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #107', '2026-03-12 15:11:57'),
(272, 1, NULL, 'credit', 'usdt', 0.32, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #107', '2026-03-12 15:11:57'),
(273, 11, NULL, 'debit', 'usdt', 3.60, 'Purchase: azure student with api 100$ (hotmail) • Qty: 1 • Order #108', '2026-03-14 05:04:30'),
(274, 1, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student with api 100$ (hotmail) • Qty: 1 • Order #108', '2026-03-14 05:04:30'),
(275, 11, NULL, 'debit', 'usdt', 4.50, 'Purchase: Azure student 100$ Edu mail • Qty: 1 • Order #109', '2026-03-14 05:05:21'),
(276, 1, NULL, 'credit', 'usdt', 4.50, 'Sale income: Azure student 100$ Edu mail • Qty: 1 • Order #109', '2026-03-14 05:05:21'),
(277, 35, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-14 07:32:33'),
(278, 35, NULL, 'debit', 'usdt', 0.41, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #110', '2026-03-14 07:35:06'),
(279, 1, NULL, 'credit', 'usdt', 0.41, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #110', '2026-03-14 07:35:06'),
(280, 35, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #111', '2026-03-14 12:55:18'),
(281, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #111', '2026-03-14 12:55:18'),
(282, 3, NULL, 'debit', 'usdt', 5.50, 'Purchase: RDP 8 RAM 2 CORE   FR • Qty: 1 • Order #112', '2026-03-14 13:25:30'),
(283, 1, NULL, 'credit', 'usdt', 5.50, 'Sale income: RDP 8 RAM 2 CORE   FR • Qty: 1 • Order #112', '2026-03-14 13:25:30'),
(284, 1, NULL, 'debit', 'usdt', 9.50, 'Purchase: RDP 16 RAM 4 CORE italy • Qty: 1 • Order #113', '2026-03-14 13:37:52'),
(285, 36, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 08:56:56'),
(286, 36, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #114', '2026-03-15 08:57:05'),
(287, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #114', '2026-03-15 08:57:05'),
(288, 36, NULL, 'debit', 'usdt', 0.41, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #115', '2026-03-15 09:02:42'),
(289, 1, NULL, 'credit', 'usdt', 0.41, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #115', '2026-03-15 09:02:42'),
(290, 37, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:03:11'),
(291, 37, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #116', '2026-03-15 09:03:28'),
(292, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #116', '2026-03-15 09:03:28'),
(293, 38, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:04:49'),
(294, 38, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #117', '2026-03-15 09:04:56'),
(295, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #117', '2026-03-15 09:04:56'),
(296, 38, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #118', '2026-03-15 09:05:06'),
(297, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #118', '2026-03-15 09:05:06'),
(298, 39, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:05:20'),
(299, 39, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #119', '2026-03-15 09:05:26'),
(300, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #119', '2026-03-15 09:05:26'),
(301, 39, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #120', '2026-03-15 09:05:35'),
(302, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #120', '2026-03-15 09:05:35'),
(303, 40, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:05:48'),
(304, 40, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #121', '2026-03-15 09:05:54'),
(305, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #121', '2026-03-15 09:05:54'),
(306, 40, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #122', '2026-03-15 09:06:03'),
(307, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #122', '2026-03-15 09:06:03'),
(308, 41, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:06:15'),
(309, 41, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #123', '2026-03-15 09:06:21'),
(310, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #123', '2026-03-15 09:06:21'),
(311, 41, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #124', '2026-03-15 09:06:30'),
(312, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #124', '2026-03-15 09:06:30'),
(313, 42, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:06:44'),
(314, 42, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #125', '2026-03-15 09:06:49'),
(315, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #125', '2026-03-15 09:06:49'),
(316, 42, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #126', '2026-03-15 09:06:58'),
(317, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #126', '2026-03-15 09:06:58'),
(318, 45, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:07:29'),
(319, 45, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #127', '2026-03-15 09:07:35'),
(320, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #127', '2026-03-15 09:07:35'),
(321, 45, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #128', '2026-03-15 09:07:43'),
(322, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #128', '2026-03-15 09:07:43'),
(323, 46, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:08:05'),
(324, 46, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #129', '2026-03-15 09:08:11'),
(325, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #129', '2026-03-15 09:08:11'),
(326, 46, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #130', '2026-03-15 09:08:29'),
(327, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #130', '2026-03-15 09:08:29'),
(328, 47, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:08:47'),
(329, 47, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #131', '2026-03-15 09:08:54'),
(330, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #131', '2026-03-15 09:08:54'),
(331, 47, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #132', '2026-03-15 09:09:05'),
(332, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #132', '2026-03-15 09:09:05'),
(333, 48, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:09:25'),
(334, 48, NULL, 'debit', 'usdt', 0.48, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #133', '2026-03-15 09:09:31'),
(335, 1, NULL, 'credit', 'usdt', 0.48, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #133', '2026-03-15 09:09:31'),
(336, 48, NULL, 'debit', 'usdt', 0.41, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #134', '2026-03-15 09:09:43'),
(337, 1, NULL, 'credit', 'usdt', 0.41, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #134', '2026-03-15 09:09:43'),
(338, 49, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:10:01'),
(339, 49, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #135', '2026-03-15 09:10:14'),
(340, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #135', '2026-03-15 09:10:14'),
(341, 50, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:10:38'),
(342, 50, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #136', '2026-03-15 09:11:55'),
(343, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #136', '2026-03-15 09:11:55'),
(344, 51, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:13:44'),
(345, 51, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #137', '2026-03-15 09:13:52'),
(346, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #137', '2026-03-15 09:13:52'),
(347, 52, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:15:57'),
(348, 52, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #138', '2026-03-15 09:16:03'),
(349, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #138', '2026-03-15 09:16:03'),
(350, 53, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:20:04'),
(351, 53, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #139', '2026-03-15 09:20:11'),
(352, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #139', '2026-03-15 09:20:11'),
(353, 54, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:20:51'),
(354, 54, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #140', '2026-03-15 09:20:58'),
(355, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #140', '2026-03-15 09:20:58'),
(356, 55, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:21:14'),
(357, 55, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #141', '2026-03-15 09:21:19'),
(358, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #141', '2026-03-15 09:21:19'),
(359, 56, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:21:34'),
(360, 56, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #142', '2026-03-15 09:21:38'),
(361, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #142', '2026-03-15 09:21:38'),
(362, 57, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:21:52'),
(363, 57, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #143', '2026-03-15 09:21:57'),
(364, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #143', '2026-03-15 09:21:57'),
(365, 58, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:22:10'),
(366, 58, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #144', '2026-03-15 09:22:27'),
(367, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #144', '2026-03-15 09:22:27'),
(368, 59, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:22:47'),
(369, 59, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #145', '2026-03-15 09:22:53'),
(370, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #145', '2026-03-15 09:22:53'),
(371, 60, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:23:06'),
(372, 60, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #146', '2026-03-15 09:23:11'),
(373, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #146', '2026-03-15 09:23:11'),
(374, 61, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:23:23'),
(375, 61, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #147', '2026-03-15 09:23:30'),
(376, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #147', '2026-03-15 09:23:30'),
(377, 62, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:24:59'),
(378, 62, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #148', '2026-03-15 09:25:06'),
(379, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #148', '2026-03-15 09:25:06'),
(380, 63, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:25:28'),
(381, 63, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #149', '2026-03-15 09:25:37'),
(382, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #149', '2026-03-15 09:25:37'),
(383, 64, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:26:00'),
(384, 64, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #150', '2026-03-15 09:26:07'),
(385, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #150', '2026-03-15 09:26:07'),
(386, 65, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:26:29'),
(387, 65, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #151', '2026-03-15 09:26:36'),
(388, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #151', '2026-03-15 09:26:36'),
(389, 68, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:30:47'),
(390, 68, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #152', '2026-03-15 09:30:53'),
(391, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #152', '2026-03-15 09:30:53'),
(392, 69, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:51:21'),
(393, 69, NULL, 'debit', 'usdt', 0.82, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #153', '2026-03-15 09:51:30'),
(394, 1, NULL, 'credit', 'usdt', 0.82, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #153', '2026-03-15 09:51:30'),
(395, 70, NULL, 'credit', 'usdt', 1.00, 'Welcome bonus', '2026-03-15 09:55:48'),
(396, 70, NULL, 'debit', 'usdt', 0.96, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #154', '2026-03-15 09:55:52'),
(397, 1, NULL, 'credit', 'usdt', 0.96, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #154', '2026-03-15 09:55:52'),
(398, 3, NULL, 'debit', 'usdt', 5.50, 'Purchase: RDP 8 RAM 2 CORE   FR • Qty: 1 • Order #155', '2026-03-15 19:42:00'),
(399, 1, NULL, 'credit', 'usdt', 5.50, 'Sale income: RDP 8 RAM 2 CORE   FR • Qty: 1 • Order #155', '2026-03-15 19:42:00'),
(400, 3, NULL, 'debit', 'usdt', 9.50, 'Purchase: RDP 16 RAM 4 CORE italy • Qty: 1 • Order #156', '2026-03-15 19:42:05'),
(401, 1, NULL, 'credit', 'usdt', 9.50, 'Sale income: RDP 16 RAM 4 CORE italy • Qty: 1 • Order #156', '2026-03-15 19:42:05'),
(402, 3, NULL, 'debit', 'usdt', 19.00, 'Purchase: RDP 16 RAM 4 CORE italy • Qty: 2 • Order #157', '2026-03-15 19:42:13'),
(403, 1, NULL, 'credit', 'usdt', 19.00, 'Sale income: RDP 16 RAM 4 CORE italy • Qty: 2 • Order #157', '2026-03-15 19:42:13'),
(404, 1, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #158', '2026-03-15 21:03:54');
INSERT INTO `transactions` (`id`, `user_id`, `admin_id`, `kind`, `currency`, `amount`, `reason`, `created_at`) VALUES
(405, 11, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525 • TXID: 27485678SDAKSDH • تم رفض الايداع الخاص بك لان صورة فيك', '2026-03-15 21:06:48'),
(406, 71, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-17 01:26:37'),
(407, 71, 1, 'credit', 'usdt', 4.50, 'Deposit approved • Binance ID | 1217447525', '2026-03-17 05:42:43'),
(408, 71, NULL, 'debit', 'usdt', 4.50, 'Purchase: Azure student 100$ Edu mail • Qty: 1 • Order #159', '2026-03-17 05:43:11'),
(409, 1, NULL, 'credit', 'usdt', 4.50, 'Sale income: Azure student 100$ Edu mail • Qty: 1 • Order #159', '2026-03-17 05:43:11'),
(410, 71, 1, 'credit', 'usdt', 1.00, 'Gift', '2026-03-17 05:46:06'),
(411, 71, 1, 'credit', 'usdt', 4.50, 'Admin balance added', '2026-03-17 05:46:25'),
(412, 71, 1, 'debit', 'usdt', 4.50, 'Admin balance deducted', '2026-03-17 05:46:56'),
(413, 71, NULL, 'debit', 'usdt', 0.27, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #160', '2026-03-17 06:59:40'),
(414, 1, NULL, 'credit', 'usdt', 0.27, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #160', '2026-03-17 06:59:40'),
(415, 71, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #161', '2026-03-17 06:59:56'),
(416, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #161', '2026-03-17 06:59:56'),
(417, 72, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-17 08:44:25'),
(418, 72, 1, 'credit', 'usdt', 5.00, 'Deposit approved • Binance ID | 1217447525 • TXID: 420696176341557248', '2026-03-17 09:14:27'),
(419, 72, NULL, 'debit', 'usdt', 4.50, 'Purchase: Azure student 100$ Edu mail • Qty: 1 • Order #162', '2026-03-17 09:15:18'),
(420, 1, NULL, 'credit', 'usdt', 4.50, 'Sale income: Azure student 100$ Edu mail • Qty: 1 • Order #162', '2026-03-17 09:15:18'),
(421, 72, 1, 'credit', 'usdt', 1.00, 'Gift', '2026-03-17 09:15:55'),
(422, 72, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #163', '2026-03-18 05:53:50'),
(423, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #163', '2026-03-18 05:53:50'),
(424, 71, 1, 'credit', 'usdt', 3.00, 'Deposit approved • Binance ID | 1217447525', '2026-03-18 06:26:02'),
(425, 71, NULL, 'debit', 'usdt', 3.60, 'Purchase: azure student with api 100$ (hotmail) • Qty: 1 • Order #164', '2026-03-18 06:27:44'),
(426, 1, NULL, 'credit', 'usdt', 3.60, 'Sale income: azure student with api 100$ (hotmail) • Qty: 1 • Order #164', '2026-03-18 06:27:44'),
(427, 11, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #165', '2026-03-18 18:28:00'),
(428, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #165', '2026-03-18 18:28:00'),
(429, 73, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-21 20:19:44'),
(430, 74, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-22 10:55:24'),
(431, 74, 1, 'note', 'usdt', 0.00, 'Deposit rejected • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3 • رجاء تاكد من عملية الايداع بشكل صحيح ورفق اثبات الدفع الخاص بك Please ensure that the deposit process is correct and attach your proof of payment.', '2026-03-22 12:33:51'),
(432, 74, 1, 'credit', 'usdt', 1.00, 'gift reg', '2026-03-22 12:34:30'),
(433, 75, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-23 13:23:48'),
(434, 76, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-26 18:59:45'),
(435, 72, 1, 'credit', 'usdt', 7.00, 'Deposit approved • Binance ID | 1217447525 • TXID: 422547934353973248', '2026-03-27 09:01:15'),
(436, 72, NULL, 'debit', 'usdt', 7.50, 'Purchase: ✅ Azure PAY AZ YOU GO  (Upgrade) • Qty: 1 • Order #166', '2026-03-27 09:03:43'),
(437, 1, NULL, 'credit', 'usdt', 7.50, 'Sale income: ✅ Azure PAY AZ YOU GO  (Upgrade) • Qty: 1 • Order #166', '2026-03-27 09:03:43'),
(438, 72, 1, 'credit', 'usdt', 1.00, 'Gift', '2026-03-27 09:06:29'),
(439, 77, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-27 18:59:08'),
(440, 78, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-28 02:31:03'),
(441, 78, 1, 'credit', 'usdt', 13.00, 'Admin balance added', '2026-03-28 03:45:17'),
(442, 78, 1, 'credit', 'usdt', 1.00, 'Gift', '2026-03-28 03:45:36'),
(443, 78, NULL, 'debit', 'usdt', 13.80, 'Purchase: Azure student 100$ Edu mail ( Renew ) • Qty: 3 • Order #167', '2026-03-28 03:47:05'),
(444, 1, NULL, 'credit', 'usdt', 13.80, 'Sale income: Azure student 100$ Edu mail ( Renew ) • Qty: 3 • Order #167', '2026-03-28 03:47:05'),
(445, 79, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-29 04:12:47'),
(446, 80, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-03-29 18:23:56'),
(447, 11, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525', '2026-03-29 23:41:33'),
(448, 81, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-01 07:48:31'),
(449, 82, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-02 13:36:09'),
(450, 83, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-04 12:56:34'),
(451, 84, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-04 19:10:02'),
(452, 72, 1, 'credit', 'usdt', 5.00, 'Deposit approved • Binance ID | 1217447525 • TXID: 424242878789509120', '2026-04-05 12:01:19'),
(453, 72, NULL, 'debit', 'usdt', 4.60, 'Purchase: Azure student 100$ Edu mail ( Renew ) • Qty: 1 • Order #168', '2026-04-05 12:01:35'),
(454, 1, NULL, 'credit', 'usdt', 4.60, 'Sale income: Azure student 100$ Edu mail ( Renew ) • Qty: 1 • Order #168', '2026-04-05 12:01:35'),
(455, 85, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-05 12:17:39'),
(456, 72, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525 • TXID: 424409416238374912', '2026-04-06 10:20:16'),
(457, 72, 1, 'credit', 'usdt', 3.00, 'Admin balance added', '2026-04-06 10:20:57'),
(458, 72, NULL, 'debit', 'usdt', 4.60, 'Purchase: Azure student 100$ Edu mail ( Renew ) • Qty: 1 • Order #169', '2026-04-06 10:22:01'),
(459, 1, NULL, 'credit', 'usdt', 4.60, 'Sale income: Azure student 100$ Edu mail ( Renew ) • Qty: 1 • Order #169', '2026-04-06 10:22:01'),
(460, 90, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-08 15:13:35'),
(461, 91, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-09 08:46:16'),
(462, 1, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #170', '2026-04-10 20:54:22'),
(463, 92, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-11 04:46:15'),
(464, 93, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-11 05:12:30'),
(465, 94, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-11 18:50:00'),
(466, 95, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-12 09:25:08'),
(467, 1, NULL, 'debit', 'usdt', 5.50, 'Purchase: Azure student 100$ Edu mail ( Renew ) OLD STOCK 2026 • Qty: 1 • Order #171', '2026-04-13 01:31:46'),
(468, 96, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-13 10:02:57'),
(469, 98, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-19 08:07:19'),
(470, 98, 1, 'credit', 'usdt', 7.00, 'Deposit approved • Binance ID | 1217447525', '2026-04-19 09:57:23'),
(471, 98, NULL, 'debit', 'usdt', 6.50, 'Purchase: Azure student 100$ Edu mail ( Renew ) OLD STOCK 2026 Asia location support✅😉 • Qty: 1 • Order #172', '2026-04-19 12:29:13'),
(472, 1, NULL, 'credit', 'usdt', 6.50, 'Sale income: Azure student 100$ Edu mail ( Renew ) OLD STOCK 2026 Asia location support✅😉 • Qty: 1 • Order #172', '2026-04-19 12:29:13'),
(474, 72, 1, 'credit', 'usdt', 10.00, 'Deposit approved • Binance ID | 1217447525 • TXID: 427193875632807936', '2026-04-21 11:05:16'),
(475, 72, 1, 'debit', 'usdt', 9.50, 'Get pay az', '2026-04-21 11:07:26'),
(476, 100, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-26 02:48:48'),
(477, 101, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-26 05:39:02'),
(478, 100, 1, 'credit', 'usdt', 4.50, 'Deposit approved • Binance ID | 1217447525', '2026-04-26 07:30:57'),
(479, 100, NULL, 'debit', 'usdt', 4.60, 'Purchase: azure student with api 100$ (hotmail) • Qty: 1 • Order #173', '2026-04-26 07:31:13'),
(480, 1, NULL, 'credit', 'usdt', 4.60, 'Sale income: azure student with api 100$ (hotmail) • Qty: 1 • Order #173', '2026-04-26 07:31:13'),
(481, 102, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-04-26 11:38:39'),
(482, 102, 1, 'credit', 'usdt', 1.00, 'GIFT MRLI', '2026-04-26 11:41:35'),
(483, 102, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #174', '2026-04-26 11:52:22'),
(484, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #174', '2026-04-26 11:52:22'),
(485, 1, NULL, 'debit', 'usdt', 10.00, 'Purchase: RDP 16 RAM 2 CORE linux serve (ubuntu) • Qty: 1 • Order #175', '2026-04-27 12:52:31'),
(486, 1, NULL, 'debit', 'usdt', 9.50, 'Purchase: ✅ Azure PAY AZ YOU GO  (Upgrade) • Qty: 1 • Order #176', '2026-04-27 12:52:37'),
(487, 103, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-05-08 20:14:54'),
(488, 72, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #177', '2026-05-14 03:55:20'),
(489, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #177', '2026-05-14 03:55:20'),
(490, 105, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-05-24 07:16:08'),
(491, 105, 1, 'credit', 'usdt', 25.00, 'Pay az', '2026-05-24 07:54:13'),
(492, 105, 1, 'credit', 'usdt', 1.00, 'Gift', '2026-05-24 07:54:27'),
(493, 105, NULL, 'debit', 'usdt', 25.00, 'Purchase: ✅ Azure PAY AZ YOU GO (SUPPORT CLAUDE AI) • Qty: 1 • Order #178', '2026-05-24 07:55:13'),
(494, 1, NULL, 'credit', 'usdt', 25.00, 'Sale income: ✅ Azure PAY AZ YOU GO (SUPPORT CLAUDE AI) • Qty: 1 • Order #178', '2026-05-24 07:55:13'),
(495, 105, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #179', '2026-05-24 08:51:05'),
(496, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #179', '2026-05-24 08:51:05'),
(497, 106, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-06-13 12:08:58'),
(498, 107, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-06-22 06:15:43'),
(499, 108, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-06-27 04:00:21'),
(500, 109, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-06-27 04:00:30'),
(501, 110, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-02 10:24:08'),
(502, 111, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-04 20:08:59'),
(503, 112, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-07 11:43:14'),
(504, 113, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-10 03:47:05'),
(505, 114, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-11 12:09:56'),
(506, 115, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-11 19:48:51'),
(507, 116, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-12 10:22:19'),
(508, 117, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-13 22:14:44'),
(509, 118, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-14 18:54:51'),
(510, 119, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-21 09:41:24'),
(511, 120, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-22 08:35:49'),
(512, 120, 1, 'credit', 'usdt', 10.00, 'Deposit approved • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', '2026-07-22 10:17:51'),
(513, 120, 1, 'credit', 'usdt', 2.00, 'gift', '2026-07-22 10:18:13'),
(514, 121, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-27 13:10:36'),
(515, 121, 1, 'credit', 'usdt', 0.50, 'Deposit approved • Binance ID | 1217447525', '2026-07-27 13:34:38'),
(516, 121, NULL, 'debit', 'usdt', 0.44, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #180', '2026-07-27 13:35:48'),
(517, 1, NULL, 'credit', 'usdt', 0.44, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 2 • Order #180', '2026-07-27 13:35:48'),
(518, 121, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525', '2026-07-27 22:05:24'),
(519, 121, 1, 'credit', 'usdt', 0.34, 'Deposit approved • Binance ID | 1217447525', '2026-07-27 22:05:31'),
(520, 121, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #181', '2026-07-27 22:21:49'),
(521, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #181', '2026-07-27 22:21:49'),
(522, 121, NULL, 'debit', 'usdt', 0.27, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #182', '2026-07-27 22:26:42'),
(523, 1, NULL, 'credit', 'usdt', 0.27, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #182', '2026-07-27 22:26:42'),
(524, 122, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-28 03:58:02'),
(525, 122, 1, 'credit', 'usdt', 10.00, 'Admin balance added', '2026-07-28 07:26:41'),
(526, 122, 1, 'credit', 'usdt', 2.00, 'gift', '2026-07-28 07:26:48'),
(527, 122, 1, 'debit', 'usdt', 6.00, 'RDP 8 RAM', '2026-07-28 07:41:40'),
(528, 123, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-07-29 09:53:41'),
(529, 120, NULL, 'debit', 'usdt', 2.20, 'Purchase: card vcc azure Azure virtual credit card, $2 balance, Mercury Bank • Qty: 1 • Order #183', '2026-08-02 09:15:03'),
(530, 1, NULL, 'credit', 'usdt', 2.20, 'Sale income: card vcc azure Azure virtual credit card, $2 balance, Mercury Bank • Qty: 1 • Order #183', '2026-08-02 09:15:03'),
(531, 124, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-04 08:22:38'),
(532, 122, NULL, 'debit', 'usdt', 4.80, 'Purchase: Azure student 100$ Edu mail • Qty: 1 • Order #184', '2026-08-06 09:07:00'),
(533, 1, NULL, 'credit', 'usdt', 4.80, 'Sale income: Azure student 100$ Edu mail • Qty: 1 • Order #184', '2026-08-06 09:07:01'),
(534, 125, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-06 09:51:46'),
(535, 127, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-07 02:30:56'),
(536, 128, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-13 01:37:44'),
(537, 129, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-14 09:14:45'),
(538, 130, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-18 19:51:36'),
(539, 131, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-20 14:13:27'),
(540, 132, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-21 07:18:58'),
(541, 133, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-08-31 10:37:25'),
(542, 134, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-10 23:51:45'),
(543, 135, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-13 09:36:12'),
(544, 28, NULL, 'debit', 'usdt', 0.22, 'Purchase: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #185', '2026-09-14 14:27:25'),
(545, 1, NULL, 'credit', 'usdt', 0.22, 'Sale income: ✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc. • Qty: 1 • Order #185', '2026-09-14 14:27:25'),
(546, 136, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-15 03:26:28'),
(547, 137, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-18 14:40:56'),
(548, 138, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-21 14:49:26'),
(549, 139, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-21 15:48:50'),
(550, 140, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-23 18:05:19'),
(551, 141, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-28 21:16:03'),
(552, 142, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-29 10:04:03'),
(553, 130, 1, 'note', 'usdt', 0.00, 'Deposit rejected • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3', '2026-09-30 15:14:51'),
(554, 138, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525 • TXID: 455622283645313024', '2026-09-30 15:14:52'),
(555, 121, 1, 'note', 'usdt', 0.00, 'Deposit rejected • Binance ID | 1217447525', '2026-09-30 15:14:53'),
(556, 127, 1, 'note', 'usdt', 0.00, 'Deposit rejected • USDT TRC20 | TGoU24wSmPTNZWNnfYt54FC8ZjEaZxECS3 • TXID: 9d415d48c30190c60d03e3ed78ca06672b735ad9e820d525973d3bc892f788ea', '2026-09-30 15:14:56'),
(557, 143, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-09-30 22:31:15'),
(558, 144, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-10-03 15:15:53'),
(559, 145, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-10-04 12:39:32'),
(560, 146, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-10-05 19:49:12'),
(561, 147, NULL, 'credit', 'usdt', 0.10, 'Welcome bonus', '2026-10-08 08:11:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(190) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('user','admin','seller','support') NOT NULL DEFAULT 'user',
  `status` enum('active','blocked') NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `avatar_url` varchar(500) DEFAULT NULL,
  `bio` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `status`, `created_at`, `avatar_url`, `bio`) VALUES
(1, 'Mrbeat', '2002@gmail.com', '$2y$10$cQjWv7A0QKmqQUb2Q/.uu.rL7qYOroRFBd49ZfVXZsFaxrqTYOQPa', 'admin', 'active', '2026-03-05 13:39:51', 'uploads/avatars/av_1_1772798785_2356.jpg', 'or Sale\r\n\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable:\r\n\r\nSpecifications: RDP 16, 32GB RAM, 4GB Storage, 1-Month Warranty\r\n\r\n✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.\r\n\r\n🔥 Special Price! Con'),
(3, 'mohamed warda', 'reda@gmail.com', '$2y$10$BmCy55CViiGxyUOiRug5I.7BU0BHF7rp810Pxm.0uqfK7VzL1OUEi', 'user', 'active', '2026-03-05 14:53:43', 'uploads/avatars/av_3_1772794251_5160.jpg', 'لسنا ضعفاء لكننا شرفاء'),
(10, 'r-resller', 'rrr', '$2y$10$iraTPe3n0/3chYLz6PKZx.hwuKJK2w2S9qspHsTu9Z/Ckhnng3fuS', 'seller', 'active', '2026-03-06 15:15:09', 'uploads/avatars/admin_10_1772827476_8761.png', 'For Sale\r\n\r\n✅ Azure Student Dashboard, $100 (Renewed Stock)\r\nAvailable:\r\n\r\nSpecifications: RDP 16, 32GB RAM, 4GB Storage, 1-Month Warranty\r\n\r\n✅ Clean University Email Account (Contains Personal Information), Office 365 Account, etc.\r\n\r\n🔥 Special Price! Co'),
(11, 'reda', NULL, '$2y$10$Rgorg5w4cisSSk6Lhbw3cuIQp7a4FgCrncFxclBuFn0K2.cu3ELsq', 'user', 'active', '2026-03-07 10:30:09', 'uploads/avatars/av_11_1772883674_4057.jpg', NULL),
(15, 'ali', 'ali@gmail.com', '$2y$10$s8Qe1sxZzoXfNbjDPcg8SeQXdKFZHu82jlMutodTBIGNI4.Rx9ity', 'user', 'active', '2026-03-07 19:07:47', NULL, NULL),
(21, 'admin', 'anub7com@gmail.com', '$2y$10$4HtJi7b3xkjFhUVpMb1msupv/NNDx7S2INdihSHS7u6XIB.pvLpfa', 'user', 'active', '2026-03-08 15:25:02', NULL, NULL),
(23, 'Bakabaji', 'telet2600@gmail.com', '$2y$10$uaIRm8alayyIHLQyiVtnDewfxNwXufdgzx2hXL0CnXnAQf9xQz2vy', 'user', 'active', '2026-03-08 19:51:08', NULL, NULL),
(24, 'Sadbunny', 'lodaelgharebb@gmail.com', '$2y$10$rfbhu1olR3Y64.IGDaSSAusIBU/crdnAmN/1EyESEn9udTrdgLxuq', 'seller', 'active', '2026-03-08 21:22:41', NULL, NULL),
(25, 'Hossam', 'sedomemo007@gmail.com', '$2y$10$QIctqqkEx4QFR881z2U2LuFJqbW6q6d6rAtCVjoq3JWB0TLcyQUQ.', 'user', 'active', '2026-03-09 16:43:44', NULL, NULL),
(26, 'yossefpor', 'dhdhshdhhdb@gmail.com', '$2y$10$ru8rhXssrPKeF0qBZTtPvO0AQY8Eqcum.maAGJTVNHKw/chb7JWey', 'user', 'active', '2026-03-09 21:14:27', NULL, NULL),
(28, 'Abderrezak', 'cardmaster638@gmail.com', '$2y$10$2Hw0ARky8raS2EVPqP.DLe7WHE/1/5k2L/xHQPRf3ajYkRTLB8eKu', 'user', 'active', '2026-03-10 03:56:04', NULL, NULL),
(29, 'czbd', 'bdcoderzone@gmail.com', '$2y$10$7ul3BA8VHzN5daOAano2vOC9wiqt91hCUZAtQCYhM1RbzGtapYb3e', 'user', 'active', '2026-03-10 06:46:21', NULL, NULL),
(30, 'gaber', 'gaber@gmail.com', '$2y$10$Z06IjGzHrj3O8X7km7G7IutROWRBGY0g3RlhJENAFIKhDT.Lsd27O', 'seller', 'active', '2026-03-10 12:03:21', NULL, NULL),
(31, 'sayed', 'sae@outlook.com', '$2y$10$auL1SISxDtQe.Iv4T5pgUeHwqrx06QojcUQ5g6Sqk/lhsq.5Qzfxi', 'user', 'active', '2026-03-10 14:05:18', NULL, NULL),
(32, 'sakuya', 'sakuya@zxclk.com', '$2y$10$.gKn.Qi3swxpglG6ihHFauX3gxFTJ37bAkkbN7FcUUJvrJtKRP1RO', 'user', 'active', '2026-03-12 01:04:53', NULL, NULL),
(33, 'plunging7999', 'plunging7999@qq.com', '$2y$10$ncFg6k2nvnZ2ZUgv9Sy0sejfj5I5qKGclxJg/LaJStdi4odhmZMjy', 'user', 'active', '2026-03-12 09:52:57', NULL, NULL),
(34, 'mrbeattest', 'htq8rdjz0@mozmail.com', '$2y$10$TSzH2h7xi0e.LsCk95RSy.IwZlmSX6ZhZFowogxjZxgHLPP/p5ihq', 'user', 'active', '2026-03-12 14:52:34', NULL, NULL),
(35, 'verson', NULL, '$2y$10$9BGmzftw5fMKlIRuRE21YOI.a9WETxvphxz2Qt7gRRUNzqbVRVm2G', 'user', 'active', '2026-03-14 07:32:33', NULL, NULL),
(36, 'TEST@test.com', NULL, '$2y$10$4SDkbrZw1ZyxrGH7lM5N..T.YpZDUq7S9Xf5KvjlMaPdQJKhOzsFK', 'user', 'blocked', '2026-03-15 08:56:56', NULL, NULL),
(37, 'TEST1@test.com', NULL, '$2y$10$bXLNePqblFSlzPosk0udXuaKW2KC8kfKD43UkfvmiZXX1QlgDXYOO', 'user', 'blocked', '2026-03-15 09:03:11', NULL, NULL),
(38, 'TEST2@test.com', NULL, '$2y$10$mQsGEIgo551v1MUxuvCQiuf281V/6ZPYXuHY5guYgvnpzNX6K6hli', 'user', 'active', '2026-03-15 09:04:49', NULL, NULL),
(39, 'TEST3@test.com', NULL, '$2y$10$amnIkcZwSoB6A.UvFUGiAuS4gKtCfV8REllqx8aN28IYhIKTqYSj2', 'user', 'blocked', '2026-03-15 09:05:20', NULL, NULL),
(40, 'TES4T@test.com', NULL, '$2y$10$OWCA1Mj.3ybEH9Dt8Lbl2eUhsLBTDLesvVRU8I7FnmWWHTCDFWSlW', 'user', 'active', '2026-03-15 09:05:48', NULL, NULL),
(41, 'TEST5@test.com', NULL, '$2y$10$gcMhGvyZpF3FtnMj0T0VEesXrohSmE5CXJG238fSOMiucmFTigpLW', 'user', 'blocked', '2026-03-15 09:06:15', NULL, NULL),
(42, 'TEST6@test.com', NULL, '$2y$10$2UyxO2jriX20rnU6ngU6q.TfD51v5tUQ.1yPhLMWtbr/O7ztAX4Yq', 'user', 'blocked', '2026-03-15 09:06:44', NULL, NULL),
(45, 'TES66666@test.com', NULL, '$2y$10$HIqTcIOGJVmHDBOIM4vI8eOhpnGcoCZF5IAY9JPJ5UCvD0RAa9A4u', 'user', 'blocked', '2026-03-15 09:07:29', NULL, NULL),
(46, 'TEST5555@test.com', NULL, '$2y$10$w4BsReluZ6JP5tsVeXDfSeBXM0wNAnD2mEIB8c5QWbPf/uBdTey6S', 'user', 'blocked', '2026-03-15 09:08:05', NULL, NULL),
(47, 'TEST556566@test.com', NULL, '$2y$10$V/PsllCR932HYSiUZjfiB.3FtZTg62q1XNB/pH4yaNegGnscTCnB.', 'user', 'blocked', '2026-03-15 09:08:47', NULL, NULL),
(48, 'TEST22222@test.com', NULL, '$2y$10$J9x90vIM4WRzw7/Q.SXRDey6yOY6pqi26l8bUBHC.G/DgTxOFACsG', 'user', 'blocked', '2026-03-15 09:09:25', NULL, NULL),
(49, 'TEST556333test.com', NULL, '$2y$10$sXMPwqTGs.CayAJ6/i/LWOWXIq6EbEUmgomZLZoR8WFVwEN0huQKq', 'user', 'blocked', '2026-03-15 09:10:01', NULL, NULL),
(50, 'TEST2233test.com', NULL, '$2y$10$iXDOPI3vW35oLnUW4nB2nOkBrQo5Pg8/QXrNbLO5Di3rKY7t0UaDu', 'user', 'blocked', '2026-03-15 09:10:38', NULL, NULL),
(51, 'TEST522223test.com', NULL, '$2y$10$/wYXROGWAXEl3V9g7SMoHujlGDt4SipOiaBA5bGSCZhYuKA4nerE2', 'user', 'blocked', '2026-03-15 09:13:44', NULL, NULL),
(52, 'TES123213@TEsT.COM', NULL, '$2y$10$kk8joKcp3mtTXnqDKBm1UO5MZgCpY4rBtrlDLiQB2P9q95nxJMJFy', 'user', 'blocked', '2026-03-15 09:15:57', NULL, NULL),
(53, 'TEST222223test.com', NULL, '$2y$10$1A1c1i1/ITu94s6mtUwuyO7/IaYUQJseSqp/thiA0usZctPv1g2YS', 'user', 'blocked', '2026-03-15 09:20:04', NULL, NULL),
(54, 'a#JH4J5458', NULL, '$2y$10$lBHkMzTViumYbefwKi/RquEBduM8rH3TVNjJksT1WHES8PqbBWk8G', 'user', 'blocked', '2026-03-15 09:20:51', NULL, NULL),
(55, 'a3HGJ4548577', NULL, '$2y$10$vaGo1s2ZtUjqt4cg2xqLN.iMLcAgUb74R0ogdNN/Iu7eVhMeX4h8u', 'user', 'blocked', '2026-03-15 09:21:14', NULL, NULL),
(56, 'a#JH45488', NULL, '$2y$10$C29RwHKB03ql4zdUuwBSduYcknpdXJ0HDzYnuhYY29GpAZWBlA/se', 'user', 'blocked', '2026-03-15 09:21:34', NULL, NULL),
(57, 'as#HGH54577787', NULL, '$2y$10$TiSoA6gcroNi6AjEry9KfueEyUa25RmFcu/b6ixtq6goPnrUpfM.m', 'user', 'blocked', '2026-03-15 09:21:52', NULL, NULL),
(58, 'a#HGH54474877', NULL, '$2y$10$EitOs8tyXJfAhEI50LIJKedWRqs1kAKLugQgZTk3q4W2XndpIgCTC', 'user', 'blocked', '2026-03-15 09:22:10', NULL, NULL),
(59, 'geiguehugrh@6', NULL, '$2y$10$ffg6BOMCDf9//.V6NgYiiOoomGQC8y3cwPmqdsbP4Kz7QfiFmXMLW', 'user', 'blocked', '2026-03-15 09:22:47', NULL, NULL),
(60, 'htgitrhgtrh@54', NULL, '$2y$10$dcDyjZKUrACc.2uODHykZekzvYZpzNrG2k6BxMs6MllyasK5p2oWi', 'user', 'blocked', '2026-03-15 09:23:06', NULL, NULL),
(61, 'a3HG454585778', NULL, '$2y$10$RfJ6xnosm.Jce7LD8.Q4UuxnqnoIo4cfIm4S3Ia.JFRAO68JAjYVe', 'user', 'blocked', '2026-03-15 09:23:23', NULL, NULL),
(62, 'a#JHJ4574787', NULL, '$2y$10$k2ogntjylT8A/B0E64v92eWlIpUkUA0J8Ka3WVnMFByHWoXCBTCPC', 'user', 'blocked', '2026-03-15 09:24:59', NULL, NULL),
(63, 'hugheruguer@588', NULL, '$2y$10$ceFIZyG1oc4A/icJlyR1D.xkElBdm2G29uqfCJuZ/P1QSlUJ58MgC', 'user', 'blocked', '2026-03-15 09:25:28', NULL, NULL),
(64, 'digjiigtru@55', NULL, '$2y$10$dTKdO/2TJnD.SobVEtT7Vu2bC6mMkj8oCeWIdWRSlFa607DU8rMG.', 'user', 'blocked', '2026-03-15 09:26:00', NULL, NULL),
(65, 'yjhryrj@44', NULL, '$2y$10$g.UMD51PPAe3mcY/WFBaWuiYfaL6E1NqcmPXyXilE0vZDcVri.Pz2', 'user', 'blocked', '2026-03-15 09:26:29', NULL, NULL),
(68, 'hugheruguer@22588', NULL, '$2y$10$mFVY/TLT/vrMju/qFKiPHOVIMA7kRYhrzOC5We9Y3v9g8QCUZ3cjO', 'user', 'blocked', '2026-03-15 09:30:47', NULL, NULL),
(69, 'TEST2222@test.com', 'TEST2222@test.comTEST2222@test.com', '$2y$10$yzHh5.aofumlcXEYTKPO0.ebuK.PBbMwphU8pcNIKFCJ853Rplm1S', 'user', 'blocked', '2026-03-15 09:51:21', NULL, NULL),
(70, 'reigeru8euy@55', 'reigeru8euy@55', '$2y$10$vPNKrxXPa3hQJ3ETVXeRK.qYWOiLbmo0g27YXT7JwWdg.COabQZs6', 'user', 'blocked', '2026-03-15 09:55:48', NULL, NULL),
(71, 'liuyu12345', NULL, '$2y$10$LgxXS1l6cNePsS6J7dyQmudVHcKkkEOMQbLRgHjFw7tcdfTdcWTO.', 'user', 'active', '2026-03-17 01:26:37', NULL, NULL),
(72, 'nhatbien99@gmail.com', NULL, '$2y$10$I95gc9YtG0MBLv9GlLr6tOLS5WdBdpDZtlet/oN.0qq0XjvOGpmz2', 'user', 'active', '2026-03-17 08:44:25', NULL, NULL),
(73, 'ukplipzwzk', 'kuymfdsq@immenseignite.info', '$2y$10$FRWiDUsBrXgPo.wR7/ImQeP6k8st0LIjVGP6y8OC4U43EAOxeul3q', 'user', 'active', '2026-03-21 20:19:44', NULL, NULL),
(74, 'mrbeasttest', 'zygopggrd@mozmail.com', '$2y$10$dVeLujfruTe7qsSH..qmS.xRqm17pNQjEUv4zGuIwwX5E07wG90EO', 'user', 'active', '2026-03-22 10:55:24', NULL, NULL),
(75, 'grand', 'grand65636@gmail.com', '$2y$10$DLst1o4Jnq7B5JiMtULf9.9cFApZhDfe/kswMIHYjrLRDtLShPcQa', 'user', 'active', '2026-03-23 13:23:48', NULL, NULL),
(76, 'skrem9988', 'askrem188@gmail.com', '$2y$10$UsjTl7rYerRTelFYo0ZyFug1JZiHbAEtuub2PL5JoX0WWLDSdM2L2', 'user', 'active', '2026-03-26 18:59:45', NULL, NULL),
(77, 'mrwaled300', 'waledawds00@gmail.com', '$2y$10$RKW2zgjoTU2ySMDUzK6Tq.4YWI3FWSiJTx4.DI7zqN3VUnUM9qVsa', 'user', 'active', '2026-03-27 18:59:08', NULL, NULL),
(78, 'xxx', 'x@wandouyu.com', '$2y$10$2zoKGzevbAVTEqZ/lsHWPO/ooM77ZHu3YbWxpOvzHnNjaMgkosIqK', 'user', 'active', '2026-03-28 02:31:03', NULL, NULL),
(79, 'asr3afd', 'asr3afd@asgea.com', '$2y$10$T.BFS6AaLLHSkC/IRwguIe/B4DLwDhf0mfEIMhEOPLbujpv6fsv5C', 'user', 'active', '2026-03-29 04:12:47', NULL, NULL),
(80, 'ilzca', 'lzca311@gmail.com', '$2y$10$DUEKKwaAiyhF8t/.yEVJLeE3zOZW/Mfa2I9rCL4BE.TmqTB9IsVZe', 'user', 'active', '2026-03-29 18:23:56', NULL, NULL),
(81, 'tamako26169', 'tamako26169@gmail.com', '$2y$10$5aj8dG7qtYanz.bEYZ7JXe2DKsZPgJE7CW8pJdSPbT.7zDk2.TJzK', 'user', 'active', '2026-04-01 07:48:31', NULL, NULL),
(82, 'hmoden99', 'mohmmadalkbisi93@gmail.com', '$2y$10$A8yO3V0DYRCj0WNAJNntaOlqktMEiu02SrkyAtHi3GVHIPZ97gdRq', 'user', 'active', '2026-04-02 13:36:09', NULL, NULL),
(83, 'shimyx911', 'hussienelshimy911@gmail.com', '$2y$10$LJ.O0nOF9JRzl3N9/0gESOe4NaXahEEc6/5aG71nPv/dujqHFjwfe', 'user', 'active', '2026-04-04 12:56:34', NULL, NULL),
(84, 's22940656', 's22940656@gmail.com', '$2y$10$0PbVag4XNaZ4LX96jakhp.y6gGsJR4dWwx4orsJzWoLqOEDHJSB6e', 'user', 'active', '2026-04-04 19:10:02', NULL, NULL),
(85, 'cmeay232@gmail.com', 'cmeay232@gmail.com', '$2y$10$aPztDdgC2ZmnJaB8G9xsnuMpXALJPmG04eXHFx8/ce2HdM5CDrDb2', 'user', 'active', '2026-04-05 12:17:39', NULL, NULL),
(89, 'alii', 'alii@gmail.com', '$2y$10$WDAsjbKDFEJnqSvhwu3hMeqsYlkP8J8uaXLyb27bOEbnNda4jeHYu', 'seller', 'active', '2026-04-07 09:21:53', NULL, NULL),
(90, 'amrmenam', NULL, '$2y$10$sqlJV7xhe3QJKpgO0HX7F.RZKSeSfAgKqiW.5nQ64uFL.O6wr5mRS', 'user', 'active', '2026-04-08 15:13:35', NULL, NULL),
(91, 'sunaoen33', 'sunaoen33@outlook.com', '$2y$10$pBA78f..4laODjwUb7N5AOhRi6uTnZw4IgFfmb3Ex//sKkcnPzNc2', 'user', 'active', '2026-04-09 08:46:16', NULL, NULL),
(92, 'lmyktdtgdp', 'vmfosyzg@immenseignite.info', '$2y$10$wZvP1M3FUQVQNJG/ciJezenIl6BudfNO34gxN2NNnxWwOtHGio86m', 'user', 'active', '2026-04-11 04:46:14', NULL, NULL),
(93, 'hazem farouk', 'zoma01271@gmail.com', '$2y$10$FgH1hr7j0acn39vr/wB4GO/HOQINF6G7socZ/pseSYEjnrwMQbWVC', 'user', 'active', '2026-04-11 05:12:30', NULL, NULL),
(94, 'mostafato150', 'mostafato150@gmail.com', '$2y$10$8NtQFRYqm1Cc/tYIM3Xml.6EEEg7XPWyUhAWVpFTABtWH8c7DvcVq', 'user', 'active', '2026-04-11 18:50:00', NULL, NULL),
(95, 'web298', 'web298@outlook.com', '$2y$10$7vjl0CTOX5tL7fLyU1wfYegi1tA.CmJHoUANsZSNh7V9v4hUMEM9q', 'user', 'active', '2026-04-12 09:25:08', NULL, NULL),
(96, 'fulf88', 'jamsik@gmail.com', '$2y$10$.FGHVXzeiT/53HKzelp3EOsts4zEnqNB0PHRc7qxNwI.qj/Cv2hLW', 'user', 'active', '2026-04-13 10:02:57', NULL, NULL),
(98, 'abderrezak8', 'razakbouaziz8@gmail.com', '$2y$10$R29u78izIuvEw1BCfWovgewskIwp4SzGC/HP3Frw.Q2hbu94vnOXe', 'user', 'active', '2026-04-19 08:07:19', NULL, NULL),
(100, 'Nour', 'qmer990@gmail.com', '$2y$10$52Qp8onAloZM9rCiR9SSyeVqoBayEedEFyRRzmbYSZzkI.XXIngQO', 'user', 'active', '2026-04-26 02:48:48', NULL, NULL),
(101, 'zdspmwyogi', 'nlwhsszj@immenseignite.info', '$2y$10$24Ldb/OUSqcnt1JRpz/uEum3EbZkOZl7ME7XrKxj5F7sgRftJtGlS', 'user', 'active', '2026-04-26 05:39:02', NULL, NULL),
(102, '952409302@qq.com', '952409302@qq.com', '$2y$10$i/9l0xGUN7c1KD4Sxb5YGORVGMLb8c2jNYwsgxxvCTDrLcV1/6akS', 'seller', 'active', '2026-04-26 11:38:39', NULL, NULL),
(103, 'kfelshhmwg', 'vfuzotjo@ immenseignite.info', '$2y$10$aJMLLWiTJUmmBG8gejS.xu61dARqlAlEBWJ9CfH8wNeem5QzgZ9vq', 'user', 'active', '2026-05-08 20:14:54', NULL, NULL),
(105, 'yeshooo', 'yeshooo123@gmail.com', '$2y$10$nYVZKAPnA10oeBLA/Nwy1OjDpa24iULl87zbdiybY59UWvtlEs1iC', 'user', 'active', '2026-05-24 07:16:08', NULL, NULL),
(106, 'chems eddine', 'ing.chems.eddine23@gmail.com', '$2y$10$CVo4vBjEh5iNn7cIYO4pOuMJA5fwME7Ro5PcHqcpJDfRmkCH/pZcy', 'user', 'active', '2026-06-13 12:08:58', NULL, NULL),
(107, 'intenelody', 'intenelody@gmail.com', '$2y$10$Nq4XwVD8T4lJvQ4dUeOXEe2dngqAP7WSeWtInmePTc9aCsvSlQvTm', 'user', 'active', '2026-06-22 06:15:43', NULL, NULL),
(108, 'eldyznidmq', 'xxjfsfvl@immenseignite.info', '$2y$10$Ln0DmnefT0b3rK2aSlvvz.vaMj1jvMzPV6SwB6ftX280BEqXTwv8O', 'user', 'active', '2026-06-27 04:00:21', NULL, NULL),
(109, 'mwvmoksxky', 'mkdedqjd@ immenseignite.info', '$2y$10$zX/jsc6EKXb4nRiO57Zz3Ouft/ftEfhKsZZqDkz/Ph9XDodY49AYG', 'user', 'active', '2026-06-27 04:00:30', NULL, NULL),
(110, 'aleemohammad', NULL, '$2y$10$6i.NdqoXIcip9NRyFa38.O6GN5DgJ5QF7lmBTfOdXgZVFXg7UL1wi', 'user', 'active', '2026-07-02 10:24:08', NULL, NULL),
(111, 'wodefa001', 'wodefa001@dl6.me', '$2y$10$REXcnCjdfgaeDiqjcCGlvezLAIGjqSlonhfYOcnYo.QT4sa237wgi', 'user', 'active', '2026-07-04 20:08:59', NULL, NULL),
(112, 'saobien', 'vpsmo@live.com', '$2y$10$dD0BZy0WW5/gQElj98so7uX.8h5JXcia.nPeksqqLrUnjHwi0TQ9m', 'user', 'active', '2026-07-07 11:43:14', NULL, NULL),
(113, 'xhbce96959', 'xhbce96959@minitts.net', '$2y$10$6C8mW2331cHQryQt7g5pheDv2qPckTlGCiaT..sqI6Z43G2n5HpeG', 'user', 'active', '2026-07-10 03:47:05', NULL, NULL),
(114, 'hamza7898', 'hb999859@gmail.com', '$2y$10$D36B.QMEhKv6fnxfrCdqleXAmo25xsWsCBGaxh6ARGcGw71lDlLIK', 'user', 'active', '2026-07-11 12:09:56', NULL, NULL),
(115, 'nanerog570', 'nanerog570@duvips.com', '$2y$10$rhhWqlFHkmRdKMUeujon/uVchZCEHiDMyaYB1Qh/PCXvW.sk0rC0O', 'user', 'active', '2026-07-11 19:48:51', NULL, NULL),
(116, 'burhan', 'burhanazure25@gmail.com', '$2y$10$wHI97ALEMuHiXNYTjS0YGuqXQUe3EBKjBgqxp2BkCV..wfLoJTrxW', 'user', 'active', '2026-07-12 10:22:19', NULL, NULL),
(117, 'mizanym', NULL, '$2y$10$dMARS/bo.tgZi8oriP5ZROoqcY4GAcFcFaXHB8emGECyVXpy1dSqW', 'user', 'active', '2026-07-13 22:14:44', NULL, NULL),
(118, 'sasadas', 'adaddaadyy@gmail.com', '$2y$10$M3x2fEw6ZgInnUJM6Om8ceBX6xhg7Wlo9Xbk0rh9QeU37zOHmCiRq', 'user', 'active', '2026-07-14 18:54:51', NULL, NULL),
(119, 'a3128769', 'atear02s@gmail.com', '$2y$10$5ZnhF0zHDwTXNbYttw58JOIm4y28A35aDCtePbESRW0zCggKLSzGu', 'user', 'active', '2026-07-21 09:41:24', NULL, NULL),
(120, 'mikebatty099', 'mikebatty099@atomicmail.io', '$2y$10$Z.UuC80XOghKkjLEnpUaC.ROpffKUmpRPUCJnt.N5r25jH0qrFUl6', 'user', 'active', '2026-07-22 08:35:49', NULL, NULL),
(121, 'novalanto', 'aabc3464@gmail.com', '$2y$10$GCmeWrv8scOCQevuM9LW6OyKyA/x9rwerz/nQtOU0ZNgSxK3ujnm6', 'user', 'active', '2026-07-27 13:10:36', NULL, NULL),
(122, 'sulk-gem', 'sulk-gem-darkness@duck.com', '$2y$10$N.0I3ka75NA3tO1JS1biDe4LHIIhwoFgkscrZLWBd6uBaXbkibJxu', 'user', 'active', '2026-07-28 03:58:02', NULL, NULL),
(123, 'Mireg', 'mireg33438@apdtax.com', '$2y$10$/EwrDzXY7zmL/7w.n5NLY.nnKHywCh7flv7ijXughjnK3Kqinyjfi', 'user', 'active', '2026-07-29 09:53:41', NULL, NULL),
(124, 'ArlucrobyTonenhg', 'ArlucrobyTonenhg@hotmail.com', '$2y$10$cu0.EJDMG4f0OQhslZUTAOe/D8mxycotbdgJzEEM5QvXr8nKwqVSC', 'user', 'active', '2026-08-04 08:22:38', NULL, NULL),
(125, 'lintyo22', 'hyyy7548@proton.me', '$2y$10$GSaAOYFzYHnmwk3H6djO9O.bKJE5byBei0GKjj/C/meEKoF5Ek/K.', 'user', 'active', '2026-08-06 09:51:46', NULL, NULL),
(127, 'juiye2', 'm1ntxupu@proton.me', '$2y$10$I96JqfPTlrAiMRrrx2fs7.Tr27SUVuHjpIPr4XaveMVLiVgrxWVzC', 'user', 'active', '2026-08-07 02:30:56', NULL, NULL),
(128, 'Yunus', 'Yunus@usone.eu.org', '$2y$10$97OKQFSdhmBQhEhhkPsUqer1FbudpGKm03IEI1/yQtbqoKrEVc1vO', 'user', 'active', '2026-08-13 01:37:44', NULL, NULL),
(129, 'axi', 'lbxx1123@gmail.com', '$2y$10$xO0tkdNDrXrSXh2ImiN9SerNzv9Wt58v1PM3PrXaN/jQV3Z7kF0/m', 'user', 'active', '2026-08-14 09:14:45', NULL, NULL),
(130, 'kingjacob', NULL, '$2y$10$S/XueZCe/OifZ8yQ60UTKO2/ZwxJGttAa93wZIl6c0LwJnUdCc1M2', 'user', 'active', '2026-08-18 19:51:36', NULL, NULL),
(131, 'amenohan', 'fokire6870@prodbits.com', '$2y$10$TgwAzQHSnelIVhD1oA9kbuwt9JbnoHCDitvVt38jJePyF1vA929pC', 'user', 'active', '2026-08-20 14:13:27', NULL, NULL),
(132, 'kingjones', NULL, '$2y$10$lKSjNckcz3Wy55w2ONddCel1DFuLd8qOB/4vx7hT5OjEnZMzkTQTC', 'user', 'active', '2026-08-21 07:18:58', NULL, NULL),
(133, 'Summer', NULL, '$2y$10$jjrZyhwPllpxbP34Bc.mBOh1ZIBLChdiNE0A338PM0zbkXwXKTRd6', 'user', 'active', '2026-08-31 10:37:25', NULL, NULL),
(134, 'madamtaisia8585', 'madamtaisia@mail.ru', '$2y$10$kuklWjaCplXDAUw/hVQYeOGvLeVtnYQzLG4Ri6m/JqoqUZQ2u2bdu', 'user', 'active', '2026-09-10 23:51:45', NULL, NULL),
(135, 'raisahab', 'raisahab264@gmail.com', '$2y$10$.zCccowHFusfBcbltoAZkOzLt0fFP2Z.6quJcjOtqkDzSvn92ikHC', 'user', 'active', '2026-09-13 09:36:12', NULL, NULL),
(136, 'Peter', 'uopredsalo6@gmail.com', '$2y$10$sGws6rJKNgPX7OELpVesvuyeK8gP8bZTztf9zcnq54/C1qRQG0njq', 'user', 'active', '2026-09-15 03:26:28', NULL, NULL),
(137, 'Buddy', 'riwingbeen@gmail.com', '$2y$10$wcQn1GTizhFsBdIgieYLC.eubdnxiAbmD8/m7sHK1ksyn3SgkUWP6', 'user', 'active', '2026-09-18 14:40:56', NULL, NULL),
(138, 'pvssyfkermelown', NULL, '$2y$10$xFF04Q5l3EnDvljClVj3/uYlRJDEbKVCwboj29m.mlHDlirpN6p9y', 'user', 'active', '2026-09-21 14:49:26', NULL, NULL),
(139, 'pussyfuckman', NULL, '$2y$10$WOyeRpfiRs0BSQ81cE6tRuMhRb2eFkekueexZTfDNNtMVw84O4yTm', 'user', 'active', '2026-09-21 15:48:50', NULL, NULL),
(140, 'remarosiak', NULL, '$2y$10$R7tWTAnOjjUnf835megWyOGdkThgAShkkjnJqaPTnaqty6eBoIV9q', 'user', 'active', '2026-09-23 18:05:19', NULL, NULL),
(141, 'MAY', 'MAY.MOHAMED02@f-arts.helwan.edu.eg', '$2y$10$L7pJY1lIeUDx2MJM0PkgsO29atqeld.artlR.hYx0VdoYP4fCcxIe', 'user', 'active', '2026-09-28 21:16:03', NULL, NULL),
(142, 'Yunus@usone.eu.org', 'Azure188@Yun', '$2y$10$1217Q/LFawD5QVw.4A9mqOpoqJIM6yHbbZ1SuXudCXvbAmh6dHNEu', 'user', 'active', '2026-09-29 10:04:03', NULL, NULL),
(143, 'No544', NULL, '$2y$10$jhZpj4mu3VKvRKbgNKsUnuRYaD9oR5/z.NTUnU8.twpjILXpPa99u', 'user', 'active', '2026-09-30 22:31:15', NULL, NULL),
(144, 'yangzai666', 'sanbaiwang008@gmail.com', '$2y$10$MbXlvw9MWxE51bGWcOdjC.B.JV7mC8S6hCg.Fit9yW.UZqlor3LPG', 'user', 'active', '2026-10-03 15:15:53', NULL, NULL),
(145, 'Erik', 'erik.gerl3@gmail.com', '$2y$10$/FCZmyEv2pE6VV2QeYQ4xuXsAEWEGBDR0yBMZwP1TEmTD3tkiFZpC', 'user', 'active', '2026-10-04 12:39:32', NULL, NULL),
(146, 'kosomak', 'pedey14181@hudzer.com', '$2y$10$XhDg6jTm/zSWPxdqWjxsP..GXuFrLHro.U1.TTQqLHFBIxVdFib/m', 'user', 'active', '2026-10-05 19:49:12', NULL, NULL),
(147, 'younger', 'youjoyyy@gmail.com', '$2y$10$rYRUnBn7vdF6pn/O9kQ.4OTiNaxX1K.Uy63tPLDyMvVa8CkzqRXJy', 'user', 'active', '2026-10-08 08:11:33', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `wallets`
--

CREATE TABLE `wallets` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `balance_mb` decimal(18,2) NOT NULL DEFAULT 0.00,
  `balance_usdt` decimal(18,2) NOT NULL DEFAULT 0.00,
  `hold_usdt` decimal(18,2) NOT NULL DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wallets`
--

INSERT INTO `wallets` (`user_id`, `balance_mb`, `balance_usdt`, `hold_usdt`, `updated_at`) VALUES
(1, 0.00, 143.27, 0.00, '2026-09-14 14:27:25'),
(3, 0.00, 779.68, 0.00, '2026-03-15 19:42:13'),
(10, 0.00, 2060.20, 0.00, '2026-03-09 02:59:10'),
(11, 0.00, 78.28, 0.00, '2026-03-18 18:28:00'),
(15, 0.00, 98.00, 0.00, '2026-03-07 19:08:45'),
(21, 0.00, 1.00, 0.00, '2026-03-08 15:25:02'),
(23, 0.00, 1.00, 0.00, '2026-03-08 19:51:08'),
(24, 0.00, 1.00, 0.00, '2026-03-08 21:22:41'),
(25, 0.00, 1.00, 0.00, '2026-03-09 16:43:44'),
(26, 0.00, 1.00, 0.00, '2026-03-09 21:14:27'),
(28, 0.00, 0.78, 0.00, '2026-09-14 14:27:25'),
(29, 0.00, 0.80, 0.00, '2026-03-10 06:46:43'),
(30, 0.00, 1.00, 0.00, '2026-03-10 12:32:54'),
(31, 0.00, 1.00, 0.00, '2026-03-10 14:23:31'),
(32, 0.00, 1.00, 0.00, '2026-03-12 06:21:12'),
(33, 0.00, 0.00, 0.00, '2026-03-12 10:00:43'),
(34, 0.00, 0.08, 0.00, '2026-03-12 14:55:31'),
(35, 0.00, 0.11, 0.00, '2026-03-14 12:55:18'),
(36, 0.00, 0.11, 0.00, '2026-03-15 09:02:42'),
(37, 0.00, 0.04, 0.00, '2026-03-15 09:03:28'),
(38, 0.00, 0.04, 0.00, '2026-03-15 09:05:06'),
(39, 0.00, 0.04, 0.00, '2026-03-15 09:05:35'),
(40, 0.00, 0.04, 0.00, '2026-03-15 09:06:03'),
(41, 0.00, 0.04, 0.00, '2026-03-15 09:06:30'),
(42, 0.00, 0.04, 0.00, '2026-03-15 09:06:58'),
(45, 0.00, 0.04, 0.00, '2026-03-15 09:07:43'),
(46, 0.00, 0.04, 0.00, '2026-03-15 09:08:29'),
(47, 0.00, 0.04, 0.00, '2026-03-15 09:09:05'),
(48, 0.00, 0.11, 0.00, '2026-03-15 09:09:43'),
(49, 0.00, 0.04, 0.00, '2026-03-15 09:10:14'),
(50, 0.00, 0.04, 0.00, '2026-03-15 09:11:55'),
(51, 0.00, 0.04, 0.00, '2026-03-15 09:13:52'),
(52, 0.00, 0.04, 0.00, '2026-03-15 09:16:03'),
(53, 0.00, 0.04, 0.00, '2026-03-15 09:20:11'),
(54, 0.00, 0.04, 0.00, '2026-03-15 09:20:58'),
(55, 0.00, 0.04, 0.00, '2026-03-15 09:21:19'),
(56, 0.00, 0.04, 0.00, '2026-03-15 09:21:38'),
(57, 0.00, 0.04, 0.00, '2026-03-15 09:21:57'),
(58, 0.00, 0.18, 0.00, '2026-03-15 09:22:27'),
(59, 0.00, 0.18, 0.00, '2026-03-15 09:22:53'),
(60, 0.00, 0.04, 0.00, '2026-03-15 09:23:11'),
(61, 0.00, 0.04, 0.00, '2026-03-15 09:23:30'),
(62, 0.00, 0.18, 0.00, '2026-03-15 09:25:06'),
(63, 0.00, 0.18, 0.00, '2026-03-15 09:25:37'),
(64, 0.00, 0.18, 0.00, '2026-03-15 09:26:07'),
(65, 0.00, 0.18, 0.00, '2026-03-15 09:26:36'),
(68, 0.00, 0.04, 0.00, '2026-03-15 09:30:53'),
(69, 0.00, 0.18, 0.00, '2026-03-15 09:51:30'),
(70, 0.00, 0.04, 0.00, '2026-03-15 09:55:52'),
(71, 0.00, 0.01, 0.00, '2026-03-18 06:27:44'),
(72, 0.00, 0.96, 0.00, '2026-05-14 03:55:20'),
(73, 0.00, 0.10, 0.00, '2026-03-21 20:19:44'),
(74, 0.00, 1.10, 0.00, '2026-03-22 12:34:30'),
(75, 0.00, 0.10, 0.00, '2026-03-23 13:23:48'),
(76, 0.00, 0.10, 0.00, '2026-03-26 18:59:45'),
(77, 0.00, 0.10, 0.00, '2026-03-27 18:59:08'),
(78, 0.00, 0.30, 0.00, '2026-03-28 03:47:05'),
(79, 0.00, 0.10, 0.00, '2026-03-29 04:12:47'),
(80, 0.00, 0.10, 0.00, '2026-03-29 18:23:56'),
(81, 0.00, 0.10, 0.00, '2026-04-01 07:48:31'),
(82, 0.00, 0.10, 0.00, '2026-04-02 13:36:09'),
(83, 0.00, 0.10, 0.00, '2026-04-04 12:56:34'),
(84, 0.00, 0.10, 0.00, '2026-04-04 19:10:02'),
(85, 0.00, 0.10, 0.00, '2026-04-05 12:17:39'),
(89, 0.00, 0.00, 0.00, '2026-04-07 09:21:53'),
(90, 0.00, 0.10, 0.00, '2026-04-08 15:13:35'),
(91, 0.00, 0.10, 0.00, '2026-04-09 08:46:16'),
(92, 0.00, 0.10, 0.00, '2026-04-11 04:46:15'),
(93, 0.00, 0.10, 0.00, '2026-04-11 05:12:30'),
(94, 0.00, 0.10, 0.00, '2026-04-11 18:50:00'),
(95, 0.00, 0.10, 0.00, '2026-04-12 09:25:08'),
(96, 0.00, 0.10, 0.00, '2026-04-13 10:02:57'),
(98, 0.00, 0.60, 0.00, '2026-04-19 12:29:13'),
(100, 0.00, 0.00, 0.00, '2026-04-26 07:31:13'),
(101, 0.00, 0.10, 0.00, '2026-04-26 05:39:02'),
(102, 0.00, 0.88, 0.00, '2026-04-26 11:52:22'),
(103, 0.00, 0.10, 0.00, '2026-05-08 20:14:54'),
(105, 0.00, 0.88, 0.00, '2026-05-24 08:51:05'),
(106, 0.00, 0.10, 0.00, '2026-06-13 12:08:58'),
(107, 0.00, 0.10, 0.00, '2026-06-22 06:15:43'),
(108, 0.00, 0.10, 0.00, '2026-06-27 04:00:21'),
(109, 0.00, 0.10, 0.00, '2026-06-27 04:00:30'),
(110, 0.00, 0.10, 0.00, '2026-07-02 10:24:08'),
(111, 0.00, 0.10, 0.00, '2026-07-04 20:08:59'),
(112, 0.00, 0.10, 0.00, '2026-07-07 11:43:14'),
(113, 0.00, 0.10, 0.00, '2026-07-10 03:47:05'),
(114, 0.00, 0.10, 0.00, '2026-07-11 12:09:56'),
(115, 0.00, 0.10, 0.00, '2026-07-11 19:48:51'),
(116, 0.00, 0.10, 0.00, '2026-07-12 10:22:19'),
(117, 0.00, 0.10, 0.00, '2026-07-13 22:14:44'),
(118, 0.00, 0.10, 0.00, '2026-07-14 18:54:51'),
(119, 0.00, 0.10, 0.00, '2026-07-21 09:41:24'),
(120, 0.00, 9.90, 0.00, '2026-08-02 09:15:03'),
(121, 0.00, 0.01, 0.00, '2026-07-27 22:26:42'),
(122, 0.00, 1.30, 0.00, '2026-08-06 09:07:00'),
(123, 0.00, 0.10, 0.00, '2026-07-29 09:53:41'),
(124, 0.00, 0.10, 0.00, '2026-08-04 08:22:38'),
(125, 0.00, 0.10, 0.00, '2026-08-06 09:51:46'),
(127, 0.00, 0.10, 0.00, '2026-08-07 02:30:56'),
(128, 0.00, 0.10, 0.00, '2026-08-13 01:37:44'),
(129, 0.00, 0.10, 0.00, '2026-08-14 09:14:45'),
(130, 0.00, 0.10, 0.00, '2026-08-18 19:51:36'),
(131, 0.00, 0.10, 0.00, '2026-08-20 14:13:27'),
(132, 0.00, 0.10, 0.00, '2026-08-21 07:18:58'),
(133, 0.00, 0.10, 0.00, '2026-08-31 10:37:25'),
(134, 0.00, 0.10, 0.00, '2026-09-10 23:51:45'),
(135, 0.00, 0.10, 0.00, '2026-09-13 09:36:12'),
(136, 0.00, 0.10, 0.00, '2026-09-15 03:26:28'),
(137, 0.00, 0.10, 0.00, '2026-09-18 14:40:56'),
(138, 0.00, 0.10, 0.00, '2026-09-21 14:49:26'),
(139, 0.00, 0.10, 0.00, '2026-09-21 15:48:50'),
(140, 0.00, 0.10, 0.00, '2026-09-23 18:05:19'),
(141, 0.00, 0.10, 0.00, '2026-09-28 21:16:03'),
(142, 0.00, 0.10, 0.00, '2026-09-29 10:04:03'),
(143, 0.00, 0.10, 0.00, '2026-09-30 22:31:15'),
(144, 0.00, 0.10, 0.00, '2026-10-03 15:15:53'),
(145, 0.00, 0.10, 0.00, '2026-10-04 12:39:32'),
(146, 0.00, 0.10, 0.00, '2026-10-05 19:49:12'),
(147, 0.00, 0.10, 0.00, '2026-10-08 08:11:33');

-- --------------------------------------------------------

--
-- Table structure for table `withdraw_requests`
--

CREATE TABLE `withdraw_requests` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `currency` varchar(20) NOT NULL DEFAULT 'usdt',
  `network` varchar(80) DEFAULT NULL,
  `address` varchar(255) NOT NULL,
  `amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `note` varchar(255) DEFAULT NULL,
  `status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  `admin_note` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `withdraw_requests`
--

INSERT INTO `withdraw_requests` (`id`, `user_id`, `currency`, `network`, `address`, `amount`, `note`, `status`, `admin_note`, `created_at`, `reviewed_at`, `reviewed_by`) VALUES
(1, 3, 'usdt', 'TRC20', 'FASD5AS41D78ASD4AS8D', 2000.00, NULL, 'rejected', NULL, '2026-03-06 12:22:40', '2026-03-06 12:23:01', 1),
(2, 3, 'usdt', 'TRC20', 'DXASDAS52D41CASD87AS', 430.00, NULL, 'rejected', NULL, '2026-03-06 12:28:55', '2026-03-06 12:29:03', 1),
(3, 3, 'usdt', 'TRC20', 'DXASDAS52D41CASD87AS', 2000.00, NULL, 'rejected', NULL, '2026-03-06 12:42:59', '2026-03-06 12:43:05', 1),
(4, 3, 'usdt', 'TRC20', 'sd5124asd7as8dasd', 398.00, NULL, 'approved', NULL, '2026-03-06 12:43:23', '2026-03-06 12:43:28', 1),
(5, 3, 'usdt', 'Binance ID', '1537878', 400.00, 'رجاء التاكد من عنوان النسخ', 'approved', 'ماشي يا حبيبي', '2026-03-07 03:37:51', '2026-03-07 03:38:57', 1),
(6, 10, 'usdt', 'TRC20', 'AE48nZtWMo9RtTmCPUBsFtiXbNfDcRx2YVyrPbCN9nxi', 5.00, 'بسرعه', 'approved', NULL, '2026-03-07 16:51:48', '2026-03-07 16:51:59', 1),
(7, 10, 'usdt', 'TRC20', 'sd5124asd7as8dasd', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:05', '2026-03-07 18:17:30', 1),
(8, 10, 'usdt', 'TRC20', 'sd5124asd7as8dasd', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:09', '2026-03-07 18:17:27', 1),
(9, 10, 'usdt', 'sd5124asd7as8dasd', '1', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:14', '2026-03-07 18:17:26', 1),
(10, 10, 'usdt', 'sd5124asd7as8dasd', 'sd5124asd7as8dasd', 1.00, NULL, 'approved', 'خلي بالك من نفس', '2026-03-07 17:44:17', '2026-03-07 18:17:25', 1),
(11, 10, 'usdt', 'TRC20', 'sd5124asd7as8dasd', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:24', '2026-03-07 18:17:21', 1),
(12, 10, 'usdt', 'TRC20', 'sd5124asd7as8dasd', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:32', '2026-03-07 18:17:20', 1),
(13, 10, 'usdt', 'Binance id', '12378562785', 1.00, NULL, 'approved', NULL, '2026-03-07 17:44:45', '2026-03-07 18:17:18', 1),
(14, 3, 'usdt', 'Binance ID', '1563278635278', 50.00, 'محتاجها عاجل', 'approved', NULL, '2026-03-07 19:04:22', '2026-03-07 19:04:42', 1),
(15, 10, 'usdt', 'BNB Smart Chain (BEP20)', '0xcd7f8687599b6dad21f970837d72f116e4f61bef', 10.00, NULL, 'approved', NULL, '2026-03-08 17:00:27', '2026-03-08 17:00:36', 1),
(16, 10, 'usdt', 'BNB Smart Chain (BEP20)', '0xcd7f8687599b6dad21f970837d72f116e4f61bef', 50.00, NULL, 'approved', NULL, '2026-03-08 17:00:56', '2026-03-08 17:00:59', 1),
(17, 3, 'usdt', 'Binance ID', '23874563243', 100.00, NULL, 'rejected', NULL, '2026-03-08 18:17:33', '2026-03-08 18:18:26', 1),
(18, 31, 'usdt', 'Binance ID', '584552658', 1.00, NULL, 'rejected', 'We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-10 14:06:34', '2026-03-10 14:23:31', 1),
(19, 32, 'usdt', 'USDT  BNB Smart Chain (BEP20)', '0xcd7f8687599b6dad21f970837d72f116e4f61bef', 1.00, NULL, 'rejected', NULL, '2026-03-12 01:06:51', '2026-03-12 06:21:12', 1),
(20, 11, 'usdt', 'USDT  BNB Smart Chain (BEP20)', '27485edawsdasdsa', 10.00, NULL, 'rejected', 'We apologize for the withdrawal rejection, but the login bonus cannot be withdrawn and can be used to purchase products. The minimum withdrawal amount is $10.', '2026-03-12 14:19:52', '2026-03-12 14:20:07', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `banners`
--
ALTER TABLE `banners`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `contact_methods`
--
ALTER TABLE `contact_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposit_requests`
--
ALTER TABLE `deposit_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dep_user` (`user_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_user` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_item_order` (`order_id`),
  ADD KEY `fk_item_product` (`product_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_seller_id` (`seller_id`);

--
-- Indexes for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_stock_product` (`product_id`),
  ADD KEY `fk_stock_sold_user` (`sold_to_user_id`);

--
-- Indexes for table `seller_reviews`
--
ALTER TABLE `seller_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_seller_user` (`seller_id`,`user_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`k`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ticket_user` (`user_id`);

--
-- Indexes for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_msg_ticket` (`ticket_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_tx_user` (`user_id`),
  ADD KEY `fk_tx_admin` (`admin_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `wallets`
--
ALTER TABLE `wallets`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_wr_user` (`user_id`),
  ADD KEY `fk_wr_admin` (`reviewed_by`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `banners`
--
ALTER TABLE `banners`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `contact_methods`
--
ALTER TABLE `contact_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `deposit_requests`
--
ALTER TABLE `deposit_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=186;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `product_stock`
--
ALTER TABLE `product_stock`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=703;

--
-- AUTO_INCREMENT for table `seller_reviews`
--
ALTER TABLE `seller_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=123;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=562;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `deposit_requests`
--
ALTER TABLE `deposit_requests`
  ADD CONSTRAINT `fk_dep_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_item_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_stock`
--
ALTER TABLE `product_stock`
  ADD CONSTRAINT `fk_stock_product` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_stock_sold_user` FOREIGN KEY (`sold_to_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `ticket_messages`
--
ALTER TABLE `ticket_messages`
  ADD CONSTRAINT `fk_msg_ticket` FOREIGN KEY (`ticket_id`) REFERENCES `tickets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_tx_admin` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tx_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wallets`
--
ALTER TABLE `wallets`
  ADD CONSTRAINT `fk_wallet_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `withdraw_requests`
--
ALTER TABLE `withdraw_requests`
  ADD CONSTRAINT `fk_wr_admin` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_wr_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
