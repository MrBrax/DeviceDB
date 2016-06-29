/*
SQLyog Ultimate v11.5 (64 bit)
MySQL - 5.5.41-0+wheezy1 : Database - wdevices
*********************************************************************
*/

/*!40101 SET NAMES utf8 */;

/*!40101 SET SQL_MODE=''*/;

/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
/*Table structure for table `device_extra` */

CREATE TABLE `device_extra` (
  `device_id` int(32) NOT NULL,
  `data` char(64) NOT NULL,
  `description` char(255) DEFAULT NULL,
  PRIMARY KEY (`device_id`,`data`)
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

/*Data for the table `device_model` */

insert  into `device_model`(`id`,`brand`,`model`,`type`,`description`,`image`) values (1,'HP','Pro x2',6,'Detachable screen, gray.','hpprox2.jpg'),(2,'Apple','iPad Mini',4,NULL,'ipadmini.jpg'),(3,'Fujitsu','Lifebook S710',6,NULL,NULL),(4,'ASUS','PU500C',6,NULL,NULL),(5,'ASUS','M51VR',6,NULL,'asus_m51vr.jpg'),(6,'Qihan','QH-NV470SO-P',5,NULL,NULL),(7,'Lenovo','G555',6,NULL,'lenovo_g555.jpg'),(8,'TP-Link','TL-SG2424',7,'24 port managed switch',NULL),(9,'Custom','Computer',1,NULL,'customcomputer.jpg'),(10,'MSI','GE60',6,NULL,NULL),(11,'XEROX','ColorQube 9301',8,NULL,'colorqube.gif'),(12,'XEROX','ColorQube 8900X',8,NULL,'colorqube.gif'),(13,'XEROX','ColorQube 8900S',8,NULL,'colorqube.gif'),(14,'Hexatronic','GAFS242M',9,NULL,NULL),(15,'Brother','HL-2150N',8,NULL,NULL),(16,'HP','LaserJet 1320',8,NULL,NULL),(17,'Brother','HL-2250DN',8,NULL,NULL),(18,'Custom','Server',2,NULL,NULL),(19,'Personal','Laptop',6,NULL,NULL),(20,'ASUS','F550C',6,NULL,NULL),(21,'DELL','Vostro V131',6,NULL,NULL),(22,'ASUS','Pro PU301L',6,NULL,NULL),(23,'ASUS','S301L',6,NULL,NULL),(24,'Lenovo','ThinkPad 0196-5PG',6,NULL,NULL),(25,'ASUS','X5DC-SX011V',6,NULL,NULL),(26,'DELL','Latitude E6220',6,NULL,NULL),(27,'ASUS','K70I',6,NULL,'k701.jpg'),(28,'HP','EliteBook 2530p',6,NULL,NULL),(29,'Apple','Mac Mini 2009',1,NULL,NULL),(30,'ASUS','EEE PC 1015PEM',6,NULL,NULL),(31,'ASUS','X71SL',6,NULL,NULL),(32,'ASUS','X5DIN',6,NULL,NULL),(33,'ASUS','X302L',6,NULL,NULL),(34,'Samsung','CLX-3170',8,NULL,NULL),(35,'Brother','Brother HL-5250DN',8,NULL,NULL),(36,'Acer','Aspire E1-571',NULL,NULL,NULL);

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

/*Data for the table `device_psu` */

insert  into `device_psu`(`id`,`brand`,`voltage`,`amperage`,`model`,`icon`) values (1,'Lenovo',20,3.25,'42T4416','42T4416.jpg'),(2,'HP',19.5,2.31,NULL,NULL),(3,'HP',18.5,3.5,'608425-002','608425-002.jpg'),(4,'ASUS',19,3.42,'EXA1203YH','EXA1203YH.jpg'),(5,'DELL',19.5,3.34,'HA65NS1-00',NULL),(6,'ASUS',19,3.42,'SADP-65KB B','SADP-65KB_B.jpg'),(7,'DELL',19.5,6.7,NULL,'vostropsu.jpg');

/*Table structure for table `device_software` */

CREATE TABLE `device_software` (
  `device_id` int(16) NOT NULL,
  `software_id` int(16) NOT NULL,
  `serial` char(255) DEFAULT NULL,
  `installed` datetime DEFAULT NULL,
  PRIMARY KEY (`device_id`,`software_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

/*Data for the table `device_software` */

/*Table structure for table `device_types` */

CREATE TABLE `device_types` (
  `id` int(32) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

/*Data for the table `device_types` */

insert  into `device_types`(`id`,`name`) values (1,'Computer'),(2,'Server'),(3,'Router'),(4,'Tablet'),(5,'IP Camera'),(6,'Laptop'),(7,'Switch'),(8,'Printer'),(9,'Fiber switch');

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
  `date_aquired` datetime DEFAULT NULL,
  `date_installed` datetime DEFAULT NULL,
  `date_serviced` datetime DEFAULT NULL,
  `date_issued` datetime DEFAULT NULL,
  `deleted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=214 DEFAULT CHARSET=latin1;


/*Table structure for table `locations` */

CREATE TABLE `locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `parent` int(16) DEFAULT NULL,
  `img` char(64) DEFAULT NULL,
  `x` float DEFAULT '0',
  `y` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=latin1;

/*Table structure for table `owners` */

CREATE TABLE `owners` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` char(32) DEFAULT NULL,
  `firstname` char(64) DEFAULT NULL,
  `lastname` char(64) DEFAULT NULL,
  `ssn` char(64) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=latin1;

/*Table structure for table `repairs` */

CREATE TABLE `repairs` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `device_id` int(16) DEFAULT NULL,
  `description` char(255) DEFAULT NULL,
  `date_created` datetime DEFAULT NULL,
  `date_start` datetime DEFAULT NULL,
  `date_end` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;


/*Table structure for table `software` */

CREATE TABLE `software` (
  `id` int(16) NOT NULL AUTO_INCREMENT,
  `name` char(255) DEFAULT NULL,
  `version` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;


/*Table structure for table `system_os` */

CREATE TABLE `system_os` (
  `id` int(8) NOT NULL AUTO_INCREMENT,
  `name` char(64) DEFAULT NULL,
  `version` char(32) DEFAULT NULL,
  `obsolete` tinyint(1) DEFAULT '0',
  `icon` char(32) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

/*Data for the table `system_os` */

insert  into `system_os`(`id`,`name`,`version`,`obsolete`,`icon`) values (1,'Windows 7','Home',0,NULL),(2,'Windows 8','Pro',0,NULL),(3,'Windows 8','Enterprise',0,NULL),(4,'Windows 8.1','Pro',0,'w81pro.png'),(5,'Windows 8.1','Enterprise',0,'w81ent.png'),(6,'Windows 10','Pro',0,'w10pro.png'),(7,'Windows 10','Enterprise',0,'w10ent.png'),(8,'Debian','7',0,NULL),(9,'Debian','8',0,NULL),(10,'Windows Server 2008R2','Enterprise',0,'w7srv.png'),(11,'Windows Server 2012','DataCenter',0,'w8srv.png'),(12,'pfSense 2.2.6','',0,'pfsense.png'),(13,'pfSense 2.3',NULL,0,NULL),(14,'FreeNAS',NULL,0,NULL),(15,'Windows 7','Pro',0,'w7pro.png'),(16,'iOS 9.3.1',NULL,0,NULL),(17,'iOS 9.3.2',NULL,0,NULL);

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
