CREATE TABLE IF NOT EXISTS `jos_jstats` (
  `unique_id` varchar(40) NOT NULL,
  `php_version` varchar(255) NOT NULL,
  `db_type` varchar(255) NOT NULL,
  `db_version` varchar(255) NOT NULL,
  `cms_version` varchar(255) NOT NULL,
  `server_os` varchar(255) NOT NULL,
  `modified` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`unique_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
