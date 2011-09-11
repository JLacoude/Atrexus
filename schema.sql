--
-- Database: `atrexus`
--

-- --------------------------------------------------------

--
-- Table structure for table `actionlogs`
--

CREATE TABLE IF NOT EXISTS `actionlogs` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `time` datetime NOT NULL,
  `type` enum('attackSoldier','bindToItem','captureHeadquarter','createSoldier','moveSoldier') NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `battlefield_id` int(11) unsigned NOT NULL,
  `by_id` bigint(20) NOT NULL,
  `by_X` int(11) NOT NULL,
  `by_Y` int(11) NOT NULL,
  `target_id` bigint(20) NOT NULL,
  `target_X` int(11) NOT NULL,
  `target_Y` int(11) NOT NULL,
  `damages` int(11) NOT NULL,
  `kill` tinyint(1) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `byUser` (`time`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `battlefields`
--

CREATE TABLE IF NOT EXISTS `battlefields` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `visibility` enum('public','restricted','private') NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `headquarters`
--

CREATE TABLE IF NOT EXISTS `headquarters` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `hive_id` int(11) unsigned DEFAULT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `cost_to_capture` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `position_id` (`position_id`),
  KEY `hive_id` (`hive_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `hives`
--

CREATE TABLE IF NOT EXISTS `hives` (
  `ID` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `battlefield_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `battlefield_id` (`battlefield_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `logintries`
--

CREATE TABLE IF NOT EXISTS `logintries` (
  `IP` varchar(39) NOT NULL,
  `counter` int(11) NOT NULL,
  `last` datetime NOT NULL,
  PRIMARY KEY (`IP`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `passwordresets`
--

CREATE TABLE IF NOT EXISTS `passwordresets` (
  `token` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `personnas`
--

CREATE TABLE IF NOT EXISTS `personnas` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `battlefield_id` int(11) unsigned NOT NULL,
  `hive_id` int(11) unsigned NOT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `AP` int(10) unsigned NOT NULL,
  `last_regen` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `hive_id` (`hive_id`),
  KEY `position_id` (`position_id`),
  KEY `user_id` (`user_id`),
  KEY `battlefield_id` (`battlefield_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE IF NOT EXISTS `positions` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `battlefield_id` int(11) unsigned NOT NULL,
  `X` int(11) NOT NULL,
  `Y` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `unicity` (`battlefield_id`,`X`,`Y`),
  KEY `battlefield_id` (`battlefield_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `soldiers`
--

CREATE TABLE IF NOT EXISTS `soldiers` (
  `ID` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `hive_id` int(11) unsigned NOT NULL,
  `position_id` bigint(20) unsigned DEFAULT NULL,
  `HP` int(10) unsigned NOT NULL,
  `AP` int(10) unsigned NOT NULL,
  `updated` datetime NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `position_id` (`position_id`),
  KEY `hive_id` (`hive_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `ID` int(11) NOT NULL AUTO_INCREMENT,
  `IP` varchar(39) DEFAULT NULL,
  `login` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `IP` (`IP`,`login`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Constraints
--

--
-- Constraints for table `headquarters`
--
ALTER TABLE `headquarters`
  ADD CONSTRAINT `headquarters_ibfk_2` FOREIGN KEY (`hive_id`) REFERENCES `hives` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `headquarters_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `hives`
--
ALTER TABLE `hives`
  ADD CONSTRAINT `hives_ibfk_1` FOREIGN KEY (`battlefield_id`) REFERENCES `battlefields` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `personnas`
--
ALTER TABLE `personnas`
  ADD CONSTRAINT `personnas_ibfk_1` FOREIGN KEY (`hive_id`) REFERENCES `hives` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `personnas_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `personnas_ibfk_3` FOREIGN KEY (`battlefield_id`) REFERENCES `battlefields` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `positions`
--
ALTER TABLE `positions`
  ADD CONSTRAINT `positions_ibfk_1` FOREIGN KEY (`battlefield_id`) REFERENCES `battlefields` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `soldiers`
--
ALTER TABLE `soldiers`
  ADD CONSTRAINT `soldiers_ibfk_1` FOREIGN KEY (`hive_id`) REFERENCES `hives` (`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `soldiers_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`ID`) ON DELETE SET NULL ON UPDATE CASCADE;

-- --------------------------------------------------------

--
-- Triggers
--

--
-- Triggers `soldiers`
--
DROP TRIGGER IF EXISTS `testpositionsoldier`;
DELIMITER //
CREATE TRIGGER `testpositionsoldier` BEFORE INSERT ON `soldiers`
 FOR EACH ROW BEGIN
     DECLARE hqalreadythere INT;
     SELECT COUNT(*) INTO hqalreadythere FROM headquarters WHERE headquarters.position_id = NEW.position_id;
     IF hqalreadythere > 0 THEN SET NEW.position_id = NULL; 
     END IF;
  END
//
DELIMITER ;

