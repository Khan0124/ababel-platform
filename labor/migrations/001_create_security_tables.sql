-- Migration: Create Security Tables
-- Date: 2025-07-31

-- Create security_logs table for audit trail
CREATE TABLE IF NOT EXISTS `security_logs` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `event_type` VARCHAR(50) NOT NULL,
    `description` TEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `user_id` INT NULL,
    `user_type` ENUM('admin', 'lab_employee') NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_event_type` (`event_type`),
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_created_at` (`created_at`),
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create user_sessions table for session management
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `user_type` ENUM('admin', 'lab_employee') NOT NULL,
    `session_token` VARCHAR(128) NOT NULL UNIQUE,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `last_activity` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `ended_at` TIMESTAMP NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_session_token` (`session_token`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_last_activity` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create password_resets table
CREATE TABLE IF NOT EXISTS `password_resets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `email` VARCHAR(100) NOT NULL,
    `token` VARCHAR(255) NOT NULL,
    `user_type` ENUM('admin', 'lab_employee') NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP NOT NULL,
    INDEX `idx_email` (`email`),
    INDEX `idx_token` (`token`),
    INDEX `idx_expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Create failed_login_attempts table for brute force protection
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
    `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
    `ip_address` VARCHAR(45) NOT NULL,
    `email_or_username` VARCHAR(100),
    `user_agent` TEXT,
    `attempted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ip_address` (`ip_address`),
    INDEX `idx_attempted_at` (`attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add missing columns to existing tables
ALTER TABLE `admins` 
    ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS `two_factor_secret` VARCHAR(255) NULL,
    ADD COLUMN IF NOT EXISTS `two_factor_enabled` BOOLEAN DEFAULT FALSE,
    ADD COLUMN IF NOT EXISTS `password_changed_at` TIMESTAMP NULL,
    ADD INDEX IF NOT EXISTS `idx_email` (`email`),
    ADD INDEX IF NOT EXISTS `idx_is_active` (`is_active`);

ALTER TABLE `lab_employees` 
    ADD COLUMN IF NOT EXISTS `username` VARCHAR(50) UNIQUE NULL AFTER `email`,
    ADD COLUMN IF NOT EXISTS `is_active` BOOLEAN DEFAULT TRUE,
    ADD COLUMN IF NOT EXISTS `last_login` TIMESTAMP NULL,
    ADD COLUMN IF NOT EXISTS `password_changed_at` TIMESTAMP NULL,
    ADD INDEX IF NOT EXISTS `idx_username` (`username`),
    ADD INDEX IF NOT EXISTS `idx_email` (`email`),
    ADD INDEX IF NOT EXISTS `idx_lab_id` (`lab_id`),
    ADD INDEX IF NOT EXISTS `idx_is_active` (`is_active`);

-- Update character set for all tables to utf8mb4
ALTER TABLE `activity_logs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `admins` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `cashbox` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `employee_attendance` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `employee_shifts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `exam_catalog` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `exam_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `exam_components` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `exam_invoices` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `insurance_companies` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `labs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `lab_employees` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `patients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `patient_exams` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `referrals` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `shifts` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `stock_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `stock_movements` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `subscriptions` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
ALTER TABLE `tickets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Add performance indexes
CREATE INDEX IF NOT EXISTS `idx_patient_exams_composite` ON `patient_exams` (`lab_id`, `patient_id`, `status`);
CREATE INDEX IF NOT EXISTS `idx_cashbox_composite` ON `cashbox` (`lab_id`, `transaction_date`, `type`);
CREATE INDEX IF NOT EXISTS `idx_exam_catalog_composite` ON `exam_catalog` (`lab_id`, `is_active`);

-- Create trigger to auto-set username for lab_employees if not provided
DELIMITER //
CREATE TRIGGER IF NOT EXISTS `set_employee_username`
BEFORE INSERT ON `lab_employees`
FOR EACH ROW
BEGIN
    IF NEW.username IS NULL OR NEW.username = '' THEN
        SET NEW.username = NEW.email;
    END IF;
END//
DELIMITER ;