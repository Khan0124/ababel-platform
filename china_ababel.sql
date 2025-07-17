-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jul 17, 2025 at 07:55 PM
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
-- Database: `china_ababel`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cashbox_movements`
--

CREATE TABLE `cashbox_movements` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `movement_date` date NOT NULL,
  `movement_type` enum('in','out','transfer') NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `amount_rmb` decimal(15,2) DEFAULT 0.00,
  `amount_usd` decimal(15,2) DEFAULT 0.00,
  `amount_sdg` decimal(15,2) DEFAULT 0.00,
  `amount_aed` decimal(15,2) DEFAULT 0.00,
  `bank_name` varchar(100) DEFAULT NULL,
  `tt_number` varchar(50) DEFAULT NULL,
  `receipt_no` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `balance_after_rmb` decimal(15,2) DEFAULT NULL,
  `balance_after_usd` decimal(15,2) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `cashbox_movements`
--

INSERT INTO `cashbox_movements` (`id`, `transaction_id`, `movement_date`, `movement_type`, `category`, `amount_rmb`, `amount_usd`, `amount_sdg`, `amount_aed`, `bank_name`, `tt_number`, `receipt_no`, `description`, `balance_after_rmb`, `balance_after_usd`, `created_by`, `created_at`) VALUES
(1, 1, '2025-07-17', 'in', 'customer_transfer', 212.00, 0.00, 0.00, 0.00, 'ببب', NULL, '455453', NULL, NULL, NULL, 1, '2025-07-16 16:16:21'),
(2, 1, '2025-07-17', 'in', 'payment_received', 212.00, 0.00, 0.00, 0.00, 'ببب', NULL, NULL, 'دفعة مستلمة من العميل: Mohamed Abdulla Ali Farh', NULL, NULL, 1, '2025-07-16 18:36:36'),
(3, 2, '2025-07-17', 'in', 'payment_received', 400.00, 800.00, 1000.00, 0.00, '', NULL, NULL, 'دفعة مستلمة من العميل: Mohamed Abdulla Ali Farh', NULL, NULL, 1, '2025-07-16 20:34:33');

-- --------------------------------------------------------

--
-- Stand-in structure for view `cashbox_summary`
-- (See below for the actual view)
--
CREATE TABLE `cashbox_summary` (
`balance_rmb` decimal(37,2)
,`balance_usd` decimal(37,2)
,`balance_sdg` decimal(37,2)
);

-- --------------------------------------------------------

--
-- Table structure for table `clients`
--

CREATE TABLE `clients` (
  `id` int(11) NOT NULL,
  `client_code` varchar(50) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL COMMENT 'Name in English',
  `name_ar` varchar(255) DEFAULT NULL COMMENT 'Name in Arabic',
  `phone` varchar(100) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `balance_rmb` decimal(15,2) DEFAULT 0.00,
  `balance_usd` decimal(15,2) DEFAULT 0.00,
  `balance_sdg` decimal(15,2) DEFAULT 0.00,
  `credit_limit` decimal(15,2) DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `clients`
--

INSERT INTO `clients` (`id`, `client_code`, `name`, `name_ar`, `phone`, `email`, `address`, `balance_rmb`, `balance_usd`, `balance_sdg`, `credit_limit`, `status`, `created_at`, `updated_at`) VALUES
(1, '1', 'Mohamed Abdulla Ali Farh', 'محمد عبدالله علي', '0910564187', 'hmadakhan686@gmail.com', 'Portsudan', 600.00, 1200.00, 0.00, 0.00, 'active', '2025-07-16 13:58:13', '2025-07-16 20:34:33');

-- --------------------------------------------------------

--
-- Stand-in structure for view `client_balances`
-- (See below for the actual view)
--
CREATE TABLE `client_balances` (
`id` int(11)
,`name` varchar(255)
,`client_code` varchar(50)
,`total_balance_rmb` decimal(37,2)
,`total_balance_usd` decimal(37,2)
,`transaction_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `loadings`
--

CREATE TABLE `loadings` (
  `id` int(11) NOT NULL,
  `loading_no` varchar(50) DEFAULT NULL,
  `shipping_date` date NOT NULL,
  `arrival_date` date DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `claim_number` varchar(50) DEFAULT NULL,
  `container_no` varchar(50) NOT NULL,
  `bl_number` varchar(50) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `client_code` varchar(20) DEFAULT NULL,
  `client_name` varchar(255) DEFAULT NULL,
  `item_description` text DEFAULT NULL,
  `cartons_count` int(11) DEFAULT 0,
  `purchase_amount` decimal(15,2) DEFAULT 0.00,
  `commission_amount` decimal(15,2) DEFAULT 0.00,
  `total_amount` decimal(15,2) DEFAULT 0.00,
  `shipping_usd` decimal(15,2) DEFAULT 0.00,
  `total_with_shipping` decimal(15,2) DEFAULT 0.00,
  `office` varchar(100) DEFAULT NULL,
  `status` enum('pending','shipped','arrived','cleared','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_by` int(11) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `loadings`
--

INSERT INTO `loadings` (`id`, `loading_no`, `shipping_date`, `arrival_date`, `payment_method`, `claim_number`, `container_no`, `bl_number`, `client_id`, `client_code`, `client_name`, `item_description`, `cartons_count`, `purchase_amount`, `commission_amount`, `total_amount`, `shipping_usd`, `total_with_shipping`, `office`, `status`, `notes`, `created_by`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, NULL, '2025-07-17', NULL, 'transfer', '12', 'CMAU7702685', NULL, 1, '1', 'Mohamed Abdulla Ali Farh', 'احذية', 500, 2000000.00, 200.00, 2000200.00, 2000.00, 12000200.00, 'port_sudan', 'pending', NULL, 1, '2025-07-17 09:22:40', NULL, '2025-07-17 09:22:40');

-- --------------------------------------------------------

--
-- Table structure for table `office_notifications`
--

CREATE TABLE `office_notifications` (
  `id` int(11) NOT NULL,
  `office` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `read_by` int(11) DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `office_notifications`
--

INSERT INTO `office_notifications` (`id`, `office`, `type`, `reference_id`, `reference_type`, `message`, `is_read`, `read_by`, `read_at`, `created_at`) VALUES
(1, 'port_sudan', 'new_container', 1, 'loading', 'New container CMAU7702685 assigned to your office', 0, NULL, NULL, '2025-07-17 09:22:40');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(50) DEFAULT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` varchar(20) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `updated_at`) VALUES
(1, 'exchange_rate_usd_rmb', '20', NULL, '2025-07-17 11:26:41'),
(2, 'exchange_rate_sdg_rmb', '4000', NULL, '2025-07-16 13:53:00'),
(3, 'exchange_rate_aed_rmb', '2000', NULL, '2025-07-16 13:53:00'),
(4, 'company_name', 'شركة أبابيل للتنمية و الاستثمار المحدودة', NULL, '2025-07-16 14:49:23'),
(5, 'company_address', '', NULL, '2025-07-16 13:53:00'),
(6, 'company_phone', '', NULL, '2025-07-16 13:53:00'),
(13, 'banks_list', 'Bank of Khartoum,Faisal Islamic Bank,Omdurman National Bank,Blue Nile Bank,Agricultural Bank of Sudan', NULL, '2025-07-16 16:13:37');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_no` varchar(50) DEFAULT NULL,
  `client_id` int(11) DEFAULT NULL,
  `transaction_type_id` int(11) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `description` text DEFAULT NULL,
  `description_ar` text DEFAULT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `bank_name` varchar(100) DEFAULT NULL,
  `loading_no` varchar(50) DEFAULT NULL,
  `goods_amount_rmb` decimal(15,2) DEFAULT 0.00,
  `commission_rmb` decimal(15,2) DEFAULT 0.00,
  `total_amount_rmb` decimal(15,2) DEFAULT 0.00,
  `payment_rmb` decimal(15,2) DEFAULT 0.00,
  `balance_rmb` decimal(15,2) DEFAULT 0.00,
  `shipping_usd` decimal(15,2) DEFAULT 0.00,
  `payment_usd` decimal(15,2) DEFAULT 0.00,
  `balance_usd` decimal(15,2) DEFAULT 0.00,
  `payment_sdg` decimal(15,2) DEFAULT 0.00,
  `rate_usd_rmb` decimal(10,4) DEFAULT NULL,
  `rate_sdg_rmb` decimal(10,4) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `approved_at` datetime DEFAULT NULL,
  `status` enum('pending','approved','cancelled') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_no`, `client_id`, `transaction_type_id`, `transaction_date`, `description`, `description_ar`, `invoice_no`, `bank_name`, `loading_no`, `goods_amount_rmb`, `commission_rmb`, `total_amount_rmb`, `payment_rmb`, `balance_rmb`, `shipping_usd`, `payment_usd`, `balance_usd`, `payment_sdg`, `rate_usd_rmb`, `rate_sdg_rmb`, `created_by`, `approved_by`, `approved_at`, `status`, `created_at`, `updated_at`) VALUES
(1, 'TRX-2025-000001', 1, 5, '2025-07-17', 'قيمة البضاعة', NULL, '45', 'ببب', '7', 1200.00, 12.00, 1212.00, 212.00, 1000.00, 2000.00, 0.00, 2000.00, 0.00, NULL, NULL, 1, 1, '2025-07-17 02:36:36', 'approved', '2025-07-16 16:16:21', '2025-07-16 18:36:36'),
(2, 'TRX-2025-000026', 1, 2, '2025-07-17', '', NULL, '', '', '', 1200.00, 1200.00, 2400.00, 400.00, 2000.00, 1000.00, 800.00, 200.00, 1000.00, NULL, NULL, 1, 1, '2025-07-17 04:34:33', 'approved', '2025-07-16 19:15:13', '2025-07-16 20:34:33');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_types`
--

CREATE TABLE `transaction_types` (
  `id` int(11) NOT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `name_ar` varchar(100) DEFAULT NULL,
  `type` enum('income','expense','transfer') NOT NULL,
  `affects_cashbox` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `transaction_types`
--

INSERT INTO `transaction_types` (`id`, `code`, `name`, `name_ar`, `type`, `affects_cashbox`) VALUES
(1, 'GOODS_PURCHASE', 'Goods Purchase', 'شراء بضاعة', 'expense', 1),
(2, 'SHIPPING', 'Shipping Cost', 'تكلفة الشحن', 'expense', 1),
(3, 'PAYMENT_RECEIVED', 'Payment Received', 'دفعة مستلمة', 'income', 1),
(4, 'OFFICE_TRANSFER', 'Office Transfer', 'تحويل مكتب', 'transfer', 1),
(5, 'FACTORY_PAYMENT', 'Factory Payment', 'دفعة للمصنع', 'expense', 1);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `role` enum('admin','accountant','viewer') DEFAULT 'accountant',
  `language` varchar(5) DEFAULT 'ar',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `email`, `role`, `language`, `is_active`, `last_login`, `created_at`) VALUES
(1, 'admin', '$2y$10$crLrK32LCHVboWqNS5lEmubXH.YHsyOQoiuFJB1QtXY/qhmJeLNSa', 'admin admin', 'admin@china.ababel.net', 'admin', 'ar', 1, '2025-07-17 11:05:20', '2025-07-16 12:04:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `cashbox_movements`
--
ALTER TABLE `cashbox_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_cash_date` (`movement_date`);

--
-- Indexes for table `clients`
--
ALTER TABLE `clients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_code` (`client_code`);

--
-- Indexes for table `loadings`
--
ALTER TABLE `loadings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `container_no` (`container_no`),
  ADD KEY `client_id` (`client_id`),
  ADD KEY `shipping_date` (`shipping_date`),
  ADD KEY `status` (`status`),
  ADD KEY `office` (`office`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_loading_no` (`loading_no`),
  ADD KEY `idx_claim_number` (`claim_number`);

--
-- Indexes for table `office_notifications`
--
ALTER TABLE `office_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `office` (`office`),
  ADD KEY `is_read` (`is_read`),
  ADD KEY `reference` (`reference_type`,`reference_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_no` (`transaction_no`),
  ADD UNIQUE KEY `unique_transaction_bank` (`transaction_no`,`bank_name`),
  ADD KEY `transaction_type_id` (`transaction_type_id`),
  ADD KEY `idx_trans_date` (`transaction_date`),
  ADD KEY `idx_trans_client` (`client_id`);

--
-- Indexes for table `transaction_types`
--
ALTER TABLE `transaction_types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);

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
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `cashbox_movements`
--
ALTER TABLE `cashbox_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `clients`
--
ALTER TABLE `clients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `loadings`
--
ALTER TABLE `loadings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `office_notifications`
--
ALTER TABLE `office_notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transaction_types`
--
ALTER TABLE `transaction_types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

-- --------------------------------------------------------

--
-- Structure for view `cashbox_summary`
--
DROP TABLE IF EXISTS `cashbox_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`china_ababel`@`localhost` SQL SECURITY DEFINER VIEW `cashbox_summary`  AS SELECT sum(case when `cashbox_movements`.`movement_type` = 'in' then `cashbox_movements`.`amount_rmb` else -`cashbox_movements`.`amount_rmb` end) AS `balance_rmb`, sum(case when `cashbox_movements`.`movement_type` = 'in' then `cashbox_movements`.`amount_usd` else -`cashbox_movements`.`amount_usd` end) AS `balance_usd`, sum(case when `cashbox_movements`.`movement_type` = 'in' then `cashbox_movements`.`amount_sdg` else -`cashbox_movements`.`amount_sdg` end) AS `balance_sdg` FROM `cashbox_movements` ;

-- --------------------------------------------------------

--
-- Structure for view `client_balances`
--
DROP TABLE IF EXISTS `client_balances`;

CREATE ALGORITHM=UNDEFINED DEFINER=`china_ababel`@`localhost` SQL SECURITY DEFINER VIEW `client_balances`  AS SELECT `c`.`id` AS `id`, `c`.`name` AS `name`, `c`.`client_code` AS `client_code`, coalesce(sum(`t`.`balance_rmb`),0) AS `total_balance_rmb`, coalesce(sum(`t`.`balance_usd`),0) AS `total_balance_usd`, count(`t`.`id`) AS `transaction_count` FROM (`clients` `c` left join `transactions` `t` on(`c`.`id` = `t`.`client_id` and `t`.`status` = 'approved')) GROUP BY `c`.`id` ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cashbox_movements`
--
ALTER TABLE `cashbox_movements`
  ADD CONSTRAINT `cashbox_movements_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`);

--
-- Constraints for table `loadings`
--
ALTER TABLE `loadings`
  ADD CONSTRAINT `loadings_client_fk` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `loadings_created_by_fk` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`),
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`transaction_type_id`) REFERENCES `transaction_types` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
