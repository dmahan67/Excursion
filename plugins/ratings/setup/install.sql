DROP TABLE IF EXISTS `ratings`;
CREATE TABLE IF NOT EXISTS `ratings` (
	`id` int(11) NOT NULL auto_increment,
	`rating_id` int(11) NOT NULL,
	`rating_num` int(11) NOT NULL,
	`user` varchar(25) NOT NULL,
PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;