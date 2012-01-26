CREATE TABLE IF NOT EXISTS `members` (
  `id` int NOT NULL auto_increment,
  `groupid` int NOT NULL default '1',
  `username` varchar(100) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `theme` varchar(64) collate utf8_unicode_ci NOT NULL default 'bootstrap',
  `regdate` int NOT NULL default '0',
  `token` char(16) collate utf8_unicode_ci NOT NULL default '',
  `SQ_Index` int(2) NOT NULL,
  `SQ_Answer` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `password` (`password`),
  KEY `regdate` (`regdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE IF NOT EXISTS `groups` (
  `id` int NOT NULL auto_increment,
  `title` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `desc` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `icon` varchar(128) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=5;

INSERT INTO `groups` (`id`, `title`, `desc`, `icon`) VALUES
(1, 'Inactive', 'Inactive', ''),
(2, 'Banned', 'Banned', ''),
(3, 'Members', 'Members', ''),
(4, 'Administrators', 'Administrators', '');

CREATE TABLE IF NOT EXISTS `plugins` (
  `id` mediumint NOT NULL auto_increment,
  `hook` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `code` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `part` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `file` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `active` tinyint unsigned NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `plugins` (`id`, `hook`, `code`, `part`, `title`, `file`, `active`) VALUES
(1, 'index.tags', 'news', 'main', 'News', 'news/news.php', 1);

CREATE TABLE IF NOT EXISTS `categories` (
  `id` mediumint NOT NULL auto_increment,
  `code` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `path` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `title` varchar(128) collate utf8_unicode_ci NOT NULL,
  `desc` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `code` (`code`),
  KEY `path` (`path`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `categories` (`id`, `code`, `path`, `title`, `desc`) VALUES
(1, 'news', 1, 'News', 'A list of all important updates');

CREATE TABLE IF NOT EXISTS `pages` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `state` tinyint(1) unsigned NOT NULL default '0',
  `cat` varchar(255) collate utf8_unicode_ci NOT NULL,
  `title` varchar(255) collate utf8_unicode_ci NOT NULL,
  `desc` varchar(255) collate utf8_unicode_ci default NULL,
  `text` MEDIUMTEXT collate utf8_unicode_ci NOT NULL,
  `owner` int(11) NOT NULL default '0',
  `date` int(11) NOT NULL default '0',
  `page_file` tinyint(4) default NULL,
  `page_url` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`),
  KEY `cat` (`cat`),
  KEY `state` (`state`),
  KEY `date` (`date`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `pages` (`state`, `cat`, `title`, `desc`, `text`, `owner`, `date`, `page_file`, `page_url`) VALUES
(1, 'news', 'Welcome!', '', 'Congratulations! Your website has been successfully installed.', 1, UNIX_TIMESTAMP(), '', '');

CREATE TABLE IF NOT EXISTS `security_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(255) NOT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `security_questions` (`question`) VALUES
('What is the town you grew up in?'),
('What is your fathers middle name?'),
('What is your mothers maiden name?'),
('What was/is your highschool mascot?'),
('What state were you born in?'),
('What was the make/model of your first car?'),
('What is your favorite musician?');