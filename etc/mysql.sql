CREATE TABLE IF NOT EXISTS `jos_jstats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unique_id` varchar(32) NOT NULL,
  `php_version` varchar(255) NOT NULL,
  `db_type` varchar(255) NOT NULL,
  `db_version` varchar(255) NOT NULL,
  `cms_version` varchar(255) NOT NULL,
  `server_os` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
