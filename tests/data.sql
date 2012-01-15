-- phpMyAdmin SQL Dump
-- version 3.3.9.2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jan 15, 2012 at 03:45 PM
-- Server version: 5.5.9
-- PHP Version: 5.3.5

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tmp`
--

-- --------------------------------------------------------

--
-- Table structure for table `avatars`
--

CREATE TABLE `avatars` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `avatars`
--

INSERT INTO `avatars` VALUES(1, 'Jack''s Avatar');
INSERT INTO `avatars` VALUES(3, 'Nina''s Avatar');

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` VALUES(1, 'a category');
INSERT INTO `categories` VALUES(2, 'another category');
INSERT INTO `categories` VALUES(3, 'final category');

-- --------------------------------------------------------

--
-- Table structure for table `categories_posts`
--

CREATE TABLE `categories_posts` (
  `category_id` int(11) NOT NULL DEFAULT '0',
  `post_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`,`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `categories_posts`
--

INSERT INTO `categories_posts` VALUES(1, 1);
INSERT INTO `categories_posts` VALUES(2, 1);
INSERT INTO `categories_posts` VALUES(3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `comment` text,
  `post_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `comments`
--

INSERT INTO `comments` VALUES(1, 'Yea!', 1, 5);
INSERT INTO `comments` VALUES(2, 'Woot!', 1, 3);

-- --------------------------------------------------------

--
-- Table structure for table `posts`
--

CREATE TABLE `posts` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=8 ;

--
-- Dumping data for table `posts`
--

INSERT INTO `posts` VALUES(1, 4, 'Let''s save the world');
INSERT INTO `posts` VALUES(2, 4, 'A day in the life');
INSERT INTO `posts` VALUES(3, 4, 'Defusing bombs 101.');
INSERT INTO `posts` VALUES(4, 5, 'The art of the double cross');
INSERT INTO `posts` VALUES(7, 3, 'Love in the workplace');

-- --------------------------------------------------------

--
-- Table structure for table `posts_tagged`
--

CREATE TABLE `posts_tagged` (
  `post_id` int(11) DEFAULT NULL,
  `tag_id` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Dumping data for table `posts_tagged`
--

INSERT INTO `posts_tagged` VALUES(1, 1);
INSERT INTO `posts_tagged` VALUES(1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `tags`
--

CREATE TABLE `tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`tag_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=4 ;

--
-- Dumping data for table `tags`
--

INSERT INTO `tags` VALUES(1, 'tag1');
INSERT INTO `tags` VALUES(2, 'tag2');
INSERT INTO `tags` VALUES(3, 'tag3');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(54) DEFAULT NULL,
  `avatar_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=7 ;

--
-- Dumping data for table `users`
--

INSERT INTO `users` VALUES(1, 'Sample User', 'sample@example.com', 'jdafhaus', NULL);
INSERT INTO `users` VALUES(2, 'Another User', 'another@example.com', '89yhr8sfhnaksf', NULL);
INSERT INTO `users` VALUES(3, 'Chloe O''Brien', 'chloe@ctu.gov', 'ashfu98ashdf', NULL);
INSERT INTO `users` VALUES(4, 'Jack Bauer', 'jack@ctu.gov', 'saduf8ahgsdyf', 1);
INSERT INTO `users` VALUES(5, 'Nina Myers', 'nina@ctu.gov', 'j98asdhfasdfua09', 3);
INSERT INTO `users` VALUES(6, 'Curtis', 'curtis@ctu.gov', '23984ryg8yhr23', NULL);
