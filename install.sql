CREATE TABLE `members` (
  `id` int NOT NULL auto_increment,
  `groupid` int NOT NULL default '1',
  `username` varchar(100) collate utf8_unicode_ci NOT NULL,
  `password` varchar(32) collate utf8_unicode_ci NOT NULL default '',
  `email` varchar(64) collate utf8_unicode_ci NOT NULL default '',
  `regdate` int NOT NULL default '0',
  `token` char(16) collate utf8_unicode_ci NOT NULL default '',
  PRIMARY KEY  (`id`),
  KEY `password` (`password`),
  KEY `regdate` (`regdate`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `groups` (
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

CREATE TABLE `plugins` (
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