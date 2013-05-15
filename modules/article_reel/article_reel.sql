-- phpMyAdmin SQL Dump
-- version 3.5.5
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Apr 24, 2013 at 11:38 AM
-- Server version: 5.5.29
-- PHP Version: 5.4.10

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

--
-- Database: `oxidshop`
--

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `projectName` varchar(50) NOT NULL,
  `fileName` varchar(50) NOT NULL,
  `width` int(4) NOT NULL,
  `height` int(4) NOT NULL,
  `singleFiles` int(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `projectName` (`projectName`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;