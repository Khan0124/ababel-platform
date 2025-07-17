-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 17, 2025 at 07:56 PM
-- Server version: 10.11.10-MariaDB-log
-- PHP Version: 8.3.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ababel`
--

DELIMITER $$
--
-- Procedures
--
$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`id`, `user_id`, `action`, `reference_type`, `reference_id`, `details`, `created_at`) VALUES
(1, 8, 'تحديث حالة الحاوية', 'container', 241, 'تم تحديث حالة البوليصة - تم تحديث حالة التختيم', '2025-07-15 21:38:04');

-- --------------------------------------------------------

--
-- Table structure for table `cashbox`
--

CREATE TABLE `cashbox` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `usd` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1,
  `daily_expense_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashbox`
--

INSERT INTO `cashbox` (`id`, `transaction_id`, `client_id`, `container_id`, `user_id`, `type`, `source`, `category`, `description`, `method`, `amount`, `usd`, `created_at`, `notes`, `synced`, `daily_expense_id`) VALUES
(7872, 1586, 33, NULL, 8, 'صرف', 'موانئ', NULL, 'موانئ', 'بنك', 1000000.00, 0.00, '2025-07-12 13:46:47', NULL, 1, NULL),
(7873, 1587, 33, NULL, NULL, 'قبض', 'عميل', NULL, '', 'بنكك', 500000.00, 0.00, '2025-07-12 14:16:48', 'سداد مطالبة:  - العميل: السماني شرف الدين', 1, NULL),
(7874, 1588, 33, NULL, NULL, 'قبض', 'عميل', NULL, '', 'بنكك', 10000.00, 0.00, '2025-07-12 14:17:16', 'سداد مطالبة:  - العميل: السماني شرف الدين', 1, NULL),
(7875, 1589, 33, NULL, NULL, 'قبض', 'عميل', NULL, '', 'بنكك', 50000.00, 0.00, '2025-07-12 14:18:35', 'سداد مطالبة:  - العميل: السماني شرف الدين', 1, NULL),
(7876, 1590, 33, NULL, 8, 'قبض', 'دخل خارجي', NULL, '', 'بنك', 500000.00, 0.00, '2025-07-12 14:24:46', NULL, 1, NULL),
(7877, 1590, 33, NULL, NULL, 'قبض', 'عميل', NULL, '', 'بنك', 500000.00, 0.00, '2025-07-14 12:11:26', 'سداد مطالبة:  - العميل: السماني شرف الدين', 1, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `china_containers`
--

CREATE TABLE `china_containers` (
  `id` int(11) NOT NULL,
  `loading_number` varchar(50) NOT NULL,
  `container_number` varchar(50) DEFAULT NULL,
  `bill_number` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `carrier` varchar(100) DEFAULT NULL,
  `registry` varchar(100) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `expected_arrival` date DEFAULT NULL,
  `ship_name` varchar(100) DEFAULT NULL,
  `custom_station` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT 0.00,
  `start_date` date DEFAULT curdate(),
  `insurance_balance` decimal(10,2) DEFAULT 0.00,
  `password` varchar(255) DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `code`, `name`, `phone`, `balance`, `start_date`, `insurance_balance`, `password`, `synced`) VALUES
(33, '70', 'السماني شرف الدين', '0912378183', 3910000.00, '2025-05-29', 100000.00, '$2y$10$qAwKbdPZgSoEVgFAIJzy7eFbhTVyE2rrlHqd2ABT4cqEjYoSAhF76', 0),
(34, 'A-70', 'محمد عبدالله علي فرح', '9999999999', 0.00, '2025-05-29', 0.00, '$2y$10$OIrinpXUc0uVsHghimCV5ub1fE4xv5kI0m3Q/UazE2h4qi56cxD2i', 0),
(35, '35', 'محمد عبدالله علي', '02940824', 0.00, '2025-06-18', 0.00, '$2y$10$TkZgg7Qd4mzAgpLBG5ukl.gKNOzgw6Hq7fcFBYuXjoIkndv8D10i.', 1),
(36, 'A-10', 'ابن رشد', '9999999999', 0.00, '2025-06-18', 0.00, '$2y$10$ghPPzWKsiM7oaDXi1gWSU.QBKDAsKcsOFFo60HxGfk29Kqriufo9a', 1);

-- --------------------------------------------------------

--
-- Table structure for table `client_logins`
--

CREATE TABLE `client_logins` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `status` enum('مفعل','غير مفعل') DEFAULT 'مفعل',
  `last_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `containers`
--

CREATE TABLE `containers` (
  `id` int(11) NOT NULL,
  `entry_date` date NOT NULL,
  `code` varchar(50) NOT NULL,
  `client_name` varchar(255) NOT NULL,
  `loading_number` varchar(50) NOT NULL,
  `carton_count` int(11) NOT NULL,
  `container_number` varchar(100) NOT NULL,
  `bill_number` varchar(100) NOT NULL,
  `category` varchar(100) NOT NULL,
  `carrier` varchar(100) NOT NULL,
  `registry` int(11) DEFAULT NULL,
  `weight` varchar(50) DEFAULT NULL,
  `expected_arrival` date NOT NULL,
  `ship_name` varchar(100) NOT NULL,
  `custom_station` varchar(100) NOT NULL,
  `unloading_place` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `release_status` enum('Yes','No') NOT NULL DEFAULT 'No',
  `company_release` enum('Yes','No') NOT NULL DEFAULT 'No',
  `office` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `seen_by_port` tinyint(4) DEFAULT 0,
  `status` varchar(100) DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1,
  `bill_of_lading_status` enum('not_issued','issued','delayed') DEFAULT 'not_issued',
  `bill_of_lading_date` date DEFAULT NULL,
  `bill_of_lading_file` varchar(255) DEFAULT NULL,
  `tashitim_status` enum('not_done','done','delayed') DEFAULT 'not_done',
  `tashitim_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `containers`
--

INSERT INTO `containers` (`id`, `entry_date`, `code`, `client_name`, `loading_number`, `carton_count`, `container_number`, `bill_number`, `category`, `carrier`, `registry`, `weight`, `expected_arrival`, `ship_name`, `custom_station`, `unloading_place`, `notes`, `release_status`, `company_release`, `office`, `created_at`, `seen_by_port`, `status`, `register_id`, `synced`, `bill_of_lading_status`, `bill_of_lading_date`, `bill_of_lading_file`, `tashitim_status`, `tashitim_date`) VALUES
(241, '2025-07-01', '70', 'السماني شرف الدين', '4', 600, '99999', '0', 'SLIPPERS', 'PIL', 165, '600', '2025-10-29', 'Catlonia', 'القضارف', 'القضارف', '', 'No', 'No', 'بورتسودان', '2025-07-15 18:16:30', 0, NULL, NULL, 1, 'issued', '2025-07-15', NULL, 'not_done', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `container_operational_status`
--

CREATE TABLE `container_operational_status` (
  `id` int(11) NOT NULL,
  `container_id` int(11) NOT NULL,
  `status` enum('Bill of Lading Issued','Customs Cleared') NOT NULL,
  `file_path` varchar(255) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `container_operational_status`
--

INSERT INTO `container_operational_status` (`id`, `container_id`, `status`, `file_path`, `date`, `created_at`) VALUES
(5, 241, 'Bill of Lading Issued', 'uploads/bills/68769c31e9526_logo_684c2266bc59f.png', NULL, '2025-07-15 21:21:37');

-- --------------------------------------------------------

--
-- Table structure for table `container_position_history`
--

CREATE TABLE `container_position_history` (
  `id` int(11) NOT NULL,
  `container_id` int(11) NOT NULL,
  `status` enum('Loaded','At Port','At Sea','Arrived','Transported by Land','Delivered','Empty Returned') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_expenses`
--

CREATE TABLE `daily_expenses` (
  `id` int(11) NOT NULL,
  `type` varchar(50) NOT NULL,
  `client_id` int(11) NOT NULL,
  `container_id` int(11) NOT NULL,
  `items_json` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `user_id` int(11) DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `documents`
--

CREATE TABLE `documents` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `related_type` enum('قبض','صرف','إجراء','حاوية','عام') DEFAULT 'عام',
  `related_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `expense_sources`
--

CREATE TABLE `expense_sources` (
  `id` int(11) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `gov_actions`
--

CREATE TABLE `gov_actions` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `amount` decimal(12,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `insurance_transactions`
--

CREATE TABLE `insurance_transactions` (
  `id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','refund','payment') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_issues`
--

CREATE TABLE `inventory_issues` (
  `id` int(11) NOT NULL,
  `lost_type` varchar(255) DEFAULT NULL,
  `lost_quantity` int(11) DEFAULT NULL,
  `lost_value` decimal(12,2) DEFAULT NULL,
  `damaged_type` varchar(255) DEFAULT NULL,
  `damaged_quantity` int(11) DEFAULT NULL,
  `damaged_value` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `inventory_issues`
--

INSERT INTO `inventory_issues` (`id`, `lost_type`, `lost_quantity`, `lost_value`, `damaged_type`, `damaged_quantity`, `damaged_value`, `created_at`) VALUES
(1, 'احذية', 20, 2000000.00, 'احذية', 30, 3000000.00, '2025-06-17 12:03:38');

-- --------------------------------------------------------

--
-- Table structure for table `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `attempt_time` datetime DEFAULT current_timestamp(),
  `success` tinyint(1) DEFAULT 0,
  `failure_reason` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  `used` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_expenses`
--

CREATE TABLE `purchase_expenses` (
  `id` int(11) NOT NULL,
  `loading_number` varchar(100) DEFAULT NULL,
  `client_code` varchar(50) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `container_number` varchar(100) DEFAULT NULL,
  `customs_amount` decimal(12,2) DEFAULT NULL,
  `customs_additional` decimal(12,2) DEFAULT NULL,
  `manifesto_amount` decimal(12,2) DEFAULT NULL,
  `manifesto_additional` decimal(12,2) DEFAULT NULL,
  `customs_profit` decimal(12,2) DEFAULT NULL,
  `ports_amount` decimal(12,2) DEFAULT NULL,
  `ports_additional` decimal(12,2) DEFAULT NULL,
  `permission_amount` decimal(12,2) DEFAULT NULL,
  `permission_additional` decimal(12,2) DEFAULT 0.00,
  `yard_amount` decimal(12,2) DEFAULT NULL,
  `yard_additional` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `registers`
--

CREATE TABLE `registers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `registers`
--

INSERT INTO `registers` (`id`, `name`, `created_at`, `synced`) VALUES
(165, 'ابابيل', '2025-06-12 14:46:02', 1),
(166, 'شركة نقل', '2025-06-12 14:47:17', 1);

-- --------------------------------------------------------

--
-- Table structure for table `register_requests`
--

CREATE TABLE `register_requests` (
  `id` int(11) NOT NULL,
  `register_id` int(11) DEFAULT NULL,
  `client_code` varchar(50) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `loading_number` varchar(100) DEFAULT NULL,
  `carton_count` int(11) DEFAULT NULL,
  `custom_station` varchar(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `container_number` varchar(100) DEFAULT NULL,
  `purchase_amount` decimal(12,2) DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `customs_amount` decimal(12,2) DEFAULT NULL,
  `claim_amount` decimal(12,2) DEFAULT NULL,
  `unloading_place` varchar(255) DEFAULT NULL,
  `carrier` varchar(255) DEFAULT NULL,
  `bill_number` varchar(100) DEFAULT NULL,
  `refund_value` varchar(100) DEFAULT NULL,
  `refund_type` enum('جزء من حاوية','حاوية كاملة') DEFAULT NULL,
  `manifesto_number` varchar(100) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `driver_phone` varchar(100) DEFAULT NULL,
  `transporter_name` varchar(255) DEFAULT NULL,
  `transport_fee` decimal(12,2) DEFAULT NULL,
  `commission` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `remember_tokens`
--

CREATE TABLE `remember_tokens` (
  `user_id` int(11) NOT NULL,
  `token` varchar(64) DEFAULT NULL,
  `expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `remember_tokens`
--

INSERT INTO `remember_tokens` (`user_id`, `token`, `expires`) VALUES
(8, 'eeb2f615eed1ee71dd1c3858ef0dbb467f32e12d586a978f661499d6ff12e25e', '2025-08-16 19:36:55');

-- --------------------------------------------------------

--
-- Table structure for table `sales_invoices`
--

CREATE TABLE `sales_invoices` (
  `id` int(11) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `buyer_name` varchar(255) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `carton_count` int(11) DEFAULT NULL,
  `invoice_value` decimal(12,2) DEFAULT NULL,
  `vat_value` decimal(12,2) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `timestamp` datetime DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `username`, `ip_address`, `event_type`, `timestamp`, `user_id`) VALUES
(1, 'admin', '172.68.234.187', 'successful_login', '2025-07-15 12:52:47', NULL),
(2, 'hmadakhan686@gmail.com', '172.68.234.11', 'failed_login', '2025-07-15 12:53:48', NULL),
(3, 'admin', '172.68.234.11', 'failed_login', '2025-07-15 12:53:58', NULL),
(4, 'admin', '172.68.234.11', 'failed_login', '2025-07-15 12:54:16', NULL),
(5, 'admin', '172.68.234.136', 'failed_login', '2025-07-15 13:03:50', NULL),
(6, 'admin', '162.158.22.223', 'successful_login', '2025-07-15 13:04:25', NULL),
(7, 'admin', '162.158.22.223', 'successful_login', '2025-07-15 13:04:51', NULL),
(8, 'admin', '172.68.234.10', 'successful_login', '2025-07-15 13:51:14', NULL),
(9, 'admin', '172.68.234.10', 'successful_login', '2025-07-15 13:51:28', NULL),
(10, 'admin', '172.70.108.136', 'successful_login', '2025-07-15 13:58:06', NULL),
(11, 'admin', '172.70.108.136', 'successful_login', '2025-07-15 13:58:44', NULL),
(12, 'admin', '172.71.183.147', 'successful_login', '2025-07-15 14:21:59', NULL),
(13, 'admin', '172.71.98.101', 'successful_login', '2025-07-15 17:19:59', NULL),
(14, 'admin', '172.71.99.180', 'successful_login', '2025-07-15 17:21:28', NULL),
(15, 'admin', '172.71.99.180', 'successful_login', '2025-07-15 17:22:04', NULL),
(16, 'admin', '172.70.108.136', 'successful_login', '2025-07-15 17:49:35', NULL),
(17, 'admin', '172.70.108.136', 'failed_login', '2025-07-15 17:49:50', NULL),
(18, 'admin', '172.70.108.136', 'failed_login', '2025-07-15 17:50:02', NULL),
(19, 'rrrr', '172.70.108.136', 'failed_login', '2025-07-15 17:50:09', NULL),
(20, '4444', '172.70.108.136', 'failed_login', '2025-07-15 17:50:16', NULL),
(21, 'admin', '172.70.108.136', 'failed_login', '2025-07-15 17:50:26', NULL),
(22, 'admin', '172.71.183.49', 'successful_login', '2025-07-15 20:42:02', NULL),
(23, 'admin', '104.23.170.67', 'successful_login', '2025-07-15 20:54:25', NULL),
(24, 'admin', '172.71.102.241', 'successful_login', '2025-07-16 09:25:52', NULL),
(25, 'admin', '162.158.158.161', 'successful_login', '2025-07-17 09:49:01', NULL),
(26, 'admin', '104.23.187.80', 'successful_login', '2025-07-17 14:36:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `exchange_rate` decimal(10,2) NOT NULL,
  `synced` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `exchange_rate`, `synced`) VALUES
(1541, 3000.00, 1),
(1542, 2500.00, 1),
(1543, 1.00, 1),
(1544, 2650.00, 1),
(1545, 2650.00, 1),
(1546, 30000.00, 1),
(1547, 3000.00, 1),
(1548, 2000.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `updated_at`) VALUES
(1, 'max_login_attempts', '5', 'Maximum login attempts before lockout', '2025-07-15 12:41:00'),
(2, 'lockout_duration', '300', 'Lockout duration in seconds', '2025-07-15 12:41:00'),
(3, 'session_timeout', '1800', 'Session timeout in seconds', '2025-07-15 12:41:00'),
(4, 'remember_token_duration', '2592000', 'Remember token duration in seconds', '2025-07-15 12:41:00'),
(5, 'password_min_length', '8', 'Minimum password length', '2025-07-15 12:41:00'),
(6, 'require_strong_passwords', '1', 'Require strong passwords (1=yes, 0=no)', '2025-07-15 12:41:00'),
(7, 'enable_2fa', '0', 'Enable two-factor authentication (1=yes, 0=no)', '2025-07-15 12:41:00'),
(8, 'log_retention_days', '90', 'Security log retention period in days', '2025-07-15 12:41:00');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `type` enum('قبض','مطالبة') DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `method` varchar(20) DEFAULT NULL,
  `amount_usd` decimal(10,2) DEFAULT NULL,
  `exchange_rate` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(100) DEFAULT NULL,
  `reference_number` varchar(255) DEFAULT NULL,
  `receipt_image` varchar(255) DEFAULT NULL,
  `proof` varchar(255) DEFAULT NULL,
  `serial` varchar(50) DEFAULT NULL,
  `container_id` int(11) DEFAULT NULL,
  `register_id` int(11) DEFAULT NULL,
  `actual_cost` double DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1,
  `status` enum('open','partial','paid') NOT NULL DEFAULT 'open',
  `paid_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `related_claim_id` int(11) DEFAULT NULL,
  `approval_status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `client_id`, `type`, `amount`, `description`, `created_at`, `method`, `amount_usd`, `exchange_rate`, `payment_method`, `reference_number`, `receipt_image`, `proof`, `serial`, `container_id`, `register_id`, `actual_cost`, `synced`, `status`, `paid_amount`, `related_claim_id`, `approval_status`) VALUES
(1586, 33, 'مطالبة', 1000000.00, 'موانئ', '2025-07-12 10:46:47', NULL, NULL, 2000.00, 'بنك', '', NULL, NULL, '20250712-1586', 240, 0, NULL, 1, 'paid', 1060000.00, 0, 'pending'),
(1587, 33, 'قبض', 500000.00, '', '2025-07-12 11:16:24', NULL, NULL, NULL, 'بنكك', '', 'uploads/receipts/1752318984_1000168651.jpg', NULL, '20250712-68724408d4fb9', NULL, NULL, NULL, 1, 'open', 0.00, 1586, 'approved'),
(1588, 33, 'قبض', 10000.00, '', '2025-07-12 11:17:05', NULL, NULL, NULL, 'بنكك', '', 'uploads/receipts/1752319025_1000168405.jpg', NULL, '20250712-6872443145ec3', NULL, NULL, NULL, 1, 'open', 0.00, 1586, 'approved'),
(1589, 33, 'قبض', 50000.00, '', '2025-07-12 11:18:14', NULL, NULL, NULL, 'بنكك', '', 'uploads/receipts/1752319094_1000168405.jpg', NULL, '20250712-68724476a1d74', NULL, NULL, NULL, 1, 'open', 0.00, 1586, 'approved'),
(1590, 33, 'قبض', 500000.00, '', '2025-07-12 11:24:46', NULL, NULL, 2000.00, 'بنك', '', NULL, NULL, '20250712-1590', 0, 0, NULL, 1, 'open', 0.00, 1586, 'approved');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('مدير عام','محاسب','مدير مكتب') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `status` enum('مفعل','غير مفعل') DEFAULT 'مفعل',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `office` enum('بورتسودان','عطبرة','الصين') DEFAULT 'بورتسودان',
  `last_sync` timestamp NULL DEFAULT NULL,
  `synced` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `failed_login_attempts` int(11) DEFAULT 0,
  `locked_until` datetime DEFAULT NULL,
  `password_changed_at` datetime DEFAULT current_timestamp(),
  `two_factor_enabled` tinyint(1) DEFAULT 0,
  `two_factor_secret` varchar(32) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password`, `role`, `email`, `status`, `created_at`, `office`, `last_sync`, `synced`, `last_login`, `failed_login_attempts`, `locked_until`, `password_changed_at`, `two_factor_enabled`, `two_factor_secret`) VALUES
(8, '', 'admin', '$2y$10$TkZgg7Qd4mzAgpLBG5ukl.gKNOzgw6Hq7fcFBYuXjoIkndv8D10i.', 'مدير عام', NULL, 'مفعل', '2025-05-28 14:21:20', 'بورتسودان', NULL, 1, NULL, 0, NULL, '2025-07-15 12:41:00', 0, NULL),
(10, 'محمد ابوالقاسم', 'مابوالقاسم', '$2y$10$w.EOfGlpxQA8D7qscagS9e6hiDgc8NVMhCknnU3OzpgiO0.dTgPO6', 'محاسب', '', 'غير مفعل', '2025-07-12 10:38:56', 'عطبرة', NULL, 1, NULL, 0, NULL, '2025-07-15 12:41:00', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `session_id` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `data` text DEFAULT NULL,
  `last_activity` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashbox`
--
ALTER TABLE `cashbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `created_at` (`created_at`),
  ADD KEY `idx_cashbox_created_at` (`created_at`);

--
-- Indexes for table `china_containers`
--
ALTER TABLE `china_containers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loading_number` (`loading_number`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

--
-- Indexes for table `client_logins`
--
ALTER TABLE `client_logins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `containers`
--
ALTER TABLE `containers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `loading_number` (`loading_number`),
  ADD UNIQUE KEY `container_number` (`container_number`),
  ADD KEY `entry_date` (`entry_date`),
  ADD KEY `idx_containers_entry_date` (`entry_date`);

--
-- Indexes for table `container_operational_status`
--
ALTER TABLE `container_operational_status`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `container_id_status` (`container_id`,`status`);

--
-- Indexes for table `container_position_history`
--
ALTER TABLE `container_position_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `container_id` (`container_id`);

--
-- Indexes for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `documents`
--
ALTER TABLE `documents`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `expense_sources`
--
ALTER TABLE `expense_sources`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `description` (`description`);

--
-- Indexes for table `gov_actions`
--
ALTER TABLE `gov_actions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insurance_transactions`
--
ALTER TABLE `insurance_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `inventory_issues`
--
ALTER TABLE `inventory_issues`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_attempt_time` (`attempt_time`),
  ADD KEY `idx_success` (`success`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires` (`expires`);

--
-- Indexes for table `purchase_expenses`
--
ALTER TABLE `purchase_expenses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `registers`
--
ALTER TABLE `registers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `register_requests`
--
ALTER TABLE `register_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `serial` (`serial`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `client_id_2` (`client_id`,`created_at`),
  ADD KEY `idx_transactions_created_at` (`created_at`),
  ADD KEY `idx_transactions_client` (`client_id`),
  ADD KEY `idx_transactions_type` (`type`),
  ADD KEY `idx_transactions_created` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username_status` (`username`,`status`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_last_activity` (`last_activity`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cashbox`
--
ALTER TABLE `cashbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7878;

--
-- AUTO_INCREMENT for table `china_containers`
--
ALTER TABLE `china_containers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `client_logins`
--
ALTER TABLE `client_logins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `containers`
--
ALTER TABLE `containers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=242;

--
-- AUTO_INCREMENT for table `container_operational_status`
--
ALTER TABLE `container_operational_status`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `container_position_history`
--
ALTER TABLE `container_position_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `daily_expenses`
--
ALTER TABLE `daily_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=360;

--
-- AUTO_INCREMENT for table `documents`
--
ALTER TABLE `documents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `expense_sources`
--
ALTER TABLE `expense_sources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `gov_actions`
--
ALTER TABLE `gov_actions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `insurance_transactions`
--
ALTER TABLE `insurance_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_issues`
--
ALTER TABLE `inventory_issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_expenses`
--
ALTER TABLE `purchase_expenses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `registers`
--
ALTER TABLE `registers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `register_requests`
--
ALTER TABLE `register_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sales_invoices`
--
ALTER TABLE `sales_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1549;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1591;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cashbox`
--
ALTER TABLE `cashbox`
  ADD CONSTRAINT `cashbox_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `cashbox_ibfk_2` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);

--
-- Constraints for table `client_logins`
--
ALTER TABLE `client_logins`
  ADD CONSTRAINT `client_logins_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `container_operational_status`
--
ALTER TABLE `container_operational_status`
  ADD CONSTRAINT `container_operational_status_ibfk_1` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `container_position_history`
--
ALTER TABLE `container_position_history`
  ADD CONSTRAINT `container_position_history_ibfk_1` FOREIGN KEY (`container_id`) REFERENCES `containers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `insurance_transactions`
--
ALTER TABLE `insurance_transactions`
  ADD CONSTRAINT `insurance_transactions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Constraints for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD CONSTRAINT `password_reset_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `remember_tokens`
--
ALTER TABLE `remember_tokens`
  ADD CONSTRAINT `remember_tokens_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD CONSTRAINT `security_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `daily_security_cleanup` ON SCHEDULE EVERY 1 DAY STARTS '2025-07-15 13:01:37' ON COMPLETION NOT PRESERVE ENABLE DO CALL CleanupSecurityData()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
