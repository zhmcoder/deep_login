-- phpMyAdmin SQL Dump
-- version 4.8.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Dec 06, 2021 at 03:22 PM
-- Server version: 5.5.60-log
-- PHP Version: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `xunji`
--

-- --------------------------------------------------------

--
-- Table structure for table `verify_code`
--

CREATE TABLE `verify_code` (
  `id` int(11) NOT NULL,
  `mobile` varchar(13) NOT NULL COMMENT '手机号',
  `code` varchar(6) NOT NULL COMMENT '验证码',
  `ctime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `utime` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT '使用时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '-1失效0未使用1已使用',
  `sendStatus` tinyint(4) NOT NULL DEFAULT '-1' COMMENT '0表示发送 1表示发送成功，2表示发送失败',
  `sendTime` timestamp NULL DEFAULT NULL,
  `smsCreated` varchar(16) DEFAULT NULL,
  `smsSid` varchar(32) DEFAULT NULL,
  `smsStatus` varchar(16) DEFAULT NULL,
  `client_ip` varchar(16) CHARACTER SET utf8mb4 DEFAULT NULL,
  `app_id` varchar(64) CHARACTER SET utf8mb4 DEFAULT NULL,
  `created_at` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `verify_code`
--
ALTER TABLE `verify_code`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `verify_code`
--
ALTER TABLE `verify_code`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;
