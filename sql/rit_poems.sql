-- phpMyAdmin SQL Dump
-- version 4.0.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Sep 18, 2013 at 05:00 PM
-- Server version: 5.5.31-0ubuntu0.12.04.1-log
-- PHP Version: 5.3.10-1ubuntu3.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `rit`
--

-- --------------------------------------------------------

--
-- Table structure for table `rit_poems`
--

CREATE TABLE IF NOT EXISTS `rit_poems` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'The unique ID of the entry.',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The title of the poem.',
  `author` varchar(255) COLLATE utf8_unicode_ci NOT NULL COMMENT 'The author of the poem.',
  `text` text COLLATE utf8_unicode_ci NOT NULL COMMENT 'The text of the poem.',
  `votes` int(11) NOT NULL DEFAULT '0' COMMENT 'The amount of votes that the poem has.',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Stores the RIT honors poems.' AUTO_INCREMENT=2 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
