/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.10-MariaDB, for Linux (x86_64)
--
-- Host: localhost    Database: labor
-- ------------------------------------------------------
-- Server version	10.11.10-MariaDB-log

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `activity_logs`
--

DROP TABLE IF EXISTS `activity_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `admin_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `admin_id` (`admin_id`),
  CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activity_logs`
--

LOCK TABLES `activity_logs` WRITE;
/*!40000 ALTER TABLE `activity_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `activity_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES
(1,'Admin Master','admin@laborsaas.com','$2y$10$5.DVDtI7z91eOW6bs9pIReEjxpFZ.r4iPxMiINrM3UvY5dvR7aGH6',NULL,'2025-06-13 11:26:49'),
(3,'Test Admin','admin@test.com','$2y$10$Lm7lfzSZ5kCe1GcXnMl8XOqbm3gFkDqPqn5YvhcKzE9SjgIx6/qNO',NULL,'2025-07-26 16:32:13'),
(4,'Mohamed','hmadakhan686@gmail.com','$2y$10$5/Bw8EnUS2aRxh4XKbvQC.Ksl/eetdwMrtkNlyH4axDgsX/UX/g3u',NULL,'2025-07-27 13:23:51');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cashbox`
--

DROP TABLE IF EXISTS `cashbox`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cashbox` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cashbox`
--

LOCK TABLES `cashbox` WRITE;
/*!40000 ALTER TABLE `cashbox` DISABLE KEYS */;
INSERT INTO `cashbox` VALUES
(1,2,NULL,'قبض',NULL,'فحص مختبر',2000.00,NULL,'2025-06-13 20:34:35',5,'كاش',NULL),
(2,2,NULL,'قبض',NULL,'فحص مختبر',2000.00,NULL,'2025-06-13 20:49:05',5,'كاش',NULL),
(3,2,NULL,'قبض',NULL,'فحص مختبر',1000.00,NULL,'2025-06-14 09:45:16',5,'كاش',NULL),
(4,2,NULL,'قبض',NULL,'فحص مختبر',5000.00,NULL,'2025-06-14 09:45:19',5,'كاش',NULL),
(5,2,NULL,'صرف',NULL,'جوينت',1000.00,NULL,'2025-06-14 13:40:32',5,'كاش',''),
(6,2,NULL,'قبض',NULL,'فحص مختبر',1000.00,NULL,'2025-06-14 14:40:53',5,'كاش',NULL),
(7,2,NULL,'قبض',NULL,'فحص مختبر',5000.00,NULL,'2025-06-14 15:07:17',5,'كاش',NULL),
(8,2,NULL,'قبض',NULL,'فحص مختبر',1000.00,NULL,'2025-06-14 15:07:22',5,'كاش',NULL),
(9,2,NULL,'قبض',NULL,'فحص مختبر',5000.00,NULL,'2025-06-14 15:07:26',5,'كاش',NULL),
(10,2,NULL,'قبض',NULL,'فحص مختبر',1000.00,NULL,'2025-06-14 18:36:22',5,'كاش',NULL),
(11,2,NULL,'قبض',NULL,'فحص مختبر',2000.00,NULL,'2025-06-14 18:36:48',5,'كاش',NULL),
(12,2,NULL,'قبض',NULL,'فحص مختبر',1000.00,NULL,'2025-06-14 19:06:54',5,'كاش',NULL),
(13,2,'2025-06-14 19:21:41','قبض','فاتورة فحوصات للمريض رقم 1','فحص مخبري',1000.00,5,'2025-06-14 20:21:41',NULL,'كاش',NULL),
(14,2,'2025-06-14 20:05:03','قبض','فاتورة فحوصات للمريض رقم 1',NULL,2000.00,5,'2025-06-14 21:05:03',NULL,'كاش',NULL),
(15,2,'2025-06-14 20:35:14','قبض','تحصيل فحص للمريض ID:2 (فاتورة 10)',NULL,2000.00,5,'2025-06-14 21:35:14',NULL,'كاش',NULL),
(16,2,'2025-06-14 20:42:46','قبض','تحصيل فحص للمريض ID:2 (فاتورة 11)',NULL,3000.00,5,'2025-06-14 21:42:46',NULL,'كاش',NULL),
(17,2,'2025-06-14 21:11:34','قبض','تحصيل فحص للمريض ID:1 (فاتورة 12)',NULL,1500.00,5,'2025-06-14 22:11:34',NULL,'كاش',NULL),
(18,2,'2025-06-14 21:29:36','قبض','تحصيل فحص للمريض ID:2 (فاتورة 15)','فحوصات',5000.00,5,'2025-06-14 21:29:36',NULL,'كاش','فاتورة فحوص رقم 15'),
(19,2,NULL,'قبض',NULL,'فحص مختبر',5000.00,NULL,'2025-06-14 22:38:12',5,'كاش',NULL),
(20,2,'2025-06-15 19:45:14','قبض','تحصيل فحص للمريض ID:1 (فاتورة 18)','فحوصات',1500.00,5,'2025-06-15 19:45:14',NULL,'كاش','فاتورة فحوص رقم 18'),
(21,2,'2025-06-15 19:45:51','قبض','تحصيل فحص للمريض ID:2 (فاتورة 19)','فحوصات',4500.00,5,'2025-06-15 19:45:51',NULL,'كاش','فاتورة فحوص رقم 19'),
(22,2,NULL,'قبض',NULL,'فحص مختبر',5000.00,NULL,'2025-06-15 20:47:16',5,'كاش',NULL);
/*!40000 ALTER TABLE `cashbox` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_attendance`
--

DROP TABLE IF EXISTS `employee_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `check_in` datetime DEFAULT NULL,
  `check_out` datetime DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_attendance`
--

LOCK TABLES `employee_attendance` WRITE;
/*!40000 ALTER TABLE `employee_attendance` DISABLE KEYS */;
INSERT INTO `employee_attendance` VALUES
(1,5,1,'2025-06-14','2025-06-14 12:16:01',NULL,NULL,'2025-06-14 12:16:01');
/*!40000 ALTER TABLE `employee_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_shifts`
--

DROP TABLE IF EXISTS `employee_shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `assigned_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_shifts`
--

LOCK TABLES `employee_shifts` WRITE;
/*!40000 ALTER TABLE `employee_shifts` DISABLE KEYS */;
INSERT INTO `employee_shifts` VALUES
(2,5,1,1,'2025-06-14 12:06:21');
/*!40000 ALTER TABLE `employee_shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_catalog`
--

DROP TABLE IF EXISTS `exam_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `is_optional` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code_exam` (`code_exam`),
  KEY `lab_id` (`lab_id`),
  KEY `fk_exam_catalog_category` (`category_id`),
  CONSTRAINT `exam_catalog_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_exam_catalog_category` FOREIGN KEY (`category_id`) REFERENCES `exam_categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_catalog`
--

LOCK TABLES `exam_catalog` WRITE;
/*!40000 ALTER TABLE `exam_catalog` DISABLE KEYS */;
INSERT INTO `exam_catalog` VALUES
(1,2,'سكري','sudar','su',NULL,2000.00,'0','1000',NULL,NULL,'تت','2025-06-13 18:04:19',0,0,NULL),
(3,2,'سكري','sudar','1su','SLIPPERS',1000.00,'0','1000','تت','20','','2025-06-13 18:09:41',0,0,NULL),
(4,2,'سكري','sudar','su2','SLIPPERS',1000.00,'0','1000','تت','20','','2025-06-13 18:14:23',0,0,NULL),
(5,2,'ضغط','ta3','ta','',5000.00,'0','1000','تت','','','2025-06-14 09:40:59',1,0,NULL),
(7,2,'دم','CBC','cbc','SLIPPERS',1000.00,'0','1000','تت','20','','2025-06-14 11:25:25',1,0,NULL);
/*!40000 ALTER TABLE `exam_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_categories`
--

DROP TABLE IF EXISTS `exam_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `name_en` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `exam_categories_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_categories`
--

LOCK TABLES `exam_categories` WRITE;
/*!40000 ALTER TABLE `exam_categories` DISABLE KEYS */;
/*!40000 ALTER TABLE `exam_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_components`
--

DROP TABLE IF EXISTS `exam_components`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_components` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `exam_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `quantity_needed` int(11) DEFAULT NULL,
  `is_optional` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `exam_id` (`exam_id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `exam_components_ibfk_1` FOREIGN KEY (`exam_id`) REFERENCES `exam_catalog` (`id`) ON DELETE CASCADE,
  CONSTRAINT `exam_components_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_components`
--

LOCK TABLES `exam_components` WRITE;
/*!40000 ALTER TABLE `exam_components` DISABLE KEYS */;
INSERT INTO `exam_components` VALUES
(1,7,1,1,0);
/*!40000 ALTER TABLE `exam_components` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `exam_invoices`
--

DROP TABLE IF EXISTS `exam_invoices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `exam_invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `invoice_date` date NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `discount` decimal(10,2) DEFAULT 0.00,
  `insurance_company_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `exam_invoices`
--

LOCK TABLES `exam_invoices` WRITE;
/*!40000 ALTER TABLE `exam_invoices` DISABLE KEYS */;
INSERT INTO `exam_invoices` VALUES
(1,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:14:59',0.00,NULL),
(2,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:15:23',0.00,NULL),
(3,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:15:56',0.00,NULL),
(4,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:16:22',0.00,NULL),
(5,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:16:54',0.00,NULL),
(6,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:20:26',0.00,NULL),
(7,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:21:01',0.00,NULL),
(8,2,1,'2025-06-14',1000.00,'دكتور خالد','',5,'2025-06-14 19:21:41',0.00,NULL),
(9,2,1,'2025-06-14',2000.00,'دكتور خالد','',5,'2025-06-14 20:05:03',0.00,NULL),
(10,2,2,'2025-06-14',2000.00,'دكتور خالد','',5,'2025-06-14 20:35:14',0.00,NULL),
(11,2,2,'2025-06-14',3000.00,'دكتور خالد','',5,'2025-06-14 20:42:46',0.00,NULL),
(12,2,1,'2025-06-14',2000.00,'دكتور خالد','',5,'2025-06-14 21:11:34',500.00,1),
(13,2,2,'2025-06-14',2000.00,'دكتور خالد','',5,'2025-06-14 21:22:21',0.00,1),
(14,2,2,'2025-06-14',5000.00,'دكتور خالد','',5,'2025-06-14 21:28:28',0.00,1),
(15,2,2,'2025-06-14',5000.00,'دكتور خالد','',5,'2025-06-14 21:29:36',0.00,1),
(16,2,1,'2025-06-15',2000.00,'دكتور خالد','',5,'2025-06-15 19:40:06',500.00,1),
(17,2,1,'2025-06-15',2000.00,'دكتور خالد','',5,'2025-06-15 19:42:56',500.00,1),
(18,2,1,'2025-06-15',2000.00,'دكتور خالد','',5,'2025-06-15 19:45:14',500.00,1),
(19,2,2,'2025-06-15',5000.00,'دكتور خالد','',5,'2025-06-15 19:45:51',500.00,1);
/*!40000 ALTER TABLE `exam_invoices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `insurance_companies`
--

DROP TABLE IF EXISTS `insurance_companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `insurance_companies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `insurance_companies`
--

LOCK TABLES `insurance_companies` WRITE;
/*!40000 ALTER TABLE `insurance_companies` DISABLE KEYS */;
INSERT INTO `insurance_companies` VALUES
(1,2,'النيلين للتأمين','2025-06-14 21:52:46',1);
/*!40000 ALTER TABLE `insurance_companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `lab_employees`
--

DROP TABLE IF EXISTS `lab_employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lab_employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('مدير','طبيب','محضر','محاسب') DEFAULT 'طبيب',
  `status` enum('نشط','معطل') DEFAULT 'نشط',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `lab_employees`
--

LOCK TABLES `lab_employees` WRITE;
/*!40000 ALTER TABLE `lab_employees` DISABLE KEYS */;
INSERT INTO `lab_employees` VALUES
(5,2,'طبيب','manager@lab.com','$2y$10$/yqyNwb5FJhZOoYhbaX92.ZIMKcAl2kO4h6S2mXhfV/eBa/ssGy.e','مدير','نشط');
/*!40000 ALTER TABLE `lab_employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `labs`
--

DROP TABLE IF EXISTS `labs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `labs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_main` varchar(20) DEFAULT NULL,
  `phone_secondary` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `map_link` text DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `labs`
--

LOCK TABLES `labs` WRITE;
/*!40000 ALTER TABLE `labs` DISABLE KEYS */;
INSERT INTO `labs` VALUES
(2,'معمل الشفاء','hmadakhan686@gmail.com','0910564187','','Portsudan','logo_684c2266bc59f.png','','active','2025-06-13 12:26:17');
/*!40000 ALTER TABLE `labs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patient_exams`
--

DROP TABLE IF EXISTS `patient_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patient_exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `comment` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patient_exams`
--

LOCK TABLES `patient_exams` WRITE;
/*!40000 ALTER TABLE `patient_exams` DISABLE KEYS */;
INSERT INTO `patient_exams` VALUES
(1,2,1,1,NULL,'','2025-06-13','900','تم التسليم',NULL,5,'2025-06-13 19:20:28',''),
(2,2,1,1,NULL,'','2025-06-13',NULL,'عمل استرداد',NULL,5,'2025-06-13 20:49:39',NULL),
(3,2,1,3,NULL,'','2025-06-13','900','تم التسليم',NULL,5,'2025-06-13 20:49:39','good'),
(4,2,1,4,NULL,'','2025-06-13','900','تم التسليم',NULL,5,'2025-06-13 20:49:39',''),
(5,2,1,5,NULL,'دكتور خالد','2025-06-14','900','تم التسليم',NULL,5,'2025-06-14 09:43:42',''),
(6,2,1,7,NULL,'دكتور خالد','2025-06-14','900','تم التسليم',NULL,5,'2025-06-14 11:26:38',''),
(7,2,1,5,NULL,'دكتور خالد','2025-06-14','900','تم التسليم',NULL,5,'2025-06-14 11:28:30',''),
(8,2,1,7,NULL,'دكتور خالد','2025-06-14','900','تم التسليم',NULL,5,'2025-06-14 11:28:51',''),
(9,2,1,5,NULL,'دكتور خالد','2025-06-14',NULL,'قيد الإجراء',NULL,5,'2025-06-14 18:58:32',NULL),
(10,2,1,7,NULL,'دكتور خالد','2025-06-14',NULL,'قيد الإجراء',NULL,5,'2025-06-14 18:58:32',NULL),
(11,2,1,7,NULL,'دكتور خالد','2025-06-14',NULL,'قيد الإجراء',NULL,5,'2025-06-14 20:09:54',NULL),
(12,2,1,7,3,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:15:56',NULL),
(13,2,1,7,4,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:16:22',NULL),
(14,2,1,7,5,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:16:54',NULL),
(15,2,1,7,6,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:20:26',NULL),
(16,2,1,7,7,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:21:01',NULL),
(17,2,1,7,8,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 20:21:41',NULL),
(18,2,1,7,NULL,'دكتور خالد','2025-06-14',NULL,'قيد الإجراء',NULL,5,'2025-06-14 21:04:29',NULL),
(19,2,1,1,9,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 21:05:03',NULL),
(20,2,2,1,10,NULL,'2025-06-14','900','تم الاستخراج',5,5,'2025-06-14 21:35:14',''),
(21,2,2,3,11,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 21:42:46',NULL),
(22,2,2,1,11,NULL,'2025-06-14','900','تم الاستخراج',5,5,'2025-06-14 21:42:46',''),
(23,2,1,1,12,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 22:11:34',NULL),
(24,2,1,1,12,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 22:11:34',NULL),
(25,2,2,1,13,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 22:22:21',NULL),
(26,2,2,5,14,NULL,'2025-06-14',NULL,'قيد الإجراء',5,NULL,'2025-06-14 22:28:28',NULL),
(27,2,2,5,15,NULL,'2025-06-14','900','تم التسليم',5,5,'2025-06-14 22:29:36',''),
(28,2,1,1,16,NULL,'2025-06-15',NULL,'قيد الإجراء',5,NULL,'2025-06-15 20:40:06',NULL),
(29,2,1,1,17,NULL,'2025-06-15',NULL,'قيد الإجراء',5,NULL,'2025-06-15 20:42:56',NULL),
(30,2,1,1,18,NULL,'2025-06-15',NULL,'قيد الإجراء',5,NULL,'2025-06-15 20:45:14',NULL),
(31,2,2,5,19,NULL,'2025-06-15','900','تم التسليم',5,5,'2025-06-15 20:45:51','');
/*!40000 ALTER TABLE `patient_exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `patients`
--

DROP TABLE IF EXISTS `patients`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `patients` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) DEFAULT NULL,
  `code` varchar(20) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `gender` enum('ذكر','أنثى') DEFAULT NULL,
  `age_value` int(11) DEFAULT NULL,
  `age_unit` enum('يوم','أسبوع','شهر','سنة') DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `history` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `patients`
--

LOCK TABLES `patients` WRITE;
/*!40000 ALTER TABLE `patients` DISABLE KEYS */;
INSERT INTO `patients` VALUES
(1,2,'P-11847','محمد عبدالله علي فرح ','ذكر',29,'سنة','0910564187','Portsudan','','2025-06-13 15:04:02'),
(2,2,'P-19956','عبدالعظييم','ذكر',30,'سنة','0910564187','Portsudan','','2025-06-14 21:20:18');
/*!40000 ALTER TABLE `patients` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `referrals`
--

DROP TABLE IF EXISTS `referrals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `referrals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `referrals`
--

LOCK TABLES `referrals` WRITE;
/*!40000 ALTER TABLE `referrals` DISABLE KEYS */;
INSERT INTO `referrals` VALUES
(1,2,'دكتور خالد','2025-06-14 19:14:30');
/*!40000 ALTER TABLE `referrals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `shifts`
--

DROP TABLE IF EXISTS `shifts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `shifts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `days` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `shifts`
--

LOCK TABLES `shifts` WRITE;
/*!40000 ALTER TABLE `shifts` DISABLE KEYS */;
INSERT INTO `shifts` VALUES
(1,2,'صباحية','08:00:00','15:00:00','السبت-الخميس','2025-06-14 12:02:14');
/*!40000 ALTER TABLE `shifts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_items`
--

DROP TABLE IF EXISTS `stock_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(50) DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `type` enum('مستهلك','دائم') DEFAULT 'مستهلك',
  `min_quantity` float DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `stock_items_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_items`
--

LOCK TABLES `stock_items` WRITE;
/*!40000 ALTER TABLE `stock_items` DISABLE KEYS */;
INSERT INTO `stock_items` VALUES
(1,2,'جلكوز',1500,'مل','2026-06-14','2025-06-14 09:13:31','مستهلك',100);
/*!40000 ALTER TABLE `stock_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `stock_id` int(11) DEFAULT NULL,
  `lab_id` int(11) DEFAULT NULL,
  `movement_type` enum('إدخال','إخراج') DEFAULT NULL,
  `quantity` float DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `employee_id` int(11) DEFAULT NULL,
  `item_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
INSERT INTO `stock_movements` VALUES
(1,1,2,'إدخال',100,'شراء','2025-06-14 09:28:17',NULL,0),
(2,1,2,'إخراج',100,'تالف','2025-06-14 09:37:46',NULL,0),
(3,NULL,2,'',1,'خصم نتيجة للفحص رقم 6','2025-06-14 11:27:53',NULL,1),
(4,NULL,2,'',1,'خصم نتيجة للفحص رقم 8','2025-06-14 11:29:16',NULL,1),
(5,1,2,'إدخال',3,'شراء','2025-06-14 11:42:40',NULL,0),
(6,1,2,'إخراج',1,'تالف','2025-06-14 12:01:08',NULL,0),
(7,1,2,'إدخال',100,'شراء','2025-06-14 12:03:58',NULL,0),
(8,1,2,'إدخال',500,'شراء','2025-06-15 20:51:20',NULL,0);
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) DEFAULT NULL,
  `plan` varchar(50) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('active','expired','trial') DEFAULT 'trial',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `response` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `replied_at` datetime DEFAULT NULL,
  `seen_by_admin` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lab_id` (`lab_id`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`lab_id`) REFERENCES `labs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-07-31 17:12:15
