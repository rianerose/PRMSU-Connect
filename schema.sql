-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Oct 31, 2025 at 04:30 AM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `announcementdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcement`
--

DROP TABLE IF EXISTS `announcement`;
CREATE TABLE IF NOT EXISTS `announcement` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `publish_at` datetime DEFAULT NULL,
  `datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'General',
  `college` enum('ALL','CCIT','CIT','COE','CAS','CBAPA','CON','CTHM','CTE','CCJ') COLLATE utf8mb4_unicode_ci DEFAULT 'ALL',
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_announcement_title` (`title`),
  KEY `idx_announcement_college` (`college`),
  KEY `fk_ann_creator` (`created_by`),
  KEY `idx_college_datetime` (`college`,`datetime`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

DROP TABLE IF EXISTS `event`;
CREATE TABLE IF NOT EXISTS `event` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL,
  `location` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'General',
  `college` enum('ALL','CCIT','CIT','COE','CAS','CBAPA','CON','CTHM','CTE','CCJ') COLLATE utf8mb4_unicode_ci DEFAULT 'ALL',
  `publish_at` datetime DEFAULT NULL,
  `created_by` int UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `participation_scope` enum('ALL','COLLEGE') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ALL',
  PRIMARY KEY (`id`),
  KEY `idx_event_datetime` (`datetime`),
  KEY `idx_event_college` (`college`),
  KEY `fk_event_creator` (`created_by`),
  KEY `idx_publish_datetime` (`publish_at`,`datetime`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- Table structure for table `eventparticipation`
--

DROP TABLE IF EXISTS `eventparticipation`;
CREATE TABLE IF NOT EXISTS `eventparticipation` (
  `user_id` int NOT NULL,
  `event_id` int NOT NULL,
  `status` enum('going','not_going') COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`,`event_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `role` enum('student','admin') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'student',
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `college` enum('ALL','CCIT','CIT','COE','CAS','CBAPA','CON','CTHM','CTE','CCJ') COLLATE utf8mb4_unicode_ci DEFAULT 'ALL',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  UNIQUE KEY `uniq_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `role`, `email`, `username`, `password`, `full_name`, `college`, `created_at`) VALUES
(1, 'admin', 'admin@school.local', 'admin', '$2b$12$MkUVF4bhvzYYD.W5SxlzL./goY/4p4wvGjw62574goOTtjo4blCRi', 'Admin User', 'ALL', '2025-10-29 10:04:23'),
(2, 'student', 'alex@student.local', 'alex', '$2b$12$XeqY7yqNApj7DwDwm.8NU.sjb02tYhmQk4UaE0BNA6cso5zGItwnO', 'Alex Student', 'CCIT', '2025-10-29 10:04:23'),
(3, 'student', 'beth@student.local', 'beth', '$2b$12$XeqY7yqNApj7DwDwm.8NU.sjb02tYhmQk4UaE0BNA6cso5zGItwnO', 'Beth Learner', 'CIT', '2025-10-29 10:04:23'),
(4, 'student', 'rosendoadriane50@gmail.com', 'Rianesaur', '$2y$10$vN4ndmmETExlI7Bywrzo7ecAwm5lzdXbipOFZ.TeKltKCKVoFLw3q', 'Adriane J. Rosendo', 'CCIT', '2025-10-29 10:18:04'),
(5, 'student', 'justinebalmaceda@gmail.com', 'justine', '$2y$10$DVGUb9U1atUMGydkiu1d3OyF91vmxzwYjTWjh/wbzxEqQ4Lazd8h6', 'Justine Balmaceda', 'CCIT', '2025-10-31 03:12:38'),
(6, 'student', 'mariah@gmail.com', 'mariah', '$2y$10$y1HI7o7KAl8Wuq5Gdxhe/u7qUyb/iZlTLEFsvGl1orMd0KqUuXMbG', 'Mariah', 'CON', '2025-10-31 03:18:34');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcement`
--
ALTER TABLE `announcement` ADD FULLTEXT KEY `ft_announcement_title_desc` (`title`,`description`);

--
-- Indexes for table `event`
--
ALTER TABLE `event` ADD FULLTEXT KEY `ft_event_title_desc` (`title`,`description`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcement`
--
ALTER TABLE `announcement`
  ADD CONSTRAINT `fk_ann_creator` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `event`
--
ALTER TABLE `event`
  ADD CONSTRAINT `fk_event_creator` FOREIGN KEY (`created_by`) REFERENCES `user` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
