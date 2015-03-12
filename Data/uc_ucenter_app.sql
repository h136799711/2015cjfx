-- phpMyAdmin SQL Dump
-- version 4.2.0-alpha2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2015-02-11 16:26:56
-- 服务器版本： 5.5.37
-- PHP Version: 5.4.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `hebidu_rbac`
--

-- --------------------------------------------------------

--
-- 表的结构 `uc_ucenter_app`
--

CREATE TABLE IF NOT EXISTS `uc_ucenter_app` (
`id` int(10) unsigned NOT NULL COMMENT '应用ID',
  `app_id` char(32) NOT NULL COMMENT 'appid随机生成',
  `title` varchar(30) NOT NULL COMMENT '应用名称',
  `url` varchar(100) NOT NULL COMMENT '应用URL',
  `ip` char(15) NOT NULL DEFAULT '' COMMENT '应用IP',
  `auth_key` varchar(100) NOT NULL DEFAULT '' COMMENT '加密KEY',
  `sys_login` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '同步登陆',
  `allow_ip` varchar(255) NOT NULL DEFAULT '' COMMENT '允许访问的IP',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `status` tinyint(4) NOT NULL DEFAULT '0' COMMENT '应用状态'
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='应用表' AUTO_INCREMENT=3 ;

--
-- 转存表中的数据 `uc_ucenter_app`
--

INSERT INTO `uc_ucenter_app` (`id`, `app_id`, `title`, `url`, `ip`, `auth_key`, `sys_login`, `allow_ip`, `create_time`, `update_time`, `status`) VALUES
(1, '', 'OAuth2应用模块', 'http://127.0.0.1/', '127.0.0.1', 'hDE):a&XO"yF<V,>q}|L!nogZ~KfHWJ8MRQjY]7s', 1, '127.0.0.1', 0, 0, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `uc_ucenter_app`
--
ALTER TABLE `uc_ucenter_app`
 ADD PRIMARY KEY (`id`), ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `uc_ucenter_app`
--
ALTER TABLE `uc_ucenter_app`
MODIFY `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '应用ID',AUTO_INCREMENT=3;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
