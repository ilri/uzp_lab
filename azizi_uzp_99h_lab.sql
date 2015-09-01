-- phpMyAdmin SQL Dump
-- version 4.4.13.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 01, 2015 at 03:41 PM
-- Server version: 5.6.24
-- PHP Version: 5.6.11

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `azizi_uzp_99h_lab`
--

-- --------------------------------------------------------

--
-- Table structure for table `aliquots`
--

CREATE TABLE IF NOT EXISTS `aliquots` (
  `id` int(11) NOT NULL,
  `label` varchar(9) COLLATE latin1_bin NOT NULL,
  `parent_sample` int(11) NOT NULL,
  `aliquot_number` tinyint(4) NOT NULL,
  `tray` varchar(11) COLLATE latin1_bin DEFAULT NULL,
  `position` tinyint(4) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `aliq_animals`
--

CREATE TABLE IF NOT EXISTS `aliq_animals` (
  `id` int(11) NOT NULL,
  `animal_id` varchar(20) COLLATE latin1_bin NOT NULL,
  `organism` enum('Sheep','Goat') COLLATE latin1_bin DEFAULT NULL,
  `age` varchar(15) COLLATE latin1_bin DEFAULT NULL,
  `sex` enum('Male','Female') COLLATE latin1_bin DEFAULT NULL,
  `prev_tag` varchar(10) COLLATE latin1_bin DEFAULT NULL,
  `location` varchar(35) COLLATE latin1_bin NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `aliq_samples`
--

CREATE TABLE IF NOT EXISTS `aliq_samples` (
  `id` int(11) NOT NULL,
  `label` varchar(9) COLLATE latin1_bin NOT NULL,
  `adding_timestamp` datetime NOT NULL,
  `comments` varchar(1000) COLLATE latin1_bin DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

-- --------------------------------------------------------

--
-- Table structure for table `ast_result`
--

CREATE TABLE IF NOT EXISTS `ast_result` (
  `id` int(11) NOT NULL,
  `plate45_id` int(11) NOT NULL,
  `drug` varchar(9) NOT NULL,
  `value` int(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `biochemical_test`
--

CREATE TABLE IF NOT EXISTS `biochemical_test` (
  `id` int(11) NOT NULL,
  `mh2_id` int(11) NOT NULL,
  `media` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `biochemical_test_results`
--

CREATE TABLE IF NOT EXISTS `biochemical_test_results` (
  `id` int(11) NOT NULL,
  `media_id` int(11) NOT NULL,
  `test` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `observ_type` varchar(50) NOT NULL,
  `observ_value` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `broth_assoc`
--

CREATE TABLE IF NOT EXISTS `broth_assoc` (
  `id` int(11) NOT NULL,
  `field_sample_id` int(11) NOT NULL,
  `broth_sample` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_bootsock_assoc`
--

CREATE TABLE IF NOT EXISTS `campy_bootsock_assoc` (
  `id` int(11) NOT NULL,
  `bootsock_id` int(11) NOT NULL,
  `daughter_sample` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_colonies`
--

CREATE TABLE IF NOT EXISTS `campy_colonies` (
  `id` int(11) NOT NULL,
  `colony` varchar(9) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `box` varchar(10) DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_cryovials`
--

CREATE TABLE IF NOT EXISTS `campy_cryovials` (
  `id` int(11) NOT NULL,
  `falcon_id` int(11) NOT NULL,
  `cryovial` varchar(9) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `box` varchar(10) DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_mccda_assoc`
--

CREATE TABLE IF NOT EXISTS `campy_mccda_assoc` (
  `id` int(11) NOT NULL,
  `falcon_id` int(11) NOT NULL,
  `plate1_barcode` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_mccda_growth`
--

CREATE TABLE IF NOT EXISTS `campy_mccda_growth` (
  `id` int(11) NOT NULL,
  `mccda_plate_id` int(11) NOT NULL,
  `am_plate` varchar(9) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `campy_received_bootsocks`
--

CREATE TABLE IF NOT EXISTS `campy_received_bootsocks` (
  `id` int(11) NOT NULL,
  `sample` varchar(9) NOT NULL,
  `datetime_received` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `for_sequencing` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `colonies`
--

CREATE TABLE IF NOT EXISTS `colonies` (
  `id` int(11) NOT NULL,
  `mcconky_plate_id` int(11) NOT NULL,
  `colony` varchar(9) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `box` varchar(10) DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL,
  `pos_saved_by` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `dna_eppendorfs`
--

CREATE TABLE IF NOT EXISTS `dna_eppendorfs` (
  `id` int(11) NOT NULL,
  `mh6_id` int(11) NOT NULL,
  `eppendorf` varchar(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `dna` varchar(9) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mcconky_assoc`
--

CREATE TABLE IF NOT EXISTS `mcconky_assoc` (
  `id` int(11) NOT NULL,
  `broth_sample_id` int(11) NOT NULL,
  `plate1_barcode` varchar(9) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `media_used` varchar(20) NOT NULL,
  `user` varchar(20) NOT NULL,
  `no_qtr_colonies` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mh2_assoc`
--

CREATE TABLE IF NOT EXISTS `mh2_assoc` (
  `id` int(11) NOT NULL,
  `plate2_id` int(11) NOT NULL,
  `mh` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mh3_assoc`
--

CREATE TABLE IF NOT EXISTS `mh3_assoc` (
  `id` int(11) NOT NULL,
  `plate3_id` int(11) NOT NULL,
  `mh` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mh6_assoc`
--

CREATE TABLE IF NOT EXISTS `mh6_assoc` (
  `id` int(11) NOT NULL,
  `plate6_id` int(11) NOT NULL,
  `mh` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mh_assoc`
--

CREATE TABLE IF NOT EXISTS `mh_assoc` (
  `id` int(11) NOT NULL,
  `colony_id` int(11) NOT NULL,
  `mh` varchar(50) NOT NULL,
  `user` varchar(50) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `mh_vial`
--

CREATE TABLE IF NOT EXISTS `mh_vial` (
  `id` int(11) NOT NULL,
  `mh_id` int(11) NOT NULL,
  `mh_vial` varchar(50) NOT NULL,
  `datetime_saved` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `box` varchar(50) DEFAULT NULL,
  `position_in_box` int(11) DEFAULT NULL,
  `pos_saved_by` varchar(50) DEFAULT NULL,
  `user` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plate2`
--

CREATE TABLE IF NOT EXISTS `plate2` (
  `id` int(11) NOT NULL,
  `mh_vial_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plate3`
--

CREATE TABLE IF NOT EXISTS `plate3` (
  `id` int(11) NOT NULL,
  `mh_vial_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plate6`
--

CREATE TABLE IF NOT EXISTS `plate6` (
  `id` int(11) NOT NULL,
  `mh_vial_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plate45`
--

CREATE TABLE IF NOT EXISTS `plate45` (
  `id` int(11) NOT NULL,
  `mh3_id` int(11) NOT NULL,
  `plate` varchar(9) NOT NULL,
  `number` int(11) NOT NULL,
  `user` varchar(20) NOT NULL,
  `datetime_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `received_samples`
--

CREATE TABLE IF NOT EXISTS `received_samples` (
  `id` int(11) NOT NULL,
  `sample` varchar(9) NOT NULL,
  `datetime_received` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(20) NOT NULL,
  `for_sequencing` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE IF NOT EXISTS `sessions` (
  `id` int(11) NOT NULL,
  `session_id` varchar(255) COLLATE latin1_bin DEFAULT NULL,
  `data` text COLLATE latin1_bin,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_bin;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aliquots`
--
ALTER TABLE `aliquots`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`),
  ADD UNIQUE KEY `tray` (`tray`,`position`),
  ADD KEY `parent_sample` (`parent_sample`);

--
-- Indexes for table `aliq_animals`
--
ALTER TABLE `aliq_animals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `animal_id` (`animal_id`);

--
-- Indexes for table `aliq_samples`
--
ALTER TABLE `aliq_samples`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `label` (`label`);

--
-- Indexes for table `ast_result`
--
ALTER TABLE `ast_result`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate_drug` (`plate45_id`,`drug`);

--
-- Indexes for table `biochemical_test`
--
ALTER TABLE `biochemical_test`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `media` (`media`),
  ADD KEY `fk_plate2_id` (`mh2_id`);

--
-- Indexes for table `biochemical_test_results`
--
ALTER TABLE `biochemical_test_results`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_biochemical_test` (`media_id`,`test`,`observ_type`);

--
-- Indexes for table `broth_assoc`
--
ALTER TABLE `broth_assoc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `broth_sample` (`broth_sample`),
  ADD KEY `field_sample_id` (`field_sample_id`,`user`);

--
-- Indexes for table `campy_bootsock_assoc`
--
ALTER TABLE `campy_bootsock_assoc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `broth_sample` (`daughter_sample`),
  ADD KEY `field_sample_id` (`bootsock_id`,`user`);

--
-- Indexes for table `campy_colonies`
--
ALTER TABLE `campy_colonies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colony` (`colony`);

--
-- Indexes for table `campy_cryovials`
--
ALTER TABLE `campy_cryovials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colony` (`cryovial`),
  ADD UNIQUE KEY `box` (`box`,`position_in_box`),
  ADD KEY `falcon_id` (`falcon_id`);

--
-- Indexes for table `campy_mccda_assoc`
--
ALTER TABLE `campy_mccda_assoc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `broth_sample` (`plate1_barcode`),
  ADD KEY `field_sample_id` (`falcon_id`,`user`);

--
-- Indexes for table `campy_mccda_growth`
--
ALTER TABLE `campy_mccda_growth`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colony` (`am_plate`),
  ADD UNIQUE KEY `mccda_plate_id` (`mccda_plate_id`,`am_plate`),
  ADD KEY `mcconky_plate_id` (`mccda_plate_id`);

--
-- Indexes for table `campy_received_bootsocks`
--
ALTER TABLE `campy_received_bootsocks`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sample` (`sample`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `colonies`
--
ALTER TABLE `colonies`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `colony` (`colony`),
  ADD KEY `mcconky_plate_id` (`mcconky_plate_id`);

--
-- Indexes for table `dna_eppendorfs`
--
ALTER TABLE `dna_eppendorfs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`eppendorf`),
  ADD UNIQUE KEY `plate6_id` (`mh6_id`),
  ADD UNIQUE KEY `dna` (`dna`);

--
-- Indexes for table `mcconky_assoc`
--
ALTER TABLE `mcconky_assoc`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `broth_sample_id` (`broth_sample_id`),
  ADD UNIQUE KEY `plate1_barcode` (`plate1_barcode`),
  ADD UNIQUE KEY `broth_sample_id_2` (`broth_sample_id`,`plate1_barcode`);

--
-- Indexes for table `mh2_assoc`
--
ALTER TABLE `mh2_assoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plate_2_id` (`plate2_id`);

--
-- Indexes for table `mh3_assoc`
--
ALTER TABLE `mh3_assoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plate3_id` (`plate3_id`);

--
-- Indexes for table `mh6_assoc`
--
ALTER TABLE `mh6_assoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plate_6_id` (`plate6_id`);

--
-- Indexes for table `mh_assoc`
--
ALTER TABLE `mh_assoc`
  ADD PRIMARY KEY (`id`),
  ADD KEY `colony_id` (`colony_id`);

--
-- Indexes for table `mh_vial`
--
ALTER TABLE `mh_vial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `mh_id` (`mh_id`);

--
-- Indexes for table `plate2`
--
ALTER TABLE `plate2`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`),
  ADD KEY `fk_plate2_colony_id` (`mh_vial_id`);

--
-- Indexes for table `plate3`
--
ALTER TABLE `plate3`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`),
  ADD KEY `fk_plate3_colony_id` (`mh_vial_id`);

--
-- Indexes for table `plate6`
--
ALTER TABLE `plate6`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate` (`plate`),
  ADD KEY `fk_plate6_colony_id` (`mh_vial_id`);

--
-- Indexes for table `plate45`
--
ALTER TABLE `plate45`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `plate45` (`plate`),
  ADD UNIQUE KEY `plate_number` (`mh3_id`,`number`);

--
-- Indexes for table `received_samples`
--
ALTER TABLE `received_samples`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sample` (`sample`),
  ADD KEY `user` (`user`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_id` (`session_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aliquots`
--
ALTER TABLE `aliquots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `aliq_animals`
--
ALTER TABLE `aliq_animals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `aliq_samples`
--
ALTER TABLE `aliq_samples`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `ast_result`
--
ALTER TABLE `ast_result`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `biochemical_test`
--
ALTER TABLE `biochemical_test`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `biochemical_test_results`
--
ALTER TABLE `biochemical_test_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `broth_assoc`
--
ALTER TABLE `broth_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_bootsock_assoc`
--
ALTER TABLE `campy_bootsock_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_colonies`
--
ALTER TABLE `campy_colonies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_cryovials`
--
ALTER TABLE `campy_cryovials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_mccda_assoc`
--
ALTER TABLE `campy_mccda_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_mccda_growth`
--
ALTER TABLE `campy_mccda_growth`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `campy_received_bootsocks`
--
ALTER TABLE `campy_received_bootsocks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `colonies`
--
ALTER TABLE `colonies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `dna_eppendorfs`
--
ALTER TABLE `dna_eppendorfs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mcconky_assoc`
--
ALTER TABLE `mcconky_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mh2_assoc`
--
ALTER TABLE `mh2_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mh3_assoc`
--
ALTER TABLE `mh3_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mh6_assoc`
--
ALTER TABLE `mh6_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mh_assoc`
--
ALTER TABLE `mh_assoc`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `mh_vial`
--
ALTER TABLE `mh_vial`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `plate2`
--
ALTER TABLE `plate2`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `plate3`
--
ALTER TABLE `plate3`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `plate6`
--
ALTER TABLE `plate6`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `plate45`
--
ALTER TABLE `plate45`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `received_samples`
--
ALTER TABLE `received_samples`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `aliquots`
--
ALTER TABLE `aliquots`
  ADD CONSTRAINT `aliquots_ibfk_1` FOREIGN KEY (`parent_sample`) REFERENCES `aliq_samples` (`id`);

--
-- Constraints for table `ast_result`
--
ALTER TABLE `ast_result`
  ADD CONSTRAINT `fk_plate45_id` FOREIGN KEY (`plate45_id`) REFERENCES `plate45` (`id`);

--
-- Constraints for table `biochemical_test`
--
ALTER TABLE `biochemical_test`
  ADD CONSTRAINT `biochemical_test_ibfk_1` FOREIGN KEY (`mh2_id`) REFERENCES `mh2_assoc` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `biochemical_test_results`
--
ALTER TABLE `biochemical_test_results`
  ADD CONSTRAINT `fk_media_id` FOREIGN KEY (`media_id`) REFERENCES `biochemical_test` (`id`);

--
-- Constraints for table `broth_assoc`
--
ALTER TABLE `broth_assoc`
  ADD CONSTRAINT `broth_assoc_ibfk_1` FOREIGN KEY (`field_sample_id`) REFERENCES `received_samples` (`id`);

--
-- Constraints for table `campy_bootsock_assoc`
--
ALTER TABLE `campy_bootsock_assoc`
  ADD CONSTRAINT `campy_bootsock_assoc_ibfk_1` FOREIGN KEY (`bootsock_id`) REFERENCES `campy_received_bootsocks` (`id`);

--
-- Constraints for table `campy_cryovials`
--
ALTER TABLE `campy_cryovials`
  ADD CONSTRAINT `campy_cryovials_ibfk_1` FOREIGN KEY (`falcon_id`) REFERENCES `campy_bootsock_assoc` (`id`);

--
-- Constraints for table `campy_mccda_assoc`
--
ALTER TABLE `campy_mccda_assoc`
  ADD CONSTRAINT `campy_mccda_assoc_ibfk_1` FOREIGN KEY (`falcon_id`) REFERENCES `campy_bootsock_assoc` (`id`);

--
-- Constraints for table `campy_mccda_growth`
--
ALTER TABLE `campy_mccda_growth`
  ADD CONSTRAINT `campy_mccda_growth_ibfk_1` FOREIGN KEY (`mccda_plate_id`) REFERENCES `campy_mccda_assoc` (`id`);

--
-- Constraints for table `colonies`
--
ALTER TABLE `colonies`
  ADD CONSTRAINT `colonies_ibfk_1` FOREIGN KEY (`mcconky_plate_id`) REFERENCES `mcconky_assoc` (`id`);

--
-- Constraints for table `dna_eppendorfs`
--
ALTER TABLE `dna_eppendorfs`
  ADD CONSTRAINT `dna_eppendorfs_ibfk_1` FOREIGN KEY (`mh6_id`) REFERENCES `mh6_assoc` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mcconky_assoc`
--
ALTER TABLE `mcconky_assoc`
  ADD CONSTRAINT `mcconky_assoc_ibfk_1` FOREIGN KEY (`broth_sample_id`) REFERENCES `broth_assoc` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mh2_assoc`
--
ALTER TABLE `mh2_assoc`
  ADD CONSTRAINT `mh2_assoc_ibfk_1` FOREIGN KEY (`plate2_id`) REFERENCES `plate2` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mh3_assoc`
--
ALTER TABLE `mh3_assoc`
  ADD CONSTRAINT `mh3_assoc_ibfk_1` FOREIGN KEY (`plate3_id`) REFERENCES `plate3` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mh6_assoc`
--
ALTER TABLE `mh6_assoc`
  ADD CONSTRAINT `mh6_assoc_ibfk_1` FOREIGN KEY (`plate6_id`) REFERENCES `plate6` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mh_assoc`
--
ALTER TABLE `mh_assoc`
  ADD CONSTRAINT `mh_assoc_ibfk_1` FOREIGN KEY (`colony_id`) REFERENCES `colonies` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `mh_vial`
--
ALTER TABLE `mh_vial`
  ADD CONSTRAINT `mh_vial_ibfk_1` FOREIGN KEY (`mh_id`) REFERENCES `mh_assoc` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `plate2`
--
ALTER TABLE `plate2`
  ADD CONSTRAINT `plate2_ibfk_1` FOREIGN KEY (`mh_vial_id`) REFERENCES `mh_vial` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `plate3`
--
ALTER TABLE `plate3`
  ADD CONSTRAINT `plate3_ibfk_1` FOREIGN KEY (`mh_vial_id`) REFERENCES `mh_vial` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `plate6`
--
ALTER TABLE `plate6`
  ADD CONSTRAINT `plate6_ibfk_1` FOREIGN KEY (`mh_vial_id`) REFERENCES `mh_vial` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `plate45`
--
ALTER TABLE `plate45`
  ADD CONSTRAINT `plate45_ibfk_1` FOREIGN KEY (`mh3_id`) REFERENCES `mh3_assoc` (`id`) ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
