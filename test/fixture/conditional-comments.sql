-- mysqldump-php https://github.com/ifsnop/mysqldump-php
--
-- Host: 127.0.0.1:3306	Database: th_staging
-- ------------------------------------------------------
-- Server version 	5.5.5-10.1.43-MariaDB-0ubuntu0.18.04.1
-- Date: Fri, 17 Jan 2020 12:47:27 +0000

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `address_type`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_type` (
                                `id` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
                                `native_name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                                PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `address_type`
--

LOCK TABLES `address_type` WRITE;
/*!40000 ALTER TABLE `address_type` DISABLE KEYS */;
SET autocommit=0;
INSERT INTO `address_type` VALUES (1,'residency'),(2,'ship;ping'),(3,'invoice');
/*!40000 ALTER TABLE `address_type` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;


SELECT /*+ NO_BKA(t1) */ * FROM address_type t1;


-- Dumped table `address_type` with 3 row(s)
--

--
-- Table structure for table `address_type_i18n`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `address_type_i18n` (
                                     `id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
                                     `language_id` tinyint(3) unsigned NOT NULL,
                                     `address_type_id` tinyint(3) unsigned NOT NULL,
                                     `name` varchar(45) COLLATE utf8_unicode_ci NOT NULL,
                                     PRIMARY KEY (`id`),
                                     KEY `fk_address_type_i18n_language1_idx` (`language_id`),
                                     KEY `fk_address_type_i18n_address_type1_idx` (`address_type_id`),
                                     CONSTRAINT `address_type_i18n_ibfk_1` FOREIGN KEY (`language_id`) REFERENCES `language` (`id`) ON UPDATE CASCADE,
                                     CONSTRAINT `address_type_i18n_ibfk_2` FOREIGN KEY (`address_type_id`) REFERENCES `address_type` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
COMMIT;
