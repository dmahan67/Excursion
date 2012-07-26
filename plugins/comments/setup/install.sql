DROP TABLE IF EXISTS `comments`;
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int NOT NULL auto_increment,
  `area` varchar(225) collate utf8_unicode_ci NOT NULL default '',
  `area_id` int NOT NULL default '0',
  `userid` varchar(100) collate utf8_unicode_ci NOT NULL,
  `date` int(11) NOT NULL default '0',
  `text` MEDIUMTEXT collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;