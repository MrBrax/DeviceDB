/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `device_additional` */

CREATE TABLE `device_additional` (
  `device_id` int(16) NOT NULL,
  `key` int(8) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`device_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `device_extra` */

CREATE TABLE `device_extra` (
  `device_id` int(32) NOT NULL,
  `data` char(64) NOT NULL,
  `description` char(255) DEFAULT NULL,
  PRIMARY KEY (`device_id`,`data`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `device_flags` */

CREATE TABLE `device_flags` (
  `device_id` int(32) NOT NULL,
  `flag_id` int(16) NOT NULL,
  `flag_value` tinyint(8) DEFAULT NULL,
  PRIMARY KEY (`device_id`,`flag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `device_model` */

CREATE TABLE `device_model` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `brand` char(64) DEFAULT NULL,
  `model` char(64) DEFAULT NULL,
  `type` int(8) DEFAULT NULL,
  `description` char(255) DEFAULT NULL,
  `image` char(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=latin1;

/*Table structure for table `device_psu` */

CREATE TABLE `device_psu` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `brand` char(32) DEFAULT NULL,
  `voltage` float DEFAULT NULL,
  `amperage` float DEFAULT NULL,
  `model` char(32) DEFAULT NULL,
  `icon` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=latin1;

/*Table structure for table `device_software` */

CREATE TABLE `device_software` (
  `device_id` int(16) NOT NULL,
  `software_id` int(16) NOT NULL,
  `serial` char(255) DEFAULT NULL,
  `installed` datetime DEFAULT NULL,
  PRIMARY KEY (`device_id`,`software_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `device_types` */

CREATE TABLE `device_types` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `no_owner` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Table structure for table `devices` */

CREATE TABLE `devices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `serial` char(255) DEFAULT NULL,
  `model` int(8) DEFAULT NULL,
  `psu` int(8) DEFAULT NULL,
  `psu_serial` char(255) DEFAULT NULL,
  `os` int(8) DEFAULT NULL,
  `type` int(8) DEFAULT '1',
  `location` int(8) DEFAULT NULL,
  `location_spec` char(128) DEFAULT NULL,
  `mac` char(48) DEFAULT NULL,
  `ip` char(48) DEFAULT NULL,
  `owner` int(16) DEFAULT NULL,
  `public` tinyint(1) DEFAULT '0',
  `storage` tinyint(1) DEFAULT '0',
  `repairing` tinyint(1) DEFAULT '0',
  `needs_repair` tinyint(1) DEFAULT '0',
  `byod` tinyint(1) DEFAULT '0',
  `acd` tinyint(1) DEFAULT '0',
  `outside` tinyint(1) DEFAULT '0',
  `travel` tinyint(1) DEFAULT '0',
  `dyslexia` tinyint(1) DEFAULT '0',
  `resigned` tinyint(1) DEFAULT '0',
  `date_aquired` datetime DEFAULT NULL,
  `date_installed` datetime DEFAULT NULL,
  `date_serviced` datetime DEFAULT NULL,
  `date_issued` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=217 DEFAULT CHARSET=latin1;

/*Table structure for table `locations` */

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `parent` int(16) DEFAULT NULL,
  `img` char(64) DEFAULT NULL,
  `x` float DEFAULT '0',
  `y` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=latin1;

/*Table structure for table `model_additional` */

CREATE TABLE `model_additional` (
  `model_id` int(16) NOT NULL,
  `key` int(8) NOT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`model_id`,`key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Table structure for table `owners` */

CREATE TABLE `owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(32) DEFAULT NULL,
  `firstname` char(64) DEFAULT NULL,
  `lastname` char(64) DEFAULT NULL,
  `ssn` char(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=latin1;

/*Table structure for table `owners_date` */

CREATE TABLE `owners_date` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `device_id` int(16) DEFAULT NULL,
  `owner_id` int(16) DEFAULT NULL,
  `date_aquired` datetime DEFAULT NULL,
  `date_leave` datetime DEFAULT NULL,
  `damage` blob,
  `notes` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=latin1;

/*Table structure for table `repairs` */

CREATE TABLE `repairs` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `device_id` int(16) DEFAULT NULL,
  `description` char(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Table structure for table `software` */

CREATE TABLE `software` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `version` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=latin1;

/*Table structure for table `system_os` */

CREATE TABLE `system_os` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` char(64) DEFAULT NULL,
  `version` char(32) DEFAULT NULL,
  `obsolete` tinyint(1) DEFAULT '0',
  `icon` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
