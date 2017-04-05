CREATE DATABASE `pb` /*!40100 DEFAULT CHARACTER SET utf8 */;

DROP TABLE IF EXISTS `pb`.`pb_contacts`;
CREATE TABLE  `pb`.`pb_contacts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `samname` varchar(255) NOT NULL DEFAULT '',
  `fname` varchar(255) NOT NULL DEFAULT '',
  `lname` varchar(255) NOT NULL DEFAULT '',
  `dep` varchar(255) NOT NULL DEFAULT '',
  `org` varchar(255) NOT NULL DEFAULT '',
  `pos` varchar(255) NOT NULL DEFAULT '',
  `pint` varchar(255) NOT NULL DEFAULT '',
  `pcell` varchar(255) NOT NULL DEFAULT '',
  `mail` varchar(255) NOT NULL DEFAULT '',
  `bday` date DEFAULT NULL,
  `mime` varchar(255) NOT NULL DEFAULT '',
  `photo` blob NOT NULL,
  `visible` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
