CREATE TABLE IF NOT EXISTS `config` (
  `part` varchar(24) collate utf8_unicode_ci NOT NULL default 'core',
  `title` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `order` char(2) collate utf8_unicode_ci NOT NULL default '00',
  `value` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `default` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `varients` varchar(255) collate utf8_unicode_ci NOT NULL default '',
  `text` varchar(255) collate utf8_unicode_ci NOT NULL default '',
   KEY (`part`, `title`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

INSERT INTO `config` (`part`, `title`, `order`, `value`, `default`, `varients`, `text`) VALUES
('core', 'version', '1', '0.5.0', '', '', ''),
('core', 'title', '2', '', '', '', ''),
('core', 'subtitle', '3', '', '', '', ''),
('core', 'keywords', '4', '', '', '', ''),
('core', 'forcetheme', '5', 'no', '', '', ''),
('core', 'disablereg', '6', 'no', '', '', ''),
('core', 'valnew', '7', 'no', '', '', ''),
('core', 'disableval', '8', 'no', '', '', ''),
('core', 'maintenance', '9', 'no', '', '', '');

CREATE TABLE IF NOT EXISTS `members` (
  `id` int NOT NULL auto_increment,
  `groupid` int NOT NULL default '1',
  `username` varchar(100) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `theme` varchar(64) collate utf8_unicode_ci NOT NULL default 'bootstrap',
  `birthdate` DATE NOT NULL DEFAULT '0000-00-00',
  `gender` char(1) collate utf8_unicode_ci NOT NULL default 'U',
  `avatar` varchar(225) collate utf8_unicode_ci NOT NULL default 'assets/avatars/blank_avatar.jpg',
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
(1, 'news', 1, 'News', 'A list of all important updates'),
(2, 'articles', 2, 'Articles', 'Interesting reads around the web'),
(3, 'downloads', 3, 'Downloads', 'Files available for download');

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