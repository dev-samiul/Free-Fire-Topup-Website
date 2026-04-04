-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 04, 2026 at 06:34 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 7.3.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dream_topup`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`) VALUES
(1, 'mraiprime', '$2y$10$DwWX04NWVR2bhzpKu9Fw7uZRnGu1KCY/Jjz79.6OEeUZQxpAy.vli'),
(2, 'sameulislam369', '$2y$10$Y6V3BOsEihWnMsIZRPq1O.ngpRlPqG/5z0Vb7KUuVIp0vmHdVKIgW');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `priority` int(11) DEFAULT '0',
  `slot` int(11) DEFAULT '0',
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `deposits`
--

CREATE TABLE `deposits` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `method` varchar(50) DEFAULT NULL,
  `wallet_number` varchar(50) DEFAULT NULL,
  `trx_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'uid',
  `description` text,
  `image` varchar(255) DEFAULT NULL,
  `api_key` varchar(255) DEFAULT NULL,
  `category_id` int(11) DEFAULT '0',
  `status` enum('active','inactive') DEFAULT 'active',
  `hint_text` varchar(255) DEFAULT NULL,
  `check_uid` tinyint(4) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `name`, `type`, `description`, `image`, `api_key`, `category_id`, `status`, `hint_text`, `check_uid`) VALUES
(2, 'Free Fire Topup (BD)', 'uid', 'â¦¿ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ ID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡à¥¤\r\n\r\nâ¦¿ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ Red TopUp BD à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼à¥¤\r\n\r\nâ¦¿ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦¸à¦¾à¦ªà§‹à¦°à§à¦Ÿà§‡ à¦®à§‡à¦¸à§‡à¦œ à¦¦à¦¿à¦¨à¥¤\r\n\r\nâ¦¿ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ à¥¤', 'uploads/game_1770827701.jpg', 'TPBD-1B87AB7E6594F330', 1, 'active', '', 1),
(3, 'Weekly / Monthly', 'uid', 'â—‹ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâ—‹ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\r\\\\\\\\nâ—‹ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâ—‹ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ ', 'uploads/game_1770827744.jpg', '', 1, 'active', '', 1),
(4, 'Level Up Pass', 'uid', 'âœ”ï¸ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâŒ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ âœ˜', 'uploads/game_1770827764.jpg', '', 1, 'active', '', 1),
(5, 'Weekly Lite [BD]', 'uid', 'âœ”ï¸ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâŒ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ âœ˜', 'uploads/game_1770827784.jpg', '', 1, 'active', '', 1),
(6, 'E badge Evo_Access_UID', 'uid', 'âœ”ï¸ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\r\\\\\\\\nâœ”ï¸ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\r\\\\\\\\nâŒ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ âœ˜', 'uploads/game_1770827804.jpg', '', 1, 'active', '', 1),
(7, 'Indonesia Topup', 'uid', 'âœ”ï¸ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Indonesia à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\nâœ”ï¸ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\nâœ”ï¸ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\nâŒ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ âœ˜', 'uploads/game_1770666512.png', '', 1, 'inactive', NULL, 1),
(8, 'FREE FIRE AUTO LIKE', 'uid', 'âœ”ï¸ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ UID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡ à¥¤\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\nâœ”ï¸ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼ à¥¤\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\nâœ”ï¸ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦šà§‡à¦• à¦•à¦°à¦¾à¦° à¦œà¦¨à§à¦¯ ID Pass à¦¦à¦¿à¦¤à§‡ à¦¹à¦¬à§‡ à¥¤\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\r\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\nâŒ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ âœ˜', 'uploads/game_1770666618.png', '', 2, 'active', NULL, 1),
(9, 'YouTube Premium', 'subscription', 'â¦¿ à¦¶à§à¦§à§à¦®à¦¾à¦¤à§à¦° Bangladesh à¦¸à¦¾à¦°à§à¦­à¦¾à¦°à§‡ ID Code à¦¦à¦¿à§Ÿà§‡ à¦Ÿà¦ª à¦†à¦ª à¦¹à¦¬à§‡à¥¤\r\n\r\nâ¦¿ Player ID Code à¦­à§à¦² à¦¦à¦¿à§Ÿà§‡ Diamond à¦¨à¦¾ à¦ªà§‡à¦²à§‡ Red TopUp BD à¦•à¦°à§à¦¤à§ƒà¦ªà¦•à§à¦· à¦¦à¦¾à¦¯à¦¼à§€ à¦¨à¦¯à¦¼à¥¤\r\n\r\nâ¦¿ Order à¦•à¦®à¦ªà§à¦²à¦¿à¦Ÿ à¦¹à¦“à¦¯à¦¼à¦¾à¦° à¦ªà¦°à§‡à¦“ à¦†à¦‡à¦¡à¦¿à¦¤à§‡ à¦¡à¦¾à¦‡à¦®à¦¨à§à¦¡ à¦¨à¦¾ à¦—à§‡à¦²à§‡ à¦¸à¦¾à¦ªà§‹à¦°à§à¦Ÿà§‡ à¦®à§‡à¦¸à§‡à¦œ à¦¦à¦¿à¦¨à¥¤\r\n\r\nâ¦¿ à¦…à¦°à§à¦¡à¦¾à¦° Cancel à¦¹à¦²à§‡ à¦•à¦¿ à¦•à¦¾à¦°à¦£à§‡ à¦¤à¦¾ Cancel à¦¹à§Ÿà§‡à¦›à§‡ à¦¤à¦¾ à¦…à¦°à§à¦¡à¦¾à¦° à¦¹à¦¿à¦¸à§à¦Ÿà§‹à¦°à¦¿à¦¤à§‡ à¦¦à§‡à¦“à§Ÿà¦¾ à¦¥à¦¾à¦•à§‡ à¦…à¦¨à§à¦—à§à¦°à¦¹ à¦ªà§à¦°à§à¦¬à¦• à¦¦à§‡à¦–à§‡ à¦ªà§à¦¨à¦°à¦¾à§Ÿ à¦¸à¦ à¦¿à¦• à¦¤à¦¥à§à¦¯ à¦¦à¦¿à§Ÿà§‡ à¦…à¦°à§à¦¡à¦¾à¦° à¦•à¦°à¦¬à§‡à¦¨ à¥¤', 'uploads/game_1769795899.png', '', 3, 'active', 'à¦†à¦ªà¦¨à¦¾à¦° à¦‡à¦®à§‡à¦‡à¦² à¦ à¦¿à¦•à¦¾à¦¨à¦¾ à¦¦à§‡à¦¨', 0),
(10, '2 TK WEEKLY LITE', 'uid', 'â¦¿ à¦…à¦«à¦¾à¦°à¦Ÿà¦¿ à¦¸à¦®à§à¦ªà¦°à§à¦•à§‡ à¦œà¦¾à¦¨à¦¤à§‡ à¦Ÿà§‡à¦²à¦¿à¦—à§à¦°à¦¾à¦® à¦šà§à¦¯à¦¾à¦¨à§‡à¦²à§‡ à¦œà¦¯à¦¼à§‡à¦¨ à¦•à¦°à§à¦¨à¥¤ ', 'uploads/game_1770666770.png', NULL, 2, 'inactive', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `game_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `player_id` varchar(100) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `id` int(11) NOT NULL,
  `name` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `qr_image` varchar(255) DEFAULT NULL,
  `number` varchar(50) DEFAULT NULL,
  `description` text,
  `short_desc` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `unipin_code` varchar(100) DEFAULT NULL,
  `slug` varchar(255) DEFAULT NULL,
  `content` text,
  `type` varchar(50) DEFAULT 'topup',
  `status` varchar(20) DEFAULT 'active',
  `reseller_percent` int(11) DEFAULT '0',
  `check_uid` int(11) DEFAULT '0',
  `has_tutorial` int(11) DEFAULT '0',
  `input_field_name` varchar(255) DEFAULT 'Enter Player ID',
  `image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `game_id`, `name`, `price`, `unipin_code`, `slug`, `content`, `type`, `status`, `reseller_percent`, `check_uid`, `has_tutorial`, `input_field_name`, `image`) VALUES
(3, 2, '25 Diamond', '23.00', 'UPBD-Q-S-0147305', NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(4, 2, '50 Diamond', '40.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(5, 2, '115 Diamond', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(6, 2, '240 Diamond', '160.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(7, 2, '610 Diamond', '410.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(8, 2, '1240 Diamond', '810.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(9, 2, '2090 Diamond', '1410.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(10, 2, '2530 Diamond', '1600.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(11, 3, '1x Weekly', '158.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(12, 3, '1x Monthly', '770.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(13, 5, '1x Weekly Lite', '50.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(14, 5, '2x Weekly Lite', '100.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(15, 5, '3x Weekly Lite', '140.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(16, 5, '4x Weekly Lite', '185.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(17, 6, '3 Days Evo Access', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(18, 6, '7 Days Evo Access', '120.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(19, 6, '14 Days Evo Access', '230.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(20, 6, '30 Days Evo Access', '320.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(21, 4, 'Level Up Package-Level 6', '50.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(22, 4, 'Level Up Package-Level 10', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(23, 4, 'Level Up Package-Level 15', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(24, 4, 'Level Up Package-Level 20', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(25, 4, 'Level Up Package-Level 25', '80.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(26, 4, 'Level Up Package-Level 30', '110.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL),
(27, 4, 'Full Level Up (1270 Diamond)', '400.00', NULL, NULL, NULL, 'topup', 'active', 0, 0, 0, 'Enter Player ID', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_codes`
--

CREATE TABLE `product_codes` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `code` varchar(255) NOT NULL,
  `status` enum('unused','used') DEFAULT 'unused',
  `order_id` int(11) DEFAULT '0',
  `used_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `product_codes`
--

INSERT INTO `product_codes` (`id`, `product_id`, `code`, `status`, `order_id`, `used_at`) VALUES
(1, 3, 'UP-1235-8963', 'unused', 0, NULL),
(2, 4, 'UP-1236-6963', 'unused', 0, NULL),
(3, 4, 'UP-1232-6996', 'unused', 0, NULL),
(4, 5, 'UP-26-3698', 'unused', 0, NULL),
(5, 6, 'UP-26-3698', 'unused', 0, NULL),
(6, 7, 'UP-26-3698', 'unused', 0, NULL),
(7, 8, 'UP-26-3698', 'unused', 0, NULL),
(8, 9, 'UP-26-3698', 'unused', 0, NULL),
(9, 10, 'UP-26-3698', 'unused', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `redeem_codes`
--

CREATE TABLE `redeem_codes` (
  `id` int(11) NOT NULL,
  `game_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `code` varchar(100) DEFAULT NULL,
  `status` enum('active','used','expired') DEFAULT 'active',
  `order_id` int(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `value` text,
  `facebook` text,
  `instagram` text,
  `youtube` text,
  `telegram_link` text,
  `contact_email` text,
  `whatsapp_number` text,
  `fab_link` text,
  `google_client_id` text,
  `google_client_secret` text,
  `google_redirect_url` varchar(255) DEFAULT 'http://localhost/topup/login.php'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `facebook`, `instagram`, `youtube`, `telegram_link`, `contact_email`, `whatsapp_number`, `fab_link`, `google_client_id`, `google_client_secret`, `google_redirect_url`) VALUES
(1, 'fab_link', 'https://wa.me/', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(2, 'download_link', '#', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(3, 'unipin_base_url', 'https://androidartist.com/api/uctopup', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(4, 'popup_image', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(5, 'popup_link', '#', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(6, 'popup_btn_text', 'See Offer', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(7, 'popup_text', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(8, 'admin_bkash_number', '01615307596', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(9, 'admin_nagad_number', '01615307596', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(10, 'admin_rocket_number', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(11, 'add_money_video', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(12, 'site_name', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(13, 'site_title', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(14, 'home_title', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(15, 'paginate_per_page', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(16, 'currency', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(17, 'site_color', '#06A81B', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(18, 'unipin_api_key', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(19, 'uid_api_url', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(20, 'uid_api_key', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(21, 'meta_desc', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(22, 'keywords', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(23, 'whatsapp_number', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(24, 'telegram_link', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(25, 'facebook', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(26, 'instagram', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(27, 'youtube', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(28, 'contact_email', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(29, 'home_notice', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(30, 'firebase_database_url', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(31, 'firebase_api_key', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(32, 'firebase_auth_domain', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(33, 'firebase_project_id', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(34, 'firebase_storage_bucket', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(35, 'firebase_messaging_sender_id', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php'),
(36, 'firebase_app_id', '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'http://localhost/topup/login.php');

-- --------------------------------------------------------

--
-- Table structure for table `sliders`
--

CREATE TABLE `sliders` (
  `id` int(11) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `link` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `sliders`
--

INSERT INTO `sliders` (`id`, `image`, `link`) VALUES
(5, 'uploads/slider_1771678014.jpg', ''),
(6, 'uploads/slider_1771678034.jpg', ''),
(7, 'uploads/slider_1771678048.jpg', '');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `type` varchar(20) DEFAULT 'debit',
  `description` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'completed',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `balance` decimal(10,2) DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `support_pin` int(11) DEFAULT '0',
  `image` varchar(255) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `role` varchar(20) DEFAULT 'user',
  `status` varchar(20) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `phone`, `email`, `password`, `balance`, `created_at`, `support_pin`, `image`, `avatar`, `role`, `status`) VALUES
(0, 'Sameul Islam', '01700000000', 'sameulislam369@gmail.com', '$2y$10$Y6V3BOsEihWnMsIZRPq1O.ngpRlPqG/5z0Vb7KUuVIp0vmHdVKIgW', '77.00', '2026-02-21 12:44:00', 15431, NULL, NULL, 'admin', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `variations`
--

CREATE TABLE `variations` (
  `id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `stock` int(11) DEFAULT '999'
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deposits`
--
ALTER TABLE `deposits`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `game_id` (`game_id`);

--
-- Indexes for table `product_codes`
--
ALTER TABLE `product_codes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `status` (`status`);

--
-- Indexes for table `redeem_codes`
--
ALTER TABLE `redeem_codes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sliders`
--
ALTER TABLE `sliders`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `variations`
--
ALTER TABLE `variations`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `deposits`
--
ALTER TABLE `deposits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `product_codes`
--
ALTER TABLE `product_codes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `sliders`
--
ALTER TABLE `sliders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
