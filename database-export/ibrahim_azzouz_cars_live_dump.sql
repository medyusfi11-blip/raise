-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: 127.0.0.1    Database: ibrahim_azzouz_cars
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

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
-- Current Database: `ibrahim_azzouz_cars`
--

/*!40000 DROP DATABASE IF EXISTS `ibrahim_azzouz_cars`*/;

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `ibrahim_azzouz_cars` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `ibrahim_azzouz_cars`;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `admins` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role` enum('super_admin','admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_admins_username` (`username`),
  UNIQUE KEY `uk_admins_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'admin','$2y$10$J64vXOmtdxgzcPfxlAiHlelvovXNyXCyo56QlDV4ahnNKSbYsIU/i','Ibrahim Azzouz','admin@ibrahimazzouzcars.com','super_admin','2026-07-18 02:58:24','2026-11-18 13:33:54');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `car_id` int(10) unsigned NOT NULL,
  `customer_name` varchar(150) NOT NULL,
  `customer_email` varchar(150) NOT NULL,
  `customer_phone` varchar(30) NOT NULL,
  `customer_city` varchar(100) NOT NULL DEFAULT '',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `total_days` int(10) unsigned NOT NULL DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('Pending','Accepted','Rejected','Completed') NOT NULL DEFAULT 'Pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_bookings_car_id` (`car_id`),
  KEY `idx_bookings_status` (`status`),
  KEY `idx_bookings_dates` (`start_date`,`end_date`),
  CONSTRAINT `fk_bookings_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `car_features`
--

DROP TABLE IF EXISTS `car_features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `car_features` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `car_id` int(10) unsigned NOT NULL,
  `feature_name` varchar(100) NOT NULL,
  `feature_icon` varchar(100) NOT NULL DEFAULT 'fas fa-check',
  PRIMARY KEY (`id`),
  KEY `idx_cf_car_id` (`car_id`),
  CONSTRAINT `fk_cf_car` FOREIGN KEY (`car_id`) REFERENCES `cars` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `car_features`
--

LOCK TABLES `car_features` WRITE;
/*!40000 ALTER TABLE `car_features` DISABLE KEYS */;
INSERT INTO `car_features` VALUES (1,1,'Climatisation','fas fa-snowflake'),(2,1,'Bluetooth','fas fa-bluetooth-b'),(3,1,'Direction assistée','fas fa-steering-wheel'),(4,1,'ABS','fas fa-shield-alt'),(5,2,'Climatisation','fas fa-snowflake'),(6,2,'Bluetooth','fas fa-bluetooth-b'),(7,2,'Direction assistée','fas fa-steering-wheel'),(8,2,'ABS','fas fa-shield-alt'),(9,2,'Barres de toit','fas fa-car'),(10,2,'Écran tactile','fas fa-tablet-alt'),(16,4,'Climatisation','fas fa-snowflake'),(17,4,'Direction assistée','fas fa-steering-wheel'),(18,4,'ABS','fas fa-shield-alt'),(19,4,'Volume de chargement','fas fa-truck-loading');
/*!40000 ALTER TABLE `car_features` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cars`
--

DROP TABLE IF EXISTS `cars`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cars` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `model` varchar(150) NOT NULL,
  `type` enum('Hatchback','SUV','Sedan','Van','Truck') NOT NULL DEFAULT 'Sedan',
  `year` year(4) NOT NULL,
  `price_per_day` decimal(10,2) NOT NULL DEFAULT 0.00,
  `transmission` enum('Manual','Automatic') NOT NULL DEFAULT 'Manual',
  `fuel_type` enum('Petrol','Diesel','Electric','Hybrid') NOT NULL DEFAULT 'Petrol',
  `seats` int(10) unsigned NOT NULL DEFAULT 5,
  `units_available` int(10) unsigned NOT NULL DEFAULT 1,
  `status` enum('Available','Reserved','Maintenance') NOT NULL DEFAULT 'Available',
  `image` varchar(500) NOT NULL DEFAULT '',
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_cars_status` (`status`),
  KEY `idx_cars_type` (`type`),
  KEY `idx_cars_price` (`price_per_day`),
  KEY `idx_cars_year` (`year`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cars`
--

LOCK TABLES `cars` WRITE;
/*!40000 ALTER TABLE `cars` DISABLE KEYS */;
INSERT INTO `cars` VALUES (1,'Dacia Sandero Access','Sandero','Hatchback',2023,250.00,'Manual','Petrol',5,3,'Available','uploads/cars/dacia-sandero-access.jpg','[\"Climatisation\",\"Bluetooth\",\"Direction assistée\",\"ABS\"]','Dacia Sandero Access — citadine économique et fiable. Idéale pour les déplacements quotidiens à prix doux.','2026-07-18 02:58:24','2026-08-12 23:57:14'),(2,'Dacia Sandero Stepway','Sandero Stepway','SUV',2023,300.00,'Manual','Petrol',5,2,'Available','uploads/cars/dacia-sandero-stepway.jpg','[\"Climatisation\",\"Bluetooth\",\"Direction assistée\",\"ABS\",\"Barres de toit\",\"Écran tactile\"]','Dacia Sandero Stepway — style crossover, confort amélioré et garde au sol rehaussée pour toutes les routes marocaines.','2026-07-18 02:58:24','2026-08-12 23:57:14'),(4,'Dacia Logan Van','Logan','Van',2022,220.00,'Manual','Diesel',2,1,'Available','uploads/cars/dacia-logan-van.jpg','[\"Climatisation\",\"Direction assistée\",\"ABS\",\"Volume de chargement maximal\"]','Dacia Logan Van — utilitaire polyvalent pour le transport de marchandises. Grand volume, consommation réduite.','2026-07-18 02:58:24','2026-08-12 23:57:14'),(5,'audi','2025','Sedan',2026,350.00,'Manual','Petrol',5,1,'Available','uploads/cars/audi-a4.jpg','[]','','2026-07-18 15:38:41','2026-08-13 00:55:00'),(6,'audi','2025','Sedan',2026,350.00,'Manual','Petrol',5,1,'Available','uploads/cars/audi-a4.jpg','[]',NULL,'2026-07-18 15:49:42','2026-08-13 00:16:05'),(8,'dacia sandiro','2025','Sedan',2026,200.00,'Manual','Petrol',5,1,'Available','uploads/cars/car_0cad339df324887ce3c4b57f.png','[]','wesrdgtgyhjki','2026-08-13 00:19:16','2026-08-13 00:19:16'),(11,'dacia','2025','Sedan',2026,780.00,'Manual','Petrol',5,1,'Available','uploads/cars/car_679a173d80f8c9ba63fd258b.png','[]',NULL,'2026-08-13 00:56:15','2026-08-13 00:56:15');
/*!40000 ALTER TABLE `cars` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `settings` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_settings_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','Ibrahim Azzouz Cars'),(2,'site_phone','+212 6 15 50 42 73'),(3,'site_whatsapp','+212 7 64 73 01 87'),(4,'site_email','contact@ibrahimazzouzcars.com'),(5,'site_instagram','https://www.instagram.com/azzouz_brahim_car?igsh=MW9taDRhenk0Mmw0Mg==');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping events for database 'ibrahim_azzouz_cars'
--

--
-- Dumping routines for database 'ibrahim_azzouz_cars'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 13:36:36
