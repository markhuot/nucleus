# ************************************************************
# Sequel Pro SQL dump
# Version 3408
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Host: 127.0.0.1 (MySQL 5.1.58-1ubuntu1)
# Database: tmp
# Generation Time: 2012-01-15 16:58:34 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table avatars
# ------------------------------------------------------------

DROP TABLE IF EXISTS `avatars`;

CREATE TABLE `avatars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `avatars` WRITE;
/*!40000 ALTER TABLE `avatars` DISABLE KEYS */;

INSERT INTO `avatars` (`id`, `url`)
VALUES
	(1,'Jack\'s Avatar');

/*!40000 ALTER TABLE `avatars` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories`;

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;

INSERT INTO `categories` (`id`, `title`)
VALUES
	(1,'a category'),
	(2,'another category'),
	(3,'final category');

/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table categories_posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `categories_posts`;

CREATE TABLE `categories_posts` (
  `category_id` int(11) NOT NULL DEFAULT '0',
  `post_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `categories_posts` WRITE;
/*!40000 ALTER TABLE `categories_posts` DISABLE KEYS */;

INSERT INTO `categories_posts` (`category_id`, `post_id`)
VALUES
	(1,1),
	(2,1),
	(3,1);

/*!40000 ALTER TABLE `categories_posts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `comments`;

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment` text,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;

INSERT INTO `comments` (`id`, `comment`, `post_id`, `user_id`)
VALUES
	(1,'Yea!',1,5),
	(2,'Woot!',1,3);

/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table posts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `posts`;

CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `posts` WRITE;
/*!40000 ALTER TABLE `posts` DISABLE KEYS */;

INSERT INTO `posts` (`id`, `user_id`, `title`)
VALUES
	(1,4,'Let\'s save the world'),
	(2,4,'A day in the life'),
	(3,4,'Defusing bombs 101.'),
	(4,5,'The art of the double cross'),
	(7,3,'Love in the workplace');

/*!40000 ALTER TABLE `posts` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table posts_tagged
# ------------------------------------------------------------

DROP TABLE IF EXISTS `posts_tagged`;

CREATE TABLE `posts_tagged` (
  `post_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `posts_tagged` WRITE;
/*!40000 ALTER TABLE `posts_tagged` DISABLE KEYS */;

INSERT INTO `posts_tagged` (`post_id`, `tag_id`)
VALUES
	(1,1),
	(1,2);

/*!40000 ALTER TABLE `posts_tagged` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table tags
# ------------------------------------------------------------

DROP TABLE IF EXISTS `tags`;

CREATE TABLE `tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;

INSERT INTO `tags` (`tag_id`, `name`)
VALUES
	(1,'tag1'),
	(2,'tag2'),
	(3,'tag3');

/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table users
# ------------------------------------------------------------

DROP TABLE IF EXISTS `users`;

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(54) DEFAULT NULL,
  `avatar_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;

INSERT INTO `users` (`id`, `name`, `email`, `password`, `avatar_id`)
VALUES
	(1,'Sample User','sample@example.com','jdafhaus',NULL),
	(2,'Another User','another@example.com','89yhr8sfhnaksf',NULL),
	(3,'Chloe O\'Brien','chloe@ctu.gov','ashfu98ashdf',NULL),
	(4,'Jack Bauer','jack@ctu.gov','saduf8ahgsdyf',1),
	(5,'Nina Myers','nina@ctu.gov','j98asdhfasdfua09',NULL),
	(6,'Curtis','curtis@ctu.gov','23984ryg8yhr23',NULL);

/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;



/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
