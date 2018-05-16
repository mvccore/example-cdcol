CREATE DATABASE IF NOT EXISTS `cdcol` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `cdcol`;

DROP TABLE IF EXISTS `cds`;
CREATE TABLE `cds` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `interpret` varchar(200) NOT NULL,
  `year` int(4) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `title` (`title`),
  KEY `interpret` (`interpret`),
  KEY `year` (`year`)
) CHARSET=utf8;

INSERT INTO `cds` (`id`, `title`, `interpret`, `year`) VALUES
(1,	'Jump',	'Van Halen',	1984),
(2,	'Hey Boy Hey Girl',	'The Chemical Brothers',	1999),
(3,	'Black Light',	'Groove Armada',	2010),
(4,	'Hotel',	'Moby',	2005),
(5, 'Berlin Calling', 'Paul Kalkbrenner', 2008);

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_name` varchar(50) NOT NULL,
  `password_hash` varchar(60) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_name` (`user_name`)
) CHARSET=utf8;

INSERT INTO `users` (`id`, `user_name`, `password_hash`, `full_name`) VALUES
(1,	'admin',	'$2y$10$IWZhKlJOK3R3ZTY1dHMxROHExizREYyUTjUUiQfm9wk.rrPCRjwbC',	'Administrator'); -- password is "demo"