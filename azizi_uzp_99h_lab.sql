-- MySQL dump 10.13  Distrib 5.1.73, for redhat-linux-gnu (x86_64)
--
-- Host: localhost    Database: azizi_uzp_99h_lab
-- ------------------------------------------------------
-- Server version	5.1.73

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
-- Table structure for table `ast_result`
--

DROP TABLE IF EXISTS `ast_result`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ast_result` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate45_id` int(11) NOT NULL,
  `drug` varchar(9) NOT NULL,
  `value` int(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate_drug` (`plate45_id`,`drug`),
  CONSTRAINT `fk_plate45_id` FOREIGN KEY (`plate45_id`) REFERENCES `plate45` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `biochemical_test`
--

DROP TABLE IF EXISTS `biochemical_test`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biochemical_test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate2_id` int(11) NOT NULL,
  `media` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media` (`media`),
  KEY `fk_plate2_id` (`plate2_id`),
  CONSTRAINT `fk_plate2_id` FOREIGN KEY (`plate2_id`) REFERENCES `plate2` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `biochemical_test_results`
--

DROP TABLE IF EXISTS `biochemical_test_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `biochemical_test_results` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_id` int(11) NOT NULL,
  `test` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `observ_type` varchar(50) NOT NULL,
  `observ_value` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_biochemical_test` (`media_id`,`test`,`observ_type`),
  CONSTRAINT `fk_media_id` FOREIGN KEY (`media_id`) REFERENCES `biochemical_test` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `broth_assoc`
--

DROP TABLE IF EXISTS `broth_assoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `broth_assoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `field_sample_id` int(11) NOT NULL,
  `broth_sample` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `broth_sample` (`broth_sample`),
  KEY `field_sample_id` (`field_sample_id`,`user`),
  CONSTRAINT `broth_assoc_ibfk_1` FOREIGN KEY (`field_sample_id`) REFERENCES `received_samples` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `colonies`
--

DROP TABLE IF EXISTS `colonies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `colonies` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `mcconky_plate_id` int(11) NOT NULL,
  `colony` varchar(9) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `box` varchar(10) DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL,
  `pos_saved_by` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `colony` (`colony`),
  KEY `mcconky_plate_id` (`mcconky_plate_id`),
  CONSTRAINT `colonies_ibfk_1` FOREIGN KEY (`mcconky_plate_id`) REFERENCES `mcconky_assoc` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dna_eppendorfs`
--

DROP TABLE IF EXISTS `dna_eppendorfs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `dna_eppendorfs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate6_id` int(11) NOT NULL,
  `eppendorf` varchar(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dna` varchar(9) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`eppendorf`),
  UNIQUE KEY `plate6_id` (`plate6_id`),
  UNIQUE KEY `dna` (`dna`),
  CONSTRAINT `fk_plate6_id` FOREIGN KEY (`plate6_id`) REFERENCES `plate6` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mcconky_assoc`
--

DROP TABLE IF EXISTS `mcconky_assoc`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mcconky_assoc` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `broth_sample_id` int(11) NOT NULL,
  `plate1_barcode` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `media_used` varchar(20) NOT NULL,
  `user` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `broth_sample_id` (`broth_sample_id`),
  UNIQUE KEY `plate1_barcode` (`plate1_barcode`),
  UNIQUE KEY `broth_sample_id_2` (`broth_sample_id`,`plate1_barcode`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plate2`
--

DROP TABLE IF EXISTS `plate2`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plate2` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`plate`),
  KEY `fk_plate2_colony_id` (`colony_id`),
  CONSTRAINT `fk_plate2_colony_id` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plate3`
--

DROP TABLE IF EXISTS `plate3`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plate3` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`plate`),
  KEY `fk_plate3_colony_id` (`colony_id`),
  CONSTRAINT `fk_plate3_colony_id` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plate45`
--

DROP TABLE IF EXISTS `plate45`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plate45` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plate3_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `number` int(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate45` (`plate`),
  UNIQUE KEY `plate_number` (`plate3_id`,`number`),
  CONSTRAINT `fk_plate45_plate3_id` FOREIGN KEY (`plate3_id`) REFERENCES `plate3` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `plate6`
--

DROP TABLE IF EXISTS `plate6`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `plate6` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `colony_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plate` (`plate`),
  KEY `fk_plate6_colony_id` (`colony_id`),
  CONSTRAINT `fk_plate6_colony_id` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `received_samples`
--

DROP TABLE IF EXISTS `received_samples`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `received_samples` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sample` varchar(9) NOT NULL,
  `datetime_received` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `sample` (`sample`),
  KEY `user` (`user`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=latin1;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  `data` text COLLATE latin1_bin,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_id` (`session_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2015-07-03 12:51:39
