-- phpMyAdmin SQL Dump
-- version 3.2.4
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Oct 27, 2010 at 09:45 PM
-- Server version: 5.1.41
-- PHP Version: 5.3.1

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mound`
--

-- --------------------------------------------------------

--
-- Table structure for table `assignreleasecontainer`
--

CREATE TABLE IF NOT EXISTS `assignreleasecontainer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL DEFAULT '0',
  `releasecontainer_id` bigint(20) NOT NULL DEFAULT '0',
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `assignreleasecontainer`
--


-- --------------------------------------------------------

--
-- Table structure for table `breakdownnote`
--

CREATE TABLE IF NOT EXISTS `breakdownnote` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `breakdown_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `note` varchar(21844) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `breakdownnote`
--


-- --------------------------------------------------------

--
-- Table structure for table `releasecontainer`
--

CREATE TABLE IF NOT EXISTS `releasecontainer` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL DEFAULT '''No Container Name''',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `releasecontainer`
--


-- --------------------------------------------------------

--
-- Table structure for table `status`
--

CREATE TABLE IF NOT EXISTS `status` (
  `id` bigint(20) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

--
-- Dumping data for table `status`
--

INSERT INTO `status` (`id`, `name`, `description`, `active`) VALUES
(2, 'Analysis/Research', 'Pre-development information gathering and design.', b'1'),
(3, 'Development', 'Code writing and deliverable creation.', b'1'),
(4, 'Testing', 'Unit testing, Application Testing and User Testing.', b'1'),
(5, 'Implementation', 'Rolling out the project.', b'1'),
(1, 'New', 'Task that is unworked.', b'1'),
(6, 'Pending', 'A task that is on hold pending information from stakeholders or completion of dependent action.', b'1'),
(7, 'Completed', 'A task which is complete.', b'1'),
(8, 'Cancelled', 'Ticket was not done...', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `ticket`
--

CREATE TABLE IF NOT EXISTS `ticket` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(500) NOT NULL,
  `description` varchar(21844) NOT NULL,
  `business_value_rating` tinyint(4) NOT NULL DEFAULT '0',
  `complexity_rating` tinyint(4) NOT NULL DEFAULT '0',
  `predicted_hours` int(11) NOT NULL DEFAULT '0',
  `priority` bigint(20) NOT NULL DEFAULT '0',
  `status_id` bigint(20) NOT NULL DEFAULT '1',
  `tickettype_id` bigint(20) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `created_by` bigint(20) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ticket`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticketattachment`
--

CREATE TABLE IF NOT EXISTS `ticketattachment` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL,
  `source` varchar(500) NOT NULL,
  `description` varchar(21844) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ticketattachment`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticketbreakdown`
--

CREATE TABLE IF NOT EXISTS `ticketbreakdown` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL,
  `description` varchar(500) NOT NULL DEFAULT '''No Task Name''',
  `created_by` bigint(20) NOT NULL DEFAULT '0',
  `completed_by` bigint(20) NOT NULL,
  `complete` bit(1) NOT NULL DEFAULT b'0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ticketbreakdown`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticketnote`
--

CREATE TABLE IF NOT EXISTS `ticketnote` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `note` varchar(21844) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ticketnote`
--


-- --------------------------------------------------------

--
-- Table structure for table `ticketrole`
--

CREATE TABLE IF NOT EXISTS `ticketrole` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(21844) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `ticketrole`
--

INSERT INTO `ticketrole` (`id`, `name`, `description`, `active`) VALUES
(1, 'Actor', 'A person from which work is required to move the project forward.', b'1'),
(2, 'Monitor', 'A person that just needs to stay in the loop.', b'1'),
(3, 'Owner', 'A person that is the go-to person during this phase of the project.', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `tickettime`
--

CREATE TABLE IF NOT EXISTS `tickettime` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `hour` int(11) NOT NULL DEFAULT '0',
  `minute` int(11) NOT NULL DEFAULT '0',
  `description` varchar(500) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `tickettime`
--


-- --------------------------------------------------------

--
-- Table structure for table `tickettype`
--

CREATE TABLE IF NOT EXISTS `tickettype` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(500) NOT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Dumping data for table `tickettype`
--

INSERT INTO `tickettype` (`id`, `name`, `description`, `active`) VALUES
(1, 'Project', 'A complex specification that could take more than a few hours.', b'1'),
(2, 'Task', 'A small specification that should take no more than a few hours to complete.', b'1'),
(3, 'Bug', 'Squash some bugs...', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `ticketuserrole`
--

CREATE TABLE IF NOT EXISTS `ticketuserrole` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticket_id` bigint(20) NOT NULL DEFAULT '0',
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `ticketrole_id` bigint(20) NOT NULL DEFAULT '0',
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `ticketuserrole`
--


-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `name`, `password`, `email`, `first_name`, `last_name`, `active`, `created_at`) VALUES
(1, 'Test', 'moqt55ByXlS4.', 'test@test.com', 'Test', 'McTest', b'1', '2010-10-18 18:51:22');

-- --------------------------------------------------------

--
-- Table structure for table `userstatus`
--

CREATE TABLE IF NOT EXISTS `userstatus` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) NOT NULL DEFAULT '0',
  `user_status` varchar(500) NOT NULL DEFAULT '''Doing Stuff...''',
  `active` bit(1) NOT NULL DEFAULT b'1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

--
-- Dumping data for table `userstatus`
--


/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
