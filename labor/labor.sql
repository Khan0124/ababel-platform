-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Aug 02, 2025 at 10:15 PM
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
-- Database: `labor`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `last_login`, `created_at`) VALUES
(1, 'Admin Master', 'admin@laborsaas.com', '$2y$10$8A5pJf4n5eb0VBlKvON20OayXKrtVfGUTJl5H144RZazcFXnMFwaS', '2025-08-02 17:12:17', '2025-06-13 11:26:49'),
(3, 'Test Admin', 'admin@test.com', '$2y$10$KjjyhbMSGwnNpVaiQXTRSOx7x.K/NXpDNWuhG7JzMgsxt2kiIjqw6', NULL, '2025-07-26 16:32:13'),
(4, 'Mohamed', 'hmadakhan686@gmail.com', '$2y$10$pxN/9n8H4my/SCFOUCb85O.IVbOeRTP.mbJ4i4GAeGehFb7jHMXl6', '2025-08-02 16:47:17', '2025-07-27 13:23:51');

-- --------------------------------------------------------

--
-- Table structure for table `cashbox`
--

CREATE TABLE `cashbox` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `transaction_date` datetime DEFAULT NULL,
  `type` enum('قبض','صرف') NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `source` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `method` varchar(50) DEFAULT 'كاش',
  `notes` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `cashbox`
--

INSERT INTO `cashbox` (`id`, `lab_id`, `transaction_date`, `type`, `description`, `source`, `amount`, `created_by`, `created_at`, `employee_id`, `method`, `notes`) VALUES
(1, 2, NULL, 'قبض', NULL, 'فحص مختبر', 2000.00, NULL, '2025-06-13 20:34:35', 5, 'كاش', NULL),
(2, 2, NULL, 'قبض', NULL, 'فحص مختبر', 2000.00, NULL, '2025-06-13 20:49:05', 5, 'كاش', NULL),
(3, 2, NULL, 'قبض', NULL, 'فحص مختبر', 1000.00, NULL, '2025-06-14 09:45:16', 5, 'كاش', NULL),
(4, 2, NULL, 'قبض', NULL, 'فحص مختبر', 5000.00, NULL, '2025-06-14 09:45:19', 5, 'كاش', NULL),
(5, 2, NULL, 'صرف', NULL, 'جوينت', 1000.00, NULL, '2025-06-14 13:40:32', 5, 'كاش', ''),
(6, 2, NULL, 'قبض', NULL, 'فحص مختبر', 1000.00, NULL, '2025-06-14 14:40:53', 5, 'كاش', NULL),
(7, 2, NULL, 'قبض', NULL, 'فحص مختبر', 5000.00, NULL, '2025-06-14 15:07:17', 5, 'كاش', NULL),
(8, 2, NULL, 'قبض', NULL, 'فحص مختبر', 1000.00, NULL, '2025-06-14 15:07:22', 5, 'كاش', NULL),
(9, 2, NULL, 'قبض', NULL, 'فحص مختبر', 5000.00, NULL, '2025-06-14 15:07:26', 5, 'كاش', NULL),
(10, 2, NULL, 'قبض', NULL, 'فحص مختبر', 1000.00, NULL, '2025-06-14 18:36:22', 5, 'كاش', NULL),
(11, 2, NULL, 'قبض', NULL, 'فحص مختبر', 2000.00, NULL, '2025-06-14 18:36:48', 5, 'كاش', NULL),
(12, 2, NULL, 'قبض', NULL, 'فحص مختبر', 1000.00, NULL, '2025-06-14 19:06:54', 5, 'كاش', NULL),
(13, 2, '2025-06-14 19:21:41', 'قبض', 'فاتورة فحوصات للمريض رقم 1', 'فحص مخبري', 1000.00, 5, '2025-06-14 20:21:41', NULL, 'كاش', NULL),
(14, 2, '2025-06-14 20:05:03', 'قبض', 'فاتورة فحوصات للمريض رقم 1', NULL, 2000.00, 5, '2025-06-14 21:05:03', NULL, 'كاش', NULL),
(15, 2, '2025-06-14 20:35:14', 'قبض', 'تحصيل فحص للمريض ID:2 (فاتورة 10)', NULL, 2000.00, 5, '2025-06-14 21:35:14', NULL, 'كاش', NULL),
(16, 2, '2025-06-14 20:42:46', 'قبض', 'تحصيل فحص للمريض ID:2 (فاتورة 11)', NULL, 3000.00, 5, '2025-06-14 21:42:46', NULL, 'كاش', NULL),
(17, 2, '2025-06-14 21:11:34', 'قبض', 'تحصيل فحص للمريض ID:1 (فاتورة 12)', NULL, 1500.00, 5, '2025-06-14 22:11:34', NULL, 'كاش', NULL),
(18, 2, '2025-06-14 21:29:36', 'قبض', 'تحصيل فحص للمريض ID:2 (فاتورة 15)', 'فحوصات', 5000.00, 5, '2025-06-14 21:29:36', NULL, 'كاش', 'فاتورة فحوص رقم 15'),
(19, 2, NULL, 'قبض', NULL, 'فحص مختبر', 5000.00, NULL, '2025-06-14 22:38:12', 5, 'كاش', NULL),
(20, 2, '2025-06-15 19:45:14', 'قبض', 'تحصيل فحص للمريض ID:1 (فاتورة 18)', 'فحوصات', 1500.00, 5, '2025-06-15 19:45:14', NULL, 'كاش', 'فاتورة فحوص رقم 18'),
(21, 2, '2025-06-15 19:45:51', 'قبض', 'تحصيل فحص للمريض ID:2 (فاتورة 19)', 'فحوصات', 4500.00, 5, '2025-06-15 19:45:51', NULL, 'كاش', 'فاتورة فحوص رقم 19'),
(22, 2, NULL, 'قبض', NULL, 'فحص مختبر', 5000.00, NULL, '2025-06-15 20:47:16', 5, 'كاش', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `email_queue`
--

CREATE TABLE `email_queue` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `subject` varchar(500) NOT NULL,
  `message` text NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_attendance`
--

CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_attendance`
--

INSERT INTO `employee_attendance` (`id`, `employee_id`, `shift_id`, `date`, `check_in`, `check_out`, `notes`, `created_at`) VALUES
(1, 5, 1, '2025-06-14', '2025-06-14 12:16:01', NULL, NULL, '2025-06-14 12:16:01');

-- --------------------------------------------------------

--
-- Table structure for table `employee_shifts`
--

CREATE TABLE `employee_shifts` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `employee_shifts`
--

INSERT INTO `employee_shifts` (`id`, `employee_id`, `shift_id`, `is_active`, `assigned_at`) VALUES
(2, 5, 1, 1, '2025-06-14 12:06:21');

-- --------------------------------------------------------

--
-- Table structure for table `exam_catalog`
--

CREATE TABLE `exam_catalog` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `code_exam` varchar(50) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `normal_range` varchar(100) DEFAULT NULL,
  `sample_type` varchar(100) DEFAULT NULL,
  `delivery_time` varchar(50) DEFAULT NULL,
  `description` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_optional` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_catalog`
--

INSERT INTO `exam_catalog` (`id`, `lab_id`, `name`, `name_en`, `code_exam`, `category`, `price`, `unit`, `normal_range`, `sample_type`, `delivery_time`, `description`, `created_at`, `is_active`, `is_optional`, `category_id`) VALUES
(1, 2, 'سكري', 'sudar', 'su', NULL, 2000.00, '0', '1000', NULL, NULL, 'تت', '2025-06-13 18:04:19', 0, 0, NULL),
(3, 2, 'سكري', 'sudar', '1su', 'SLIPPERS', 1000.00, '0', '1000', 'تت', '20', '', '2025-06-13 18:09:41', 0, 0, NULL),
(4, 2, 'سكري', 'sudar', 'su2', 'SLIPPERS', 1000.00, '0', '1000', 'تت', '20', '', '2025-06-13 18:14:23', 0, 0, NULL),
(5, 2, 'ضغط', 'ta3', 'ta', '', 5000.00, '0', '1000', 'تت', '', '', '2025-06-14 09:40:59', 1, 0, NULL),
(7, 2, 'دم', 'CBC', 'cbc', 'SLIPPERS', 1000.00, '0', '1000', 'تت', '20', '', '2025-06-14 11:25:25', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `exam_categories`
--

CREATE TABLE `exam_categories` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `exam_components`
--

CREATE TABLE `exam_components` (
  `id` int(11) NOT NULL,
  `exam_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_needed` int(11) DEFAULT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_components`
--

INSERT INTO `exam_components` (`id`, `exam_id`, `item_id`, `quantity_needed`, `is_optional`) VALUES
(1, 7, 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `exam_invoices`
--

CREATE TABLE `exam_invoices` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `notes` mediumtext DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `discount` decimal(10,2) DEFAULT 0.00,
  `insurance_company_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `exam_invoices`
--

INSERT INTO `exam_invoices` (`id`, `lab_id`, `patient_id`, `invoice_date`, `total_amount`, `referred_by`, `notes`, `created_by`, `created_at`, `discount`, `insurance_company_id`) VALUES
(1, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:14:59', 0.00, NULL),
(2, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:15:23', 0.00, NULL),
(3, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:15:56', 0.00, NULL),
(4, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:16:22', 0.00, NULL),
(5, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:16:54', 0.00, NULL),
(6, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:20:26', 0.00, NULL),
(7, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:21:01', 0.00, NULL),
(8, 2, 1, '2025-06-14', 1000.00, 'دكتور خالد', '', 5, '2025-06-14 19:21:41', 0.00, NULL),
(9, 2, 1, '2025-06-14', 2000.00, 'دكتور خالد', '', 5, '2025-06-14 20:05:03', 0.00, NULL),
(10, 2, 2, '2025-06-14', 2000.00, 'دكتور خالد', '', 5, '2025-06-14 20:35:14', 0.00, NULL),
(11, 2, 2, '2025-06-14', 3000.00, 'دكتور خالد', '', 5, '2025-06-14 20:42:46', 0.00, NULL),
(12, 2, 1, '2025-06-14', 2000.00, 'دكتور خالد', '', 5, '2025-06-14 21:11:34', 500.00, 1),
(13, 2, 2, '2025-06-14', 2000.00, 'دكتور خالد', '', 5, '2025-06-14 21:22:21', 0.00, 1),
(14, 2, 2, '2025-06-14', 5000.00, 'دكتور خالد', '', 5, '2025-06-14 21:28:28', 0.00, 1),
(15, 2, 2, '2025-06-14', 5000.00, 'دكتور خالد', '', 5, '2025-06-14 21:29:36', 0.00, 1),
(16, 2, 1, '2025-06-15', 2000.00, 'دكتور خالد', '', 5, '2025-06-15 19:40:06', 500.00, 1),
(17, 2, 1, '2025-06-15', 2000.00, 'دكتور خالد', '', 5, '2025-06-15 19:42:56', 500.00, 1),
(18, 2, 1, '2025-06-15', 2000.00, 'دكتور خالد', '', 5, '2025-06-15 19:45:14', 500.00, 1),
(19, 2, 2, '2025-06-15', 5000.00, 'دكتور خالد', '', 5, '2025-06-15 19:45:51', 500.00, 1);

-- --------------------------------------------------------

--
-- Table structure for table `insurance_companies`
--

CREATE TABLE `insurance_companies` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `insurance_companies`
--

INSERT INTO `insurance_companies` (`id`, `lab_id`, `name`, `created_at`, `is_active`) VALUES
(1, 2, 'النيلين للتأمين', '2025-06-14 21:52:46', 1);

-- --------------------------------------------------------

--
-- Table structure for table `labs`
--

CREATE TABLE `labs` (
  `id` int(11) NOT NULL,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `phone_secondary` varchar(20) DEFAULT NULL,
  `address` mediumtext DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `map_link` mediumtext DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `subscription_type` varchar(50) DEFAULT 'basic',
  `subscription_end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `labs`
--

INSERT INTO `labs` (`id`, `name`, `email`, `password`, `phone`, `phone_secondary`, `address`, `logo`, `map_link`, `status`, `subscription_type`, `subscription_end_date`, `created_at`, `updated_at`) VALUES
(2, 'معمل الشفاء', 'hmadakhan686@gmail.com', NULL, '0910564187', '', 'Portsudan', 'logo_684c2266bc59f.png', '', 'active', 'basic', NULL, '2025-06-13 12:26:17', '2025-08-01 20:17:37');

-- --------------------------------------------------------

--
-- Table structure for table `lab_employees`
--

CREATE TABLE `lab_employees` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('مدير','طبيب','محضر','محاسب') DEFAULT 'طبيب',
  `status` enum('نشط','معطل') DEFAULT 'نشط',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `password_changed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lab_employees`
--

INSERT INTO `lab_employees` (`id`, `lab_id`, `name`, `email`, `username`, `password`, `role`, `status`, `is_active`, `last_login`, `password_changed_at`) VALUES
(5, 2, 'طبيب', 'manager@lab.com', 'manager@lab.com', '$2y$10$/yqyNwb5FJhZOoYhbaX92.ZIMKcAl2kO4h6S2mXhfV/eBa/ssGy.e', 'مدير', 'نشط', 1, '2025-08-02 14:11:35', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(11) NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL,
  `executed_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('admin','lab_employee') NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','success','warning','danger') DEFAULT 'info',
  `action_url` varchar(500) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `user_type`, `lab_id`, `title`, `message`, `type`, `action_url`, `is_read`, `read_at`, `created_at`) VALUES
(1, 1, 'admin', NULL, 'مرحباً بك في النظام المحدث', 'تم تحديث نظام إدارة المختبرات بنجاح مع مزايا جديدة ومحسنة', 'success', NULL, 0, NULL, '2025-07-31 17:25:49'),
(2, 1, 'admin', NULL, 'تحديث الأمان', 'تم تفعيل نظام الأمان المتقدم مع تشفير البيانات', 'info', NULL, 0, NULL, '2025-07-31 17:25:49'),
(3, 1, 'admin', NULL, 'نظام الإشعارات', 'تم إضافة نظام إشعارات متقدم للمتابعة الفورية', 'success', NULL, 0, NULL, '2025-07-31 17:25:49');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('ذكر','أنثى') DEFAULT NULL,
  `age_value` int(11) DEFAULT NULL,
  `age_unit` enum('يوم','أسبوع','شهر','سنة') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `history` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `lab_id`, `code`, `name`, `gender`, `age_value`, `age_unit`, `phone`, `address`, `history`, `created_at`) VALUES
(1, 2, 'P-11847', 'محمد عبدالله علي فرح ', 'ذكر', 29, 'سنة', '0910564187', 'Portsudan', '', '2025-06-13 15:04:02'),
(2, 2, 'P-19956', 'عبدالعظييم', 'ذكر', 30, 'سنة', '0910564187', 'Portsudan', '', '2025-06-14 21:20:18');

-- --------------------------------------------------------

--
-- Table structure for table `patient_exams`
--

CREATE TABLE `patient_exams` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `exam_id` int(11) NOT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `referred_by` varchar(100) DEFAULT NULL,
  `exam_date` date DEFAULT NULL,
  `value` varchar(100) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'قيد الإجراء',
  `created_by` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `comment` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `patient_exams`
--

INSERT INTO `patient_exams` (`id`, `lab_id`, `patient_id`, `exam_id`, `invoice_id`, `referred_by`, `exam_date`, `value`, `status`, `created_by`, `employee_id`, `created_at`, `comment`) VALUES
(1, 2, 1, 1, NULL, '', '2025-06-13', '900', 'تم التسليم', NULL, 5, '2025-06-13 19:20:28', ''),
(2, 2, 1, 1, NULL, '', '2025-06-13', NULL, 'عمل استرداد', NULL, 5, '2025-06-13 20:49:39', NULL),
(3, 2, 1, 3, NULL, '', '2025-06-13', '900', 'تم التسليم', NULL, 5, '2025-06-13 20:49:39', 'good'),
(4, 2, 1, 4, NULL, '', '2025-06-13', '900', 'تم التسليم', NULL, 5, '2025-06-13 20:49:39', ''),
(5, 2, 1, 5, NULL, 'دكتور خالد', '2025-06-14', '900', 'تم التسليم', NULL, 5, '2025-06-14 09:43:42', ''),
(6, 2, 1, 7, NULL, 'دكتور خالد', '2025-06-14', '900', 'تم التسليم', NULL, 5, '2025-06-14 11:26:38', ''),
(7, 2, 1, 5, NULL, 'دكتور خالد', '2025-06-14', '900', 'تم التسليم', NULL, 5, '2025-06-14 11:28:30', ''),
(8, 2, 1, 7, NULL, 'دكتور خالد', '2025-06-14', '900', 'تم التسليم', NULL, 5, '2025-06-14 11:28:51', ''),
(9, 2, 1, 5, NULL, 'دكتور خالد', '2025-06-14', NULL, 'قيد الإجراء', NULL, 5, '2025-06-14 18:58:32', NULL),
(10, 2, 1, 7, NULL, 'دكتور خالد', '2025-06-14', NULL, 'قيد الإجراء', NULL, 5, '2025-06-14 18:58:32', NULL),
(11, 2, 1, 7, NULL, 'دكتور خالد', '2025-06-14', NULL, 'قيد الإجراء', NULL, 5, '2025-06-14 20:09:54', NULL),
(12, 2, 1, 7, 3, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:15:56', NULL),
(13, 2, 1, 7, 4, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:16:22', NULL),
(14, 2, 1, 7, 5, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:16:54', NULL),
(15, 2, 1, 7, 6, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:20:26', NULL),
(16, 2, 1, 7, 7, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:21:01', NULL),
(17, 2, 1, 7, 8, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 20:21:41', NULL),
(18, 2, 1, 7, NULL, 'دكتور خالد', '2025-06-14', NULL, 'قيد الإجراء', NULL, 5, '2025-06-14 21:04:29', NULL),
(19, 2, 1, 1, 9, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 21:05:03', NULL),
(20, 2, 2, 1, 10, NULL, '2025-06-14', '900', 'تم الاستخراج', 5, 5, '2025-06-14 21:35:14', ''),
(21, 2, 2, 3, 11, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 21:42:46', NULL),
(22, 2, 2, 1, 11, NULL, '2025-06-14', '900', 'تم الاستخراج', 5, 5, '2025-06-14 21:42:46', ''),
(23, 2, 1, 1, 12, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 22:11:34', NULL),
(24, 2, 1, 1, 12, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 22:11:34', NULL),
(25, 2, 2, 1, 13, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 22:22:21', NULL),
(26, 2, 2, 5, 14, NULL, '2025-06-14', NULL, 'قيد الإجراء', 5, NULL, '2025-06-14 22:28:28', NULL),
(27, 2, 2, 5, 15, NULL, '2025-06-14', '900', 'تم التسليم', 5, 5, '2025-06-14 22:29:36', ''),
(28, 2, 1, 1, 16, NULL, '2025-06-15', NULL, 'قيد الإجراء', 5, NULL, '2025-06-15 20:40:06', NULL),
(29, 2, 1, 1, 17, NULL, '2025-06-15', NULL, 'قيد الإجراء', 5, NULL, '2025-06-15 20:42:56', NULL),
(30, 2, 1, 1, 18, NULL, '2025-06-15', NULL, 'قيد الإجراء', 5, NULL, '2025-06-15 20:45:14', NULL),
(31, 2, 2, 5, 19, NULL, '2025-06-15', '900', 'تم التسليم', 5, 5, '2025-06-15 20:45:51', '');

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `lab_id`, `name`, `created_at`) VALUES
(1, 2, 'دكتور خالد', '2025-06-14 19:14:30');

-- --------------------------------------------------------

--
-- Table structure for table `security_logs`
--

CREATE TABLE `security_logs` (
  `id` int(11) NOT NULL,
  `event_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `security_logs`
--

INSERT INTO `security_logs` (`id`, `event_type`, `description`, `ip_address`, `user_agent`, `user_id`, `created_at`) VALUES
(1, 'unauthorized_access', 'Invalid employee session', '172.70.108.221', NULL, NULL, '2025-07-31 18:11:43'),
(2, 'successful_login', 'Admin login successful', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:30:50'),
(3, 'unauthorized_access', 'Invalid admin session', '104.23.172.56', NULL, NULL, '2025-07-31 18:30:50'),
(4, 'logout', 'User logged out', '104.23.172.56', NULL, NULL, '2025-07-31 18:30:50'),
(5, 'failed_login', 'Invalid credentials for: hmadakhan686@gmail.com', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:31:12'),
(6, 'failed_login', 'Invalid credentials for: hmadakhan686@gmail.com', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:31:19'),
(7, 'successful_login', 'Admin login successful', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:31:38'),
(8, 'unauthorized_access', 'Invalid admin session', '104.23.172.56', NULL, NULL, '2025-07-31 18:31:38'),
(9, 'logout', 'User logged out', '104.23.172.56', NULL, NULL, '2025-07-31 18:31:38'),
(10, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.71.98.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:34:59'),
(11, 'successful_login', 'Admin login successful', '172.71.98.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 18:35:25'),
(12, 'unauthorized_access', 'Invalid admin session', '104.23.168.42', NULL, NULL, '2025-07-31 18:35:38'),
(13, 'logout', 'User logged out', '104.23.168.42', NULL, NULL, '2025-07-31 18:35:38'),
(14, 'unauthorized_access', 'Invalid employee session', '172.70.47.150', NULL, NULL, '2025-07-31 19:50:23'),
(15, 'successful_login', 'Admin login successful', '172.70.47.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-07-31 19:50:52'),
(16, 'unauthorized_access', 'Invalid admin session', '172.70.47.150', NULL, NULL, '2025-07-31 19:50:52'),
(17, 'logout', 'User logged out', '172.70.47.150', NULL, NULL, '2025-07-31 19:50:52'),
(18, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.71.170.154', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:36:21'),
(19, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.71.191.101', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:36:32'),
(20, 'failed_login', 'Invalid credentials for: manager@lab.com', '198.41.227.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:51:31'),
(21, 'failed_login', 'Invalid credentials for: manger@lab.com', '198.41.227.172', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:51:40'),
(22, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.70.94.175', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:52:37'),
(23, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.70.94.175', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 22:52:44'),
(24, 'successful_login', 'Admin login successful', '104.23.201.197', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 23:01:04'),
(25, 'unauthorized_access', 'Invalid admin session', '104.23.201.197', NULL, NULL, '2025-08-01 23:01:04'),
(26, 'logout', 'User logged out', '104.23.201.197', NULL, NULL, '2025-08-01 23:01:04'),
(27, 'unauthorized_access', 'Invalid employee session', '162.158.42.138', NULL, NULL, '2025-08-01 23:21:37'),
(28, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.68.23.108', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 23:24:24'),
(29, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.68.23.108', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 23:24:30'),
(30, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.68.23.108', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-01 23:24:37'),
(31, 'unauthorized_access', 'Invalid employee session', '172.70.223.137', NULL, NULL, '2025-08-02 16:37:57'),
(32, 'failed_login', 'Invalid credentials for: hmadakhan686@gmail.com', '172.70.223.137', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:38:04'),
(33, 'failed_login', 'Invalid credentials for: hmadakhan686@gmail.com', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:46:15'),
(34, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:46:24'),
(35, 'failed_login', 'Invalid credentials for: manager@lab.com', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:46:52'),
(36, 'successful_login', 'Admin login successful', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:47:17'),
(37, 'unauthorized_access', 'Invalid admin session', '172.70.234.4', NULL, NULL, '2025-08-02 16:47:18'),
(38, 'logout', 'User logged out', '172.70.234.4', NULL, NULL, '2025-08-02 16:47:18'),
(39, 'failed_login', 'Invalid credentials for: hmadakhan686@gmail.com', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:47:24'),
(40, 'successful_login', 'Employee login successful', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:51:29'),
(41, 'unauthorized_access', 'Invalid employee session', '172.71.242.138', NULL, NULL, '2025-08-02 16:51:29'),
(42, 'logout', 'User logged out', '172.71.242.138', NULL, NULL, '2025-08-02 16:51:29'),
(43, 'failed_login', 'Invalid credentials for: manger@lab.com', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:51:39'),
(44, 'successful_login', 'Employee login successful', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:51:46'),
(45, 'unauthorized_access', 'Invalid employee session', '172.71.242.138', NULL, NULL, '2025-08-02 16:51:47'),
(46, 'logout', 'User logged out', '172.71.242.138', NULL, NULL, '2025-08-02 16:51:47'),
(47, 'successful_login', 'Admin login successful', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:52:09'),
(48, 'unauthorized_access', 'Invalid admin session', '172.71.242.138', NULL, NULL, '2025-08-02 16:52:10'),
(49, 'logout', 'User logged out', '172.71.242.138', NULL, NULL, '2025-08-02 16:52:10'),
(50, 'successful_login', 'Admin login successful', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:52:22'),
(51, 'unauthorized_access', 'Invalid admin session', '172.71.242.138', NULL, NULL, '2025-08-02 16:52:23'),
(52, 'logout', 'User logged out', '172.71.242.138', NULL, NULL, '2025-08-02 16:52:23'),
(53, 'successful_login', 'Employee login successful', '172.71.100.194', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:53:35'),
(54, 'unauthorized_access', 'Invalid employee session', '172.71.100.194', NULL, NULL, '2025-08-02 16:53:35'),
(55, 'logout', 'User logged out', '172.71.100.194', NULL, NULL, '2025-08-02 16:53:35'),
(56, 'successful_login', 'Admin login successful', NULL, '', NULL, '2025-08-02 16:55:43'),
(57, 'successful_login', 'Employee login successful', NULL, '', NULL, '2025-08-02 16:55:43'),
(58, 'successful_login', 'Employee login successful', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:56:38'),
(59, 'unauthorized_access', 'Invalid employee session', '162.158.193.113', NULL, NULL, '2025-08-02 16:57:11'),
(60, 'logout', 'User logged out', '162.158.193.113', NULL, NULL, '2025-08-02 16:57:11'),
(61, 'successful_login', 'Employee login successful', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:57:26'),
(62, 'unauthorized_access', 'Invalid employee session', '162.158.193.113', NULL, NULL, '2025-08-02 16:57:29'),
(63, 'logout', 'User logged out', '162.158.193.113', NULL, NULL, '2025-08-02 16:57:29'),
(64, 'successful_login', 'Employee login successful', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 16:57:43'),
(65, 'unauthorized_access', 'Invalid employee session', '172.69.169.189', NULL, NULL, '2025-08-02 16:58:30'),
(66, 'logout', 'User logged out', '172.69.169.189', NULL, NULL, '2025-08-02 16:58:30'),
(67, 'successful_login', 'Admin login successful', NULL, '', NULL, '2025-08-02 17:03:38'),
(68, 'successful_login', 'Employee login successful', NULL, '', NULL, '2025-08-02 17:03:39'),
(69, 'successful_login', 'Employee login successful', '172.71.100.203', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 17:11:35'),
(70, 'logout', 'User logged out', '172.71.100.203', NULL, NULL, '2025-08-02 17:12:02'),
(71, 'successful_login', 'Admin login successful', '172.71.100.203', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', NULL, '2025-08-02 17:12:17'),
(72, 'logout', 'User logged out', '172.71.100.203', NULL, NULL, '2025-08-02 17:13:12');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `days` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`id`, `lab_id`, `name`, `start_time`, `end_time`, `days`, `created_at`) VALUES
(1, 2, 'صباحية', '08:00:00', '15:00:00', 'السبت-الخميس', '2025-06-14 12:02:14');

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `type` enum('مستهلك','دائم') DEFAULT 'مستهلك',
  `min_quantity` float DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_items`
--

INSERT INTO `stock_items` (`id`, `lab_id`, `name`, `quantity`, `unit`, `expiry_date`, `created_at`, `type`, `min_quantity`) VALUES
(1, 2, 'جلكوز', 1500, 'مل', '2026-06-14', '2025-06-14 09:13:31', 'مستهلك', 100);

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `stock_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `movement_type` enum('إدخال','إخراج') DEFAULT NULL,
  `quantity` float DEFAULT NULL,
  `reason` mediumtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_movements`
--

INSERT INTO `stock_movements` (`id`, `stock_id`, `lab_id`, `movement_type`, `quantity`, `reason`, `created_at`, `employee_id`, `item_id`) VALUES
(1, 1, 2, 'إدخال', 100, 'شراء', '2025-06-14 09:28:17', NULL, 0),
(2, 1, 2, 'إخراج', 100, 'تالف', '2025-06-14 09:37:46', NULL, 0),
(3, NULL, 2, '', 1, 'خصم نتيجة للفحص رقم 6', '2025-06-14 11:27:53', NULL, 1),
(4, NULL, 2, '', 1, 'خصم نتيجة للفحص رقم 8', '2025-06-14 11:29:16', NULL, 1),
(5, 1, 2, 'إدخال', 3, 'شراء', '2025-06-14 11:42:40', NULL, 0),
(6, 1, 2, 'إخراج', 1, 'تالف', '2025-06-14 12:01:08', NULL, 0),
(7, 1, 2, 'إدخال', 100, 'شراء', '2025-06-14 12:03:58', NULL, 0),
(8, 1, 2, 'إدخال', 500, 'شراء', '2025-06-15 20:51:20', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `plan` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','trial') DEFAULT 'trial',
  `notes` mediumtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` mediumtext DEFAULT NULL,
  `response` mediumtext DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `replied_at` datetime DEFAULT NULL,
  `seen_by_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('admin','lab_employee') NOT NULL,
  `session_token` varchar(128) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_activity` datetime DEFAULT current_timestamp(),
  `ended_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `user_type`, `session_token`, `ip_address`, `user_agent`, `created_at`, `last_activity`, `ended_at`, `is_active`) VALUES
(1, 4, 'admin', '77651f49aaa5b25b86b1aab8fdbb292bbf67adb32108d57ad7a48aa1bfa991f6d4ad2ee59f87f766d972525d89ec08e6a33d15bdebd895c2af2e2fe4b4d47ef2', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 18:30:50', '2025-07-31 18:30:50', '2025-08-02 16:47:18', 0),
(2, 4, 'admin', '233d744dfb3495d69bf8068de8763e5bae86b8ba1bae94cc4c1e4270be07256dc15c83ef73c86cf43959a8a6222a5a8a4a2ccf62dd98460124f2f6ebf267fc4d', '104.23.172.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 18:31:38', '2025-07-31 18:31:38', '2025-08-02 16:47:18', 0),
(3, 4, 'admin', '8bdd80705de79666b262448e4b887e59427b5fd9c50b168bd77635ef117496efbab33e4b15e4c31e174a04b67c1c922a0fda11e38e565c2b53789a4bd2a59147', '172.71.98.147', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 18:35:25', '2025-07-31 18:35:25', '2025-08-02 16:47:18', 0),
(4, 4, 'admin', '5b63fd2795ee0c25791ed5bd97bc82f97db55009f87061b028565958d4c8b7ffb456a280896c071e5eee8d57ee369cc70cc33ed0b5a73f4a43dea0fa4fbe0345', '172.70.47.150', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-07-31 19:50:52', '2025-07-31 19:50:52', '2025-08-02 16:47:18', 0),
(5, 4, 'admin', 'b79f3d9f5ecfe0c352cf4883bd2ed416d941fc29d5269cf31cd0e022c528fac44e147822a2b453df285faf6928a97025a62901ff505279e191471802560c9552', '104.23.201.197', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-01 23:01:04', '2025-08-01 23:01:04', '2025-08-02 16:47:18', 0),
(6, 4, 'admin', '789a3c0b79ac415491f9413bf49dc352e15b498bdb6bbec5cbad8c986e6eadbd22e1bfb0ace3aa45ad39b30c358c1d1049ac6b46ab58485c35780f9f8b6723c3', '172.70.234.4', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:47:17', '2025-08-02 16:47:17', '2025-08-02 16:47:18', 0),
(7, 5, 'lab_employee', 'a05d58054dadfe24bcdcc216396ba58658e6137224779755de8983720ef2905db9a8d18939401136bdb860868b1fa75d306052d8d0bd7b8b05e52555a49dc800', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:51:29', '2025-08-02 16:51:29', '2025-08-02 17:12:02', 0),
(8, 5, 'lab_employee', '0548d5caeb693a89f08a132144bfe2fba97e5b6b6310d817311c7d39a14fc5287f877411ee44f95c93d8c9ae46e4ad6a26e57eae89ef1033fa6f7ccf8e74b4f8', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:51:46', '2025-08-02 16:51:46', '2025-08-02 17:12:02', 0),
(9, 1, 'admin', 'ffec2fdafa44fc8c28efd177417782985654e0894a64e856da878eff99d8c9030089046b4aec24ec673e8d27cec8799f57030f57eca02e90aef737ca77348245', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:52:09', '2025-08-02 16:52:09', '2025-08-02 17:13:12', 0),
(10, 1, 'admin', '3da689e15f62334187f7161a19526fe028fe6b0d31da368a4a8a1f39790156421e7933f53941ae8304b3f0c30e46d3d6a517a0405669ad2ffc7dfce3fdb7dd06', '172.71.242.138', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:52:22', '2025-08-02 16:52:22', '2025-08-02 17:13:12', 0),
(11, 5, 'lab_employee', 'bb5e5bc20cd313200a5fde23c7f68adc8a7a53c40c9084bf8f08f73eedbd97753c54fdebd81e3c018964361a14538eacaf65351edacbce06cc7713f4c9c24041', '172.71.100.194', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:53:35', '2025-08-02 16:53:35', '2025-08-02 17:12:02', 0),
(12, 1, 'admin', '4a8d6e70cce7c22c155b97377474732888186f5e92bc16df048b0007f23793e4c5ffe03fc365812eee39df8272797a76b1ffa3d2204ea5ba9ea954f6478871d0', NULL, '', '2025-08-02 16:55:43', '2025-08-02 16:55:43', '2025-08-02 17:13:12', 0),
(13, 5, 'lab_employee', '4083e9a88d746f64289a8b5013f5332b22e19f62564f5a498090ee1cac038097c9028159265995a70dd7cad48abd5d1b9d9ed6b80b585b2d2323a8fc3b29db6f', NULL, '', '2025-08-02 16:55:43', '2025-08-02 16:55:43', '2025-08-02 17:12:02', 0),
(14, 5, 'lab_employee', '6325bffec241e9d9b0684a62a2dbbe770de60f7793a03ccd955277c0a4a99dc2c4d985df0a25983df474e8a4b9d651814aab9d1eb2910864feb3f397e0843dcf', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:56:38', '2025-08-02 16:56:38', '2025-08-02 17:12:02', 0),
(15, 5, 'lab_employee', '9e3e2ef21d9315d66d5f8cd94eaa78d48c4078028bcddd37d71c6bb19191f41f5bf8e751398ddbe66b90db13d1a966ce3d896618d3d6f1b2e04dcb7b2b194ab6', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:57:26', '2025-08-02 16:57:26', '2025-08-02 17:12:02', 0),
(16, 5, 'lab_employee', '294ce7ac16f12ad043d34231ac49292344cd8adbf837f0fce52dd948017ea9f1b6da2bdc84a0ed2836cc79602e5f798c979d1442e620b48efd031a10df180457', '162.158.193.113', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 16:57:43', '2025-08-02 16:57:43', '2025-08-02 17:12:02', 0),
(17, 1, 'admin', '1ab01c8b33eaa1b8fa504249448b20a3415d2a0173fa61db19f06924828473aa235e97a2c310625e7ea52a2c1e427e48bb6b5036d0fd221fda07034e6f6ade6f', NULL, '', '2025-08-02 17:03:38', '2025-08-02 17:03:38', '2025-08-02 17:13:12', 0),
(18, 5, 'lab_employee', '818333b72b7cde9095dd61692569132fbb192db68d47ac3d20c7951cb02eaaf6df2ce1f138bcf44d0bf47ffefce21c022e69414292ddd07aa20bd0389d2ef2ef', NULL, '', '2025-08-02 17:03:39', '2025-08-02 17:03:39', '2025-08-02 17:12:02', 0),
(19, 5, 'lab_employee', '2c62bb4dacb2cad8fc3f1a91c8e0ff1db8522314526a4539abcd9e25a93d3ae4071b13edb4da33b16225f68e06c11344d5b7fd2edb6f4905cfe050b10295a197', '172.71.100.203', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 17:11:35', '2025-08-02 17:11:35', '2025-08-02 17:12:02', 0),
(20, 1, 'admin', 'f16722f55c664fc859d38ac55a63d7f705b36ae776521f4cee64a2b7b2b906f83a98ce1018331c5a7a21f9e06737863028ba903adb7df46de803ac18c0936241', '172.71.100.203', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36', '2025-08-02 17:12:17', '2025-08-02 17:12:17', '2025-08-02 17:13:12', 0);

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_queue`
--

CREATE TABLE `whatsapp_queue` (
  `id` int(11) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `message` text NOT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `status` enum('pending','sent','failed') DEFAULT 'pending',
  `attempts` int(11) DEFAULT 0,
  `last_attempt` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `sent_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `cashbox`
--
ALTER TABLE `cashbox`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cashbox_composite` (`lab_id`,`transaction_date`,`type`);

--
-- Indexes for table `email_queue`
--
ALTER TABLE `email_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `exam_catalog`
--
ALTER TABLE `exam_catalog`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code_exam` (`code_exam`),
  ADD KEY `lab_id` (`lab_id`),
  ADD KEY `fk_exam_catalog_category` (`category_id`),
  ADD KEY `idx_exam_catalog_composite` (`lab_id`,`is_active`);

--
-- Indexes for table `exam_categories`
--
ALTER TABLE `exam_categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `exam_components`
--
ALTER TABLE `exam_components`
  ADD PRIMARY KEY (`id`),
  ADD KEY `exam_id` (`exam_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `exam_invoices`
--
ALTER TABLE `exam_invoices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `insurance_companies`
--
ALTER TABLE `insurance_companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `labs`
--
ALTER TABLE `labs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `lab_employees`
--
ALTER TABLE `lab_employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_lab_id` (`lab_id`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `migration` (`migration`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`,`user_type`),
  ADD KEY `idx_lab` (`lab_id`),
  ADD KEY `idx_unread` (`is_read`),
  ADD KEY `idx_created` (`created_at`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `patient_exams`
--
ALTER TABLE `patient_exams`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `security_logs`
--
ALTER TABLE `security_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_event_type` (`event_type`),
  ADD KEY `idx_ip_address` (`ip_address`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lab_id` (`lab_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_token` (`session_token`),
  ADD KEY `idx_is_active` (`is_active`);

--
-- Indexes for table `whatsapp_queue`
--
ALTER TABLE `whatsapp_queue`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_created` (`created_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `cashbox`
--
ALTER TABLE `cashbox`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `email_queue`
--
ALTER TABLE `email_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee_attendance`
--
ALTER TABLE `employee_attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `exam_catalog`
--
ALTER TABLE `exam_catalog`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `exam_categories`
--
ALTER TABLE `exam_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `exam_components`
--
ALTER TABLE `exam_components`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `exam_invoices`
--
ALTER TABLE `exam_invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `insurance_companies`
--
ALTER TABLE `insurance_companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `labs`
--
ALTER TABLE `labs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `lab_employees`
--
ALTER TABLE `lab_employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `patient_exams`
--
ALTER TABLE `patient_exams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `security_logs`
--
ALTER TABLE `security_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `whatsapp_queue`
--
ALTER TABLE `whatsapp_queue`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`);

--
-- Constraints for table `exam_catalog`
--
ALTER TABLE `exam_catalog`
  ADD CONSTRAINT `exam_catalog_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_exam_catalog_category` FOREIGN KEY (`category_id`) REFERENCES `exam_categories` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `exam_categories`
--
ALTER TABLE `exam_categories`
  ADD CONSTRAINT `exam_categories_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `exam_components`
--
ALTER TABLE `exam_components`
  ADD CONSTRAINT `exam_components_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam_catalog` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `exam_components_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD CONSTRAINT `stock_items_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
