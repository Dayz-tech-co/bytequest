-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 28, 2025 at 11:25 AM
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
-- Database: `bytequest`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `reset_otp` varchar(10) DEFAULT NULL,
  `otp_expiry` datetime DEFAULT NULL,
  `role` varchar(50) DEFAULT 'admin',
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`admin_id`, `name`, `email`, `password`, `created_at`, `reset_otp`, `otp_expiry`, `role`) VALUES
(1, 'Zayd oduola', 'ZaydAdmin@Byte.blog', '$2y$10$oP1Ymn4jOjlQuOklFpP3FeSflFll/ZcA7RPgt6FQoYdYlALnlFP5K', '2025-04-17 11:58:30', NULL, NULL, 'admin'),
(2, 'oyinlola oduola', 'dart1oyinlola@gmail.com', '$2y$10$5hIr7XZt9Tx0fMxTJn8O9uQHrtAVkdFvds3tBFddyPe4zf.InNX0e', '2025-04-17 12:04:31', NULL, NULL, 'admin'),
(3, 'elsulzee oduola', 'elsulzeeoduola@gmail.com', '$2y$10$LDgey2SbtW3dm9H8t04CFubkDq8.GKh.VLB/eWWrZB9aSYF.bR5Ze', '2025-04-17 12:07:20', NULL, NULL, 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `blogs`
--

DROP TABLE IF EXISTS `blogs`;
CREATE TABLE IF NOT EXISTS `blogs` (
  `blog_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `blogs`
--

INSERT INTO `blogs` (`blog_id`, `title`, `content`, `image`, `author_id`, `created_at`, `updated_at`) VALUES
(4, 'MY CAREER JOURNEY && PATH', 'WHY CHOOSING PROGRAMMING || SOFTWARE DEVELOPMENT?', NULL, 1, '2025-04-11 15:47:29', '2025-04-11 15:47:29'),
(3, 'MY SIDE HUSTLE', 'HOW I STARTED POS, THEN HOW IT LATER ENDED', NULL, 3, '2025-04-11 13:40:54', '2025-04-11 15:27:23'),
(7, 'HOW TO WORK  DAILY', 'DAILY SURVIVOR', NULL, 3, '2025-04-17 11:16:52', '2025-04-17 11:21:23'),
(6, 'MY LOVE FOR FOOTBALL', 'MY PURPOSE FOR STARTING AND WHY DID I ENDED IT?', NULL, 1, '2025-04-11 15:49:01', '2025-04-11 16:01:30'),
(8, 'MARRIAGE AFFAIRS', 'CAN MARRIAGE COME IN WHEN THE EXPECTED INCOME STARTED FLOWING IN ?', NULL, 2, '2025-04-17 11:17:52', '2025-04-17 11:23:10');

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `comment_id` int NOT NULL AUTO_INCREMENT,
  `blog_id` int NOT NULL,
  `author_id` int NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  PRIMARY KEY (`comment_id`),
  KEY `blog_id` (`blog_id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` (`comment_id`, `blog_id`, `author_id`, `comment`, `created_at`, `status`) VALUES
(1, 4, 1, 'So tell us why did you chose progrmming and softwaare development amidst all profession? UPDATED.', '2025-04-12 11:45:32', 'approved'),
(2, 2, 2, 'So tell us how do we efficiently study Arabic? UPDATED', '2025-04-12 11:48:51', 'approved'),
(7, 8, 3, 'Please Share the insightful points for  we the singles', '2025-04-17 10:41:54', 'pending'),
(4, 1, 1, 'We Are Also Curious To Know About Your Footall Journey.UPDATED', '2025-04-12 11:53:22', 'approved'),
(6, 1, 1, 'DID YOUR FOOTBALL JOURNEY ENDED WELL? UPDATED', '2025-04-12 11:56:19', 'rejected');

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

DROP TABLE IF EXISTS `posts`;
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `content` text,
  `image_url` text,
  `author_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `author_id` (`author_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `email_verified` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `otp` varchar(6) DEFAULT NULL,
  `otp_expiration` datetime DEFAULT NULL,
  `status` enum('active','suspended','banned','pending') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email_2` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `email`, `password`, `role`, `email_verified`, `created_at`, `otp`, `otp_expiration`, `status`) VALUES
(1, 'zayd Dev', 'zayd_dev', 'ZaydDev@gmail.com', '$2y$10$35hVqvUalLX4.0H/xTxqEuzQa8V.FcwIpvFJTvCcr/ZMmyKLerA0e', 'user', 0, '2025-04-09 14:02:09', NULL, NULL, 'active'),
(2, 'Dyaz Dev', 'Dyaz_dev', 'DyazDev@gmail.com', '$2y$10$lZRYXZMBlFxnW20auoUYbeBgNvVWwdQCcllDYEtsWiHmMrMTBUWH.', 'user', 0, '2025-04-09 14:03:36', NULL, NULL, 'suspended'),
(3, 'Elsulzee Dev', 'Elsulzee_Dev', 'ElsulzeeDev@gmail.com', '$2y$10$LYZbTHVmmozM0rCB1B4c7eVx.IoMdXWNBwD.bck9oHc8wEu7cPq0m', 'user', 0, '2025-04-09 14:04:50', '631822', '2025-04-15 02:30:22', 'banned'),
(5, 'oduola son', 'still_oduola', 'oduolason@gmail.com', '$2y$10$btQX.ctjAR4z.xxM9CdmoO3qhJPz1j2KkYHLaTwsw618bmMoCeq4S', 'user', 0, '2025-04-17 09:40:23', '179020', '2025-04-17 09:53:14', 'active');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
